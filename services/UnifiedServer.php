<?php
/**
 * Planet Hosts Unified Streaming Server
 * Protocol Detection → Auth → Relay Engine
 *
 * SHOUTcast v1, v2, Icecast auto-detected from first bytes.
 * DJs authenticate with username:password against radio_djs table.
 * No source password is ever exposed to DJs.
 *
 * Usage: php UnifiedServer.php <stream_id>
 */

// ─── CONFIG ───
$streamId = isset($argv[1]) ? (int)$argv[1] : 4;
$logLevel = 3; // 0=errors, 1=connect/disconnect, 2=auth, 3=full debug

// ─── DB ───
$pdo = new PDO("mysql:host=localhost;dbname=radiohosting","radiouser","Skylinehosting171");
$st = $pdo->query("SELECT * FROM streaming_stations WHERE id=" . $streamId)->fetch(PDO::FETCH_OBJ);
if (!$st) { die("Stream $streamId not found\n"); }

$listenPorts = [9002, 9050];
// Studio audio relay port (browser sends audio chunks here)
$studioPort = 9006;
$listenPorts[] = $studioPort;
$shoutHost = 'localhost';
$shoutPort = $st->port; // SHOUTcast port (9000)
$srcPass = $st->plain_password; // Internal source password
$logDir = "/home/planethosts/radio/autodj";
@mkdir($logDir, 0755, true);
$pidFile = "$logDir/unified.pid";
$logFile = "$logDir/unified.log";
file_put_contents($pidFile, getmypid());
@unlink($logFile); // Fresh log

// ─── LOGGER ───
function logMsg($msg, $level = 2) {
    global $logFile;
    $l = "[" . date('Y-m-d H:i:s') . "] {$msg}\n";
    file_put_contents($logFile, $l, FILE_APPEND);
    if ($level <= 2) echo $l;
}
function logHex($label, $data) {
    logMsg("$label: " . bin2hex(substr($data, 0, 200)), 1);
    logMsg("TXT: " . substr(str_replace(["\r","\n"],["\\r","\\n"],$data), 0, 200), 1);
}

// ─── AUTHENTICATION ───
function authDj($user, $pass) {
    global $pdo, $streamId;
    $s = $pdo->prepare("SELECT * FROM radio_djs WHERE username=? AND stream_id=? AND status='active'");
    $s->execute([$user, $streamId]);
    $d = $s->fetch(PDO::FETCH_OBJ);
    return ($d && password_verify($pass, $d->password)) ? $d : null;
}
function authDjByPassword($pass) {
    global $pdo, $streamId;
    $s = $pdo->prepare("SELECT * FROM radio_djs WHERE stream_id=? AND status='active'");
    $s->execute([$streamId]);
    while ($d = $s->fetch(PDO::FETCH_OBJ)) {
        if (password_verify($pass, $d->password)) return $d;
    }
    return null;
}

// ─── RELAY ENGINE ───
function relayToShoutcast($client, $dj) {
    global $shoutHost, $shoutPort, $srcPass, $st, $streamId, $pdo;
    logMsg("[RELAY] Stopping AutoDJ...", 2);
    $pf = "/home/planethosts/radio/autodj/autodj.pid";
    if (file_exists($pf)) { $pid = (int)trim(file_get_contents($pf)); if ($pid > 0) exec("kill $pid 2>/dev/null"); usleep(500000); @unlink($pf); }
    exec("pkill -f \"runner_{$streamId}\" 2>/dev/null");
    try { $pdo->exec("UPDATE streaming_stations SET autodj_enabled=0 WHERE id=" . $streamId); } catch (\Exception $e) {}
    sleep(2);

    logMsg("[RELAY] Connecting to SHOUTcast on {$shoutHost}:{$shoutPort}...", 2);
    $shout = @fsockopen($shoutHost, $shoutPort, $e, $s, 5);
    if (!$shout) { logMsg("[RELAY] FAILED: {$s}", 1); fwrite($client, "HTTP/1.0 502\r\n\r\nBackend offline"); return false; }
    stream_set_timeout($shout, 5);

    fwrite($shout, $srcPass . "\r\n");
    $resp = fread($shout, 1024);
    logMsg("[RELAY] SHOUTcast auth: " . trim(str_replace(["\r","\n"],["",""],$resp)), 2);
    if (strpos($resp, 'OK2') === false && strpos($resp, 'OK') === false) {
        logMsg("[RELAY] SHOUTcast rejected: " . trim($resp), 1);
        fwrite($client, "HTTP/1.0 502\r\n\r\n" . trim($resp));
        fclose($shout);
        return false;
    }

    $name = $dj->name ?: $dj->username;
    fwrite($shout, "icy-name:{$name}\r\nicy-br:{$st->bitrate}\r\nicy-pub:1\r\nContent-Type:audio/mpeg\r\n\r\n");
    fwrite($client, "OK2\r\nicy-caps:11\r\n\r\n");
    logMsg("[RELAY] Streaming started for DJ {$dj->username}", 1);

    $buf = 65536; $total = 0;
    while (!feof($client)) {
        $d = @fread($client, $buf);
        if ($d === false || $d === '') break;
        fwrite($shout, $d);
        $total += strlen($d);
    }
    fclose($shout);
    logMsg("[RELAY] DJ {$dj->username} disconnected (" . round($total/1024) . " KB relayed)", 1);
    return true;
}

