<?php

namespace Admin\Controllers;

use Core\Controller;

class IpController extends Controller
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

    protected function ensureTable()
    {
        try {
            $pdo = $this->db->pdo();
            $pdo->exec("CREATE TABLE IF NOT EXISTS `server_ips` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `ip_address` VARCHAR(45) NOT NULL,
                `server` VARCHAR(100) DEFAULT NULL,
                `hostname` VARCHAR(255) DEFAULT NULL,
                `assigned_to` INT DEFAULT NULL,
                `ns1` VARCHAR(255) DEFAULT NULL,
                `ns2` VARCHAR(255) DEFAULT NULL,
                `ns3` VARCHAR(255) DEFAULT NULL,
                `ns4` VARCHAR(255) DEFAULT NULL,
                `is_active` TINYINT DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `ip_unique` (`ip_address`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $checkCol = $pdo->query("SHOW COLUMNS FROM `server_ips` LIKE 'server'")->fetch();
            if (!$checkCol) {
                try { $pdo->exec("ALTER TABLE `server_ips` ADD COLUMN `server` VARCHAR(100) DEFAULT NULL AFTER `ip_address`"); } catch (\Exception $e) {}
            }
            $checkAssigned = $pdo->query("SHOW COLUMNS FROM `server_ips` LIKE 'assigned_to'")->fetch();
            if (!$checkAssigned) {
                try { $pdo->exec("ALTER TABLE `server_ips` ADD COLUMN `assigned_to` INT DEFAULT NULL AFTER `hostname`, ADD INDEX `assigned_idx` (`assigned_to`)"); } catch (\Exception $e) {}
            }
        } catch (\Exception $e) {}
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->ensureTable();
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        $ips = $this->db->table('server_ips')->orderBy('created_at', 'DESC')->get() ?: [];
        $accounts = [];
        try { $accounts = $this->db->table('hosting_users')->get() ?: []; } catch (\Exception $e) {}
        $nameservers = [];
        try { $nameservers = $this->db->table('dns_nameservers')->get() ?: []; } catch (\Exception $e) {}

        $accountMap = [];
        foreach ($accounts as $a) {
            $accountMap[$a->id] = $a;
        }

        return $this->view('admin.ip.index', [
            'user' => $user,
            'ips' => $ips,
            'accounts' => $accounts,
            'accountMap' => $accountMap,
            'nameservers' => $nameservers,
            'theme_settings' => $theme_settings,
            'title' => 'IP Management'
        ]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->ensureTable();
        $ip = trim($this->request->post('ip_address', ''));
        $server = trim($this->request->post('server', ''));
        $ns1 = trim($this->request->post('ns1', ''));
        $ns2 = trim($this->request->post('ns2', ''));

        if (!$ip) {
            $_SESSION['error_message'] = 'IP address is required.';
            $this->response->redirect('/admin/ip');
            exit;
        }

        $exists = $this->db->table('server_ips')->where('ip_address', $ip)->first();
        if ($exists) {
            $_SESSION['error_message'] = 'This IP already exists in the pool.';
            $this->response->redirect('/admin/ip');
            exit;
        }

        $this->db->table('server_ips')->insertGetId([
            'ip_address' => $ip,
            'server' => $server,
            'hostname' => $server ?: $ip,
            'ns1' => $ns1,
            'ns2' => $ns2,
            'is_active' => 1,
        ]);

        $_SESSION['success_message'] = "IP {$ip} added to pool.";
        $this->response->redirect('/admin/ip');
        exit;
    }

    public function assign($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->ensureTable();
        $accountId = (int)$this->request->get('account_id', 0);

        $ip = $this->db->table('server_ips')->where('id', $id)->first();
        if (!$ip) {
            $_SESSION['error_message'] = 'IP not found.';
            $this->response->redirect('/admin/ip');
            exit;
        }

        if ($accountId) {
            $account = $this->db->table('hosting_users')->where('id', $accountId)->first();
            if (!$account) {
                $_SESSION['error_message'] = 'Account not found.';
                $this->response->redirect('/admin/ip');
                exit;
            }
            $this->db->table('server_ips')->where('id', $id)->update(['assigned_to' => $accountId]);
            $_SESSION['success_message'] = "IP {$ip->ip_address} assigned to {$account->username}.";
        } else {
            $_SESSION['error_message'] = 'Please specify an account to assign.';
        }

        $this->response->redirect('/admin/ip');
        exit;
    }

    public function unassign($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->ensureTable();
        $ip = $this->db->table('server_ips')->where('id', $id)->first();
        if (!$ip) {
            $_SESSION['error_message'] = 'IP not found.';
            $this->response->redirect('/admin/ip');
            exit;
        }

        $this->db->table('server_ips')->where('id', $id)->update(['assigned_to' => null]);
        $_SESSION['success_message'] = "IP {$ip->ip_address} unassigned.";
        $this->response->redirect('/admin/ip');
        exit;
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->ensureTable();
        $ip = $this->db->table('server_ips')->where('id', $id)->first();
        if (!$ip) {
            $_SESSION['error_message'] = 'IP not found.';
            $this->response->redirect('/admin/ip');
            exit;
        }

        $this->db->table('server_ips')->where('id', $id)->delete();
        $_SESSION['success_message'] = "IP {$ip->ip_address} removed from pool.";
        $this->response->redirect('/admin/ip');
        exit;
    }

    public function nameservers()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $ns1 = trim($this->request->post('ns1', ''));
        $ns2 = trim($this->request->post('ns2', ''));
        $ns3 = trim($this->request->post('ns3', ''));
        $ns4 = trim($this->request->post('ns4', ''));

        try {
            $pdo = $this->db->pdo();
            $pdo->exec("CREATE TABLE IF NOT EXISTS `dns_nameservers` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nameserver` VARCHAR(255) NOT NULL,
                `ip_address` VARCHAR(45) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {}

        $this->db->table('dns_nameservers')->where('1', 1)->delete();
        foreach ([$ns1, $ns2, $ns3, $ns4] as $ns) {
            if ($ns) $this->db->table('dns_nameservers')->insertGetId(['nameserver' => $ns]);
        }

        $_SESSION['success_message'] = 'Nameservers updated.';
        $this->response->redirect('/admin/ip');
        exit;
    }
}
