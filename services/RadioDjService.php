<?php
namespace Services;

/**
 * Radio DJ account management.
 *
 * Model:
 *  - Every hosting account gets a PRIMARY DJ whose username == the account username
 *    (e.g. account "testacct" => primary DJ "testacct").
 *  - The primary DJ is auto-created when the first station is provisioned and is
 *    assigned to EVERY station on the account (icecast + shoutcast v1 + v2).
 *  - Any DJ created on the account is, by default, assigned to ALL stations
 *    via the radio_dj_streams junction. Later, a DJ can be added to a new radio too.
 */
class RadioDjService
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Return all streaming_stations for a hosting user.
     */
    public function getAccountStations($hostingUserId)
    {
        try {
            return $this->db->table('streaming_stations')
                ->where('user_id', (int)$hostingUserId)
                ->orderBy('id', 'ASC')
                ->get() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Ensure the primary DJ (username = account username) exists and is assigned
     * to every station on the account. Returns the DJ row (or null).
     */
    public function ensurePrimaryDj($hostingUser)
    {
        if (!$hostingUser || empty($hostingUser->username)) return null;
        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $hostingUser->username));
        if (!$username) return null;

        $stations = $this->getAccountStations($hostingUser->id);
        $primaryStream = !empty($stations[0]) ? (int)$stations[0]->id : 0;

        // Find existing primary DJ by username
        $dj = null;
        try {
            $dj = $this->db->table('radio_djs')->where('username', $username)->first();
        } catch (\Exception $e) {}

        if (!$dj) {
            $password = bin2hex(random_bytes(6));
            try {
                $djId = $this->db->table('radio_djs')->insertGetId([
                    'stream_id' => $primaryStream,
                    'username' => $username,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'plain_password' => $password,
                    'name' => $hostingUser->username,
                    'email' => $hostingUser->email ?? '',
                    'status' => 'active',
                    'can_stream' => 1,
                ]);
                $dj = $this->db->table('radio_djs')->where('id', $djId)->first();
            } catch (\Exception $e) {
                return null;
            }
        }

        // Ensure primary stream is set on the DJ row
        if ($dj && $primaryStream && (int)$dj->stream_id === 0) {
            try {
                $this->db->table('radio_djs')->where('id', $dj->id)->update(['stream_id' => $primaryStream]);
                $dj->stream_id = $primaryStream;
            } catch (\Exception $e) {}
        }

        // Assign to all account stations (junction)
        if ($dj) {
            $this->assignDjToStations($dj->id, $stations);
        }

        return $dj;
    }

    /**
     * Assign a DJ to every station on an account (junction inserts, idempotent).
     */
    public function assignDjToStations($djId, $stations = null, $hostingUserId = null)
    {
        if ($stations === null && $hostingUserId !== null) {
            $stations = $this->getAccountStations($hostingUserId);
        }
        if (!$stations) return;

        // Load the DJ's primary stream id
        $primaryStream = 0;
        try {
            $dj = $this->db->table('radio_djs')->where('id', (int)$djId)->first();
            $primaryStream = $dj ? (int)$dj->stream_id : 0;
        } catch (\Exception $e) {}

        foreach ($stations as $st) {
            $sid = (int)$st->id;
            if ($sid <= 0) continue;
            // Skip if the DJ row already references it as the primary (implicit)
            if ($sid === $primaryStream) {
                try {
                    $this->db->table('radio_dj_streams')->where('dj_id', (int)$djId)->where('stream_id', $sid)->delete();
                } catch (\Exception $e) {}
                continue;
            }
            try {
                $exists = $this->db->table('radio_dj_streams')
                    ->where('dj_id', (int)$djId)
                    ->where('stream_id', $sid)
                    ->first();
                if (!$exists) {
                    $this->db->table('radio_dj_streams')->insertGetId([
                        'dj_id' => (int)$djId,
                        'stream_id' => $sid,
                        'assigned_by' => 0,
                        'assigned_at' => date('Y-m-d H:i:s'),
                        'is_active' => 'yes',
                    ]);
                }
            } catch (\Exception $e) {}
        }
    }

    /**
     * Assign a DJ to ALL stations on the hosting user's account (used on DJ create).
     */
    public function assignNewDjToAllStations($djId, $hostingUserId)
    {
        $stations = $this->getAccountStations($hostingUserId);
        $this->assignDjToStations($djId, $stations);
    }
}
