<?php

namespace Plugins\Studio\Services;

use Plugins\Radio\Services\StreamingEngine;

class StudioService
{
    protected $db;
    protected $config;
    protected $engine;

    public function __construct($db, $config)
    {
        $this->db = $db;
        $this->config = $config;
        $this->engine = StreamingEngine::getInstance();
    }

    public function getStations()
    {
        return $this->engine->getAllStations() ?: [];
    }

    public function getUserStations($userId)
    {
        return $this->engine->getUserStations($userId) ?: [];
    }

    public function getStation($stationId)
    {
        return $this->engine->getStation($stationId);
    }

    public function getStationStats($stationId)
    {
        return $this->engine->getStationStats($stationId);
    }

    public function getStationHealth($stationId)
    {
        return $this->engine->healthCheck($stationId);
    }

    public function getStationMonitoring($stationId)
    {
        return $this->engine->getMonitoringData($stationId);
    }

    public function getStationLogs($stationId, $lines = 50)
    {
        return $this->engine->getStationLogs($stationId, $lines);
    }

    public function getAvailableEngines()
    {
        return $this->engine->getAvailableDrivers();
    }

    public function getQueue($stationId)
    {
        $autodj = $this->db->table('radio_autodj')->where('stream_id', $stationId)->first();
        if (!$autodj) return [];

        $items = $this->db->table('radio_playlist_items')
            ->join('radio_playlists', 'radio_playlist_items.playlist_id', '=', 'radio_playlists.id')
            ->where('radio_playlists.stream_id', $stationId)
            ->orderBy('radio_playlist_items.added_at', 'asc')
            ->limit(50)
            ->get() ?: [];

        return $items;
    }

    public function getCurrentSong($stationId)
    {
        $stream = $this->db->table('radio_streams')->where('id', $stationId)->first();
        if (!$stream) return null;

        $stats = $this->getStationStats($stationId);

        return [
            'title' => $stream->last_song_title ?? $stats['server_name'] ?? 'N/A',
            'artist' => $stream->last_song_artist ?? '',
            'listeners' => $stats['listeners'] ?? 0,
            'stream_start' => $stats['stream_start'] ?? '',
        ];
    }

    public function getSongHistory($stationId, $limit = 20)
    {
        return $this->db->table('radio_song_history')
            ->where('stream_id', $stationId)
            ->orderBy('played_at', 'desc')
            ->limit($limit)
            ->get() ?: [];
    }

    public function getMusicLibrary($stationId)
    {
        return $this->db->table('radio_playlist_items')
            ->join('radio_playlists', 'radio_playlist_items.playlist_id', '=', 'radio_playlists.id')
            ->where('radio_playlists.stream_id', $stationId)
            ->orderBy('radio_playlist_items.artist', 'asc')
            ->orderBy('radio_playlist_items.album', 'asc')
            ->get() ?: [];
    }

    public function getPlaylists($stationId)
    {
        return $this->db->table('radio_playlists')
            ->where('stream_id', $stationId)
            ->get() ?: [];
    }

    public function getPlaylistItems($playlistId)
    {
        return $this->db->table('radio_playlist_items')
            ->where('playlist_id', $playlistId)
            ->get() ?: [];
    }

    public function getLiveDjs($stationId)
    {
        return $this->db->table('radio_djs')
            ->where('stream_id', $stationId)
            ->get() ?: [];
    }

    public function getConnectedDj($stationId)
    {
        return $this->db->table('radio_djs')
            ->where('stream_id', $stationId)
            ->where('current_connections', '>', 0)
            ->first();
    }

