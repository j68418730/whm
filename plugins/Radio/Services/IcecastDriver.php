<?php

namespace Plugins\Radio\Services;

class IcecastDriver implements StreamingDriverInterface
{
    protected $db;
    protected $binaryPath = '/usr/bin/icecast2';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getDisplayName() { return 'Icecast'; }
    public function getBinaryPath() { return $this->detectBinaryPath(); }

    protected function detectBinaryPath()
    {
        foreach ([$this->binaryPath, '/usr/bin/icecast', '/usr/local/bin/icecast', '/usr/local/bin/icecast2'] as $path) {
            if (is_file($path) && is_executable($path)) return $path;
        }
        $which = trim(shell_exec('command -v icecast2 2>/dev/null || command -v icecast 2>/dev/null') ?: '');
        return $which ?: $this->binaryPath;
    }

    public function getVersion()
    {
        $output = shell_exec($this->detectBinaryPath() . ' -v 2>/dev/null') ?: '';
        preg_match('/Icecast (\d+\.\d+\.\d+)/', $output, $m);
        return $m[1] ?? 'unknown';
    }

    public function isInstalled()
    {
        return is_file($this->detectBinaryPath());
    }

    public function isRunning()
    {
        $output = trim(shell_exec('systemctl is-active icecast2 2>/dev/null') ?: '');
        return $output === 'active';
    }

    public function install($installPath = null)
    {
        $cmd = $installPath ? "bash \"$installPath\"" : 'dnf install -y icecast icecast2 2>/dev/null || apt install -y icecast2 2>/dev/null';
        $output = shell_exec($cmd . ' 2>&1');
        shell_exec('systemctl enable --now icecast2 2>/dev/null || true');
        return ['success' => $this->isInstalled(), 'output' => $output];
    }

    public function uninstall()
    {
        shell_exec('systemctl stop icecast2 2>/dev/null; dnf remove -y icecast icecast2 2>/dev/null || apt remove -y icecast2 2>/dev/null');
    }

    public function createStation($userId, $data = [])
    {
        $port = $data['port'] ?? $this->allocatePort();
        $password = $data['password'] ?? bin2hex(random_bytes(8));
        $mount = $data['mount_point'] ?? $this->generateMountPoint($userId, $data['name'] ?? '');
        $bitrate = $data['bitrate'] ?? 128;
        $name = $data['name'] ?? 'My Icecast Station';
        $maxListeners = $data['max_listeners'] ?? 100;

        $configPath = $this->generateConfigFile($userId, $port, $password, $mount, $bitrate, $maxListeners);

        $id = $this->db->table('streaming_stations')->insertGetId([
            'user_id' => $userId,
            'engine' => 'icecast',
            'name' => $name,
            'server_type' => 'icecast',
            'port' => $port,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'admin_password' => password_hash($password, PASSWORD_DEFAULT),
            'mount_point' => $mount,
            'bitrate' => $bitrate,
            'format' => 'ogg',
            'max_listeners' => $maxListeners,
            'config_path' => $configPath,
            'status' => 'stopped',
        ]);

        return ['id' => $id, 'port' => $port, 'password' => $password, 'mount_point' => $mount, 'config_path' => $configPath];
    }

    public function deleteStation($station)
    {
        $this->stopStation($station);
        if ($station->config_path && file_exists($station->config_path)) @unlink($station->config_path);
        if ($station->pid_file && file_exists($station->pid_file)) @unlink($station->pid_file);
        $this->db->table('streaming_stations')->where('id', $station->id)->delete();
        $this->releasePort($station->port);
    }

