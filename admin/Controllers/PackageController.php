<?php

namespace Admin\Controllers;

use Core\Controller;

class PackageController extends Controller
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

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $packages = $this->db->table('hosting_packages')->get();
        $types = ['web_hosting', 'web_reseller', 'shoutcast', 'shoutcast_reseller', 'icecast', 'icecast_reseller', 'vps', 'dedicated'];
        $grouped = [];
        foreach ($types as $type) {
            $grouped[$type] = array_filter($packages, function($p) use ($type) { return $p->type === $type; });
        }
        $resellers = $this->db->table('resellers')->get();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        $total = count($packages);
        $active = count(array_filter($packages, function($p) { return ($p->is_active ?? 0) == 1; }));
        return $this->view('admin.package.index', [
            'user' => $user,
            'grouped' => $grouped,
            'types' => $types,
            'packagesStats' => ['total_packages' => $total, 'active_packages' => $active],
            'resellers' => $resellers,
            'theme_settings' => $theme_settings
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $types = ['web_hosting', 'web_reseller', 'shoutcast', 'shoutcast_reseller', 'icecast', 'icecast_reseller', 'vps', 'dedicated'];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.package.create', ['user' => $user, 'types' => $types, 'theme_settings' => $theme_settings]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $features = $this->request->post('features', []);
        $data = [
            'name' => $this->request->post('name', ''),
            'type' => $this->request->post('type', 'web_hosting'),
            'description' => $this->request->post('description', ''),
            'features' => json_encode($features),
            'monthly_price' => (float)$this->request->post('monthly_price', 0),
            'disk_space' => (int)$this->request->post('disk_space', 0),
            'bandwidth' => (int)$this->request->post('bandwidth', 0),
            'listener_limit' => (int)$this->request->post('listener_limit', 0),
            'bitrate' => (int)$this->request->post('bitrate', 0),
            'storage_limit' => (int)$this->request->post('storage_limit', 0),
            'dj_accounts' => (int)$this->request->post('dj_accounts', 0),
            'sort_order' => (int)$this->request->post('sort_order', 0),
            'is_active' => 1,
        ];
        $this->db->table('hosting_packages')->insertGetId($data);
        $_SESSION['success_message'] = 'Package created.';
        $this->response->redirect('/admin/packages');
        exit;
    }

    public function edit($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $package = $this->db->table('hosting_packages')->where('id', $id)->first();
        if (!$package) { $this->response->redirect('/admin/packages'); exit; }
        if (is_string($package->features)) $package->features = json_decode($package->features, true) ?? [];
        $types = ['web_hosting', 'web_reseller', 'shoutcast', 'shoutcast_reseller', 'icecast', 'icecast_reseller', 'vps', 'dedicated'];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.package.edit', ['user' => $user, 'package' => $package, 'types' => $types, 'theme_settings' => $theme_settings]);
    }

    public function update($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $features = $this->request->post('features', []);
        $data = [
            'name' => $this->request->post('name', ''),
            'type' => $this->request->post('type', 'web_hosting'),
            'description' => $this->request->post('description', ''),
            'features' => json_encode($features),
            'monthly_price' => (float)$this->request->post('monthly_price', 0),
            'disk_space' => (int)$this->request->post('disk_space', 0),
            'bandwidth' => (int)$this->request->post('bandwidth', 0),
            'listener_limit' => (int)$this->request->post('listener_limit', 0),
            'bitrate' => (int)$this->request->post('bitrate', 0),
            'storage_limit' => (int)$this->request->post('storage_limit', 0),
            'dj_accounts' => (int)$this->request->post('dj_accounts', 0),
            'sort_order' => (int)$this->request->post('sort_order', 0),
            'is_active' => $this->request->post('is_active') === 'on' ? 1 : 0,
        ];
        $this->db->table('hosting_packages')->where('id', $id)->update($data);
        $_SESSION['success_message'] = 'Package updated.';
        $this->response->redirect('/admin/packages');
        exit;
    }

    public function destroy($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('hosting_packages')->where('id', $id)->update(['is_active' => 0]);
        $_SESSION['success_message'] = 'Package deleted.';
        $this->response->redirect('/admin/packages');
        exit;
    }

    public function upgrade($accountId)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $newPackageId = (int)$this->request->post('package_id', 0);
        if ($newPackageId) {
            $this->db->table('hosting_users')->where('id', $accountId)->update(['package_id' => $newPackageId]);
            $_SESSION['success_message'] = 'Account upgraded to new package.';
        }
        $this->response->redirect('/admin/account');
        exit;
    }

    public function assignReseller($packageId)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $resellerId = (int)$this->request->post('reseller_id', 0);
        if ($resellerId) {
            $this->db->table('hosting_packages')->where('id', $packageId)->update(['reseller_id' => $resellerId]);
            $_SESSION['success_message'] = 'Package assigned to reseller.';
        }
        $this->response->redirect('/admin/packages');
        exit;
    }

    public function apiList()
    {
        $packages = $this->db->table('hosting_packages')->where('is_active', 1)->get();
        $types = ['web_hosting', 'web_reseller', 'shoutcast', 'shoutcast_reseller', 'icecast', 'icecast_reseller', 'vps', 'dedicated'];
        $grouped = [];
        foreach ($types as $type) {
            $items = array_filter($packages, function($p) use ($type) { return $p->type === $type; });
            if ($items) $grouped[$type] = array_values($items);
        }
        return json_encode($grouped);
    }
}
