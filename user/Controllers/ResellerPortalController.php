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
        // Only packages matching the reseller's type (web_reseller or icecast_reseller)
        $type = $this->reseller->type ?? 'web_reseller';
        $stmt = $this->db->pdo()->query("SELECT * FROM hosting_packages WHERE is_active = 1 AND type IN ('web_hosting','icecast','web_reseller','icecast_reseller') ORDER BY type ASC") ?: [];
        $packages = $stmt ? $stmt->fetchAll(\PDO::FETCH_OBJ) : [];
        return $this->view('user.reseller.packages', [
            'user' => $u, 'reseller' => $this->reseller, 'layout' => 'reseller_layout', 'title' => 'Packages', 'packages' => $packages,
        ]);
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
}
