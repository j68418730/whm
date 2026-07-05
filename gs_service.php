<?php
namespace Plugins\GameServers\Services;

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
        $template = null;
        if ($appId > 0) {
            require_once dirname(dirname(__DIR__)) . '/../../services/GameTemplateEngine.php';
            $engine = new \Services\GameTemplateEngine();
            $template = $engine->getTemplate((string)$appId);
        }
        $this->pdo->prepare("INSERT INTO game_servers (user_id, game_type, server_name, port, max_players, status, install_path, is_demo) VALUES (?, ?, ?, ?, ?, 'installing', ?, ?)")
            ->execute([$userId, $slug, $serverName, $port, $maxPlayers, $installDir, $appId === 0 ? 1 : 0]);
        $serverId = $this->pdo->lastInsertId();
        $steamUser = 'planet_hosts_dev';
        $steamPass = 'Skylinehosting171';
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
            'server_name' => $serverName, 'port' => $port, 'max_players' => $maxPlayers,
            'install_path' => $installDir, 'map_name' => 'de_dust2', 'password' => '',
            'rcon_password' => bin2hex(random_bytes(8)), 'rcon_port' => $template->rcon_port ?? 27020,
            'query_port' => $template->query_port ?? $port, 'motd' => 'Welcome to ' . $serverName, 'ip' => '0.0.0.0',
        ];
        file_put_contents("{$installDir}/start.sh", $engine->generateStartScript($template, $serverData)); @chmod("{$installDir}/start.sh", 0755);
        file_put_contents("{$installDir}/stop.sh", $engine->generateStopScript($template, $serverData)); @chmod("{$installDir}/stop.sh", 0755);
        file_put_contents("{$installDir}/restart.sh", $engine->generateRestartScript($template, $serverData)); @chmod("{$installDir}/restart.sh", 0755);
        $configContent = $engine->generateConfig($template, $serverData);
        $configPath = "{$installDir}/server.cfg";
        file_put_contents($configPath, $configContent);
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
        $gameType = $server->game_type ?? '';
        if (!$gameType) return;
        $stmt = $this->pdo->prepare("SELECT * FROM game_templates WHERE (LOWER(name) LIKE ? OR appid = ?) AND status = 'active' LIMIT 1");
        $like = '%' . strtolower($gameType) . '%';
        $stmt->execute([$like, $server->game_type]);
        $template = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$template) return;
        require_once dirname(dirname(__DIR__)) . '/../../services/GameTemplateEngine.php';
        $engine = new \Services\GameTemplateEngine();
        $sd = [
            'server_name' => $server->server_name, 'port' => $server->port, 'max_players' => $server->max_players,
            'install_path' => $server->install_path, 'map_name' => $server->map_name ?: 'de_dust2', 'password' => '',
            'rcon_password' => $server->rcon_password ?: bin2hex(random_bytes(8)),
            'rcon_port' => $server->rcon_port ?: ($template->rcon_port ?? 27020),
            'query_port' => $server->query_port ?: ($template->query_port ?? $server->port),
            'motd' => 'Welcome to ' . $server->server_name, 'ip' => '0.0.0.0',
        ];
        file_put_contents($server->install_path . '/start.sh', $engine->generateStartScript($template, $sd)); @chmod($server->install_path . '/start.sh', 0755);
        file_put_contents($server->install_path . '/stop.sh', $engine->generateStopScript($template, $sd)); @chmod($server->install_path . '/stop.sh', 0755);
        file_put_contents($server->install_path . '/restart.sh', $engine->generateRestartScript($template, $sd)); @chmod($server->install_path . '/restart.sh', 0755);
        $cfg = $engine->generateConfig($template, $sd);
        $configPath = $server->install_path . '/server.cfg';
        file_put_contents($configPath, $cfg);
        $this->pdo->prepare("UPDATE game_servers SET config_path = ?, rcon_password = ? WHERE id = ?")->execute([$configPath, $sd['rcon_password'], $server->id]);
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

    // ── Backup System ──
    public function createBackup($serverId)
    {
        $s = $this->pdo->prepare("SELECT * FROM game_servers WHERE id = ?");
        $s->execute([$serverId]);
        $server = $s->fetch(\PDO::FETCH_OBJ);
        if (!$server) return ['success' => false, 'message' => 'Server not found.'];
        $installDir = $server->install_path;
        if (!is_dir($installDir)) return ['success' => false, 'message' => 'Install directory not found.'];
        $backupDir = $installDir . '/backups';
        @mkdir($backupDir, 0755, true);
        $dateStr = date('Y-m-d_H-i-s');
        $name = $server->server_name . '_backup_' . $dateStr;
        $archivePath = $backupDir . '/' . $name . '.tar.gz';
        $this->pdo->prepare("INSERT INTO game_server_backups (server_id, name, file_path, status, type) VALUES (?, ?, ?, 'running', 'manual')")->execute([$serverId, $name, $archivePath]);
        $backupId = $this->pdo->lastInsertId();
        exec("tar czf " . escapeshellarg($archivePath) . " -C " . escapeshellarg(dirname($installDir)) . " " . escapeshellarg(basename($installDir)) . " 2>/dev/null", $out, $code);
        if ($code === 0 && file_exists($archivePath)) {
            $size = filesize($archivePath);
            $this->pdo->prepare("UPDATE game_server_backups SET status='completed', file_size=?, completed_at=NOW() WHERE id=?")->execute([$size, $backupId]);
            $count = $this->pdo->prepare("SELECT COUNT(*) as c FROM game_server_backups WHERE server_id=? AND status='completed'");
            $count->execute([$serverId]); $c = $count->fetch(\PDO::FETCH_OBJ)->c;
            $this->pdo->prepare("UPDATE game_servers SET total_backups=?, total_backup_size=? WHERE id=?")->execute([$c, $size, $serverId]);
            return ['success' => true, 'message' => "Backup created: {$name} (" . number_format($size / 1048576, 1) . " MB)"];
        }
        $this->pdo->prepare("UPDATE game_server_backups SET status='failed' WHERE id=?")->execute([$backupId]);
        return ['success' => false, 'message' => 'Backup failed.'];
    }

    public function restoreBackup($serverId, $backupId)
    {
        $b = $this->pdo->prepare("SELECT * FROM game_server_backups WHERE id=? AND server_id=?");
        $b->execute([$backupId, $serverId]);
        $backup = $b->fetch(\PDO::FETCH_OBJ);
        if (!$backup || !$backup->file_path || !file_exists($backup->file_path)) return ['success' => false, 'message' => 'Backup not found.'];
        $s = $this->pdo->prepare("SELECT * FROM game_servers WHERE id=?");
        $s->execute([$serverId]);
        $server = $s->fetch(\PDO::FETCH_OBJ);
        if (!$server) return ['success' => false, 'message' => 'Server not found.'];
        $this->stop($serverId);
        $installDir = $server->install_path;
        exec("rm -rf " . escapeshellarg($installDir) . "/* " . escapeshellarg($installDir) . "/.* 2>/dev/null");
        exec("tar xzf " . escapeshellarg($backup->file_path) . " -C " . escapeshellarg(dirname($installDir)) . " 2>/dev/null", $out, $code);
        if ($code === 0) return ['success' => true, 'message' => 'Backup restored.'];
        return ['success' => false, 'message' => 'Restore failed.'];
    }

    // ── Firewall ──
    public function applyFirewall($serverId)
    {
        $rules = $this->pdo->prepare("SELECT * FROM game_server_firewall_rules WHERE server_id=? AND is_active=1");
        $rules->execute([$serverId]);
        $s = $this->pdo->prepare("SELECT install_path, port FROM game_servers WHERE id=?");
        $s->execute([$serverId]);
        $server = $s->fetch(\PDO::FETCH_OBJ);
        if (!$server) return;
        $chain = "GAME_SRV_{$serverId}";
        exec("iptables -N {$chain} 2>/dev/null; iptables -F {$chain} 2>/dev/null");
        // Default: allow the game port
        exec("iptables -A {$chain} -p udp --dport {$server->port} -j ACCEPT 2>/dev/null");
        exec("iptables -A {$chain} -p tcp --dport {$server->port} -j ACCEPT 2>/dev/null");
        foreach ($rules as $r) {
            $target = $r->rule_type === 'allow' ? 'ACCEPT' : 'DROP';
            $ports = $r->port_start ? ($r->port_end && $r->port_end > $r->port_start ? "--dport {$r->port_start}:{$r->port_end}" : "--dport {$r->port_start}") : '';
            $proto = $r->protocol === 'both' ? '' : "-p {$r->protocol}";
            $src = $r->source_ip && $r->source_ip !== '0.0.0.0/0' ? "-s {$r->source_ip}" : '';
            $cmd = "iptables -A {$chain} {$proto} {$src} {$ports} -j {$target} 2>/dev/null";
            exec($cmd);
        }
        // Link to INPUT
        exec("iptables -C INPUT -j {$chain} 2>/dev/null || iptables -A INPUT -j {$chain} 2>/dev/null");
    }

    // ── Workshop Sync ──
    public function syncWorkshopItems($serverId)
    {
        $s = $this->pdo->prepare("SELECT * FROM game_servers WHERE id=?");
        $s->execute([$serverId]);
        $server = $s->fetch(\PDO::FETCH_OBJ);
        if (!$server) return ['success' => false, 'message' => 'Server not found.'];
        $items = $this->pdo->prepare("SELECT * FROM game_server_workshop_items WHERE server_id=? AND installed=0");
        $items->execute([$serverId]);
        $all = $items->fetchAll(\PDO::FETCH_OBJ);
        if (!$all) return ['success' => true, 'message' => 'No pending workshop items.'];
        $installDir = $server->install_path;
        $steamCmdPath = '/usr/games/steamcmd';
        $steamUser = 'planet_hosts_dev';
        $steamPass = 'Skylinehosting171';
        $count = 0;
        foreach ($all as $item) {
            $workshopDir = "{$installDir}/steamapps/workshop";
            @mkdir($workshopDir, 0755, true);
            $cmd = "{$steamCmdPath} +force_install_dir {$installDir} +login {$steamUser} {$steamPass} +workshop_download_item {$server->game_type} {$item->workshop_id} +quit > {$installDir}/workshop_{$item->workshop_id}.log 2>&1";
            exec("nohup {$cmd} &");
            $this->pdo->prepare("UPDATE game_server_workshop_items SET installed=1, install_path=? WHERE id=?")->execute([$workshopDir, $item->id]);
            $count++;
        }
        return ['success' => true, 'message' => "Syncing {$count} workshop items."];
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
