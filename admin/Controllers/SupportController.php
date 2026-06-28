<?php

namespace Admin\Controllers;

use Core\Controller;

class SupportController extends Controller
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

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
    }

    protected function theme()
    {
        return json_decode($this->auth->user()->theme_settings ?? '{}', true);
    }

    // ── Support Center Hub ──
    public function index()
    {
        $this->guard();
        $user = $this->auth->user();
        $ticketCount = count($this->db->table('tickets')->get() ?: []);
        $openCount = count($this->db->table('tickets')->where('status', 'open')->get() ?: []);
        $articleCount = count($this->db->table('kb_articles')->get() ?: []);
        $announceCount = count($this->db->table('announcements')->where('is_active', 1)->get() ?: []);
        return $this->view('admin.support.index', ['user' => $user, 'title' => 'Support Center',
            'theme_settings' => $this->theme(), 'ticketCount' => $ticketCount, 'openCount' => $openCount,
            'articleCount' => $articleCount, 'announceCount' => $announceCount]);
    }

    // ── Tickets ──
    public function tickets()
    {
        $this->guard();
        $user = $this->auth->user();
        $tickets = $this->db->table('tickets')->get() ?: [];
        return $this->view('admin.support.tickets', ['user' => $user, 'title' => 'Support Tickets', 'theme_settings' => $this->theme(), 'tickets' => $tickets]);
    }

    public function ticketView($id)
    {
        $this->guard();
        $user = $this->auth->user();
        $ticket = $this->db->table('tickets')->where('id', $id)->first();
        $replies = $ticket ? ($this->db->table('ticket_replies')->where('ticket_id', $id)->get() ?: []) : [];
        return $this->view('admin.support.ticket_view', ['user' => $user, 'title' => 'Ticket #'.$id, 'theme_settings' => $this->theme(), 'ticket' => $ticket, 'replies' => $replies]);
    }

    public function ticketReply($id)
    {
        $this->guard();
        $this->db->table('ticket_replies')->insertGetId([
            'ticket_id' => $id, 'admin_id' => $this->auth->user()->id,
            'message' => $this->request->post('message', ''),
        ]);
        $this->db->table('tickets')->where('id', $id)->update(['status' => 'answered', 'updated_at' => date('Y-m-d H:i:s')]);
        $_SESSION['success_message'] = 'Reply posted.';
        $this->response->redirect('/admin/support/tickets/' . $id);
    }

    public function ticketClose($id)
    {
        $this->guard();
        $this->db->table('tickets')->where('id', $id)->update(['status' => 'closed']);
        $this->response->redirect('/admin/support/tickets');
    }

    public function ticketDelete($id)
    {
        $this->guard();
        $this->db->table('ticket_replies')->where('ticket_id', $id)->delete();
        $this->db->table('tickets')->where('id', $id)->delete();
        $_SESSION['success_message'] = 'Ticket deleted.';
        $this->response->redirect('/admin/support/tickets');
    }

    // ── Knowledgebase ──
    public function kb()
    {
        $this->guard();
        $user = $this->auth->user();
        $articles = $this->db->table('kb_articles')->get() ?: [];
        $cats = $this->db->table('kb_categories')->get() ?: [];
        $catNames = [];
        foreach ($cats as $c) $catNames[$c->id] = $c->name;
        return $this->view('admin.support.kb', ['user' => $user, 'title' => 'Knowledgebase', 'theme_settings' => $this->theme(), 'articles' => $articles, 'cats' => $cats, 'catNames' => $catNames]);
    }

    public function kbCategoryStore()
    {
        $this->guard();
        $name = $this->request->post('name', '');
        $this->db->table('kb_categories')->insertGetId(['name' => $name, 'slug' => strtolower(preg_replace('/[^a-z0-9]+/', '-', $name)), 'description' => $this->request->post('description', '')]);
        $_SESSION['success_message'] = 'Category created.';
        $this->response->redirect('/admin/support/kb');
    }

    public function kbCategoryDelete($id)
    {
        $this->guard();
        $this->db->table('kb_categories')->where('id', $id)->delete();
        $this->response->redirect('/admin/support/kb');
    }

    public function kbCategoryUpdate($id)
    {
        $this->guard();
        $this->db->table('kb_categories')->where('id', $id)->update([
            'name' => $this->request->post('name', ''),
            'slug' => strtolower(preg_replace('/[^a-z0-9]+/', '-', $this->request->post('name', ''))),
            'description' => $this->request->post('description', ''),
        ]);
        $_SESSION['success_message'] = 'Category updated.';
        $this->response->redirect('/admin/support/kb');
    }

    public function kbArticleStore()
    {
        $this->guard();
        $title = $this->request->post('title', '');
        $this->db->table('kb_articles')->insertGetId([
            'category_id' => (int)$this->request->post('category_id', 0) ?: null,
            'title' => $title, 'slug' => strtolower(preg_replace('/[^a-z0-9]+/', '-', $title)) . '-' . time(),
            'content' => $this->request->post('content', ''), 'is_published' => $this->request->post('is_published', 1),
        ]);
        $_SESSION['success_message'] = 'Article created.';
        $this->response->redirect('/admin/support/kb');
    }

    public function kbArticleDelete($id)
    {
        $this->guard();
        $this->db->table('kb_articles')->where('id', $id)->delete();
        $this->response->redirect('/admin/support/kb');
    }

    // ── Announcements ──
    public function announcements()
    {
        $this->guard();
        $user = $this->auth->user();
        $announcements = $this->db->table('announcements')->get() ?: [];
        return $this->view('admin.support.announcements', ['user' => $user, 'title' => 'Announcements', 'theme_settings' => $this->theme(), 'announcements' => $announcements]);
    }

    public function announcementStore()
    {
        $this->guard();
        $this->db->table('announcements')->insertGetId([
            'title' => $this->request->post('title', ''), 'content' => $this->request->post('content', ''),
            'type' => $this->request->post('type', 'info'), 'is_active' => $this->request->post('is_active', 1),
        ]);
        $_SESSION['success_message'] = 'Announcement created.';
        $this->response->redirect('/admin/support/announcements');
    }

    public function announcementDelete($id)
    {
        $this->guard();
        $this->db->table('announcements')->where('id', $id)->delete();
        $this->response->redirect('/admin/support/announcements');
    }

    public function announcementUpdate($id)
    {
        $this->guard();
        $this->db->table('announcements')->where('id', $id)->update([
            'title' => $this->request->post('title', ''),
            'content' => $this->request->post('content', ''),
            'type' => $this->request->post('type', 'info'),
            'is_active' => (int)$this->request->post('is_active', 0),
        ]);
        $_SESSION['success_message'] = 'Announcement updated.';
        $this->response->redirect('/admin/support/announcements');
    }

    // ── Server Status ──
    public function serverStatus()
    {
        $this->guard();
        $user = $this->auth->user();
        $services = [
            ['name' => 'Apache', 'service' => 'apache2'],
            ['name' => 'MariaDB', 'service' => 'mariadb'],
            ['name' => 'Postfix', 'service' => 'postfix'],
            ['name' => 'Dovecot', 'service' => 'dovecot'],
            ['name' => 'VSFTPD', 'service' => 'vsftpd'],
            ['name' => 'Bind9', 'service' => 'bind9'],
            ['name' => 'Icecast', 'service' => 'icecast2'],
            ['name' => 'SSH', 'service' => 'ssh'],
            ['name' => 'Docker', 'service' => 'docker'],
        ];
        foreach ($services as &$s) {
            $status = trim(shell_exec("systemctl is-active {$s['service']} 2>/dev/null") ?: 'unknown');
            $s['status'] = $status === 'active' ? 'running' : 'stopped';
            $s['uptime'] = '';
            if ($status === 'active') {
                $uptime = trim(shell_exec("systemctl show {$s['service']} --property=ActiveEnterTimestamp 2>/dev/null") ?: '');
                $s['uptime'] = str_replace('ActiveEnterTimestamp=', '', $uptime);
            }
        }
        return $this->view('admin.support.server_status', ['user' => $user, 'title' => 'Server Status', 'theme_settings' => $this->theme(), 'services' => $services]);
    }
}
