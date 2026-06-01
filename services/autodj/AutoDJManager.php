<?php
/**
 * AutoDJ Manager Service
 * Manages AutoDJ for radio streams
 * Integrated as core service in WHM
 */

namespace Services\AutoDJ;

use Core\Config;
use Core\Database;

class AutoDJManager
{
    protected $config;
    protected $db;

    public function __construct(Config $config, Database $db)
    {
        $this->config = $config;
        $this->db = $db;
    }

    /**
     * Enable AutoDJ for a stream
     */
    public function enableAutodj($streamId, $userId = null)
    {
        $query = $this->db->table('radio_streams')->where('id', $streamId);
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        $stream = $query->first();

        if (!$stream) {
            throw new \Exception("Stream not found.");
        }

        // Check if AutoDJ is already enabled
        $existing = $this->db->table('radio_autodj')->where('stream_id', $streamId)->first();
        if ($existing) {
            return $existing->id;
        }

        // Generate a plaintext password for AutoDJ (ezstream needs plaintext)
        $autodjPassword = $this->generatePassword();

        // Create AutoDJ configuration
        $configPath = $this->generateAutodjConfig($streamId, $autodjPassword);

        // Save AutoDJ record
        $autodjId = $this->db->table('radio_autodj')->insertGetId([
            'stream_id' => $streamId,
            'config_path' => $configPath,
            'autodj_password' => $autodjPassword,
            'status' => 'stopped',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $autodjId;
    }

    public function getByStreamId($streamId)
    {
        return $this->db->table('radio_autodj')->where('stream_id', $streamId)->first();
    }

    public function getById($autodjId)
    {
        return $this->db->table('radio_autodj')->where('id', $autodjId)->first();
    }

    /**
     * Disable AutoDJ for a stream
     */
    public function disableAutodj($streamId, $userId = null)
    {
        if ($userId !== null) {
            $stream = $this->db->table('radio_streams')->where('id', $streamId)->where('user_id', $userId)->first();
            if (!$stream) {
                throw new \Exception("Stream not found.");
            }
        }
        $autodj = $this->db->table('radio_autodj')->where('stream_id', $streamId)->first();

        if (!$autodj) {
            throw new \Exception("AutoDJ not found for this stream.");
        }

        // Stop AutoDJ if running
        if ($autodj->status === 'running') {
            $this->stopAutodjProcess($autodj->config_path);
        }

        // Delete AutoDJ record and config file
        @unlink($autodj->config_path);
        $this->db->table('radio_autodj')->where('id', $autodj->id)->delete();

        return true;
    }

    /**
     * Start AutoDJ for a stream
     */
    public function startAutodj($autodjId)
    {
        $autodj = $this->db->table('radio_autodj')->where('id', $autodjId)->first();

        if (!$autodj) {
            throw new \Exception("AutoDJ not found.");
        }

        // Start the AutoDJ process
        $process = $this->startAutodjProcess($autodj->config_path);

        // Update status
        $this->db->table('radio_autodj')
            ->where('id', $autodjId)
            ->update([
                'status' => 'running',
                'updated_at' => now(),
            ]);

        return true;
    }

    /**
     * Stop AutoDJ for a stream
     */
    public function stopAutodj($autodjId)
    {
        $autodj = $this->db->table('radio_autodj')->where('id', $autodjId)->first();

        if (!$autodj) {
            throw new \Exception("AutoDJ not found.");
        }

        // Stop the AutoDJ process
        $this->stopAutodjProcess($autodj->config_path);

        // Update status
        $this->db->table('radio_autodj')
            ->where('id', $autodjId)
            ->update([
                'status' => 'stopped',
                'updated_at' => now(),
            ]);

        return true;
    }

    /**
     * Generate AutoDJ configuration file (for ezstream)
     */
    protected function generateAutodjConfig($streamId, $autodjPassword)
    {
        $stream = $this->db->table('radio_streams')->where('id', $streamId)->first();
        $username = $this->getUsernameById($stream->user_id);
        $configDir = "/home/{$username}/radio/autodj";
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        $configFile = "{$configDir}/autodj_{$streamId}.cfg";

        // Get stream details for mount point
        $mountPoint = "/live";

        $config = <<<CONF
[input]
filename = stdin
once = false
restart_delay = 2
recheck = 1
recheck_after_write = 2
metadata_interval = 16384

[instance]
format = mp3
bitrate = 128
server = localhost
port = {$stream->port}
password = {$autodjPassword}
mountPoint = {$mountPoint}
name = AutoDJ Stream
description = AutoDJ generated stream
genre = Various
url = http://localhost:{$stream->port}
public = 0

[stream]
title = AutoDJ Stream
CONF;

        file_put_contents($configFile, $config);

        return $configFile;
    }

    /**
     * Start the AutoDJ process
     */
    protected function startAutodjProcess($configPath)
    {
        $binary = $this->config->get("radio.autodj.binary_path");
        $command = "{$binary} -c {$configPath}";

        // Execute the command in the background
        exec("nohup {$command} > /dev/null 2>&1 &");

        return $command;
    }

    /**
     * Stop the AutoDJ process
     */
    protected function stopAutodjProcess($configPath)
    {
        // In a real system, we would track the process ID and kill it
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

    protected function getUsernameById($userId)
    {
        $user = $this->db->table('hosting_users')->where('id', $userId)->first();
        if ($user && !empty($user->username)) {
            return $user->username;
        }
        return "user_{$userId}";
    }
}
