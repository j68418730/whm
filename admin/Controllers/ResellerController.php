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

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $resellers = $this->db->table('resellers')->get() ?: [];
        $total = count($resellers);
        $active = 0;
        $totalAccounts = 0;
        $acctCounts = [];
        foreach ($resellers as $r) {
            if ($r->is_active) $active++;
            $cnt = count($this->db->table('hosting_users')->where('reseller_id', $r->id)->get() ?: []);
            $totalAccounts += $cnt;
            $acctCounts[$r->id] = $cnt;
        }
        return $this->view('admin.reseller.index', [
            'user' => $user, 'title' => 'Reseller Center', 'resellers' => $resellers,
            'acctCounts' => $acctCounts,
            'resellerStats' => ['total_resellers' => $total, 'active_resellers' => $active, 'accounts_owned_by_resellers' => $totalAccounts],
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.reseller.create', [
            'user' => $user, 'title' => 'Create Reseller',
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $adminId = $this->auth->user()->id;
        $email = $this->request->post('email', '');
        $this->db->table('resellers')->insertGetId([
            'admin_id' => $adminId, 'company_name' => $this->request->post('company_name', ''),
            'contact_name' => $this->request->post('contact_name', ''), 'email' => $email,
            'phone' => $this->request->post('phone', ''), 'website' => $this->request->post('website', ''),
            'is_active' => $this->request->post('is_active', 1),
        ]);
        $_SESSION['success_message'] = "Reseller {$email} created.";
        $this->response->redirect('/admin/reseller');
    }

    public function edit($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $reseller = $this->db->table('resellers')->where('id', $id)->first();
        return $this->view('admin.reseller.edit', [
            'user' => $user, 'title' => 'Edit Reseller', 'reseller' => $reseller,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function update($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('resellers')->where('id', $id)->update([
            'company_name' => $this->request->post('company_name', ''),
            'contact_name' => $this->request->post('contact_name', ''),
            'email' => $this->request->post('email', ''),
            'phone' => $this->request->post('phone', ''),
            'website' => $this->request->post('website', ''),
            'is_active' => $this->request->post('is_active', 1),
        ]);
        $_SESSION['success_message'] = 'Reseller updated.';
        $this->response->redirect('/admin/reseller');
    }

    public function show($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $reseller = $this->db->table('resellers')->where('id', $id)->first();
        $accounts = $this->db->table('hosting_users')->where('reseller_id', $id)->get() ?: [];
        $pkgNames = [];
        foreach ($accounts as $a) {
            $pkg = $this->db->table('hosting_packages')->where('id', $a->package_id)->first();
            $pkgNames[$a->id] = $pkg ? $pkg->name : '-';
        }
        return $this->view('admin.reseller.show', [
            'user' => $user, 'title' => 'Reseller Details', 'reseller' => $reseller, 'accounts' => $accounts,
            'pkgNames' => $pkgNames,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }
}
