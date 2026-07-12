<?php

namespace Plugins\Studio\Services;

class RealtimeService
{
    protected $db;
    protected $sseFile;

    public function __construct($db)
    {
        $this->db = $db;
        $this->sseFile = sys_get_temp_dir() . '/studio_sse_';
    }

    public function emit($stationId, $event, $data)
    {
        $payload = json_encode(['event' => $event, 'data' => $data, 'time' => time()]);
        $file = $this->sseFile . $stationId . '.json';
        file_put_contents($file, $payload . "\n", FILE_APPEND | LOCK_EX);

        $logFile = $this->sseFile . $stationId . '_log.json';
        $log = [];
        if (file_exists($logFile)) {
            $log = json_decode(file_get_contents($logFile), true) ?: [];
        }
        $log[] = ['event' => $event, 'time' => time()];
        $log = array_slice($log, -50);
        file_put_contents($logFile, json_encode($log));
    }

    public function poll($stationId, $since = 0)
    {
        $file = $this->sseFile . $stationId . '.json';
        if (!file_exists($file)) return [];

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $events = [];
        foreach ($lines as $line) {
            $ev = json_decode($line, true);
            if ($ev && $ev['time'] > $since) {
                $events[] = $ev;
            }
        }
        return $events;
    }

    public function clear($stationId)
    {
        $file = $this->sseFile . $stationId . '.json';
        if (file_exists($file)) @unlink($file);
        $logFile = $this->sseFile . $stationId . '_log.json';
        if (file_exists($logFile)) @unlink($logFile);
    }

    public function notifySongChange($stationId, $song)
    {
        $this->emit($stationId, 'song_change', $song);
    }

    public function notifyListenerChange($stationId, $count)
    {
        $this->emit($stationId, 'listener_change', ['listeners' => $count]);
    }

    public function notifyQueueChange($stationId, $queue)
    {
        $this->emit($stationId, 'queue_change', ['count' => count($queue)]);
    }

    public function notifyConnectorStatus($stationId, $status)
    {
        $this->emit($stationId, 'connector_status', $status);
    }

    public function notifyConnectorUpload($stationId, $file)
    {
        $this->emit($stationId, 'connector_upload', ['file' => $file]);
    }

    public function createConnectorLog($stationId, $level, $message)
    {
        $this->db->table('studio_connector_logs')->insertGetId([
            'station_id' => $stationId,
            'level' => $level,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getConnectorLogs($stationId, $limit = 50)
    {
        return $this->db->table('studio_connector_logs')
            ->where('station_id', $stationId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get() ?: [];
    }
}