// ─── PROTOCOL HANDLERS ───

function handleShoutcastV1($client, $firstLine) {
    logMsg("[SCv1] Source connected", 2);
    logHex("[SCv1] First line", $firstLine);

    // Read remaining headers until blank line, then get password
    $headers = '';
    $passwordLine = '';
    $gotBlank = false;
    while (($h = fgets($client)) !== false) {
        $h = trim($h);
        if ($h === '') { $gotBlank = true; continue; }
        if ($gotBlank) { $passwordLine = $h; break; }
        $headers .= $h . "\n";
    }

    if (!$passwordLine) { logMsg("[SCv1] No password received", 1); return; }
    $dj = null;
    if (strpos($passwordLine, ':') !== false) {
        list($u, $p) = explode(':', $passwordLine, 2);
        $dj = authDj($u, $p);
        if ($dj) logMsg("[SCv1] DJ auth via username:password: {$u}", 1);
    }
    if (!$dj) {
        $dj = authDjByPassword($passwordLine);
        if ($dj) logMsg("[SCv1] DJ auth via password match: {$dj->username}", 1);
    }
    if (!$dj) { logMsg("[SCv1] Auth FAILED", 1); fwrite($client, "HTTP/1.0 401\r\n\r\nAuth failed"); return; }
    relayToShoutcast($client, $dj);
}

function handleShoutcastV2($client, $firstLine) {
    logMsg("[SCv2] Source: " . trim($firstLine), 2);

    // Read ICY headers + password after blank line
    $headers = '';
    $passwordLine = '';
    $authHeader = '';
    $gotBlank = false;
    while (($h = fgets($client)) !== false) {
        $h = trim($h);
        if ($h === '') { $gotBlank = true; continue; }
        if ($gotBlank) { $passwordLine = $h; break; }
        $headers .= $h . "\n";
        if (preg_match('/^Authorization:\s*Basic\s+(.+)$/i', $h, $m)) {
            $authHeader = base64_decode($m[1]);
        }
    }
    logHex("[SCv2] Headers", $headers);

    $dj = null;
    if ($authHeader) {
        list($u, $p) = explode(':', $authHeader, 2);
        $dj = authDj($u, $p);
        if ($dj) logMsg("[SCv2] DJ auth via Authorization header: {$u}", 1);
    }
    if (!$dj && $passwordLine) {
        if (strpos($passwordLine, ':') !== false) {
            list($u, $p) = explode(':', $passwordLine, 2);
            $dj = authDj($u, $p);
            if ($dj) logMsg("[SCv2] DJ auth via password: {$u}", 1);
        }
    }
    if (!$dj && $passwordLine) {
        $dj = authDjByPassword($passwordLine);
        if ($dj) logMsg("[SCv2] DJ auth via password match: {$dj->username}", 1);
    }
    if (!$dj) { logMsg("[SCv2] Auth FAILED", 1); fwrite($client, "HTTP/1.0 401\r\n\r\nAuth failed"); return; }
    relayToShoutcast($client, $dj);
}

