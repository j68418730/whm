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

        if ($isDjConnected && $autodjEnabled) {
            // DJ connected - disable AutoDJ
            $this->db->table('radio_streams')->where('id', $stream->id)->update([
                'autodj_enabled' => 0,
                'autodj_active' => 0,
            ]);
            $this->log("AutoDJ paused for {$stream->server_name} - DJ connected");
        } elseif (!$isDjConnected && !$autodjEnabled && ($stream->autodj_active ?? 0) == 0) {
            // DJ disconnected - re-enable AutoDJ
            $this->db->table('radio_streams')->where('id', $stream->id)->update([
                'autodj_enabled' => 1,
                'autodj_active' => 1,
            ]);
            $this->log("AutoDJ resumed for {$stream->server_name} - DJ disconnected");
        }
    }

    protected function isDjConnected($stream)
    {
        // Check if a source is connected to the Icecast mount
        $port = $stream->port ?? 8000;
        $mount = $stream->mount ?? '/stream';
        $adminPass = $stream->admin_password ?? 'admin';

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

        // Check if the mount has a connected source (DJ)
        // Simple check: look for the mount point in the XML response
        $xml = simplexml_load_string($resp);
        if ($xml) {
            foreach ($xml->source ?? [] as $source) {
                $mountName = (string)($source->mount ?? '');
                $listenerCount = (int)($source->listeners ?? 0);
                if ($mountName === $mount && $listenerCount > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function log($msg)
    {
        error_log("[RadioAutoDJ] {$msg}");
    }
}
