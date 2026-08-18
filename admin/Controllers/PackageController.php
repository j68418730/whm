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

    protected function getFeatureLists()
    {
        return $this->db->table('feature_lists')->where('is_active', 1)->orderBy('name', 'ASC')->get() ?: [];
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $packages = $this->db->table('hosting_packages')->orderBy('type', 'ASC')->orderBy('sort_order', 'ASC')->get() ?: [];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        $total = count($packages);
        $active = count(array_filter($packages, function($p) { return ($p->is_active ?? 0) == 1; }));

        // Product counts per package
        $productCounts = [];
        try {
            $allProducts = $this->db->table('billing_products')->get() ?: [];
            foreach ($allProducts as $bp) {
                if ($bp->package_id) {
                    if (!isset($productCounts[$bp->package_id])) $productCounts[$bp->package_id] = 0;
                    $productCounts[$bp->package_id]++;
                }
            }
        } catch (\Exception $e) {}

        return $this->view('admin.package.index', [
            'user' => $user, 'packages' => $packages,
            'packagesStats' => ['total_packages' => $total, 'active_packages' => $active],
            'productCounts' => $productCounts,
            'theme_settings' => $theme_settings
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $featureLists = $this->getFeatureLists();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.package.create', ['user' => $user, 'featureLists' => $featureLists, 'theme_settings' => $theme_settings]);
    }

    protected function mergePkgFeatures()
    {
        $features = $this->request->post('features', []);
        $featMap = [];
        foreach ((array)$features as $f) {
            $featMap[$f] = true;
        }
        $customPkg = $this->request->post('custom_pkg', []);
        $streaming = [];
        if ($this->request->post('custom_streaming_enabled')) {
            $streaming['enabled'] = true;
            foreach ($customPkg as $k => $v) {
                if (strpos($k, 'str_') === 0) {
                    $streaming[substr($k, 4)] = $v;
                }
            }
        }
        $game = [];
        if ($this->request->post('custom_game_enabled')) {
            $game['enabled'] = true;
            foreach ($customPkg as $k => $v) {
                if (strpos($k, 'game_') === 0) {
                    $game[substr($k, 5)] = $v;
                }
            }
        }
        $merged = array_merge($featMap, [
            'streaming_package' => $streaming,
            'game_package' => $game,
        ]);
        return json_encode($merged);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $data = [
            'name' => $this->request->post('name', ''),
            'type' => $this->request->post('type', 'web_hosting'),
            'php_version' => $this->request->post('php_version', '8.2'),
            'features' => $this->mergePkgFeatures(),
            'feature_list_id' => (int)$this->request->post('feature_list_id', 0) ?: null,
            'max_domains' => (int)$this->request->post('max_domains', 1),
            'max_subdomains' => (int)$this->request->post('max_subdomains', 0),
            'disk_space' => (int)$this->request->post('disk_space', 0),
            'bandwidth' => (int)$this->request->post('bandwidth', 0),
            'email_accounts' => (int)$this->request->post('email_accounts', 0),
            'ftp_accounts' => (int)$this->request->post('ftp_accounts', 0),
            'databases' => (int)$this->request->post('databases', 0),
            'subdomains' => (int)$this->request->post('subdomains', 0),
            'parked_domains' => (int)$this->request->post('parked_domains', 0),
            'addon_domains' => (int)$this->request->post('addon_domains', 0),
            'listener_limit' => (int)$this->request->post('listener_limit', 0),
            'bitrate' => (int)$this->request->post('bitrate', 0),
            'dj_accounts' => (int)$this->request->post('dj_accounts', 0),
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
        if (isset($package->features) && is_string($package->features)) $package->features = json_decode($package->features, true) ?? [];
        $billingProducts = $this->db->table('billing_products')->where('package_id', $id)->get() ?: [];
        $featureLists = $this->getFeatureLists();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.package.edit', ['user' => $user, 'package' => $package, 'billingProducts' => $billingProducts, 'featureLists' => $featureLists, 'theme_settings' => $theme_settings]);
    }

    public function update($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $data = [
            'name' => $this->request->post('name', ''),
            'type' => $this->request->post('type', 'web_hosting'),
            'php_version' => $this->request->post('php_version', '8.2'),
            'features' => $this->mergePkgFeatures(),
            'feature_list_id' => (int)$this->request->post('feature_list_id', 0) ?: null,
            'max_domains' => (int)$this->request->post('max_domains', 1),
            'max_subdomains' => (int)$this->request->post('max_subdomains', 0),
            'disk_space' => (int)$this->request->post('disk_space', 0),
            'bandwidth' => (int)$this->request->post('bandwidth', 0),
            'email_accounts' => (int)$this->request->post('email_accounts', 0),
            'ftp_accounts' => (int)$this->request->post('ftp_accounts', 0),
            'databases' => (int)$this->request->post('databases', 0),
            'subdomains' => (int)$this->request->post('subdomains', 0),
            'parked_domains' => (int)$this->request->post('parked_domains', 0),
            'addon_domains' => (int)$this->request->post('addon_domains', 0),
            'listener_limit' => (int)$this->request->post('listener_limit', 0),
            'bitrate' => (int)$this->request->post('bitrate', 0),
            'dj_accounts' => (int)$this->request->post('dj_accounts', 0),
            'is_active' => $this->request->post('is_active') === 'on' ? 1 : (($this->request->post('is_active') ?? '') === '1' ? 1 : 0),
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
        $_SESSION['success_message'] = 'Package deactivated.';
        $this->response->redirect('/admin/packages');
        exit;
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $pkg = $this->db->table('hosting_packages')->where('id', $id)->first();
        if (!$pkg) { $_SESSION['error_message'] = 'Package not found.'; $this->response->redirect('/admin/packages'); exit; }
        $inUse = $this->db->table('hosting_users')->where('package_id', $id)->first();
        if ($inUse) { $_SESSION['error_message'] = 'Cannot delete — package is in use by active accounts.'; $this->response->redirect('/admin/packages'); exit; }
        $inUseReseller = $this->db->table('resellers')->where('package_id', $id)->first();
        if ($inUseReseller) { $_SESSION['error_message'] = 'Cannot delete — package is assigned to resellers.'; $this->response->redirect('/admin/packages'); exit; }
        $inUseStation = $this->db->table('streaming_stations')->where('package_id', $id)->first();
        if ($inUseStation) { $_SESSION['error_message'] = 'Cannot delete — package is in use by streaming stations.'; $this->response->redirect('/admin/packages'); exit; }
        if ($pkg->product_id) {
            $this->db->table('billing_products')->where('id', $pkg->product_id)->update(['is_active' => 0]);
        }
        $this->db->table('hosting_packages')->where('id', $id)->delete();
        $_SESSION['success_message'] = 'Package permanently deleted.';
        $this->response->redirect('/admin/packages');
        exit;
    }


    // --- Category management ---

    public function upgrade($accountId)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $packageId = (int)$this->request->post('package_id', 0);
        if (!$packageId) { $_SESSION['error_message'] = 'No package selected.'; $this->response->redirect('/admin/account'); exit; }
        $this->db->table('hosting_users')->where('id', (int)$accountId)->update(['package_id' => $packageId]);
        $_SESSION['success_message'] = 'Account upgraded.';
        $this->response->redirect('/admin/account/show/' . $accountId);
    }

    public function assignReseller($packageId)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $resellerId = (int)$this->request->post('reseller_id', 0);
        $this->db->table('hosting_packages')->where('id', (int)$packageId)->update(['reseller_id' => $resellerId ?: null]);
        $_SESSION['success_message'] = 'Reseller assigned.';
        $this->response->redirect('/admin/packages');
    }

    public function toggle($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized'])->send(); exit; }
        $pkg = $this->db->table('hosting_packages')->where('id', $id)->first();
        if (!$pkg) { $this->response->json(['error' => 'Not found'])->send(); exit; }
        $new = ($pkg->is_active ?? 0) ? 0 : 1;
        $this->db->table('hosting_packages')->where('id', $id)->update(['is_active' => $new]);
        $this->response->json(['success' => true, 'is_active' => $new])->send();
        exit;
    }

    public function bulk()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized'])->send(); exit; }
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        $ids = $input['ids'] ?? [];
        if (empty($ids)) { $this->response->json(['error' => 'No IDs'])->send(); exit; }
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($action === 'delete') $this->db->table('hosting_packages')->where('id', $id)->update(['is_active' => 0]);
            elseif ($action === 'enable') $this->db->table('hosting_packages')->where('id', $id)->update(['is_active' => 1]);
            elseif ($action === 'disable') $this->db->table('hosting_packages')->where('id', $id)->update(['is_active' => 0]);
            elseif ($action === 'clone') {
                $orig = $this->db->table('hosting_packages')->where('id', $id)->first();
                if ($orig) {
                    $d = (array)$orig; unset($d['id'], $d['created_at'], $d['updated_at']);
                    $d['name'] = $orig->name . ' (Clone)';
                    $d['is_active'] = 0;
                    $this->db->table('hosting_packages')->insertGetId($d);
                }
            }
        }
        $this->response->json(['success' => true])->send();
        exit;
    }

    public function clone($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized'])->send(); exit; }
        $original = $this->db->table('hosting_packages')->where('id', $id)->first();
        if (!$original) { $this->response->json(['error' => 'Not found'])->send(); exit; }
        $data = (array)$original;
        unset($data['id'], $data['created_at'], $data['updated_at']);
        $data['name'] = $original->name . ' (Clone)';
        $data['is_active'] = 0;
        $data['sort_order'] = ((int)$original->sort_order) + 1;
        $this->db->table('hosting_packages')->insertGetId($data);
        $this->response->json(['success' => true])->send();
        exit;
    }

    public function apiList()
    {
        $packages = $this->db->table('hosting_packages')->where('is_active', 1)->get();
        $categories = $this->getCategories();
        $grouped = [];
        foreach ($categories as $cat) {
            $items = array_filter($packages, function($p) use ($cat) { return $p->type === $cat->name; });
            if ($items) $grouped[$cat->name] = array_values($items);
        }
        header('Content-Type: application/json');
        echo json_encode($grouped);
        exit;
    }
}
