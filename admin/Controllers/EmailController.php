<?php

namespace Admin\Controllers;

use Core\Controller;

class EmailController extends Controller
{
    protected $auth, $request, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    /** Open the PostfixAdmin SQLite DB (the real mail store). */
    protected function mailDb()
    {
        $file = '/var/vmail/postfixadmin.db';
        if (!is_readable($file)) return null;
        try {
            return new \PDO('sqlite:' . $file, null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /** Generate a bcrypt hash matching PostfixAdmin (raw hash; scheme goes in password_encode). */
    protected function mailHash($password)
    {
        $hash = trim((string)@shell_exec('htpasswd -bnBC 10 "" ' . escapeshellarg($password) . ' 2>/dev/null | tr -d ":\n"'));
        return $hash;
    }

    /** Sync a mailbox row into the panel's MySQL table (UI mirror). */
    protected function syncMailAccount($email, $domain, $password, $quota)
    {
        $existing = $this->db->table('mail_accounts')->where('email', $email)->first();
        $data = ['email' => $email, 'domain' => $domain, 'quota_mb' => (int)$quota];
        if ($password !== null) {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            $data['password_plain'] = $password;
        }
        if ($existing) $this->db->table('mail_accounts')->where('id', $existing->id)->update($data);
        else $this->db->table('mail_accounts')->insertGetId($data);
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $tab = $this->request->get('tab', 'accounts');
        // Read mailboxes from PostfixAdmin SQLite (source of truth)
        $accounts = [];
        $sqlite = $this->mailDb();
        if ($sqlite) {
            try {
                $rows = $sqlite->query("SELECT id, username, full_name, local_part, domain, quota, active, created FROM mailbox ORDER BY username")->fetchAll(\PDO::FETCH_OBJ);
                foreach ($rows as $r) $accounts[] = (object)[
                    'id' => $r->id, 'email' => $r->username, 'domain' => $r->domain,
                    'quota_mb' => (int)($r->quota / 1024), 'created_at' => $r->created,
                ];
            } catch (\Exception $e) {}
        }
        if (!$accounts) $accounts = $this->db->table('mail_accounts')->get() ?: [];
        $forwarders = $this->db->table('mail_forwarders')->get() ?: [];
        $autoresponders = $this->db->table('mail_autoresponder')->get() ?: [];
        $spam = $this->db->table('mail_spam')->get() ?: [];
        $queueSize = trim(shell_exec('mailq 2>/dev/null | tail -1 | awk "{print \$5}"') ?: '0');
        $postfix = trim(shell_exec('systemctl is-active postfix 2>/dev/null') ?: 'unknown');
        $dovecot = trim(shell_exec('systemctl is-active dovecot 2>/dev/null') ?: 'unknown');
        $domains = $this->db->table('hosting_users')->get() ?: [];

        return $this->view('admin.email.index', [
            'user' => $user, 'title' => 'Email', 'tab' => $tab,
            'accounts' => $accounts, 'forwarders' => $forwarders,
            'autoresponders' => $autoresponders, 'spam' => $spam,
            'queueSize' => $queueSize, 'postfix' => $postfix, 'dovecot' => $dovecot,
            'domains' => $domains,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function createAccount()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $email = $this->request->post('email', '');
        $domain = $this->request->post('domain', '');
        $password = $this->request->post('password', '');
        $full = strtolower(trim($email)) . '@' . strtolower(trim($domain));
        if ($email && $domain && strlen($password) >= 6) {
            $sqlite = $this->mailDb();
            $local = explode('@', $full)[0];
            if ($sqlite) {
                $stmt = $sqlite->prepare("INSERT INTO mailbox (username, password, password_encode, full_name, maildir, quota, local_part, domain, active) VALUES (?, ?, '{BLF-CRYPT}', ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$full, $this->mailHash($password), $local, strtolower($domain) . '/' . $local . '/', 1048576 * (int)$this->request->post('quota', 1000), $local, strtolower($domain)]);
                // Ensure maildir exists
                @exec('mkdir -p ' . escapeshellarg('/var/vmail/' . strtolower($domain) . '/' . $local));
                @exec('chown -R 5000:8 ' . escapeshellarg('/var/vmail/' . strtolower($domain) . '/' . $local));
            }
            // Sync mirror for the panel UI
            $this->syncMailAccount($full, $domain, $password, (int)$this->request->post('quota', 1000));
            $_SESSION['success_message'] = "Account {$full} created (wired to PostfixAdmin/Dovecot).";
        } else {
            $_SESSION['error_message'] = 'Username, domain and password (min 6 chars) required.';
        }
        $this->response->redirect('/admin/email?tab=accounts');
    }

    public function deleteAccount($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $email = '';
        // The list shows SQLite mailbox ids -> resolve email from the real store
        $sqlite = $this->mailDb();
        if ($sqlite) {
            $stmt = $sqlite->prepare("SELECT username FROM mailbox WHERE id = ?");
            $stmt->execute([(int)$id]);
            $row = $stmt->fetch(\PDO::FETCH_OBJ);
            if ($row) $email = $row->username;
        }
        // Fallback: MySQL mirror row by id
        if ($email === '') {
            $acct = $this->db->table('mail_accounts')->where('id', (int)$id)->first();
            $email = $acct->email ?? '';
        }
        // Delete from PostfixAdmin SQLite (the real store)
        if ($sqlite && $email) {
            $stmt = $sqlite->prepare("DELETE FROM mailbox WHERE username = ?");
            $stmt->execute([$email]);
            $stmt = $sqlite->prepare("DELETE FROM alias WHERE address = ?");
            $stmt->execute([$email]);
        }
        $this->db->table('mail_accounts')->where('email', $email)->delete();
        $_SESSION['success_message'] = $email ? "Mailbox {$email} deleted." : 'Mailbox not found.';
        $this->response->redirect('/admin/email?tab=accounts');
    }

    public function createForwarder()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('mail_forwarders')->insertGetId([
            'from_email' => $this->request->post('from', ''), 'to_email' => $this->request->post('to', ''),
            'domain' => $this->request->post('domain', ''),
        ]);
        $_SESSION['success_message'] = 'Forwarder created.';
        $this->response->redirect('/admin/email?tab=forwarders');
    }

    public function deleteForwarder($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('mail_forwarders')->where('id', $id)->delete();
        $this->response->redirect('/admin/email?tab=forwarders');
    }

    public function setAutoresponder()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $email = $this->request->post('email', '');
        $existing = $this->db->table('mail_autoresponder')->where('email', $email)->first();
        $data = ['email' => $email, 'domain' => $this->request->post('domain', ''), 'subject' => $this->request->post('subject', ''), 'message' => $this->request->post('message', ''), 'enabled' => 1];
        if ($existing) $this->db->table('mail_autoresponder')->where('id', $existing->id)->update($data);
        else $this->db->table('mail_autoresponder')->insertGetId($data);
        $_SESSION['success_message'] = 'Autoresponder saved.';
        $this->response->redirect('/admin/email?tab=autoresponders');
    }

    public function disableAutoresponder($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('mail_autoresponder')->where('id', $id)->update(['enabled' => 0]);
        $this->response->redirect('/admin/email?tab=autoresponders');
    }

    public function setSpam()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('mail_spam')->insertGetId([
            'domain' => $this->request->post('domain', ''), 'action' => $this->request->post('action', 'move_junk'),
            'threshold' => $this->request->post('threshold', '5.0'),
        ]);
        $_SESSION['success_message'] = 'Spam settings saved.';
        $this->response->redirect('/admin/email?tab=spam');
    }

    public function clearQueue()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec('postsuper -d ALL 2>/dev/null');
        $_SESSION['success_message'] = 'Mail queue cleared.';
        $this->response->redirect('/admin/email?tab=queue');
    }

    public function changePassword($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $acct = $this->db->table('mail_accounts')->where('id', (int)$id)->first();
        $password = $this->request->post('password', '');
        if (!$acct) {
            // Try by SQLite row id if it isn't in the mirror
            $sqlite = $this->mailDb();
            if ($sqlite) {
                $stmt = $sqlite->prepare("SELECT username FROM mailbox WHERE id = ?");
                $stmt->execute([(int)$id]);
                $row = $stmt->fetch(\PDO::FETCH_OBJ);
                if ($row) $acct = (object)['email' => $row->username];
            }
        }
        if ($acct && strlen($password) >= 6) {
            $email = $acct->email;
            // Update the real store (PostfixAdmin SQLite)
            $sqlite = $this->mailDb();
            if ($sqlite) {
                $stmt = $sqlite->prepare("UPDATE mailbox SET password = ?, password_encode = '{BLF-CRYPT}', modified = CURRENT_TIMESTAMP WHERE username = ?");
                $stmt->execute([$this->mailHash($password), $email]);
            }
            // Sync the panel mirror
            $this->syncMailAccount($email, explode('@', $email)[1] ?? '', $password, 1000);
            $_SESSION['success_message'] = "Password changed for {$email} (Dovecot/Postfix).";
        } else {
            $_SESSION['error_message'] = 'Account not found or password too short (min 6 chars).';
        }
        $this->response->redirect('/admin/email?tab=accounts');
    }
}
