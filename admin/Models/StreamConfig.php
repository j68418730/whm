<?php
// admin/Models/StreamConfig.php

namespace Admin\Models;

use Core\Model;

class StreamConfig extends Model
{
    protected $table = 'station_stream_config';

    public function station()
    {
        return $this->db->table('hosting_users')
            ->where('id', $this->station_id)
            ->first();
    }

    public function getIcecastConfig()
    {
        return [
            'hostname' => $this->icecast_hostname,
            'port' => $this->icecast_port,
            'username' => $this->icecast_username,
            'password' => $this->icecast_password,
            'mount' => $this->icecast_mount,
            'protocol' => $this->icecast_protocol,
        ];
    }

    public function getShoutcastV1Config()
    {
        return [
            'hostname' => $this->shoutcast_v1_hostname,
            'port' => $this->shoutcast_v1_port,
            'password' => $this->shoutcast_v1_password,
        ];
    }

    public function getShoutcastV2Config()
    {
        return [
            'hostname' => $this->shoutcast_v2_hostname,
            'port' => $this->shoutcast_v2_port,
            'username' => $this->shoutcast_v2_username,
            'password' => $this->shoutcast_v2_password,
        ];
    }

    public function getConfigForType($type)
    {
        switch ($this->icecast_protocol) {
            case 'icecast':
            case 'icecast_kh':
                return $this->getIcecastConfig();
            case 'shoutcast_v1':
                return $this->getShoutcastV1Config();
            case 'shoutcast_v2':
                return $this->getShoutcastV2Config();
            default:
                return $this->getIcecastConfig();
        }
    }

    public function getConnectionString()
    {
        $config = $this->getConfigForType($this->icecast_protocol);
        
        switch ($this->icecast_protocol) {
            case 'icecast':
            case 'icecast_kh':
                return "icecast://{$config['username']}:{$config['password']}@{$config['hostname']}:{$config['port']}{$config['mount']}";
            case 'shoutcast_v1':
                return "shoutcast://:{$config['password']}@{$config['hostname']}:{$config['port']}";
            case 'shoutcast_v2':
                return "shoutcast2://{$config['username']}:{$config['password']}@{$config['hostname']}:{$config['port']}";
            default:
                return '';
        }
    }
}