<?php

namespace Admin\Controllers;

use Core\Controller;

class ResellerController extends Controller
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

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
    }

    protected function loadReseller($id)
    {
        $r = $this->db->table('resellers')->where('id', (int)$id)->first();
        if (!$r) { $_SESSION['error_message'] = 'Reseller not found.'; $this->response->redirect('/admin/reseller'); exit; }
        return $r;
    }

    // ── Package-derived types ──
    // Resellers are typed by the package they hold: web_reseller or icecast_reseller.
    // The master admin picks which reseller package defines cost/limits; the reseller
    // then retails Planet Hosts products to their own customers at their margins.
    public function index()
    {
        $this->guard();
        $user = $this->auth->user();
        $resellers = $this->db->table('resellers')->orderBy('id', 'ASC')->get() ?: [];
        $stmt = $this->db->pdo()->query("SELECT * FROM hosting_packages WHERE is_active = 1 AND type IN ('web_reseller','icecast_reseller') ORDER BY type ASC");
        $pkgs = $stmt ? $stmt->fetchAll(\PDO::FETCH_OBJ) : [];
        $pkgMap = [];
        foreach ($pkgs as $p) $pkgMap[$p->id] = $p;
        $total = count($resellers); $active = 0; $totalAccounts = 0; $acctCounts = [];
        foreach ($resellers as $r) {
            if ($r->is_active) $active++;
            $cnt = count($this->db->table('hosting_users')->where('reseller_id', $r->id)->get() ?: []);
            $totalAccounts += $cnt; $acctCounts[$r->id] = $cnt;
        }
        return $this->view('admin.reseller.index', [
            'user' => $user, 'title' => 'Reseller Management', 'resellers' => $resellers,
            'pkgMap' => $pkgMap, 'acctCounts' => $acctCounts,
            'resellerStats' => ['total_resellers' => $total, 'active_resellers' => $active, 'accounts_owned_by_resellers' => $totalAccounts],
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function create()
    {
        $this->guard();
        $user = $this->auth->user();
        $stmt = $this->db->pdo()->query("SELECT * FROM hosting_packages WHERE is_active = 1 AND type IN ('web_reseller','icecast_reseller') ORDER BY type ASC");
        $pkgs = $stmt ? $stmt->fetchAll(\PDO::FETCH_OBJ) : [];
        $featureLists = $this->db->table('feature_lists')->where('is_active', 1)->orderBy('name', 'ASC')->get() ?: [];
        return $this->view('admin.reseller.create', [
            'user' => $user, 'title' => 'Create Reseller', 'pkgs' => $pkgs, 'featureLists' => $featureLists,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function store()
    {
        $this->guard();
        $adminId = $this->auth->user()->id;
        $email = $this->request->post('email', '');
        $packageId = (int)$this->request->post('package_id', 0) ?: null;
        $type = 'web_reseller';
        if ($packageId) {
            $pkg = $this->db->table('hosting_packages')->where('id', $packageId)->first();
            if ($pkg) $type = in_array($pkg->type, ['icecast_reseller']) ? 'icecast_reseller' : 'web_reseller';
        }
        $rId = $this->db->table('resellers')->insertGetId([
            'admin_id' => $adminId,
            'company_name' => $this->request->post('company_name', ''),
            'contact_name' => $this->request->post('contact_name', ''),
            'email' => $email,
            'phone' => $this->request->post('phone', ''),
            'website' => $this->request->post('website', ''),
            'package_id' => $packageId,
            'type' => $type,
            'monthly_fee' => (float)$this->request->post('monthly_fee', 0),
            'billing_cycle' => $this->request->post('billing_cycle', 'monthly'),
            'feature_list_id' => (int)$this->request->post('feature_list_id', 0) ?: null,
            'features' => null,
            'is_active' => $this->request->post('is_active', 1) ? 1 : 0,
        ]);
        // Default resource limits from package type (upsell to market defaults)
        $base = $type === 'icecast_reseller'
            ? ['customers_limit' => 200, 'hosting_limit' => 200, 'radio_station_limit' => 25, 'game_server_limit' => 10, 'vps_limit' => 5, 'domain_limit' => 500, 'database_limit' => 500]
            : ['customers_limit' => 500, 'hosting_limit' => 500, 'radio_station_limit' => 100, 'game_server_limit' => 0, 'vps_limit' => 25, 'domain_limit' => 1000, 'database_limit' => 2000];
        $this->db->table('resellers')->where('id', $rId)->update($base);
        $_SESSION['success_message'] = "Reseller {$email} created ({$type}).";
        $this->response->redirect('/admin/reseller/show/' . $rId);
    }

    public function edit($id)
    {
        $this->guard();
        $user = $this->auth->user();
        $reseller = $this->loadReseller($id);
        $accounts = $this->db->table('hosting_users')->where('reseller_id', $id)->get() ?: [];
        $stmt = $this->db->pdo()->query("SELECT * FROM hosting_users WHERE reseller_id IS NULL OR reseller_id = 0");
        $unassigned = $stmt ? $stmt->fetchAll(\PDO::FETCH_OBJ) : [];
        $allAccounts = $this->db->table('hosting_users')->get() ?: [];
        $featureLists = $this->db->table('feature_lists')->where('is_active', 1)->orderBy('name', 'ASC')->get() ?: [];
        $stmt = $this->db->pdo()->query("SELECT * FROM hosting_packages WHERE is_active = 1 AND type IN ('web_reseller','icecast_reseller') ORDER BY type ASC");
        $pkgs = $stmt ? $stmt->fetchAll(\PDO::FETCH_OBJ) : [];
        return $this->view('admin.reseller.edit', [
            'user' => $user, 'title' => 'Edit Reseller', 'reseller' => $reseller,
            'accounts' => $accounts, 'unassigned' => $unassigned, 'allAccounts' => $allAccounts,
            'featureLists' => $featureLists, 'pkgs' => $pkgs,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function update($id)
    {
        $this->guard();
        $reseller = $this->loadReseller($id);
        $packageId = (int)$this->request->post('package_id', 0) ?: null;
        $type = $reseller->type;
        if ($packageId) {
            $pkg = $this->db->table('hosting_packages')->where('id', $packageId)->first();
            if ($pkg) $type = in_array($pkg->type, ['icecast_reseller']) ? 'icecast_reseller' : 'web_reseller';
        }
        $this->db->table('resellers')->where('id', $id)->update([
            'company_name' => $this->request->post('company_name', ''),
            'contact_name' => $this->request->post('contact_name', ''),
            'email' => $this->request->post('email', ''),
            'phone' => $this->request->post('phone', ''),
            'website' => $this->request->post('website', ''),
            'package_id' => $packageId,
            'type' => $type,
            'monthly_fee' => (float)$this->request->post('monthly_fee', 0),
            'billing_cycle' => $this->request->post('billing_cycle', 'monthly'),
            'feature_list_id' => (int)$this->request->post('feature_list_id', 0) ?: null,
            'features' => json_encode($this->request->post('features', [])) ?: null,
            'is_active' => $this->request->post('is_active', 1) ? 1 : 0,
        ]);
        $this->db->pdo()->prepare("UPDATE hosting_users SET reseller_id = NULL WHERE reseller_id = ?")->execute([(int)$id]);
        $assignedAccounts = $this->request->post('assigned_accounts', []);
        if (!empty($assignedAccounts)) {
            foreach ($assignedAccounts as $acctId) {
                $this->db->pdo()->prepare("UPDATE hosting_users SET reseller_id = ? WHERE id = ?")->execute([(int)$id, (int)$acctId]);
            }
        }
        $this->audit($id, 'reseller.updated', 'reseller', $id, ['fields' => ['package_id','type','monthly_fee','feature_list_id']]);
        $_SESSION['success_message'] = 'Reseller updated.';
        $this->response->redirect('/admin/reseller/show/' . $id);
    }

    // ── Detail / Management hub ──
    public function show($id)
    {
        $this->guard();
        $user = $this->auth->user();
        $reseller = $this->loadReseller($id);
        $pkg = $reseller->package_id ? $this->db->table('hosting_packages')->where('id', $reseller->package_id)->first() : null;
        $accounts = $this->db->table('hosting_users')->where('reseller_id', $id)->get() ?: [];
        $customers = $this->db->table('hosting_users')->where('reseller_id', $id)->orderBy('created_at', 'DESC')->limit(8)->get() ?: [];
        $staff = $this->db->table('reseller_staff')->where('reseller_id', $id)->get() ?: [];
        $keys = $this->db->table('reseller_api_keys')->where('reseller_id', $id)->get() ?: [];
        $audit = $this->db->table('reseller_audit_logs')->where('reseller_id', $id)->orderBy('created_at', 'DESC')->limit(12)->get() ?: [];
        // Services owned by this reseller's customers (billing_services has no reseller_id)
        $pdo = $this->db->pdo();
        $serviceCount = 0;
        $customerIds = array_map(fn($a) => (int)$a->id, $accounts);
        if (!empty($customerIds)) {
            $in = implode(',', $customerIds);
            try { $serviceCount = (int)$pdo->query("SELECT COUNT(*) FROM billing_services WHERE user_id IN ($in)")->fetchColumn(); } catch (\Exception $e) {}
        }
        $products = $this->db->table('billing_products')->where('is_active', 1)->orderBy('type', 'ASC')->get() ?: [];
        $alerts = $this->db->table('reseller_alerts')->where('reseller_id', $id)->orderBy('created_at', 'DESC')->limit(20)->get() ?: [];
        return $this->view('admin.reseller.show', [
            'user' => $user, 'title' => $reseller->company_name,
            'reseller' => $reseller, 'pkg' => $pkg, 'accounts' => $accounts, 'customers' => $customers,
            'staff' => $staff, 'keys' => $keys, 'audit' => $audit, 'services' => $serviceCount, 'products' => $products,
            'alerts' => $alerts,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    // ── Resources / limits ──
    public function resources($id)
    {
        $this->guard();
        $user = $this->auth->user();
        $reseller = $this->loadReseller($id);
        $usage = $this->usage($id);
        return $this->view('admin.reseller.resources', [
            'user' => $user, 'title' => $reseller->company_name . ' — Resources', 'reseller' => $reseller, 'usage' => $usage,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function resourcesSave($id)
    {
        $this->guard();
        $this->loadReseller($id);
        $fields = ['customers_limit','hosting_limit','storage_limit','bandwidth_limit','database_limit','domain_limit','vps_limit','game_server_limit','radio_station_limit'];
        $data = [];
        foreach ($fields as $f) $data[$f] = (int)$this->request->post($f, 0);
        $this->db->table('resellers')->where('id', $id)->update($data);
        $this->audit($id, 'resources.updated', 'reseller_resource', (int)$id, $data);
        $_SESSION['success_message'] = 'Reseller resource limits updated.';
        $this->response->redirect('/admin/reseller/resources/' . $id);
    }

    // ── Pricing / margins ──
    public function pricing($id)
    {
        $this->guard();
        $user = $this->auth->user();
        $reseller = $this->loadReseller($id);
        return $this->view('admin.reseller.pricing', [
            'user' => $user, 'title' => $reseller->company_name . ' — Pricing', 'reseller' => $reseller,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function pricingSave($id)
    {
        $this->guard();
        $this->loadReseller($id);
        $data = [
            'markup_mode' => $this->request->post('markup_mode', 'percent') === 'fixed' ? 'fixed' : 'percent',
            'hosting_margin' => (float)$this->request->post('hosting_margin', 0),
            'radio_margin' => (float)$this->request->post('radio_margin', 0),
            'vps_margin' => (float)$this->request->post('vps_margin', 0),
            'game_margin' => (float)$this->request->post('game_margin', 0),
            'domain_margin' => (float)$this->request->post('domain_margin', 0),
        ];
        $this->db->table('resellers')->where('id', $id)->update($data);
        $this->audit($id, 'pricing.updated', 'reseller_pricing', (int)$id, $data);
        $_SESSION['success_message'] = 'Reseller pricing/margins updated.';
        $this->response->redirect('/admin/reseller/pricing/' . $id);
    }

    // ── Branding (white label) ──
    public function branding($id)
    {
        $this->guard();
        $user = $this->auth->user();
        $reseller = $this->loadReseller($id);
        return $this->view('admin.reseller.branding', [
            'user' => $user, 'title' => $reseller->company_name . ' — Branding', 'reseller' => $reseller,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function brandingSave($id)
    {
        $this->guard();
        $this->loadReseller($id);
        $data = [
            'brand_logo' => $this->request->post('brand_logo', ''),
            'brand_favicon' => $this->request->post('brand_favicon', ''),
            'brand_primary_color' => $this->request->post('brand_primary_color', ''),
            'brand_secondary_color' => $this->request->post('brand_secondary_color', ''),
            'brand_url' => $this->request->post('brand_url', ''),
            'support_email' => $this->request->post('support_email', ''),
            'billing_email' => $this->request->post('billing_email', ''),
            'terms_url' => $this->request->post('terms_url', ''),
            'privacy_url' => $this->request->post('privacy_url', ''),
        ];
        $this->db->table('resellers')->where('id', $id)->update($data);
        $this->audit($id, 'branding.updated', 'reseller_brand', (int)$id, ['fields' => array_keys($data)]);
        $_SESSION['success_message'] = 'Reseller branding updated.';
        $this->response->redirect('/admin/reseller/branding/' . $id);
    }

    // ── Staff ──
    public function staffCreate($id)
    {
        $this->guard();
        $this->loadReseller($id);
        $email = trim($this->request->post('email', ''));
        $name = trim($this->request->post('name', ''));
        $password = $this->request->post('password', '');
        $role = $this->request->post('role', 'support');
        if ($email === '' || $password === '') {
            $_SESSION['error_message'] = 'Staff email and password are required.';
            $this->response->redirect('/admin/reseller/show/' . $id); exit;
        }
        $exists = $this->db->table('reseller_staff')->where('reseller_id', $id)->where('email', $email)->first();
        if ($exists) { $_SESSION['error_message'] = 'Staff email already exists.'; $this->response->redirect('/admin/reseller/show/' . $id); exit; }
        $staffId = $this->db->table('reseller_staff')->insertGetId([
            'reseller_id' => (int)$id, 'name' => $name, 'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role, 'permissions' => json_encode($this->request->post('permissions', [])), 'is_active' => 1,
        ]);
        $this->audit($id, 'staff.created', 'reseller_staff', (int)$staffId, ['email' => $email, 'role' => $role]);
        $_SESSION['success_message'] = "Staff {$email} added.";
        $this->response->redirect('/admin/reseller/show/' . $id);
    }

    public function staffToggle($id, $staffId)
    {
        $this->guard();
        $this->loadReseller($id);
        $s = $this->db->table('reseller_staff')->where('id', $staffId)->where('reseller_id', $id)->first();
        if ($s) {
            $this->db->table('reseller_staff')->where('id', $staffId)->update(['is_active' => $s->is_active ? 0 : 1]);
            $this->audit($id, 'staff.toggled', 'reseller_staff', (int)$staffId, ['is_active' => $s->is_active ? 0 : 1]);
        }
        $this->response->redirect('/admin/reseller/show/' . $id);
    }

    public function staffDelete($id, $staffId)
    {
        $this->guard();
        $this->loadReseller($id);
        $this->db->table('reseller_staff')->where('id', $staffId)->where('reseller_id', $id)->delete();
        $this->audit($id, 'staff.deleted', 'reseller_staff', (int)$staffId);
        $_SESSION['success_message'] = 'Staff member removed.';
        $this->response->redirect('/admin/reseller/show/' . $id);
    }

    // ── API keys ──
    public function apiCreate($id)
    {
        $this->guard();
        $this->loadReseller($id);
        $name = trim($this->request->post('name', ''));
        $key = bin2hex(random_bytes(24));
        $this->db->table('reseller_api_keys')->insertGetId([
            'reseller_id' => (int)$id, 'name' => $name !== '' ? $name : 'API Key',
            'key_hash' => hash('sha256', $key),
            'permissions' => json_encode($this->request->post('permissions', [])),
            'is_active' => 1,
        ]);
        $this->audit($id, 'api.key_created', 'reseller_api_key', (int)$this->db->lastInsertId());
        $_SESSION['reseller_api_key_' . $id] = $key; // show once
        $_SESSION['success_message'] = 'API key created. It was shown once — copy it now.';
        $this->response->redirect('/admin/reseller/show/' . $id);
    }

    public function apiToggle($id, $keyId)
    {
        $this->guard();
        $this->loadReseller($id);
        $k = $this->db->table('reseller_api_keys')->where('id', $keyId)->where('reseller_id', $id)->first();
        if ($k) {
            $this->db->table('reseller_api_keys')->where('id', $keyId)->update(['is_active' => $k->is_active ? 0 : 1]);
            $this->audit($id, 'api.key_toggled', 'reseller_api_key', (int)$keyId);
        }
        $this->response->redirect('/admin/reseller/show/' . $id);
    }

    public function apiDelete($id, $keyId)
    {
        $this->guard();
        $this->loadReseller($id);
        $this->db->table('reseller_api_keys')->where('id', $keyId)->where('reseller_id', $id)->delete();
        $this->audit($id, 'api.key_deleted', 'reseller_api_key', (int)$keyId);
        $this->response->redirect('/admin/reseller/show/' . $id);
    }

    // ── Audit ──
    public function auditLog($id)
    {
        $this->guard();
        $user = $this->auth->user();
        $reseller = $this->loadReseller($id);
        $logs = $this->db->table('reseller_audit_logs')->where('reseller_id', $id)->orderBy('created_at', 'DESC')->limit(200)->get() ?: [];
        return $this->view('admin.reseller.audit', [
            'user' => $user, 'title' => $reseller->company_name . ' — Audit Log', 'reseller' => $reseller, 'logs' => $logs,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function delete($id)
    {
        $this->guard();
        $this->loadReseller($id);
        $this->db->pdo()->prepare("UPDATE hosting_users SET reseller_id = NULL WHERE reseller_id = ?")->execute([(int)$id]);
        $this->db->table('resellers')->where('id', $id)->delete();
        $_SESSION['success_message'] = 'Reseller deleted; their accounts unassigned.';
        $this->response->redirect('/admin/reseller');
    }

    // ── helpers ──
    protected function usage($resellerId)
    {
        $usage = [];
        try {
            $pdo = $this->db->pdo();
            $usage['customers'] = (int)$pdo->query("SELECT COUNT(*) FROM hosting_users WHERE reseller_id=" . (int)$resellerId)->fetchColumn();
            $usage['active_services'] = (int)$pdo->query("SELECT COUNT(*) FROM billing_services WHERE reseller_id=" . (int)$resellerId)->fetchColumn();
            $usage['open_tickets'] = (int)$pdo->query("SELECT COUNT(*) FROM tickets t JOIN hosting_users hu ON hu.id=t.user_id WHERE hu.reseller_id=" . (int)$resellerId . " AND t.status='open'")->fetchColumn();
            $usage['unpaid_invoices'] = (int)$pdo->query("SELECT COUNT(*) FROM invoices i JOIN hosting_users hu ON hu.id=i.user_id WHERE hu.reseller_id=" . (int)$resellerId . " AND i.status IN ('pending','overdue')")->fetchColumn();
        } catch (\Exception $e) { $usage = []; }
        return $usage;
    }

    protected function audit($resellerId, $action, $resourceType = null, $resourceId = null, $details = null)
    {
        try {
            $this->db->table('reseller_audit_logs')->insertGetId([
                'reseller_id' => (int)$resellerId,
                'staff_email' => '$admin',
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId ? (int)$resourceId : null,
                'details' => $details ? json_encode($details) : null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (\Exception $e) {}
    }

    // Alert a reseller (admin -> reseller message shown in their unified alerts feed).
    public function sendAlert($id)
    {
        $this->guard();
        $reseller = $this->loadReseller($id);
        $title = trim($this->request->post('alert_title', ''));
        $message = trim($this->request->post('alert_message', ''));
        $type = in_array($this->request->post('alert_type', '') , ['info','warning','success','danger']) ? $this->request->post('alert_type', '') : 'info';
        if ($title === '') { $_SESSION['error_message'] = 'Alert title is required.'; $this->response->redirect('/admin/reseller/show/' . $id); exit; }
        $this->db->table('reseller_alerts')->insertGetId([
            'reseller_id' => (int)$id, 'admin_id' => $this->auth->user()->id,
            'title' => $title, 'message' => $message, 'type' => $type,
        ]);
        $this->audit((int)$id, 'alert.sent', 'reseller_alert', null, ['title' => $title]);
        $_SESSION['success_message'] = "Alert sent to reseller '{$reseller->company_name}'.";
        $this->response->redirect('/admin/reseller/show/' . $id);
    }

    public function deleteAlert($id, $alertId)
    {
        $this->guard();
        $this->loadReseller($id);
        $this->db->table('reseller_alerts')->where('id', (int)$alertId)->where('reseller_id', (int)$id)->delete();
        $this->audit((int)$id, 'alert.deleted', 'reseller_alert', (int)$alertId);
        $this->response->redirect('/admin/reseller/show/' . $id);
    }

    // ── Admin unified alerts feed (across all resellers) ──
    public function alerts()
    {
        $this->guard();
        $user = $this->auth->user();
        $pdo = $this->db->pdo();
        $items = [];

        // Due + past-due invoices under any reseller
        try {
            $rows = $pdo->query("SELECT i.id, i.invoice_number, i.total, i.status, i.due_date, hu.username AS client, hu.reseller_id, r.company_name AS reseller
                FROM invoices i JOIN hosting_users hu ON hu.id = i.user_id JOIN resellers r ON r.id = hu.reseller_id
                WHERE i.status IN ('sent','overdue') ORDER BY i.due_date ASC LIMIT 200")->fetchAll(\PDO::FETCH_OBJ) ?: [];
            foreach ($rows as $x) {
                $overdue = $x->status === 'overdue';
                $items[] = ['icon' => $overdue ? '⛔' : '⚠️', 'color' => $overdue ? '#f87171' : '#facc15',
                    'title' => ($overdue ? 'Past Due Invoice' : 'Due Invoice') . ' #' . $x->invoice_number,
                    'detail' => '$' . number_format((float)$x->total,2) . ' — ' . $x->client . ' (' . $x->reseller . ')' . ($x->due_date ? ' · due ' . date('M j', strtotime($x->due_date)) : ''),
                    'time' => $x->due_date, 'link' => '/admin/reseller/show/' . (int)$x->reseller_id];
            }
        } catch (\Exception $e) {}

        // New clients in last 7 days
        try {
            $rows = $pdo->query("SELECT hu.id, hu.username, hu.created_at, r.company_name AS reseller, hu.reseller_id
                FROM hosting_users hu JOIN resellers r ON r.id = hu.reseller_id
                WHERE hu.reseller_id IS NOT NULL AND hu.created_at >= '" . date('Y-m-d', strtotime('-7 days')) . "' ORDER BY hu.created_at DESC LIMIT 100")->fetchAll(\PDO::FETCH_OBJ) ?: [];
            foreach ($rows as $x) {
                $items[] = ['icon' => '✅', 'color' => '#4ade80', 'title' => 'New Client: ' . $x->username,
                    'detail' => $x->reseller, 'time' => $x->created_at, 'link' => '/admin/account/show/' . (int)$x->id];
            }
        } catch (\Exception $e) {}

        // New orders last 7 days
        try {
            $orders = $pdo->query("SELECT o.id, o.total, o.type, o.status, o.created_at, hu.username AS client, r.company_name AS reseller
                FROM billing_orders o JOIN hosting_users hu ON hu.id = o.user_id JOIN resellers r ON r.id = hu.reseller_id
                WHERE hu.reseller_id IS NOT NULL AND o.created_at >= '" . date('Y-m-d', strtotime('-7 days')) . "' ORDER BY o.created_at DESC LIMIT 100")->fetchAll(\PDO::FETCH_OBJ) ?: [];
            foreach ($orders as $x) {
                $items[] = ['icon' => '🛒', 'color' => '#38bdf8', 'title' => 'New Order #' . (int)$x->id,
                    'detail' => '$' . number_format((float)$x->total,2) . ' — ' . $x->client . ' (' . $x->reseller . ')', 'time' => $x->created_at, 'link' => '/admin/billing/orders'];
            }
        } catch (\Exception $e) {}

        // Resellers over quota
        try {
            $resellers = $this->db->table('resellers')->where('is_active', 1)->get() ?: [];
            foreach ($resellers as $rr) {
                $soldDisk = (float)($pdo->query("SELECT COALESCE(SUM(disk_space),0) FROM reseller_packages WHERE reseller_id=" . (int)$rr->id . " AND is_active=1")->fetchColumn() ?? 0) / 1024;
                $diskTotal = (float)($rr->storage_limit ?: 2199023255552) / 1073741824;
                if ($diskTotal > 0 && (($soldDisk / $diskTotal) * 100) >= 90) {
                    $pct = round(($soldDisk / $diskTotal) * 100, 1);
                    $items[] = ['icon' => '🔴', 'color' => '#f87171', 'title' => $rr->company_name . ' — Low Disk', 'detail' => $pct . '% of allocation committed', 'time' => null, 'link' => '/admin/reseller/resources/' . (int)$rr->id];
                }
            }
        } catch (\Exception $e) {}

        // Admin->reseller messages
        try {
            $msgs = $pdo->query("SELECT a.*, r.company_name AS reseller FROM reseller_alerts a JOIN resellers r ON r.id = a.reseller_id ORDER BY a.created_at DESC LIMIT 100")->fetchAll(\PDO::FETCH_OBJ) ?: [];
            foreach ($msgs as $x) {
                $items[] = ['icon' => '💬', 'color' => '#' . ($x->type === 'danger' ? 'f87171' : ($x->type === 'warning' ? 'facc15' : ($x->type === 'success' ? '4ade80' : '38bdf8'))),
                    'title' => $x->title . ' → ' . $x->reseller, 'detail' => $x->message ?? '', 'time' => $x->created_at, 'link' => '/admin/reseller/show/' . (int)$x->reseller_id, 'unread' => empty($x->is_read)];
            }
        } catch (\Exception $e) {}

        usort($items, function ($a, $b) { return strtotime((string)$b['time']) <=> strtotime((string)$a['time']); });
        return $this->view('admin.reseller.alerts', [
            'user' => $user, 'title' => 'Alerts — Resellers', 'items' => $items,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    // ── Products available to a reseller type ──
    public function products($id)
    {
        $this->guard();
        $user = $this->auth->user();
        $reseller = $this->loadReseller($id);
        $products = $this->db->table('billing_products')->where('is_active', 1)->orderBy('type', 'ASC')->get() ?: [];
        return $this->view('admin.reseller.products', [
            'user' => $user, 'title' => $reseller->company_name . ' — Products', 'reseller' => $reseller, 'products' => $products,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }
}