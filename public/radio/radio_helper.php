<?php
require_once __DIR__ . '/../../core/ServerCreds.php';
require_once __DIR__ . '/../../core/helpers.php';
/**
 * Radio Helper — unified Icecast & SHOUTcast handler
 * Auto-detects server type from streaming_stations.server_type
 * Status is determined by a LIVE probe of the stream server (127.0.0.1),
 * not by the (often stale) DB status column.
 */

function radio_get_stream(int $id): ?stdClass
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=radiohosting;charset=utf8mb4',
            \db_user(), \db_pass(),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    // Handle composite station IDs (10000+offset)
    $realId = $id > 10000 ? ($id % 10000) : $id;
    $s = $pdo->prepare("SELECT *, name AS server_name FROM streaming_stations WHERE id = ?");
    $s->execute([$realId]);
    return $s->fetch(PDO::FETCH_OBJ) ?: null;
}

function radio_is_icecast(stdClass $stream): bool
{
    return strtolower($stream->server_type ?? '') === 'icecast';
}

function radio_is_shoutcast(stdClass $stream): bool
{
    $t = strtolower($stream->server_type ?? '');
    return $t === 'shoutcast' || $t === 'shoutcast1' || $t === 'shoutcast2';
}

function radio_server_type(stdClass $stream): string
{
    return radio_is_icecast($stream) ? 'icecast' : 'shoutcast';
}

/**
 * Resolve the real listen port for a station.
 * DB port first, then engine defaults (shared per-engine DNAS on this host),
 * then parse the station config file if present.
 */
function radio_stream_port(stdClass $stream): int
{
    $p = (int)($stream->port ?? 0);
    if ($p > 0) return $p;
    // Parse the station's own config if it exists
    $cfg = $stream->config_path ?? '';
    if ($cfg && is_file($cfg)) {
        $content = @file_get_contents($cfg);
        if ($content !== false) {
            if (preg_match('/<port>(\d+)<\/port>/i', $content, $m)) return (int)$m[1];
            if (preg_match('/^PortBase\s*=\s*(\d+)/mi', $content, $m)) return (int)$m[1];
            if (preg_match('/^portbase\s*=\s*(\d+)/mi', $content, $m)) return (int)$m[1];
        }
    }
    // Engine defaults on this server (shared DNAS instances)
    $type = strtolower($stream->server_type ?? 'icecast');
    if ($type === 'shoutcast1') return 11000;   // SHOUTcast v1 DNAS
    if ($type === 'shoutcast2' || $type === 'shoutcast') return 8000; // SHOUTcast v2 DNAS
    return 8002;                                 // global Icecast2
}

function radio_stream_url(stdClass $stream): string
{
    $host = primary_domain();
    $port = radio_stream_port($stream);
    $proto = !empty($stream->ssl_enabled) ? 'https' : 'http';
    if (radio_is_icecast($stream)) {
        $mount = $stream->mount_point ?? '/live';
        if (!str_starts_with($mount, '/')) $mount = "/{$mount}";
        return "{$proto}://{$host}:{$port}{$mount}";
    }
    return "{$proto}://{$host}:{$port}/;stream.nsv";
}

function radio_ssl_stream_url(int $streamId): string
{
    return site_base_url() . "/radio/stream-proxy.php?stream={$streamId}";
}

function radio_get_live_dj(int $streamId): ?stdClass
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4',\db_user(), \db_pass());
    }
    $s = $pdo->prepare("SELECT current_dj, current_song, current_artist, current_song_started FROM streaming_stations WHERE id=? AND current_dj IS NOT NULL AND current_dj != ''");
    $s->execute([$streamId]);
    return $s->fetch(PDO::FETCH_OBJ) ?: null;
}

function radio_stats_defaults(stdClass $stream): array
{
    return [
        'listeners' => (int)($stream->listener_count ?? 0),
        'peak' => (int)($stream->peak_listeners ?? $stream->listener_peak ?? 0),
        'bitrate' => (int)($stream->bitrate ?? 128),
        'song' => $stream->current_song ?? $stream->name ?? '',
        'artist' => $stream->current_artist ?? '',
        'status' => false,
        'uptime' => '',
        'live_dj' => $stream->current_dj ?? null,
    ];
}

function radio_http_get(string $url, int $timeout = 3): string
{
    return @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => $timeout, 'ignore_errors' => true]])) ?: '';
}

function radio_port_open(string $host, int $port, int $timeout = 2): bool
{
    if ($port <= 0) return false;
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$fp) return false;
    fclose($fp);
    return true;
}

/**
 * Live probe a SHOUTcast v2 DNAS via /stats?sid=1
 */
function radio_probe_shoutcast_v2(stdClass $stream, int $port): array
{
    $xml = radio_http_get("http://127.0.0.1:{$port}/stats?sid=1");
    if ($xml === '') return [];
    $stats = @simplexml_load_string($xml);
    if (!$stats) return [];
    return [
        'status' => ((int)($stats->STREAMSTATUS ?? 0)) === 1,
        'server_up' => true,
        'listeners' => (int)($stats->CURRENTLISTENERS ?? 0),
        'peak' => (int)($stats->PEAKLISTENERS ?? 0),
        'bitrate' => (int)($stats->BITRATE ?? 0),
        'song' => (string)($stats->SONGTITLE ?? ''),
        'uptime' => (string)($stats->SERVERUPTIME ?? ''),
    ];
}

