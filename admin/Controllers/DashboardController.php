<?php
namespace Admin\Controllers;

use Core\Controller;

class DashboardController extends Controller
{
    protected $auth, $db, $response, $request;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->db = $app->get('db');
        $this->response = $app->get('response');
        $this->request = $app->get('request');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            return;
        }

        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Count accounts by status
        $accounts = $this->db->table('hosting_users')->get() ?: [];
        $activeAccounts = count(array_filter($accounts, fn($a) => ($a->status ?? 'active') === 'active'));
        $suspendedAccounts = count(array_filter($accounts, fn($a) => ($a->status ?? 'active') === 'suspended'));
        $terminatedAccounts = count(array_filter($accounts, fn($a) => ($a->status ?? 'active') === 'terminated'));

        // Count packages and resellers
        $packages = $this->db->table('hosting_packages')->get() ?: [];
        $resellers = $this->db->table('resellers')->get() ?: [];

        // Server resources (safe defaults if commands fail)
        $server = [
            'hostname' => trim(shell_exec('hostname 2>/dev/null') ?: 'localhost'),
            'public_ip' => '15.204.114.226',
            'uptime' => trim(shell_exec('uptime -p 2>/dev/null') ?: ''),
            'load' => '',
            'ram' => '',
            'disk' => '',
        ];

        // Services status
        $serviceNames = ['apache2' => 'Apache', 'mariadb' => 'MariaDB', 'icecast2' => 'Icecast', 'postfix' => 'Postfix', 'dovecot' => 'Dovecot', 'named' => 'DNS', 'vsftpd' => 'FTP', 'firewalld' => 'Firewall'];
        $services = [];
        foreach ($serviceNames as $sName => $sLabel) {
            $active = trim(shell_exec("systemctl is-active {$sName} 2>/dev/null") ?: '') === 'active';
            $services[] = ['name' => $sLabel, 'active' => $active, 'status' => $active ? 'active' : ''];
        }

        // Streaming engines
        $streamEngines = [
            ['name' => 'SHOUTcast v2', 'installed' => file_exists('/opt/planethosts/shoutcast/sc_serv'), 'running' => false],
            ['name' => 'SHOUTcast v1', 'installed' => file_exists('/opt/planethosts/shoutcast1/sc_serv'), 'running' => false],
            ['name' => 'Icecast', 'installed' => trim(shell_exec('which icecast 2>/dev/null') ?: '') !== '' || trim(shell_exec('systemctl is-active icecast2 2>/dev/null') ?: '') === 'active', 'running' => false],
        ];

        // Recent accounts
        $recentAccounts = $this->db->table('hosting_users')->orderBy('created_at', 'DESC')->limit(5)->get() ?: [];

        // Recent tickets
        $openTickets = 0;
        try { $openTickets = (int)$this->db->table('tickets')->where('status', 'open')->count(); } catch (\Exception $e) {}

        // Recent orders
        $recentOrders = [];

        // Widget data (minimal for Option A)
        $statsData = [
            'total_accounts' => count($accounts),
            'active_accounts' => $activeAccounts,
            'suspended_accounts' => $suspendedAccounts,
            'terminated_accounts' => $terminatedAccounts,
            'total_packages' => count($packages),
            'active_packages' => count(array_filter($packages, fn($p) => ($p->is_active ?? 0) == 1)),
            'total_resellers' => count($resellers),
            'open_tickets' => $openTickets,
            'revenue_month' => 0,
            'pending_invoices' => 0,
            'pending_invoice_total' => 0,
            'paypal_balance' => null,
        ];

        return $this->view('admin.dashboard.index', [
            'user' => $user,
            'theme_settings' => $theme_settings,
            'statsData' => $statsData,
            'recentAccounts' => $recentAccounts,
            'openTicketCount' => $openTickets,
            'services' => $services,
            'streamEngines' => $streamEngines,
        ]);
    }
}