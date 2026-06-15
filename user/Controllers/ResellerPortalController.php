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
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $user = $this->auth->user();
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
            'user' => $u, 'reseller' => $this->reseller, 'title' => 'Reseller Dashboard',
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
            'user' => $u, 'reseller' => $this->reseller, 'title' => 'Clients', 'accounts' => $accounts, 'pkgNames' => $pkgNames,
        ]);
    }

    public function packages()
    {
        $u = $this->requireReseller();
        $packages = $this->db->table('hosting_packages')->get() ?: [];
        return $this->view('user.reseller.packages', [
            'user' => $u, 'reseller' => $this->reseller, 'title' => 'Packages', 'packages' => $packages,
        ]);
    }

    public function branding()
    {
        $u = $this->requireReseller();
        return $this->view('user.reseller.branding', [
            'user' => $u, 'reseller' => $this->reseller, 'title' => 'Branding',
        ]);
    }

    public function billing()
    {
        $u = $this->requireReseller();
        $invoices = $this->db->table('invoices')->where('reseller_id', $this->reseller->id)->get() ?: [];
        $totalOwed = 0;
        foreach ($invoices as $inv) { if ($inv->status === 'sent' || $inv->status === 'overdue') $totalOwed += $inv->total; }
        return $this->view('user.reseller.billing', [
            'user' => $u, 'reseller' => $this->reseller, 'title' => 'Billing', 'invoices' => $invoices, 'totalOwed' => $totalOwed,
        ]);
    }

    public function support()
    {
        $u = $this->requireReseller();
        $tickets = $this->db->table('tickets')->get() ?: [];
        return $this->view('user.reseller.support', [
            'user' => $u, 'reseller' => $this->reseller, 'title' => 'Support', 'tickets' => $tickets,
        ]);
    }
}
