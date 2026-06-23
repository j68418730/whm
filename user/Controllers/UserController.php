<?php

namespace User\Controllers;

use Core\Controller;

class UserController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;
    protected $hostingUser;
    protected $package;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function loadUser()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $user = $this->auth->user();
        // Try email match first, then username/id match
        $this->hostingUser = $this->db->table('hosting_users')->where('email', $user->email)->first();
        if (!$this->hostingUser && !empty($user->name)) {
            $this->hostingUser = $this->db->table('hosting_users')->where('username', $user->name)->first();
        }
        if (!$this->hostingUser && !empty($user->id)) {
            $this->hostingUser = $this->db->table('hosting_users')->where('id', $user->id)->first();
        }
        // If admin is viewing without sudo, grab first hosting account
        if (!$this->hostingUser && $this->auth->isAdmin()) {
            $this->hostingUser = $this->db->table('hosting_users')->orderBy('id', 'ASC')->first();
        }
        if ($this->hostingUser) {
            $this->package = $this->db->table('hosting_packages')->where('id', $this->hostingUser->package_id)->first();
        }
        return $user;
    }

    public function index()
    {
        $u = $this->loadUser();
        $hosting = $this->hostingUser;
        $pkg = $this->package;
        $pkgType = $pkg->type ?? ($hosting->plan_type ?? '');
        $uid = $hosting ? $hosting->id : 0;

        // Detect account type
        $isWeb = $pkgType === 'web_hosting' || $pkgType === 'hosting' || $pkgType === '' || str_contains($pkgType, 'web');
        $isRadio = str_contains($pkgType, 'icecast') || str_contains($pkgType, 'shoutcast') || str_contains($pkgType, 'radio') || str_contains($pkgType, 'stream');
        $isVps = str_contains($pkgType, 'vps') || str_contains($pkgType, 'virtual');
        $isDedicated = str_contains($pkgType, 'dedicated') || str_contains($pkgType, 'ded');
        $isChat = str_contains($pkgType, 'chat') || str_contains($pkgType, 'livechat');
        $isGame = str_contains($pkgType, 'game');

        // Streams
        $streams = $uid ? ($this->db->table('radio_streams')->where('user_id', $uid)->get() ?: []) : [];
        $hasStreams = count($streams) > 0;
        if ($hasStreams) $isRadio = true;

        // Disk usage (only for web/vps/dedicated)
        $diskTotal = $pkg->disk_space ?? 0;
        $diskUsed = 0; $diskPct = 0;
        if ($hosting && ($isWeb || $isVps || $isDedicated)) {
            $dir = '/home/' . $hosting->username;
            if (is_dir($dir)) {
                $du = @shell_exec("du -sk " . escapeshellarg($dir) . " 2>/dev/null | awk '{print \$1}'");
                $diskUsed = $du ? round((int)trim($du) / 1024 / 1024, 2) : 0;
            }
            $diskPct = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100) : 0;
        }

        // Email (only for web)
        $emailAccounts = [];
        $emailCount = 0;
        $emailAllowed = false;
        if ($isWeb && $uid && $hosting) {
            try { $emailAccounts = $this->db->table('mail_accounts')->where('domain', $hosting->domain ?? '')->get() ?: []; } catch (\Exception $e) {}
            $emailCount = count($emailAccounts);
            $emailAllowed = ($pkg->email_accounts ?? 0) > 0;
        }

        // Domains (only for web)
        $domains = [];
        if ($isWeb && $uid) {
            try { $domains = $this->db->table('hosting_domains')->where('user_id', $uid)->get() ?: []; } catch (\Exception $e) {}
            if ($hosting && $hosting->domain) {
                $hasMain = false;
                foreach ($domains as $d) { if (($d->domain ?? '') === $hosting->domain) $hasMain = true; }
                if (!$hasMain) {
                    $mainDomain = (object)['domain' => $hosting->domain, 'type' => 'main', 'status' => 'active'];
                    array_unshift($domains, $mainDomain);
                }
            }
        }

        // Tickets (all account types)
        $openTickets = $uid ? ($this->db->table('tickets')->where('user_id', $uid)->where('status', 'open')->get() ?: []) : [];
        $pendingTickets = $uid ? ($this->db->table('tickets')->where('user_id', $uid)->where('status', 'pending')->get() ?: []) : [];
        $resolvedTickets = $uid ? ($this->db->table('tickets')->where('user_id', $uid)->where('status', 'resolved')->get() ?: []) : [];

        // Invoices (all account types)
        $openInvoices = $uid ? ($this->db->table('invoices')->where('user_id', $uid)->where('status', 'unpaid')->get() ?: []) : [];
        $paidInvoices = $uid ? ($this->db->table('invoices')->where('user_id', $uid)->where('status', 'paid')->get() ?: []) : [];

        // Services / Orders (all account types)
        $services = $uid ? ($this->db->table('billing_services')->where('user_id', $uid)->get() ?: []) : [];
        // If no billing services, show the hosting package as a service
        if (empty($services) && $hosting && $package) {
            $svc = new \stdClass();
            $svc->id = $hosting->id;
            $svc->name = $package->name;
            $svc->type = $package->type ?? 'web_hosting';
            $svc->price = $package->monthly_price ?? 0;
            $services = [$svc];
        }
        $orders = $uid ? ($this->db->table('billing_orders')->where('user_id', $uid)->get() ?: []) : [];

        // Game servers (all account types)
        $gameServers = $uid ? ($this->db->table('game_servers')->where('user_id', $uid)->get() ?: []) : [];
        $hasGames = count($gameServers) > 0;

        // Chatbox tenant (all account types)
        $chatTenant = null;
        if ($uid) $chatTenant = $this->db->table('chatbox_tenants')->where('hosting_user_id', $uid)->first();
        $hasChat = $chatTenant !== null;

        // Activity (recent)
        $recentActivity = [];
        try { $recentActivity = $this->db->table('activity_log')->where('user_id', $uid)->orderBy('created_at', 'DESC')->limit(5)->get() ?: []; } catch (\Exception $e) {}

        $lastLogin = $hosting->last_login ?? 'First login';
        $userIp = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

        $notifications = [];
        if ($diskPct > 90) $notifications[] = ['type' => 'warning', 'msg' => "Disk usage at {$diskPct}%"];
        if (count($openInvoices) > 0) $notifications[] = ['type' => 'danger', 'msg' => count($openInvoices) . ' unpaid invoice(s)'];

        return $this->view('user.dashboard', [
            'user' => $u, 'hosting' => $hosting, 'package' => $pkg,
            'streams' => $streams, 'hasStreams' => $hasStreams,
            'isWeb' => $isWeb, 'isRadio' => $isRadio, 'isVps' => $isVps,
            'isDedicated' => $isDedicated, 'isChat' => $isChat, 'isGame' => $isGame,
            'diskUsed' => $diskUsed, 'diskTotal' => $diskTotal, 'diskPct' => $diskPct,
            'emailCount' => $emailCount, 'emailAllowed' => $emailAllowed,
            'domains' => $domains, 'openTickets' => $openTickets, 'pendingTickets' => $pendingTickets,
            'resolvedTickets' => $resolvedTickets, 'openInvoices' => $openInvoices, 'paidInvoices' => $paidInvoices,
            'services' => $services, 'orders' => $orders,
            'gameServers' => $gameServers, 'hasGames' => $hasGames,
            'chatTenant' => $chatTenant, 'hasChat' => $hasChat,
            'recentActivity' => $recentActivity,
            'lastLogin' => $lastLogin, 'userIp' => $userIp,
            'notifications' => $notifications, 'title' => 'Dashboard',
        ]);
    }

    public function services() { $u = $this->loadUser(); return $this->view('user.services', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'My Services']); }
    public function usage() { $u = $this->loadUser(); return $this->view('user.usage', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Resource Usage']); }
    public function profile() { $u = $this->loadUser(); return $this->view('user.profile', ['user' => $u, 'hosting' => $this->hostingUser, 'package' => $this->package, 'title' => 'Profile']); }
    public function security() { $u = $this->loadUser(); return $this->view('user.security', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Security']); }
    public function support() { $u = $this->loadUser(); return $this->view('user.support', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Support']); }
    public function stats() { $u = $this->loadUser(); return $this->view('user.stats', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Statistics']); }
    public function tools() { $u = $this->loadUser(); return $this->view('user.tools', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Tools']); }
    public function logout() { session_destroy(); header('Location: /'); exit; }
    public function chat() { $u = $this->loadUser(); $app = \Core\Application::getInstance(); $user = $app->get('auth')->user(); $pdo = $this->db->pdo(); $hosting = $this->hostingUser; require BASE_PATH . '/public/user/chat.php'; exit; }
    public function admins() { $u = $this->loadUser(); $app = \Core\Application::getInstance(); $user = $app->get('auth')->user(); $pdo = $this->db->pdo(); $hosting = $this->hostingUser; require BASE_PATH . '/public/user/admins.php'; exit; }
    public function djManager() { $u = $this->loadUser(); $app = \Core\Application::getInstance(); $user = $app->get('auth')->user(); $pdo = $this->db->pdo(); $hosting = $this->hostingUser; require BASE_PATH . '/public/user/dj-manager.php'; exit; }
    public function phpSwitcher() { $u = $this->loadUser(); $app = \Core\Application::getInstance(); $user = $app->get('auth')->user(); $pdo = $this->db->pdo(); $hosting = $this->hostingUser; require BASE_PATH . '/public/user/php-switcher.php'; exit; }
}
