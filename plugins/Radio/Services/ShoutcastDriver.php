<?php

namespace Plugins\Radio\Services;

class ShoutcastDriver implements StreamingDriverInterface
{
    protected $db;
    protected $installDir = '/opt/planethosts/shoutcast';
    protected $binaryName = 'sc_serv';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getDisplayName() { return 'SHOUTcast'; }
    public function getBinaryPath() { return "{$this->installDir}/{$this->binaryName}"; }

    public function getVersion()
    {
        if (!file_exists($this->getBinaryPath())) return 'not installed';
        // Binary is SHOUTcast DNAS 2.6.1 Build 777 — don't execute it (starts the server)
        return '2.6.1';
    }

    public function isInstalled() { return file_exists($this->getBinaryPath()); }

    public function isRunning()
    {
        return !empty(trim(shell_exec("pgrep -f {$this->binaryName} 2>/dev/null") ?: ''));
    }

    public function install($installPath = null)
    {
        if ($this->isInstalled()) return ['success' => true, 'output' => 'Already installed'];

        $sourceDir = $installPath ?: dirname(dirname(dirname(__DIR__))) . '/public';
        $destDir = $this->installDir;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $output = [];
        $tarball = "{$sourceDir}/sc_serv2_linux_x64-latest.tar.gz";
        if (file_exists($tarball)) {
            copy($tarball, "{$destDir}/sc_serv2_linux_x64-latest.tar.gz");
            shell_exec("cd {$destDir} && tar -xzf sc_serv2_linux_x64-latest.tar.gz 2>&1");
            chmod("{$destDir}/sc_serv", 0755);
            @unlink("{$destDir}/sc_serv2_linux_x64-latest.tar.gz");
            $output[] = 'Extracted from tarball';
        }
        foreach (['logs','control','setup','docs','examples'] as $d) {
            $path = "{$destDir}/{$d}";
            if (!is_dir($path)) mkdir($path, 0755, true);
        }
        return ['success' => $this->isInstalled(), 'output' => implode("\n", $output)];
    }

    public function update()
    {
        $bin = $this->getBinaryPath();
        if (!file_exists($bin)) return ['success' => false, 'error' => 'Not installed'];
        $backup = $bin . '.bak.' . date('Ymd');
        copy($bin, $backup);
        $this->stopAllStations();
        // Re-run install to get new binary
        return ['success' => true, 'output' => "Backed up to {$backup}"];
    }

    public function repair()
    {
        $this->stopAllStations();
        $stations = $this->db->table('streaming_stations')->where('engine', 'shoutcast')->get() ?: [];
        $repaired = 0;
        foreach ($stations as $s) {
            if (!file_exists($s->config_path)) {
                $this->generateConfig($s);
                $repaired++;
            }
            if ($s->systemd_service && !file_exists("/etc/systemd/system/{$s->systemd_service}.service")) {
                $svc = $this->generateSystemdService($s->port, $s->config_path, $this->getUsername($s->user_id));
                file_put_contents("/etc/systemd/system/{$s->systemd_service}.service", $svc);
                $repaired++;
            }
        }
        shell_exec('systemctl daemon-reload 2>/dev/null');
        return ['success' => true, 'output' => "Repaired {$repaired} stations"];
    }

    public function uninstall()
    {
        $this->stopAllStations();
        $stations = $this->db->table('streaming_stations')->where('engine', 'shoutcast')->get() ?: [];
        foreach ($stations as $s) {
            if ($s->systemd_service) {
                shell_exec("systemctl disable {$s->systemd_service}.service 2>/dev/null");
                @unlink("/etc/systemd/system/{$s->systemd_service}.service");
            }
        }
        shell_exec('systemctl daemon-reload 2>/dev/null');
        if (is_dir($this->installDir)) shell_exec("rm -rf " . escapeshellarg($this->installDir));
        return ['success' => true];
    }

