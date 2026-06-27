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
            'public_ip' => trim(shell_exec('timeout 3 curl -s https://ifconfig.me/ip 2>/dev/null') ?: '127.0.0.1'),
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

        // Services (try both crond and cron for Debian/RHEL)
        $cronActive = trim(shell_exec('systemctl is-active cron 2>/dev/null') ?: '') === 'active';
        if (!$cronActive) $cronActive = trim(shell_exec('systemctl is-active crond 2>/dev/null') ?: '') === 'active';
        $serviceNames = ['apache2' => 'Apache', 'mariadb' => 'MariaDB', 'icecast2' => 'Icecast', 'postfix' => 'Postfix', 'dovecot' => 'Dovecot', 'firewalld' => 'Firewall', 'nginx' => 'Nginx'];
        $services = [];
        foreach ($serviceNames as $sName => $sLabel) {
            $active = trim(shell_exec("systemctl is-active {$sName} 2>/dev/null") ?: '') === 'active';
            $services[] = ['name' => $sLabel, 'active' => $active, 'status' => $active ? 'active' : ''];
        }
        $services[] = ['name' => 'Cron', 'active' => $cronActive, 'status' => $cronActive ? 'active' : ''];

        // Streaming engines status
        $streamEngines = [];
        $sc2Installed = file_exists('/opt/planethosts/shoutcast/sc_serv');
        $sc1Installed = file_exists('/opt/planethosts/shoutcast1/sc_serv');
        $iceInstalled = trim(shell_exec('which icecast 2>/dev/null') ?: '') !== '' || trim(shell_exec('systemctl is-active icecast2 2>/dev/null') ?: '') === 'active';
        $streamEngines[] = ['name' => 'SHOUTcast v2', 'installed' => $sc2Installed, 'running' => $sc2Installed && !empty(trim(shell_exec('pgrep -x sc_serv 2>/dev/null') ?: ''))];
        $streamEngines[] = ['name' => 'SHOUTcast v1', 'installed' => $sc1Installed, 'running' => $sc1Installed && !empty(trim(shell_exec('pgrep -x sc_serv 2>/dev/null') ?: ''))];
        $streamEngines[] = ['name' => 'Icecast', 'installed' => $iceInstalled, 'running' => trim(shell_exec('systemctl is-active icecast2 2>/dev/null') ?: '') === 'active'];

        // Nameservers and primary domain
        $ns1 = '';
        $ns2 = '';
        $primaryDomain = 'planet-hosts.com';
        try {
            $ns1r = $this->db->table('automation_settings')->where('setting_key', 'ns1')->first();
            $ns2r = $this->db->table('automation_settings')->where('setting_key', 'ns2')->first();
            if ($ns1r) $ns1 = $ns1r->setting_value;
            if ($ns2r) $ns2 = $ns2r->setting_value;
            $hostR = $this->db->table('automation_settings')->where('setting_key', 'hostname')->first();
            if ($hostR) $primaryDomain = $hostR->setting_value;
        } catch (\Exception $e) {}
        if (empty($ns1)) $ns1 = 'ns1.planet-hosts.com';
        if (empty($ns2)) $ns2 = 'ns2.planet-hosts.com';

        // Server IPs
        $serverIps = [];
        try {
            $ips = $this->db->table('server_ips')->where('is_active', 1)->get() ?: [];
            foreach ($ips as $ipRow) {
                $ip = $ipRow->ip_address ?? $ipRow->ip ?? '';
                if (empty($ip)) continue;
                $serverIps[] = [
                    'ip' => $ip,
                    'ns1' => $ipRow->ns1 ?? $ns1,
                    'ns2' => $ipRow->ns2 ?? $ns2,
                ];
            }
        } catch (\Exception $e) {}
        if (empty($serverIps)) {
            $serverIps[] = ['ip' => $server['public_ip'] ?? '127.0.0.1', 'ns1' => $ns1, 'ns2' => $ns2];
        }

        // Station counts
        $stationCounts = [];
        try { $allStations = $this->db->table('streaming_stations')->get() ?: []; } catch (\Exception $e) { $allStations = []; }
        $stationCounts['total'] = count($allStations);
        $stationCounts['running'] = count(array_filter($allStations, fn($s) => $s->status === 'running'));

        $pluginManager = \Core\Application::getInstance()->getPluginManager();
        $addons = $pluginManager ? $pluginManager->loadedMetadata() : [];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Widget system — load from /widgets/ folder
        $all_widgets = [];
        $widgets_main = '';
        $widgets_side = '';
        try {
            $wm = \Core\WidgetManager::getInstance();
            $wm->setDb($this->db)->setUserId($user->id);
            $basePath = \Core\Application::getInstance()->getBasePath();
            $wm->loadFromFolder($basePath . '/widgets');
            $wm->loadCustomWidgets($user->id);

            $statsData = [
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
            ];

            // Share dashboard data with widgets via WidgetManager
            $wm->setData('stats', $statsData);
            $wm->setData('server', $server);
            $wm->setData('services', $services);
            $wm->setData('streamEngines', $streamEngines);
            $wm->setData('stationCounts', $stationCounts);
            $wm->setData('ns1', $ns1);
            $wm->setData('ns2', $ns2);
            $wm->setData('primaryDomain', $primaryDomain);
            $wm->setData('serverIps', $serverIps);
            $wm->setData('recentAccounts', $recentAccounts);
            $wm->setData('recentTickets', $recentTickets);
            $wm->setData('recentOrders', $recentOrders);

            $wm->ensureDefaults($user->id);
            $all_widgets = $wm->getAllWidgets();
            $widgets_main = $wm->renderZone('main');
            $widgets_side = $wm->renderZone('side');
        } catch (\Exception $e) {
            $all_widgets = [];
            $widgets_main = '';
            $widgets_side = '';
        }

        return $this->view('admin.dashboard.index', [
            'user' => $user,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
            'all_widgets' => $all_widgets,
            'widgets_main' => $widgets_main,
            'widgets_side' => $widgets_side,
        ]);
    }

    public function health()
    {
        $services = [];
        foreach (['apache2', 'mariadb', 'icecast2', 'postfix', 'dovecot', 'firewalld', 'nginx'] as $s) {
            $active = trim(shell_exec("systemctl is-active {$s} 2>/dev/null") ?: '') === 'active';
            $services[$s] = $active;
        }
        $cronActive = trim(shell_exec('systemctl is-active cron 2>/dev/null') ?: '') === 'active';
        if (!$cronActive) $cronActive = trim(shell_exec('systemctl is-active crond 2>/dev/null') ?: '') === 'active';
        $services['cron'] = $cronActive;
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

    public function version()
    {
        $this->response->json([
            'version' => '1.0.0',
            'name' => 'Planet Hosts Panel',
            'php' => phpversion(),
        ])->send();
        exit;
    }
}
