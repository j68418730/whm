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
        $servers = $this->db->table('game_servers')->get() ?: [];
        return $this->view('Plugins.GameServers.Views.admin.index', ['user' => $user, 'servers' => $servers, 'title' => 'Game Servers']);
    }

    public function show($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $server = $this->db->table('game_servers')->where('id', (int)$id)->first();
        if (!$server) { $this->response->redirect('/admin/games'); exit; }
        return $this->view('Plugins.GameServers.Views.admin.show', ['user' => $user, 'server' => $server, 'title' => '🎮 ' . $server->server_name]);
    }

    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $name = trim($this->request->post('name', ''));
        $appId = (int)$this->request->post('app_id', 0);
        $port = (int)$this->request->post('port', 27015);
        $max = (int)$this->request->post('max_players', 16);
        if (!$name) { $_SESSION['error_message'] = 'Name required.'; $this->response->redirect('/admin/games'); exit; }
        $hosting = $this->db->table('hosting_users')->where('email', $this->auth->user()->email)->first();
        $manager = new \Plugins\GameServers\Services\GameServerManager();
        $result = $manager->install($hosting ? $hosting->id : 1, $name, $appId, $port, $max);
        if (isset($result['error'])) $_SESSION['error_message'] = $result['error'];
        else $_SESSION['success_message'] = "Installing '{$name}'...";
        $this->response->redirect('/admin/games');
    }

    public function start($id) { return $this->doAction($id, 'start'); }
    public function stop($id) { return $this->doAction($id, 'stop'); }
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
            $_SESSION['success_message'] = 'Executed.';
        }
        $this->response->redirect('/admin/games/show/' . $id);
    }
    public function saveConfig($id) {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $s = $this->db->table('game_servers')->where('id', (int)$id)->first();
        if ($s && $_POST && isset($_POST['config_content'])) {
            $p = $s->config_path ?: $s->install_path . '/server.cfg';
            file_put_contents($p, $_POST['config_content']);
            $this->db->table('game_servers')->where('id', $id)->update(['config_path' => $p]);
            $_SESSION['success_message'] = 'Config saved.';
        }
        $this->response->redirect('/admin/games/show/' . $id);
    }
    public function uninstall($id) { return $this->doAction($id, 'uninstall'); }
    protected function doAction($id, $m) {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $mg = new \Plugins\GameServers\Services\GameServerManager();
        $mg->$m((int)$id);
        $this->response->redirect('/admin/games');
    }
}
