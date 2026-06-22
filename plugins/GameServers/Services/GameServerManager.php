<?php
namespace GameServers\Services;

class GameServerManager
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    }

    public function install($userId, $serverName, $appId = 0, $port = 27015, $maxPlayers = 16)
    {
        $user = $this->pdo->prepare("SELECT username FROM hosting_users WHERE id = ?");
        $user->execute([$userId]);
        $user = $user->fetch(\PDO::FETCH_OBJ);
        if (!$user) return ['error' => 'User not found'];

        $username = $user->username;
        $slug = preg_replace('/[^a-z0-9]/', '', strtolower($serverName));
        $installDir = "/home/{$username}/gameservers/{$slug}";
        $port = $this->findAvailablePort($port);

        $diskFree = disk_free_space('/');
        if ($diskFree < 512 * 1024 * 1024) return ['error' => 'Not enough disk space.'];

        $this->installSteamCMD();
        @mkdir($installDir, 0755, true);

        // Load template if AppID matches
        $template = null;
        if ($appId > 0) {
            require_once dirname(dirname(__DIR__)) . '/../../services/GameTemplateEngine.php';
            $engine = new \Services\GameTemplateEngine();
            $template = $engine->getTemplate((string)$appId);
        }

        $this->pdo->prepare("INSERT INTO game_servers (user_id, game_type, server_name, port, max_players, status, install_path, is_demo) VALUES (?, ?, ?, ?, ?, 'installing', ?, ?)")
            ->execute([$userId, $slug, $serverName, $port, $maxPlayers, $installDir, $appId === 0 ? 1 : 0]);
        $serverId = $this->pdo->lastInsertId();

        $steamUser = env('STEAM_USERNAME', 'planet_hosts_dev');
        $steamPass = env('STEAM_PASS', 'Skylinehosting171');
        $script = "#!/bin/bash\ncd {$installDir}\nexport HOME=/home/{$username}\n";
        if ($appId > 0) {
            $script .= "steamcmd +login {$steamUser} {$steamPass} +app_update {$appId} +quit\n";
        } else {
            $script .= "echo 'Demo server installed on port {$port}' > {$installDir}/readme.txt\n";
        }
        file_put_contents("{$installDir}/install.sh", $script);
        chmod("{$installDir}/install.sh", 0755);
        file_put_contents("{$installDir}/install.log", "Starting installation...\n");
        exec("nohup bash {$installDir}/install.sh > {$installDir}/install.log 2>&1 &");

        // Generate server.cfg and start/stop/restart scripts from template
        if ($template) {
            $this->generateServerScripts($template, $serverId, $serverName, $installDir, $port, $maxPlayers);
        }

        return ['success' => true, 'server_id' => $serverId];
    }

    protected function generateServerScripts($template, $serverId, $serverName, $installDir, $port, $maxPlayers)
    {
        require_once dirname(dirname(__DIR__)) . '/../../services/GameTemplateEngine.php';
        $engine = new \Services\GameTemplateEngine();

        $serverData = [
            'server_name' => $serverName,
            'port' => $port,
            'max_players' => $maxPlayers,
            'install_path' => $installDir,
            'map_name' => 'de_dust2',
            'password' => '',
            'rcon_password' => bin2hex(random_bytes(8)),
            'rcon_port' => $template->rcon_port ?? 27020,
            'query_port' => $template->query_port ?? $port,
            'motd' => 'Welcome to ' . $serverName,
            'ip' => '0.0.0.0',
        ];

        // Generate start.sh
        $startContent = $engine->generateStartScript($template, $serverData);
        file_put_contents("{$installDir}/start.sh", $startContent);
        @chmod("{$installDir}/start.sh", 0755);

        // Generate stop.sh
        $stopContent = $engine->generateStopScript($template, $serverData);
        file_put_contents("{$installDir}/stop.sh", $stopContent);
        @chmod("{$installDir}/stop.sh", 0755);

        // Generate restart.sh
        $restartContent = $engine->generateRestartScript($template, $serverData);
        file_put_contents("{$installDir}/restart.sh", $restartContent);
        @chmod("{$installDir}/restart.sh", 0755);

        // Generate server.cfg
        $configContent = $engine->generateConfig($template, $serverData);
        $configPath = "{$installDir}/server.cfg";
        file_put_contents($configPath, $configContent);

        // Store config path and RCON password in database
        $this->pdo->prepare("UPDATE game_servers SET config_path = ?, rcon_password = ?, rcon_port = ?, query_port = ? WHERE id = ?")
            ->execute([$configPath, $serverData['rcon_password'], $serverData['rcon_port'], $serverData['query_port'], $serverId]);
    }

    public function getStatus($serverId)
    {
        $server = $this->pdo->prepare("SELECT * FROM game_servers WHERE id = ?");
        $server->execute([$serverId]);
        $s = $server->fetch(\PDO::FETCH_OBJ);
        if (!$s) return null;

        $running = $s->pid ? file_exists("/proc/{$s->pid}") : false;
        if (!$running && $s->pid) {
            $this->pdo->prepare("UPDATE game_servers SET status='stopped', pid=NULL WHERE id=?")->execute([$serverId]);
        }

        $log = '';
        $logFile = $s->install_path . '/console.log';
        if (file_exists($logFile)) $log = shell_exec("tail -30 " . escapeshellarg($logFile));

        return ['running' => $running, 'pid' => $s->pid, 'players' => 0, 'map' => '', 'log' => $log];
    }

    public function start($serverId)
    {
        $server = $this->pdo->prepare("SELECT * FROM game_servers WHERE id = ?");
        $server->execute([$serverId]);
        $s = $server->fetch(\PDO::FETCH_OBJ);
        if (!$s) return false;

        $startScript = "{$s->install_path}/start.sh";
        if (!file_exists($startScript)) {
            // Try to generate from template
            $this->tryGenerateFromTemplate($s);
            if (!file_exists($startScript)) {
                file_put_contents($startScript, "#!/bin/bash\ncd {$s->install_path}\necho 'Server running on port {$s->port}'");
                chmod($startScript, 0755);
            }
        }

        exec("nohup bash {$startScript} > {$s->install_path}/server.log 2>&1 & echo $!", $out);
        $pid = (int)($out[0] ?? 0);
        if ($pid > 0) {
            $this->pdo->prepare("UPDATE game_servers SET status='running', pid=? WHERE id=?")->execute([$pid, $serverId]);
            return true;
        }
        return false;
    }

    protected function tryGenerateFromTemplate($server)
    {
        // Try to find a template matching this server's game type
        $gameType = $server->game_type ?? '';
        if (!$gameType) return;

        $stmt = $this->pdo->prepare("SELECT * FROM game_templates WHERE (LOWER(name) LIKE ? OR appid = ?) AND status = 'active' LIMIT 1");
        $like = '%' . strtolower($gameType) . '%';
        $stmt->execute([$like, $server->game_type]);
        $template = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$template) return;

        require_once dirname(dirname(__DIR__)) . '/../../services/GameTemplateEngine.php';
        $engine = new \Services\GameTemplateEngine();
        $serverData = [
            'server_name' => $server->server_name,
            'port' => $server->port,
            'max_players' => $server->max_players,
            'install_path' => $server->install_path,
            'map_name' => $server->map_name ?: 'de_dust2',
            'password' => '',
            'rcon_password' => $server->rcon_password ?: bin2hex(random_bytes(8)),
            'rcon_port' => $server->rcon_port ?: ($template->rcon_port ?? 27020),
            'query_port' => $server->query_port ?: ($template->query_port ?? $server->port),
            'motd' => 'Welcome to ' . $server->server_name,
            'ip' => '0.0.0.0',
        ];

        $start = $engine->generateStartScript($template, $serverData);
        file_put_contents($server->install_path . '/start.sh', $start);
        @chmod($server->install_path . '/start.sh', 0755);

        $stop = $engine->generateStopScript($template, $serverData);
        file_put_contents($server->install_path . '/stop.sh', $stop);
        @chmod($server->install_path . '/stop.sh', 0755);

        $restart = $engine->generateRestartScript($template, $serverData);
        file_put_contents($server->install_path . '/restart.sh', $restart);
        @chmod($server->install_path . '/restart.sh', 0755);

        $cfg = $engine->generateConfig($template, $serverData);
        $configPath = $server->install_path . '/server.cfg';
        file_put_contents($configPath, $cfg);
        $this->pdo->prepare("UPDATE game_servers SET config_path = ?, rcon_password = ? WHERE id = ?")
            ->execute([$configPath, $serverData['rcon_password'], $server->id]);
    }

    public function stop($serverId)
    {
        $server = $this->pdo->prepare("SELECT * FROM game_servers WHERE id = ?");
        $server->execute([$serverId]);
        $s = $server->fetch(\PDO::FETCH_OBJ);
        if (!$s || !$s->pid) return false;
        exec("kill {$s->pid} 2>/dev/null; sleep 1; kill -9 {$s->pid} 2>/dev/null");
        $this->pdo->prepare("UPDATE game_servers SET status='stopped', pid=NULL WHERE id=?")->execute([$serverId]);
        return true;
    }

    public function uninstall($serverId)
    {
        $server = $this->pdo->prepare("SELECT * FROM game_servers WHERE id = ?");
        $server->execute([$serverId]);
        $s = $server->fetch(\PDO::FETCH_OBJ);
        if (!$s) return false;
        $this->stop($serverId);
        exec("rm -rf {$s->install_path} 2>/dev/null");
        $this->pdo->prepare("DELETE FROM game_servers WHERE id=?")->execute([$serverId]);
        return true;
    }

    protected function installSteamCMD()
    {
        if (file_exists('/usr/games/steamcmd')) return;
        exec("dpkg --add-architecture i386 2>/dev/null; apt-get update -qq 2>/dev/null; apt-get install -y -qq steamcmd 2>/dev/null", $out, $code);
        if ($code !== 0) {
            exec("cd /usr/games && curl -sqL https://steamcdn-a.akamaihd.net/client/installer/steamcmd_linux.tar.gz | tar zxf - 2>/dev/null");
            exec("ln -sf /usr/games/steamcmd.sh /usr/games/steamcmd 2>/dev/null");
        }
    }

    protected function findAvailablePort($basePort)
    {
        require_once dirname(dirname(dirname(__DIR__))) . '/core/PortManager.php';
        $pm = new \Core\PortManager();
        return $pm->allocate('game_server', null, null, $basePort) ?: ($basePort + 500);
    }
}
