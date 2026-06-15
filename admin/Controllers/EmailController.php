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

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $tab = $this->request->get('tab', 'accounts');
        $accounts = $this->db->table('mail_accounts')->get() ?: [];
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
        $full = $email . '@' . $domain;
        if ($email && $domain && $password) {
            $this->db->table('mail_accounts')->insertGetId([
                'email' => $full, 'domain' => $domain,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'quota_mb' => (int)$this->request->post('quota', 1000),
            ]);
            $_SESSION['success_message'] = "Account {$full} created.";
        }
        $this->response->redirect('/admin/email?tab=accounts');
    }

    public function deleteAccount($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('mail_accounts')->where('id', $id)->delete();
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
}