function handleIcecast($client, $firstLine) {
    logMsg("[Icecast] Source: " . trim($firstLine), 2);

    // Parse headers for Authorization
    $authHeader = '';
    $passwordLine = '';
    while (($h = fgets($client)) !== false) {
        $h = trim($h);
        if ($h === '') break;
        if (preg_match('/^authorization:\s*basic\s+(.+)$/i', $h, $m)) {
            $authHeader = base64_decode($m[1]);
        }
        // Icecast also sends password after headers for some clients
        $passwordLine = $h;
    }

    $dj = null;
    if ($authHeader) {
        list($u, $p) = explode(':', $authHeader, 2);
        $dj = authDj($u, $p);
        if ($dj) logMsg("[Icecast] DJ auth via Authorization: {$u}", 1);
    }
    if (!$dj) {
        $dj = authDjByPassword($passwordLine);
        if ($dj) logMsg("[Icecast] DJ auth via password match: {$dj->username}", 1);
    }
    if (!$dj) { logMsg("[Icecast] Auth FAILED", 1); fwrite($client, "HTTP/1.0 401\r\n\r\nAuth failed"); return; }
    relayToShoutcast($client, $dj);
}

function handleHttpAdmin($client, $firstLine) {
    logMsg("[HTTP] Admin request: " . trim($firstLine), 2);
    $path = trim(explode(' ', $firstLine)[1] ?? '/');
    global $shoutPort;
    fwrite($client, "HTTP/1.0 302 Found\r\nLocation: http://planet-hosts.com:{$shoutPort}{$path}\r\n\r\n");
}

/**
 * Studio Audio Relay — browser sends raw audio chunks
 * Used by Planet Hosts Studio web broadcaster
 * Receives audio data via HTTP POST and relays to SHOUTcast
 */
function handleStudioAudio($client, $firstLine) {
    global $shoutHost, $shoutPort, $srcPass, $st, $streamId, $pdo;
    
    // Parse POST request
    $parts = explode("\r\n\r\n", $firstLine, 2);
    $body = $parts[1] ?? '';
    
    // Read remaining body from headers
    $contentLength = 0;
    foreach (explode("\r\n", $firstLine) as $line) {
        if (stripos($line, 'content-length:') !== false) {
            $contentLength = (int)trim(substr($line, 15));
        }
    }
    
    // Read full body
    while (strlen($body) < $contentLength && !feof($client)) {
        $body .= fread($client, 65536);
    }
    
    if (strlen($body) < 100) { // Too small, probably not real audio
        fwrite($client, "HTTP/1.0 400\r\n\r\nToo small");
        return;
    }
    
    // Stop AutoDJ using existing method
    $pf = "/home/planethosts/radio/autodj/autodj.pid";
    if (file_exists($pf)) { $pid = (int)trim(file_get_contents($pf)); if ($pid > 0) exec("kill $pid 2>/dev/null"); usleep(500000); @unlink($pf); }
    exec("pkill -f \"runner_{$streamId}\" 2>/dev/null");
    
    // Connect to SHOUTcast using existing source password
    $shout = @fsockopen($shoutHost, $shoutPort, $e, $s, 5);
    if (!$shout) { fwrite($client, "HTTP/1.0 502\r\n\r\nBackend offline"); return; }
    stream_set_timeout($shout, 0);
    fwrite($shout, $srcPass . "\r\n");
    $resp = fread($shout, 1024);
    if (strpos($resp, 'OK2') === false && strpos($resp, 'OK') === false) {
        fwrite($client, "HTTP/1.0 502\r\n\r\n" . trim($resp)); fclose($shout); return;
    }
    fwrite($shout, "icy-name:Studio\r\nicy-br:128\r\nicy-pub:1\r\nContent-Type:audio/mpeg\r\n\r\n");
    
    // Forward audio data
    fwrite($shout, $body);
    
    // Keep receiving and forwarding until client disconnects
    while (!feof($client)) {
        $d = fread($client, 65536);
        if ($d === false || $d === '') break;
        fwrite($shout, $d);
    }
    fclose($shout);
    logMsg("[Studio] Audio session ended", 1);
    
    // Don't send HTTP response — keep-alive for streaming
}

