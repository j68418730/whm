<?php
namespace Plugins\GameServers\Controllers\User;

use Core\Controller;

class GameServersController extends Controller
{
    protected $auth;
    protected $db;
    protected $response;
    protected $request;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->db = $app->get('db');
        $this->response = $app->get('response');
        $this->request = $app->get('request');
    }

    protected function loadUser()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $user = $this->auth->user();
        $hostingUser = $this->db->table('hosting_users')->where('email', $user->email)->first();
        if (!$hostingUser) $hostingUser = $this->db->table('hosting_users')->where('username', $user->name)->first();
        if (!$hostingUser) { $this->response->redirect('/user'); exit; }
        return $hostingUser;
    }

    public function index()
    {
        $hosting = $this->loadUser();
        $servers = $this->db->table('game_servers')->where('user_id', $hosting->id)->orderBy('created_at', 'DESC')->get() ?: [];
        $gameTypes = $this->db->table('game_types')->where('is_active', 1)->orderBy('name', 'ASC')->get() ?: [];
        return $this->view('Plugins.GameServers.Views.user.index', [
            'user' => $this->auth->user(), 'hosting' => $hosting,
            'servers' => $servers, 'gameTypes' => $gameTypes, 'title' => 'Game Servers'
        ]);
    }

    public function show($id)
    {
        $hosting = $this->loadUser();
        $server = $this->db->table('game_servers')->where('id', (int)$id)->where('user_id', $hosting->id)->first();
        if (!$server) { $this->response->redirect('/user/games'); exit; }
        $gameType = $this->db->table('game_types')->where('name', $server->game_type)->first();
        // Read config file
        $configContent = '';
        $configPath = $server->config_path ?: $server->install_path . '/server.cfg';
        if (file_exists($configPath)) $configContent = file_get_contents($configPath);
        // Read console log
        $consoleLog = '';
        $logFile = $server->install_path . '/console.log';
        if (file_exists($logFile)) $consoleLog = file_get_contents($logFile);
        return $this->view('Plugins.GameServers.Views.user.show', [
            'user' => $this->auth->user(), 'hosting' => $hosting, 'server' => $server,
            'gameType' => $gameType, 'configContent' => $configContent, 'consoleLog' => $consoleLog,
            'title' => $server->server_name
        ]);
    }

    public function start($id)
    {
        $this->loadUser();
        require_once BASE_PATH . '/plugins/GameServers/Services/GameServerManager.php';
        $manager = new \GameServers\Services\GameServerManager();
        $manager->start((int)$id);
        $_SESSION['success_message'] = 'Server starting...';
        $this->response->redirect('/user/games/show/' . (int)$id);
    }

    public function stop($id)
    {
        $this->loadUser();
        require_once BASE_PATH . '/plugins/GameServers/Services/GameServerManager.php';
        $manager = new \GameServers\Services\GameServerManager();
        $manager->stop((int)$id);
        $_SESSION['success_message'] = 'Server stopped.';
        $this->response->redirect('/user/games/show/' . (int)$id);
    }

    public function restart($id)
    {
        $this->loadUser();
        require_once BASE_PATH . '/plugins/GameServers/Services/GameServerManager.php';
        $manager = new \GameServers\Services\GameServerManager();
        $manager->stop((int)$id);
        sleep(2);
        $manager->start((int)$id);
        $_SESSION['success_message'] = 'Server restarting...';
        $this->response->redirect('/user/games/show/' . (int)$id);
    }

    public function status($id)
    {
        $this->loadUser();
        require_once BASE_PATH . '/plugins/GameServers/Services/GameServerManager.php';
        $manager = new \GameServers\Services\GameServerManager();
        $this->response->json($manager->getStatus((int)$id));
        $this->response->send();
        exit;
    }

    public function command($id)
    {
        $hosting = $this->loadUser();
        $server = $this->db->table('game_servers')->where('id', (int)$id)->where('user_id', $hosting->id)->first();
        if ($server && $_POST && isset($_POST['cmd'])) {
            $safeCmd = escapeshellcmd($_POST['cmd']);
            exec("cd {$server->install_path} && {$safeCmd} >> {$server->install_path}/console.log 2>&1 &");
            $_SESSION['success_message'] = 'Command sent.';
        }
        $this->response->redirect('/user/games/show/' . (int)$id);
    }

    public function saveConfig($id)
    {
        $hosting = $this->loadUser();
        $server = $this->db->table('game_servers')->where('id', (int)$id)->where('user_id', $hosting->id)->first();
        if ($server && $_POST && isset($_POST['config_content'])) {
            $path = $server->config_path ?: $server->install_path . '/server.cfg';
            file_put_contents($path, $_POST['config_content']);
            if (!$server->config_path) {
                $this->db->table('game_servers')->where('id', $id)->update(['config_path' => $path]);
            }
            $_SESSION['success_message'] = 'Configuration saved.';
        }
        $this->response->redirect('/user/games/show/' . (int)$id);
    }

    public function uninstall($id)
    {
        $this->loadUser();
        require_once BASE_PATH . '/plugins/GameServers/Services/GameServerManager.php';
        $manager = new \GameServers\Services\GameServerManager();
        $manager->uninstall((int)$id);
        $_SESSION['success_message'] = 'Game server uninstalled.';
        $this->response->redirect('/user/games');
    }
}
