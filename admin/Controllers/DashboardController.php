<?php

namespace Admin\Controllers;

use Core\Controller;

class DashboardController extends Controller
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

    protected function getSetting($key, $default = '')
    {
        $r = $this->db->table('automation_settings')->where('setting_key', $key)->first();
        return $r ? $r->setting_value : $default;
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login'); exit;
        }

        $user = $this->auth->user();

        // ΓöÇΓöÇΓöÇ Server Stats ΓöÇΓöÇΓöÇ
        $server = $this->getServerStats();

        // ΓöÇΓöÇΓöÇ Service Status ΓöÇΓöÇΓöÇ
        $services = $this->getServiceStatus();

        // ΓöÇΓöÇΓöÇ Accounts ΓöÇΓöÇΓöÇ
        $accounts = $this->db->table('hosting_users')->get() ?: [];
        $activeAccounts = count(array_filter($accounts, fn($a) => $a->status === 'active'));
        $suspendedCount = count(array_filter($accounts, fn($a) => $a->status === 'suspended'));
        $recentAccounts = array_slice(array_reverse($accounts), 0, 5);

        // ΓöÇΓöÇΓöÇ Packages ΓöÇΓöÇΓöÇ
        $packages = $this->db->table('hosting_packages')->get() ?: [];

        // ΓöÇΓöÇΓöÇ Resellers ΓöÇΓöÇΓöÇ
        $resellers = $this->db->table('resellers')->get() ?: [];

        // ΓöÇΓöÇΓöÇ Billing / Revenue ΓöÇΓöÇΓöÇ
        $monthStart = date('Y-m-01 00:00:00');
        $paymentsThisMonth = $this->db->table('billing_payments')->where('created_at', '>=', $monthStart)->get() ?: [];
        $revenueMonth = array_sum(array_map(fn($p) => (float)$p->amount, $paymentsThisMonth));
        $pendingInvoices = $this->db->table('invoices')->where('status', 'unpaid')->get() ?: [];
        $pendingInvoiceTotal = array_sum(array_map(fn($i) => (float)$i->total, $pendingInvoices));

        // ΓöÇΓöÇΓöÇ Tickets ΓöÇΓöÇΓöÇ
        $openTickets = $this->db->table('tickets')->where('status', 'open')->get() ?: [];
        $recentTickets = array_slice(array_reverse($openTickets), 0, 5);

        // ΓöÇΓöÇΓöÇ Orders ΓöÇΓöÇΓöÇ
        $recentOrders = $this->db->table('billing_orders')->orderBy('id', 'DESC')->limit(5)->get() ?: [];

        // ΓöÇΓöÇΓöÇ PayPal Balance ΓöÇΓöÇΓöÇ
        $paypalBalance = $this->getPayPalBalance();

        // ΓöÇΓöÇΓöÇ Plugin Manager ΓöÇΓöÇΓöÇ
        $pluginManager = \Core\Application::getInstance()->getPluginManager();
        $addons = $pluginManager ? $pluginManager->loadedMetadata() : [];

        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Widget system
        require_once BASE_PATH . '/core/Widget.php';
        require_once BASE_PATH . '/core/WidgetManager.php';
        $wm = \Core\WidgetManager::getInstance();
        $wm->setDb($this->db)->setUserId($user->id);
        $wm->ensureDefaults();
        $userWidgets = $wm->getUserWidgets();
        $allWidgets = $wm->getAllWidgets();
        $mainZone = $wm->renderZone('main', $userWidgets);
        $sideZone = $wm->renderZone('side', $userWidgets);

        return $this->view('admin.dashboard.index', [
            'user' => $user,
            'server' => $server,
            'services' => $services,
            'stats' => [
                'total_accounts' => count($accounts),
                'active_accounts' => $activeAccounts,
                'suspended_accounts' => $suspendedCount,
                'total_packages' => count($packages),
                'total_resellers' => count($resellers),
                'open_tickets' => count($openTickets),
                'revenue_month' => $revenueMonth,
                'pending_invoices' => count($pendingInvoices),
                'pending_invoice_total' => $pendingInvoiceTotal,
                'paypal_balance' => $paypalBalance,
            ],
            'recent_accounts' => $recentAccounts,
            'recent_tickets' => $recentTickets,
            'recent_orders' => $recentOrders,
            'addons' => $addons,
            'theme_settings' => $theme_settings,
            'widgets_main' => $mainZone,
            'widgets_side' => $sideZone,
            'all_widgets' => $allWidgets,
        ]);
    }

    public function version()
    {
        require_once BASE_PATH . '/core/Version.php';
        $check = checkVersion();
        $this->response->json([
            'version' => PANEL_VERSION_NAME,
            'version_code' => PANEL_VERSION_CODE,
            'serial' => PANEL_SERIAL,
            'update' => $check,
        ]);
        $this->response->send();
        exit;
    }

    public function health()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error'=>'Unauthorized']); $this->response->send(); exit; }
        $this->response->json($this->getServerStats());
        $this->response->send();
        exit;
    }

    private function getServerStats()
    {
        $stats = ['cpu' => 'N/A', 'ram' => 'N/A', 'disk' => 'N/A', 'uptime' => 'N/A', 'load' => 'N/A', 'hostname' => gethostname()];

        // CPU
        $cpu = @file_get_contents('/proc/stat');
        if ($cpu) {
            preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $cpu, $m);
            $total = array_sum(array_slice($m, 1));
            $idle = (int)$m[4];
            $stats['cpu'] = $total > 0 ? round(100 * (1 - $idle / $total), 1) . '%' : 'N/A';
        }

        // RAM
        $mem = @file_get_contents('/proc/meminfo');
        if ($mem) {
            preg_match('/MemTotal:\s+(\d+)/', $mem, $mt);
            preg_match('/MemAvailable:\s+(\d+)/', $mem, $ma);
            $total = (int)($mt[1] ?? 0);
            $avail = (int)($ma[1] ?? 0);
            $used = $total - $avail;
            $stats['ram'] = $total > 0 ? round($used / 1024 / 1024, 1) . 'G / ' . round($total / 1024 / 1024, 1) . 'G' : 'N/A';
            $stats['ram_pct'] = $total > 0 ? round(100 * $used / $total, 1) : 0;
        }

        // Disk
        $disk = @disk_total_space('/');
        $diskFree = @disk_free_space('/');
        if ($disk) {
            $used = $disk - $diskFree;
            $stats['disk'] = round($used / 1024 / 1024 / 1024, 1) . 'G / ' . round($disk / 1024 / 1024 / 1024, 1) . 'G';
            $stats['disk_pct'] = round(100 * $used / $disk, 1);
        }

        // Uptime
        $up = @file_get_contents('/proc/uptime');
        if ($up) {
            $secs = (int)explode(' ', $up)[0];
            $d = floor($secs / 86400); $h = floor(($secs % 86400) / 3600); $m = floor(($secs % 3600) / 60);
            $stats['uptime'] = "{$d}d {$h}h {$m}m";
        }

        // Load
        $load = @sys_getloadavg();
        if ($load) $stats['load'] = round($load[0], 2);

        return $stats;
    }

    private function getServiceStatus()
    {
        $checks = ['apache2', 'mariadb', 'postfix', 'dovecot', 'vsftpd', 'bind9', 'ssh', 'icecast2', 'firewalld', 'fail2ban', 'redis-server'];
        // Detect all PHP-FPM versions
        exec('ls /usr/bin/php* 2>/dev/null', $out);
        foreach ($out as $p) {
            if (preg_match('/php(\d+\.\d+)$/', $p, $m)) {
                $checks[] = "php{$m[1]}-fpm";
            }
        }
        $statuses = [];
        foreach ($checks as $svc) {
            $result = trim(shell_exec("systemctl is-active {$svc} 2>/dev/null") ?: '');
            $statuses[] = ['name' => $svc, 'active' => $result === 'active', 'status' => $result];
        }
        return $statuses;
    }

    private function getPayPalBalance()
    {
        $clientId = $this->getSetting('paypal_client_id', '');
        $secret = $this->getSetting('paypal_secret', '');
        $mode = $this->getSetting('paypal_mode', 'sandbox');

        if (!$clientId || !$secret) return null;

        $apiUrl = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        // Get access token
        $ch = curl_init("{$apiUrl}/v1/oauth2/token");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => "{$clientId}:{$secret}",
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_TIMEOUT => 10,
        ]);
        $authResp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) return null;
        $auth = json_decode($authResp, true);
        $token = $auth['access_token'] ?? null;
        if (!$token) return null;

        // Get balance
        $ch2 = curl_init("{$apiUrl}/v1/reporting/balances");
        curl_setopt_array($ch2, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer {$token}", 'Content-Type: application/json'],
            CURLOPT_TIMEOUT => 10,
        ]);
        $balResp = curl_exec($ch2);
        $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);

        if ($httpCode2 !== 200) return null;
        $bal = json_decode($balResp, true);
        $balances = $bal['balances'] ?? [];
        $total = 0;
        foreach ($balances as $b) {
            if (($b['total_balance']['value'] ?? 0) > 0) {
                $total += (float)$b['total_balance']['value'];
            }
        }
        return $total > 0 ? $total : null;
    }
}

