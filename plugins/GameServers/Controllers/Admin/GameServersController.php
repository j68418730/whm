<?php
namespace Plugins\GameServers\Controllers\Admin;

use Core\Controller;

class GameServersController extends Controller
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
        $servers = $this->db->table('game_servers')->orderBy('created_at', 'DESC')->get() ?: [];
        $gameTypes = $this->db->table('game_types')->where('is_active', 1)->orderBy('name', 'ASC')->get() ?: [];
        $hostingUsers = $this->db->table('hosting_users')->orderBy('username', 'ASC')->get() ?: [];
        return $this->view('Plugins.GameServers.Views.admin.index', [
            'user' => $user, 'servers' => $servers, 'gameTypes' => $gameTypes,
            'hostingUsers' => $hostingUsers, 'title' => 'Game Servers'
        ]);
    }

    public function show($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $server = $this->db->table('game_servers')->where('id', (int)$id)->first();
        if (!$server) { $this->response->redirect('/admin/games'); exit; }
        $owner = $this->db->table('hosting_users')->where('id', $server->user_id)->first();
        $gameType = $this->db->table('game_types')->where('name', $server->game_type)->first();
        $hostingUsers = $this->db->table('hosting_users')->orderBy('username', 'ASC')->get() ?: [];
        $configContent = '';
        $configPath = $server->config_path ?: $server->install_path . '/server.cfg';
        if (file_exists($configPath)) $configContent = file_get_contents($configPath);
        $consoleLog = '';
        $logFile = $server->install_path . '/console.log';
        if (file_exists($logFile)) $consoleLog = file_get_contents($logFile);
        return $this->view('Plugins.GameServers.Views.admin.show', [
            'user' => $user, 'server' => $server, 'owner' => $owner, 'gameType' => $gameType,
            'hostingUsers' => $hostingUsers, 'configContent' => $configContent,
            'consoleLog' => $consoleLog, 'title' => '🎮 ' . $server->server_name
        ]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $name = trim($this->request->post('name', ''));
        $gameTypeName = $this->request->post('game_type', '');
        $userId = (int)$this->request->post('user_id', 0);
        $port = (int)$this->request->post('port', 0);
        $maxPlayers = (int)$this->request->post('max_players', 16);
        $appId = $this->request->post('app_id', '');

        if (!$name || !$userId) {
            $_SESSION['error_message'] = 'Server name and user are required.';
            $this->response->redirect('/admin/games'); exit;
        }

        // Auto-assign port if not specified
        if ($port <= 0) {
            require_once BASE_PATH . '/core/PortManager.php';
            $pm = new \Core\PortManager();
            $alloc = $pm->allocate('game_server');
            $port = $alloc ?: 27015;
        }

        $installDir = '/home/gameservers/' . preg_replace('/[^a-z0-9]/', '', strtolower($name)) . '_' . time();

        $this->db->table('game_servers')->insertGetId([
            'user_id' => $userId,
            'game_type' => $gameTypeName ?: 'Custom',
            'server_name' => $name,
            'port' => $port,
            'max_players' => $maxPlayers,
            'status' => 'stopped',
            'install_path' => $installDir,
            'is_demo' => 0,
        ]);

        @mkdir($installDir, 0755, true);
        if ($appId) {
            $_SESSION['success_message'] = "Server '{$name}' created. Installing via SteamCMD (App {$appId}) on port {$port}.";
            exec("cd {$installDir} && nohup steamcmd +login planet_hosts_dev Skylinehosting171 +force_install_dir {$installDir} +app_update {$appId} validate +quit > {$installDir}/install.log 2>&1 &");
        } else {
            $_SESSION['success_message'] = "Server '{$name}' created on port {$port} with no Steam App ID.";
        }
        $this->response->redirect('/admin/games');
    }

    public function assign($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $newUserId = (int)$this->request->post('user_id', 0);
        if ($newUserId) {
            $this->db->table('game_servers')->where('id', (int)$id)->update(['user_id' => $newUserId]);
            $_SESSION['success_message'] = 'Server reassigned.';
        }
        $this->response->redirect('/admin/games/show/' . (int)$id);
    }

    public function suspend($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('game_servers')->where('id', (int)$id)->update(['status' => 'suspended']);
        $_SESSION['success_message'] = 'Server suspended.';
        $this->response->redirect('/admin/games');
    }

    public function unsuspend($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('game_servers')->where('id', (int)$id)->update(['status' => 'stopped']);
        $_SESSION['success_message'] = 'Server unsuspended.';
        $this->response->redirect('/admin/games');
    }

    public function start($id) { return $this->doAction($id, 'start'); }
    public function stop($id) { return $this->doAction($id, 'stop'); }
    public function restart($id) {
        $mg = new \Plugins\GameServers\Services\GameServerManager();
        $mg->stop((int)$id);
        sleep(1);
        $mg->start((int)$id);
        $this->response->redirect('/admin/games');
    }

    public function status($id) {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { echo '{}'; exit; }
        $m = new \Plugins\GameServers\Services\GameServerManager();
        $this->response->json($m->getStatus((int)$id));
        $this->response->send(); exit;
    }

    public function command($id) {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $s = $this->db->table('game_servers')->where('id', (int)$id)->first();
        if ($s && $_POST && isset($_POST['cmd'])) {
            exec("cd {$s->install_path} && " . escapeshellcmd($_POST['cmd']) . " >> {$s->install_path}/console.log 2>&1 &");
            $_SESSION['success_message'] = 'Command executed.';
        }
        $this->response->redirect('/admin/games/show/' . $id);
    }

    public function saveConfig($id) {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $s = $this->db->table('game_servers')->where('id', (int)$id)->first();
        if ($s && $_POST && isset($_POST['config_content'])) {
            $p = $s->config_path ?: $s->install_path . '/server.cfg';
            @mkdir(dirname($p), 0755, true);
            file_put_contents($p, $_POST['config_content']);
            if (!$s->config_path) {
                $this->db->table('game_servers')->where('id', $id)->update(['config_path' => $p]);
            }
            $_SESSION['success_message'] = 'Configuration saved.';
        }
        $this->response->redirect('/admin/games/show/' . $id);
    }

    public function uninstall($id) { return $this->doAction($id, 'uninstall'); }

    public function settings()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $steamUser = $this->db->table('automation_settings')->where('setting_key', 'steam_username')->first();
        $steamPass = $this->db->table('automation_settings')->where('setting_key', 'steam_password')->first();
        $installDir = $this->db->table('automation_settings')->where('setting_key', 'game_install_dir')->first();
        $defaultPort = $this->db->table('automation_settings')->where('setting_key', 'game_default_port')->first();
        return $this->view('Plugins.GameServers.Views.admin.settings', [
            'user' => $user, 'title' => 'Game Server Settings',
            'steam_username' => $steamUser->setting_value ?? 'planet_hosts_dev',
            'steam_password' => $steamPass->setting_value ?? '',
            'game_install_dir' => $installDir->setting_value ?? '/home/gameservers',
            'game_default_port' => $defaultPort->setting_value ?? '27015',
        ]);
    }

    public function settingsSave()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $keys = ['steam_username', 'steam_password', 'game_install_dir', 'game_default_port'];
        foreach ($keys as $key) {
            $val = $this->request->post($key, '');
            $existing = $this->db->table('automation_settings')->where('setting_key', $key)->first();
            if ($existing) {
                $this->db->table('automation_settings')->where('setting_key', $key)->update(['setting_value' => $val]);
            } else {
                $this->db->table('automation_settings')->insertGetId(['setting_key' => $key, 'setting_value' => $val]);
            }
        }
        $_SESSION['success_message'] = 'Game server settings saved.';
        $this->response->redirect('/admin/games/settings');
    }

    protected function doAction($id, $m) {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $mg = new \Plugins\GameServers\Services\GameServerManager();
        $mg->$m((int)$id);
        $this->response->redirect('/admin/games');
    }
}
