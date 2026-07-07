<?php
/**
 * AutoDJ Manager - Auto-disables AutoDJ when a live DJ connects,
 * and re-enables it when the DJ disconnects.
 */
namespace Services;

class RadioAutoDJ
{
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
    }

    public function checkAllStreams()
    {
        $streams = $this->db->table('radio_streams')->get() ?: [];
        foreach ($streams as $s) {
            $this->checkStream($s);
        }
    }

    public function checkStream($stream)
    {
        $isDjConnected = $this->isDjConnected($stream);
        $autodjEnabled = (bool)($stream->autodj_enabled ?? false);
        $autodjRunning = $this->isAutodjProcessRunning($stream);

        if ($isDjConnected && $autodjRunning) {
            // DJ connected while AutoDJ is running — stop AutoDJ
            $this->stopAutodjProcess($stream);
            $this->db->table('radio_streams')->where('id', $stream->id)->update([
                'autodj_enabled' => 0,
                'autodj_active' => 0,
            ]);
            try { $this->db->table('radio_autodj_config')->where('station_id', $stream->id)->update(['autodj_enabled' => 0]); } catch (\Exception $e) {}
            $this->log("AutoDJ paused for {$stream->server_name} - DJ connected");
        } elseif (!$isDjConnected && !$autodjEnabled && !$autodjRunning) {
            // DJ disconnected and AutoDJ is off — restart AutoDJ
            $this->db->table('radio_streams')->where('id', $stream->id)->update([
                'autodj_enabled' => 1,
                'autodj_active' => 1,
            ]);
            try {
                $ss = $this->db->table('streaming_stations')->where('id', $stream->id)->first();
                if ($ss) {
                    $hu = $this->db->table('hosting_users')->where('id', $ss->user_id)->first();
                    if ($hu) {
                        $cfg = $this->db->table('radio_autodj_config')->where('station_id', $stream->id + 10000)->first();
                        $plIds = $cfg && $cfg->playlist_ids ? json_decode($cfg->playlist_ids, true) : [];
                        $player = new RadioAutoDJPlayer($ss, $hu->username, $plIds);
                        $player->start();
                    }
                }
            } catch (\Exception $e) {}
            $this->log("AutoDJ resumed for {$stream->server_name} - DJ disconnected");
        }
    }

    protected function isDjConnected($stream)
    {
        $port = $stream->port ?? 8000;
        $type = strtolower($stream->server_type ?? 'icecast');
        $adminPass = $stream->admin_password ?? 'admin';

        if ($type === 'icecast') {
            $url = "http://localhost:{$port}/admin/listmounts";
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERPWD => "admin:{$adminPass}",
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
            ]);
            $resp = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode !== 200) return false;
            $xml = simplexml_load_string($resp);
            if ($xml) {
                foreach ($xml->source ?? [] as $source) {
                    $mountName = (string)($source->mount ?? '');
                    $listenerCount = (int)($source->listeners ?? 0);
                    if ($mountName === ($stream->mount ?? '/stream') && $listenerCount > 0) return true;
                }
            }
            return false;
        } else {
            // SHOUTcast: check statistics for active source
            $url = "http://localhost:{$port}/statistics";
            $xml = @simplexml_load_string(@file_get_contents($url, false, stream_context_create(['http'=>['timeout'=>3]])));
            if (!$xml) return false;
            return ((int)($xml->STREAMSTATUS ?? 0)) > 0;
        }
    }

    protected function isAutodjProcessRunning($stream)
    {
        $streamId = $stream->id ?? 0;
        exec("pgrep -f \"runner_{$streamId}\" 2>/dev/null", $pids);
        if (!empty($pids)) return true;
        exec("pgrep -f \"ffmpeg.*{$stream->port}\" 2>/dev/null", $pids);
        return !empty($pids);
    }

    protected function stopAutodjProcess($stream)
    {
        $streamId = $stream->id ?? 0;
        $pidFile = '/home/' . ($stream->username ?? 'planethosts') . '/radio/autodj/autodj.pid';
        if (file_exists($pidFile)) {
            $pid = (int)trim(@file_get_contents($pidFile));
            if ($pid > 0) { exec("kill {$pid} 2>/dev/null"); usleep(200000); exec("kill -0 {$pid} 2>/dev/null && kill -9 {$pid} 2>/dev/null"); }
            @unlink($pidFile);
        }
        exec("pkill -f \"runner_{$streamId}\" 2>/dev/null");
        exec("pkill -f \"ffmpeg.*{$stream->port}\" 2>/dev/null");
    }

    protected function log($msg)
    {
        error_log("[RadioAutoDJ] {$msg}");
    }
}
