<?php
// admin/Models/Dj.php

namespace Admin\Models;

use Core\Model;

class Dj extends Model
{
    protected $table = 'dj_accounts';

    public function stations()
    {
        return $this->db->table('dj_stations')
            ->join('hosting_users', 'dj_stations.station_id', '=', 'hosting_users.id')
            ->where('dj_stations.dj_id', $this->id)
            ->get() ?: [];
    }

    public function apiKeys()
    {
        return $this->db->table('dj_api_keys')->where('dj_id', $this->id)->get() ?: [];
    }

    public function sessions()
    {
        return $this->db->table('dj_sessions')->where('dj_id', $this->id)->get() ?: [];
    }

    public function activityLog($limit = 50)
    {
        return $this->db->table('dj_activity_log')
            ->where('dj_id', $this->id)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get() ?: [];
    }

    public function hasAccessToStation($stationId)
    {
        return $this->db->table('dj_stations')
            ->where('dj_id', $this->id)
            ->where('station_id', $stationId)
            ->first() !== null;
    }
}