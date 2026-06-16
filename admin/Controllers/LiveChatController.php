<?php

namespace Admin\Controllers;

use Core\Controller;

class LiveChatController extends Controller
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
        $sessions = $this->db->table('chat_sessions')->get() ?: [];
        $waiting = array_filter($sessions, fn($s) => $s->status === 'waiting');
        $active = array_filter($sessions, fn($s) => $s->status === 'active');
        $visitors = $this->db->table('chat_visitors')->get() ?: [];
        $canned = $this->db->table('chat_canned_responses')->get() ?: [];
        $groups = $this->db->table('chat_operator_groups')->get() ?: [];
        return $this->view('admin.livechat.index', [
            'user' => $user, 'title' => 'Live Chat', 'sessions' => $sessions,
            'waiting' => $waiting, 'active' => $active, 'visitors' => $visitors,
            'canned' => $canned, 'groups' => $groups,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function messages($sessionId)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error'=>'Unauthorized']); exit; }
        $msgs = $this->db->table('chat_messages')->where('session_id', $sessionId)->get() ?: [];
        $this->response->json($msgs);
        $this->response->send();
        exit;
    }

    public function send()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $sessionId = (int)$this->request->post('session_id', 0);
        $msg = $this->request->post('message', '');
        if ($sessionId && $msg) {
            $this->db->table('chat_messages')->insertGetId([
                'session_id' => $sessionId, 'sender_type' => 'operator',
                'sender_name' => $this->auth->user()->name ?? 'Staff',
                'message' => $msg,
            ]);
            $this->db->table('chat_sessions')->where('id', $sessionId)->update(['status' => 'active']);
        }
        $this->response->redirect('/admin/livechat');
    }

    public function transfer($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $groupId = (int)$this->request->post('group_id', 0);
        $this->db->table('chat_sessions')->where('id', $id)->update(['department' => $groupId ? "Group #{$groupId}" : 'General']);
        $_SESSION['success_message'] = 'Chat transferred.';
        $this->response->redirect('/admin/livechat');
    }

    public function close($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $msgs = $this->db->table('chat_messages')->where('session_id', $id)->get() ?: [];
        $transcript = '';
        foreach ($msgs as $m) $transcript .= "[{$m->created_at}] {$m->sender_name}: {$m->message}\n";
        $this->db->table('chat_transcripts')->insertGetId(['session_id' => $id, 'transcript' => $transcript]);
        $this->db->table('chat_sessions')->where('id', $id)->update(['status' => 'closed', 'closed_at' => date('Y-m-d H:i:s')]);
        $this->response->redirect('/admin/livechat');
    }

    // Canned responses
    public function cannedStore()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('chat_canned_responses')->insertGetId([
            'title' => $this->request->post('title', ''), 'message' => $this->request->post('message', ''),
            'category' => $this->request->post('category', 'General'),
        ]);
        $this->response->redirect('/admin/livechat');
    }

    public function cannedDelete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('chat_canned_responses')->where('id', $id)->delete();
        $this->response->redirect('/admin/livechat');
    }

    // Groups
    public function groupStore()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('chat_operator_groups')->insertGetId([
            'name' => $this->request->post('name', ''), 'department' => $this->request->post('department', ''),
        ]);
        $this->response->redirect('/admin/livechat');
    }

    public function groupDelete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('chat_operator_groups')->where('id', $id)->delete();
        $this->response->redirect('/admin/livechat');
    }

    // Visitor tracking AJAX
    public function track()
    {
        $sessId = $_COOKIE['PHPSESSID'] ?? session_id();
        $page = $_POST['page'] ?? '';
        $existing = $this->db->table('chat_visitors')->where('session_id', $sessId)->first();
        if ($existing) {
            $history = $existing->page_history ? json_decode($existing->page_history, true) : [];
            $history[] = ['page' => $page, 'time' => date('Y-m-d H:i:s')];
            $history = array_slice($history, -20);
            $this->db->table('chat_visitors')->where('id', $existing->id)->update([
                'current_page' => $page, 'page_history' => json_encode($history), 'time_on_site' => time() - strtotime($existing->first_seen),
            ]);
        } else {
            $this->db->table('chat_visitors')->insertGetId([
                'session_id' => $sessId, 'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'current_page' => $page, 'page_history' => json_encode([['page' => $page, 'time' => date('Y-m-d H:i:s')]]),
            ]);
        }
        echo 'ok';
        exit;
    }
}
