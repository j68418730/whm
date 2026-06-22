<?php

namespace User\Controllers;

use Core\Controller;

class ChatController extends Controller
{
    protected $request, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    public function start()
    {
        $name = $this->request->post('name', 'Visitor');
        $email = $this->request->post('email', '');
        $sessId = $_COOKIE['PHPSESSID'] ?? session_id();
        $visitor = $this->db->table('chat_visitors')->where('session_id', $sessId)->first();
        $visitorId = $visitor ? $visitor->id : null;
        $sessionId = $this->db->table('chat_sessions')->insertGetId([
            'visitor_id' => $visitorId, 'visitor_name' => $name, 'visitor_email' => $email,
            'status' => 'waiting', 'subject' => $this->request->post('subject', 'Chat'),
        ]);
        $this->db->table('chat_messages')->insertGetId([
            'session_id' => $sessionId, 'sender_type' => 'system',
            'message' => "{$name} has started a chat.",
        ]);
        // Notify all admins about new chat
        $now = date('Y-m-d H:i:s');
        $admins = $this->db->table('admins')->get() ?: [];
        foreach ($admins as $admin) {
            $this->db->table('notifications')->insertGetId([
                'user_id' => $admin->id,
                'type' => 'chat',
                'title' => 'Support Request: ' . $name,
                'message' => 'New Visitor ' . $name . ' (' . ($email ?: 'no email') . ') has requested support.',
                'created_at' => $now,
            ]);
        }
        $this->response->json(['id' => $sessionId]);
        $this->response->send();
        exit;
    }

    public function poll($sessionId)
    {
        $since = (int)$this->request->get('since', 0);
        $msgs = $this->db->table('chat_messages')->where('session_id', $sessionId)->get() ?: [];
        $new = array_filter($msgs, fn($m) => $m->id > $since);
        $session = $this->db->table('chat_sessions')->where('id', $sessionId)->first();
        $this->response->json(['messages' => array_values($new), 'status' => $session->status ?? 'closed']);
        $this->response->send();
        exit;
    }

    public function send()
    {
        $sessionId = (int)$this->request->post('session_id', 0);
        $msg = $this->request->post('message', '');
        $name = $this->request->post('name', 'Visitor');
        if ($sessionId && $msg) {
            $this->db->table('chat_messages')->insertGetId([
                'session_id' => $sessionId, 'sender_type' => 'visitor',
                'sender_name' => $name, 'message' => $msg,
            ]);
        }
        echo 'ok';
        exit;
    }

    public function upload($sessionId)
    {
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $dir = BASE_PATH . '/storage/chat/';
            @mkdir($dir, 0755, true);
            $name = time() . '_' . basename($_FILES['file']['name']);
            move_uploaded_file($_FILES['file']['tmp_name'], $dir . $name);
            $this->db->table('chat_messages')->insertGetId([
                'session_id' => $sessionId, 'sender_type' => 'visitor',
                'sender_name' => 'Visitor', 'message' => 'Sent a file',
                'file_url' => '/storage/chat/' . $name, 'file_name' => $_FILES['file']['name'],
            ]);
            $this->response->json(['url' => '/storage/chat/' . $name]);
        }
        $this->response->send();
        exit;
    }
}
