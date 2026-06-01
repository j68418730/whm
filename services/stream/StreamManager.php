<?php
/**
 * Stream Manager Service
 * Manages radio streaming servers (Icecast/Shoutcast)
 * Integrated as core service in WHM
 */

namespace Services\Stream;

use Core\Config;
use Core\Database;

class StreamManager
{
    protected $config;
    protected $db;

    public function __construct(Config $config, Database $db)
    {
        $this->config = $config;
        $this->db = $db;
    }

    /**
     * Create a new stream for a user
     */
    public function createStream($userId, $serverType, $port = null, $password = null)
    {
        // Get server configuration
        $serverConfig = $this->config->get("radio.servers.{$serverType}");

        if (!$serverConfig['enabled']) {
            throw new \Exception("Server type {$serverType} is not enabled.");
        }

        // Set default port if not provided
        if ($port === null) {
            $port = $serverConfig['default_port'];
        }

        // Generate password if not provided
        if ($password === null) {
            $password = $this->generatePassword();
        }

        // Create stream configuration file
        $configPath = $this->generateConfigFile($userId, $serverType, $port, $password);

        // Start the server process
        $process = $this->startServer($serverType, $configPath);

        // Save stream record to database
        $streamId = $this->db->table('radio_streams')->insertGetId([
            'user_id' => $userId,
            'server_type' => $serverType,
            'port' => $port,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'config_path' => $configPath,
            'status' => 'starting',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'id' => $streamId,
            'port' => $port,
            'password' => $password,
            'mount_point' => "/live",
            'status' => 'starting'
        ];
    }

    public function getUserStreams($userId)
    {
        $streams = $this->db->table('radio_streams')->where('user_id', $userId)->get();

        return array_map(function ($stream) {
            return $this->presentStream($stream);
        }, $streams);
    }

    public function getStream($streamId, $userId = null)
    {
        $query = $this->db->table('radio_streams')->where('id', $streamId);
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $stream = $query->first();
        return $stream ? $this->presentStream($stream) : null;
    }

    public function startStream($streamId, $userId = null)
    {
        $stream = $this->getStream($streamId, $userId);
        if (!$stream) {
            throw new \Exception("Stream not found.");
        }

        $this->startServer($stream['server_type'], $stream['config_path']);
        $this->db->table('radio_streams')->where('id', $streamId)->update([
            'status' => 'running',
            'updated_at' => now(),
        ]);

        return true;
    }

    /**
     * Stop a stream
     */
    public function stopStream($streamId, $userId = null)
    {
        $query = $this->db->table('radio_streams')->where('id', $streamId);
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        $stream = $query->first();

        if (!$stream) {
            throw new \Exception("Stream not found.");
        }

        // Stop the server process (implementation depends on server type)
        $this->stopServerProcess($stream->server_type, $stream->config_path);

        // Update database
        $this->db->table('radio_streams')
            ->where('id', $streamId)
            ->update([
                'status' => 'stopped',
                'updated_at' => now(),
            ]);

        return true;
    }

    protected function presentStream($stream)
    {
        return [
            'id' => $stream->id,
            'user_id' => $stream->user_id,
            'server_type' => $stream->server_type,
            'port' => $stream->port,
            'config_path' => $stream->config_path,
            'status' => $stream->status,
            'listener_count' => $stream->listener_count ?? 0,
            'bandwidth_used' => $stream->bandwidth_used ?? 0,
            'mount_point' => '/live',
        ];
    }

    /**
     * Generate configuration file for the stream
     */
    protected function generateConfigFile($userId, $serverType, $port, $password)
    {
        $username = $this->getUsernameById($userId);
        $configDir = "/home/{$username}/radio/streams";
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        $configFile = "{$configDir}/{$serverType}.conf";

        if ($serverType === 'icecast') {
            $config = $this->generateIcecastConfig($port, $password);
        } elseif ($serverType === 'shoutcast') {
            $config = $this->generateShoutcastConfig($port, $password);
        } else {
            throw new \Exception("Unsupported server type: {$serverType}");
        }

        file_put_contents($configFile, $config);

        return $configFile;
    }

    protected function generateIcecastConfig($port, $password)
    {
        return <<<XML
<icecast>
    <limits>
        <clients>100</clients>
        <sources>2</sources>
        <threadpool>5</threadpool>
        <queue-size>524288</queue-size>
        <client-timeout>30</client-timeout>
        <header-timeout>15</header-timeout>
        <source-timeout>10</source-timeout>
        <burst-on-connect>1</burst-on-connect>
        <burst-size>65535</burst-size>
    </limits>

    <authentication>
        <source-password>{$password}</source-password>
        <admin-user>admin</admin-user>
        <admin-password>{$password}</admin-password>
    </authentication>

    <hostname>localhost</hostname>
    <listen-socket>
        <port>{$port}</port>
    </listen-socket>

    <fileserve>1</fileserve>

    <paths>
        <basedir>/usr/share/icecast2</basedir>
        <logdir>/var/log/icecast2</logdir>
        <webroot>/usr/share/icecast2/web</webroot>
        <adminroot>/usr/share/icecast2/admin</adminroot>
        <alias source="/" dest="/status.xsl"/>
    </paths>

    <logging>
        <accesslog>access.log</accesslog>
        <errorlog>error.log</errorlog>
        <loglevel>3</loglevel>
        <logsize>10000</logsize>
    </logging>

    <security>
        <chroot>0</chroot>
        <changeowner>
            <user>nobody</user>
            <group>nogroup</group>
        </changeowner>
    </security>
</icecast>
XML;
    }

    protected function generateShoutcastConfig($port, $password)
    {
        return <<<CONF
maxuser=100
password={$password}
portbase={$port}
logfile=logs/sc_serv.log
realtime=1
screenlog=1
showlastsongs=1
charset=utf-8
CONF;
    }

    /**
     * Start the server process
     */
    protected function startServer($serverType, $configPath)
    {
        $binary = $this->config->get("radio.servers.{$serverType}.binary_path");

        if ($serverType === 'icecast') {
            $command = "{$binary} -c {$configPath}";
        } elseif ($serverType === 'shoutcast') {
            $command = "{$binary} {$configPath}";
        } else {
            throw new \Exception("Unsupported server type: {$serverType}");
        }

        // Execute the command in the background
        // In a real system, we would use a process manager like Supervisor
        $safeCommand = implode(' ', array_map('escapeshellarg', explode(' ', $command)));
        exec("nohup {$safeCommand} > /dev/null 2>&1 &");

        return $command;
    }

    /**
     * Stop the server process
     */
    protected function stopServerProcess($serverType, $configPath)
    {
        // In a real system, we would track the process ID and kill it
        // For now, we'll just note that the stream is stopped
        // A more robust implementation would use PID files or a process manager
        exec("pkill -f " . escapeshellarg($configPath));
    }

    /**
     * Generate a random password
     */
    protected function generatePassword($length = 16)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Get the system username for a user ID
     */
    protected function getUsernameById($userId)
    {
        $user = $this->db->table('hosting_users')->where('id', $userId)->first();
        if ($user && !empty($user->username)) {
            return $user->username;
        }
        return "user_{$userId}";
    }
}