// ─── PROTOCOL DETECTOR ───
function detectProtocol($firstBytes) {
    if (stripos($firstBytes, 'SOURCE') === 0) {
        // Could be SCv1, SCv2, or Icecast — check more
        return 'SOURCE';
    }
    if (stripos($firstBytes, 'PUT') === 0) {
        return 'ICECAST_PUT';
    }
    if (stripos($firstBytes, 'GET') === 0 || stripos($firstBytes, 'HEAD') === 0) {
        return 'HTTP_ADMIN';
    }
    if (stripos($firstBytes, 'POST') === 0) {
        return 'STUDIO_AUDIO';
    }
    if (stripos($firstBytes, 'AUTH') === 0) {
        return 'SC_V2';
    }
    // No HTTP method = likely SCv1 (password first)
    return 'SC_V1';
}

// ─── MAIN LISTENER ───
logMsg("═══════════════════════════════════════════", 2);
logMsg("Planet Hosts Unified Server v2 starting", 2);
logMsg("Stream {$streamId} | SHOUTcast on port {$shoutPort}", 2);
logMsg("Listening ports: " . implode(', ', $listenPorts), 2);

$servers = [];
foreach ($listenPorts as $port) {
    $s = @stream_socket_server("tcp://0.0.0.0:{$port}", $e, $s, STREAM_SERVER_LISTEN | STREAM_SERVER_BIND);
    if ($s) { $servers[] = $s; logMsg("Listening on port {$port}", 2); }
    else { logMsg("Failed to bind port {$port}: {$s}", 0); }
}
if (empty($servers)) die("No ports could be bound\n");

logMsg("Ready for connections", 2);

while (true) {
    $read = $servers;
    $w = null; $e = null;
    $sel = @stream_select($read, $w, $e, 5);
    if ($sel === false || empty($read)) { usleep(100000); continue; }

    foreach ($read as $r) {
        $client = @stream_socket_accept($r, 0);
        if (!$client) continue;
        stream_set_timeout($client, 10);

        // Get client IP
        $addr = '';
        @sscanf(stream_socket_get_name($client, true), '%[^:]:%d', $addr, $port);

        logMsg("════════════════════════════════", 2);
        logMsg("[CONNECT] {$addr}:{$port}", 1);

        // Now read client's first request
        $firstLine = fgets($client);
        if (!$firstLine || trim($firstLine) === '') {
            logMsg("[DISC] Empty connection from {$addr}", 1);
            fclose($client);
            continue;
        }

        logHex("[RAW]", $firstLine);

        // Only send banner for source protocols, not HTTP/Studio
        if (stripos($firstLine, 'SOURCE') === 0 || stripos($firstLine, 'GET') !== 0 && stripos($firstLine, 'HEAD') !== 0 && stripos($firstLine, 'POST') !== 0) {
            fwrite($client, "OK2\r\nicy-caps:11\r\n\r\n");
            logMsg("[BANNER] Sent OK2 banner", 2);
        }

        $proto = detectProtocol($firstLine);

        switch ($proto) {
            case 'SC_V1':
                logMsg("[DETECT] SHOUTcast v1 (password-first)", 2);
                handleShoutcastV1($client, $firstLine);
                break;
            case 'SOURCE':
                // Look at more data to distinguish SCv2 from Icecast
                $peek = $firstLine;
                $hasAuth = false;
                $hasIcy = false;
                // Read headers to check
                $tempSock = $client;
                // Actually we already have $firstLine, let's just check it
                if (stripos($firstLine, 'authorization') !== false || stripos($firstLine, 'Basic') !== false) {
                    logMsg("[DETECT] Icecast (Authorization in first line)", 2);
                    handleIcecast($client, $firstLine);
                } else {
                    logMsg("[DETECT] SHOUTcast v2 (SOURCE with headers)", 2);
                    handleShoutcastV2($client, $firstLine);
                }
                break;
            case 'ICECAST_PUT':
                logMsg("[DETECT] Icecast (PUT method)", 2);
                handleIcecast($client, $firstLine);
                break;
            case 'HTTP_ADMIN':
                handleHttpAdmin($client, $firstLine);
                break;
            case 'STUDIO_AUDIO':
                logMsg("[DETECT] Studio audio relay", 2);
                handleStudioAudio($client, $firstLine);
                break;
            default:
                logMsg("[DETECT] Unknown protocol, trying SCv1", 1);
                handleShoutcastV1($client, $firstLine);
        }

        if (is_resource($client)) fclose($client);
    }
}