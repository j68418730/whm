<?php

namespace User\Controllers;

use Core\Controller;

class EmailController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;
    protected $hostingUser;
    protected $domain;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function requireUser()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $user = $this->auth->user();
        $this->hostingUser = $this->db->table('hosting_users')->where('email', $user->email)->first();
        $this->domain = $this->hostingUser->domain ?? 'planet-hosts.com';
        return $user;
    }

    public function index()
    {
        $this->requireUser();
        $accounts = $this->db->table('mail_accounts')->where('domain', $this->domain)->get() ?: [];
        $forwarders = $this->db->table('mail_forwarders')->where('domain', $this->domain)->get() ?: [];
        $autoresponders = $this->db->table('mail_autoresponder')->where('domain', $this->domain)->get() ?: [];
        $spam = $this->db->table('mail_spam')->where('domain', $this->domain)->get() ?: [];
        return $this->view('user.email', [
            'user' => $this->auth->user(), 'hosting' => $this->hostingUser,
            'domain' => $this->domain,
            'accounts' => $accounts, 'forwarders' => $forwarders,
            'autoresponders' => $autoresponders, 'spam' => $spam,
            'webmailUrl' => 'http://planet-hosts.com:2096/',
            'title' => 'Email'
        ]);
    }

    public function createAccount()
    {
        $this->requireUser();
        $email = $this->request->post('email', '');
        $password = $this->request->post('password', '');
        $fullEmail = $email . '@' . $this->domain;
        if ($email && $password) {
            $this->db->table('mail_accounts')->insertGetId([
                'email' => $fullEmail, 'domain' => $this->domain,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'password_plain' => $password,
                'quota_mb' => (int)$this->request->post('quota', 1000),
            ]);
            // Create Linux user for IMAP auth
            $safeEmail = escapeshellarg($email);
            exec("useradd -m -d /home/{$safeEmail} -s /sbin/nologin {$safeEmail} 2>/dev/null");
            exec("echo {$safeEmail}:" . escapeshellarg($password) . " | chpasswd 2>/dev/null");
            $_SESSION['success'] = "Email account {$fullEmail} created.";
        }
        $this->response->redirect('/user/email');
        exit;
    }

    public function deleteAccount($id)
    {
        $this->requireUser();
        $acct = $this->db->table('mail_accounts')->where('id', $id)->first();
        if ($acct) {
            $local = explode('@', $acct->email)[0];
            exec("userdel -r " . escapeshellarg($local) . " 2>/dev/null");
            $this->db->table('mail_accounts')->where('id', $id)->delete();
        }
        $this->response->redirect('/user/email');
        exit;
    }

    public function createForwarder()
    {
        $this->requireUser();
        $this->db->table('mail_forwarders')->insertGetId([
            'from_email' => $this->request->post('from', '') . '@' . $this->domain,
            'to_email' => $this->request->post('to', ''),
            'domain' => $this->domain,
        ]);
        $_SESSION['success'] = 'Forwarder created.';
        $this->response->redirect('/user/email');
        exit;
    }

    public function deleteForwarder($id)
    {
        $this->requireUser();
        $this->db->table('mail_forwarders')->where('id', $id)->delete();
        $this->response->redirect('/user/email');
        exit;
    }

    public function setAutoresponder()
    {
        $this->requireUser();
        $email = $this->request->post('email', '') . '@' . $this->domain;
        $existing = $this->db->table('mail_autoresponder')->where('email', $email)->first();
        $data = ['email' => $email, 'domain' => $this->domain, 'subject' => $this->request->post('subject', ''), 'message' => $this->request->post('message', ''), 'enabled' => 1];
        if ($existing) {
            $this->db->table('mail_autoresponder')->where('id', $existing->id)->update($data);
        } else {
            $this->db->table('mail_autoresponder')->insertGetId($data);
        }
        $_SESSION['success'] = 'Autoresponder set.';
        $this->response->redirect('/user/email');
        exit;
    }

    public function disableAutoresponder($id)
    {
        $this->requireUser();
        $this->db->table('mail_autoresponder')->where('id', $id)->update(['enabled' => 0]);
        $this->response->redirect('/user/email');
        exit;
    }

    public function setSpam()
    {
        $this->requireUser();
        $this->db->table('mail_spam')->insertGetId([
            'domain' => $this->domain, 'action' => $this->request->post('action', 'move_junk'),
            'threshold' => $this->request->post('threshold', '5.0'),
        ]);
        $_SESSION['success'] = 'Spam settings updated.';
        $this->response->redirect('/user/email');
        exit;
    }

    public function changePassword($id)
    {
        $this->requireUser();
        $pw = $_POST['password'] ?? '';
        if (strlen($pw) >= 6) {
            try {
                $acct = $this->db->table('mail_accounts')->where('id', $id)->where('domain', $this->domain)->first();
                if ($acct) {
                    $this->db->table('mail_accounts')->where('id', $id)->update([
                        'password_hash' => password_hash($pw, PASSWORD_DEFAULT),
                        'password_plain' => $pw,
                    ]);
                    $local = explode('@', $acct->email)[0];
                    exec("echo " . escapeshellarg($local) . ":" . escapeshellarg($pw) . " | chpasswd 2>/dev/null");
                }
                $_SESSION['success'] = 'Email password changed.';
            } catch (\Exception $e) { $_SESSION['error'] = 'Failed to change password.'; }
        } else { $_SESSION['error'] = 'Password too short.'; }
        $this->response->redirect('/user/email');
        exit;
    }
}

