<?php

namespace Admin\Controllers;

use Core\Controller;

class DashboardController extends Controller
{
    protected $auth;
    protected $response;
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        $user = $this->auth->user();
        $accounts = $this->db->table('hosting_users')->get() ?: [];
        $packages = $this->db->table('hosting_packages')->get() ?: [];
        $resellers = $this->db->table('resellers')->get() ?: [];

        // Recent items (with error handling for missing tables)
        $recentAccounts = $this->db->table('hosting_users')->orderBy('created_at', 'DESC')->limit(5)->get() ?: [];
        try { $recentTickets = $this->db->table('support_tickets')->where('status', 'open')->orderBy('created_at', 'DESC')->limit(5)->get() ?: []; } catch (\Exception $e) { $recentTickets = []; }
        try { $recentOrders = $this->db->table('orders')->orderBy('created_at', 'DESC')->limit(5)->get() ?: []; } catch (\Exception $e) { $recentOrders = []; }

        // Stats
        $activeAccounts = count(array_filter($accounts, function($a) { return $a->status === 'active'; }));
        $suspendedAccounts = count(array_filter($accounts, function($a) { return $a->status === 'suspended'; }));
        $activePackages = count(array_filter($packages, function($p) { return ($p->is_active ?? 0) == 1; }));

        // Server stats
        $server = [
            'hostname' => trim(shell_exec('hostname 2>/dev/null') ?: 'localhost'),
            'uptime' => trim(shell_exec('uptime -p 2>/dev/null') ?: ''),
            'cpu' => trim(shell_exec('cat /proc/cpuinfo 2>/dev/null | grep "model name" | head -1 | cut -d: -f2') ?: ''),
            'load' => trim(shell_exec('cat /proc/loadavg 2>/dev/null | awk "{print \$1\" / \"\$2\" / \"\$3}"') ?: ''),
            'ram' => trim(shell_exec('free -h 2>/dev/null | grep Mem | awk "{print \$3\" / \"\$2}"') ?: ''),
            'ram_pct' => 0,
            'disk' => trim(shell_exec('df -h / 2>/dev/null | tail -1 | awk "{print \$3\" / \"\$2}"') ?: ''),
            'disk_pct' => 0,
        ];

        // RAM percentage
        $ramTotal = shell_exec('free 2>/dev/null | grep Mem | awk "{print \$2}"') ?: 0;
        $ramUsed = shell_exec('free 2>/dev/null | grep Mem | awk "{print \$3}"') ?: 0;
        if ($ramTotal > 0) $server['ram_pct'] = round(($ramUsed / $ramTotal) * 100);
        $diskTotal = shell_exec('df / 2>/dev/null | tail -1 | awk "{print \$2}"') ?: 0;
        $diskUsed = shell_exec('df / 2>/dev/null | tail -1 | awk "{print \$3}"') ?: 0;
        if ($diskTotal > 0) $server['disk_pct'] = round(($diskUsed / $diskTotal) * 100);

        // Services
        $serviceNames = ['apache2' => 'Apache', 'mariadb' => 'MariaDB', 'icecast2' => 'Icecast', 'postfix' => 'Postfix', 'dovecot' => 'Dovecot', 'firewalld' => 'Firewall', 'crond' => 'Cron', 'nginx' => 'Nginx'];
        $services = [];
        foreach ($serviceNames as $sName => $sLabel) {
            $active = trim(shell_exec("systemctl is-active {$sName} 2>/dev/null") ?: '') === 'active';
            $services[] = ['name' => $sLabel, 'active' => $active, 'status' => $active ? 'active' : ''];
        }

        $pluginManager = \Core\Application::getInstance()->getPluginManager();
        $addons = $pluginManager ? $pluginManager->loadedMetadata() : [];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Widget system
        $all_widgets = [];
        $widgets_main = '';
        $widgets_side = '';

        return $this->view('admin.dashboard.index', [
            'user' => $user,
            'stats' => [
                'total_accounts' => count($accounts),
                'active_accounts' => $activeAccounts,
                'suspended_accounts' => $suspendedAccounts,
                'total_packages' => count($packages),
                'active_packages' => $activePackages,
                'total_resellers' => count($resellers),
                'open_tickets' => count($recentTickets),
                'revenue_month' => 0,
                'pending_invoices' => 0,
                'pending_invoice_total' => 0,
                'paypal_balance' => null,
            ],
            'recentAccounts' => $recentAccounts,
            'recentTickets' => $recentTickets,
            'recentOrders' => $recentOrders,
            'server' => $server,
            'services' => $services,
            'addons' => $addons,
            'theme_settings' => $theme_settings,
            'all_widgets' => $all_widgets,
            'widgets_main' => $widgets_main,
            'widgets_side' => $widgets_side,
        ]);
    }

    public function health()
    {
        $services = [];
        foreach (['apache2', 'mariadb', 'icecast2', 'postfix', 'dovecot', 'firewalld', 'crond', 'nginx'] as $s) {
            $active = trim(shell_exec("systemctl is-active {$s} 2>/dev/null") ?: '') === 'active';
            $services[$s] = $active;
        }
        $this->response->json([
            'hostname' => trim(shell_exec('hostname 2>/dev/null') ?: ''),
            'uptime' => trim(shell_exec('uptime -p 2>/dev/null') ?: ''),
            'cpu' => trim(shell_exec('cat /proc/cpuinfo 2>/dev/null | grep "model name" | head -1 | cut -d: -f2') ?: ''),
            'load' => trim(shell_exec('cat /proc/loadavg 2>/dev/null | awk "{print \$1\" / \"\$2\" / \"\$3}"') ?: ''),
            'ram' => trim(shell_exec('free -h 2>/dev/null | grep Mem | awk "{print \$3\" / \"\$2}"') ?: ''),
            'ram_pct' => 0,
            'disk' => trim(shell_exec('df -h / 2>/dev/null | tail -1 | awk "{print \$3\" / \"\$2}"') ?: ''),
            'disk_pct' => 0,
            'services' => $services,
        ]);
    }
}
