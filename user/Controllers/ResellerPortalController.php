<?php

namespace User\Controllers;

use Core\Controller;

class ResellerPortalController extends Controller
{
    protected $auth, $request, $response, $db;
    protected $reseller;
    protected $staff = null;   // reseller_staff row when logged in as staff
    protected $addons = ['billing' => false, 'chat' => false, 'support' => false];

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function requireReseller()
    {
        // Resellers use dedicated port 2089. If reached from another portal port
        // (2087 super-admin or 2083 clients), bounce them to the reseller port.
        $serverPort = (int)($_SERVER['SERVER_PORT'] ?? 0);
        $host = strtolower(($_SERVER['HTTP_HOST'] ?? ''));
        $onPortal = $serverPort === 2087 || $serverPort === 2083 || str_contains($host, ':2087') || str_contains($host, ':2083');
        if ($onPortal) { $this->response->redirect('https://planet-hosts.com:2089/reseller'); exit; }

        // Staff sessions (reseller_staff) authenticate through user_login.php — they hold
        // no hosting account, so auth->check() is false for them. Handle staff first.
        if (!empty($_SESSION['reseller_staff'])) {
            $s = (object)$_SESSION['reseller_staff'];
            $staffRow = $this->db->table('reseller_staff')->where('id', (int)$s->id)->where('is_active', 1)->first();
            if (!$staffRow) { unset($_SESSION['reseller_staff']); $this->response->redirect('https://planet-hosts.com:2089/user_login.php'); exit; }
            $this->staff = $staffRow;
            $this->reseller = $this->db->table('resellers')->where('id', (int)$staffRow->reseller_id)->where('is_active', 1)->first();
            if (!$this->reseller) { $this->response->redirect('https://planet-hosts.com:2089/user_login.php'); exit; }
            $this->addons = $this->enabledAddons();
            return (object)['id' => 0, 'name' => $staffRow->name, 'email' => $staffRow->email, 'is_staff' => true, 'role' => $staffRow->role];
        }

        if (!$this->auth->check()) { $this->response->redirect('https://planet-hosts.com:2089/user_login.php'); exit; }
        $user = $this->auth->user();
        // Admins / super admins hitting /reseller should go back to their admin dashboard
        if (isset($user->is_admin) && $user->is_admin) { $this->response->redirect('/admin'); exit; }
        $this->reseller = $this->db->table('resellers')->where('email', $user->email)->where('is_active', 1)->first();
        if (!$this->reseller) { $this->response->redirect('/user'); exit; }
        $this->addons = $this->enabledAddons();
        return $user;
    }

    // Effective permission check. Owner = everything. Otherwise merge staff row role
    // permissions + granted custom role templates (reseller_roles via reseller_staff_roles).
    protected function can($perm)
    {
        if (!$this->staff) return true; // reseller owner — full access
        $role = $this->staff->role ?? 'support';
        if ($role === 'owner' || $role === 'manager') return true;
        $perms = is_string($this->staff->permissions ?? null) ? json_decode($this->staff->permissions, true) : ($this->staff->permissions ?? []);
        $perms = is_array($perms) ? $perms : [];
        // Merge custom role templates
        try {
            $pdo = $this->db->pdo();
            $q = $pdo->prepare("SELECT rp.permissions FROM reseller_staff_roles sr
                JOIN reseller_roles rp ON rp.id = sr.role_id
                WHERE sr.staff_id = ? AND sr.reseller_id = ? AND rp.is_active = 1");
            $q->execute([(int)$this->staff->id, (int)$this->reseller->id]);
            foreach ($q->fetchAll(\PDO::FETCH_OBJ) as $row) {
                $rp = json_decode((string)$row->permissions, true);
                if (is_array($rp)) $perms = array_merge($perms, $rp);
            }
        } catch (\Exception $e) {}
        return in_array($perm, $perms, true);
    }

    protected function requirePerm($perm)
    {
        if ($this->staff && !$this->can($perm)) {
            $_SESSION['error_message'] = 'You do not have permission to access that section.';
            $this->response->redirect('/reseller');
            exit;
        }
    }

    protected function view($view, $data = [])
    {
        $data['addons'] = $this->addons;
        $data['staff'] = $this->staff;
        $data['can'] = function ($perm) { return $this->can($perm); };
        return parent::view($view, $data);
    }

    public function dashboard()
    {
        $u = $this->requireReseller();
        $pdo = $this->db->pdo();
        $rid = (int)$this->reseller->id;

        $accounts = $this->db->table('hosting_users')->where('reseller_id', $rid)->get() ?: [];
        $totalAccounts = count($accounts);
        $activeAccounts = 0; $suspendedAccounts = 0;
        foreach ($accounts as $a) {
            if ($a->status === 'active') $activeAccounts++;
            elseif ($a->status === 'suspended') $suspendedAccounts++;
        }

        // Open tickets from their own clients
        $openTickets = 0;
        try { $openTickets = (int)$pdo->query("SELECT COUNT(*) FROM tickets t JOIN hosting_users hu ON hu.id=t.user_id WHERE hu.reseller_id={$rid} AND t.status='open'")->fetchColumn() ?? 0; } catch (\Exception $e) {}

        // Billing scoped to their clients
        $revenueMonth = (float)($pdo->query("SELECT COALESCE(SUM(bp.amount),0) FROM billing_payments bp JOIN hosting_users hu ON hu.id=bp.user_id WHERE bp.status='completed' AND hu.reseller_id={$rid} AND bp.created_at >= '" . date('Y-m-01') . "'")->fetchColumn() ?? 0);
        $totalCollected = (float)($pdo->query("SELECT COALESCE(SUM(bp.amount),0) FROM billing_payments bp JOIN hosting_users hu ON hu.id=bp.user_id WHERE bp.status='completed' AND hu.reseller_id={$rid}")->fetchColumn() ?? 0);
        $outstanding = (float)($pdo->query("SELECT COALESCE(SUM(i.total),0) FROM invoices i JOIN hosting_users hu ON hu.id=i.user_id WHERE i.status IN ('sent','overdue','pending') AND hu.reseller_id={$rid}")->fetchColumn() ?? 0);
        $pendingOrders = (int)($pdo->query("SELECT COUNT(*) FROM billing_orders o JOIN hosting_users hu ON hu.id=o.user_id WHERE hu.reseller_id={$rid} AND o.status='pending'")->fetchColumn() ?? 0);
        $activeServices = (int)($pdo->query("SELECT COUNT(*) FROM billing_services s JOIN hosting_users hu ON hu.id=s.user_id WHERE s.status='active' AND hu.reseller_id={$rid}")->fetchColumn() ?? 0);

        // Recent activity (own scope)
        $recentAccounts = $this->db->table('hosting_users')->where('reseller_id', $rid)->orderBy('created_at', 'DESC')->limit(5)->get() ?: [];
        $recentOrders = $pdo->query("SELECT o.*, hu.username AS client FROM billing_orders o JOIN hosting_users hu ON hu.id=o.user_id WHERE hu.reseller_id={$rid} ORDER BY o.created_at DESC LIMIT 5")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        $recentTickets = $pdo->query("SELECT t.id, t.subject, t.status, hu.username AS client FROM tickets t JOIN hosting_users hu ON hu.id=t.user_id WHERE hu.reseller_id={$rid} ORDER BY t.created_at DESC LIMIT 5")->fetchAll(\PDO::FETCH_OBJ) ?: [];

        // Server/health strip (infrastructure status — shared, not admin-specific)
        $serviceNames = ['apache2' => 'Apache', 'mariadb' => 'MariaDB', 'icecast2' => 'Icecast', 'postfix' => 'Postfix', 'dovecot' => 'Dovecot', 'nginx' => 'Nginx'];
        $services = [];
        foreach ($serviceNames as $sName => $sLabel) {
            $active = trim(shell_exec("systemctl is-active {$sName} 2>/dev/null") ?: '') === 'active';
            $services[] = ['name' => $sLabel, 'active' => $active];
        }

        return $this->view('user.reseller.dashboard', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Reseller Dashboard',
            'addons' => $this->addons,
            'totalAccounts' => $totalAccounts, 'activeAccounts' => $activeAccounts, 'suspendedAccounts' => $suspendedAccounts,
            'openTickets' => $openTickets, 'revenueMonth' => $revenueMonth, 'totalCollected' => $totalCollected,
            'outstanding' => $outstanding, 'pendingOrders' => $pendingOrders, 'activeServices' => $activeServices,
            'recentAccounts' => $recentAccounts, 'recentOrders' => $recentOrders, 'recentTickets' => $recentTickets,
            'services' => $services,
        ]);
    }

