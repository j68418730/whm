<?php
/**
 * Radio Helper — unified Icecast & SHOUTcast handler
 * Auto-detects server type from streaming_stations.server_type
 */

function radio_get_stream(int $id): ?stdClass
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=radiohosting;charset=utf8mb4',
            'radiouser', 'Skylinehosting171',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    $s = $pdo->prepare("SELECT *, name AS server_name FROM streaming_stations WHERE id = ?");
    $s->execute([$id]);
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

function radio_stream_url(stdClass $stream): string
{
    $host = 'planet-hosts.com';
    $port = (int)($stream->port ?? 8000);
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
    return "https://planet-hosts.com:2083/radio/stream-proxy.php?stream={$streamId}";
}

function radio_fetch_stats(stdClass $stream): array
{
    $default = [
        'listeners' => (int)($stream->listener_count ?? 0),
        'peak' => (int)($stream->listener_count ?? 0),
        'bitrate' => (int)($stream->bitrate ?? 128),
        'song' => $stream->current_song ?? $stream->name ?? '',
        'artist' => '',
        'status' => $stream->status === 'running',
        'uptime' => '',
    ];
    if ($stream->status !== 'running') return $default;

    try {
        if (radio_is_icecast($stream)) {
            return radio_fetch_icecast_stats($stream, $default);
        }
        return radio_fetch_shoutcast_stats($stream, $default);
    } catch (\Exception $e) {
        return $default;
    }
}

function radio_fetch_icecast_stats(stdClass $stream, array $d): array
{
    $port = (int)($stream->port ?? 8000);
    $mount = $stream->mount_point ?? '/live';
    if (!str_starts_with($mount, '/')) $mount = "/{$mount}";
    $url = "http://planet-hosts.com:{$port}/status-json.xsl";
    $json = @file_get_contents($url, false, stream_context_create(['http'=>['timeout'=>3]]));
    if (!$json) return $d;
    $data = json_decode($json, true);
    if (!$data) return $d;
    $source = $data['icestats']['source'] ?? [];
    if (isset($source[0])) {
        foreach ($source as $src) {
            if (($src['mount'] ?? '') === $mount) { $source = $src; break; }
        }
        if (isset($source[0])) $source = $source[0];
    }
    $d['listeners'] = (int)($source['listeners'] ?? $d['listeners']);
    $d['peak'] = (int)($source['listener_peak'] ?? $d['peak']);
    $d['bitrate'] = (int)($source['bitrate'] ?? $d['bitrate']);
    $d['song'] = $source['title'] ?? $d['song'];
    $d['artist'] = $source['artist'] ?? $d['artist'];
    $d['status'] = true;
    $d['uptime'] = $source['stream_start'] ?? $d['uptime'];
    return $d;
}

function radio_fetch_shoutcast_stats(stdClass $stream, array $d): array
{
    $port = (int)($stream->port ?? 8000);
    $url = "http://planet-hosts.com:{$port}/stats?sid=1";
    $xml = @file_get_contents($url, false, stream_context_create(['http'=>['timeout'=>3]]));
    if (!$xml) return $d;
    $stats = simplexml_load_string($xml);
    if (!$stats) return $d;
    $d['listeners'] = (int)($stats->CURRENTLISTENERS ?? $d['listeners']);
    $d['peak'] = (int)($stats->PEAKLISTENERS ?? $d['peak']);
    $d['bitrate'] = (int)($stats->BITRATE ?? $d['bitrate']);
    $d['song'] = (string)$stats->SONGTITLE ?: $d['song'];
    $d['status'] = true;
    $d['uptime'] = (string)($stats->SERVERUPTIME ?? $d['uptime']);
    if ($d['song'] && !$d['artist']) {
        $parts = explode(' - ', $d['song'], 2);
        if (count($parts) === 2) { $d['artist'] = trim($parts[0]); $d['song'] = trim($parts[1]); }
    }
    return $d;
}

function radio_embed_html(string $jsCode, string $iframeCode, string $type = 'js'): string
{
    if ($type === 'iframe') return $iframeCode;
    return $jsCode;
}

function radio_host(): string
{
    return 'https://planet-hosts.com/radio-proxy.php';
}
