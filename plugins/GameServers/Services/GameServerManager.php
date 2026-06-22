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

        return ['success' => true, 'server_id' => $serverId];
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
            file_put_contents($startScript, "#!/bin/bash\ncd {$s->install_path}\necho 'Server running on port {$s->port}'");
            chmod($startScript, 0755);
        }

        exec("nohup bash {$startScript} > {$s->install_path}/server.log 2>&1 & echo $!", $out);
        $pid = (int)($out[0] ?? 0);
        if ($pid > 0) {
            $this->pdo->prepare("UPDATE game_servers SET status='running', pid=? WHERE id=?")->execute([$pid, $serverId]);
            return true;
        }
        return false;
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