    public function clientsOverview()
    {
        $u = $this->requireReseller();
        if ($this->staff && !$this->can('clients')) { $_SESSION['error_message'] = 'You do not have permission to view clients.'; $this->response->redirect('/reseller'); exit; }
        $pdo = $this->db->pdo();
        $rid = (int)$this->reseller->id;
        $totalClients = (int)($pdo->query("SELECT COUNT(*) FROM hosting_users WHERE reseller_id = {$rid}")->fetchColumn() ?? 0);
        $pendingClients = (int)($pdo->query("SELECT COUNT(*) FROM hosting_users WHERE reseller_id = {$rid} AND status = 'pending'")->fetchColumn() ?? 0);
        $outstanding = (float)($pdo->query("SELECT COALESCE(SUM(i.total),0) FROM invoices i JOIN hosting_users hu ON hu.id=i.user_id WHERE i.status IN ('sent','overdue','pending') AND hu.reseller_id={$rid}")->fetchColumn() ?? 0);
        return $this->view('user.reseller.clients_overview', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Clients',
            'total_clients' => $totalClients, 'pending_clients' => $pendingClients, 'total_outstanding' => $outstanding,
        ]);
    }

    public function clients()
    {
        $u = $this->requireReseller();
        if ($this->staff && !$this->can('clients')) { $_SESSION['error_message'] = 'You do not have permission to view clients.'; $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $accounts = $this->db->table('hosting_users')->where('reseller_id', $rid)->orderBy('created_at', 'DESC')->get() ?: [];
        $pkgNames = [];
        foreach ($accounts as $a) {
            $pkg = $this->db->table('hosting_packages')->where('id', $a->package_id)->first();
            $pkgNames[$a->id] = $pkg ? $pkg->name : '-';
        }
        $stats = [
            'total' => count($accounts),
            'active' => count(array_filter($accounts, fn($a) => $a->status === 'active')),
            'suspended' => count(array_filter($accounts, fn($a) => $a->status === 'suspended')),
            'terminated' => count(array_filter($accounts, fn($a) => $a->status === 'terminated')),
        ];
        return $this->view('user.reseller.clients', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Clients',
            'accounts' => $accounts, 'pkgNames' => $pkgNames, 'stats' => $stats,
        ]);
    }

    public function clientShow($id)
    {
        $u = $this->requireReseller();
        $this->requirePerm('clients');
        $rid = (int)$this->reseller->id;
        $account = $this->db->table('hosting_users')->where('id', (int)$id)->where('reseller_id', $rid)->first();
        if (!$account) { $_SESSION['error_message'] = 'Client not found.'; $this->response->redirect('/reseller-clients'); exit; }
        $package = $account->package_id ? $this->db->table('hosting_packages')->where('id', (int)$account->package_id)->first() : null;
        $retailPkg = $account->reseller_package_id ? $this->db->table('reseller_packages')->where('id', (int)$account->reseller_package_id)->first() : null;
        try { $domains = $this->db->table('domains')->where('account_id', (int)$id)->get() ?: []; } catch (\Exception $e) { $domains = []; }
        $homeDir = '/home/' . $account->username;
        $diskUsage = '-'; $backupFiles = [];
        if (is_dir($homeDir)) {
            $diskOut = @shell_exec("du -sk " . escapeshellarg($homeDir) . " 2>/dev/null");
            $diskUsage = $diskOut ? round((int)trim(explode("\t", $diskOut)[0]) / 1024, 2) . ' MB' : '-';
            $backupFiles = array_merge(glob("{$homeDir}/backup_*.tar.gz") ?: [], glob("{$homeDir}/backup_*.zip") ?: []);
            rsort($backupFiles);
        }
        $vhostContent = @file_get_contents("/etc/apache2/sites-available/{$account->username}.conf");
        $vhostSslContent = @file_get_contents("/etc/apache2/sites-available/{$account->username}-ssl.conf");
        // History scoped to this reseller's own activity on this client
        try {
            $history = $this->db->table('reseller_audit_logs')
                ->where('reseller_id', $rid)
                ->where('resource_type', 'hosting_user')
                ->where('resource_id', (int)$id)
                ->orderBy('created_at', 'DESC')->limit(10)->get() ?: [];
        } catch (\Exception $e) { $history = []; }
        return $this->view('user.reseller.client_show', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Client Details',
            'account' => $account, 'package' => $package, 'retailPkg' => $retailPkg, 'domains' => $domains,
            'disk_usage' => $diskUsage, 'backup_files' => $backupFiles,
            'vhost_content' => $vhostContent, 'vhost_ssl_content' => $vhostSslContent, 'history' => $history,
        ]);
    }

    public function clientPassword($id)
    {
        $u = $this->requireReseller();
        $this->requirePerm('clients');
        $rid = (int)$this->reseller->id;
        $a = $this->db->table('hosting_users')->where('id', (int)$id)->where('reseller_id', $rid)->first();
        if (!$a) { $_SESSION['error_message'] = 'Client not found.'; $this->response->redirect('/reseller-clients'); exit; }
        $password = $this->request->post('password', '');
        if (strlen($password) < 8) { $_SESSION['error_message'] = 'Password must be 8+ characters.'; $this->response->redirect('/reseller-client/' . (int)$id); exit; }
        $this->db->table('hosting_users')->where('id', (int)$id)->update(['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
        $this->audit('client.password_reset', 'hosting_user', (int)$id, ['username' => $a->username]);
        $_SESSION['success_message'] = "Password updated for '{$a->username}'.";
        $this->response->redirect('/reseller-client/' . (int)$id);
    }

    public function clientSuspend($id)
    {
        $u = $this->requireReseller();
        if ($this->staff && !$this->can('clients')) { $_SESSION['error_message'] = 'No permission.'; $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $a = $this->db->table('hosting_users')->where('id', (int)$id)->where('reseller_id', $rid)->first();
        if (!$a) { $_SESSION['error_message'] = 'Client not found.'; $this->response->redirect('/reseller/clients'); exit; }
        $this->db->table('hosting_users')->where('id', (int)$id)->update([
            'status' => 'suspended', 'suspended_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit('client.suspended', 'hosting_user', (int)$id, ['username' => $a->username]);
        $_SESSION['success_message'] = "Client '{$a->username}' suspended.";
        $this->response->redirect('/reseller/clients');
    }

    public function clientUnsuspend($id)
    {
        $u = $this->requireReseller();
        if ($this->staff && !$this->can('clients')) { $_SESSION['error_message'] = 'No permission.'; $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $a = $this->db->table('hosting_users')->where('id', (int)$id)->where('reseller_id', $rid)->first();
        if (!$a) { $_SESSION['error_message'] = 'Client not found.'; $this->response->redirect('/reseller/clients'); exit; }
        $this->db->table('hosting_users')->where('id', (int)$id)->update(['status' => 'active']);
        $this->audit('client.unsuspended', 'hosting_user', (int)$id, ['username' => $a->username]);
        $_SESSION['success_message'] = "Client '{$a->username}' reactivated.";
        $this->response->redirect('/reseller/clients');
    }

    // ── Create client (scoped to reseller's own retail packages only) ──
    public function clientCreate()
    {
        $u = $this->requireReseller();
        if ($this->staff && !$this->can('clients')) { $_SESSION['error_message'] = 'No permission.'; $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $pkgs = $this->db->table('reseller_packages')->where('reseller_id', $rid)->where('is_active', 1)->orderBy('created_at', 'DESC')->get() ?: [];
        return $this->view('user.reseller.client_create', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Create Client', 'pkgs' => $pkgs,
        ]);
    }

    public function clientStore()
    {
        $u = $this->requireReseller();
        if ($this->staff && !$this->can('clients')) { $_SESSION['error_message'] = 'No permission.'; $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $this->request->post('username', '')));
        $email = trim($this->request->post('email', ''));
        $password = $this->request->post('password', '');
        $domain = strtolower(trim($this->request->post('domain', '')));
        $pkgId = (int)$this->request->post('reseller_package_id', 0);
        $firstName = trim($this->request->post('first_name', ''));
        $lastName = trim($this->request->post('last_name', ''));

        if (!$username || !$email || strlen($password) < 8) {
            $_SESSION['error_message'] = 'Username, valid email, and an 8+ character password are required.';
            $this->response->redirect('/reseller/clients/create'); exit;
        }
        if (strpos($email, '@') === false) {
            $_SESSION['error_message'] = 'Please provide a valid email address.';
            $this->response->redirect('/reseller/clients/create'); exit;
        }
        if (!$domain) $domain = "{$username}.planet-hosts.com";
        if ($this->db->table('hosting_users')->where('username', $username)->first()) {
            $_SESSION['error_message'] = "Username '{$username}' is already taken.";
            $this->response->redirect('/reseller/clients/create'); exit;
        }
        if ($this->db->table('hosting_users')->where('email', $email)->first()) {
            $_SESSION['error_message'] = 'An account with that email already exists.';
            $this->response->redirect('/reseller/clients/create'); exit;
        }
        // Own retail package only — never a server package
        if ($pkgId) {
            $owned = $this->db->table('reseller_packages')->where('id', $pkgId)->where('reseller_id', $rid)->first();
            if (!$owned) { $_SESSION['error_message'] = 'That package is not yours.'; $this->response->redirect('/reseller/clients/create'); exit; }
        }
        $nameserver1 = 'ns1.planet-hosts.com';
        $nameserver2 = 'ns2.planet-hosts.com';
        try {
            $ns1 = $this->db->table('automation_settings')->where('setting_key', 'ns1')->first();
            $ns2 = $this->db->table('automation_settings')->where('setting_key', 'ns2')->first();
            if ($ns1) $nameserver1 = $ns1->setting_value;
            if ($ns2) $nameserver2 = $ns2->setting_value;
        } catch (\Exception $e) {}

        $cid = $this->db->table('hosting_users')->insertGetId([
            'reseller_id' => $rid, 'reseller_package_id' => $pkgId ?: null,
            'username' => $username, 'domain' => $domain,
            'ip' => '15.204.114.226',
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'email' => $email, 'first_name' => $firstName, 'last_name' => $lastName,
            'php_version' => $this->request->post('php_version', '8.2'),
            'nameserver1' => $nameserver1, 'nameserver2' => $nameserver2,
            'status' => 'pending', 'created_by' => 'reseller',
        ]);
        $this->audit('client.created', 'hosting_user', $cid, ['username' => $username, 'domain' => $domain, 'reseller_package_id' => $pkgId]);
        $_SESSION['success_message'] = "Client '{$username}' created (status: pending). Run provisioning from the Provisioning page to activate.";
        $this->response->redirect('/reseller/clients');
    }

    public function packages()
    {
        $u = $this->requireReseller();
        if ($this->staff && !$this->can('packages')) { $_SESSION['error_message'] = 'No permission.'; $this->response->redirect('/reseller'); exit; }
        // The reseller creates/manages their OWN retail packages. They never use server packages.
        $pkgs = $this->db->table('reseller_packages')->where('reseller_id', $this->reseller->id)->orderBy('created_at', 'DESC')->get() ?: [];
        return $this->view('user.reseller.packages', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Packages', 'packages' => $pkgs,
        ]);
    }

    public function packageStore()
    {
        $u = $this->requireReseller();
        if ($this->staff && !$this->can('packages')) { $_SESSION['error_message'] = 'No permission.'; $this->response->redirect('/reseller'); exit; }
        $name = trim($this->request->post('name', ''));
        if ($name === '') { $_SESSION['error_message'] = 'Package name required.'; $this->response->redirect('/reseller/packages'); exit; }
        $type = $this->request->post('type', 'hosting');
        $userName = (string)($u->name ?? 'reseller');
        // public id: {username}_{name} — unique per reseller (the real public identifier)
        $slugBase = strtolower(preg_replace('/[^a-z0-9]+/','', strtolower($userName))) . '_' . strtolower(preg_replace('/[^a-z0-9]+/','_', strtolower($name)));
        $slug = $slugBase;
        $i = 1;
        while ($this->db->table('reseller_packages')->where('slug', $slug)->first()) { $slug = $slugBase . '_' . ($i++); }
        $data = [
            'reseller_id' => $this->reseller->id, 'name' => $name, 'slug' => $slug, 'type' => $type,
            'description' => $this->request->post('description', ''),
            'price' => (float)$this->request->post('price', 0),
            'setup_fee' => (float)$this->request->post('setup_fee', 0),
            'billing_cycle' => $this->request->post('billing_cycle', 'monthly'),
            'slots' => (int)$this->request->post('slots', 10),
            'disk_space' => (int)$this->request->post('disk_space', 0),
            'bandwidth' => (int)$this->request->post('bandwidth', 0),
            'storage_limit' => (int)$this->request->post('storage_limit', 0),
            'backup_limit' => (int)$this->request->post('backup_limit', 0),
            'database_limit' => (int)$this->request->post('database_limit', 0),
            'port_limit' => (int)$this->request->post('port_limit', 0),
            'player_slots' => (int)$this->request->post('player_slots', 0),
            'max_stations' => (int)$this->request->post('max_stations', 0),
            'max_djs' => (int)$this->request->post('max_djs', 0),
            'max_listeners' => (int)$this->request->post('max_listeners', 0),
            'max_bitrate' => (int)$this->request->post('max_bitrate', 0),
            'features' => json_encode($this->request->post('features', [])) ?: null,
            'allowed_games' => json_encode($this->request->post('allowed_games', [])) ?: null,
            'is_active' => $this->request->post('is_active', 1) ? 1 : 0,
        ];
        $this->db->table('reseller_packages')->insertGetId($data);
        $this->audit('package.created', 'reseller_package', null, ['name' => $name, 'slug' => $slug, 'type' => $type]);
        $_SESSION['success_message'] = "Package '{$name}' created (public: {$slug}).";
        $this->response->redirect('/reseller/packages');
    }

    public function packageUpdate($id)
    {
        $u = $this->requireReseller();
        if ($this->staff && !$this->can('packages')) { $_SESSION['error_message'] = 'No permission.'; $this->response->redirect('/reseller'); exit; }
        $pkg = $this->db->table('reseller_packages')->where('id', (int)$id)->where('reseller_id', $this->reseller->id)->first();
        if (!$pkg) { $_SESSION['error_message'] = 'Package not found.'; $this->response->redirect('/reseller/packages'); exit; }
        $name = trim($this->request->post('name', $pkg->name));
        $data = [
            'name' => $name,
            'type' => $this->request->post('type', $pkg->type),
            'description' => $this->request->post('description', $pkg->description),
            'price' => (float)$this->request->post('price', 0),
            'setup_fee' => (float)$this->request->post('setup_fee', 0),
            'billing_cycle' => $this->request->post('billing_cycle', $pkg->billing_cycle),
            'slots' => (int)$this->request->post('slots', 10),
            'disk_space' => (int)$this->request->post('disk_space', 0),
            'bandwidth' => (int)$this->request->post('bandwidth', 0),
            'storage_limit' => (int)$this->request->post('storage_limit', 0),
            'backup_limit' => (int)$this->request->post('backup_limit', 0),
            'database_limit' => (int)$this->request->post('database_limit', 0),
            'port_limit' => (int)$this->request->post('port_limit', 0),
            'player_slots' => (int)$this->request->post('player_slots', 0),
            'max_stations' => (int)$this->request->post('max_stations', 0),
            'max_djs' => (int)$this->request->post('max_djs', 0),
            'max_listeners' => (int)$this->request->post('max_listeners', 0),
            'max_bitrate' => (int)$this->request->post('max_bitrate', 0),
            'features' => json_encode($this->request->post('features', [])) ?: null,
            'allowed_games' => json_encode($this->request->post('allowed_games', [])) ?: null,
            'is_active' => $this->request->post('is_active', 1) ? 1 : 0,
        ];
        $this->db->table('reseller_packages')->where('id', (int)$id)->update($data);
        $this->audit('package.updated', 'reseller_package', (int)$id, ['name' => $name]);
        $_SESSION['success_message'] = 'Package updated.';
        $this->response->redirect('/reseller/packages');
    }

    public function packageDelete($id)
    {
        $u = $this->requireReseller();
        if ($this->staff && !$this->can('packages')) { $_SESSION['error_message'] = 'No permission.'; $this->response->redirect('/reseller'); exit; }
        $this->db->table('reseller_packages')->where('id', (int)$id)->where('reseller_id', $this->reseller->id)->delete();
        $this->audit('package.deleted', 'reseller_package', (int)$id);
        $_SESSION['success_message'] = 'Package deleted.';
        $this->response->redirect('/reseller/packages');
    }

    public function branding()
    {
        $u = $this->requireReseller();
        if ($this->staff && !$this->can('branding')) { $_SESSION['error_message'] = 'No permission.'; $this->response->redirect('/reseller'); exit; }
        return $this->view('user.reseller.branding', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Branding',
        ]);
    }

    public function provisioning()
    {
        $u = $this->requireReseller();
        if ($this->staff && !$this->can('provisioning')) { $_SESSION['error_message'] = 'No permission.'; $this->response->redirect('/reseller'); exit; }
        $pdo = $this->db->pdo();
        $rid = (int)$this->reseller->id;
        // Orders from this reseller's own customers (hosting_users.reseller_id)
        $stmt = $pdo->query("SELECT o.*, hu.username, hu.domain, hu.status AS account_status
            FROM billing_orders o
            JOIN hosting_users hu ON hu.id = o.user_id
            WHERE hu.reseller_id = {$rid}
            ORDER BY o.created_at DESC LIMIT 100");
        $orders = $stmt ? $stmt->fetchAll(\PDO::FETCH_OBJ) : [];
        $pkgNames = [];
        $provisioned = 0; $pending = 0;
        foreach ($orders as $o) {
            foreach (['package_id', 'product_id'] as $f) {
                if (!empty($o->{$f})) {
                    $p = $this->db->table('hosting_packages')->where('id', (int)$o->{$f})->first();
                    if ($p) { $pkgNames[$o->id] = $p->name; break; }
                    $bp = $this->db->table('billing_products')->where('id', (int)$o->{$f})->first();
                    if ($bp) { $pkgNames[$o->id] = $bp->name; break; }
                }
            }
            if (in_array($o->status, ['active', 'completed'])) $provisioned++;
            elseif (in_array($o->status, ['pending', 'unpaid'])) $pending++;
        }
        // Pending clients created directly via Create Client (no order) — need activation.
        $pendingClients = $this->db->table('hosting_users')
            ->where('reseller_id', $rid)->where('status', 'pending')
            ->orderBy('created_at', 'DESC')->get() ?: [];
        $pendingClients = array_filter($pendingClients, fn($c) => !in_array($c->id, array_map(fn($o) => (int)$o->user_id, $orders), true) ? $c : null);
        $pending += count($pendingClients);
        return $this->view('user.reseller.provisioning', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Provisioning',
            'orders' => $orders, 'pkgNames' => $pkgNames, 'provisioned' => $provisioned, 'pending' => $pending,
            'pendingClients' => $pendingClients,
        ]);
    }

    // Activate a client account created directly under this reseller (no order involved).
    public function provisioningClientRun($clientId)
    {
        $u = $this->requireReseller();
        if ($this->staff && !$this->can('provisioning')) { $_SESSION['error_message'] = 'No permission.'; $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $client = $this->db->table('hosting_users')->where('id', (int)$clientId)->where('reseller_id', $rid)->first();
        if (!$client) { $_SESSION['error_message'] = 'Client not found.'; $this->response->redirect('/reseller/provisioning'); exit; }
        try {
            // Planet Hosts backend provisioning pipeline creates the OS account, vhost, DNS, radio dirs.
            $pkgId = (int)$client->package_id;
            if ($client->reseller_package_id) {
                $rp = $this->db->table('reseller_packages')->where('id', (int)$client->reseller_package_id)->first();
                $pkgId = $rp && $rp->id ? (int)$rp->id : $pkgId;
            }
            require_once BASE_PATH . '/services/AutoProvision.php';
            // autoProvision keys off hosting_packages id; complete it with pkg lookup where possible
            $done = false;
            $pid = $pkgId ?: null;
            $pkg = $pid ? $this->db->table('hosting_packages')->where('id', $pid)->first() : null;
            if ($pkg) {
                autoProvision((int)$client->id, $pid);
                if (stripos($pkg->type ?? '', 'icecast') !== false) {
                    require_once BASE_PATH . '/services/RadioProvision.php';
                    radioProvision((int)$client->id, $pid);
                }
                $done = true;
            } else {
                $this->db->table('hosting_users')->where('id', (int)$client->id)->update(['status' => 'active']);
                $done = true;
            }
            $this->audit('client.provisioned', 'hosting_user', (int)$client->id, ['username' => $client->username]);
            $_SESSION['success_message'] = "Client '{$client->username}' provisioned through Planet Hosts backend.";
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Provisioning failed: ' . $e->getMessage();
        }
        $this->response->redirect('/reseller/provisioning');
    }

    // Reseller triggers provisioning for one of THEIR OWN customers' orders.
    // Runs through Planet Hosts backend (same pipeline as paid orders) — reseller never needs SSH/root.
    public function provisioningRun($orderId)
    {
        $u = $this->requireReseller();
        if ($this->staff && !$this->can('provisioning')) { $_SESSION['error_message'] = 'No permission.'; $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $order = $this->db->table('billing_orders')->where('id', (int)$orderId)->first();
        if (!$order) { $_SESSION['error_message'] = 'Order not found.'; $this->response->redirect('/reseller/provisioning'); exit; }
        $owner = $this->db->table('hosting_users')->where('id', $order->user_id)->where('reseller_id', $this->reseller->id)->first();
        if (!$owner) { $_SESSION['error_message'] = 'This order is not linked to your reseller account.'; $this->response->redirect('/reseller/provisioning'); exit; }
        try {
            $order->{'items'} = $order->items ?? null;
            require_once BASE_PATH . '/services/AutoProvision.php';
            require_once BASE_PATH . '/services/GameProvision.php';
            $items = json_decode((string)$order->items, true);
            if (!empty($items)) {
                $hostingProvisioned = false;
                foreach ($items as $item) {
                    $type = $item['type'] ?? 'hosting';
                    if ($type === 'game') {
                        gameProvision((int)$order->id, (int)$order->user_id, $item);
                    } elseif (!$hostingProvisioned) {
                        $pkgId = $item['id'] ?? null;
                        if ($pkgId && !is_string($pkgId)) {
                            autoProvision((int)$order->user_id, (int)$pkgId);
                            $pkg = $this->db->table('hosting_packages')->where('id', (int)$pkgId)->first();
                            if ($pkg && stripos($pkg->type ?? '', 'icecast') !== false) {
                                require_once BASE_PATH . '/services/RadioProvision.php';
                                radioProvision((int)$order->user_id, (int)$pkgId);
                            }
                        }
                        $hostingProvisioned = true;
                    }
                }
            }
            $this->db->table('billing_orders')->where('id', $order->id)->update(['status' => 'active']);
            $this->db->table('hosting_users')->where('id', $owner->id)->update(['status' => 'active']);
            $this->audit('order.provisioned', 'billing_order', (int)$order->id, ['user_id' => $order->user_id, 'total' => $order->total]);
            $_SESSION['success_message'] = "Order #{$order->id} provisioned through Planet Hosts backend.";
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Provisioning failed: ' . $e->getMessage();
        }
        $this->response->redirect('/reseller/provisioning');
    }

    public function billing()
    {
        $u = $this->requireReseller();
        $invoices = $this->db->table('invoices')->where('reseller_id', $this->reseller->id)->get() ?: [];
        $totalOwed = 0;
        foreach ($invoices as $inv) { if ($inv->status === 'sent' || $inv->status === 'overdue') $totalOwed += $inv->total; }
        return $this->view('user.reseller.billing', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Billing Overview', 'invoices' => $invoices, 'totalOwed' => $totalOwed,
        ]);
    }

    public function support()
    {
        $u = $this->requireReseller();
        // Only tickets from this reseller's own customers
        try {
            $pdo = $this->db->pdo();
            $pdo->prepare("CREATE TEMPORARY TABLE tmp_rsel_ids AS SELECT id FROM hosting_users WHERE reseller_id=?")->execute([$this->reseller->id]);
            $stmt = $pdo->query("SELECT t.id, t.subject, t.status, t.created_at, hu.username AS customer FROM tickets t JOIN hosting_users hu ON hu.id=t.user_id WHERE hu.reseller_id=" . (int)$this->reseller->id . " ORDER BY t.created_at DESC");
            $tickets = $stmt ? $stmt->fetchAll(\PDO::FETCH_OBJ) : [];
        } catch (\Exception $e) {
            $tickets = [];
        }
        return $this->view('user.reseller.support', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Support Tickets', 'tickets' => $tickets,
        ]);
    }

    // ── Addon gating ──
    // Which addon tools (billing/chat/support) the reseller may use = union of the
    // feature flags granted across their own active retail packages. A reseller who
    // sells chat packages gets the Chat tool, etc. Never shows admin/root scope.
    protected function enabledAddons()
    {
        $addons = ['billing' => false, 'chat' => false, 'support' => false];
        try {
            $pkgs = $this->db->table('reseller_packages')
                ->where('reseller_id', $this->reseller->id)
                ->where('is_active', 1)->get() ?: [];
            foreach ($pkgs as $p) {
                $feats = is_string($p->features ?? null) ? json_decode($p->features, true) : ($p->features ?? null);
                if (is_array($feats)) {
                    foreach (['billing','chat','support'] as $k) {
                        if (in_array($k, $feats, true)) $addons[$k] = true;
                    }
                }
            }
        } catch (\Exception $e) {}
        return $addons;
    }

    // ── Addon: Billing system (reseller bills THEIR clients) ──
    // Mirrors the master admin billing dashboard layout, but every query is scoped
    // to this reseller's own clients (hosting_users.reseller_id = theirs).
    public function clientBilling()
    {
        $u = $this->requireReseller();
        $this->requirePerm('billing');
        $addons = $this->enabledAddons();
        if (!$addons['billing']) { $_SESSION['error_message'] = 'The Billing addon is not enabled on your packages.'; $this->response->redirect('/reseller'); exit; }
        $pdo = $this->db->pdo();
        $rid = (int)$this->reseller->id;
        $ids = $this->clientIds();
        $in = $ids ? implode(',', $ids) : '0';

        $totalCollected = (float)($pdo->query("SELECT COALESCE(SUM(bp.amount),0) FROM billing_payments bp JOIN hosting_users hu ON hu.id=bp.user_id WHERE bp.status='completed' AND hu.reseller_id={$rid}")->fetchColumn() ?? 0);
        $outstanding = (float)($pdo->query("SELECT COALESCE(SUM(i.total),0) FROM invoices i JOIN hosting_users hu ON hu.id=i.user_id WHERE i.status IN ('sent','overdue','pending') AND hu.reseller_id={$rid}")->fetchColumn() ?? 0);
        $mrr = (float)($pdo->query("SELECT COALESCE(SUM(s.price),0) FROM billing_services s JOIN hosting_users hu ON hu.id=s.user_id WHERE s.status='active' AND hu.reseller_id={$rid}")->fetchColumn() ?? 0);
        $activeServices = (int)($pdo->query("SELECT COUNT(*) FROM billing_services s JOIN hosting_users hu ON hu.id=s.user_id WHERE s.status='active' AND hu.reseller_id={$rid}")->fetchColumn() ?? 0);

        $counts = [
            'orders' => (int)($pdo->query("SELECT COUNT(*) FROM billing_orders o JOIN hosting_users hu ON hu.id=o.user_id WHERE hu.reseller_id={$rid}")->fetchColumn() ?? 0),
            'services' => (int)($pdo->query("SELECT COUNT(*) FROM billing_services s JOIN hosting_users hu ON hu.id=s.user_id WHERE hu.reseller_id={$rid}")->fetchColumn() ?? 0),
            'invoices' => (int)($pdo->query("SELECT COUNT(*) FROM invoices i JOIN hosting_users hu ON hu.id=i.user_id WHERE hu.reseller_id={$rid}")->fetchColumn() ?? 0),
            'payments' => (int)($pdo->query("SELECT COUNT(*) FROM billing_payments bp JOIN hosting_users hu ON hu.id=bp.user_id WHERE hu.reseller_id={$rid}")->fetchColumn() ?? 0),
            'credits' => (int)($pdo->query("SELECT COUNT(*) FROM billing_credits c JOIN hosting_users hu ON hu.id=c.user_id WHERE hu.reseller_id={$rid}")->fetchColumn() ?? 0),
            'refunds' => (int)($pdo->query("SELECT COUNT(*) FROM billing_refunds r JOIN hosting_users hu ON hu.id=r.user_id WHERE hu.reseller_id={$rid}")->fetchColumn() ?? 0),
        ];

        $invoices = $pdo->query("SELECT i.*, hu.username AS client FROM invoices i
            JOIN hosting_users hu ON hu.id = i.user_id
            WHERE hu.reseller_id = {$rid} ORDER BY i.created_at DESC LIMIT 200")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        $clients = $this->db->table('hosting_users')->where('reseller_id', $rid)->get() ?: [];
        return $this->view('user.reseller.client_billing', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Billing System',
            'addons' => $addons, 'invoices' => $invoices, 'clients' => $clients,
            'totalCollected' => $totalCollected, 'outstanding' => $outstanding, 'mrr' => $mrr, 'activeServices' => $activeServices,
            'counts' => $counts,
        ]);
    }

    protected function clientIds()
    {
        $ids = [];
        foreach ($this->db->table('hosting_users')->where('reseller_id', $this->reseller->id)->get() ?: [] as $c) { $ids[] = (int)$c->id; }
        return $ids;
    }

    public function clientBillingOrders()
    {
        $u = $this->requireReseller();
        $this->requirePerm('billing');
        $addons = $this->enabledAddons();
        if (!$addons['billing']) { $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $orders = $this->db->pdo()->query("SELECT o.*, hu.username AS client FROM billing_orders o JOIN hosting_users hu ON hu.id=o.user_id WHERE hu.reseller_id={$rid} ORDER BY o.created_at DESC LIMIT 300")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        return $this->view('user.reseller.client_billing_orders', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Billing System', 'addons' => $addons, 'orders' => $orders,
        ]);
    }

    public function clientBillingServices()
    {
        $u = $this->requireReseller();
        $this->requirePerm('billing');
        $addons = $this->enabledAddons();
        if (!$addons['billing']) { $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $services = $this->db->pdo()->query("SELECT s.*, hu.username AS client, bp.name AS product_name FROM billing_services s LEFT JOIN hosting_users hu ON hu.id=s.user_id LEFT JOIN billing_products bp ON s.product_id=bp.id WHERE hu.reseller_id={$rid} ORDER BY s.id DESC LIMIT 300")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        return $this->view('user.reseller.client_billing_services', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Billing System', 'addons' => $addons, 'services' => $services,
        ]);
    }

    public function clientBillingPayments()
    {
        $u = $this->requireReseller();
        $this->requirePerm('billing');
        $addons = $this->enabledAddons();
        if (!$addons['billing']) { $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $payments = $this->db->pdo()->query("SELECT bp.*, hu.username AS client FROM billing_payments bp JOIN hosting_users hu ON hu.id=bp.user_id WHERE hu.reseller_id={$rid} ORDER BY bp.id DESC LIMIT 300")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        return $this->view('user.reseller.client_billing_payments', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Billing System', 'addons' => $addons, 'payments' => $payments,
        ]);
    }

    public function clientBillingCredits()
    {
        $u = $this->requireReseller();
        $this->requirePerm('billing');
        $addons = $this->enabledAddons();
        if (!$addons['billing']) { $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $credits = $this->db->pdo()->query("SELECT c.*, hu.username AS client FROM billing_credits c JOIN hosting_users hu ON hu.id=c.user_id WHERE hu.reseller_id={$rid} ORDER BY c.id DESC LIMIT 300")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        $clients = $this->db->table('hosting_users')->where('reseller_id', $rid)->get() ?: [];
        return $this->view('user.reseller.client_billing_credits', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Billing System', 'addons' => $addons, 'credits' => $credits, 'clients' => $clients,
        ]);
    }

    public function clientBillingCreditStore()
    {
        $u = $this->requireReseller();
        $this->requirePerm('billing');
        $addons = $this->enabledAddons();
        if (!$addons['billing']) { $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $clientId = (int)$this->request->post('client_id', 0);
        $client = $this->db->table('hosting_users')->where('id', $clientId)->where('reseller_id', $rid)->first();
        if (!$client) { $_SESSION['error_message'] = 'Client not found.'; $this->response->redirect('/reseller/billing-system/credits'); exit; }
        $this->db->table('billing_credits')->insertGetId([
            'user_id' => $clientId, 'amount' => (float)$this->request->post('amount', 0),
            'description' => $this->request->post('description', ''),
        ]);
        $this->audit('billing.credit_added', 'billing_credit', $this->db->lastInsertId(), ['client' => $clientId]);
        $_SESSION['success_message'] = "Credit added to {$client->username}.";
        $this->response->redirect('/reseller/billing-system/credits');
    }

    public function clientBillingRefunds()
    {
        $u = $this->requireReseller();
        $this->requirePerm('billing');
        $addons = $this->enabledAddons();
        if (!$addons['billing']) { $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $refunds = $this->db->pdo()->query("SELECT r.*, hu.username AS client FROM billing_refunds r JOIN hosting_users hu ON hu.id=r.user_id WHERE hu.reseller_id={$rid} ORDER BY r.id DESC LIMIT 300")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        return $this->view('user.reseller.client_billing_refunds', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Billing System', 'addons' => $addons, 'refunds' => $refunds,
        ]);
    }

    public function clientBillingCreate()
    {
        $u = $this->requireReseller();
        $this->requirePerm('billing');
        $addons = $this->enabledAddons();
        if (!$addons['billing']) { $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $clientId = (int)$this->request->post('client_id', 0);
        $client = $this->db->table('hosting_users')->where('id', $clientId)->where('reseller_id', $rid)->first();
        if (!$client) { $_SESSION['error_message'] = 'Client not found.'; $this->response->redirect('/reseller/billing-system'); exit; }
        $desc = trim($this->request->post('description', ''));
        $amount = (float)$this->request->post('total', 0);
        if ($amount <= 0) { $_SESSION['error_message'] = 'Amount must be greater than zero.'; $this->response->redirect('/reseller/billing-system'); exit; }
        $num = 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $id = $this->db->table('invoices')->insertGetId([
            'user_id' => $clientId, 'reseller_id' => $rid, 'invoice_number' => $num,
            'date' => date('Y-m-d'), 'due_date' => date('Y-m-d', strtotime('+14 days')),
            'subtotal' => $amount, 'tax_rate' => 0, 'tax_amount' => 0, 'total' => $amount,
            'status' => 'sent', 'notes' => $desc,
        ]);
        $this->audit('billing.invoice_issued', 'invoice', $id, ['client' => $clientId, 'total' => $amount, 'number' => $num]);
        $_SESSION['success_message'] = "Invoice {$num} issued to {$client->username}.";
        $this->response->redirect('/reseller/billing-system');
    }

    public function clientBillingMarkPaid($id)
    {
        $u = $this->requireReseller();
        $this->requirePerm('billing');
        $addons = $this->enabledAddons();
        if (!$addons['billing']) { $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $inv = $this->db->table('invoices')->where('id', (int)$id)->where('reseller_id', $rid)->first();
        if ($inv) {
            $this->db->table('invoices')->where('id', (int)$id)->update(['status' => 'paid']);
            $this->audit('billing.invoice_paid', 'invoice', (int)$id);
            $_SESSION['success_message'] = "Invoice #{$inv->invoice_number} marked paid.";
        }
        $this->response->redirect('/reseller/billing-system');
    }

    // ── Admin shared billing tabs helper ──
    protected function billingTabs()
    {
        return [
            ['url' => '/reseller/billing-system', 'label' => '📊 Dashboard'],
            ['url' => '/reseller/billing-system/orders', 'label' => '📋 Orders'],
            ['url' => '/reseller/billing-system/services', 'label' => '🖥 Services'],
            ['url' => '/reseller/billing-system', 'label' => '💰 Invoices'],
            ['url' => '/reseller/billing-system/payments', 'label' => '💳 Payments'],
            ['url' => '/reseller/billing-system/credits', 'label' => '🏦 Credits'],
            ['url' => '/reseller/billing-system/refunds', 'label' => '↩️ Refunds'],
        ];
    }

    // ── Addon: Chat system (reseller manages their clients' chatbox tenants) ──
    public function clientChat()
    {
        $u = $this->requireReseller();
        $this->requirePerm('chat');
        $addons = $this->enabledAddons();
        if (!$addons['chat']) { $_SESSION['error_message'] = 'The Chat addon is not enabled on your packages.'; $this->response->redirect('/reseller'); exit; }
        $pdo = $this->db->pdo();
        $rid = (int)$this->reseller->id;
        $rows = $pdo->query("SELECT hu.id AS user_id, hu.username, ct.id AS tenant_id, ct.widget_title, ct.widget_color, ct.is_active
            FROM hosting_users hu LEFT JOIN chatbox_tenants ct ON ct.hosting_user_id = hu.id
            WHERE hu.reseller_id = {$rid} ORDER BY hu.username")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        return $this->view('user.reseller.client_chat', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Chat System',
            'addons' => $addons, 'clients' => $rows,
        ]);
    }

    public function clientChatCreateTenant()
    {
        $u = $this->requireReseller();
        $this->requirePerm('chat');
        $addons = $this->enabledAddons();
        if (!$addons['chat']) { $this->response->redirect('/reseller'); exit; }
        $rid = (int)$this->reseller->id;
        $clientId = (int)$this->request->post('client_id', 0);
        $client = $this->db->table('hosting_users')->where('id', $clientId)->where('reseller_id', $rid)->first();
        if (!$client) { $_SESSION['error_message'] = 'Client not found.'; $this->response->redirect('/reseller/chat-system'); exit; }
        $existing = $this->db->table('chatbox_tenants')->where('hosting_user_id', $clientId)->first();
        if ($existing) { $_SESSION['error_message'] = 'This client already has a chat tenant.'; $this->response->redirect('/reseller/chat-system'); exit; }
        $tid = $this->db->table('chatbox_tenants')->insertGetId([
            'hosting_user_id' => $clientId, 'name' => $client->username . '\'s Chat',
            'widget_title' => 'Live Chat', 'widget_color' => '#008cff', 'widget_bg' => '#0a0e1a',
            'widget_text_color' => '#ffffff', 'font_family' => 'Inter, sans-serif',
            'guest_enabled' => 1, 'registration_enabled' => 1, 'voice_enabled' => 0,
            'max_rooms' => 5, 'message_limit_days' => 30, 'is_active' => 1,
        ]);
        $this->audit('chat.tenant_created', 'chatbox_tenant', $tid, ['client' => $clientId]);
        $_SESSION['success_message'] = "Chat box created for {$client->username}. Embed: /chatbox/widget.js.php?tenant_id={$tid}";
        $this->response->redirect('/reseller/chat-system');
    }

    public function clientChatToggle($tenantId)
    {
        $u = $this->requireReseller();
        $this->requirePerm('chat');
        $addons = $this->enabledAddons();
        if (!$addons['chat']) { $this->response->redirect('/reseller'); exit; }
        $pdo = $this->db->pdo();
        $t = $pdo->prepare("SELECT ct.* FROM chatbox_tenants ct JOIN hosting_users hu ON hu.id = ct.hosting_user_id WHERE ct.id = ? AND hu.reseller_id = ?");
        $t->execute([(int)$tenantId, (int)$this->reseller->id]);
        $tenant = $t->fetch(\PDO::FETCH_OBJ);
        if ($tenant) {
            $this->db->table('chatbox_tenants')->where('id', (int)$tenantId)->update(['is_active' => $tenant->is_active ? 0 : 1]);
            $this->audit('chat.tenant_toggled', 'chatbox_tenant', (int)$tenantId, ['is_active' => $tenant->is_active ? 0 : 1]);
        }
        $this->response->redirect('/reseller/chat-system');
    }

    // ── Addon: Support system (reseller manages THEIR clients' tickets, already routed to admin) ──
    public function clientSupport()
    {
        $u = $this->requireReseller();
        $this->requirePerm('support');
        $addons = $this->enabledAddons();
        if (!$addons['support']) { $_SESSION['error_message'] = 'The Support addon is not enabled on your packages.'; $this->response->redirect('/reseller'); exit; }
        $pdo = $this->db->pdo();
        $rid = (int)$this->reseller->id;
        $stmt = $pdo->query("SELECT t.id, t.subject, t.status, t.priority, t.created_at, hu.username AS customer
            FROM tickets t JOIN hosting_users hu ON hu.id = t.user_id
            WHERE hu.reseller_id = {$rid} ORDER BY t.created_at DESC LIMIT 200");
        $tickets = $stmt ? $stmt->fetchAll(\PDO::FETCH_OBJ) : [];
        return $this->view('user.reseller.client_support', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Support System',
            'addons' => $addons, 'tickets' => $tickets,
        ]);
    }

    // ── Roles & Staff (mirrors admin Admins/Roles, scoped to this reseller) ──
    public function roles()
    {
        $u = $this->requireReseller();
        $this->requirePerm('staff');
        $rid = (int)$this->reseller->id;
        $staff = $this->db->table('reseller_staff')->where('reseller_id', $rid)->orderBy('id', 'ASC')->get() ?: [];
        $roles = $this->db->table('reseller_roles')->where('reseller_id', $rid)->orderBy('name', 'ASC')->get() ?: [];
        // Attach role templates per staff member
        $staffRoles = [];
        try {
            $q = $this->db->pdo()->query("SELECT sr.staff_id, sr.role_id FROM reseller_staff_roles sr WHERE sr.reseller_id = {$rid}");
            foreach ($q->fetchAll(\PDO::FETCH_OBJ) as $row) $staffRoles[(int)$row->staff_id][] = (int)$row->role_id;
        } catch (\Exception $e) {}
        $permMap = [
            'clients' => '👥 Clients', 'packages' => '📦 Packages', 'provisioning' => '⚙️ Provisioning',
            'billing' => '💰 Billing', 'chat' => '💬 Chat', 'support' => '🎧 Support',
            'branding' => '🎨 Branding', 'staff' => '🛡️ Staff & Roles',
        ];
        return $this->view('user.reseller.roles', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Roles & Staff',
            'staff' => $staff, 'roles' => $roles, 'staffRoles' => $staffRoles, 'permMap' => $permMap,
        ]);
    }

    public function roleStore()
    {
        $u = $this->requireReseller();
        $this->requirePerm('staff');
        $name = trim($this->request->post('name', ''));
        if ($name === '') { $_SESSION['error_message'] = 'Role name is required.'; $this->response->redirect('/reseller/roles'); exit; }
        $rid = (int)$this->reseller->id;
        $id = $this->db->table('reseller_roles')->insertGetId([
            'reseller_id' => $rid, 'name' => $name,
            'description' => $this->request->post('description', ''),
            'permissions' => json_encode($this->request->post('permissions', [])),
            'is_active' => 1,
        ]);
        $this->audit('staff.role_created', 'reseller_role', $id, ['name' => $name]);
        $_SESSION['success_message'] = "Role '{$name}' created.";
        $this->response->redirect('/reseller/roles');
    }

    public function roleUpdate($id)
    {
        $u = $this->requireReseller();
        $this->requirePerm('staff');
        $rid = (int)$this->reseller->id;
        $role = $this->db->table('reseller_roles')->where('id', (int)$id)->where('reseller_id', $rid)->first();
        if (!$role) { $_SESSION['error_message'] = 'Role not found.'; $this->response->redirect('/reseller/roles'); exit; }
        $this->db->table('reseller_roles')->where('id', (int)$id)->update([
            'name' => trim($this->request->post('name', $role->name)),
            'description' => $this->request->post('description', $role->description),
            'permissions' => json_encode($this->request->post('permissions', [])),
            'is_active' => (int)$this->request->post('is_active', 1),
        ]);
        $this->audit('staff.role_updated', 'reseller_role', (int)$id, ['name' => $role->name]);
        $_SESSION['success_message'] = 'Role updated.';
        $this->response->redirect('/reseller/roles');
    }

    public function roleDelete($id)
    {
        $u = $this->requireReseller();
        $this->requirePerm('staff');
        $rid = (int)$this->reseller->id;
        $role = $this->db->table('reseller_roles')->where('id', (int)$id)->where('reseller_id', $rid)->first();
        if ($role) {
            $this->db->table('reseller_staff_roles')->where('role_id', (int)$id)->where('reseller_id', $rid)->delete();
            $this->db->table('reseller_roles')->where('id', (int)$id)->delete();
            $this->audit('staff.role_deleted', 'reseller_role', (int)$id, ['name' => $role->name]);
            $_SESSION['success_message'] = 'Role deleted.';
        }
        $this->response->redirect('/reseller/roles');
    }

    public function staffStore()
    {
        $u = $this->requireReseller();
        $this->requirePerm('staff');
        $rid = (int)$this->reseller->id;
        $email = trim($this->request->post('email', ''));
        $name = trim($this->request->post('name', ''));
        $password = $this->request->post('password', '');
        $role = $this->request->post('role', 'support');
        if (!in_array($role, ['owner','manager','support','billing','technician'], true)) $role = 'support';
        if ($email === '' || $password === '') {
            $_SESSION['error_message'] = 'Staff email and password are required.';
            $this->response->redirect('/reseller/roles'); exit;
        }
        $exists = $this->db->table('reseller_staff')->where('reseller_id', $rid)->where('email', $email)->first();
        if ($exists) { $_SESSION['error_message'] = 'That staff email is already in use.'; $this->response->redirect('/reseller/roles'); exit; }
        $sid = $this->db->table('reseller_staff')->insertGetId([
            'reseller_id' => $rid, 'name' => $name, 'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role, 'permissions' => json_encode($this->request->post('permissions', [])),
            'is_active' => 1,
        ]);
        // Attach custom role templates
        foreach ((array)$this->request->post('role_ids', []) as $roleId) {
            $roleId = (int)$roleId;
            $owned = $this->db->table('reseller_roles')->where('id', $roleId)->where('reseller_id', $rid)->first();
            if ($owned) {
                $this->db->pdo()->prepare("INSERT IGNORE INTO reseller_staff_roles (reseller_id, staff_id, role_id) VALUES (?,?,?)")
                    ->execute([$rid, $sid, $roleId]);
            }
        }
        $this->audit('staff.created', 'reseller_staff', $sid, ['email' => $email, 'role' => $role]);
        $_SESSION['success_message'] = "Staff member '{$email}' added ({$role}). They can log in at the reseller portal.";
        $this->response->redirect('/reseller/roles');
    }

    public function staffToggle($id)
    {
        $u = $this->requireReseller();
        $this->requirePerm('staff');
        $rid = (int)$this->reseller->id;
        $s = $this->db->table('reseller_staff')->where('id', (int)$id)->where('reseller_id', $rid)->first();
        if ($s && $s->role !== 'owner') {
            $this->db->table('reseller_staff')->where('id', (int)$id)->update(['is_active' => $s->is_active ? 0 : 1]);
            $this->audit('staff.toggled', 'reseller_staff', (int)$id);
        }
        $this->response->redirect('/reseller/roles');
    }

    public function staffDelete($id)
    {
        $u = $this->requireReseller();
        $this->requirePerm('staff');
        $rid = (int)$this->reseller->id;
        $s = $this->db->table('reseller_staff')->where('id', (int)$id)->where('reseller_id', $rid)->first();
        if ($s && $s->role !== 'owner') {
            $this->db->table('reseller_staff_roles')->where('staff_id', (int)$id)->where('reseller_id', $rid)->delete();
            $this->db->table('reseller_staff')->where('id', (int)$id)->delete();
            $this->audit('staff.deleted', 'reseller_staff', (int)$id);
            $_SESSION['success_message'] = 'Staff member removed.';
        }
        $this->response->redirect('/reseller/roles');
    }

    protected function audit($action = 'action', $resourceType = null, $resourceId = null, $details = null)
    {
        try {
            $u = $this->auth->user();
            $who = $this->staff ? $this->staff->email : ($u->email ?? '$reseller');
            $this->db->table('reseller_audit_logs')->insertGetId([
                'reseller_id' => $this->reseller->id ?? 0,
                'staff_email' => $who,
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId ? (int)$resourceId : null,
                'details' => $details ? json_encode($details) : null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (\Exception $e) {}
    }
}