    public function startStation($station)
    {
        if (!file_exists($station->config_path)) {
            $this->generateConfig($station);
        }
        $pidFile = "/tmp/icecast_{$station->id}.pid";
        $cmd = "nohup " . $this->detectBinaryPath() . " -c {$station->config_path} > /dev/null 2>&1 & echo \$! > {$pidFile}";
        shell_exec($cmd);
        sleep(1);
        $this->db->table('streaming_stations')->where('id', $station->id)->update([
            'status' => 'running',
            'pid_file' => $pidFile,
            'last_started' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true];
    }

    public function stopStation($station)
    {
        if ($station->pid_file && file_exists($station->pid_file)) {
            $pid = trim(file_get_contents($station->pid_file));
            shell_exec("kill {$pid} 2>/dev/null; kill -9 {$pid} 2>/dev/null");
            @unlink($station->pid_file);
        }
        if ($station->config_path) {
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
        return $this->generateConfigFile(
            $station->user_id,
            $station->port,
            '', // password from DB
            $station->mount_point ?? '/live',
            $station->bitrate ?? 128,
            $station->max_listeners ?? 100
        );
    }

    protected function generateConfigFile($userId, $port, $password, $mount, $bitrate, $maxListeners)
    {
        $user = $this->db->table('hosting_users')->where('id', $userId)->first();
        $username = $user->username ?? "user{$userId}";
        $dir = "/home/{$username}/radio/streams";
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        $configPath = "{$dir}/icecast_{$port}.xml";
        $certPath = "/etc/letsencrypt/live/{$username}.planet-hosts.com/fullchain.pem";

        $xml = "<?xml version=\"1.0\"?>\n<icecast>\n"
            . "    <limits>\n"
            . "        <clients>{$maxListeners}</clients>\n"
            . "        <sources>2</sources>\n"
            . "        <threadpool>5</threadpool>\n"
            . "        <queue-size>524288</queue-size>\n"
            . "        <client-timeout>30</client-timeout>\n"
            . "        <header-timeout>15</header-timeout>\n"
            . "        <source-timeout>10</source-timeout>\n"
            . "        <burst-on-connect>1</burst-on-connect>\n"
            . "        <burst-size>65535</burst-size>\n"
            . "    </limits>\n"
            . "    <authentication>\n"
            . "        <source-password>{$password}</source-password>\n"
            . "        <admin-password>{$password}</admin-password>\n"
            . "    </authentication>\n"
            . "    <listen-socket>\n"
            . "        <port>{$port}</port>\n"
            . "        <bind-address>0.0.0.0</bind-address>\n";

        if (file_exists($certPath)) {
            $xml .= "        <ssl>1</ssl>\n";
        }

        $xml .= "    </listen-socket>\n"
            . "    <mount>\n"
            . "        <mount-name>{$mount}</mount-name>\n"
            . "        <username>source</username>\n"
            . "        <password>{$password}</password>\n"
            . "        <max-listeners>{$maxListeners}</max-listeners>\n"
            . "        <burst-size>65535</burst-size>\n"
            . "        <no-yp>0</no-yp>\n"
            . "    </mount>\n"
            . "    <fileserve>1</fileserve>\n"
            . "    <paths>\n"
            . "        <basedir>{$dir}</basedir>\n"
            . "        <logdir>{$dir}/logs</logdir>\n"
            . "        <webroot>{$dir}/webroot</webroot>\n"
            . "        <adminroot>{$dir}/adminroot</adminroot>\n"
            . "        <alias source=\"/\" dest=\"/status.xsl\"/>\n"
            . "    </paths>\n"
            . "    <logging>\n"
            . "        <accesslog>access.log</accesslog>\n"
            . "        <errorlog>error.log</errorlog>\n"
            . "        <loglevel>3</loglevel>\n"
            . "    </logging>\n"
            . "    <security>\n"
            . "        <chroot>0</chroot>\n"
            . "    </security>\n";

        if (file_exists($certPath)) {
            $xml .= "    <ssl-certificate>{$certPath}</ssl-certificate>\n";
        }

        $xml .= "</icecast>\n";

        file_put_contents($configPath, $xml);
        return $configPath;
    }

    public function getStats($station)
    {
        $statusUrl = "http://127.0.0.1:{$station->port}/status-json.xsl";
        $ctx = stream_context_create(['http' => ['timeout' => 3]]);
        $json = @file_get_contents($statusUrl, false, $ctx);
        $stats = $json ? json_decode($json, true) : null;

        return [
            'listeners' => $stats['icestats']['source'][0]['listeners'] ?? 0,
            'listener_peak' => $stats['icestats']['source'][0]['listener_peak'] ?? 0,
            'bitrate' => $stats['icestats']['source'][0]['bitrate'] ?? $station->bitrate,
            'genre' => $stats['icestats']['source'][0]['genre'] ?? '',
            'server_name' => $stats['icestats']['source'][0]['server_name'] ?? $station->name,
            'stream_start' => $stats['icestats']['source'][0]['stream_start_iso'] ?? '',
            'source_ip' => $stats['icestats']['source'][0]['source_ip'] ?? '',
            'audio_info' => $stats['icestats']['source'][0]['audio_info'] ?? '',
        ];
    }

    public function getLogs($station, $lines = 100)
    {
        $logFile = "/home/{$this->getUsername($station->user_id)}/radio/streams/logs/error.log";
        if (!file_exists($logFile)) return [];
        $output = shell_exec("tail -{$lines} " . escapeshellarg($logFile) . ' 2>/dev/null');
        return explode("\n", trim($output ?: ''));
    }

    public function healthCheck($station)
    {
        $running = false;
        if ($station->pid_file && file_exists($station->pid_file)) {
            $pid = trim(file_get_contents($station->pid_file));
            $running = file_exists("/proc/{$pid}");
        }
        $portOpen = @fsockopen('127.0.0.1', $station->port, $errno, $errstr, 2);
        $stats = $this->getStats($station);

        return [
            'running' => $running,
            'port_open' => $portOpen !== false,
            'listeners' => $stats['listeners'],
            'config_exists' => $station->config_path && file_exists($station->config_path),
            'binary_exists' => $this->isInstalled(),
        ];
    }

    protected function allocatePort()
    {
        $used = $this->db->table('streaming_stations')->select('port')->get() ?: [];
        $usedPorts = array_map(fn($s) => $s->port, $used);
        for ($port = 8000; $port <= 9000; $port++) {
            if (!in_array($port, $usedPorts) && !@fsockopen('127.0.0.1', $port, $e, $e, 1)) {
                return $port;
            }
        }
        throw new \Exception('No available ports');
    }

    protected function releasePort($port)
    {
        // Port released by deletion
    }

    protected function generateMountPoint($userId, $name = ''): string
    {
        $slug = $name ? preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($name))) : "user{$userId}";
        $slug = trim($slug, '-') ?: "station";
        // Ensure uniqueness by checking existing mount points
        $existing = $this->db->table('streaming_stations')
            ->where('mount_point', 'LIKE', "/{$slug}%")
            ->count();
        return $existing > 0 ? "/{$slug}-{$existing}" : "/{$slug}";
    }

    protected function getUsername($userId)
    {
        $user = $this->db->table('hosting_users')->where('id', $userId)->first();
        return $user->username ?? "user{$userId}";
    }
}
