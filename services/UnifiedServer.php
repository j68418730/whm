<?php
/**
 * Unified Stream Server — Listens on port 9000
 * SOURCE connections → DJ auth (username:password) → relay to SHOUTcast on port 9003
 * GET/HEAD connections → redirect listeners to port 9003
 * 
 * Like SonicPanel / Centova Cast — DJs authenticate with their own credentials
 * No source password is ever shared with DJs.
 */
$streamId = isset($argv[1]) ? (int)$argv[1] : 4;
$pdo = new PDO("mysql:host=localhost;dbname=radiohosting","radiouser","Skylinehosting171");
$st = $pdo->query("SELECT * FROM streaming_stations WHERE id=" . $streamId)->fetch(PDO::FETCH_OBJ);
if (!$st) { echo "Stream not found\n"; exit; }

$listenPort = $st->port; // 9000
$shoutPort = $st->port + 3; // 9003
$host = 'planet-hosts.com';

$logDir = "/home/" . $st->user_id . "/radio/autodj";
@mkdir($logDir, 0755, true);
$logFile = $logDir . "/unified.log";
$pidFile = $logDir . "/unified.pid";
file_put_contents($pidFile, getmypid());

$server = @stream_socket_server("tcp://0.0.0.0:{$listenPort}", $errno, $errstr, STREAM_SERVER_LISTEN | STREAM_SERVER_BIND);
if (!$server) { logMsg("Failed to bind port {$listenPort}: {$errstr}"); exit; }
logMsg("Unified Server on {$listenPort} — SHOUTcast on {$shoutPort}");

while (true) {
    $client = @stream_socket_accept($server, 5);
    if (!$client) { usleep(100000); continue; }
    stream_set_timeout($client, 10);
    $peek = fgets($client);
    if (!$peek) { if (is_resource($client)) fclose($client); continue; }

    if (stripos($peek, 'SOURCE') === 0) {
        handleSource($client, $peek, $shoutPort, $st, $streamId, $pdo);
    } else {
        // Redirect listeners to port 9003
        $path = trim(explode(' ', $peek)[1] ?? '/');
        fwrite($client, "HTTP/1.0 302 Found\r\nLocation: http://{$host}:{$shoutPort}{$path}\r\n\r\n");
    }
    if (is_resource($client)) fclose($client);
}

function handleSource($client, $firstLine, $shoutPort, $st, $streamId, $pdo) {
    logMsg("Source: " . trim($firstLine));
    $authHeader = ''; $passwordLine = ''; $gotBlank = false;
    while (($h = fgets($client)) !== false) {
        $h = trim($h);
        if ($h === '') { $gotBlank = true; continue; }
        if ($gotBlank) { $passwordLine = $h; break; }
        if (preg_match('/^Authorization:\s*Basic\s+(.+)$/i', $h, $m)) {
            $authHeader = base64_decode($m[1]);
        }
    }
    $dj = null;
    if ($authHeader) { list($u,$p)=explode(':',$authHeader,2); $dj = authDj($u,$p,$streamId,$pdo); }
    if (!$dj && $passwordLine) {
        if (strpos($passwordLine,':')!==false) { list($u,$p)=explode(':',$passwordLine,2); $dj = authDj($u,$p,$streamId,$pdo); }
        if (!$dj) $dj = authDjByPassword($passwordLine,$streamId,$pdo);
    }
    if (!$dj) { logMsg("Auth failed"); fwrite($client,"HTTP/1.0 401\r\n\r\nBad credentials"); return; }
    logMsg("DJ: {$dj->username}");

    // Kill AutoDJ
    $pf = "/home/planethosts/radio/autodj/autodj.pid";
    if (file_exists($pf)) { $pid=(int)trim(file_get_contents($pf)); if($pid>0) exec("kill {$pid} 2>/dev/null"); usleep(500000); @unlink($pf); }
    exec("pkill -f \"runner_{$streamId}\" 2>/dev/null");
    try { $pdo->exec("UPDATE streaming_stations SET autodj_enabled=0 WHERE id=".$streamId); } catch(\Exception$e){}
    sleep(2);

    $shout = @fsockopen('localhost',$shoutPort,$e,$s,5);
    if(!$shout){ logMsg("SHOUTcast down: {$s}"); fwrite($client,"HTTP/1.0 502\r\n\r\nStream offline"); return; }
    stream_set_timeout($shout,5);
    fwrite($shout, $st->plain_password."\r\n");
    $resp = fread($shout,1024);
    if(strpos($resp,'OK2')===false&&strpos($resp,'OK')===false){
        logMsg("SHOUTcast rejected: ".trim($resp)); fwrite($client,"HTTP/1.0 502\r\n\r\n".trim($resp)); fclose($shout); return;
    }
    fwrite($shout, "icy-name:{$dj->name}\r\nicy-br:{$st->bitrate}\r\nicy-pub:1\r\nContent-Type:audio/mpeg\r\n\r\n");
    fwrite($client, "OK2\r\nicy-caps:11\r\n\r\n");
    logMsg("Streaming from DJ {$dj->username}");
    $buf=65536;
    while(!feof($client)){ $d=fread($client,$buf); if($d===false||$d==='')break; fwrite($shout,$d); }
    fclose($shout);
    logMsg("DJ {$dj->username} disconnected");
}

function authDj($u,$p,$sid,$pdo){ $s=$pdo->prepare("SELECT*FROM radio_djs WHERE username=? AND stream_id=? AND status='active'"); $s->execute([$u,$sid]); $d=$s->fetch(PDO::FETCH_OBJ); return ($d&&password_verify($p,$d->password))?$d:null; }
function authDjByPassword($p,$sid,$pdo){ $s=$pdo->prepare("SELECT*FROM radio_djs WHERE stream_id=? AND status='active'"); $s->execute([$sid]); while($d=$s->fetch(PDO::FETCH_OBJ)){if(password_verify($p,$d->password))return $d;}return null; }
function logMsg($m){ $l="[".date('Y-m-d H:i:s')."] {$m}\n"; global $logFile; file_put_contents($logFile,$l,FILE_APPEND); echo $l; }