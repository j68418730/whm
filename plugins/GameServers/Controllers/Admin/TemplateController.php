<?php
namespace Plugins\GameServers\Controllers\Admin;

use Core\Controller;

class TemplateController extends Controller
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
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $category = $this->request->get('category', 'all');

        $engine = new \Services\GameTemplateEngine();
        if ($category && $category !== 'all') {
            $templates = $this->db->table('game_templates')->where('category', $category)->where('status', 'active')->orderBy('name', 'ASC')->get() ?: [];
        } else {
            $templates = $this->db->table('game_templates')->where('status', 'active')->orderBy('category', 'ASC')->orderBy('name', 'ASC')->get() ?: [];
        }
        $categories = $this->db->table('game_templates')->where('status', 'active')->orderBy('category', 'ASC')->get() ?: [];
        $catList = [];
        foreach ($categories as $t) $catList[$t->category] = true;
        $catList = array_keys($catList);

        $totalCount = count($templates);
        $stats = [];
        $allTemplates = $this->db->table('game_templates')->where('status', 'active')->get();
        $stats['all'] = $allTemplates ? count($allTemplates) : 0;
        foreach ($catList as $c) {
            $catTemplates = $this->db->table('game_templates')->where('category', $c)->where('status', 'active')->get();
            $stats[$c] = $catTemplates ? count($catTemplates) : 0;
        }

        return $this->view('Plugins.GameServers.Views.admin.templates.index', [
            'user' => $user, 'templates' => $templates, 'categories' => $catList,
            'currentCategory' => $category, 'title' => 'Game Templates', 'stats' => $stats,
        ]);
    }

    public function store()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $id = (int)$this->request->post('id', 0);
        $data = [
            'name' => $this->request->post('name', ''),
            'appid' => $this->request->post('appid', '0'),
            'engine' => $this->request->post('engine', 'Native'),
            'category' => $this->request->post('category', 'FPS'),
            'steamcmd_login' => $this->request->post('steamcmd_login', 'anonymous'),
            'steam_client' => (int)$this->request->post('steam_client', 0),
            'anonymous_login' => (int)$this->request->post('anonymous_login', 1),
            'requires_game_purchase' => (int)$this->request->post('requires_game_purchase', 0),
            'supports_linux' => (int)$this->request->post('supports_linux', 1),
            'supports_windows' => (int)$this->request->post('supports_windows', 0),
            'install_script' => $this->request->post('install_script', 'steamcmd +force_install_dir {INSTALL_DIR} +login {STEAMCMD_LOGIN} +app_update {APPID} validate +quit'),
            'start_command' => $this->request->post('start_command', "#!/bin/bash\ncd {INSTALL_DIR}\n./{SERVER_BINARY} -port {PORT} +maxplayers {MAX_PLAYERS} +map {MAP} +exec server.cfg"),
            'stop_command' => $this->request->post('stop_command', "#!/bin/bash\nkill $(cat {INSTALL_DIR}/server.pid 2>/dev/null) 2>/dev/null || pkill -f {SERVER_BINARY}"),
            'restart_command' => $this->request->post('restart_command', "#!/bin/bash\n{STOP_COMMAND}\nsleep 2\n{START_COMMAND}"),
            'query_port' => (int)$this->request->post('query_port', 27015),
            'game_port' => (int)$this->request->post('game_port', 27015),
            'rcon_port' => (int)$this->request->post('rcon_port', 27020),
            'default_slots' => (int)$this->request->post('default_slots', 16),
            'min_slots' => (int)$this->request->post('min_slots', 10),
            'max_slots' => (int)$this->request->post('max_slots', 64),
            'description' => $this->request->post('description', ''),
            'notes' => $this->request->post('notes', ''),
            'config_template' => $this->request->post('config_template', ''),
            'status' => $this->request->post('status', 'active'),
        ];

        if ($id) {
            $this->db->table('game_templates')->where('id', $id)->update($data);
            $_SESSION['success_message'] = 'Template updated.';
        } else {
            $this->db->table('game_templates')->insertGetId($data);
            $_SESSION['success_message'] = 'Template created.';
        }
        $this->response->redirect('/admin/games/templates');
    }

    public function delete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('game_templates')->where('id', (int)$id)->delete();
        $_SESSION['success_message'] = 'Template deleted.';
        $this->response->redirect('/admin/games/templates');
    }

    public function import()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $engine = new \Services\GameTemplateEngine();
        $imported = $engine->importFromGameTypes();

        // Also try to pull from game_types table directly
        $existingTypes = $this->db->table('game_types')->where('is_active', 1)->get() ?: [];
        $directImport = 0;
        foreach ($existingTypes as $gt) {
            $check = $this->db->table('game_templates')->where('name', $gt->name)->first();
            if ($check) continue;
            $appid = $gt->game_id ?? '0';
            $this->db->table('game_templates')->insertGetId([
                'name' => $gt->name,
                'appid' => $appid,
                'engine' => 'Native',
                'category' => 'FPS',
                'steamcmd_login' => $appid && $appid !== '0' ? 'anonymous' : '',
                'steam_client' => $gt->requires_steam ?? 0,
                'anonymous_login' => 1,
                'requires_game_purchase' => $appid && $appid !== '0' ? 0 : 0,
                'description' => $gt->description ?? '',
                'install_script' => $appid && $appid !== '0' ? 'steamcmd +force_install_dir {INSTALL_DIR} +login {STEAMCMD_LOGIN} +app_update {APPID} validate +quit' : "echo 'Manual install for {$gt->name}' > {INSTALL_DIR}/README.txt",
                'start_command' => "#!/bin/bash\ncd {INSTALL_DIR}\n./server -port {PORT} +maxplayers {MAX_PLAYERS} +map {MAP} +exec server.cfg",
                'stop_command' => "#!/bin/bash\nkill $(cat {INSTALL_DIR}/server.pid 2>/dev/null) 2>/dev/null || pkill -f server",
                'restart_command' => "#!/bin/bash\nkill $(cat {INSTALL_DIR}/server.pid 2>/dev/null) 2>/dev/null || pkill -f server\nsleep 2\ncd {INSTALL_DIR} && ./server -port {PORT} +maxplayers {MAX_PLAYERS} +map {MAP} +exec server.cfg",
                'config_template' => "hostname \"{SERVER_NAME}\"\nmaxplayers {MAX_PLAYERS}\nport {PORT}\nrcon_password \"{RCON_PASSWORD}\"\nrcon_port {RCON_PORT}\nmap {MAP}\nsv_password \"{PASSWORD}\"\n",
                'status' => 'active',
            ]);
            $directImport++;
        }

        $total = $imported + $directImport;
        $_SESSION['success_message'] = "Import complete. {$total} new game templates added.";
        $this->response->redirect('/admin/games/templates');
    }

    public function preview($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $template = $this->db->table('game_templates')->where('id', (int)$id)->first();
        if (!$template) { $this->response->redirect('/admin/games/templates'); exit; }

        $sampleServer = [
            'server_name' => 'My ' . $template->name . ' Server',
            'port' => 27015,
            'query_port' => $template->query_port,
            'rcon_port' => $template->rcon_port,
            'max_players' => $template->default_slots,
            'install_path' => '/home/gameserver/myserver',
            'map_name' => 'de_dust2',
            'password' => 'changeme',
            'rcon_password' => 'rcon_changeme',
            'motd' => 'Welcome to My Server!',
            'ip' => '0.0.0.0',
        ];

        $engine = new \Services\GameTemplateEngine();
        $sampleServer['server_binary'] = $engine->getBinaryName($template);

        $user = $this->auth->user();
        return $this->view('Plugins.GameServers.Views.admin.templates.show', [
            'user' => $user, 'template' => $template, 'title' => 'Template: ' . $template->name,
            'installScript' => $engine->generateInstallScript($template, '/home/gameserver/myserver'),
            'startScript' => $engine->generateStartScript($template, $sampleServer),
            'stopScript' => $engine->generateStopScript($template, $sampleServer),
            'restartScript' => $engine->generateRestartScript($template, $sampleServer),
            'configContent' => $engine->generateConfig($template, $sampleServer),
            'sampleServer' => $sampleServer,
        ]);
    }
}
