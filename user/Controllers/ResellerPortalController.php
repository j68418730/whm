<?php

namespace User\Controllers;

use Core\Controller;

class ResellerPortalController extends Controller
{
    protected $auth, $request, $response, $db;
    protected $reseller;
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
        if (!$this->auth->check()) { $this->response->redirect('https://planet-hosts.com:2089/user_login.php'); exit; }
        $user = $this->auth->user();
        // Admins / super admins hitting /reseller should go back to their admin dashboard
        if (isset($user->is_admin) && $user->is_admin) { $this->response->redirect('/admin'); exit; }
        $this->reseller = $this->db->table('resellers')->where('email', $user->email)->where('is_active', 1)->first();
        if (!$this->reseller) { $this->response->redirect('/user'); exit; }
        $this->addons = $this->enabledAddons();
        return $user;
    }

    protected function view($view, $data = [])
    {
        $data['addons'] = $this->addons;
        return parent::view($view, $data);
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

    public function provisioning()
    {
        $u = $this->requireReseller();
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
        return $this->view('user.reseller.provisioning', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Provisioning',
            'orders' => $orders, 'pkgNames' => $pkgNames, 'provisioned' => $provisioned, 'pending' => $pending,
        ]);
    }

    // Reseller triggers provisioning for one of THEIR OWN customers' orders.
    // Runs through Planet Hosts backend (same pipeline as paid orders) — reseller never needs SSH/root.
    public function provisioningRun($orderId)
    {
        $u = $this->requireReseller();
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
    public function clientBilling()
    {
        $u = $this->requireReseller();
        $addons = $this->enabledAddons();
        if (!$addons['billing']) { $_SESSION['error_message'] = 'The Billing addon is not enabled on your packages.'; $this->response->redirect('/reseller'); exit; }
        $pdo = $this->db->pdo();
        $rid = (int)$this->reseller->id;
        $invoices = $pdo->query("SELECT i.*, hu.username AS client FROM invoices i
            JOIN hosting_users hu ON hu.id = i.user_id
            WHERE hu.reseller_id = {$rid} ORDER BY i.created_at DESC LIMIT 200")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        $totalOutstanding = 0;
        foreach ($invoices as $inv) { if (in_array($inv->status, ['sent','overdue','pending'])) $totalOutstanding += $inv->total; }
        // Clients for the issue-invoice form
        $clients = $this->db->table('hosting_users')->where('reseller_id', $rid)->get() ?: [];
        return $this->view('user.reseller.client_billing', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Billing System',
            'addons' => $addons, 'invoices' => $invoices, 'totalOutstanding' => $totalOutstanding, 'clients' => $clients,
        ]);
    }

    public function clientBillingCreate()
    {
        $u = $this->requireReseller();
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

    // ── Addon: Chat system (reseller manages their clients' chatbox tenants) ──
    public function clientChat()
    {
        $u = $this->requireReseller();
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