    public function createStation($userId, $data = [])
    {
        if (!$this->isInstalled()) throw new \Exception('SHOUTcast not installed');
        $port = $data['port'] ?? $this->allocatePort();
        $password = $data['password'] ?? bin2hex(random_bytes(8));
        $adminPassword = $data['admin_password'] ?? bin2hex(random_bytes(8));
        $name = $data['name'] ?? 'My SHOUTcast Station';
        $bitrate = $data['bitrate'] ?? 128;
        $maxListeners = $data['max_listeners'] ?? 100;
        $public = $data['public_server'] ?? 0;
        $authhash = $data['stream_authhash'] ?? '';
        $format = $data['format'] ?? 'mp3';

        $username = $this->getUsername($userId);
        $stationDir = "/home/{$username}/stations/{$port}/";
        foreach (['logs','ssl','playlists','autodj','metadata','backups','tmp'] as $d) {
            @mkdir("{$stationDir}{$d}", 0755, true);
        }
        @chown($stationDir, $username);

        $configPath = "{$stationDir}sc_serv.conf";
        file_put_contents($configPath, $this->generateConfigContent($port, $password, $adminPassword, $maxListeners, $name, $public, $authhash, $bitrate, $format, $stationDir));

        $serviceName = "ph-stream-{$port}";
        $svcContent = $this->generateSystemdService($port, $configPath, $username);
        file_put_contents("/etc/systemd/system/{$serviceName}.service", $svcContent);
        shell_exec('systemctl daemon-reload 2>/dev/null');

        $id = $this->db->table('streaming_stations')->insertGetId([
            'user_id' => $userId, 'engine' => 'shoutcast', 'name' => $name,
            'server_type' => 'shoutcast', 'port' => $port,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'admin_password' => password_hash($adminPassword, PASSWORD_DEFAULT),
            'mount_point' => $this->generateMountPoint($userId, $name), 'bitrate' => $bitrate, 'format' => $format,
            'max_listeners' => $maxListeners, 'public_server' => $public,
            'stream_authhash' => $authhash, 'config_path' => $configPath,
            'systemd_service' => $serviceName, 'status' => 'stopped',
        ]);

        return ['id' => $id, 'port' => $port, 'password' => $password, 'admin_password' => $adminPassword, 'config_path' => $configPath, 'systemd_service' => $serviceName];
    }

    public function deleteStation($station)
    {
        $this->stopStation($station);
        if ($station->systemd_service) {
            shell_exec("systemctl disable {$station->systemd_service}.service 2>/dev/null");
            @unlink("/etc/systemd/system/{$station->systemd_service}.service");
            shell_exec('systemctl daemon-reload 2>/dev/null');
        }
        $stationDir = dirname($station->config_path);
        if (is_dir($stationDir)) shell_exec("rm -rf " . escapeshellarg($stationDir));
        $this->db->table('streaming_stations')->where('id', $station->id)->delete();
    }