function radio_stats_raw_get(int $port, string $path): string
{
    $fp = @fsockopen('127.0.0.1', $port, $errno, $errstr, 4);
    if (!$fp) return '';
    fwrite($fp, "GET {$path} HTTP/1.0\r\nUser-Agent: Mozilla/5.0 (PlanetHosts)\r\n\r\n");
    stream_set_timeout($fp, 4);
    $resp = '';
    while (!feof($fp)) {
        $d = fread($fp, 8192);
        if ($d === false || $d === '') break;
        $resp .= $d;
    }
    fclose($fp);
    return $resp;
}

/**
 * Live probe a SHOUTcast v1 DNAS via its index page + admin.cgi stats.
 * v1 serves non-standard headers — use a raw socket, not the http:// wrapper.
 */
function radio_probe_shoutcast_v1(stdClass $stream, int $port): array
{
    if (!radio_port_open('127.0.0.1', $port)) return [];
    $html = radio_stats_raw_get($port, '/index.html');
    if ($html === '') return [];
    $out = ['status' => false, 'server_up' => true, 'listeners' => 0, 'peak' => 0, 'bitrate' => (int)($stream->bitrate ?? 128), 'song' => '', 'uptime' => ''];
    // v1 index page shows "Stream is up at ... kb/s with <B>0 of 500 listeners"
    if (preg_match('/Stream is up/i', $html)) $out['status'] = true;
    if (preg_match('/with <B>(\d+) of/i', $html, $m)) $out['listeners'] = (int)$m[1];
    elseif (preg_match('/with (\d+) listener/i', $html, $m)) $out['listeners'] = (int)$m[1];
    if (preg_match('/Current Song:\s*<\/font><\/td><td><font class=default><b>\s*([^<]*)<\/b>/i', $html, $m)) $out['song'] = trim($m[1]);
    if (preg_match('/Listener Peak:\s*<\/font><\/td><td><font class=default><b>(\d+)/i', $html, $m)) $out['peak'] = (int)$m[1];
    if (preg_match('/Stream is up at (\d+) kbps/i', $html, $m)) $out['bitrate'] = (int)$m[1];
    // If v2-style /stats also works (hybrid), prefer it
    $v2 = radio_probe_shoutcast_v2($stream, $port);
    if (!empty($v2)) return $v2 + ['server_up' => true];
    return $out;
}

/**
 * Live probe Icecast via status-json.xsl, matching the station's mount.
 */
function radio_probe_icecast(stdClass $stream, int $port): array
{
    $json = radio_http_get("http://127.0.0.1:{$port}/status-json.xsl");
    if ($json === '') return [];
    $data = json_decode($json, true);
    if (!is_array($data)) return [];
    $mount = $stream->mount_point ?? '/live';
    if (!str_starts_with($mount, '/')) $mount = "/{$mount}";
    $src = $data['icestats']['source'] ?? [];
    $found = null;
    if (isset($src[0])) {
        foreach ($src as $s) {
            if (($s['mount'] ?? '') === $mount) { $found = $s; break; }
        }
    } elseif (isset($src['mount']) && $src['mount'] === $mount) {
        $found = $src;
    }
    if (!$found) return ['status' => false, 'server_up' => true, 'listeners' => 0, 'peak' => 0, 'bitrate' => (int)($stream->bitrate ?? 128), 'song' => '', 'uptime' => ''];
    return [
        'status' => true,
        'server_up' => true,
        'listeners' => (int)($found['listeners'] ?? 0),
        'peak' => (int)($found['listener_peak'] ?? 0),
        'bitrate' => (int)($found['bitrate'] ?? $stream->bitrate ?? 128),
        'song' => (string)($found['title'] ?? ''),
        'uptime' => (string)($found['stream_start_extended'] ?? $found['stream_start'] ?? ''),
    ];
}

function radio_fetch_stats(stdClass $stream): array
{
    $default = radio_stats_defaults($stream);
    // Suspended stations are never online
    if (($stream->status ?? '') === 'suspended') return $default;

    $port = radio_stream_port($stream);
    try {
        if (radio_is_icecast($stream)) {
            $probe = radio_probe_icecast($stream, $port);
        } else {
            $type = strtolower($stream->server_type ?? '');
            $probe = $type === 'shoutcast1'
                ? radio_probe_shoutcast_v1($stream, $port)
                : radio_probe_shoutcast_v2($stream, $port);
        }
    } catch (\Throwable $e) {
        $probe = [];
    }

    if (empty($probe)) {
        // Probe failed entirely — fall back to a TCP port check
        $default['status'] = radio_port_open('127.0.0.1', $port);
        return $default;
    }

    $default['status'] = (bool)($probe['status'] ?? false) || ($probe['server_up'] ?? false);
    foreach (['listeners', 'peak', 'bitrate', 'uptime'] as $k) {
        if (isset($probe[$k]) && $probe[$k] !== '' && $probe[$k] !== null) $default[$k] = $probe[$k];
    }
    if (!empty($probe['song'])) {
        $default['song'] = $probe['song'];
        $default['artist'] = $stream->current_artist ?? '';
        if ($default['artist'] === '' && strpos($probe['song'], ' - ') !== false) {
            $parts = explode(' - ', $probe['song'], 2);
            $default['artist'] = trim($parts[0]);
            $default['song'] = trim($parts[1]);
        }
    }
    return $default;
}

function radio_embed_html(string $jsCode, string $iframeCode, string $type = 'js'): string
{
    if ($type === 'iframe') return $iframeCode;
    return $jsCode;
}

function radio_host(): string
{
    return site_base_url();
}