    public function getRequests($stationId, $limit = 20)
    {
        return $this->db->table('radio_requests')
            ->where('stream_id', $stationId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get() ?: [];
    }

    public function getSchedule($stationId)
    {
        return $this->db->table('radio_schedule')
            ->where('stream_id', $stationId)
            ->where('is_active', 1)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get() ?: [];
    }

    public function getListenerAnalytics($stationId, $days = 7)
    {
        $since = date('Y-m-d', strtotime("-{$days} days"));
        return $this->db->table('radio_listener_analytics')
            ->where('stream_id', $stationId)
            ->where('date', '>=', $since)
            ->orderBy('date', 'asc')
            ->orderBy('hour', 'asc')
            ->get() ?: [];
    }

    public function getBranding($stationId)
    {
        return $this->db->table('radio_branding')
            ->where('station_id', $stationId)
            ->first();
    }

    public function getStreamInfo($stationId)
    {
        $station = $this->getStation($stationId);
        if (!$station) return null;

        $stats = $this->getStationStats($stationId);

        return [
            'id' => $station->id,
            'name' => $station->name,
            'engine' => $station->engine ?? $station->server_type,
            'server_type' => $station->server_type,
            'port' => $station->port,
            'mount_point' => $station->mount_point,
            'bitrate' => $station->bitrate,
            'format' => $station->format,
            'max_listeners' => $station->max_listeners,
            'public_server' => $station->public_server,
            'status' => $station->status,
            'listeners' => $stats['listeners'] ?? 0,
            'listener_peak' => $stats['listener_peak'] ?? 0,
            'genre' => $stats['genre'] ?? '',
            'audio_info' => $stats['audio_info'] ?? '',
            'ssl_enabled' => $station->ssl_enabled,
            'autodj_enabled' => $station->autodj_enabled,
        ];
    }

    public function getUserById($userId)
    {
        return $this->db->table('hosting_users')->where('id', $userId)->first();
    }

    public function getDashboardData($stationId)
    {
        return [
            'station' => $this->getStreamInfo($stationId),
            'current_song' => $this->getCurrentSong($stationId),
            'queue' => $this->getQueue($stationId),
            'history' => $this->getSongHistory($stationId, 10),
            'djs' => $this->getLiveDjs($stationId),
            'connected_dj' => $this->getConnectedDj($stationId),
            'requests' => $this->getRequests($stationId, 10),
            'playlists' => $this->getPlaylists($stationId),
            'library' => $this->getMusicLibrary($stationId),
            'schedule' => $this->getSchedule($stationId),
            'health' => $this->getStationHealth($stationId),
            'monitoring' => $this->getStationMonitoring($stationId),
        ];
    }

    // ─── Phase 2: Queue Editing ───

    public function addToQueue($stationId, $playlistItemId)
    {
        $item = $this->db->table('radio_playlist_items')->where('id', $playlistItemId)->first();
        if (!$item) return ['success' => false, 'error' => 'Track not found'];

        $autoDj = $this->db->table('radio_autodj')->where('stream_id', $stationId)->first();
        if (!$autoDj) return ['success' => false, 'error' => 'AutoDJ not configured'];

        $this->db->table('studio_queue')->insertGetId([
            'station_id' => $stationId,
            'playlist_item_id' => $playlistItemId,
            'title' => $item->title,
            'artist' => $item->artist,
            'album' => $item->album,
            'duration' => $item->duration,
            'file_path' => $item->file_path,
            'sort_order' => $this->getNextQueueOrder($stationId),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true];
    }

    public function removeFromQueue($queueId)
    {
        $this->db->table('studio_queue')->where('id', $queueId)->delete();
        return ['success' => true];
    }

    public function reorderQueue($stationId, array $order)
    {
        foreach ($order as $pos => $id) {
            $this->db->table('studio_queue')->where('id', $id)->where('station_id', $stationId)->update([
                'sort_order' => $pos,
            ]);
        }
        return ['success' => true];
    }

    public function clearQueue($stationId)
    {
        $this->db->table('studio_queue')->where('station_id', $stationId)->delete();
        return ['success' => true];
    }

    public function getStudioQueue($stationId)
    {
        return $this->db->table('studio_queue')
            ->where('station_id', $stationId)
            ->orderBy('sort_order', 'asc')
            ->get() ?: [];
    }

    protected function getNextQueueOrder($stationId)
    {
        $last = $this->db->table('studio_queue')
            ->where('station_id', $stationId)
            ->orderBy('sort_order', 'desc')
            ->first();
        return $last ? ($last->sort_order + 1) : 0;
    }

    // ─── Phase 2: Play Control ───

    public function playSong($stationId, $queueId = null)
    {
        $station = $this->getStation($stationId);
        if (!$station) return ['success' => false, 'error' => 'Station not found'];

        if ($queueId) {
            $queueItem = $this->db->table('studio_queue')->where('id', $queueId)->where('station_id', $stationId)->first();
            if (!$queueItem) return ['success' => false, 'error' => 'Queue item not found'];

            $this->db->table('radio_streams')->where('id', $stationId)->update([
                'last_song_title' => $queueItem->title,
                'last_song_artist' => $queueItem->artist,
            ]);

            $this->db->table('radio_song_history')->insertGetId([
                'stream_id' => $stationId,
                'title' => $queueItem->title,
                'artist' => $queueItem->artist,
                'played_at' => date('Y-m-d H:i:s'),
            ]);

            $this->db->table('studio_queue')->where('id', $queueId)->delete();
        }

        if ($station->engine === 'shoutcast' && method_exists($this->engine->driver('shoutcast'), 'startAutodj')) {
            $this->engine->driver('shoutcast')->startAutodj($stationId);
        }

        return ['success' => true, 'song' => $queueItem ?? null];
    }

    public function stopPlayback($stationId)
    {
        $station = $this->getStation($stationId);
        if (!$station) return ['success' => false, 'error' => 'Station not found'];

        if ($station->engine === 'shoutcast' && method_exists($this->engine->driver('shoutcast'), 'stopAutodj')) {
            $this->engine->driver('shoutcast')->stopAutodj($stationId);
        }

        return ['success' => true];
    }

    public function cueSong($stationId, $queueId)
    {
        $queueItem = $this->db->table('studio_queue')->where('id', $queueId)->where('station_id', $stationId)->first();
        if (!$queueItem) return ['success' => false, 'error' => 'Queue item not found'];

        $this->db->table('studio_queue')->where('id', $queueId)->update([
            'is_cued' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'song' => $queueItem];
    }

    // ─── Phase 2: Playlist Editing ───

    public function createPlaylist($stationId, $name, $description = '')
    {
        $id = $this->db->table('radio_playlists')->insertGetId([
            'stream_id' => $stationId,
            'name' => $name,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'id' => $id];
    }

    public function deletePlaylist($playlistId)
    {
        $this->db->table('radio_playlist_items')->where('playlist_id', $playlistId)->delete();
        $this->db->table('radio_playlists')->where('id', $playlistId)->delete();
        return ['success' => true];
    }

    public function addSongToPlaylist($playlistId, $title, $artist = '', $album = '', $duration = 0, $filePath = '')
    {
        $id = $this->db->table('radio_playlist_items')->insertGetId([
            'playlist_id' => $playlistId,
            'file_path' => $filePath,
            'title' => $title,
            'artist' => $artist,
            'album' => $album,
            'duration' => (int)$duration,
            'added_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'id' => $id];
    }

    public function removeSongFromPlaylist($itemId)
    {
        $item = $this->db->table('radio_playlist_items')->where('id', $itemId)->first();
        if ($item && $item->file_path && file_exists($item->file_path)) {
            @unlink($item->file_path);
        }
        $this->db->table('radio_playlist_items')->where('id', $itemId)->delete();
        return ['success' => true];
    }

    // ─── Phase 2: Request Management ───

    public function approveRequest($requestId)
    {
        $this->db->table('radio_requests')->where('id', $requestId)->update(['status' => 'approved']);
        return ['success' => true];
    }

    public function rejectRequest($requestId)
    {
        $this->db->table('radio_requests')->where('id', $requestId)->update(['status' => 'removed']);
        return ['success' => true];
    }

    // ─── Phase 2: Voice Tracking ───

    public function saveVoiceTrack($stationId, $name, $filePath = '', $duration = 0)
    {
        $id = $this->db->table('studio_voice_tracks')->insertGetId([
            'station_id' => $stationId,
            'name' => $name,
            'file_path' => $filePath,
            'duration' => (int)$duration,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'id' => $id];
    }

    public function getVoiceTracks($stationId)
    {
        return $this->db->table('studio_voice_tracks')
            ->where('station_id', $stationId)
            ->orderBy('created_at', 'desc')
            ->get() ?: [];
    }

    public function deleteVoiceTrack($trackId)
    {
        $track = $this->db->table('studio_voice_tracks')->where('id', $trackId)->first();
        if ($track && $track->file_path && file_exists($track->file_path)) {
            @unlink($track->file_path);
        }
        $this->db->table('studio_voice_tracks')->where('id', $trackId)->delete();
        return ['success' => true];
    }

    // ─── Album Art ───

    public function getAlbumArt($itemId)
    {
        $item = $this->db->table('radio_playlist_items')->where('id', $itemId)->first();
        if (!$item || !$item->file_path || !file_exists($item->file_path)) return null;

        $ext = strtolower(pathinfo($item->file_path, PATHINFO_EXTENSION));
        $artDir = dirname($item->file_path) . '/.albumart';
        $artFile = $artDir . '/' . md5($item->file_path) . '.jpg';

        // Return cached art if exists
        if (file_exists($artFile)) {
            return ['path' => $artFile, 'mime' => 'image/jpeg'];
        }

        // Extract embedded album art via ffmpeg
        if (shell_exec('which ffmpeg 2>/dev/null')) {
            @mkdir($artDir, 0755, true);
            $cmd = sprintf('ffmpeg -i %s -an -vcodec copy -f image2pipe - 2>/dev/null | head -c 524288',
                escapeshellarg($item->file_path));
            $imageData = shell_exec($cmd);
            if ($imageData && strlen($imageData) > 100) {
                file_put_contents($artFile, $imageData);
                return ['path' => $artFile, 'mime' => 'image/jpeg'];
            }
        }

        return null;
    }

    // ─── Voice FX ───

    public function getVoiceFxPresets()
    {
        return [
            ['id' => 'none', 'name' => 'None', 'params' => []],
            ['id' => 'echo', 'name' => 'Echo', 'params' => ['delay' => 0.3, 'decay' => 0.5]],
            ['id' => 'reverb', 'name' => 'Reverb', 'params' => ['room' => 0.8, 'damp' => 0.5]],
            ['id' => 'pitch_up', 'name' => 'Pitch Up', 'params' => ['pitch' => 1.2]],
            ['id' => 'pitch_down', 'name' => 'Pitch Down', 'params' => ['pitch' => 0.8]],
            ['id' => 'robot', 'name' => 'Robot', 'params' => ['freq' => 200, 'mix' => 0.7]],
            ['id' => 'chorus', 'name' => 'Chorus', 'params' => ['depth' => 0.3, 'rate' => 0.5]],
            ['id' => 'flanger', 'name' => 'Flanger', 'params' => ['depth' => 0.5, 'rate' => 0.3]],
            ['id' => 'distortion', 'name' => 'Distortion', 'params' => ['gain' => 10, 'mix' => 0.6]],
            ['id' => 'telephone', 'name' => 'Telephone', 'params' => ['low' => 300, 'high' => 3400]],
            ['id' => 'slowmo', 'name' => 'Slow Motion', 'params' => ['tempo' => 0.7]],
            ['id' => 'fastforward', 'name' => 'Fast Forward', 'params' => ['tempo' => 1.5]],
        ];
    }

    public function getFfmpegVoiceFxFilter($presetId)
    {
        $presets = [
            'echo' => 'aecho=0.8:0.5:300:0.3',
            'reverb' => 'aecho=0.8:0.7:500:0.4,aecho=0.6:0.5:1000:0.3',
            'pitch_up' => 'asetrate=48000*1.2,atempo=1/1.2',
            'pitch_down' => 'asetrate=48000*0.8,atempo=1/0.8',
            'robot' => 'afftfilt=real=\'hypot(re,im)*sin(0)\':imag=\'hypot(re,im)*cos(0)\'',
            'chorus' => 'chorus=0.7:0.9:55:0.4:0.25:2',
            'flanger' => 'flanger=0:2:0.71:0.5:66:0.5',
            'distortion' => 'acrusher=0:1:8:0:log',
            'telephone' => 'highpass=f=300,lowpass=f=3400',
            'slowmo' => 'atempo=0.7',
            'fastforward' => 'atempo=1.5',
        ];
        return $presets[$presetId] ?? null;
    }

    public function applyVoiceFx($inputPath, $presetId, $outputPath = null)
    {
        $filter = $this->getFfmpegVoiceFxFilter($presetId);
        if (!$filter) return ['success' => false, 'error' => 'Unknown preset'];

        if (!$outputPath) {
            $info = pathinfo($inputPath);
            $outputPath = $info['dirname'] . '/' . $info['filename'] . '_fx_' . $presetId . '.' . ($info['extension'] ?: 'mp3');
        }

        if (!shell_exec('which ffmpeg 2>/dev/null')) {
            return ['success' => false, 'error' => 'FFmpeg not available'];
        }

        $cmd = sprintf('ffmpeg -i %s -af %s -y %s 2>&1',
            escapeshellarg($inputPath),
            escapeshellarg($filter),
            escapeshellarg($outputPath));

        $output = shell_exec($cmd);
        $success = file_exists($outputPath) && filesize($outputPath) > 0;

        return [
            'success' => $success,
            'output_path' => $success ? $outputPath : null,
            'output' => $output,
        ];
    }

    // ─── Audio Preview Stream ───

    public function streamAudio($itemId)
    {
        $item = $this->db->table('radio_playlist_items')->where('id', $itemId)->first();
        if (!$item || !$item->file_path || !file_exists($item->file_path)) {
            return null;
        }

        $ext = strtolower(pathinfo($item->file_path, PATHINFO_EXTENSION));
        $mimeMap = [
            'mp3' => 'audio/mpeg',
            'aac' => 'audio/aac',
            'ogg' => 'audio/ogg',
            'flac' => 'audio/flac',
            'wav' => 'audio/wav',
            'm4a' => 'audio/mp4',
            'wma' => 'audio/x-ms-wma',
            'opus' => 'audio/ogg',
        ];

        return [
            'path' => $item->file_path,
            'mime' => $mimeMap[$ext] ?? 'application/octet-stream',
            'title' => $item->title,
            'artist' => $item->artist,
            'size' => filesize($item->file_path),
        ];
    }

    // ─── Phase 2: Desktop Connector Auth ───

    public function validateConnectorKey($apiKey)
    {
        $key = $this->db->table('api_keys')
            ->where('key_hash', hash('sha256', $apiKey))
            ->where('is_active', 1)
            ->first();
        if (!$key) return null;
        return $key;
    }

    public function createConnectorSession($userId, $deviceName)
    {
        $token = bin2hex(random_bytes(32));
        $this->db->table('studio_connector_sessions')->insertGetId([
            'user_id' => $userId,
            'device_name' => $deviceName,
            'token' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return ['token' => $token, 'expires_in' => 86400];
    }
}