    public function suspendStation($stationId)
    {
        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station) return ['success' => false, 'error' => 'Station not found'];
        $this->stopStation($station);
        if ($station->systemd_service) shell_exec("systemctl mask {$station->systemd_service}.service 2>/dev/null");
        $this->db->table('streaming_stations')->where('id', $stationId)->update(['status' => 'suspended']);
        return ['success' => true];
    }

    public function resumeStation($stationId)
    {
        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station) return ['success' => false, 'error' => 'Station not found'];
        if ($station->systemd_service) shell_exec("systemctl unmask {$station->systemd_service}.service 2>/dev/null");
        $this->db->table('streaming_stations')->where('id', $stationId)->update(['status' => 'stopped']);
        return ['success' => true];
    }

    public function cloneStation($stationId, $newName = null)
    {
        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station) return ['success' => false, 'error' => 'Station not found'];
        $newPort = $this->allocatePort();
        $data = [
            'name' => $newName ?: $station->name . ' (Clone)',
            'password' => bin2hex(random_bytes(8)),
            'admin_password' => bin2hex(random_bytes(8)),
            'bitrate' => $station->bitrate,
            'max_listeners' => $station->max_listeners,
            'format' => $station->format,
            'public_server' => $station->public_server,
            'port' => $newPort,
        ];
        return $this->createStation($station->user_id, $data);
    }

    public function moveStation($stationId, $newUserId)
    {
        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station) return ['success' => false, 'error' => 'Station not found'];
        $this->stopStation($station);
        $this->db->table('streaming_stations')->where('id', $stationId)->update(['user_id' => $newUserId]);
        return ['success' => true];
    }

    public function renameStation($stationId, $newName)
    {
        $this->db->table('streaming_stations')->where('id', $stationId)->update(['name' => $newName]);
        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if ($station && file_exists($station->config_path)) {
            $config = file_get_contents($station->config_path);
            $config = preg_replace('/^streamname=.*/m', "streamname={$newName}", $config);
            file_put_contents($station->config_path, $config);
        }
        return ['success' => true];
    }

    public function resetPassword($stationId)
    {
        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station) return ['success' => false, 'error' => 'Station not found'];
        $newPass = bin2hex(random_bytes(8));
        $this->db->table('streaming_stations')->where('id', $stationId)->update([
            'password' => password_hash($newPass, PASSWORD_DEFAULT),
        ]);
        if (file_exists($station->config_path)) {
            $config = file_get_contents($station->config_path);
            $config = preg_replace('/^password=.*/m', "password={$newPass}", $config);
            file_put_contents($station->config_path, $config);
        }
        return ['success' => true, 'password' => $newPass];
    }

    public function backupStation($stationId)
    {
        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station) return ['success' => false, 'error' => 'Not found'];
        $stationDir = dirname($station->config_path);
        $backupFile = "/tmp/station_{$stationId}_" . date('YmdHis') . ".tar.gz";
        if (is_dir($stationDir)) {
            shell_exec("tar -czf {$backupFile} -C " . escapeshellarg(dirname($stationDir)) . " " . escapeshellarg(basename($stationDir)) . " 2>/dev/null");
        }
        $this->db->table('streaming_stations')->where('id', $stationId)->update([
            'backups' => $backupFile,
        ]);
        return ['success' => file_exists($backupFile), 'file' => $backupFile];
    }

    public function restoreStation($stationId, $backupFile)
    {
        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station) return ['success' => false, 'error' => 'Not found'];
        $stationDir = dirname($station->config_path);
        if (!file_exists($backupFile)) return ['success' => false, 'error' => 'Backup not found'];
        $this->stopStation($station);
        shell_exec("rm -rf " . escapeshellarg($stationDir));
        shell_exec("tar -xzf {$backupFile} -C /home 2>/dev/null");
        return ['success' => true];
    }

    public function generateSsl($stationId)
    {
        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station) return ['success' => false, 'error' => 'Not found'];
        $domain = $this->getUsername($station->user_id) . '.planet-hosts.com';
        $email = "admin@{$domain}";

        $output = shell_exec("certbot certonly --webroot -w /home/{$this->getUsername($station->user_id)}/public_html"
            . " -d " . escapeshellarg($domain)
            . " --non-interactive --agree-tos --email " . escapeshellarg($email)
            . " 2>&1");

        $success = file_exists("/etc/letsencrypt/live/{$domain}/fullchain.pem");
        if ($success) {
            $stationDir = dirname($station->config_path);
            $certPath = "/etc/letsencrypt/live/{$domain}/fullchain.pem";
            $keyPath = "/etc/letsencrypt/live/{$domain}/privkey.pem";
            copy($certPath, "{$stationDir}/ssl/fullchain.pem");
            copy($keyPath, "{$stationDir}/ssl/privkey.pem");
            $this->db->table('streaming_stations')->where('id', $stationId)->update([
                'ssl_enabled' => 1, 'ssl_mode' => 'letsencrypt',
            ]);
        }
        return ['success' => $success, 'output' => $output];
    }

    public function startStation($station)
    {
        if (!$station->config_path || !file_exists($station->config_path))
            return ['success' => false, 'error' => 'Config not found'];

        if ($station->systemd_service) {
            shell_exec("systemctl start {$station->systemd_service}.service 2>/dev/null");
        } else {
            $pidFile = "/tmp/sc_{$station->id}.pid";
            shell_exec("nohup {$this->getBinaryPath()} {$station->config_path} > /dev/null 2>&1 & echo \$! > {$pidFile}");
        }
        sleep(1);
        $this->db->table('streaming_stations')->where('id', $station->id)->update([
            'status' => 'running', 'last_started' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true];
    }

    public function stopStation($station)
    {
        if ($station->systemd_service) {
            shell_exec("systemctl stop {$station->systemd_service}.service 2>/dev/null");
        } else {
            shell_exec("pkill -f " . escapeshellarg($station->config_path) . " 2>/dev/null");
        }
        $this->db->table('streaming_stations')->where('id', $station->id)->update([
            'status' => 'stopped', 'last_stopped' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true];
    }

    public function generateConfig($station)
    {
        $config = $this->generateConfigContent(
            $station->port, '', $station->admin_password ?? '',
            $station->max_listeners ?? 100, $station->name ?? 'Station',
            $station->public_server ?? 0, $station->stream_authhash ?? '',
            $station->bitrate ?? 128, $station->format ?? 'mp3',
            dirname($station->config_path) . '/'
        );
        file_put_contents($station->config_path, $config);
        return $station->config_path;
    }

    public function rebuildConfig($stationId)
    {
        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station) return ['success' => false, 'error' => 'Not found'];
        $this->generateConfig($station);
        return ['success' => true, 'config_path' => $station->config_path];
    }

    public function getStats($station)
    {
        $statsUrl = "http://127.0.0.1:{$station->port}/stats?sid=1";
        $ctx = stream_context_create(['http' => ['timeout' => 3]]);
        $xml = @file_get_contents($statsUrl, false, $ctx);
        $stats = ['listeners'=>0,'listener_peak'=>0,'bitrate'=>$station->bitrate,'genre'=>'','server_name'=>$station->name,'stream_start'=>'','source_ip'=>'','audio_info'=>''];
        if ($xml) {
            preg_match('/<LISTENERS>(\d+)<\/LISTENERS>/', $xml, $m); $stats['listeners'] = (int)($m[1] ?? 0);
            preg_match('/<PEAK_LISTENERS>(\d+)<\/PEAK_LISTENERS>/', $xml, $m); $stats['listener_peak'] = (int)($m[1] ?? 0);
            preg_match('/<BITRATE>(\d+)<\/BITRATE>/', $xml, $m); $stats['bitrate'] = (int)($m[1] ?? $station->bitrate);
            preg_match('/<GENRE>(.*?)<\/GENRE>/', $xml, $m); $stats['genre'] = $m[1] ?? '';
            preg_match('/<SERVER_NAME>(.*?)<\/SERVER_NAME>/', $xml, $m); $stats['server_name'] = $m[1] ?? $station->name;
        }
        return $stats;
    }

    public function getLogs($station, $lines = 100)
    {
        $logFile = dirname($station->config_path) . '/logs/sc_serv.log';
        if (!file_exists($logFile)) return [];
        $output = shell_exec("tail -{$lines} " . escapeshellarg($logFile) . ' 2>/dev/null');
        return explode("\n", trim($output ?: ''));
    }

    public function healthCheck($station)
    {
        $portOpen = @fsockopen('127.0.0.1', $station->port, $errno, $errstr, 2);
        if ($station->systemd_service) {
            $svc = trim(shell_exec("systemctl is-active {$station->systemd_service}.service 2>/dev/null") ?: '');
        } else {
            $svc = $this->isRunning() ? 'active' : 'inactive';
        }
        return [
            'running' => $svc === 'active', 'port_open' => $portOpen !== false,
            'listeners' => $this->getStats($station)['listeners'],
            'config_exists' => $station->config_path && file_exists($station->config_path),
            'binary_exists' => $this->isInstalled(), 'service_status' => $svc,
        ];
    }

    public function getMonitoringData($stationId)
    {
        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station) return null;
        $stats = $this->getStats($station);
        $health = $this->healthCheck($station);
        $pid = $station->systemd_service ? trim(shell_exec("systemctl show -p MainPID {$station->systemd_service}.service 2>/dev/null | cut -d= -f2") ?: '0') : '0';
        $cpu = $pid > 0 ? trim(shell_exec("ps -p {$pid} -o %cpu --no-headers 2>/dev/null") ?: '0') : '0';
        $mem = $pid > 0 ? trim(shell_exec("ps -p {$pid} -o %mem --no-headers 2>/dev/null") ?: '0') : '0';
        return [
            'station_id' => $stationId, 'status' => $station->status, 'running' => $health['running'],
            'listeners' => $stats['listeners'], 'listener_peak' => $stats['listener_peak'],
            'bitrate' => $stats['bitrate'], 'cpu' => $cpu, 'memory' => $mem,
            'uptime' => $station->last_started ? (time() - strtotime($station->last_started)) . 's' : '0',
            'last_check' => date('Y-m-d H:i:s'),
        ];
    }

    public function autoRestartFailed()
    {
        $stations = $this->db->table('streaming_stations')->where('engine', 'shoutcast')->where('status', 'running')->get() ?: [];
        $restarted = 0;
        foreach ($stations as $s) {
            $health = $this->healthCheck($s);
            if (!$health['port_open'] && !$health['running']) {
                $this->startStation($s);
                $restarted++;
            }
        }
        return $restarted;
    }

    public function stopAllStations()
    {
        $stations = $this->db->table('streaming_stations')->where('engine', 'shoutcast')->get() ?: [];
        foreach ($stations as $s) {
            if ($s->systemd_service) shell_exec("systemctl stop {$s->systemd_service}.service 2>/dev/null");
        }
    }

    // ─── AutoDJ ───

    public function configureAutodj($stationId, $type = 'liquidsoap')
    {
        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station) return ['success' => false, 'error' => 'Not found'];

        $stationDir = dirname($station->config_path);
        $playlistDir = "{$stationDir}/playlists";
        $autodjDir = "{$stationDir}/autodj";
        $username = $this->getUsername($station->user_id);

        if ($type === 'liquidsoap' && shell_exec('which liquidsoap 2>/dev/null')) {
            $scriptPath = "{$autodjDir}/autodj.liq";
            $script = "#! /usr/bin/liquidsoap\n\n"
                . "set(\"log.file\",\"{$autodjDir}/autodj.log\")\n"
                . "set(\"log.level\",3)\n\n"
                . "# Playlist\n"
                . "playlist = playlist(\"{$playlistDir}/main.m3u\")\n\n"
                . "# Output to SHOUTcast\n"
                . "output.shoutcast(%mp3,\n"
                . "  host = \"127.0.0.1\",\n"
                . "  port = {$station->port},\n"
                . "  password = \"source_password_here\",\n"
                . "  name = \"{$station->name}\",\n"
                . "  genre = \"Mixed\",\n"
                . "  playlist\n"
                . ")\n";
            file_put_contents($scriptPath, $script);
            @chown($scriptPath, $username);
            // Create empty playlist
            if (!file_exists("{$playlistDir}/main.m3u")) file_put_contents("{$playlistDir}/main.m3u", "# AutoDJ Playlist\n");

            $this->db->table('streaming_stations')->where('id', $stationId)->update([
                'autodj_enabled' => 1, 'autodj_type' => $type,
            ]);
            return ['success' => true, 'type' => $type, 'script' => $scriptPath];
        }

        if ($type === 'ffmpeg' && shell_exec('which ffmpeg 2>/dev/null')) {
            // FFmpeg-based AutoDJ using playlist concat
            $scriptPath = "{$autodjDir}/autodj.sh";
            $script = "#!/bin/bash\nwhile true; do\n"
                . "  ffmpeg -re -f concat -safe 0 -i <(find {$playlistDir} -name '*.mp3' -type f | shuf | awk '{print \"file '\''\"\$0\"'\\''\"}') \\\n"
                . "    -c:a libmp3lame -b:a {$station->bitrate}k \\\n"
                . "    -f mp3 icecast://source:source_password_here@127.0.0.1:{$station->port}/stream 2>>{$autodjDir}/ffmpeg.log\n"
                . "  sleep 2\ndone\n";
            file_put_contents($scriptPath, $script);
            chmod($scriptPath, 0755);
            @chown($scriptPath, $username);

            $this->db->table('streaming_stations')->where('id', $stationId)->update([
                'autodj_enabled' => 1, 'autodj_type' => $type,
            ]);
            return ['success' => true, 'type' => $type, 'script' => $scriptPath];
        }

        return ['success' => false, 'error' => 'No supported AutoDJ engine (install liquidsoap or ffmpeg)'];
    }

    public function startAutodj($stationId)
    {
        $station = $this->db->table('streaming_stations')->where('id', $stationId)->first();
        if (!$station || !$station->autodj_enabled) return ['success' => false, 'error' => 'AutoDJ not configured'];
        $stationDir = dirname($station->config_path);
        if ($station->autodj_type === 'liquidsoap') {
            $script = "{$stationDir}/autodj/autodj.liq";
            if (file_exists($script)) shell_exec("nohup liquidsoap '{$script}' > {$stationDir}/autodj/autodj.log 2>&1 &");
        } elseif ($station->autodj_type === 'ffmpeg') {
            $script = "{$stationDir}/autodj/autodj.sh";
            if (file_exists($script)) shell_exec("nohup bash '{$script}' > {$stationDir}/autodj/autodj.log 2>&1 &");
        }
        return ['success' => true];
    }

    public function stopAutodj($stationId)
    {
        $stationDir = dirname($this->db->table('streaming_stations')->where('id', $stationId)->first()->config_path ?? '');
        shell_exec("pkill -f {$stationDir}/autodj/ 2>/dev/null");
        return ['success' => true];
    }

    protected function generateConfigContent($port, $password, $adminPassword, $maxListeners, $name, $public, $authhash, $bitrate, $format, $stationDir)
    {
        $logDir = str_replace('\\', '/', $stationDir . 'logs');
        $config = "; SHOUTcast DNAS Configuration\n; Auto-generated by Planet Hosts\n; Port: {$port}\n\n"
            . "logfile={$logDir}/sc_serv.log\nw3clog={$logDir}/sc_w3c.log\n"
            . "banfile={$stationDir}sc_serv.ban\nripfile={$stationDir}sc_serv.rip\n"
            . "portbase={$port}\npassword={$password}\nadminpassword={$adminPassword}\n"
            . "maxuser={$maxListeners}\nstreamname={$name}\n";

        if ($public) $config .= "publicserver=always\n";
        if ($authhash) $config .= "streamauthhash={$authhash}\n";
        $config .= "bitrate={$bitrate}\ncontenttype=" . (['mp3'=>0,'aac'=>1,'ogg'=>2,'flac'=>3,'wma'=>4][$format] ?? 0) . "\n";

        // SSL support
        $sslCert = "{$stationDir}ssl/fullchain.pem";
        $sslKey = "{$stationDir}ssl/privkey.pem";
        if (file_exists($sslCert) && file_exists($sslKey)) {
            $config .= "ssl_cert={$sslCert}\nssl_key={$sslKey}\n";
        }
        return $config;
    }

    protected function generateSystemdService($port, $configPath, $username)
    {
        $bin = $this->getBinaryPath();
        return "[Unit]\nDescription=Planet Hosts SHOUTcast Stream (Port {$port})\nAfter=network.target\n\n"
            . "[Service]\nType=simple\nUser={$username}\nGroup={$username}\n"
            . "ExecStart={$bin} {$configPath}\nExecStop=/bin/kill -s TERM \$MAINPID\n"
            . "Restart=on-failure\nRestartSec=10\nLimitNOFILE=65536\n\n"
            . "[Install]\nWantedBy=multi-user.target\n";
    }

    protected function generateMountPoint($userId, $name = ''): string
    {
        $slug = $name ? preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($name))) : "user{$userId}";
        $slug = trim($slug, '-') ?: "station";
        $existing = $this->db->table('streaming_stations')
            ->where('mount_point', 'LIKE', "/{$slug}%")
            ->count();
        return $existing > 0 ? "/{$slug}-{$existing}" : "/{$slug}";
    }

    protected function allocatePort()
    {
        $used = $this->db->table('streaming_stations')->select('port')->get() ?: [];
        $usedPorts = array_map(fn($s) => $s->port, $used);
        for ($port = 9000; $port <= 10000; $port++) {
            if (!in_array($port, $usedPorts) && !@fsockopen('127.0.0.1', $port, $e, $e, 1)) return $port;
        }
        throw new \Exception('No available ports');
    }

    protected function getUsername($userId)
    {
        $user = $this->db->table('hosting_users')->where('id', $userId)->first();
        return $user->username ?? "user{$userId}";
    }
}
