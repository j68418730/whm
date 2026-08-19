<?php

namespace Admin\Controllers;

use Core\Controller;

class GameServersController extends Controller
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

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
    }

    protected function theme()
    {
        $user = $this->auth->user();
        return json_decode($user->theme_settings ?? '{}', true);
    }

    // ─── Game Types ───

    public function types()
    {
        $this->guard();
        $user = $this->auth->user();
        $types = $this->db->table('game_types')->orderBy('sort_order', 'ASC')->get() ?: [];
        return $this->view('admin.gameservers.types', [
            'user' => $user,
            'title' => 'Game Types',
            'theme_settings' => $this->theme(),
            'types' => $types,
        ]);
    }

    public function typesStore()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $data = [
            'name' => $this->request->post('name', ''),
            'description' => $this->request->post('description', ''),
            'icon' => $this->request->post('icon', ''),
            'pricing_model' => $this->request->post('pricing_model', 'per_slot'),
            'min_slots' => (int)$this->request->post('min_slots', 1),
            'max_slots' => (int)$this->request->post('max_slots', 100),
            'price_per_slot' => (float)$this->request->post('price_per_slot', 0),
            'setup_fee' => (float)$this->request->post('setup_fee', 0),
            'billing_cycle' => $this->request->post('billing_cycle', 'monthly'),
            'is_active' => (int)$this->request->post('is_active', 1),
            'sort_order' => (int)$this->request->post('sort_order', 0),
        ];
        if ($id) {
            $this->db->table('game_types')->where('id', $id)->update($data);
            $_SESSION['success_message'] = 'Game type updated.';
        } else {
            $this->db->table('game_types')->insertGetId($data);
            $_SESSION['success_message'] = 'Game type created.';
        }
        $this->response->redirect('/admin/games');
    }

    public function typesDelete($id)
    {
        $this->guard();
        $this->db->table('game_slot_pricing')->where('game_type_id', (int)$id)->delete();
        $this->db->table('game_packages')->where('game_type_id', (int)$id)->delete();
        $this->db->table('game_types')->where('id', (int)$id)->delete();
        $_SESSION['success_message'] = 'Game type deleted.';
        $this->response->redirect('/admin/games');
    }

    // ─── Slot Pricing ───

    public function pricing()
    {
        $this->guard();
        $user = $this->auth->user();
        $pricing = $this->db->table('game_slot_pricing')->get() ?: [];
        $types = $this->db->table('game_types')->orderBy('sort_order', 'ASC')->get() ?: [];
        $typeMap = [];
        foreach ($types as $t) $typeMap[$t->id] = $t->name;
        return $this->view('admin.gameservers.pricing', [
            'user' => $user,
            'title' => 'Slot Pricing',
            'theme_settings' => $this->theme(),
            'pricing' => $pricing,
            'types' => $types,
            'typeMap' => $typeMap,
        ]);
    }

    public function pricingStore()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $data = [
            'game_type_id' => (int)$this->request->post('game_type_id', 0),
            'min_slots' => (int)$this->request->post('min_slots', 1),
            'max_slots' => (int)$this->request->post('max_slots', 100),
            'price_per_slot' => (float)$this->request->post('price_per_slot', 0),
        ];
        if ($id) {
            $this->db->table('game_slot_pricing')->where('id', $id)->update($data);
            $_SESSION['success_message'] = 'Pricing tier updated.';
        } else {
            $this->db->table('game_slot_pricing')->insertGetId($data);
            $_SESSION['success_message'] = 'Pricing tier created.';
        }
        $this->response->redirect('/admin/games/pricing');
    }

    public function pricingDelete($id)
    {
        $this->guard();
        $this->db->table('game_slot_pricing')->where('id', (int)$id)->delete();
        $_SESSION['success_message'] = 'Pricing tier deleted.';
        $this->response->redirect('/admin/games/pricing');
    }

    // ─── Packages ───

    public function packages()
    {
        $this->guard();
        $user = $this->auth->user();
        $packages = $this->db->table('game_packages')->get() ?: [];
        $types = $this->db->table('game_types')->orderBy('sort_order', 'ASC')->get() ?: [];
        $typeMap = [];
        foreach ($types as $t) $typeMap[$t->id] = $t->name;
        return $this->view('admin.gameservers.packages', [
            'user' => $user,
            'title' => 'Game Packages',
            'theme_settings' => $this->theme(),
            'packages' => $packages,
            'types' => $types,
            'typeMap' => $typeMap,
        ]);
    }

    public function packagesStore()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $data = [
            'game_type_id' => (int)$this->request->post('game_type_id', 0),
            'name' => $this->request->post('name', ''),
            'description' => $this->request->post('description', ''),
            'slots' => (int)$this->request->post('slots', 10),
            'price' => (float)$this->request->post('price', 0),
            'setup_fee' => (float)$this->request->post('setup_fee', 0),
            'billing_cycle' => $this->request->post('billing_cycle', 'monthly'),
            'is_active' => (int)$this->request->post('is_active', 1),
        ];
        if ($id) {
            $this->db->table('game_packages')->where('id', $id)->update($data);
            $_SESSION['success_message'] = 'Package updated.';
        } else {
            $this->db->table('game_packages')->insertGetId($data);
            $_SESSION['success_message'] = 'Package created.';
        }
        $this->response->redirect('/admin/games/packages');
    }

    public function packagesDelete($id)
    {
        $this->guard();
        $this->db->table('game_packages')->where('id', (int)$id)->delete();
        $_SESSION['success_message'] = 'Package deleted.';
        $this->response->redirect('/admin/games/packages');
    }

    // ─── Game Servers (instances) ───

    public function servers()
    {
        $this->guard();
        $user = $this->auth->user();
        $servers = $this->db->table('game_servers')->orderBy('id', 'DESC')->get() ?: [];
        $types = $this->db->table('game_types')->orderBy('sort_order', 'ASC')->get() ?: [];
        $typeMap = [];
        foreach ($types as $t) $typeMap[$t->id] = $t->name;
        $owners = $this->db->table('hosting_users')->get() ?: [];
        $ownerMap = [];
        foreach ($owners as $o) $ownerMap[$o->id] = $o->username . ($o->domain ? ' (' . $o->domain . ')' : '');
        $serverStatusMap = ['stopped' => 'Stopped', 'running' => 'Running', 'starting' => 'Starting', 'suspended' => 'Suspended', 'installing' => 'Installing'];
        // Sync migration flag: servers created before type_id existed carry game_type string
        $settings = [];
        $rows = $this->db->table('game_settings')->get() ?: [];
        foreach ($rows as $r) $settings[$r->setting_key] = $r->setting_value;
        return $this->view('admin.gameservers.servers', [
            'user' => $user,
            'title' => 'Game Servers',
            'theme_settings' => $this->theme(),
            'servers' => $servers,
            'types' => $types,
            'typeMap' => $typeMap,
            'ownerMap' => $ownerMap,
            'serverStatusMap' => $serverStatusMap,
            'settings' => $settings,
        ]);
    }

    public function serversStore()
    {
        $this->guard();
        $id = (int)$this->request->post('id', 0);
        $data = [
            'user_id' => (int)$this->request->post('user_id', 0),
            'type_id' => (int)$this->request->post('type_id', 0),
            'name' => $this->request->post('name', ''),
            'game_type' => $this->request->post('game_type', ''),
            'port' => (int)$this->request->post('port', 0),
            'status' => $this->request->post('status', 'stopped'),
            'install_path' => $this->request->post('install_path', ''),
            'config_path' => $this->request->post('config_path', ''),
            'is_active' => (int)$this->request->post('is_active', 1),
        ];
        if (!$data['name']) {
            $_SESSION['error_message'] = 'Server name is required.';
            $this->response->redirect('/admin/games/servers'); exit;
        }
        if ($data['type_id'] && !$data['game_type']) {
            $gt = $this->db->table('game_types')->where('id', $data['type_id'])->first();
            if ($gt) $data['game_type'] = $gt->name;
        }
        if ($id) {
            $this->db->table('game_servers')->where('id', $id)->update($data);
            $_SESSION['success_message'] = 'Game server updated.';
        } else {
            if ($data['port'] <= 0) $data['port'] = $this->nextPort();
            $this->db->table('game_servers')->insertGetId($data);
            $_SESSION['success_message'] = 'Game server created.';
        }
        $this->response->redirect('/admin/games/servers');
    }

    protected function nextPort()
    {
        $stmt = $this->db->pdo()->query("SELECT COALESCE(MAX(port), 27000) AS maxport FROM game_servers");
        $row = $stmt->fetch();
        $next = ((int)($row['maxport'] ?? 27000)) + 1;
        return $next > 65535 ? 27015 : $next;
    }

    public function serversToggle($id)
    {
        $this->guard();
        $s = $this->db->table('game_servers')->where('id', (int)$id)->first();
        if (!$s) { $_SESSION['error_message'] = 'Server not found.'; $this->response->redirect('/admin/games/servers'); exit; }
        $new = $s->is_active ? 0 : 1;
        $status = $new ? ($s->status === 'suspended' ? 'stopped' : $s->status) : 'suspended';
        $this->db->table('game_servers')->where('id', (int)$id)->update(['is_active' => $new, 'status' => $status]);
        $_SESSION['success_message'] = $new ? 'Game server activated.' : 'Game server suspended.';
        $this->response->redirect('/admin/games/servers');
    }

    public function serversStatus($id)
    {
        $this->guard();
        $s = $this->db->table('game_servers')->where('id', (int)$id)->first();
        if (!$s) { $_SESSION['error_message'] = 'Server not found.'; $this->response->redirect('/admin/games/servers'); exit; }
        $status = $this->request->get('status', '');
        $allowed = ['stopped', 'running', 'starting', 'suspended', 'installing'];
        if (!in_array($status, $allowed)) { $status = $s->status; }
        $this->db->table('game_servers')->where('id', (int)$id)->update(['status' => $status]);
        $_SESSION['success_message'] = "Status set to '{$status}'.";
        $this->response->redirect('/admin/games/servers');
    }

    public function serversDelete($id)
    {
        $this->guard();
        $this->db->table('game_servers')->where('id', (int)$id)->delete();
        $_SESSION['success_message'] = 'Game server deleted.';
        $this->response->redirect('/admin/games/servers');
    }

    // ─── Settings ───

    public function settings()
    {
        $this->guard();
        $user = $this->auth->user();
        $settings = [];
        $rows = $this->db->table('game_settings')->get() ?: [];
        foreach ($rows as $r) $settings[$r->setting_key] = $r->setting_value;
        return $this->view('admin.gameservers.settings', [
            'user' => $user,
            'title' => 'Game Server Settings',
            'theme_settings' => $this->theme(),
            'settings' => $settings,
        ]);
    }

    public function settingsSave()
    {
        $this->guard();
        $keys = ['default_max_players', 'default_billing_cycle', 'currency_symbol', 'enable_slot_pricing', 'enable_packages', 'setup_fee_type', 'setup_fee_value'];
        foreach ($keys as $key) {
            $val = $this->request->post($key, '');
            $existing = $this->db->table('game_settings')->where('setting_key', $key)->first();
            if ($existing) {
                $this->db->table('game_settings')->where('setting_key', $key)->update(['setting_value' => $val]);
            } else {
                $this->db->table('game_settings')->insertGetId(['setting_key' => $key, 'setting_value' => $val]);
            }
        }
        $_SESSION['success_message'] = 'Settings saved.';
        $this->response->redirect('/admin/games/settings');
    }
}
