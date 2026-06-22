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
        $servers = $this->db->table('game_servers')->where('user_id', $hosting->id)->get() ?: [];
        $games = \GameServers\Services\GameServerManager::$games;
        return $this->view('Plugins.GameServers.Views.user.index', [
            'user' => $this->auth->user(), 'hosting' => $hosting, 'servers' => $servers, 'games' => $games, 'title' => 'Game Servers'
        ]);
    }

    public function install($type)
    {
        $hosting = $this->loadUser();
        $manager = new \GameServers\Services\GameServerManager();
        $result = $manager->install($hosting->id, $type, ucfirst($type) . ' Server');
        if (isset($result['error'])) {
            $_SESSION['error_message'] = $result['error'];
        } else {
            $_SESSION['success_message'] = 'Installing game server...';
        }
        $this->response->redirect('/user/games');
    }

    public function start($id)
    {
        $this->loadUser();
        $manager = new \GameServers\Services\GameServerManager();
        $manager->start((int)$id);
        $this->response->redirect('/user/games');
    }

    public function stop($id)
    {
        $this->loadUser();
        $manager = new \GameServers\Services\GameServerManager();
        $manager->stop((int)$id);
        $this->response->redirect('/user/games');
    }

    public function status($id)
    {
        $this->loadUser();
        $manager = new \GameServers\Services\GameServerManager();
        $this->response->json($manager->getStatus((int)$id));
        $this->response->send();
        exit;
    }

    public function show($id)
    {
        $hosting = $this->loadUser();
        $server = $this->db->table('game_servers')->where('id', (int)$id)->where('user_id', $hosting->id)->first();
        if (!$server) { $this->response->redirect('/user/games'); exit; }
        $games = \GameServers\Services\GameServerManager::$games;
        return $this->view('Plugins.GameServers.Views.user.show', [
            'user' => $this->auth->user(), 'hosting' => $hosting, 'server' => $server, 'games' => $games, 'title' => $server->server_name
        ]);
    }

    public function command($id)
    {
        $hosting = $this->loadUser();
        $server = $this->db->table('game_servers')->where('id', (int)$id)->where('user_id', $hosting->id)->first();
        if ($server && $_POST && isset($_POST['cmd'])) {
            exec("cd {$server->install_path} && " . escapeshellcmd($_POST['cmd']) . " >> {$server->install_path}/console.log 2>&1 &");
            $_SESSION['success_message'] = 'Command executed.';
        }
        $this->response->redirect('/user/games');
    }

    public function saveConfig($id)
    {
        $hosting = $this->loadUser();
        $server = $this->db->table('game_servers')->where('id', (int)$id)->where('user_id', $hosting->id)->first();
        if ($server && $_POST && isset($_POST['config_content'])) {
            $path = $server->config_path ?: $server->install_path . '/server.cfg';
            file_put_contents($path, $_POST['config_content']);
            $this->db->table('game_servers')->where('id', $id)->update(['config_path' => $path]);
            $_SESSION['success_message'] = 'Config saved.';
        }
        $this->response->redirect('/user/games');
    }

    public function uninstall($id)
    {
        $this->loadUser();
        $manager = new \GameServers\Services\GameServerManager();
        $manager->uninstall((int)$id);
        $_SESSION['success_message'] = 'Game server uninstalled.';
        $this->response->redirect('/user/games');
    }
}
