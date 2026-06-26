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

    protected function getCategories()
    {
        return $this->db->table('package_categories')->get() ?: [];
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
        $categories = $this->getCategories();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        $total = count($packages);
        $active = count(array_filter($packages, function($p) { return ($p->is_active ?? 0) == 1; }));

        // Stats by type category
        $statsByType = ['hosting' => 0, 'streaming' => 0, 'game' => 0, 'addon' => 0];
        foreach ($packages as $p) {
            $t = $p->type ?? '';
            if (in_array($t, ['web_hosting', 'web_reseller'])) $statsByType['hosting']++;
            elseif (in_array($t, ['shoutcast', 'shoutcast_reseller', 'icecast', 'icecast_reseller'])) $statsByType['streaming']++;
            elseif (in_array($t, ['game_server'])) $statsByType['game']++;
            elseif (in_array($t, ['chat_room', 'chat_room_voice', 'dj_panel'])) $statsByType['addon']++;
        }

        // Usage counts
        $usageCounts = [];
        try {
            $allUsers = $this->db->table('hosting_users')->get() ?: [];
            $allResellers = $this->db->table('resellers')->get() ?: [];
            $allStations = $this->db->table('streaming_stations')->get() ?: [];
            foreach ($packages as $p) {
                $cnt = ['accounts' => 0, 'resellers' => 0, 'stations' => 0];
                foreach ($allUsers as $u) { if ($u->package_id == $p->id) $cnt['accounts']++; }
                foreach ($allResellers as $r) { if ($r->package_id == $p->id) $cnt['resellers']++; }
                foreach ($allStations as $s) { if ($s->package_id == $p->id) $cnt['stations']++; }
                if ($cnt['accounts'] || $cnt['resellers'] || $cnt['stations']) $usageCounts[$p->id] = $cnt;
            }
        } catch (\Exception $e) {}

        return $this->view('admin.package.index', [
            'user' => $user, 'packages' => $packages, 'categories' => $categories,
            'totalPackages' => $total, 'activePackages' => $active,
            'statsByType' => $statsByType, 'usageCounts' => $usageCounts,
            'theme_settings' => $theme_settings
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $categories = $this->getCategories();
        $featureLists = $this->getFeatureLists();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.package.create', ['user' => $user, 'categories' => $categories, 'featureLists' => $featureLists, 'theme_settings' => $theme_settings]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $features = $this->request->post('features', []);
        $data = [
            'name' => $this->request->post('name', ''),
            'type' => $this->request->post('type', 'Web Hosting'),
            'description' => $this->request->post('description', ''),
            'features' => json_encode($features),
            'feature_list_id' => (int)$this->request->post('feature_list_id', 0) ?: null,
            'max_domains' => (int)$this->request->post('max_domains', 1),
            'max_subdomains' => (int)$this->request->post('max_subdomains', 0),
            'monthly_price' => (float)$this->request->post('monthly_price', 0),
            'quarterly_price' => (float)$this->request->post('quarterly_price', 0),
            'semi_annual_price' => (float)$this->request->post('semi_annual_price', 0),
            'annual_price' => (float)$this->request->post('annual_price', 0),
            'setup_fee' => (float)$this->request->post('setup_fee', 0),
            'disk_space' => (int)$this->request->post('disk_space', 0),
            'bandwidth' => (int)$this->request->post('bandwidth', 0),
            'listener_limit' => (int)$this->request->post('listener_limit', 0),
            'bitrate' => (int)$this->request->post('bitrate', 0),
            'storage_limit' => (int)$this->request->post('storage_limit', 0),
            'dj_accounts' => (int)$this->request->post('dj_accounts', 0),
            'sort_order' => (int)$this->request->post('sort_order', 0),
            'is_active' => 1,
            'icecast_enabled' => $this->request->post('icecast_enabled') ? 1 : 0,
            'dj_panel_enabled' => $this->request->post('dj_panel_enabled') ? 1 : 0,
            'live_chat_enabled' => $this->request->post('live_chat_enabled') ? 1 : 0,
            'chatroom_enabled' => $this->request->post('chatroom_enabled') ? 1 : 0,
            'chatroom_voice_enabled' => $this->request->post('chatroom_voice_enabled') ? 1 : 0,
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
        $categories = $this->getCategories();
        $featureLists = $this->getFeatureLists();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.package.edit', ['user' => $user, 'package' => $package, 'categories' => $categories, 'featureLists' => $featureLists, 'theme_settings' => $theme_settings]);
    }

    public function update($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $features = $this->request->post('features', []);
        $data = [
            'name' => $this->request->post('name', ''),
            'type' => $this->request->post('type', 'Web Hosting'),
            'description' => $this->request->post('description', ''),
            'features' => json_encode($features),
            'feature_list_id' => (int)$this->request->post('feature_list_id', 0) ?: null,
            'max_domains' => (int)$this->request->post('max_domains', 1),
            'max_subdomains' => (int)$this->request->post('max_subdomains', 0),
            'monthly_price' => (float)$this->request->post('monthly_price', 0),
            'quarterly_price' => (float)$this->request->post('quarterly_price', 0),
            'semi_annual_price' => (float)$this->request->post('semi_annual_price', 0),
            'annual_price' => (float)$this->request->post('annual_price', 0),
            'setup_fee' => (float)$this->request->post('setup_fee', 0),
            'disk_space' => (int)$this->request->post('disk_space', 0),
            'bandwidth' => (int)$this->request->post('bandwidth', 0),
            'listener_limit' => (int)$this->request->post('listener_limit', 0),
            'bitrate' => (int)$this->request->post('bitrate', 0),
            'storage_limit' => (int)$this->request->post('storage_limit', 0),
            'dj_accounts' => (int)$this->request->post('dj_accounts', 0),
            'sort_order' => (int)$this->request->post('sort_order', 0),
            'is_active' => $this->request->post('is_active') === 'on' ? 1 : 0,
            'icecast_enabled' => $this->request->post('icecast_enabled') ? 1 : 0,
            'dj_panel_enabled' => $this->request->post('dj_panel_enabled') ? 1 : 0,
            'live_chat_enabled' => $this->request->post('live_chat_enabled') ? 1 : 0,
            'chatroom_enabled' => $this->request->post('chatroom_enabled') ? 1 : 0,
            'chatroom_voice_enabled' => $this->request->post('chatroom_voice_enabled') ? 1 : 0,
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

    // --- Category management ---

    public function categories()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $categories = $this->getCategories();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.package.categories', ['user' => $user, 'categories' => $categories, 'theme_settings' => $theme_settings, 'title' => 'Package Categories']);
    }

    public function storeCategory()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('package_categories')->insertGetId([
            'name' => $this->request->post('name', ''),
            'icon' => $this->request->post('icon', '📦'),
            'sort_order' => (int)$this->request->post('sort_order', 0),
        ]);
        $_SESSION['success_message'] = 'Category created.';
        $this->response->redirect('/admin/packages/categories');
        exit;
    }

    public function updateCategory($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $name = $this->request->post('name', '');
        $icon = $this->request->post('icon', '');
        $sort = (int)$this->request->post('sort_order', 0);
        if ($name) {
            $data = ['name' => $name];
            if ($icon) $data['icon'] = $icon;
            if ($sort) $data['sort_order'] = $sort;
            $this->db->table('package_categories')->where('id', $id)->update($data);
            $_SESSION['success_message'] = 'Category updated.';
        }
        $this->response->redirect('/admin/packages/categories');
        exit;
    }

    public function deleteCategory($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('package_categories')->where('id', $id)->delete();
        $_SESSION['success_message'] = 'Category deleted.';
        $this->response->redirect('/admin/packages/categories');
        exit;
    }

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

    public function clone($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { return $this->response->json(['error' => 'Unauthorized'], 401); }
        $original = $this->db->table('hosting_packages')->where('id', $id)->first();
        if (!$original) { return $this->response->json(['error' => 'Not found'], 404); }
        $data = (array)$original;
        unset($data['id'], $data['created_at'], $data['updated_at']);
        $data['name'] = $original->name . ' (Clone)';
        $data['is_active'] = 0;
        $data['sort_order'] = ((int)$original->sort_order) + 1;
        $this->db->table('hosting_packages')->insertGetId($data);
        return $this->response->json(['success' => true]);
    }

    public function toggle($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { return $this->response->json(['error' => 'Unauthorized'], 401); }
        $pkg = $this->db->table('hosting_packages')->where('id', $id)->first();
        if (!$pkg) { return $this->response->json(['error' => 'Not found'], 404); }
        $new = ($pkg->is_active ?? 0) ? 0 : 1;
        $this->db->table('hosting_packages')->where('id', $id)->update(['is_active' => $new]);
        return $this->response->json(['success' => true, 'is_active' => $new]);
    }

    public function bulk()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { return $this->response->json(['error' => 'Unauthorized'], 401); }
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        $ids = $input['ids'] ?? [];
        if (empty($ids)) { return $this->response->json(['error' => 'No IDs'], 400); }
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
        return $this->response->json(['success' => true]);
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
        return json_encode($grouped);
    }
}
