<?php

namespace User\Controllers;

use Core\Controller;

class ResellerPortalController extends Controller
{
    protected $auth, $request, $response, $db;
    protected $reseller;

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
        if (!$this->auth->check()) { $this->response->redirect('https://planet-hosts.com:2089/user_login.php'); exit; }
        $user = $this->auth->user();
        // Admins / super admins hitting /reseller should go back to their admin dashboard
        if (isset($user->is_admin) && $user->is_admin) { $this->response->redirect('/admin'); exit; }
        $this->reseller = $this->db->table('resellers')->where('email', $user->email)->where('is_active', 1)->first();
        if (!$this->reseller) { $this->response->redirect('/user'); exit; }
        return $user;
    }

    public function dashboard()
    {
        $u = $this->requireReseller();
        $accounts = $this->db->table('hosting_users')->where('reseller_id', $this->reseller->id)->get() ?: [];
        $totalAccounts = count($accounts);
        $activeAccounts = 0;
        foreach ($accounts as $a) { if ($a->status === 'active') $activeAccounts++; }
        return $this->view('user.reseller.dashboard', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Reseller Dashboard',
            'totalAccounts' => $totalAccounts, 'activeAccounts' => $activeAccounts,
        ]);
    }

    public function clients()
    {
        $u = $this->requireReseller();
        $accounts = $this->db->table('hosting_users')->where('reseller_id', $this->reseller->id)->get() ?: [];
        $pkgNames = [];
        foreach ($accounts as $a) {
            $pkg = $this->db->table('hosting_packages')->where('id', $a->package_id)->first();
            $pkgNames[$a->id] = $pkg ? $pkg->name : '-';
        }
        return $this->view('user.reseller.clients', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Clients', 'accounts' => $accounts, 'pkgNames' => $pkgNames,
        ]);
    }

    public function packages()
    {
        $u = $this->requireReseller();
        // The reseller creates/manages their OWN retail packages. They never use server packages.
        $pkgs = $this->db->table('reseller_packages')->where('reseller_id', $this->reseller->id)->orderBy('created_at', 'DESC')->get() ?: [];
        return $this->view('user.reseller.packages', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Packages', 'packages' => $pkgs,
        ]);
    }

    public function packageStore()
    {
        $u = $this->requireReseller();
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
        $this->db->table('reseller_packages')->where('id', (int)$id)->where('reseller_id', $this->reseller->id)->delete();
        $this->audit('package.deleted', 'reseller_package', (int)$id);
        $_SESSION['success_message'] = 'Package deleted.';
        $this->response->redirect('/reseller/packages');
    }

    public function branding()
    {
        $u = $this->requireReseller();
        return $this->view('user.reseller.branding', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Branding',
        ]);
    }

    public function billing()
    {
        $u = $this->requireReseller();
        $invoices = $this->db->table('invoices')->where('reseller_id', $this->reseller->id)->get() ?: [];
        $totalOwed = 0;
        foreach ($invoices as $inv) { if ($inv->status === 'sent' || $inv->status === 'overdue') $totalOwed += $inv->total; }
        return $this->view('user.reseller.billing', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Billing', 'invoices' => $invoices, 'totalOwed' => $totalOwed,
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
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Support', 'tickets' => $tickets,
        ]);
    }

    protected function audit($action = 'action', $resourceType = null, $resourceId = null, $details = null)
    {
        try {
            $u = $this->auth->user();
            $this->db->table('reseller_audit_logs')->insertGetId([
                'reseller_id' => $this->reseller->id ?? 0,
                'staff_email' => $u->email ?? '$reseller',
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId ? (int)$resourceId : null,
                'details' => $details ? json_encode($details) : null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (\Exception $e) {}
    }
}
