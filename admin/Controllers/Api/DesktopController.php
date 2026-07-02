<?php

namespace Admin\Controllers\Api;

use Core\Controller;

class DesktopController extends Controller
{
    protected $auth, $request, $response, $db;
    protected $currentApiKey;

    public function __construct()
    {
        parent::__construct();
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function skipCsrf() { return true; }

    protected function json($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function pdo()
    {
        return $this->db->pdo();
    }

    protected function apiKeyAuth()
    {
        $rawKey = $this->request->header('X-API-Key', '');
        if (empty($rawKey)) {
            $this->json(['success' => false, 'error' => 'Missing X-API-Key header'], 401);
        }
        $hash = hash('sha256', $rawKey);
        $key = $this->db->table('api_keys')->where('key_hash', $hash)->where('is_active', '=', 1)->first();
        if (!$key) {
            $this->json(['success' => false, 'error' => 'Invalid or inactive API key'], 403);
        }
        $this->currentApiKey = $key;
        return $key;
    }

    protected function logApiCall($endpoint)
    {
        try {
            $this->db->table('api_log')->insertGetId([
                'api_key_id' => $this->currentApiKey->id ?? 0,
                'endpoint' => $endpoint,
                'method' => $this->request->method(),
                'ip' => $this->request->ip(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {}
    }

    protected function getJsonInput()
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return $data ?: $_POST;
    }

    // ─────────────── Auth ───────────────

    public function verify()
    {
        $key = $this->apiKeyAuth();
        $name = $key->name;
        $role = 'api';
        if (!empty($key->user_id) && $key->user_type === 'admin') {
            $admin = $this->db->table('admins')->where('id', (int)$key->user_id)->first();
            if ($admin) { $name = $admin->username; $role = $admin->role ?? 'admin'; }
        } elseif (!empty($key->user_id) && $key->user_type === 'hosting') {
            $u = $this->db->table('hosting_users')->where('id', (int)$key->user_id)->first();
            if ($u) { $name = $u->username; $role = 'user'; }
        }
        $this->logApiCall('/api/auth/verify');
        $this->json([
            'success' => true,
            'data' => [
                'operator_id' => (int)($key->user_id ?? 0),
                'operator_name' => $name,
                'role' => $role,
                'permissions' => $key->permissions ?? 'read',
                'token' => $this->request->header('X-API-Key'),
            ]
        ]);
    }

    public function login()
    {
        $input = $this->getJsonInput();
        $email = $input['email'] ?? $this->request->post('email', '');
        $password = $input['password'] ?? $this->request->post('password', '');
        $username = $input['username'] ?? $this->request->post('username', '');

        $user = null;
        if (!empty($email) && !empty($password)) {
            $user = $this->db->table('admins')->where('email', $email)->first();
        } elseif (!empty($username) && !empty($password)) {
            $user = $this->db->table('admins')->where('username', $username)->first();
            if (!$user) $user = $this->db->table('hosting_users')->where('username', $username)->first();
        } else {
            $this->json(['success' => false, 'error' => 'Email/username and password required'], 400);
        }

        if (!$user || !password_verify($password, $user->password ?? '')) {
            $this->json(['success' => false, 'error' => 'Invalid credentials'], 401);
        }

        $raw = 'ph_' . bin2hex(random_bytes(16));
        $this->db->table('api_keys')->insertGetId([
            'name' => 'Desktop Session - ' . ($user->username ?? ''),
            'key_hash' => hash('sha256', $raw),
            'permissions' => 'admin',
            'rate_limit' => 120,
            'is_active' => 1,
            'user_id' => (int)$user->id,
            'user_type' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->json([
            'success' => true,
            'data' => [
                'operator_id' => (int)$user->id,
                'operator_name' => $user->username ?? $user->email ?? '',
                'role' => $user->role ?? 'admin',
                'token' => $raw,
            ]
        ]);
    }

    // ─────────────── Dashboard ───────────────

    public function dashboard()
    {
        $this->apiKeyAuth();
        $pdo = $this->pdo();
        $activeChats = $pdo->query("SELECT COUNT(*) FROM chat_sessions WHERE status='active'")->fetchColumn();
        $waitingChats = $pdo->query("SELECT COUNT(*) FROM chat_sessions WHERE status='waiting'")->fetchColumn();
        $openTickets = $pdo->query("SELECT COUNT(*) FROM tickets WHERE status='open'")->fetchColumn();
        $cutoff = date('Y-m-d H:i:s', time() - 300);
        $stm = $pdo->prepare("SELECT COUNT(*) FROM chat_visitors WHERE last_seen >= ?");
        $stm->execute([$cutoff]);
        $visitorsOnline = $stm->fetchColumn();
        $this->logApiCall('/api/dashboard');
        $this->json([
            'success' => true,
            'data' => [
                'active_chats' => (int)$activeChats,
                'waiting_chats' => (int)$waitingChats,
                'open_tickets' => (int)$openTickets,
                'visitors_online' => (int)$visitorsOnline,
            ]
        ]);
    }

    // ─────────────── Search ───────────────

    public function search()
    {
        $this->apiKeyAuth();
        $q = $this->request->query('q', '');
        if (empty($q)) $this->json(['success' => true, 'data' => []]);
        $pdo = $this->pdo();
        $like = "%{$q}%";
        $results = [];

        $stm = $pdo->prepare("SELECT id, username, email, domain, status FROM hosting_users WHERE username LIKE ? OR email LIKE ? OR domain LIKE ? LIMIT 20");
        $stm->execute([$like, $like, $like]);
        foreach ($stm->fetchAll(\PDO::FETCH_OBJ) as $a) {
            $results[] = ['id' => (int)$a->id, 'type' => 'account', 'username' => $a->username ?? '', 'email' => $a->email ?? '', 'domain' => $a->domain ?? '', 'status' => $a->status ?? ''];
        }

        $stm = $pdo->prepare("SELECT id, subject, status, created_at FROM tickets WHERE subject LIKE ? LIMIT 20");
        $stm->execute([$like]);
        foreach ($stm->fetchAll(\PDO::FETCH_OBJ) as $t) {
            $results[] = ['id' => (int)$t->id, 'type' => 'ticket', 'subject' => $t->subject ?? '', 'status' => $t->status ?? '', 'created_at' => $t->created_at ?? ''];
        }
        $this->json(['success' => true, 'data' => $results]);
    }

    // ─────────────── Accounts ───────────────

    public function listAccounts()
    {
        $this->apiKeyAuth();
        $page = (int)$this->request->query('page', 1);
        $perPage = (int)$this->request->query('per_page', 20);
        $offset = ($page - 1) * $perPage;
        $pdo = $this->pdo();
        $total = $pdo->query("SELECT COUNT(*) FROM hosting_users")->fetchColumn();
        $stm = $pdo->prepare("SELECT id, username, email, domain, package, status, created_at FROM hosting_users ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
        $stm->execute();
        $this->json(['success' => true, 'total' => (int)$total, 'data' => $stm->fetchAll(\PDO::FETCH_OBJ)]);
    }

    public function getAccount($id)
    {
        $this->apiKeyAuth();
        $a = $this->db->table('hosting_users')->where('id', (int)$id)->first();
        if (!$a) $this->json(['success' => false, 'error' => 'Account not found'], 404);
        $this->json(['success' => true, 'data' => [
            'id' => (int)$a->id, 'username' => $a->username ?? '', 'email' => $a->email ?? '',
            'company' => $a->company ?? '', 'domain' => $a->domain ?? '',
            'package' => $a->package ?? '', 'status' => $a->status ?? '',
            'created_at' => $a->created_at ?? '', 'last_login' => $a->last_login ?? '',
        ]]);
    }

    public function updateAccount($id)
    {
        $this->apiKeyAuth();
        $data = $this->getJsonInput();
        unset($data['id'], $data['password']);
        if (empty($data)) $this->json(['success' => false, 'error' => 'No data provided'], 400);
        $this->db->table('hosting_users')->where('id', (int)$id)->update($data);
        $this->json(['success' => true, 'message' => 'Account updated']);
    }

    public function suspendAccount($id)
    {
        $this->apiKeyAuth();
        $this->db->table('hosting_users')->where('id', (int)$id)->update(['status' => 'suspended']);
        $this->json(['success' => true, 'message' => 'Account suspended']);
    }

    public function unsuspendAccount($id)
    {
        $this->apiKeyAuth();
        $this->db->table('hosting_users')->where('id', (int)$id)->update(['status' => 'active']);
        $this->json(['success' => true, 'message' => 'Account unsuspended']);
    }

    public function resetPassword($id)
    {
        $this->apiKeyAuth();
        $input = $this->getJsonInput();
        $password = $input['password'] ?? bin2hex(random_bytes(8));
        $this->db->table('hosting_users')->where('id', (int)$id)->update(['password' => password_hash($password, PASSWORD_DEFAULT)]);
        $this->json(['success' => true, 'message' => 'Password reset', 'new_password' => $password]);
    }

    public function getAccountHosting($id)
    {
        $this->apiKeyAuth();
        $list = $this->db->table('hosting_accounts')->where('user_id', (int)$id)->get() ?: [];
        $this->json(['success' => true, 'data' => $list]);
    }

    public function getAccountStreaming($id)
    {
        $this->apiKeyAuth();
        $list = $this->db->table('streaming_accounts')->where('user_id', (int)$id)->get() ?: [];
        $this->json(['success' => true, 'data' => $list]);
    }

    public function getAccountLoginHistory($id)
    {
        $this->apiKeyAuth();
        $pdo = $this->pdo();
        $stm = $pdo->prepare("SELECT * FROM login_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
        $stm->execute([(int)$id]);
        $this->json(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_OBJ)]);
    }

    public function getAccountSessions($id)
    {
        $this->apiKeyAuth();
        $list = $this->db->table('active_sessions')->where('user_id', (int)$id)->get() ?: [];
        $this->json(['success' => true, 'data' => $list]);
    }

    public function getAccountNotes($id)
    {
        $this->apiKeyAuth();
        $pdo = $this->pdo();
        $stm = $pdo->prepare("SELECT * FROM account_notes WHERE user_id = ? ORDER BY created_at DESC");
        $stm->execute([(int)$id]);
        $this->json(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_OBJ)]);
    }

    public function addAccountNote($id)
    {
        $this->apiKeyAuth();
        $input = $this->getJsonInput();
        $content = $input['content'] ?? '';
        if (empty($content)) $this->json(['success' => false, 'error' => 'Content required'], 400);
        $nid = $this->db->table('account_notes')->insertGetId([
            'user_id' => (int)$id, 'content' => $content,
            'created_by' => $this->currentApiKey->name ?? 'API',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->json(['success' => true, 'data' => ['id' => $nid, 'content' => $content]]);
    }

    // ─────────────── Tickets ───────────────

    public function listTickets()
    {
        $this->apiKeyAuth();
        $status = $this->request->query('status', '');
        $page = (int)$this->request->query('page', 1);
        $perPage = 20; $offset = ($page - 1) * $perPage;
        $pdo = $this->pdo();
        if (!empty($status)) {
            $stm = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE status = ?");
            $stm->execute([$status]);
            $total = $stm->fetchColumn();
            $stm = $pdo->prepare("SELECT * FROM tickets WHERE status = ? ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
            $stm->execute([$status]);
        } else {
            $total = $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
            $stm = $pdo->query("SELECT * FROM tickets ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
        }
        $this->json(['success' => true, 'total' => (int)$total, 'data' => $stm->fetchAll(\PDO::FETCH_OBJ)]);
    }

    public function getTicket($id)
    {
        $this->apiKeyAuth();
        $t = $this->db->table('tickets')->where('id', (int)$id)->first();
        if (!$t) $this->json(['success' => false, 'error' => 'Ticket not found'], 404);
        $this->json(['success' => true, 'data' => $t]);
    }

    public function getTicketMessages($id)
    {
        $this->apiKeyAuth();
        $pdo = $this->pdo();
        $stm = $pdo->prepare("SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY created_at ASC");
        $stm->execute([(int)$id]);
        $this->json(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_OBJ)]);
    }

    public function replyTicket($id)
    {
        $this->apiKeyAuth();
        $input = $this->getJsonInput();
        $message = $input['message'] ?? '';
        if (empty($message)) $this->json(['success' => false, 'error' => 'Message required'], 400);
        $tid = (int)$id;
        $this->db->table('ticket_messages')->insertGetId([
            'ticket_id' => $tid, 'message' => $message,
            'sender_type' => 'operator', 'sender_name' => $this->currentApiKey->name ?? 'Staff',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('tickets')->where('id', $tid)->update(['status' => 'answered', 'updated_at' => date('Y-m-d H:i:s')]);
        $this->json(['success' => true, 'message' => 'Reply sent']);
    }

    public function closeTicket($id)
    {
        $this->apiKeyAuth();
        $this->db->table('tickets')->where('id', (int)$id)->update(['status' => 'closed', 'updated_at' => date('Y-m-d H:i:s')]);
        $this->json(['success' => true, 'message' => 'Ticket closed']);
    }

    // ─────────────── Live Chat ───────────────

    public function listChats()
    {
        $this->apiKeyAuth();
        $status = $this->request->query('status', '');
        $pdo = $this->pdo();
        if (!empty($status)) {
            $parts = explode(',', $status);
            $placeholders = implode(',', array_fill(0, count($parts), '?'));
            $stm = $pdo->prepare("SELECT * FROM chat_sessions WHERE status IN ($placeholders) ORDER BY created_at DESC");
            $stm->execute($parts);
        } else {
            $stm = $pdo->query("SELECT * FROM chat_sessions ORDER BY created_at DESC");
        }
        $this->json(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_OBJ)]);
    }

    public function getChatMessages($id)
    {
        $this->apiKeyAuth();
        $pdo = $this->pdo();
        $stm = $pdo->prepare("SELECT * FROM chat_messages WHERE session_id = ? ORDER BY created_at ASC");
        $stm->execute([(int)$id]);
        $this->json(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_OBJ)]);
    }

    public function sendChatMessage($id)
    {
        $this->apiKeyAuth();
        $input = $this->getJsonInput();
        $message = $input['message'] ?? '';
        if (empty($message)) $this->json(['success' => false, 'error' => 'Message required'], 400);
        $sid = (int)$id;
        $this->db->table('chat_messages')->insertGetId([
            'session_id' => $sid, 'message' => $message,
            'sender_type' => 'operator', 'sender_name' => $this->currentApiKey->name ?? 'Staff',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('chat_sessions')->where('id', $sid)->update(['status' => 'active', 'last_activity' => date('Y-m-d H:i:s')]);
        $this->json(['success' => true, 'message' => 'Sent']);
    }

    public function closeChat($id)
    {
        $this->apiKeyAuth();
        $this->db->table('chat_sessions')->where('id', (int)$id)->update(['status' => 'closed', 'last_activity' => date('Y-m-d H:i:s')]);
        $this->json(['success' => true, 'message' => 'Chat closed']);
    }

    public function transferChat($id)
    {
        $this->apiKeyAuth();
        $input = $this->getJsonInput();
        $department = $input['department'] ?? '';
        if (empty($department)) $this->json(['success' => false, 'error' => 'Department required'], 400);
        $this->db->table('chat_sessions')->where('id', (int)$id)->update(['department' => $department]);
        $this->json(['success' => true, 'message' => "Transferred to {$department}"]);
    }

    public function visitors()
    {
        $this->apiKeyAuth();
        $pdo = $this->pdo();
        $cutoff = date('Y-m-d H:i:s', time() - 600);
        $stm = $pdo->prepare("SELECT * FROM chat_visitors WHERE last_seen >= ? ORDER BY last_seen DESC");
        $stm->execute([$cutoff]);
        $this->json(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_OBJ)]);
    }

    // ─────────────── Billing ───────────────

    public function listInvoices()
    {
        $this->apiKeyAuth();
        $userId = $this->request->query('user_id', '');
        $pdo = $this->pdo();
        if (!empty($userId)) {
            $stm = $pdo->prepare("SELECT * FROM invoices WHERE user_id = ? ORDER BY created_at DESC");
            $stm->execute([(int)$userId]);
        } else {
            $stm = $pdo->query("SELECT * FROM invoices ORDER BY created_at DESC");
        }
        $this->json(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_OBJ)]);
    }

    public function listOrders()
    {
        $this->apiKeyAuth();
        $userId = $this->request->query('user_id', '');
        $pdo = $this->pdo();
        if (!empty($userId)) {
            $stm = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
            $stm->execute([(int)$userId]);
        } else {
            $stm = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
        }
        $this->json(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_OBJ)]);
    }

    public function listTransactions()
    {
        $this->apiKeyAuth();
        $userId = $this->request->query('user_id', '');
        $pdo = $this->pdo();
        if (!empty($userId)) {
            $stm = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
            $stm->execute([(int)$userId]);
        } else {
            $stm = $pdo->query("SELECT * FROM transactions ORDER BY created_at DESC");
        }
        $this->json(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_OBJ)]);
    }

    // ─────────────── Knowledge Base ───────────────

    public function kbCategories()
    {
        $this->apiKeyAuth();
        $pdo = $this->pdo();
        $cats = $pdo->query("SELECT * FROM kb_categories ORDER BY name ASC")->fetchAll(\PDO::FETCH_OBJ);
        $result = [];
        foreach ($cats as $c) {
            $stm = $pdo->prepare("SELECT COUNT(*) FROM kb_articles WHERE category_id = ?");
            $stm->execute([$c->id]);
            $result[] = ['id' => (int)$c->id, 'name' => $c->name ?? '', 'article_count' => (int)$stm->fetchColumn()];
        }
        $this->json(['success' => true, 'data' => $result]);
    }

    public function kbArticles()
    {
        $this->apiKeyAuth();
        $catId = $this->request->query('category_id', '');
        $pdo = $this->pdo();
        if (!empty($catId)) {
            $stm = $pdo->prepare("SELECT * FROM kb_articles WHERE category_id = ? ORDER BY title ASC");
            $stm->execute([(int)$catId]);
        } else {
            $stm = $pdo->query("SELECT * FROM kb_articles ORDER BY title ASC");
        }
        $this->json(['success' => true, 'data' => $stm->fetchAll(\PDO::FETCH_OBJ)]);
    }

    public function getKbArticle($id)
    {
        $this->apiKeyAuth();
        $a = $this->db->table('kb_articles')->where('id', (int)$id)->first();
        if (!$a) $this->json(['success' => false, 'error' => 'Article not found'], 404);
        $this->json(['success' => true, 'data' => $a]);
    }

    public function cannedResponses()
    {
        $this->apiKeyAuth();
        $pdo = $this->pdo();
        $this->json(['success' => true, 'data' => $pdo->query("SELECT * FROM canned_responses ORDER BY title ASC")->fetchAll(\PDO::FETCH_OBJ)]);
    }

    // ─────────────── File Manager ───────────────

    public function listFiles()
    {
        $this->apiKeyAuth();
        $path = $this->request->query('path', '/');
        $base = '/var/www/radiohosting';
        $fullPath = realpath($base . $path);
        if (!$fullPath || strpos($fullPath, $base) !== 0) {
            $this->json(['success' => false, 'error' => 'Invalid path'], 400);
        }
        $items = [];
        foreach (scandir($fullPath) ?: [] as $f) {
            if ($f === '.' || $f === '..') continue;
            $fp = $fullPath . '/' . $f;
            $items[] = ['name' => $f, 'path' => str_replace($base, '', $fp), 'type' => is_dir($fp) ? 'dir' : 'file', 'size' => is_file($fp) ? filesize($fp) : 0];
        }
        $this->json(['success' => true, 'data' => $items]);
    }

    public function downloadFile()
    {
        $this->apiKeyAuth();
        $path = $this->request->query('path', '');
        $base = '/var/www/radiohosting';
        $fullPath = realpath($base . $path);
        if (!$fullPath || strpos($fullPath, $base) !== 0 || !is_file($fullPath)) {
            $this->json(['success' => false, 'error' => 'File not found'], 404);
        }
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    // ─────────────── Remote Assistance ───────────────

    public function requestRemote()
    {
        $this->apiKeyAuth();
        $input = $this->getJsonInput();
        $customerId = $input['customer_id'] ?? 0;
        if (empty($customerId)) $this->json(['success' => false, 'error' => 'customer_id required'], 400);
        $sid = bin2hex(random_bytes(16));
        $this->db->table('remote_sessions')->insertGetId([
            'customer_id' => (int)$customerId, 'session_id' => $sid,
            'status' => 'pending', 'created_by' => $this->currentApiKey->name ?? 'API',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->json(['success' => true, 'data' => ['session_id' => $sid]]);
    }

    // ─────────────── Reports ───────────────

    public function getReport($type)
    {
        $this->apiKeyAuth();
        $pdo = $this->pdo();
        switch ($type) {
            case 'tickets':
                $data = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count, status FROM tickets GROUP BY DATE(created_at), status ORDER BY date DESC LIMIT 30")->fetchAll(\PDO::FETCH_OBJ);
                break;
            case 'revenue':
                $data = $pdo->query("SELECT DATE(created_at) as date, SUM(amount) as total FROM transactions GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 30")->fetchAll(\PDO::FETCH_OBJ);
                break;
            case 'chats':
                $data = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM chat_sessions GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 30")->fetchAll(\PDO::FETCH_OBJ);
                break;
            default:
                $this->json(['success' => false, 'error' => 'Unknown report type'], 400);
        }
        $this->json(['success' => true, 'data' => $data]);
    }

    // ─────────────── Version ───────────────

    public function version()
    {
        $this->json([
            'success' => true,
            'data' => [
                'version' => '1.0.0',
                'name' => 'Planet Hosts Support Console',
                'api_version' => '1.0',
            ]
        ]);
    }
}
