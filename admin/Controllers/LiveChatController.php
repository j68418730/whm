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

    public function portal()
    {
        if (!$this->auth->check()) { $this->response->redirect('/admin/login'); exit; }
        $isAdmin = $this->auth->isAdmin();
        if (!$isAdmin) { $this->response->redirect('/user/dashboard'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.livechat.portal', [
            'user' => $user, 'title' => 'Live Chat',
        ]);
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
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error'=>'Unauthorized']); $this->response->send(); exit; }
        $since = (int)$this->request->get('since', 0);
        $all = $this->db->table('chat_messages')->where('session_id', $sessionId)->orderBy('id', 'ASC')->get() ?: [];
        // Deduplicate by id and only return new ones
        $seen = [];
        $msgs = [];
        foreach ($all as $m) {
            if (isset($seen[$m->id])) continue;
            $seen[$m->id] = true;
            if ($m->id > $since) $msgs[] = $m;
        }
        $this->response->json($msgs);
        $this->response->send();
        exit;
    }

    public function send()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error'=>'Unauthorized']); $this->response->send(); exit; }
        $sessionId = (int)$this->request->post('session_id', 0);
        $msg = $this->request->post('message', '');
        $id = 0;
        if ($sessionId && $msg) {
            $id = $this->db->table('chat_messages')->insertGetId([
                'session_id' => $sessionId, 'sender_type' => 'operator',
                'sender_name' => $this->auth->user()->name ?? 'Staff',
                'message' => $msg, 'created_at' => date('Y-m-d H:i:s'),
            ]);
            $this->db->table('chat_sessions')->where('id', $sessionId)->update(['status' => 'active']);
        }
        $this->response->json(['ok'=>true, 'id'=>$id]);
        $this->response->send();
        exit;
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

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $pdo = $this->db->pdo();
        // Get message IDs for this session to delete attachments
        $msgIds = $pdo->query("SELECT id FROM chat_messages WHERE session_id=" . (int)$id)->fetchAll(\PDO::FETCH_COLUMN);
        if (!empty($msgIds)) {
            $ids = implode(',', array_map('intval', $msgIds));
            $pdo->exec("DELETE FROM chat_attachments WHERE message_id IN ($ids)");
        }
        $pdo->exec("DELETE FROM chat_ratings WHERE chat_id=" . (int)$id);
        $pdo->exec("DELETE FROM chat_messages WHERE session_id=" . (int)$id);
        $pdo->exec("DELETE FROM chat_sessions WHERE id=" . (int)$id);
        $_SESSION['success_message'] = "Chat #{$id} deleted.";
        $this->response->redirect('/admin/livechat');
    }

    public function groupDelete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('chat_operator_groups')->where('id', $id)->delete();
        $this->response->redirect('/admin/livechat');
    }

    // Visitor tracking AJAX
    public function visitorsOnline()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json([]); $this->response->send(); exit; }
        $cutoff = date('Y-m-d H:i:s', time() - 120);
        $visitors = $this->db->table('chat_visitors')->where('last_seen', '>', $cutoff)->get() ?: [];
        // Deduplicate by session_id, keep the most recent entry
        $deduped = [];
        $seen = [];
        foreach ($visitors as $v) {
            if (!isset($seen[$v->session_id])) {
                $seen[$v->session_id] = true;
                $deduped[] = $v;
            }
        }
        $this->response->json($deduped);
        $this->response->send();
        exit;
    }

    public function track()
    {
        $sessId = $_COOKIE['PHPSESSID'] ?? session_id();
        $page = $_POST['page'] ?? '';
        $tz = $_POST['tz'] ?? '';
        $res = $_POST['res'] ?? '';
        $lang = $_POST['lang'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        // Detect browser & OS from UA
        $browser = 'Unknown';
        if (strpos($ua, 'Edg') !== false) $browser='Edge';
        elseif (strpos($ua, 'Chrome') !== false) $browser='Chrome';
        elseif (strpos($ua, 'Firefox') !== false) $browser='Firefox';
        elseif (strpos($ua, 'Safari') !== false) $browser='Safari';
        $os = 'Unknown';
        if (strpos($ua, 'Windows') !== false) $os='Windows';
        elseif (strpos($ua, 'Mac') !== false) $os='MacOS';
        elseif (strpos($ua, 'Linux') !== false) $os='Linux';
        elseif (strpos($ua, 'Android') !== false) $os='Android';
        elseif (strpos($ua, 'iPhone') !== false || strpos($ua, 'iPad') !== false) $os='iOS';

        $existing = $this->db->table('chat_visitors')->where('session_id', $sessId)->first();
        if ($existing) {
            $history = $existing->page_history ? json_decode($existing->page_history, true) : [];
            $history[] = ['page' => $page, 'time' => date('Y-m-d H:i:s')];
            $history = array_slice($history, -20);
            $this->db->table('chat_visitors')->where('id', $existing->id)->update([
                'current_page' => $page, 'page_history' => json_encode($history),
                'time_on_site' => time() - strtotime($existing->first_seen),
                'browser' => $browser, 'os' => $os, 'last_seen' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->db->table('chat_visitors')->insertGetId([
                'session_id' => $sessId, 'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'current_page' => $page, 'timezone' => $tz, 'language' => $lang,
                'browser' => $browser, 'os' => $os,
                'page_history' => json_encode([['page' => $page, 'time' => date('Y-m-d H:i:s')]]),
                'last_seen' => date('Y-m-d H:i:s'), 'first_seen' => date('Y-m-d H:i:s'),
            ]);
            // Notify admins about new visitor
            $now = date('Y-m-d H:i:s');
            $admins = $this->db->table('admins')->get() ?: [];
            foreach ($admins as $admin) {
                $existingNotif = $this->db->table('notifications')
                    ->where('user_id', $admin->id)
                    ->where('type', 'visitor')
                    ->where('created_at', '>=', date('Y-m-d H:') . '00:00')
                    ->first();
                if (!$existingNotif) {
                    $this->db->table('notifications')->insertGetId([
                        'user_id' => $admin->id,
                        'type' => 'visitor',
                        'title' => 'New Visitor on Site',
                        'message' => 'A new visitor is browsing ' . $page . ' (' . $browser . ', ' . $os . ')',
                        'created_at' => $now,
                    ]);
                }
            }
        }
        echo 'ok';
        exit;
    }

    public function waitingCount()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->json(['waiting' => 0]);
            $this->response->send();
            exit;
        }
        $pdo = $this->db->pdo();
        $count = $pdo->query("SELECT COUNT(*) FROM chat_sessions WHERE status='waiting'")->fetchColumn();
        $this->response->json(['waiting' => (int)$count]);
        $this->response->send();
        exit;
    }
}
