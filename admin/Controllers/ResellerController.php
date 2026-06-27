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
        $stmt = $this->db->pdo()->query("SELECT * FROM hosting_users WHERE reseller_id IS NULL OR reseller_id = 0");
        $accounts = $stmt ? $stmt->fetchAll(\PDO::FETCH_OBJ) : [];
        $allAccounts = $this->db->table('hosting_users')->get() ?: [];
        $featureLists = $this->db->table('feature_lists')->where('is_active', 1)->orderBy('name', 'ASC')->get() ?: [];
        return $this->view('admin.reseller.create', [
            'user' => $user, 'title' => 'Create Reseller',
            'accounts' => $accounts, 'allAccounts' => $allAccounts, 'featureLists' => $featureLists,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $adminId = $this->auth->user()->id;
        $email = $this->request->post('email', '');
        $featureListId = (int)$this->request->post('feature_list_id', 0) ?: null;
        $features = $this->request->post('features', []);
        $assignedAccounts = $this->request->post('assigned_accounts', []);
        $rId = $this->db->table('resellers')->insertGetId([
            'admin_id' => $adminId, 'company_name' => $this->request->post('company_name', ''),
            'contact_name' => $this->request->post('contact_name', ''), 'email' => $email,
            'phone' => $this->request->post('phone', ''),
            'website' => $this->request->post('website', ''),
            'feature_list_id' => $featureListId,
            'features' => !empty($features) ? json_encode($features) : null,
            'is_active' => $this->request->post('is_active', 1) ? 1 : 0,
        ]);
        if (!empty($assignedAccounts)) {
            foreach ($assignedAccounts as $acctId) {
                $this->db->pdo()->prepare("UPDATE hosting_users SET reseller_id = ? WHERE id = ?")->execute([$rId, (int)$acctId]);
            }
        }
        $_SESSION['success_message'] = "Reseller {$email} created.";
        $this->response->redirect('/admin/reseller');
    }

    public function edit($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $reseller = $this->db->table('resellers')->where('id', $id)->first();
        $accounts = $this->db->table('hosting_users')->where('reseller_id', $id)->get() ?: [];
        $stmt = $this->db->pdo()->query("SELECT * FROM hosting_users WHERE reseller_id IS NULL OR reseller_id = 0");
        $unassigned = $stmt ? $stmt->fetchAll(\PDO::FETCH_OBJ) : [];
        $allAccounts = $this->db->table('hosting_users')->get() ?: [];
        $featureLists = $this->db->table('feature_lists')->where('is_active', 1)->orderBy('name', 'ASC')->get() ?: [];
        return $this->view('admin.reseller.edit', [
            'user' => $user, 'title' => 'Edit Reseller', 'reseller' => $reseller,
            'accounts' => $accounts, 'unassigned' => $unassigned, 'allAccounts' => $allAccounts,
            'featureLists' => $featureLists,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function update($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $featureListId = (int)$this->request->post('feature_list_id', 0) ?: null;
        $features = $this->request->post('features', []);
        $this->db->table('resellers')->where('id', $id)->update([
            'company_name' => $this->request->post('company_name', ''),
            'contact_name' => $this->request->post('contact_name', ''),
            'email' => $this->request->post('email', ''),
            'phone' => $this->request->post('phone', ''),
            'website' => $this->request->post('website', ''),
            'feature_list_id' => $featureListId,
            'features' => !empty($features) ? json_encode($features) : null,
            'is_active' => $this->request->post('is_active', 1) ? 1 : 0,
        ]);
        $this->db->pdo()->prepare("UPDATE hosting_users SET reseller_id = NULL WHERE reseller_id = ?")->execute([(int)$id]);
        $assignedAccounts = $this->request->post('assigned_accounts', []);
        if (!empty($assignedAccounts)) {
            foreach ($assignedAccounts as $acctId) {
                $this->db->pdo()->prepare("UPDATE hosting_users SET reseller_id = ? WHERE id = ?")->execute([(int)$id, (int)$acctId]);
            }
        }
        $_SESSION['success_message'] = 'Reseller updated.';
        $this->response->redirect('/admin/reseller');
    }

}
