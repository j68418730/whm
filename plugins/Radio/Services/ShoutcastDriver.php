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
        $bin = $this->getBinaryPath();
        if (!file_exists($bin)) return 'not installed';
        $output = shell_exec("{$bin} 2>&1") ?: '';
        preg_match('/(\d+\.\d+\.\d+)/', $output, $m);
        return $m[1] ?? '2.6.1';
    }

    public function isInstalled()
    {
        return file_exists($this->getBinaryPath());
    }

    public function isRunning()
    {
        $output = trim(shell_exec("pgrep -f {$this->binaryName} 2>/dev/null") ?: '');
        return !empty($output);
    }

    public function install($installPath = null)
    {
        if ($this->isInstalled()) {
            return ['success' => true, 'output' => 'SHOUTcast already installed'];
        }

        $sourceDir = $installPath ?: 'K:/planethostsonic/install';
        $destDir = $this->installDir;

        if (!is_dir($destDir)) {
            @mkdir($destDir, 0755, true);
        }

        // Expected source files
        $binary = "{$sourceDir}/sc_serv";
        $configExamples = "{$sourceDir}/examples";
        $docs = "{$sourceDir}/docs";
        $cacert = "{$sourceDir}/cacert.pem";

        $output = [];

        if (file_exists($binary)) {
            copy($binary, "{$destDir}/sc_serv");
            chmod("{$destDir}/sc_serv", 0755);
            $output[] = 'Binary copied: sc_serv';
        } else {
            // Try tarball
            $tarball = "{$sourceDir}/sc_serv2_linux_x64-latest.tar.gz";
            if (file_exists($tarball)) {
                shell_exec("tar -xzf " . escapeshellarg($tarball) . " -C " . escapeshellarg($destDir) . " 2>&1");
                $output[] = 'Extracted from tarball';
            }
        }

        if (file_exists($cacert)) {
            copy($cacert, "{$destDir}/cacert.pem");
            $output[] = 'CA cert copied';
        }

        if (is_dir($configExamples)) {
            $this->copyDir($configExamples, "{$destDir}/examples");
            $output[] = 'Examples copied';
        }
        if (is_dir($docs)) {
            $this->copyDir($docs, "{$destDir}/docs");
            $output[] = 'Docs copied';
        }

        // Create directories
        foreach (['logs', 'control', 'setup'] as $d) {
            $path = "{$destDir}/{$d}";
            if (!is_dir($path)) @mkdir($path, 0755, true);
        }

        $installed = $this->isInstalled();
        $output[] = $installed ? 'SHOUTcast installed successfully' : 'Binary not found at source';

        return ['success' => $installed, 'output' => implode("\n", $output)];
    }

    public function uninstall()
    {
        $this->stopAllStations();
        if (is_dir($this->installDir)) {
            shell_exec("rm -rf " . escapeshellarg($this->installDir));
        }
    }

    public function createStation($userId, $data = [])
    {
        if (!$this->isInstalled()) {
            throw new \Exception('SHOUTcast is not installed. Install it first.');
        }

        $port = $data['port'] ?? $this->allocatePort();
        $password = $data['password'] ?? bin2hex(random_bytes(8));
        $adminPassword = $data['admin_password'] ?? bin2hex(random_bytes(8));
        $name = $data['name'] ?? 'My SHOUTcast Station';
        $bitrate = $data['bitrate'] ?? 128;
        $maxListeners = $data['max_listeners'] ?? 100;
        $public = $data['public_server'] ?? 0;
        $authhash = $data['stream_authhash'] ?? '';
        $format = $data['format'] ?? 'mp3';

        $user = $this->db->table('hosting_users')->where('id', $userId)->first();
        $username = $user->username ?? "user{$userId}";

        // Create station directory
        $stationDir = "/home/{$username}/stations/{$port}/";
        $dirs = ['logs', 'ssl', 'playlists', 'autodj', 'metadata', 'backups', 'tmp'];
        foreach ($dirs as $d) {
            @mkdir("{$stationDir}{$d}", 0755, true);
        }

        // Generate sc_serv.conf
        $configPath = "{$stationDir}sc_serv.conf";
        $config = $this->generateConfigContent($port, $password, $adminPassword, $maxListeners, $name, $public, $authhash, $bitrate, $format, $stationDir);
        file_put_contents($configPath, $config);

        // Create systemd service
        $serviceName = "ph-stream-{$port}";
        $servicePath = "/etc/systemd/system/{$serviceName}.service";
        $serviceContent = $this->generateSystemdService($port, $configPath, $username);
        file_put_contents($servicePath, $serviceContent);
        shell_exec('systemctl daemon-reload 2>/dev/null');

        $id = $this->db->table('streaming_stations')->insertGetId([
            'user_id' => $userId,
            'engine' => 'shoutcast',
            'name' => $name,
            'server_type' => 'shoutcast',
            'port' => $port,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'admin_password' => password_hash($adminPassword, PASSWORD_DEFAULT),
            'mount_point' => '/stream',
            'bitrate' => $bitrate,
            'format' => $format,
            'max_listeners' => $maxListeners,
            'public_server' => $public,
            'stream_authhash' => $authhash,
            'config_path' => $configPath,
            'systemd_service' => $serviceName,
            'status' => 'stopped',
        ]);

        return [
            'id' => $id,
            'port' => $port,
            'password' => $password,
            'admin_password' => $adminPassword,
            'config_path' => $configPath,
            'systemd_service' => $serviceName,
        ];
    }

    public function deleteStation($station)
    {
        $this->stopStation($station);
        if ($station->systemd_service) {
            shell_exec("systemctl disable {$station->systemd_service}.service 2>/dev/null");
            @unlink("/etc/systemd/system/{$station->systemd_service}.service");
            shell_exec('systemctl daemon-reload 2>/dev/null');
        }
        if ($station->config_path) {
            $stationDir = dirname($station->config_path);
            if (is_dir($stationDir)) {
                shell_exec("rm -rf " . escapeshellarg($stationDir));
            }
        }
        $this->db->table('streaming_stations')->where('id', $station->id)->delete();
        $this->releasePort($station->port);
    }

    public function startStation($station)
    {
        if (!$station->config_path || !file_exists($station->config_path)) {
            return ['success' => false, 'error' => 'Config file not found'];
        }

        if ($station->systemd_service) {
            shell_exec("systemctl start {$station->systemd_service}.service 2>/dev/null");
        } else {
            $pidFile = "/tmp/sc_{$station->id}.pid";
            $bin = $this->getBinaryPath();
            $cmd = "nohup {$bin} {$station->config_path} > /dev/null 2>&1 & echo \$! > {$pidFile}";
            shell_exec($cmd);
        }

        sleep(1);

        $this->db->table('streaming_stations')->where('id', $station->id)->update([
            'status' => 'running',
            'last_started' => date('Y-m-d H:i:s'),
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
            'status' => 'stopped',
            'last_stopped' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true];
    }

    public function generateConfig($station)
    {
        $config = $this->generateConfigContent(
            $station->port,
            '', $station->admin_password ?? '',
            $station->max_listeners ?? 100,
            $station->name ?? 'Station',
            $station->public_server ?? 0,
            $station->stream_authhash ?? '',
            $station->bitrate ?? 128,
            $station->format ?? 'mp3',
            dirname($station->config_path) . '/'
        );
        file_put_contents($station->config_path, $config);
        return $station->config_path;
    }

    protected function generateConfigContent($port, $password, $adminPassword, $maxListeners, $name, $public, $authhash, $bitrate, $format, $stationDir)
    {
        $logDir = str_replace('\\', '/', $stationDir . 'logs');
        $controlDir = str_replace('\\', '/', $stationDir);

        $config = "; SHOUTcast DNAS Configuration\n"
            . "; Auto-generated by Planet Hosts Streaming Engine\n"
            . "; Port: {$port}\n\n"
            . "logfile={$logDir}/sc_serv.log\n"
            . "w3clog={$logDir}/sc_w3c.log\n"
            . "banfile={$controlDir}/sc_serv.ban\n"
            . "ripfile={$controlDir}/sc_serv.rip\n"
            . "portbase={$port}\n"
            . "password={$password}\n"
            . "adminpassword={$adminPassword}\n"
            . "maxuser={$maxListeners}\n"
            . "streamname={$name}\n";

        if ($public) {
            $config .= "publicserver=always\n";
        }

        if ($authhash) {
            $config .= "streamauthhash={$authhash}\n";
        }

        $config .= "bitrate={$bitrate}\n";

        // Format mapping
        $formatMap = ['mp3' => 0, 'aac' => 1, 'ogg' => 2, 'flac' => 3, 'wma' => 4];
        $config .= "contenttype=" . ($formatMap[$format] ?? 0) . "\n";

        return $config;
    }

    protected function generateSystemdService($port, $configPath, $username)
    {
        $bin = $this->getBinaryPath();
        return "[Unit]\n"
            . "Description=Planet Hosts SHOUTcast Stream (Port {$port})\n"
            . "After=network.target\n\n"
            . "[Service]\n"
            . "Type=simple\n"
            . "User={$username}\n"
            . "Group={$username}\n"
            . "ExecStart={$bin} {$configPath}\n"
            . "ExecStop=/bin/kill -s TERM \$MAINPID\n"
            . "Restart=on-failure\n"
            . "RestartSec=10\n"
            . "LimitNOFILE=65536\n\n"
            . "[Install]\n"
            . "WantedBy=multi-user.target\n";
    }

    public function getStats($station)
    {
        $statsUrl = "http://127.0.0.1:{$station->port}/stats?sid=1";
        $ctx = stream_context_create(['http' => ['timeout' => 3]]);
        $xml = @file_get_contents($statsUrl, false, $ctx);

        $stats = [
            'listeners' => 0,
            'listener_peak' => 0,
            'bitrate' => $station->bitrate,
            'genre' => '',
            'server_name' => $station->name,
            'stream_start' => '',
            'source_ip' => '',
            'audio_info' => '',
        ];

        if ($xml) {
            preg_match('/<LISTENERS>(\d+)<\/LISTENERS>/', $xml, $m);
            $stats['listeners'] = (int)($m[1] ?? 0);
            preg_match('/<PEAK_LISTENERS>(\d+)<\/PEAK_LISTENERS>/', $xml, $m);
            $stats['listener_peak'] = (int)($m[1] ?? 0);
            preg_match('/<BITRATE>(\d+)<\/BITRATE>/', $xml, $m);
            $stats['bitrate'] = (int)($m[1] ?? $station->bitrate);
            preg_match('/<GENRE>(.*?)<\/GENRE>/', $xml, $m);
            $stats['genre'] = $m[1] ?? '';
            preg_match('/<SERVER_NAME>(.*?)<\/SERVER_NAME>/', $xml, $m);
            $stats['server_name'] = $m[1] ?? $station->name;
        }

        return $stats;
    }

    public function getLogs($station, $lines = 100)
    {
        $stationDir = dirname($station->config_path);
        $logFile = "{$stationDir}/logs/sc_serv.log";
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
            'running' => $svc === 'active',
            'port_open' => $portOpen !== false,
            'listeners' => $this->getStats($station)['listeners'],
            'config_exists' => $station->config_path && file_exists($station->config_path),
            'binary_exists' => $this->isInstalled(),
            'service_status' => $svc,
        ];
    }

    public function stopAllStations()
    {
        $stations = $this->db->table('streaming_stations')->where('engine', 'shoutcast')->get() ?: [];
        foreach ($stations as $s) {
            $this->stopStation($s);
        }
    }

    protected function allocatePort()
    {
        $used = $this->db->table('streaming_stations')->select('port')->get() ?: [];
        $usedPorts = array_map(fn($s) => $s->port, $used);
        for ($port = 9000; $port <= 10000; $port++) {
            if (!in_array($port, $usedPorts) && !@fsockopen('127.0.0.1', $port, $e, $e, 1)) {
                return $port;
            }
        }
        throw new \Exception('No available ports for SHOUTcast');
    }

    protected function releasePort($port) {}

    protected function copyDir($src, $dst)
    {
        if (!is_dir($dst)) @mkdir($dst, 0755, true);
        $items = scandir($src);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $s = "{$src}/{$item}";
            $d = "{$dst}/{$item}";
            if (is_dir($s)) {
                $this->copyDir($s, $d);
            } else {
                copy($s, $d);
            }
        }
    }
}
