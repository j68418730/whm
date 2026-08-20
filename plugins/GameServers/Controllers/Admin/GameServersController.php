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
        $nodes = $this->db->table('game_nodes')->orderBy('name', 'ASC')->get() ?: [];
        return $this->view('Plugins.GameServers.Views.admin.index', [
            'user' => $user, 'servers' => $servers, 'gameTypes' => $gameTypes,
            'hostingUsers' => $hostingUsers, 'nodes' => $nodes, 'title' => 'Game Servers'
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
            'consoleLog' => $consoleLog, 'title' => '🎮 ' . ($server->server_name ?: $server->name)
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

        // Optional remote destination node
        $nodeId = (int)$this->request->post('node_id', 0);
        $slug = preg_replace('/[^a-z0-9]/', '', strtolower($name));
        if ($nodeId > 0) {
            $node = $this->db->table('game_nodes')->where('id', $nodeId)->first();
            if (!$node || $node->type === 'local') { $nodeId = 0; }
        }

        // Auto-assign port if not specified
        if ($port <= 0) {
            require_once BASE_PATH . '/core/PortManager.php';
            $pm = new \Core\PortManager();
            $alloc = $pm->allocate('game_server');
            $port = $alloc ?: 27015;
        }

        // Remote nodes use a slug-relative path (agent resolves its own base dir)
        $installDir = $nodeId > 0 ? $slug : '/home/gameservers/' . $slug . '_' . time();

        $this->db->table('game_servers')->insertGetId([
            'user_id' => $userId,
            'node_id' => $nodeId ?: null,
            'game_type' => $gameTypeName ?: 'Custom',
            'server_name' => $name,
            'port' => $port,
            'max_players' => $maxPlayers,
            'status' => 'stopped',
            'install_path' => $installDir,
            'is_demo' => 0,
        ]);
        $serverId = (int)$this->db->lastInsertId();

        if ($nodeId > 0) {
            $this->enqueueRemoteJob($serverId, 'install', [
                'slug' => $slug,
                'server_name' => $name,
                'appid' => $appId,
                'port' => $port,
                'max_players' => $maxPlayers,
                'install_path' => $slug,
            ]);
            $_SESSION['success_message'] = "Server '{$name}' queued for install on remote node.";
        } else {
            @mkdir($installDir, 0755, true);
            if ($appId) {
                $_SESSION['success_message'] = "Server '{$name}' created. Installing via SteamCMD (App {$appId}) on port {$port}.";
                exec("cd {$installDir} && nohup steamcmd +login planet_hosts_dev Skylinehosting171 +force_install_dir {$installDir} +app_update {$appId} validate +quit > {$installDir}/install.log 2>&1 &");
            } else {
                $_SESSION['success_message'] = "Server '{$name}' created on port {$port} with no Steam App ID.";
            }
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
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $server = $this->db->table('game_servers')->where('id', (int)$id)->first();
        if ($server && $server->node_id) {
            $this->enqueueRemoteJob((int)$id, 'restart', ['slug' => basename((string)$server->install_path), 'server_name' => $server->server_name]);
        } else {
            require_once BASE_PATH . '/plugins/GameServers/Services/GameServerManager.php';
            $mg = new \GameServers\Services\GameServerManager();
            $mg->stop((int)$id);
            sleep(1);
            $mg->start((int)$id);
        }
        $this->response->redirect('/admin/games');
    }

    public function status($id) {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { echo '{}'; exit; }
        require_once BASE_PATH . '/plugins/GameServers/Services/GameServerManager.php';
        $m = new \GameServers\Services\GameServerManager();
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
        $server = $this->db->table('game_servers')->where('id', (int)$id)->first();
        if ($server && $server->node_id) {
            // Remote node → queue the job for the agent (poll mode, no inbound ports)
            $cmd = $m === 'uninstall' ? 'delete' : $m;
            $this->enqueueRemoteJob((int)$id, $cmd, [
                'slug' => basename((string)$server->install_path),
                'server_name' => $server->server_name,
                'port' => $server->port,
                'max_players' => $server->max_players,
                'appid' => $server->appid ?? $server->type_id ?? 0,
            ]);
            $_SESSION['success_message'] = ucfirst($cmd) . ' queued on remote node.';
            $this->response->redirect('/admin/games');
            return;
        }
        require_once BASE_PATH . '/plugins/GameServers/Services/GameServerManager.php';
        $mg = new \GameServers\Services\GameServerManager();
        $mg->$m((int)$id);
        $this->response->redirect('/admin/games');
    }

    // ── Remote Nodes ──
    protected function enqueueRemoteJob($serverId, $command, $payload) {
        $server = $this->db->table('game_servers')->where('id', (int)$serverId)->first();
        if (!$server || !$server->node_id) return;
        $this->db->table('node_jobs')->insertGetId([
            'game_server_id' => (int)$serverId,
            'node_id' => (int)$server->node_id,
            'command' => $command,
            'payload' => json_encode($payload ?: (object)[]),
            'status' => 'pending',
        ]);
    }

    public function nodes()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $nodes = $this->db->table('game_nodes')->orderBy('id', 'ASC')->get() ?: [];
        return $this->view('Plugins.GameServers.Views.admin.nodes', [
            'user' => $user, 'title' => 'Game Nodes', 'nodes' => $nodes,
        ]);
    }

    public function nodeStore()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $name = trim($this->request->post('name', ''));
        $type = $this->request->post('type', 'remote') === 'local' ? 'local' : 'remote';
        $address = trim($this->request->post('address', ''));
        if ($name === '') {
            $_SESSION['error_message'] = 'Node name is required.';
            $this->response->redirect('/admin/games/nodes'); exit;
        }
        $token = bin2hex(random_bytes(32));
        $this->db->table('game_nodes')->insertGetId([
            'name' => $name, 'address' => $address, 'type' => $type, 'token' => $token, 'status' => 'offline',
        ]);
        $_SESSION['success_message'] = "Node '{$name}' added. Agent token: {$token}";
        $this->response->redirect('/admin/games/nodes');
    }

    public function nodeDelete($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->db->table('game_servers')->where('node_id', (int)$id)->update(['node_id' => null]);
        $this->db->table('node_jobs')->where('node_id', (int)$id)->update(['status' => 'failed', 'result' => 'Node removed']);
        $this->db->table('game_nodes')->where('id', (int)$id)->delete();
        $_SESSION['success_message'] = 'Node removed.';
        $this->response->redirect('/admin/games/nodes');
    }

    public function nodeTest($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $node = $this->db->table('game_nodes')->where('id', (int)$id)->first();
        if ($node && $node->last_seen) {
            $online = $node->status === 'online' && (time() - strtotime($node->last_seen)) < 120;
            $_SESSION['success_message'] = $online
                ? "Node online (last seen {$node->last_seen})."
                : "Node appears offline (last seen {$node->last_seen}).";
        } else {
            $_SESSION['success_message'] = 'No activity yet — start the agent on the remote machine.';
        }
        $this->response->redirect('/admin/games/nodes');
    }

    // ── Node token management (generate + delete only; tokens are stored, never re-used) ──
    public function nodeTokenGen($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $node = $this->db->table('game_nodes')->where('id', (int)$id)->first();
        if (!$node) { $_SESSION['error_message'] = 'Node not found.'; $this->response->redirect('/admin/games/nodes'); exit; }
        $token = bin2hex(random_bytes(32));
        $this->db->table('game_nodes')->where('id', (int)$id)->update(['token' => $token, 'status' => 'offline']);
        $_SESSION['success_message'] = "New token for '{$node->name}': {$token} — update the agent's agent-config.json (node_token) and restart it.";
        $this->response->redirect('/admin/games/nodes');
    }

    public function nodeTokenDel($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $node = $this->db->table('game_nodes')->where('id', (int)$id)->first();
        if (!$node) { $_SESSION['error_message'] = 'Node not found.'; $this->response->redirect('/admin/games/nodes'); exit; }
        $this->db->table('game_nodes')->where('id', (int)$id)->update(['token' => '', 'status' => 'offline']);
        $_SESSION['success_message'] = "Token deleted for '{$node->name}'. The agent can no longer connect.";
        $this->response->redirect('/admin/games/nodes');
    }

    /** Serve a prebuilt Windows agent zip (admin only). */
    public function agentZip()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $zipPath = BASE_PATH . '/plugins/GameServers/agent/ph-agent-windows.zip';
        if (!file_exists($zipPath)) {
            if (class_exists('ZipArchive')) {
                $zip = new \ZipArchive();
                $win = BASE_PATH . '/plugins/GameServers/agent/win';
                if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                    foreach (['ph-agent-installer.exe' => 'ph-agent-installer.exe', 'install-agent.ps1' => 'install-agent.ps1', 'agent-config.example.json' => 'agent-config.example.json', 'README-windows.md' => 'README-windows.md'] as $file => $entry) {
                        $src = $win . '/' . $file;
                        if (is_file($src)) { $zip->addFile($src, $entry); }
                    }
                    $zip->close();
                }
            }
        }
        if (file_exists($zipPath)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="ph-agent-windows.zip"');
            header('Content-Length: ' . filesize($zipPath));
            readfile($zipPath);
            exit;
        }
        $_SESSION['error_message'] = 'Agent package not available.';
        $this->response->redirect('/admin/games/nodes');
    }

    /** Serve a packaged cross-platform agent bundle for the given OS (admin only). */
    protected function agentPackage($os, $fileName)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $base = BASE_PATH . '/plugins/GameServers/agent';
        $filesByOs = [
            // shared core + per-OS scripts; installer wraps everything for the user
            'linux' => ['agent.js', 'install-linux.sh', 'agent-config.example.json', 'ph-agent.service', 'README.md', 'README-LINUX.md'],
            'macos' => ['agent.js', 'install-macos.sh', 'agent-config.example.json', 'README.md', 'README-MACOS.md'],
        ];
        $files = $filesByOs[$os] ?? [];
        if (empty($files)) { $_SESSION['error_message'] = 'Unknown agent platform.'; $this->response->redirect('/admin/games/nodes'); exit; }

        if (class_exists('ZipArchive')) {
            $tmp = sys_get_temp_dir() . '/ph-agent-' . $os . '-' . time() . '.zip';
            $zip = new \ZipArchive();
            if ($zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($files as $f) {
                    $src = $base . '/' . $f;
                    if (is_file($src)) { $zip->addFile($src, $f); }
                }
                // Make scripts executable via a top-level install hint
                if ($os === 'linux') {
                    $zip->addFromString('INSTALL-LINUX.txt', "Run:  sudo bash install-linux.sh\n(optionally pass PANEL_URL / NODE_TOKEN / BASE_DIR to install non-interactively)\n");
                } elseif ($os === 'macos') {
                    $zip->addFromString('INSTALL-MACOS.txt', "Run:  bash install-macos.sh\n(optionally pass PANEL_URL / NODE_TOKEN / BASE_DIR to install non-interactively)\n");
                }
                $zip->close();
            }
            if (is_file($tmp)) {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
                header('Content-Length: ' . filesize($tmp));
                readfile($tmp);
                @unlink($tmp);
                exit;
            }
        }
        $_SESSION['error_message'] = 'Agent package could not be built (zip support required).';
        $this->response->redirect('/admin/games/nodes');
    }

    public function agentLinux() { $this->agentPackage('linux', 'ph-agent-linux.zip'); }
    public function agentMacos() { $this->agentPackage('macos', 'ph-agent-macos.zip'); }

    /** Serve the Windows GUI installer exe directly (self-contained, embeds the agent). */
    public function agentInstaller()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $path = BASE_PATH . '/plugins/GameServers/agent/win/ph-agent-installer.exe';
        if (is_file($path)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="ph-agent-installer.exe"');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        }
        $_SESSION['error_message'] = 'Windows installer not built yet.';
        $this->response->redirect('/admin/games/nodes');
    }

    /** Serve the raw agent.js so install scripts can pull it directly. */
    public function agentSource()
    {
        $path = BASE_PATH . '/plugins/GameServers/agent/agent.js';
        if (is_file($path)) {
            header('Content-Type: text/javascript; charset=utf-8');
            header('Content-Disposition: inline; filename="agent.js"');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        }
        http_response_code(404);
        echo 'agent.js not found.';
        exit;
    }
}
