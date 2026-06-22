<?php

namespace Admin\Controllers;

use Core\Controller;

class NotificationsController extends Controller
{
    protected $auth, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    public function markRead($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $this->db->table('notifications')->where('id', (int)$id)->update(['read_at' => date('Y-m-d H:i:s')]);
        $this->response->json(['ok' => true]);
        $this->response->send();
        exit;
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $this->db->table('notifications')->where('id', (int)$id)->delete();
        $this->response->json(['ok' => true]);
        $this->response->send();
        exit;
    }

    public function markAllRead()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $this->db->pdo()->exec("UPDATE notifications SET read_at = NOW() WHERE read_at IS NULL");
        $this->response->json(['ok' => true]);
        $this->response->send();
        exit;
    }

    public function apiLatest()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $since = $_GET['since'] ?? date('Y-m-d H:i:s', strtotime('-1 hour'));
        $rows = $this->db->table('notifications')->where('created_at', '>=', $since)->orderBy('created_at', 'DESC')->limit(20)->get() ?: [];
        $stmt = $this->db->pdo()->query("SELECT COUNT(*) as c FROM notifications WHERE read_at IS NULL");
        $unread = $stmt->fetch()->c ?? 0;
        $this->response->json(['notifications' => $rows, 'unread' => (int)$unread]);
        $this->response->send();
        exit;
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();

        $settings = [];
        try {
            $rows = $this->db->table('automation_settings')->get() ?: [];
            foreach ($rows as $row) {
                $settings[$row->setting_key] = $row->setting_value;
            }
        } catch (\Throwable $e) {
            $settings = [];
        }

        $notifications = [];
        try {
            $notifications = $this->db->table('notifications')->orderBy('created_at', 'DESC')->limit(50)->get() ?: [];
        } catch (\Throwable $e) {
            $notifications = [];
        }

        $unread = count(array_filter($notifications, fn($row) => empty($row->read_at)));

        return $this->view('admin.notifications.index', [
            'user' => $user,
            'title' => 'Notifications',
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
            'settings' => $settings,
            'notifications' => $notifications,
            'unread' => $unread,
        ]);
    }
}
