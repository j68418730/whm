<?php
// admin/Models/Station.php

namespace Admin\Models;

use Core\Model;

class Station extends Model
{
    protected $table = 'hosting_users';

    public function streamConfig()
    {
        return $this->db->table('station_stream_config')
            ->where('station_id', $this->id)
            ->first();
    }

    public function djs()
    {
        return $this->db->table('dj_stations')
            ->join('dj_accounts', 'dj_stations.dj_id', '=', 'dj_accounts.id')
            ->where('dj_stations.station_id', $this->id)
            ->get() ?: [];
    }

    public function generateApiKey()
    {
        $key = 'ph_' . bin2hex(random_bytes(16));
        // Store in billing_products or a separate api_keys table
        return $key;
    }
}