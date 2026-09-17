<?php
namespace Admin\Controllers;

use Core\Controller;

class ChatDashboardController extends Controller
{
    protected $auth, $db, $response, $request;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->db = $app->get('db');
        $this->response = $app->get('response');
        $this->request = $app->get('request');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            return;
        }

        $pdo = $this->db->pdo();
        $tenants = [];
        try {
            $stmt = $pdo->query("SELECT t.id, t.name, t.widget_title, t.created_at, t.is_active,
                hu.username AS owner_username, hu.email AS owner_email,
                (SELECT COUNT(*) FROM chatbox_rooms cr WHERE cr.tenant_id = t.id) AS room_count,
                (SELECT COUNT(*) FROM chatbox_users cu WHERE cu.tenant_id = t.id) AS user_count,
                (SELECT COUNT(*) FROM chatbox_users co WHERE co.tenant_id = t.id AND co.last_active > DATE_SUB(NOW(), INTERVAL 5 MINUTE)) AS online,
                t.voice_enabled, t.custom_css
                FROM chatbox_tenants t LEFT JOIN hosting_users hu ON hu.id = t.hosting_user_id
                ORDER BY t.id");
            $tenants = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Chatbox tables not available yet: ' . htmlspecialchars($e->getMessage());
        }

        $usersByTenant = [];
        $roomsByTenant = [];
        if (count($tenants)) {
            $ids = array_map('intval', array_column($tenants, 'id'));
            $in = implode(',', $ids);
            try {
                $u = $pdo->query("SELECT * FROM chatbox_users WHERE tenant_id IN ($in) ORDER BY tenant_id, role, username");
                foreach ($u as $row) $usersByTenant[(int)$row->tenant_id][] = $row;
                $r = $pdo->query("SELECT id, tenant_id, name, type, is_active FROM chatbox_rooms WHERE tenant_id IN ($in) ORDER BY tenant_id, sort_order");
                foreach ($r as $row) $roomsByTenant[(int)$row->tenant_id][] = $row;
            } catch (\Exception $e) {
            }
        }

        $emojiCodes = ['smile','laugh','joy','sweat_smile','sad','cry','angry','heart_eyes','flushed','smiling_imp','neutral','expressionless','glasses','rotating_light','partying_face','triumph','wink','stuck_out_tongue','stuck_out_tongue_winking_eye','kissing','yum','relieved','pensive','sleeping','worried','frowning','anguished','open_mouth','hushed','confused','fear','cold_sweat','cry','sleepy'];

        return $this->view('admin.chat_dashboard.index', [
            'user' => $this->auth->user(),
            'tenants' => $tenants,
            'usersByTenant' => $usersByTenant,
            'roomsByTenant' => $roomsByTenant,
            'title' => 'Chat Box Dashboard',
            'emojiCodes' => $emojiCodes,
        ]);
    }

    public function createTenant()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            return;
        }

        if (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $widget_title = trim($_POST['widget_title'] ?? '');
            if (!$name) {
                $_SESSION['error_message'] = 'Chatbox name is required';
                $this->response->redirect('/admin/chat-dashboard');
                return;
            }
            $pdo = $this->db->pdo();
            $stmt = $pdo->prepare("INSERT INTO chatbox_tenants (hosting_user_id, name, widget_title, voice_enabled, custom_css, is_active) VALUES (?, ?, ?, 0, 'default', 1)");
            $hostingUserId = (int)($_SESSION['user']->id ?? 1);
            $stmt->execute([$hostingUserId, $name, $widget_title]);
            $_SESSION['success_message'] = 'Chatbox created successfully';
            $this->response->redirect('/admin/chat-dashboard');
            return;
        }

        return $this->view('admin.chat_dashboard.create', [
            'user' => $this->auth->user(),
            'title' => 'Create Chatbox',
        ]);
    }

    public function deleteTenant()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            return;
        }

        if (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tenant_id'])) {
            $tenantId = (int)$_POST['tenant_id'];
            $pdo = $this->db->pdo();
            $pdo->prepare("DELETE FROM chatbox_tenants WHERE id = ?")->execute([$tenantId]);
            $_SESSION['success_message'] = 'Chatbox deleted successfully';
            $this->response->redirect('/admin/chat-dashboard');
            return;
        }

        $this->response->redirect('/admin/chat-dashboard');
        return;
    }

    public function toggleTenant()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            return;
        }

        if (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tenant_id'], $_POST['action'])) {
            $tenantId = (int)$_POST['tenant_id'];
            $action = $_POST['action'];
            $pdo = $this->db->pdo();
            $pdo->prepare("UPDATE chatbox_tenants SET is_active = CASE WHEN is_active = 0 THEN 1 ELSE 0 END WHERE id = ?")->execute([$tenantId]);
            $_SESSION['success_message'] = 'Chatbox ' . ($action === 'suspend' ? 'suspended' : 'activated');
            $this->response->redirect('/admin/chat-dashboard');
            return;
        }

        $this->response->redirect('/admin/chat-dashboard');
        return;
    }

    public function manageTenant($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            return;
        }

        $pdo = $this->db->pdo();
        $tenantId = (int)$id;

        $tenant = $pdo->prepare("SELECT t.*, hu.username AS owner_username, hu.email AS owner_email FROM chatbox_tenants t LEFT JOIN hosting_users hu ON hu.id = t.hosting_user_id WHERE t.id = ?");
        $tenant->execute([$tenantId]);
        $tenant = $tenant->fetch(\PDO::FETCH_ASSOC);
        if (!$tenant) {
            $_SESSION['error_message'] = 'Chatbox not found.';
            $this->response->redirect('/admin/chat-dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'update_settings') {
                $stmt = $pdo->prepare("UPDATE chatbox_tenants SET widget_title=?, widget_color=?, widget_bg=?, widget_text_color=?, font_family=?, player_html=?, guest_enabled=?, registration_enabled=?, voice_enabled=? WHERE id=?");
                $stmt->execute([$_POST['title']??'', $_POST['color']??'#008cff', $_POST['bg']??'#0a0e1a', $_POST['text_color']??'#ffffff', $_POST['font']??'Inter, sans-serif', $_POST['player_html']??'', (int)($_POST['guest']??0), (int)($_POST['reg']??0), (int)($_POST['voice']??0), $tenantId]);
                $_SESSION['success_message'] = 'Settings saved.';
            }
            if ($_POST['action'] === 'add_room') {
                $pass = $_POST['type'] === 'password' ? password_hash($_POST['password']??'', PASSWORD_DEFAULT) : null;
                $pdo->prepare("INSERT INTO chatbox_rooms (tenant_id, name, type, password) VALUES (?, ?, ?, ?)")->execute([$tenantId, $_POST['name']??'', $_POST['type']??'public', $pass]);
                $_SESSION['success_message'] = 'Room added.';
            }
            if ($_POST['action'] === 'delete_room') {
                $pdo->prepare("DELETE FROM chatbox_rooms WHERE id = ? AND tenant_id = ?")->execute([(int)$_POST['room_id'], $tenantId]);
                $_SESSION['success_message'] = 'Room deleted.';
            }
            if ($_POST['action'] === 'add_user') {
                $pdo->prepare("INSERT INTO chatbox_users (tenant_id, username, password_hash, display_name, role, email) VALUES (?, ?, ?, ?, ?, ?)")->execute([$tenantId, $_POST['username']??'', password_hash($_POST['password']??'', PASSWORD_DEFAULT), $_POST['display_name']??'', $_POST['role']??'member', $_POST['email']??'']);
                $_SESSION['success_message'] = 'User added.';
            }
            if ($_POST['action'] === 'delete_user') {
                $pdo->prepare("DELETE FROM chatbox_users WHERE id = ? AND tenant_id = ?")->execute([(int)$_POST['user_id'], $tenantId]);
                $_SESSION['success_message'] = 'User deleted.';
            }
            if ($_POST['action'] === 'ban_user') {
                $pdo->prepare("UPDATE chatbox_users SET is_banned = 1 WHERE id = ? AND tenant_id = ?")->execute([(int)$_POST['user_id'], $tenantId]);
                $pdo->prepare("INSERT INTO chatbox_bans (tenant_id, user_id, reason) VALUES (?, ?, ?)")->execute([$tenantId, (int)$_POST['user_id'], $_POST['reason']??'']);
                $_SESSION['success_message'] = 'User banned.';
            }
            $this->response->redirect('/admin/chat-dashboard/manage/' . $tenantId);
            return;
        }

        $users = $pdo->prepare("SELECT * FROM chatbox_users WHERE tenant_id = ? ORDER BY role, username");
        $users->execute([$tenantId]);
        $usersList = $users->fetchAll(\PDO::FETCH_ASSOC);

        $rooms = $pdo->prepare("SELECT * FROM chatbox_rooms WHERE tenant_id = ? ORDER BY sort_order");
        $rooms->execute([$tenantId]);
        $roomsList = $rooms->fetchAll(\PDO::FETCH_ASSOC);

        $bans = $pdo->prepare("SELECT b.*, u.username as uname FROM chatbox_bans b LEFT JOIN chatbox_users u ON b.user_id = u.id WHERE b.tenant_id = ? ORDER BY b.created_at DESC LIMIT 20");
        $bans->execute([$tenantId]);
        $bansList = $bans->fetchAll(\PDO::FETCH_ASSOC);

        return $this->view('admin.chat_dashboard.manage', [
            'user' => $this->auth->user(),
            'tenant' => $tenant,
            'users' => $usersList,
            'rooms' => $roomsList,
            'bans' => $bansList,
            'title' => 'Manage: ' . ($tenant['widget_title'] ?: $tenant['name']),
        ]);
    }
}
