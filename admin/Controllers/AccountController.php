<?php

namespace Admin\Controllers;

use Core\Controller;

class AccountController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;

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
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        license_check('accounts');
        $user = $this->auth->user();

        $resellerId = (int)$this->request->get('reseller_id', 0);
        $search = trim((string)$this->request->get('search', ''));

        $sql = "SELECT * FROM hosting_users";
        $where = [];
        $params = [];
        if ($resellerId) {
            $where[] = "reseller_id = ?";
            $params[] = $resellerId;
        }
        if ($search) {
            $where[] = "(username LIKE ? OR domain LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like, $like, $like);
        }
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY id DESC";
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute($params);
        $accounts = $stmt->fetchAll(\PDO::FETCH_OBJ);

        $packages = $this->db->table('hosting_packages')->get();
        $packageMap = [];
        foreach ($packages as $p) $packageMap[$p->id] = $p;
        $resellers = $this->db->table('resellers')->get() ?: [];
        $owners = [];
        foreach ($resellers as $r) {
            $owners[(int)$r->id] = $r->company_name ?: $r->name ?: ('Reseller #' . $r->id);
        }
        $accountGroups = [];
        foreach ($accounts as $a) {
            $owner = $owners[(int)($a->reseller_id ?? 0)] ?? 'Root';
            $accountGroups[$owner][] = $a;
        }
        $accountsStats = [
            'total_accounts' => count($accounts),
            'active_accounts' => count(array_filter($accounts, function($a) { return $a->status === 'active'; })),
            'suspended_accounts' => count(array_filter($accounts, function($a) { return $a->status === 'suspended'; })),
            'terminated_accounts' => count(array_filter($accounts, function($a) { return $a->status === 'terminated'; })),
        ];

        // Products / services per account
        $servicesByUser = [];
        $productsMap = [];
        try { $productsMap = $this->db->table('billing_products')->get() ?: []; } catch (\Exception $e) {}
        $productsById = [];
        foreach ($productsMap as $bp) $productsById[$bp->id] = $bp;
        try {
            $services = $this->db->table('billing_services')->get() ?: [];
            foreach ($services as $s) $servicesByUser[$s->user_id][] = $s;
        } catch (\Exception $e) {}

        // Latest order per account
        $latestOrderByUser = [];
        try {
            $orders = $this->db->table('billing_orders')->orderBy('id', 'ASC')->get() ?: [];
            foreach ($orders as $o) $latestOrderByUser[$o->user_id] = $o;
        } catch (\Exception $e) {}

        // Disk usage (bulk du across /home)
        $diskUsageByUser = [];
        $duOut = @shell_exec('du -sk /home/*/ 2>/dev/null');
        foreach (preg_split('/\R+/', trim((string)$duOut)) ?: [] as $line) {
            if (!trim($line)) continue;
            $parts = preg_split('/\s+/', trim($line), 2);
            if (count($parts) !== 2) continue;
            $sizeKb = (int)$parts[0];
            $path = rtrim($parts[1], '/');
            $username = basename($path);
            $diskUsageByUser[$username] = $sizeKb > 0 ? round($sizeKb / 1024, 1) . ' MB' : '0 KB';
        }

        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.account.index', [
            'user' => $user,
            'accounts' => $accounts,
            'accountGroups' => $accountGroups,
            'packages' => $packages,
            'packageMap' => $packageMap,
            'accountsStats' => $accountsStats,
            'reseller_id' => $resellerId,
            'search' => $search,
            'resellers' => $resellers,
            'servicesByUser' => $servicesByUser,
            'productsById' => $productsById,
            'latestOrderByUser' => $latestOrderByUser,
            'diskUsageByUser' => $diskUsageByUser,
            'theme_settings' => $theme_settings
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $user = $this->auth->user();
        $packages = $this->db->pdo()->query("SELECT hp.*, COALESCE(bp.price, hp.monthly_price) as price FROM hosting_packages hp LEFT JOIN billing_products bp ON hp.id = bp.package_id AND bp.is_active = 1 WHERE hp.is_active = 1 ORDER BY hp.sort_order ASC")->fetchAll(\PDO::FETCH_OBJ);
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.account.create', [
            'user' => $user,
            'packages' => $packages,
            'theme_settings' => $theme_settings
        ]);
    }

    public function store()
    {
        session_write_close();
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            header('Location: /admin/login'); exit;
        }
        $adminId = $this->auth->user()->id;
        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $_POST['username'] ?? ''));
        $domain = strtolower(trim($_POST['domain'] ?? ''));
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $packageId = (int)($_POST['package_id'] ?? 0);

        if (!$username || !$email || !$password) {
            $_SESSION['error_message'] = 'Username, email, and password are required.';
            header('Location: /admin/account/create'); exit;
        }

        if (!$domain) $domain = "{$username}.planet-hosts.com";

        $existing = $this->db->table('hosting_users')->where('username', $username)->first();
        if ($existing) {
            $_SESSION['error_message'] = 'Username already exists.';
            header('Location: /admin/account/create'); exit;
        }

        $serverIp = '15.204.114.226';
        $homeDir = "/home/{$username}";

        // Insert with pending status — account is NOT active until provisioning completes
        $userId = $this->db->table('hosting_users')->insertGetId([
            'reseller_id' => 1,
            'package_id' => $packageId ?: null,
            'username' => $username,
            'domain' => $domain,
            'ip' => $serverIp,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'email' => $email,
            'php_version' => $_POST['php_version'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'status' => 'pending',
            'nameserver1' => 'ns1.planet-hosts.com',
            'nameserver2' => 'ns2.planet-hosts.com',
        ]);

        // Log activity
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        try {
            $this->db->table('activity_logs')->insert([
                'account_id' => $userId,
                'admin_id' => $adminId,
                'action' => 'provision_started',
                'details' => "Started provisioning for {$username} ({$domain})",
                'ip_address' => $ip,
            ]);
        } catch (\Exception $e) {}

        // --- Provision via unified script (runs all steps sequentially) ---
        $escUser = escapeshellarg($username);
        $escDomain = escapeshellarg($domain);
        $escHome = escapeshellarg($homeDir);
        $escPass = escapeshellarg($password);

        // Run provision in background with 120s timeout
        @exec("timeout 120 sudo /var/www/radiohosting/provision.sh provision {$escUser} {$escDomain} {$escHome} {$escPass} {$packageId} 2>/dev/null >/dev/null &");

        // --- Create domain record ---
        try {
            $this->db->table('domains')->insertGetId([
                'account_id' => $userId,
                'domain' => $domain,
                'type' => 'main',
                'document_root' => "{$homeDir}/public_html",
                'ip' => $serverIp,
                'status' => 'active',
            ]);
        } catch (\Exception $e) {}

        $nsList = [];
        try { $nsList = $this->db->table('dns_nameservers')->get() ?: []; } catch (\Exception $e) {}
        $_SESSION['account_created'] = [
            'username' => $username,
            'password' => $password,
            'domain' => $domain,
            'email' => $email,
            'ip' => $serverIp,
            'package_id' => $packageId,
            'home_dir' => $homeDir,
            'nameservers' => $nsList,
            'status' => 'pending',
        ];

        // Pre-allocate streaming ports for this customer
        try {
            $pm = new \Core\PortManager();
            foreach (['icecast', 'shoutcast_v1', 'rtmp', 'rtsp'] as $svcType) {
                $pm->allocate($svcType, $userId);
            }
        } catch (\Exception $e) {}

        // Create station_stream_config for this station
        try {
            $pm = new \Core\PortManager();
            $ports = $pm->getAllocatedPorts($userId);
            
            // Generate API key for the station
            $apiKey = 'ph_' . bin2hex(random_bytes(16));
            $stationStreamConfig = [
                'station_id' => $userId,
                'icecast_hostname' => 'radio.planet-hosts.com',
                'icecast_port' => isset($ports['icecast'][0]) ? $ports['icecast'][0] : 8000,
                'icecast_username' => 'source',
                'icecast_password' => 'sourcepass_' . bin2hex(random_bytes(8)),
                'icecast_mount' => '/live',
                'icecast_protocol' => 'icecast',
                'shoutcast_v1_hostname' => 'radio.planet-hosts.com',
                'shoutcast_v1_port' => isset($ports['shoutcast_v1'][0]) ? $ports['shoutcast_v1'][0] : 11000,
                'shoutcast_v1_password' => 'sc1_' . bin2hex(random_bytes(8)),
                'shoutcast_v2_hostname' => 'radio.planet-hosts.com',
                'shoutcast_v2_port' => isset($ports['shoutcast_v2'][0]) ? $ports['shoutcast_v2'][0] : 12000,
                'shoutcast_v2_username' => 'source',
                'shoutcast_v2_password' => 'sc2_' . bin2hex(random_bytes(8)),
                'auto_reconnect' => 1,
                'reconnect_interval' => 5,
                'max_reconnect_attempts' => 10,
                'bitrate' => 128,
                'format' => 'mp3',
                'samplerate' => 44100,
                'channels' => 2,
            ];
            $this->db->table('station_stream_config')->insert($stationStreamConfig);
            
            // Generate API key for this station
            $apiKey = 'ph_' . bin2hex(random_bytes(16));
            $this->db->table('billing_products')->insertGetId([
                'name' => 'Station API - ' . $username,
                'description' => 'Auto-generated API key for station ' . $username,
                'type' => 'hosting',
                'price' => 0,
                'setup_fee' => 0,
                'billing_cycle' => 'monthly',
                'is_active' => 1,
                'sort_order' => 0,
                'license_key' => $apiKey,
            ]);
            
            // Also update the station_stream_config with the API key
            $this->db->table('station_stream_config')
                ->where('station_id', $userId)
                ->update(['api_key' => $stationStreamConfig['api_key'] ?? '']);
        } catch (\Exception $e) {}

        header('Location: /admin/account/summary/' . $userId);
        exit;
    }

    public function provisionStatus($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            header('Content-Type: application/json'); echo json_encode(['error' => 'Unauthorized']); exit;
        }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) {
            header('Content-Type: application/json'); echo json_encode(['error' => 'Not found']); exit;
        }
        $u = escapeshellarg($account->username);
        $output = @exec("sudo /var/www/radiohosting/provision.sh status {$u} '' '' 2>/dev/null");
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $output ?: $account->status,
            'provision_step' => $account->provision_step,
            'db_status' => $account->status,
        ]);
        exit;
    }

    public function summary($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { header('Location: /admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { header('Location: /admin/account'); exit; }
        $package = null;
        $featureList = null;
        if ($account->package_id) {
            $package = $this->db->table('hosting_packages')->where('id', $account->package_id)->first();
            if ($package && $package->feature_list_id) {
                $featureList = $this->db->table('feature_lists')->where('id', $package->feature_list_id)->first();
            }
        }
        $data = $_SESSION['account_created'] ?? [];
        $password = $data['password'] ?? 'Set during creation';
        $nameservers = $data['nameservers'] ?? [];
        unset($_SESSION['account_created']);
        $user = $this->auth->user();
        return $this->view('admin.account.summary', [
            'user' => $user,
            'account' => $account,
            'package' => $package,
            'featureList' => $featureList,
            'plainPassword' => $password,
            'nameservers' => $nameservers,
            'title' => 'Account Created',
        ]);
    }

    public function sendAlert($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { echo 'Unauthorized'; exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { echo 'Account not found'; exit; }
        $title = trim($_POST['alert_title'] ?? '');
        $message = trim($_POST['alert_message'] ?? '');
        $type = in_array($_POST['alert_type'] ?? '', ['info','warning','success','danger']) ? $_POST['alert_type'] : 'info';
        if ($title && $message) {
            try {
                $this->db->table('user_alerts')->insertGetId([
                    'hosting_user_id' => $id, 'admin_id' => $this->auth->user()->id,
                    'title' => $title, 'message' => $message, 'type' => $type,
                ]);
                $_SESSION['success_message'] = "Alert sent to {$account->username}.";
            } catch (\Exception $e) {
                $_SESSION['error_message'] = 'Failed to send alert. Please contact support.';
            }
        }
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function emailSummary($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { echo 'Unauthorized'; exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { echo 'Account not found'; exit; }
        $to = trim($_POST['email'] ?? $account->email);
        $subject = "Planet-Hosts Account: {$account->username}";
        $msg = "Account Created Successfully!\n\n"
             . "Username: {$account->username}\n"
             . "Domain: {$account->domain}\n"
             . "IP: {$account->ip}\n\n"
             . "Nameservers:\n  ns1.planet-hosts.com\n  ns2.planet-hosts.com\n\n"
             . "Website: http://{$account->domain}/\n";
        @mail($to, $subject, $msg, "From: support@planet-hosts.com\r\nReply-To: support@planet-hosts.com");
        echo 'Email sent to ' . htmlspecialchars($to);
        exit;
    }

    private function getServerIp()
    {
        return 'planet-hosts.com';
    }

    public function show($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) {
            $this->response->redirect('/admin/account');
            exit;
        }
        $user = $this->auth->user();
        $packages = $this->db->table('hosting_packages')->get();
        $package = null;
        foreach ($packages as $p) {
            if ($p->id == $account->package_id) $package = $p;
        }
        $domains = [];
        try { $domains = $this->db->table('domains')->where('account_id', $id)->get() ?: []; } catch (\Exception $e) {}
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        // Usage stats
        $homeDir = '/home/' . $account->username;
        $diskUsage = '-';
        $bandwidthUsage = '-';
        $backupFiles = [];
        if (is_dir($homeDir)) {
            $diskOut = @shell_exec("du -sk " . escapeshellarg($homeDir) . " 2>/dev/null");
            $diskUsage = $diskOut ? round((int)trim(explode("\t", $diskOut)[0]) / 1024, 2) . ' MB' : '-';
            $backupFiles = glob("{$homeDir}/backup_*.tar.gz") ?: [];
            $backupFiles = array_merge($backupFiles, glob("{$homeDir}/backup_*.zip") ?: []);
            rsort($backupFiles);
        }
        try {
            $history = $this->db->table('activity_log')->where('target_id', (int)$id)->orderBy('created_at', 'DESC')->limit(10)->get() ?: [];
        } catch (\Exception $e) { $history = []; }
        $resellers = $this->db->table('resellers')->get() ?: [];

        // Billing: products, services, orders for this account
        $accountProduct = null;
        if ($package && $package->product_id) {
            try { $accountProduct = $this->db->table('billing_products')->where('id', $package->product_id)->first(); } catch (\Exception $e) {}
        }
        $services = [];
        try {
            $services = $this->db->pdo()->query("
                SELECT s.*, bp.name AS product_name, bp.price AS product_price, bp.billing_cycle AS product_cycle
                FROM billing_services s
                LEFT JOIN billing_products bp ON s.product_id = bp.id
                WHERE s.user_id = " . (int)$id . "
                ORDER BY s.id DESC
            ")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        } catch (\Exception $e) {}
        // Aggregate real services from every subsystem for this account
        $ownedServices = [];
        // 1. Hosting package itself
        if ($package) {
            $ownedServices[] = (object)[
                'kind' => 'hosting', 'id' => 'pkg' . $package->id, 'ref_id' => $package->id,
                'name' => $package->name, 'product_name' => $package->name,
                'status' => $account->status, 'billing_cycle' => 'monthly',
                'price' => $accountProduct->price ?? 0, 'next_due_date' => null,
                'manage_url' => '/admin/package/edit/' . (int)$package->id,
                'product_id' => $accountProduct->id ?? null,
                'detail' => 'Web Hosting Package',
            ];
        }
        // 2. Streaming stations (radio)
        try {
            $stations = $this->db->table('streaming_stations')->where('user_id', $id)->get() ?: [];
            foreach ($stations as $st) {
                $ownedServices[] = (object)[
                    'kind' => 'radio', 'id' => 'st' . $st->id, 'ref_id' => $st->id,
                    'name' => ($st->name ?: 'Station') . ' (' . strtoupper((string)$st->engine) . ')',
                    'product_name' => ($st->name ?: 'Radio Station') . ' — ' . strtoupper((string)$st->engine),
                    'status' => $st->status, 'billing_cycle' => 'monthly',
                    'price' => 0, 'next_due_date' => null,
                    'manage_url' => '/admin/streams/edit/' . (int)$st->id,
                    'product_id' => null,
                    'detail' => 'Streaming on port ' . (int)$st->port . ' · ' . (int)$st->max_listeners . ' listeners · ' . (int)$st->bitrate . ' kbps',
                ];
            }
        } catch (\Exception $e) {}
        // 3. Chatbox tenant
        try {
            $tenant = $this->db->table('chatbox_tenants')->where('hosting_user_id', $id)->first();
            if ($tenant) {
                $ownedServices[] = (object)[
                    'kind' => 'chatbox', 'id' => 'cb' . $tenant->id, 'ref_id' => $tenant->id,
                    'name' => ($tenant->name ?: 'Chatbox') . ' Chatbox',
                    'product_name' => ($tenant->name ?: 'Chatbox'),
                    'status' => $tenant->is_active ? 'active' : 'suspended',
                    'billing_cycle' => 'monthly',
                    'price' => 0, 'next_due_date' => null,
                    'manage_url' => '/admin/chat-dashboard/manage/' . (int)$tenant->id,
                    'product_id' => null,
                    'detail' => 'Chat widget · ' . (int)$tenant->max_rooms . ' rooms · ' . ($tenant->voice_enabled ? 'voice' : 'text'),
                ];
            }
        } catch (\Exception $e) {}
        // 4. Game servers
        try {
            $gs = $this->db->table('game_servers')->where('user_id', $id)->get() ?: [];
            foreach ($gs as $g) {
                $ownedServices[] = (object)[
                    'kind' => 'game', 'id' => 'g' . $g->id, 'ref_id' => $g->id,
                    'name' => ($g->name ?: $g->server_name) . ' Game Server',
                    'product_name' => ($g->name ?: $g->server_name) . ' Game Server',
                    'status' => $g->is_active ? ($g->status ?: 'active') : 'suspended',
                    'billing_cycle' => 'monthly',
                    'price' => 0, 'next_due_date' => null,
                    'manage_url' => '/admin/games/servers',
                    'product_id' => null,
                    'detail' => ($g->game_type ?: 'Game') . ' · ' . (int)$g->max_players . ' slots' . ($g->game_port ? ' · :' . (int)$g->game_port : ''),
                ];
            }
        } catch (\Exception $e) {}
        // Merge billing services in with their kind labels
        foreach ($services as $bs) {
            $ownedServices[] = (object)[
                'kind' => 'billing', 'id' => 'svc' . $bs->id, 'ref_id' => $bs->id,
                'name' => ($bs->product_name ?? ('Service #' . $bs->id)),
                'product_name' => ($bs->product_name ?? ('Service #' . $bs->id)),
                'status' => $bs->status, 'billing_cycle' => $bs->billing_cycle ?? '',
                'price' => $bs->price ?? 0, 'next_due_date' => $bs->next_due_date ?? null,
                'manage_url' => null, 'product_id' => $bs->product_id ?? null,
                'detail' => ($bs->order_id ? ('Order #' . $bs->order_id . ' · ') : '') . ($bs->billing_cycle ?? ''),
            ];
        }
        $orders = [];
        try {
            $orders = $this->db->table('billing_orders')->where('user_id', $id)->orderBy('id', 'DESC')->get() ?: [];
        } catch (\Exception $e) {}
        $allProducts = [];
        try { $allProducts = $this->db->table('billing_products')->where('is_active', 1)->orderBy('name', 'ASC')->get() ?: []; } catch (\Exception $e) {}
        $activePackages = [];
        try { $activePackages = $this->db->table('hosting_packages')->where('is_active', 1)->get() ?: []; } catch (\Exception $e) {}

        return $this->view('admin.account.show', [
            'user' => $user,
            'account' => $account,
            'package' => $package,
            'domains' => $domains,
            'theme_settings' => $theme_settings,
            'disk_usage' => $diskUsage,
            'bandwidth_usage' => $bandwidthUsage,
            'backup_files' => $backupFiles,
            'history' => $history,
            'resellers' => $resellers,
            'packages' => $packages,
            'accountProduct' => $accountProduct,
            'services' => $services,
            'ownedServices' => $ownedServices,
            'orders' => $orders,
            'allProducts' => $allProducts,
            'activePackages' => $activePackages,
        ]);
    }

    public function orderCreate($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', (int)$id)->first();
        if (!$account) { $_SESSION['error_message'] = 'Account not found.'; $this->response->redirect('/admin/account'); exit; }

        $productId = (int)$this->request->post('product_id', 0);
        $packageIdRaw = $this->request->post('package_id');
        $packageId = $packageIdRaw ? (int)$packageIdRaw : null;
        $total = (float)$this->request->post('total', 0);
        $type = $this->request->post('type', 'new');
        $status = $this->request->post('status', 'pending');
        $paymentMethod = $this->request->post('payment_method', 'manual');
        $description = trim((string)$this->request->post('description', ''));
        $createService = $this->request->post('create_service') === '1';

        $product = null;
        $items = [];
        if ($productId) {
            try { $product = $this->db->table('billing_products')->where('id', $productId)->first(); } catch (\Exception $e) {}
        }
        if ($product) {
            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => (float)$product->price,
                'billing_cycle' => $product->billing_cycle ?? 'monthly',
                'qty' => 1,
                'domain' => $account->domain ?? '',
            ];
        }

        try {
            $orderId = $this->db->table('billing_orders')->insertGetId([
                'user_id' => (int)$id,
                'product_id' => $productId ?: null,
                'package_id' => $packageId,
                'items' => json_encode($items),
                'total' => $total,
                'type' => in_array($type, ['new','renewal','upgrade','downgrade']) ? $type : 'new',
                'status' => in_array($status, ['pending','active','suspended','cancelled']) ? $status : 'pending',
                'payment_method' => $paymentMethod,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to create order: ' . $e->getMessage();
            $this->response->redirect('/admin/account/show/' . (int)$id);
            exit;
        }

        if ($createService && $orderId) {
            $cycle = $product->billing_cycle ?? 'monthly';
            if (!in_array($cycle, ['monthly','quarterly','semiannual','annual','biennial'])) $cycle = 'monthly';
            try {
                $this->db->table('billing_services')->insert([
                    'user_id' => (int)$id,
                    'product_id' => $productId ?: null,
                    'order_id' => $orderId,
                    'domain' => $account->domain ?? '',
                    'status' => ($status === 'active') ? 'active' : 'pending',
                    'billing_cycle' => $cycle,
                    'price' => $total,
                    'next_due_date' => $this->request->post('next_due_date', date('Y-m-d', strtotime('+30 days'))),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $e) {}
        }

        try {
            $this->db->table('activity_logs')->insert([
                'account_id' => (int)$id, 'admin_id' => $this->auth->user()->id,
                'action' => 'order_created',
                'details' => "Created order #{$orderId} for {$account->username}" . ($createService ? ' (service activated)' : ''),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (\Exception $e) {}

        $_SESSION['success_message'] = "Order #{$orderId} created." . ($createService ? ' Service activated.' : '');
        $this->response->redirect('/admin/account/show/' . (int)$id);
        exit;
    }

    public function orderDelete($id, $orderId)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        try {
            $this->db->table('billing_services')->where('order_id', (int)$orderId)->delete();
            $this->db->table('billing_orders')->where('id', (int)$orderId)->delete();
            $_SESSION['success_message'] = "Order #{$orderId} deleted (linked services removed).";
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to delete order: ' . $e->getMessage();
        }
        $this->response->redirect('/admin/account/show/' . (int)$id);
        exit;
    }

    public function orderStatus($id, $orderId)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $status = $this->request->post('status', 'pending');
        if (!in_array($status, ['pending','active','suspended','cancelled'])) $status = 'pending';
        try {
            $this->db->table('billing_orders')->where('id', (int)$orderId)->update(['status' => $status]);
            $_SESSION['success_message'] = "Order #{$orderId} set to {$status}.";
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to update order: ' . $e->getMessage();
        }
        $this->response->redirect('/admin/account/show/' . (int)$id);
        exit;
    }

    public function serviceStatus($id, $serviceId)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $status = $this->request->post('status', 'active');
        if (!in_array($status, ['active','suspended','terminated','pending'])) $status = 'active';
        try {
            $this->db->table('billing_services')->where('id', (int)$serviceId)->update([
                'status' => $status,
                'next_due_date' => $this->request->post('next_due_date', ''),
            ]);
            $_SESSION['success_message'] = "Service #{$serviceId} set to {$status}.";
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to update service: ' . $e->getMessage();
        }
        $this->response->redirect('/admin/account/show/' . (int)$id);
        exit;
    }

    public function serviceDelete($id, $serviceId)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        try {
            $this->db->table('billing_services')->where('id', (int)$serviceId)->delete();
            $_SESSION['success_message'] = "Service #{$serviceId} removed.";
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Failed to remove service: ' . $e->getMessage();
        }
        $this->response->redirect('/admin/account/show/' . (int)$id);
        exit;
    }

    public function suspend($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $_SESSION['error_message'] = 'Account not found.'; $this->response->redirect('/admin/account'); exit; }
        $adminId = $this->auth->user()->id;
        $u = escapeshellarg($account->username);
        $d = escapeshellarg($account->domain);
        $h = escapeshellarg("/home/{$account->username}");
        @exec("sudo /var/www/radiohosting/provision.sh suspend {$u} {$d} {$h} 2>/dev/null >/dev/null &");
        $done = $this->cascadeSuspend((int)$id, $adminId, 'suspended');
        $_SESSION['success_message'] = "Account '{$account->username}' suspended. " . $done;
        $this->response->redirect('/admin/account');
        exit;
    }

    public function allowSuspension($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $_SESSION['error_message'] = 'Account not found.'; $this->response->redirect('/admin/account'); exit; }
        $allow = (int)$this->request->post('allow_suspension', 1);
        $this->db->table('hosting_users')->where('id', $id)->update(['allow_suspension' => $allow ? 1 : 0]);
        try {
            $this->db->table('activity_logs')->insert([
                'account_id' => $id, 'admin_id' => $this->auth->user()->id,
                'action' => 'allow_suspension',
                'details' => ($allow ? 'Enabled' : 'Disabled') . " auto-suspension for {$account->username}",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (\Exception $e) {}
        $_SESSION['success_message'] = "Auto-suspension " . ($allow ? 'enabled' : 'disabled') . " for '{$account->username}'.";
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function toggleNoAutoSuspend($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $_SESSION['error_message'] = 'Account not found.'; $this->response->redirect('/admin/account'); exit; }
        $flag = (int)$this->request->post('no_auto_suspend', 0);
        $this->db->table('hosting_users')->where('id', $id)->update(['no_auto_suspend' => $flag ? 1 : 0]);
        try {
            $this->db->table('activity_logs')->insert([
                'account_id' => $id, 'admin_id' => $this->auth->user()->id,
                'action' => 'toggle_no_auto_suspend',
                'details' => ($flag ? 'Enabled' : 'Disabled') . " no-auto-suspend for {$account->username}",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (\Exception $e) {}
        $_SESSION['success_message'] = "No-auto-suspend " . ($flag ? 'enabled' : 'disabled') . " for '{$account->username}'.";
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function unsuspend($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $_SESSION['error_message'] = 'Account not found.'; $this->response->redirect('/admin/account'); exit; }
        $adminId = $this->auth->user()->id;
        $u = escapeshellarg($account->username);
        $d = escapeshellarg($account->domain);
        $h = escapeshellarg("/home/{$account->username}");
        @exec("sudo /var/www/radiohosting/provision.sh unsuspend {$u} {$d} {$h} 2>/dev/null >/dev/null &");
        $done = $this->cascadeSuspend((int)$id, $adminId, 'active');
        $_SESSION['success_message'] = "Account '{$account->username}' unsuspended. " . $done;
        $this->response->redirect('/admin/account');
        exit;
    }

    /**
     * Cascade suspend/unsuspend across every dependency of an account:
     *  - the account itself (hosting_users.status)
     *  - its streaming stations (+ their DJs), chatbox tenant, game servers,
     *    domains, email, billing services, and open tickets
     *  - if the account is a RESELLER owner, also ALL of their clients
     *    (hosting_users.reseller_id = their reseller row) — recursively.
     * Returns a human-readable summary string.
     */
    protected function cascadeSuspend($userId, $adminId, $state)
    {
        $suspending = ($state === 'suspended');
        $statusVal = $suspending ? 'suspended' : 'active';
        $done = [];
        $seen = [];
        $this->cascadeSuspendRecursive((int)$userId, $adminId, $state, $statusVal, $seen, $done);
        return implode(' ', $done);
    }

    protected function cascadeSuspendRecursive($userId, $adminId, $state, $statusVal, &$seen, &$done)
    {
        if (isset($seen[$userId])) return;
        $seen[$userId] = true;
        $suspending = ($state === 'suspended');
        $account = $this->db->table('hosting_users')->where('id', $userId)->first();
        if (!$account) return;

        // Marks DB rows as suspended/active without re-calling provision (done once above).
        $this->db->table('hosting_users')->where('id', $userId)->update([
            'status' => $statusVal,
            'suspended_at' => $state === 'suspended' ? date('Y-m-d H:i:s') : null,
            'suspended_by' => $state === 'suspended' ? $adminId : null,
        ]);
        $done[] = "{$account->username}";
        // Suspend/resume the actual Linux home (idempotent, backgrounded)
        $hu = escapeshellarg($account->username);
        $hd = escapeshellarg($account->domain ?? '');
        $hh = escapeshellarg("/home/{$account->username}");
        @exec("sudo /var/www/radiohosting/provision.sh " . ($suspending ? 'suspend' : 'unsuspend') . " {$hu} {$hd} {$hh} 2>/dev/null >/dev/null &");

        // Streaming stations + their DJs
        try {
            $st = $this->db->table('streaming_stations')->where('user_id', $userId)->get() ?: [];
            foreach ($st as $s) {
                $this->db->table('streaming_stations')->where('id', $s->id)->update(['status' => $statusVal]);
                // stop/start engine
                if ($state === 'suspended') {
                    $engine = strtolower($s->engine ?? 'icecast');
                    $port = (int)($s->port ?? 0);
                    if ($engine === 'shoutcast' || $engine === 'shoutcast1' || $engine === 'shoutcast2') {
                        @exec("pkill -f \"sc_serv.*{$port}\" 2>/dev/null >/dev/null &");
                    } else {
                        @exec("sudo systemctl stop icecast2 2>/dev/null >/dev/null &");
                    }
                }
                // DJs on this station
                $djs = $this->db->table('radio_djs')->where('stream_id', $s->id)->get() ?: [];
                foreach ($djs as $dj) {
                    $this->db->table('radio_djs')->where('id', $dj->id)->update(['status' => $suspending ? 'suspended' : 'active']);
                }
            }
            if (!empty($st)) $done[] = "streaming";
        } catch (\Exception $e) {}

        // Chatbox tenant
        try {
            $t = $this->db->table('chatbox_tenants')->where('hosting_user_id', $userId)->first();
            if ($t) {
                $this->db->table('chatbox_tenants')->where('id', $t->id)->update(['is_active' => $suspending ? 0 : 1]);
                $done[] = "chatbox";
            }
        } catch (\Exception $e) {}

        // Game servers
        try {
            $gs = $this->db->table('game_servers')->where('user_id', $userId)->get() ?: [];
            foreach ($gs as $g) {
                $this->db->table('game_servers')->where('id', $g->id)->update([
                    'status' => $statusVal, 'is_active' => $suspending ? 0 : 1,
                ]);
            }
            if (!empty($gs)) $done[] = "games";
        } catch (\Exception $e) {}

        // Domains
        try {
            $dm = $this->db->table('domains')->where('account_id', $userId)->get() ?: [];
            foreach ($dm as $d) {
                $this->db->table('domains')->where('id', $d->id)->update(['status' => $statusVal]);
            }
            if (!empty($dm)) $done[] = "domains";
        } catch (\Exception $e) {}

        // Billing services
        try {
            $svc = $this->db->table('billing_services')->where('user_id', $userId)->get() ?: [];
            foreach ($svc as $s) {
                if ($s->status === 'active' || $s->status === 'pending' || $s->status === 'suspended') {
                    $this->db->table('billing_services')->where('id', $s->id)->update(['status' => $statusVal]);
                }
            }
            if (!empty($svc)) $done[] = "billing";
        } catch (\Exception $e) {}

        // Open tickets for this user
        try {
            $tk = $this->db->table('tickets')->where('user_id', $userId)->where('status', 'open')->get() ?: [];
            foreach ($tk as $t) {
                $this->db->table('tickets')->where('id', $t->id)->update(['status' => $suspending ? 'closed' : 'open']);
            }
        } catch (\Exception $e) {}

        // Activity log
        try {
            $this->db->table('activity_logs')->insert([
                'account_id' => $userId, 'admin_id' => $adminId,
                'action' => $suspending ? 'suspended' : 'unsuspended',
                'details' => ($suspending ? 'Cascade suspended' : 'Cascade unsuspended') . " {$account->username}",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (\Exception $e) {}

        // If this user owns a reseller (email matches resellers), cascade to their clients
        try {
            $res = $this->db->table('resellers')->where('email', $account->email)->first();
            if ($res) {
                $clients = $this->db->table('hosting_users')->where('reseller_id', $res->id)->get() ?: [];
                foreach ($clients as $c) {
                    $this->cascadeSuspendRecursive((int)$c->id, $adminId, $state, $statusVal, $seen, $done);
                }
                if (!empty($clients)) $done[] = "+clients";
            }
        } catch (\Exception $e) {}
    }

    public function edit($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        $user = $this->auth->user();
        $packages = $this->db->table('hosting_packages')->where('is_active', 1)->get();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.account.edit', [
            'user' => $user, 'account' => $account, 'packages' => $packages, 'theme_settings' => $theme_settings
        ]);
    }

    public function update($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        $data = [
            'username' => strtolower(preg_replace('/[^a-z0-9]/', '', $_POST['username'] ?? $account->username)),
            'domain' => strtolower(trim($_POST['domain'] ?? $account->domain)),
            'email' => trim($_POST['email'] ?? $account->email),
            'package_id' => (int)($_POST['package_id'] ?? $account->package_id) ?: null,
            'php_version' => $_POST['php_version'] ?? $account->php_version,
            'first_name' => $_POST['first_name'] ?? $account->first_name,
            'last_name' => $_POST['last_name'] ?? $account->last_name,
            'phone' => trim($_POST['phone'] ?? ($account->phone ?? '')),
            'address' => trim($_POST['address'] ?? ($account->address ?? '')),
            'city' => trim($_POST['city'] ?? ($account->city ?? '')),
            'state' => trim($_POST['state'] ?? ($account->state ?? '')),
            'zip' => trim($_POST['zip'] ?? ($account->zip ?? '')),
            'country' => trim($_POST['country'] ?? ($account->country ?? '')),
            'avatar' => trim($_POST['avatar'] ?? ($account->avatar ?? '')),
            'payment_gateway' => trim($_POST['payment_gateway'] ?? ($account->payment_gateway ?? '')),
            'payment_gateway_id' => trim($_POST['payment_gateway_id'] ?? ($account->payment_gateway_id ?? '')),
        ];
        // 4-digit account authorization code (PIN) — hashed, never stored plaintext
        $pin = preg_replace('/\D/', '', $_POST['account_pin'] ?? '');
        if (strlen($pin) === 4) {
            $data['support_pin_hash'] = password_hash($pin, PASSWORD_DEFAULT);
        } elseif (isset($_POST['clear_pin'])) {
            $data['support_pin_hash'] = null;
        }
        $this->db->table('hosting_users')->where('id', $id)->update($data);
        $_SESSION['success_message'] = 'Account updated.' . (strlen($pin) === 4 ? ' Account PIN set.' : '');
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function terminate($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $_SESSION['error_message'] = 'Account not found.'; $this->response->redirect('/admin/account'); exit; }
        $adminId = $this->auth->user()->id;
        $u = escapeshellarg($account->username);
        $d = escapeshellarg($account->domain);
        $h = escapeshellarg("/home/{$account->username}");
        @exec("sudo /var/www/radiohosting/provision.sh terminate {$u} {$d} {$h} 2>/dev/null >/dev/null &");
        try { (new \Core\PortManager())->releaseByCustomer($id); } catch (\Exception $e) {}
        try {
            $this->db->table('domains')->where('account_id', $id)->delete();
            $this->db->table('backup_settings')->where('account_id', $id)->delete();
            $this->db->table('activity_logs')->where('account_id', $id)->delete();
            if (!empty($account->ip)) {
                try { $this->db->table('server_ips')->where('assigned_to', $account->username)->update(['assigned_to' => null]); } catch (\Exception $e) {}
            }
        } catch (\Exception $e) {}
        $this->db->table('hosting_users')->where('id', $id)->update([
            'status' => 'terminated',
            'provision_step' => null,
            'database_name' => null,
            'database_user' => null,
            'database_password' => null,
        ]);
        try {
            $this->db->table('activity_logs')->insert([
                'account_id' => $id, 'admin_id' => $adminId,
                'action' => 'terminated',
                'details' => "Account {$account->username} terminated with all data removed",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (\Exception $e) {}
        $_SESSION['success_message'] = "Account '{$account->username}' terminated.";
        $this->response->redirect('/admin/account');
        exit;
    }

    public function password($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        $newPass = $this->request->post('password', '');
        if ($newPass) {
            $this->db->table('hosting_users')->where('id', $id)->update(['password_hash' => password_hash($newPass, PASSWORD_DEFAULT)]);
            exec("echo '{$newPass}' | passwd --stdin {$account->username} 2>/dev/null");
            $_SESSION['success_message'] = 'Password changed.';
        }
        $this->response->redirect('/admin/account');
        exit;
    }

    // SSH Access
    public function sshAccess($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        $access = $this->request->post('ssh_access', 'jailed');
        $this->db->table('hosting_users')->where('id', $id)->update(['ssh_access' => $access]);
        \Core\SshJail::applySshAccess($account->username, $access);
        $_SESSION['success_message'] = "SSH access set to '{$access}' for {$account->username}.";
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function sshKeyGenerate($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        $key = \Core\SshJail::generateKeyPair($account->username);
        if ($key) {
            $this->db->table('hosting_users')->where('id', $id)->update(['ssh_public_key' => $key]);
            $_SESSION['success_message'] = 'SSH key pair generated.';
        } else {
            $_SESSION['error_message'] = 'Failed to generate SSH key.';
        }
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function sshKeyDelete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        \Core\SshJail::deleteSshKey($account->username);
        $this->db->table('hosting_users')->where('id', $id)->update(['ssh_public_key' => null]);
        $_SESSION['success_message'] = 'SSH key deleted.';
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function changeOwner($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        $resellerId = (int)$this->request->post('reseller_id', 0);
        $ownerEmail = trim($this->request->post('owner_email', ''));
        if ($resellerId) {
            $this->db->table('hosting_users')->where('id', $id)->update(['reseller_id' => $resellerId]);
        }
        if ($ownerEmail) {
            $newOwner = $this->db->table('hosting_users')->where('email', $ownerEmail)->first();
            if ($newOwner) {
                $this->db->table('hosting_users')->where('id', $id)->update(['owner_id' => $newOwner->id, 'reseller_id' => 0]);
            }
        }
        $_SESSION['success_message'] = 'Ownership changed.';
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function exitSudo()
    {
        if (!isset($_SESSION['sudo_login']) || !isset($_SESSION['sudo_admin_user'])) {
            $this->response->redirect('/admin/login');
            exit;
        }
        // Restore admin session
        $_SESSION['user'] = $_SESSION['sudo_admin_user'];
        $_SESSION['is_admin'] = true;
        unset($_SESSION['sudo_login']);
        unset($_SESSION['sudo_admin_id']);
        unset($_SESSION['sudo_admin_user']);
        $this->response->redirect('/admin/dashboard');
        exit;
    }

    public function loginAs($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $this->response->redirect('/admin/account'); exit; }
        // Sudo: keep admin session, switch user context
        $_SESSION['sudo_login'] = true;
        $_SESSION['sudo_admin_id'] = $this->auth->user()->id;
        $_SESSION['sudo_admin_user'] = $this->auth->user();
        $user = (object)[
            'id' => $account->id,
            'email' => $account->email,
            'name' => $account->first_name ?: $account->username,
            'is_admin' => false,
        ];
        $session = \Core\Application::getInstance()->get('session');
        $session->put('user', $user);
        $session->put('is_admin', false);
        $this->response->redirect('/user');
        exit;
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', (int)$id)->first();
        if (!$account) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json'); echo json_encode(['success' => false, 'error' => 'Account not found.']); exit;
            }
            $_SESSION['error_message'] = 'Account not found.'; $this->response->redirect('/admin/account'); exit;
        }

        $adminId = $this->auth->user()->id;
        $u = escapeshellarg($account->username);
        $d = escapeshellarg($account->domain);
        $h = escapeshellarg("/home/{$account->username}");
        @exec("sudo /var/www/radiohosting/provision.sh terminate {$u} {$d} {$h} 2>/dev/null >/dev/null &");
        // Release all ports allocated to this customer
        try { (new \Core\PortManager())->releaseByCustomer($id); } catch (\Exception $e) {}
        try {
            $this->db->table('domains')->where('account_id', $id)->delete();
            $this->db->table('backup_settings')->where('account_id', $id)->delete();
            $this->db->table('activity_logs')->where('account_id', $id)->delete();
            // Cascade billing data
            $this->db->table('billing_services')->where('user_id', $id)->delete();
            $this->db->table('billing_orders')->where('user_id', $id)->delete();
            $this->db->table('billing_payments')->where('user_id', $id)->delete();
            $this->db->table('invoices')->where('user_id', $id)->delete();
        } catch (\Exception $e) {}

        try {
            $this->db->table('activity_logs')->insert([
                'account_id' => $id, 'admin_id' => $adminId,
                'action' => 'deleted',
                'details' => "Account {$account->username} permanently deleted",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (\Exception $e) {}
        // Finally delete the account row itself (the previous bug: row never removed)
        try {
            $this->db->table('hosting_users')->where('id', (int)$id)->delete();
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Account cleaned but row removal failed: ' . $e->getMessage();
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json'); echo json_encode(['success' => true, 'message' => "Account '{$account->username}' permanently deleted."]); exit;
        }

        $_SESSION['success_message'] = "Account '{$account->username}' permanently deleted with all data.";
        $this->response->redirect('/admin/account');
    }

    public function backup($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $account = $this->db->table('hosting_users')->where('id', $id)->first();
        if (!$account) { $_SESSION['error_message'] = 'Account not found.'; $this->response->redirect('/admin/account'); exit; }

        $action = $this->request->get('action', 'run');
        $u = escapeshellarg($account->username);
        $d = escapeshellarg($account->domain);

        if ($action === 'run') {
            @exec("sudo /var/www/radiohosting/backup.sh run 2>/dev/null >/dev/null &");
            $_SESSION['success_message'] = "Backup started for {$account->username}.";
        } elseif ($action === 'restore') {
            $file = escapeshellarg($this->request->get('file', ''));
            if ($file) {
                @exec("sudo /var/www/radiohosting/backup.sh restore {$u} {$file} 2>/dev/null >/dev/null &");
                $_SESSION['success_message'] = "Restore started for {$account->username}.";
            } else {
                $_SESSION['error_message'] = 'No backup file specified.';
            }
        } elseif ($action === 'list') {
            $output = @exec("sudo /var/www/radiohosting/backup.sh list {$u} 2>/dev/null");
            header('Content-Type: application/json');
            echo json_encode(['backups' => explode("\n", trim($output))]);
            exit;
        }
        $this->response->redirect('/admin/account/show/' . $id);
        exit;
    }

    public function monitorAlerts($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            header('Content-Type: application/json'); echo json_encode(['error' => 'Unauthorized']); exit;
        }
        $alertFile = '/var/log/radiohosting/monitor/alerts.json';
        if (file_exists($alertFile)) {
            $alerts = json_decode(file_get_contents($alertFile), true);
            $accountAlerts = array_filter($alerts['alerts'] ?? [], function($a) use ($id) {
                return $a['account_id'] == $id;
            });
            header('Content-Type: application/json');
            echo json_encode(array_values($accountAlerts));
        } else {
            header('Content-Type: application/json'); echo json_encode([]);
        }
        exit;
    }

}

