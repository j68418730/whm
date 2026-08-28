<?php
session_start();
$action = $_POST['action'] ?? $_GET['action'] ?? 'login';
$error = '';
$success = '';
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Allow switching stream via URL param
if (isset($_GET['stream_id']) && isset($_SESSION['dj_user'])) {
    $_SESSION['dj_user']['stream_id'] = (int)$_GET['stream_id'];
    // Persist in a cookie so it survives across pages
    setcookie('dj_stream_id', (int)$_GET['stream_id'], time() + 86400 * 30, '/');
} elseif (isset($_COOKIE['dj_stream_id']) && isset($_SESSION['dj_user'])) {
    $_SESSION['dj_user']['stream_id'] = (int)$_COOKIE['dj_stream_id'];
}

// ─── raw HTTP fetch over a TCP socket ───
// Shoutcast v1 answers with HTTP/0.9, which PHP's http wrapper silently rejects,
// so we read bytes directly. Works for Icecast / Shoutcast v1 / v2.
function raw_http($port, $path, $extraHeader = '', $maxBytes = 98304) {
    $fp = @fsockopen('127.0.0.1', (int)$port, $errno, $errstr, 3);
    if (!$fp) return null;
    stream_set_timeout($fp, 3);
    @fwrite($fp, "GET {$path} HTTP/1.0\r\nHost: 127.0.0.1\r\nUser-Agent: PlanetHosts-DJ/1.0\r\n{$extraHeader}\r\n");
    $buf = '';
    while (strlen($buf) < $maxBytes) {
        $c = @fread($fp, 8192);
        if ($c === '' || $c === false) break;
        $buf .= $c;
    }
    @fclose($fp);
    return $buf;
}

// ─── Read the actual playing track from the stream's ICY metadata ───
// Works for Shoutcast (v1/v2) and Icecast; SC1 7.html has NO title, so the
// stream metadata is the authoritative live track source. Returns '' when none.
function fetch_stream_title($port, $mount = '') {
    if ($port <= 0) return '';
    if ($mount !== '' && !str_starts_with($mount, '/')) $mount = '/' . $mount;
    $paths = $mount !== '' ? [$mount, '/', '/;'] : ['/', '/stream', '/;'];
    foreach ($paths as $m) {
        $raw = raw_http($port, $m, "Icy-MetaData: 1\r\n", 65536 + 4081);
        if (!$raw) continue;
        $hdrEnd = strpos($raw, "\r\n\r\n");
        if ($hdrEnd === false) continue;
        $headers = substr($raw, 0, $hdrEnd);
        $metaInterval = 0;
        foreach (explode("\r\n", $headers) as $h) {
            if (stripos($h, 'icy-metaint:') === 0) { $metaInterval = (int)trim(substr($h, 12)); break; }
        }
        if ($metaInterval <= 0) continue;
        $at = $hdrEnd + 4 + $metaInterval;
        if (strlen($raw) <= $at) continue;
        $lb = ord($raw[$at]);
        if ($lb <= 0) continue;
        $md = substr($raw, $at + 1, $lb * 16);
        if (preg_match("/StreamTitle='(.*?)';/is", $md, $mm)) {
            $t = trim(html_entity_decode($mm[1], ENT_QUOTES));
            if ($t !== '' && $t !== '-') return $t;
        }
    }
    return '';
}

// ─── STREAM PROBE: ask the real Icecast/Shoutcast server if a source is ON AIR ───
function probe_stream($pdo, $station) {
    $engine = strtolower($station->engine ?? $station->server_type ?? 'icecast');
    $port   = (int)($station->port ?? 0);
    $out    = ['engine' => $engine, 'connected' => false, 'live' => false, 'source' => null,
               'song' => '', 'artist' => '', 'listeners' => 0];
    if ($port <= 0) return $out;
    $ctx = stream_context_create(['http' => ['timeout' => 3]]);
    if ($engine === 'icecast') {
        $json = @file_get_contents("http://127.0.0.1:{$port}/status-json.xsl", false, $ctx);
        if ($json !== false) {
            $d = json_decode($json, true);
            $out['connected'] = is_array($d);
            $mount = ltrim((string)($station->mount_point ?? ''), '/');
            $srcs  = $d['icestats']['source'] ?? [];
            if (isset($srcs['mount'])) $srcs = [$srcs];   // single source object
            foreach ($srcs as $s) {
                $m = ltrim((string)($s['mount'] ?? ''), '/');
                if ($mount !== '' && $m !== $mount && !str_ends_with($m, $mount)) continue;
                $out['live']      = true;
                $out['source']    = 'encoder';
                $out['listeners'] = (int)($s['listeners'] ?? 0);
                // Compose a clean song title from the source metadata (title / artist)
                $tTitle  = trim((string)($s['title'] ?? ''));
                $tArtist = trim((string)($s['artist'] ?? ''));
                if ($tTitle === '-' || $tTitle === '') $tTitle = '';
                if ($tArtist === '-' || $tArtist === '') $tArtist = '';
                $out['artist'] = $tArtist;
                if ($tTitle && $tArtist)      $out['song'] = $tArtist . ' - ' . $tTitle;
                elseif ($tTitle)              $out['song'] = $tTitle;
                elseif ($tArtist)             $out['song'] = $tArtist;
                // Fallback: pull the exact track from ICY metadata when the server title is empty
                if ($out['song'] === '') { $out['song'] = fetch_stream_title($port, $station->mount_point ?? ''); }
                break;
            }
        }
    } elseif (in_array($engine, ['shoutcast', 'shoutcast1', 'shoutcast2'])) {
        // SC1: 7.html is often disabled and has no title anyway → probe the root mount.
        // A connected source exposes an "icy-metaint:" header on a 200 response.
        $raw = raw_http($port, '/', "Icy-MetaData: 1\r\n", 65536 + 4081);
        $hdrEnd = $raw !== null ? strpos($raw, "\r\n\r\n") : false;
        $hasMeta = false;
        if ($hdrEnd !== false) {
            foreach (explode("\r\n", substr($raw, 0, $hdrEnd)) as $h) {
                if (stripos($h, 'icy-metaint:') === 0) { $hasMeta = true; break; }
            }
        }
        if ($raw !== null && $hasMeta) {
            $out['connected'] = true;
            $out['live']      = true;
            $out['source']    = 'encoder';
            $out['song']      = fetch_stream_title($port, $station->mount_point ?? '');
        } else {
            // No source → try SC2 extended 7.html for at least a listener count
            $b = raw_http($port, '/7.html', '', 8192);
            if ($b !== null) {
                $hb = stripos($b, '<body>');
                $body = trim($hb !== false ? substr($b, $hb + 6) : $b);
                $parts = explode(',', (string)$body);
                if (count($parts) >= 7) $out['listeners'] = (int)trim($parts[0] ?? 0);
            }
        }
    }
    return $out;
}

// ─── AUTO-LOGIN for account owners ───
if (!isset($_SESSION['dj_user']) && isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    if (!is_object($user)) $user = (object)$user;
    // Check if user has a radio stream
    $hostingId = $user->id ?? 0;
    $hStmt = $pdo->prepare("SELECT id FROM hosting_users WHERE id = ? OR email = ? OR username = ? LIMIT 1");
    $hStmt->execute([$hostingId, $user->email ?? '', $user->name ?? '']);
    $hosting = $hStmt->fetch(PDO::FETCH_OBJ);
    if ($hosting) {
        // Auto-create stream if icecast package and no stream exists
        $streamStmt = $pdo->prepare("SELECT id, port, status FROM radio_streams WHERE user_id = ? LIMIT 1");
        $streamStmt->execute([$hosting->id]);
        $stream = $streamStmt->fetch(PDO::FETCH_OBJ);
        if (!$stream) {
            $pkgStmt = $pdo->prepare("SELECT p.* FROM hosting_packages p JOIN hosting_users h ON h.package_id = p.id WHERE h.id = ?");
            $pkgStmt->execute([$hosting->id]);
            $pkg = $pkgStmt->fetch(PDO::FETCH_OBJ);
            if ($pkg && !empty($pkg->icecast_enabled)) {
                $pw = substr(md5(time().rand()), 0, 8);
                $pdo->prepare("INSERT INTO radio_streams (user_id, server_type, port, password, config_path, status) VALUES (?, 'icecast', 8000, ?, '/etc/icecast/radiohosting', 'stopped')")->execute([$hosting->id, $pw]);
                $streamStmt->execute([$hosting->id]);
                $stream = $streamStmt->fetch(PDO::FETCH_OBJ);
            }
        }
        if ($stream) {
            // Auto-login as the stream owner
            $_SESSION['dj_user'] = [
                'id' => 0, 'stream_id' => $stream->id, 'username' => $user->name ?? 'Owner',
                'name' => $user->name ?? 'Station Owner', 'stream_name' => 'My Stream',
                'port' => $stream->port, 'stream_status' => $stream->status,
                'is_owner' => true,
            ];
            $action = $_GET['action'] ?? 'dashboard';
            if ($action === 'login') $action = 'dashboard';
        }
    }
}

// ─── LIVE STATUS (AJAX) ───
if ($action === 'status' && isset($_SESSION['dj_user'])) {
    header('Content-Type: application/json');
    $streamId = (int)($_GET['stream_id'] ?? $_SESSION['dj_user']['stream_id'] ?? 0);
    $sstmt = $pdo->prepare("SELECT * FROM streaming_stations WHERE id = ?");
    $sstmt->execute([$streamId]);
    $station = $sstmt->fetch(PDO::FETCH_OBJ);
    if (!$station) { echo json_encode(['ok' => false, 'error' => 'station-not-found']); exit; }
    $probe = probe_stream($pdo, $station);
    $autoDj = (int)($station->autodj_enabled ?? 0);
    $curDj  = $station->current_dj ?? null;
    $state  = $autoDj ? 'autodj' : ($probe['live'] ? 'live' : 'offline');

    // Auto-record the connected DJ (from the session) when a live source is detected,
    // and clear the DJ when AutoDJ is back on — keeps the identity accurate live.
    try {
        if ($state === 'live' && (trim((string)$curDj) === '')) {
            $djName = trim((string)($_SESSION['dj_user']['username'] ?? ''));
            $pdo->prepare("UPDATE streaming_stations SET current_dj = ? WHERE id = ? AND (current_dj IS NULL OR current_dj = '')")->execute([$djName ?: 'Live DJ', $streamId]);
            $curDj = $djName ?: 'Live DJ';
        } elseif ($state === 'autodj' && trim((string)$curDj) !== '') {
            $pdo->exec("UPDATE streaming_stations SET current_dj = NULL WHERE id = " . $streamId);
            $curDj = null;
        }
    } catch (\Exception $e) {}

    echo json_encode([
        'ok' => true, 'stream_id' => $streamId,
        'state' => $state, 'live' => $probe['live'], 'source' => $probe['source'],
        'autoDJ' => $autoDj, 'current_dj' => $curDj,
        'song' => $probe['song'] ?: ($station->current_song ?? ''),
        'listeners' => $probe['listeners'] ?: (int)($station->listener_count ?? 0),
        'status' => $station->status ?? 'stopped', 'engine' => $probe['engine'],
        'stamp' => date('c'),
    ]);
    exit;
}

// ─── LOGIN ───
if ($_POST && $action === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT d.*, ss.port, ss.status as stream_status, ss.autodj_enabled as autodj_active,
        (SELECT COUNT(*) FROM radio_listener_analytics WHERE stream_id = d.stream_id AND date = CURDATE()) as today_listeners
        FROM radio_djs d JOIN streaming_stations ss ON d.stream_id = ss.id WHERE d.username = ?");
    $stmt->execute([$username]);
    $dj = $stmt->fetch(PDO::FETCH_OBJ);
    $djStatus = strtolower($dj->status ?? '');
    // Blocked: suspended / banned / inactive — no login at all
    if ($dj && in_array($djStatus, ['suspended', 'banned', 'inactive'])) {
        $error = 'This DJ account is ' . $djStatus . '. Contact the station owner for access.';
        $dj = null;
    }
    if ($dj && password_verify($password, $dj->password)) {
        // Store plain password in DB and session (for display in SAM credentials)
        $pdo->prepare("UPDATE radio_djs SET plain_password=?, last_active=NOW() WHERE id=?")->execute([$password, $dj->id]);
        $onLeave = strtolower($dj->status ?? '') === 'on_leave';
        $_SESSION['dj_user'] = [
            'id' => $dj->id, 'stream_id' => $dj->stream_id, 'username' => $dj->username,
            'name' => $dj->name ?: $dj->username, 'stream_name' => 'Stream',
            'port' => $dj->port, 'stream_status' => $dj->stream_status,
            'plain_password' => $password,
            'on_leave' => $onLeave,
            'status' => $dj->status ?? 'active',
        ];
        if ($onLeave) {
            $success = 'You are signed in but currently on leave — streaming is disabled.';
        }
    header('Location: /dj_panel.php?action=dashboard' . ($onLeave ? '&notice=on_leave' : ''));
    exit;
}
    if (!isset($error) || !$error) $error = 'Invalid DJ name or password, or account inactive.';
}

// ─── SAVE PROFILE DATA ───
if ($action === 'save_profile_data' && $_POST && isset($_SESSION['dj_user'])) {
    $did = $_SESSION['dj_user']['id'];
    $fields = ['name','bio','website_url','real_name','nickname','stage_name','full_bio','years_as_dj','hometown','country','languages',
        'booking_email','booking_form','phone','position','on_air_since','employee_type','department','dj_status',
        'show_name','show_description','timezone','show_duration','preferred_genres','preferred_decades','favorite_artists','favorite_songs',
        'favorite_albums','favorite_djs','hobbies','pets','fun_fact','favorite_food','favorite_drink','favorite_movie','favorite_tv_show','favorite_sports_team',
        'skills','mixer','controller','microphone','headphones','streaming_software','operating_system','preferred_software',
        'years_on_station','total_shows','total_hours','listener_likes','followers','awards','birthday',
        'profile_color','bg_color','accent_color','profile_layout'];
    $simple = ['clean_music_only','explicit_allowed','request_friendly','open_format','specialty_show',
        'accept_requests','accept_dedications','live_chat_enabled','private_messages','fan_mail',
        'public_profile','station_only','hidden_email','hidden_birthday','hidden_location'];
    $profileData = [];
    foreach ($fields as $f) { $profileData[$f] = $_POST[$f] ?? ''; }
    foreach ($simple as $f) { $profileData[$f] = isset($_POST[$f]) ? 1 : 0; }
    foreach (['facebook','instagram','twitter','tiktok','youtube','twitch','discord','spotify','apple_music','soundcloud','mixcloud','beatport'] as $s) {
        $profileData[$s] = $_POST[$s] ?? '';
    }
    $pdo->prepare("UPDATE radio_djs SET name=?, bio=?, website_url=?, profile_data=? WHERE id=?")
        ->execute([$_POST['name'] ?? '', $_POST['bio'] ?? '', $_POST['website_url'] ?? '', json_encode($profileData), $did]);
    $_SESSION['dj_user']['name'] = $_POST['name'] ?: $_SESSION['dj_user']['name'];
    $success = 'Profile saved!';
    $action = 'dashboard';
}

if ($action === 'logout') {
    session_destroy();
    header('Location: /dj_panel.php');
    exit;
}

if ($action === 'takeover' && $_POST && isset($_SESSION['dj_user'])) {
    if (!empty($_SESSION['dj_user']['on_leave'])) {
        $error = 'You are on leave and cannot take over the stream.';
        header('Location: /dj_panel.php?action=dashboard');
        exit;
    }
    $sid = $_SESSION['dj_user']['stream_id'] ?? 0;
    $djUsername = $_SESSION['dj_user']['username'] ?? '';
    if ($sid > 0) {
        // Kill AutoDJ by stream-specific runner filename
        exec("pkill -f \"runner_{$sid}\" 2>/dev/null");
        // Kill PID file process
        $pidFile = '/home/' . $sid . '/radio/autodj/autodj.pid';
        // Try planethosts path too
        $altPidFile = '/home/planethosts/radio/autodj/autodj.pid';
        foreach ([$pidFile, $altPidFile] as $pf) {
            if (file_exists($pf)) { $pid = (int)trim(file_get_contents($pf)); if ($pid > 0) { exec("kill {$pid} 2>/dev/null"); usleep(200000); } @unlink($pf); }
        }
        // Also kill any ffmpeg/shoutcast processes
        exec("pkill -f \"ffmpeg.*{$sid}\" 2>/dev/null");
        exec("pkill -f \"ShoutcastSource\" 2>/dev/null");
        // Update DB
        try {
            $pdo->exec("UPDATE streaming_stations SET autodj_enabled=0, current_dj=" . $pdo->quote($djUsername) . ", current_artist=" . $pdo->quote($djUsername) . " WHERE id=" . (int)$sid);
            $pdo->exec("UPDATE radio_autodj_config SET autodj_enabled=0 WHERE station_id=" . ((int)$sid + 10000));
            $pdo->exec("UPDATE radio_streams SET current_dj=" . $pdo->quote($djUsername) . " WHERE id=" . (int)$sid);
        } catch (\Exception $e) {}
        $success = 'AutoDJ stopped. Connect your broadcasting software to port 9000 with your DJ username:password.';
    }
    header('Location: /dj_panel.php?action=dashboard');
    exit;
}

// ─── KICK STREAM ───
if ($action === 'kick' && $_POST && isset($_SESSION['dj_user'])) {
    if (!empty($_SESSION['dj_user']['on_leave'])) {
        $error = 'You are on leave and cannot kick sources.';
        header('Location: /dj_panel.php?action=dashboard');
        exit;
    }
    $ksid = (int)($_POST['stream_id'] ?? 0);
    $djUser = $_SESSION['dj_user']['username'] ?? 'unknown';
    if ($ksid > 0) {
        $st = $pdo->prepare("SELECT * FROM streaming_stations WHERE id = ?");
        $st->execute([$ksid]);
        $s = $st->fetch(PDO::FETCH_OBJ);
        if ($s) {
            $engine = strtolower($s->engine ?? $s->server_type ?? 'icecast');
            if ($engine === 'icecast') {
                @file_get_contents("http://localhost:{$s->port}/admin/killsource?mount={$s->mount_point}", false, stream_context_create(['http'=>['timeout'=>3, 'header'=>"Authorization: Basic " . base64_encode("admin:{$s->admin_password}")]]));
            } elseif (in_array($engine, ['shoutcast2', 'shoutcast'])) {
                @file_get_contents("http://localhost:{$s->port}/admin.cgi?mode=kicksrc&sid=1", false, stream_context_create(['http'=>['timeout'=>3, 'header'=>"Authorization: Basic " . base64_encode("admin:{$s->admin_password}")]]));
            } else {
                @file_get_contents("http://localhost:{$s->port}/admin.cgi?pass={$s->admin_password}&mode=kicksrc", false, stream_context_create(['http'=>['timeout'=>3]]));
            }
            exec("pkill -f \"runner_{$ksid}\" 2>/dev/null");
            $pidFile = '/home/planethosts/radio/autodj/autodj.pid';
            if (file_exists($pidFile)) { $pid = (int)trim(file_get_contents($pidFile)); if ($pid > 0) exec("kill {$pid} 2>/dev/null"); @unlink($pidFile); }
            try { $pdo->exec("INSERT INTO radio_kick_log (stream_id, kicked_by, engine, method) VALUES ($ksid, " . $pdo->quote($djUser) . ", " . $pdo->quote($engine) . ", 'dj_panel')"); } catch (\Exception $e) {}
            $success = 'Source kicked on stream #' . $ksid . '.';
        }
    }
    header('Location: /dj_panel.php?action=dashboard');
    exit;
}

// ─── ADD SCHEDULE ───
if ($action === 'add_schedule' && $_POST && isset($_SESSION['dj_user'])) {
    $sId = $_SESSION['dj_user']['stream_id'] ?? 0;
    $djId = $_SESSION['dj_user']['id'] ?? 0;
    $sn = trim($_POST['show_name'] ?? '');
    $sd = trim($_POST['scheduled_date'] ?? '');
    $st = trim($_POST['start_time'] ?? '');
    $et = trim($_POST['end_time'] ?? '');
    if ($sn && $sd && $st && $sId) {
        try {
            $timeSlot = $st . '-' . $et;
            $dw = date('w', strtotime($sd));
            $pdo->prepare("INSERT INTO radio_dj_schedule (stream_id, dj_id, scheduled_date, time_slot, show_name, day_of_week, start_time, end_time, is_active, created_by, status) VALUES (?,?,?,?,?,?,?,?,1,'dj','booked')")
                ->execute([$sId, $djId, $sd, $timeSlot, $sn, $dw, $st, $et]);
            $success = 'Show booked!';
        } catch (\Exception $e) { $error = 'Failed to book: ' . $e->getMessage(); }
    } else { $error = 'Please fill all fields.'; }
    header('Location: /dj_panel.php?action=dashboard&tab=schedule&sched_month=' . date('n') . '&sched_year=' . date('Y'));
    exit;
}

// ─── REMOVE SCHEDULE ───
if ($action === 'remove_schedule' && isset($_GET['id']) && isset($_SESSION['dj_user'])) {
    $schedId = (int)$_GET['id'];
    try {
        $pdo->prepare("DELETE FROM radio_dj_schedule WHERE id = ? AND stream_id = ?")->execute([$schedId, $_SESSION['dj_user']['stream_id']]);
        $success = 'Show unbooked.';
    } catch (\Exception $e) { $error = 'Failed to unbook.'; }
    header('Location: /dj_panel.php?action=dashboard&tab=schedule');
    exit;
}

// ─── SAVE PROFILE ───
if ($_POST && $action === 'save_profile' && isset($_SESSION['dj_user'])) {
    $did = $_SESSION['dj_user']['id'];
    $pdo->prepare("UPDATE radio_djs SET name = ?, bio = ?, website_url = ? WHERE id = ?")->execute([
        $_POST['name'] ?? '', $_POST['bio'] ?? '', $_POST['website_url'] ?? '', $did
    ]);
    $_SESSION['dj_user']['name'] = $_POST['name'] ?: $_SESSION['dj_user']['name'];
    $success = 'Profile updated.';
    $action = 'dashboard';
}

// ─── UPLOAD BANNER ───
if ($_FILES && $action === 'upload_banner' && isset($_SESSION['dj_user'])) {
    $allowed = ['jpg','jpeg','png','gif','webp'];
    $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed) && $_FILES['file']['size'] < 5 * 1024 * 1024) {
        $dj = $_SESSION['dj_user']['username'] ?? '';
        $hst = $pdo->prepare("SELECT hu.username FROM radio_djs d JOIN streaming_stations ss ON d.stream_id=ss.id JOIN hosting_users hu ON ss.user_id=hu.id WHERE d.username=?");
        $hst->execute([$dj]); $hu = $hst->fetchColumn();
        $dir = $hu ? "/home/{$hu}/radio/dj/{$dj}/" : '/var/www/radiohosting/public/uploads/';
        @mkdir($dir, 0755, true);
        $name = 'banner.' . $ext;
        move_uploaded_file($_FILES['file']['tmp_name'], $dir . $name);
        $urlPath = $hu ? "/dj-file.php?dj={$dj}&file={$name}" : '/uploads/' . $name;
        $pdo->prepare("UPDATE radio_djs SET banner = ? WHERE id = ?")->execute([$urlPath, $_SESSION['dj_user']['id']]);
        $success = 'Banner uploaded.';
    } else {
        $error = 'Invalid file. Allowed: jpg, png, gif, webp. Max 5MB.';
    }
    $action = 'dashboard';
}

// ─── UPLOAD AVATAR ───
if ($_FILES && $action === 'upload_avatar' && isset($_SESSION['dj_user'])) {
    $allowed = ['jpg','jpeg','png','gif','webp'];
    $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed) && $_FILES['file']['size'] < 2 * 1024 * 1024) {
        $dj = $_SESSION['dj_user']['username'] ?? '';
        $hst = $pdo->prepare("SELECT hu.username FROM radio_djs d JOIN streaming_stations ss ON d.stream_id=ss.id JOIN hosting_users hu ON ss.user_id=hu.id WHERE d.username=?");
        $hst->execute([$dj]); $hu = $hst->fetchColumn();
        $dir = $hu ? "/home/{$hu}/radio/dj/{$dj}/" : '/var/www/radiohosting/public/uploads/';
        @mkdir($dir, 0755, true);
        $name = 'avatar.' . $ext;
        move_uploaded_file($_FILES['file']['tmp_name'], $dir . $name);
        $urlPath = $hu ? "/dj-file.php?dj={$dj}&file={$name}" : '/uploads/' . $name;
        $pdo->prepare("UPDATE radio_djs SET avatar = ? WHERE id = ?")->execute([$urlPath, $_SESSION['dj_user']['id']]);
        $success = 'Avatar updated.';
    } else {
        $error = 'Invalid file. Allowed: jpg, png, gif, webp. Max 2MB.';
    }
    $action = 'dashboard';
}

// ─── GALLERY UPLOAD ───
if ($action === 'upload_gallery' && $_FILES && isset($_SESSION['dj_user'])) {
    $allowed = ['jpg','jpeg','png','gif','webp','mp4','mov','avi'];
    $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed) && $_FILES['file']['size'] < 20 * 1024 * 1024) {
        $dj = $_SESSION['dj_user']['username'] ?? '';
        $hst = $pdo->prepare("SELECT hu.username FROM radio_djs d JOIN streaming_stations ss ON d.stream_id=ss.id JOIN hosting_users hu ON ss.user_id=hu.id WHERE d.username=?");
        $hst->execute([$dj]); $hu = $hst->fetchColumn();
        $dir = $hu ? "/home/{$hu}/radio/dj/{$dj}/gallery/" : '/var/www/radiohosting/public/uploads/gallery/';
        @mkdir($dir, 0755, true);
        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        move_uploaded_file($_FILES['file']['tmp_name'], $dir . $name);
        $urlPath = $hu ? "/dj-file.php?dj={$dj}&file=gallery/{$name}" : '/uploads/gallery/' . $name;
        $existing = $pdo->query("SELECT gallery FROM radio_djs WHERE id=" . (int)$_SESSION['dj_user']['id'])->fetchColumn();
        $gallery = $existing ? json_decode($existing, true) : [];
        $gallery[] = ['url' => $urlPath, 'type' => in_array($ext, ['mp4','mov','avi']) ? 'video' : 'image', 'uploaded_at' => date('Y-m-d H:i:s')];
        $pdo->prepare("UPDATE radio_djs SET gallery=? WHERE id=?")->execute([json_encode($gallery), $_SESSION['dj_user']['id']]);
        $success = 'File added to gallery.';
    } else { $error = 'Invalid file. Max 20MB. Allowed: jpg, png, gif, webp, mp4, mov, avi.'; }
    header('Location: /dj_panel.php?action=dashboard');
    exit;
}

// ─── GALLERY DELETE ───
if ($action === 'delete_gallery' && isset($_GET['idx']) && isset($_SESSION['dj_user'])) {
    $idx = (int)$_GET['idx'];
    $existing = $pdo->query("SELECT gallery FROM radio_djs WHERE id=" . (int)$_SESSION['dj_user']['id'])->fetchColumn();
    $gallery = $existing ? json_decode($existing, true) : [];
    if (isset($gallery[$idx])) { array_splice($gallery, $idx, 1); }
    $pdo->prepare("UPDATE radio_djs SET gallery=? WHERE id=?")->execute([json_encode($gallery), $_SESSION['dj_user']['id']]);
    header('Location: /dj_panel.php?action=dashboard');
    exit;
}

// ─── REMOVE REQUEST ───
if ($action === 'remove_request' && isset($_GET['req_id']) && isset($_SESSION['dj_user'])) {
    $reqId = (int)$_GET['req_id'];
    $djId = (int)$_SESSION['dj_user']['id'];
    // Remove only if the request belongs to a station THIS DJ can access
    // (primary station or any via the radio_dj_streams junction).
    $chk = $pdo->prepare("SELECT r.id FROM radio_requests r
        WHERE r.id = ?
          AND (r.stream_id = (SELECT stream_id FROM radio_djs WHERE id = ?)
               OR r.stream_id IN (SELECT stream_id FROM radio_dj_streams WHERE dj_id = ? AND is_active = 'yes'))");
    $chk->execute([$reqId, $djId, $djId]);
    if ($chk->fetch()) {
        $pdo->prepare("UPDATE radio_requests SET status = 'removed' WHERE id = ?")
            ->execute([$reqId]);
        $success = 'Request removed.';
    } else {
        $error = 'Request not found for your stations.';
    }
    header('Location: /dj_panel.php?action=dashboard&tab=requests');
    exit;
}

// ─── REQUESTS REFRESH (AJAX) — returns just the request list HTML ───
if ($action === 'requests_refresh' && isset($_SESSION['dj_user'])) {
    header('Content-Type: text/html; charset=utf-8');
    $djId = (int)$_SESSION['dj_user']['id'];
    $q = $pdo->prepare("SELECT r.*, ss.name AS station_name, ss.engine AS station_engine,
        rb.brand_logo, rb.brand_primary_color, rb.brand_slogan
        FROM radio_requests r
        JOIN streaming_stations ss ON ss.id = r.stream_id
        LEFT JOIN radio_branding rb ON rb.station_id = ss.id
        WHERE r.status = 'pending'
          AND (r.stream_id = (SELECT stream_id FROM radio_djs WHERE id = ?)
               OR r.stream_id IN (SELECT stream_id FROM radio_dj_streams WHERE dj_id = ?))
        ORDER BY r.created_at ASC");
    $q->execute([$djId, $djId]);
    $reqs = $q->fetchAll(PDO::FETCH_OBJ);
    if (empty($reqs)) {
        echo '<p class="empty-text">No pending requests.</p>';
        exit;
    }
    foreach ($reqs as $r) {
        ?>
<div class="req-item">
<div style="display:flex;gap:8px;align-items:center;min-width:0">
<?php if (!empty($r->brand_logo)): ?>
  <img src="https://planet-hosts.com<?=htmlspecialchars($r->brand_logo)?>" alt="" style="width:34px;height:34px;border-radius:8px;object-fit:cover;flex-shrink:0;border:1px solid rgba(255,255,255,.08)">
<?php endif; ?>
<div style="min-width:0">
<div class="req-title"><?php echo htmlspecialchars($r->artist . ' - ' . $r->title); ?></div>
<?php if ($r->guest_name): ?><div class="req-meta">Requested by: <?php echo htmlspecialchars($r->guest_name); ?></div><?php endif; ?>
<?php if ($r->station_name): ?><div class="req-meta" style="color:<?php echo htmlspecialchars($r->brand_primary_color ?? '#38bdf8'); ?>">📡 Station: <?php echo htmlspecialchars($r->station_name); ?></div><?php endif; ?>
<?php if ($r->message): ?><div class="req-msg">"<?php echo htmlspecialchars($r->message); ?>"</div><?php endif; ?>
</div>
</div>
<div style="display:flex;gap:6px;flex-shrink:0">
<a href="/dj_panel.php?action=played_request&req_id=<?php echo $r->id; ?>" class="btn btn-success btn-xs" title="Mark as played"><i class="fas fa-check"></i> Played</a>
<a href="/dj_panel.php?action=remove_request&req_id=<?php echo $r->id; ?>" class="btn btn-danger btn-xs">✕ Remove</a>
</div>
</div>
<?php
    }
    exit;
}

// ─── MARK REQUEST PLAYED ───
if ($action === 'played_request' && isset($_GET['req_id']) && isset($_SESSION['dj_user'])) {
    $reqId = (int)$_GET['req_id'];
    $djId = (int)$_SESSION['dj_user']['id'];
    $chk = $pdo->prepare("SELECT r.id FROM radio_requests r
        WHERE r.id = ?
          AND (r.stream_id = (SELECT stream_id FROM radio_djs WHERE id = ?)
               OR r.stream_id IN (SELECT stream_id FROM radio_dj_streams WHERE dj_id = ? AND is_active = 'yes'))");
    $chk->execute([$reqId, $djId, $djId]);
    if ($chk->fetch()) {
        $pdo->prepare("UPDATE radio_requests SET status = 'played' WHERE id = ?")
            ->execute([$reqId]);
        $success = 'Request marked as played.';
    } else {
        $error = 'Request not found for your stations.';
    }
    header('Location: /dj_panel.php?action=dashboard&tab=requests');
    exit;
}

// ─── GET DJ API CONFIG (AJAX endpoint) ───
if ($action === 'get_api_config' && isset($_SESSION['dj_user'])) {
    header('Content-Type: application/json');
    try {
        $djId = $_SESSION['dj_user']['id'] ?? 0;
        $streamId = (int)($_GET['stream_id'] ?? $_SESSION['dj_user']['stream_id'] ?? 0);

        // Look up the station name so URLs can use a readable slug
        $st = $pdo->prepare("SELECT name FROM streaming_stations WHERE id = ?");
        $st->execute([$streamId]);
        $stationName = $st->fetchColumn() ?: "Stream #{$streamId}";
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($stationName)), '-'));
        if ($slug === '') $slug = (string)$streamId;

        // Get or create API config scoped to (dj_id, stream_id) — each station has its own key/URL
        $config = $pdo->prepare("SELECT * FROM dj_api_config WHERE dj_id = ? AND stream_id = ?");
        $config->execute([$djId, $streamId]);
        $cfg = $config->fetch(PDO::FETCH_OBJ);
        
        if (!$cfg) {
            $apiKey = bin2hex(random_bytes(16));
            $reqUrl = "https://planet-hosts.com/connector/station/{$slug}/requests";
            $pdo->prepare("INSERT INTO dj_api_config (dj_id, stream_id, dj_name, dj_display_name, api_key, request_api_url) VALUES (?,?,?,?,?,?)")
                ->execute([$djId, $streamId, $_SESSION['dj_user']['name'] ?? '', $_SESSION['dj_user']['name'] ?? '', $apiKey, $reqUrl]);
            $config->execute([$djId, $streamId]);
            $cfg = $config->fetch(PDO::FETCH_OBJ);
        }
        
        // Also return all station configs for this DJ (with their slugs)
        $allStmt = $pdo->prepare("SELECT c.*, ss.name AS station_name FROM dj_api_config c LEFT JOIN streaming_stations ss ON ss.id = c.stream_id WHERE c.dj_id = ? ORDER BY c.stream_id");
        $allStmt->execute([$djId]);
        $all = $allStmt->fetchAll(PDO::FETCH_OBJ);
        foreach ($all as &$c) {
            $sn = $c->station_name ?? "Stream #{$c->stream_id}";
            $c->slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($sn)), '-')) ?: (string)$c->stream_id;
            $c->request_api_url = "https://planet-hosts.com/connector/station/{$c->slug}/requests";
        }
        unset($c);
        
        echo json_encode(['success' => true, 'data' => $cfg, 'configs' => $all, 'stationId' => $streamId, 'slug' => $slug]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ─── GET FRESH DJ DATA ───
$djData = null;
if (isset($_SESSION['dj_user'])) {
    if (!empty($_SESSION['dj_user']['is_owner'])) {
        $ss = $pdo->prepare("SELECT ss.*, rs.port as rs_port FROM streaming_stations ss LEFT JOIN radio_streams rs ON rs.id = ss.id WHERE ss.id = ?");
        $ss->execute([$_SESSION['dj_user']['stream_id']]);
        $s = $ss->fetch(PDO::FETCH_OBJ);
        $djData = (object)[
            'stream_status' => $s->status ?? 'stopped',
            'listener_count' => $s->listener_count ?? 0,
            'current_song' => $s->current_song ?? '',
            'autodj_active' => $s->autodj_enabled ?? 0,
            'track_count' => 0,
            'id' => 0,
            'stream_id' => $_SESSION['dj_user']['stream_id'],
            'hosting_username' => '',
            'current_dj' => null,
            'port' => $s->port ?? 0,
        ];
    } else {
        $selStreamId = (int)$_SESSION['dj_user']['stream_id'];
        // Get DJ profile data (banner, avatar, etc.)
        $djProfile = $pdo->prepare("SELECT id, name, bio, website_url, banner, avatar, profile_data, gallery FROM radio_djs WHERE id = ?");
        $djProfile->execute([$_SESSION['dj_user']['id']]);
        $dp = $djProfile->fetch(PDO::FETCH_OBJ);
        // Get station data for the selected stream
        $stmt = $pdo->prepare("SELECT ss.status as stream_status, ss.listener_count, ss.current_song, ss.autodj_enabled as autodj_active, ss.current_dj, ss.port, ss.bitrate,
            (SELECT COUNT(*) FROM radio_playlist_items pi JOIN radio_playlists p ON pi.playlist_id = p.id WHERE p.stream_id = ?) as track_count
            FROM streaming_stations ss WHERE ss.id = ?");
        $stmt->execute([$selStreamId, $selStreamId]);
        $sData = $stmt->fetch(PDO::FETCH_OBJ);
        $djData = (object)[
            'id' => $dp->id ?? $_SESSION['dj_user']['id'],
            'name' => $dp->name ?? $_SESSION['dj_user']['name'],
            'bio' => $dp->bio ?? '',
            'website_url' => $dp->website_url ?? '',
            'banner' => $dp->banner ?? null,
            'avatar' => $dp->avatar ?? null,
            'profile_data' => $dp->profile_data ?? null,
            'gallery' => $dp->gallery ?? null,
            'stream_status' => $sData->stream_status ?? 'stopped',
            'listener_count' => $sData->listener_count ?? 0,
            'current_song' => $sData->current_song ?? '',
            'autodj_active' => $sData->autodj_active ?? 0,
            'track_count' => $sData->track_count ?? 0,
            'stream_id' => $selStreamId,
            'current_dj' => $sData->current_dj ?? null,
            'port' => $sData->port ?? 0,
            'bitrate' => $sData->bitrate ?? 128,
        ];
        if (!$djData) { session_destroy(); header('Location: /dj_panel.php'); exit; }
    }
}

// ─── RENDER ───
if ($action !== 'dashboard' && $action !== 'profile') {
?>
<!DOCTYPE html><html><head><title>DJ Login - Planet Hosts</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:transparent;color:#fff;font-family:'Inter',sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh}
.bg{position:fixed;inset:0;background:linear-gradient(rgba(4,6,12,.82),rgba(8,12,24,.88)),url(/mainbk.webp);background-size:cover;background-position:center;background-attachment:fixed;z-index:-2}
.card{background:rgba(13,20,36,.78);border:1px solid rgba(56,189,248,.14);border-radius:20px;padding:40px 32px;max-width:400px;width:92%;text-align:center;box-shadow:0 24px 60px rgba(0,0,0,.5);backdrop-filter:blur(14px)}
h1{font-size:23px;margin-bottom:6px;letter-spacing:.3px}h1 span{color:#38bdf8}
p{color:#8ca0bf;font-size:13px;margin-bottom:22px}
.form-group{margin-bottom:14px;text-align:left}
.form-group label{display:block;margin-bottom:5px;font-size:11px;color:#8ca0bf;font-weight:600;text-transform:uppercase;letter-spacing:.05em}
.form-group input{width:100%;padding:12px 14px;background:rgba(0,0,0,.35);border:1px solid rgba(148,163,184,.18);border-radius:11px;color:#fff;font-size:14px;outline:none;box-sizing:border-box;transition:border-color .2s,box-shadow .2s}
.form-group input:focus{border-color:rgba(56,189,248,.5);box-shadow:0 0 0 3px rgba(56,189,248,.1)}
.btn{width:100%;padding:13px;background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;border:none;border-radius:11px;font-size:15px;font-weight:700;cursor:pointer;box-shadow:0 8px 24px rgba(14,165,233,.25);transition:transform .15s}
.btn:hover{transform:translateY(-1px)}
.alert{padding:11px 14px;border-radius:11px;margin-bottom:14px;font-size:13px}
.alert-error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.25);color:#f87171}
.alert-success{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.25);color:#4ade80}
</style></head><body>
<div class="bg"></div>
<div class="card">
<div style="font-size:38px;margin-bottom:8px">🎤</div>
<h1>Planet <span>DJ</span></h1>
<p>Sign in with your DJ credentials</p>
<?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="POST">
<div class="form-group"><label>DJ Username</label><input name="username" required autofocus></div>
<div class="form-group"><label>Password</label><input name="password" type="password" required></div>
<button type="submit" class="btn">Sign In</button>
</form>
<p style="margin-top:14px;font-size:11px;color:#475569">Powered by Planet-Hosts Radio</p>
</div></body></html>
<?php exit; } ?>

<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>DJ Panel - <?php echo htmlspecialchars($_SESSION['dj_user']['name'] ?? 'DJ'); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{
  --bg:#05070f; --panel:rgba(14,20,36,.66); --panel2:rgba(22,32,54,.5); --line:rgba(148,163,184,.1);
  --line2:rgba(148,163,184,.18); --txt:#e8eef8; --mut:#93a4bd; --acc:#38bdf8; --vio:#a78bfa;
  --green:#34d399; --red:#f87171; --amber:#facc15; --ink:#0a0f1e;
  --r:18px; --shadow:0 18px 40px rgba(0,0,0,.42);
}
*{margin:0;padding:0;box-sizing:border-box}
html,body{height:100%}
body{font-family:'Inter',system-ui,sans-serif;color:var(--txt);letter-spacing:.1px;background:transparent}
.bg{position:fixed;inset:0;z-index:-2;background:linear-gradient(rgba(5,7,15,.82),rgba(10,16,31,.88)),url(/mainbk.webp);background-size:cover;background-position:center;background-attachment:fixed;
  box-shadow:inset 0 0 0 999px rgba(0,0,0,0);
}
.bg::before{content:"";position:fixed;inset:0;z-index:-1;background:
  radial-gradient(1100px 560px at 85% -8%,rgba(56,189,248,.14),transparent 60%),
  radial-gradient(900px 480px at -8% 108%,rgba(167,139,250,.12),transparent 60%),
  radial-gradient(700px 400px at 50% 120%,rgba(20,184,166,.07),transparent 60%)}
.container{max-width:1240px;margin:0 auto;padding:26px 24px 70px}

.topbar{background:rgba(9,14,26,.7);backdrop-filter:blur(18px) saturate(150%);-webkit-backdrop-filter:blur(18px);
  border-bottom:1px solid var(--line);padding:13px 26px;display:flex;justify-content:space-between;
  align-items:center;position:sticky;top:0;z-index:100;gap:12px;flex-wrap:wrap}
.topbar h2{font-size:18px;font-weight:800;letter-spacing:1px;display:flex;align-items:center;gap:10px}
.topbar h2 span{background:linear-gradient(135deg,#38bdf8,#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.topbar .tb-badge{font-size:10px;font-weight:700;padding:3px 10px;border-radius:999px;background:rgba(56,189,248,.12);
  color:#38bdf8;border:1px solid rgba(56,189,248,.25);letter-spacing:1px}
.topbar a{color:#f87171;text-decoration:none;font-size:12.5px;font-weight:600;transition:color .2s}
.topbar a:hover{color:#fca5a5}
.topbar .studio-link{color:#a78bfa}

.card{background:var(--panel);border:1px solid var(--line);border-radius:var(--r);padding:20px 22px;margin-bottom:16px;
  box-shadow:var(--shadow);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
  transition:border-color .25s,transform .2s,box-shadow .2s}
.card:hover{border-color:var(--line2)}
.card h3{color:var(--acc);font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:700;margin-bottom:14px;
  display:flex;align-items:center;gap:8px}
.card h3 i{font-size:14px;opacity:.85}

.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:22px}
.stat-card{background:var(--panel);border:1px solid var(--line);border-radius:var(--r);padding:22px 16px;text-align:center;
  box-shadow:var(--shadow);backdrop-filter:blur(12px);position:relative;overflow:hidden;transition:transform .2s,border-color .2s}
.stat-card::before{content:"";position:absolute;top:0;left:0;right:0;height:2px;background:var(--c,linear-gradient(90deg,#38bdf8,#a78bfa));opacity:.6}
.stat-card:hover{border-color:var(--line2);transform:translateY(-3px)}
.stat-card .num{font-size:30px;font-weight:800;color:var(--c,#38bdf8);line-height:1.2}
.stat-card .label{font-size:10.5px;color:var(--mut);margin-top:6px;text-transform:uppercase;letter-spacing:.6px;font-weight:700}

.dj-tabs{display:flex;gap:6px;margin-bottom:22px;flex-wrap:wrap;padding:6px;background:var(--panel);
  border:1px solid var(--line);border-radius:14px;width:fit-content;max-width:100%;box-shadow:var(--shadow);backdrop-filter:blur(12px)}
.dj-tab{padding:8px 16px;border-radius:10px;font-size:12.5px;font-weight:600;cursor:pointer;transition:all .18s;
  color:var(--mut);background:transparent;border:1px solid transparent}
.dj-tab:hover{background:rgba(56,189,248,.08);color:var(--txt)}
.dj-tab.act{background:linear-gradient(135deg,rgba(56,189,248,.18),rgba(167,139,250,.13));
  color:#7dd3fc;border-color:rgba(56,189,248,.35);box-shadow:0 4px 16px rgba(56,189,248,.15)}
.dj-tab.active-station.act{background:linear-gradient(135deg,rgba(167,139,250,.22),rgba(56,189,248,.16));color:#c4b5fd}
.dj-panel{display:none}
.dj-panel.act{display:block;animation:fadeIn .3s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}

input,textarea,select{background:rgba(0,0,0,.34);border:1px solid var(--line2);border-radius:11px;color:var(--txt);
  font-size:13px;outline:none;transition:border-color .2s,box-shadow .2s;font-family:'Inter',sans-serif;
  padding:10px 14px;width:100%;box-sizing:border-box}
input:focus,textarea:focus,select:focus{border-color:rgba(56,189,248,.45);box-shadow:0 0 0 3px rgba(56,189,248,.09);background:rgba(0,0,0,.42)}
textarea{min-height:80px;resize:vertical}
select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2393a4bd' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px}
.form-group{margin-bottom:12px}
.form-group label{display:block;font-size:10.5px;color:var(--mut);margin-bottom:4px;font-weight:700;text-transform:uppercase;letter-spacing:.4px}

.btn{padding:10px 20px;border-radius:11px;border:none;font-weight:600;font-size:13px;cursor:pointer;transition:all .2s;
  font-family:'Inter',sans-serif;display:inline-flex;align-items:center;gap:6px;text-decoration:none}
.btn-primary{background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;box-shadow:0 5px 18px rgba(14,165,233,.28)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(14,165,233,.36)}
.btn-danger{background:rgba(248,113,113,.12);color:var(--red)}
.btn-danger:hover{background:rgba(248,113,113,.2)}
.btn-success{background:rgba(74,222,128,.12);color:#4ade80}
.btn-success:hover{background:rgba(74,222,128,.2)}
.btn-warning{background:rgba(250,204,21,.1);color:#facc15}
.btn-warning:hover{background:rgba(250,204,21,.18)}
.btn-secondary{background:rgba(148,163,184,.1);color:#cbd5e1}
.btn-secondary:hover{background:rgba(148,163,184,.18)}
.btn-sm{padding:6px 14px;font-size:11px;border-radius:9px}
.btn-xs{padding:3px 10px;font-size:10px;border-radius:7px}

.pill{display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border-radius:999px;font-size:12px;font-weight:700;letter-spacing:.04em}
.pill .livedot{width:9px;height:9px;border-radius:50%;animation:pulse 1.4s ease-in-out infinite}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.45;transform:scale(.78)}}
.live{background:rgba(52,211,153,.12);color:var(--green);border:1px solid rgba(52,211,153,.35)}
.live .livedot{background:var(--green);box-shadow:0 0 8px var(--green)}
.autodj{background:rgba(250,204,21,.1);color:var(--amber);border:1px solid rgba(250,204,21,.3)}
.autodj .livedot{background:var(--amber);box-shadow:0 0 8px var(--amber)}
.off{background:rgba(148,163,184,.12);color:#a9bad7;border:1px solid rgba(148,163,184,.3)}
.off .livedot{background:#64748b;animation:none}

.hero{background:linear-gradient(135deg,rgba(14,165,233,.14),rgba(167,139,250,.12)),var(--panel);border:1px solid rgba(56,189,248,.22);border-radius:22px;padding:24px 26px;margin-bottom:20px;box-shadow:var(--shadow);position:relative;overflow:hidden;backdrop-filter:blur(12px)}
.hero::after{content:"";position:absolute;top:-80px;right:-80px;width:280px;height:280px;background:radial-gradient(circle,rgba(56,189,248,.16),transparent 70%)}
.hero::before{content:"";position:absolute;bottom:-70px;left:-70px;width:220px;height:220px;background:radial-gradient(circle,rgba(167,139,250,.12),transparent 70%)}
.mono{font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.np-title{font-size:21px;font-weight:800;color:#fff;text-shadow:0 2px 12px rgba(56,189,248,.25)}
.np-sub{font-size:13px;color:var(--mut)}

.mgrid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:18px}
.tile{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:16px;position:relative;overflow:hidden;backdrop-filter:blur(10px);transition:transform .2s,border-color .2s}
.tile:hover{border-color:var(--line2);transform:translateY(-2px)}
.tile .tnum{font-size:26px;font-weight:800;line-height:1.1}
.tile .tlabel{font-size:10px;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin-top:4px;font-weight:600}
.tile .ticon{position:absolute;right:10px;top:10px;font-size:18px;opacity:.16}

.banner{width:100%;height:180px;border-radius:16px;overflow:hidden;margin-bottom:20px;background:rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;font-size:14px;color:#475569;border:1px solid rgba(56,189,248,.06)}
.banner img{width:100%;height:100%;object-fit:cover}
.banner-empty-icon{font-size:32px;opacity:.3}
.station-info{font-size:12px;color:#94a3b8;line-height:1.7}
.station-info strong{color:#64748b}
.dj-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px}
.dj-grid .card{margin-bottom:0}
@media(min-width:1200px){.dj-grid{grid-template-columns:repeat(3,1fr)}}
/* Requests + Downloads panels: full-width cards, not squeezed into one grid column */
#pn-requests .dj-grid,#pn-downloads{grid-template-columns:1fr !important;display:grid;gap:14px}
#pn-requests .card,#pn-downloads .card{margin-bottom:0}

.conn-grid{display:flex;flex-direction:column;gap:8px}
.conn-row{display:flex;justify-content:space-between;align-items:center;padding:6px 0}
.conn-row+.conn-row{border-top:1px solid rgba(255,255,255,.05)}
.conn-label{color:#64748b;font-size:12px}
.conn-value{color:#4ade80;font-family:monospace;font-size:13px;font-weight:600}
.conn-value.pw{color:#facc15}
.conn-value.api{color:#a855f7}
.api-row{font-size:10px;color:#64748b;line-height:1.8}
.api-row code{color:#a855f7;font-size:11px}
.api-row .sep{margin:0 4px;color:rgba(255,255,255,.06)}
.copy-btn{background:rgba(255,255,255,.05);color:var(--mut);border:1px solid rgba(255,255,255,.06);border-radius:7px;cursor:pointer;transition:all .2s;padding:4px 9px;font-size:10px}
.copy-btn:hover{background:rgba(255,255,255,.11);color:#cbd5e1}
.conn-box{background:rgba(0,0,0,.34);border-radius:12px;padding:16px;font-family:monospace;font-size:12px;line-height:2}
.conn-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.text-bright{color:#e9f0fa}
.text-green{color:#4ade80}
.card-desc{font-size:13px;color:#94a3b8;line-height:1.6;margin-bottom:12px}
.sam-notice{background:rgba(250,204,21,.06);border:1px solid rgba(250,204,21,.16);border-radius:11px;padding:12px;margin-bottom:12px}
.sam-title{font-size:11px;color:#facc15;font-weight:700;margin-bottom:4px}
.sam-text{font-size:11px;color:#94a3b8;line-height:1.6}
.sam-text strong{color:#e9f0fa}
.sch-form{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px}
.sch-table{width:100%;border-collapse:collapse;font-size:12px}
.sch-table th{padding:8px 6px;text-align:left;color:#64748b;font-weight:700;border-bottom:1px solid rgba(255,255,255,.07)}
.sch-table td{padding:8px 6px;border-bottom:1px solid rgba(255,255,255,.05)}
.req-item{display:flex;justify-content:space-between;align-items:center;padding:11px 0;border-bottom:1px solid rgba(255,255,255,.05);gap:10px;transition:background .2s}
.req-item:last-child{border-bottom:none}
.req-item:hover{background:rgba(56,189,248,.04)}
.req-title{font-size:14px;font-weight:600}
.req-meta{font-size:11px;color:#64748b}
.req-msg{font-size:11px;color:#94a3b8;font-style:italic}
.gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px}
.gallery-item{position:relative;border-radius:10px;overflow:hidden;background:rgba(0,0,0,.35);aspect-ratio:3/2}
.gallery-item img,.gallery-item video{width:100%;height:100%;object-fit:cover}
.gallery-del{position:absolute;top:4px;right:4px;padding:2px 6px;font-size:10px;width:auto;background:rgba(248,113,113,.85);color:#fff;border:none;border-radius:4px;cursor:pointer;transition:background .2s}
.gallery-del:hover{background:#ef4444}
.upload-zone{border:1px dashed rgba(56,189,248,.22);border-radius:11px;padding:16px;text-align:center;margin-bottom:12px}
.upload-zone input[type="file"]{display:inline-block;font-size:11px;color:#94a3b8}
.stream-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.05)}
.stream-row:last-child{border-bottom:none}
.stream-name{font-weight:600;font-size:13px}
.stream-meta{font-size:11px;color:#64748b;margin-top:2px}
.stream-status-badge{font-weight:500}
.banner-preview{width:100%;max-height:100px;object-fit:cover;border-radius:8px;margin-bottom:8px}
.alert{background:rgba(74,222,128,.09);border:1px solid rgba(74,222,128,.22);border-radius:12px;padding:10px 14px;color:#4ade80;font-size:13px;margin-bottom:16px}
.alert-error{background:rgba(248,113,113,.09);border:1px solid rgba(248,113,113,.22);color:#f87171}
.color-picker{display:flex;gap:12px;flex-wrap:wrap}
.color-picker label{font-size:11px;color:#94a3b8}
.color-picker input[type="color"]{width:60px;height:40px;padding:2px;cursor:pointer}
.gallery-sub{font-size:11px;color:#64748b;font-weight:400}
.upload-hint{display:block;color:#64748b;margin-top:4px;font-size:10px}
.file-input{font-size:11px;color:#94a3b8;margin-bottom:6px}
.profile-photo-row{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
.avatar-pic{width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid rgba(56,189,248,.2)}
.avatar-placeholder{width:64px;height:64px;border-radius:50%;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;font-size:28px;border:2px solid rgba(255,255,255,.07)}
.upload-btn{display:inline-block;padding:6px 12px;border-radius:9px;background:rgba(56,189,248,.1);border:1px solid rgba(56,189,248,.22);color:#e9f0fa;cursor:pointer;font-size:11px;transition:all .2s}
.upload-btn:hover{background:rgba(56,189,248,.18)}
.empty-text{color:#64748b;font-size:13px}
.save-row{margin-top:12px;text-align:center}
.check-label{display:flex;align-items:center;gap:6px;font-size:12px;color:#cbd5e1;margin-bottom:6px;cursor:pointer}
.check-label input{cursor:pointer}
@media(max-width:760px){.topbar{padding:12px 14px}.topbar h2{font-size:16px}.container{padding:18px 14px 50px}.dj-tabs{width:100%}.grid{grid-template-columns:1fr 1fr}.dj-grid{grid-template-columns:1fr}}
</style></head><body>
<div class="bg"></div>
<div class="topbar">
<h2><span style="width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,#0ea5e9,#7c3aed);display:inline-flex;align-items:center;justify-content:center;font-size:17px;box-shadow:0 4px 16px rgba(14,165,233,.3)">🎤</span> Planet <span style="background:linear-gradient(135deg,#38bdf8,#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent">DJ</span> <span class="tb-badge">STREAM</span></h2>
<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
<?php
// Station picker
$sp = $pdo->prepare("SELECT id, name FROM streaming_stations WHERE user_id=? ORDER BY name");
$sp->execute([$_SESSION['dj_user']['stream_id'] > 10000 ? ($_SESSION['dj_user']['stream_id'] - 10000) : $_SESSION['dj_user']['stream_id']]);
$allStations = $pdo->query("SELECT id, name FROM streaming_stations WHERE user_id IN (SELECT user_id FROM streaming_stations WHERE id=" . ((int)($_SESSION['dj_user']['stream_id'] ?? 0)) . ") ORDER BY name")->fetchAll(PDO::FETCH_OBJ);
if (!$allStations) {
    // Fallback: get by hosting user
    $hu = $pdo->prepare("SELECT user_id FROM streaming_stations WHERE id=?");
    $hu->execute([$_SESSION['dj_user']['stream_id'] ?? 0]);
    $uid = $hu->fetchColumn();
    if ($uid) {
        $allStations = $pdo->prepare("SELECT id, name FROM streaming_stations WHERE user_id=? ORDER BY name");
        $allStations->execute([$uid]);
        $allStations = $allStations->fetchAll(PDO::FETCH_OBJ);
    }
}
if (!empty($allStations) && count($allStations) > 1): ?>
<select onchange="window.location.href='/dj_panel.php?action=dashboard&stream_id='+this.value" style="padding:5px 8px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none">
<?php foreach ($allStations as $s): ?>
<option value="<?=$s->id?>" <?=$s->id==($_SESSION['dj_user']['stream_id']??0)?'selected':''?>><?=htmlspecialchars($s->name)?></option>
<?php endforeach; ?>
</select>
<?php endif; ?>
<span style="font-size:13px;color:#94a3b8"><?php echo htmlspecialchars($_SESSION['dj_user']['name'] ?? ''); ?></span>
<a href="/dj_panel.php?action=logout">Logout</a>
<a href="/studio/index.php" target="_blank" style="color:#a855f7;text-decoration:none;font-size:13px;margin-left:12px">🎛️ Studio</a>
</div>
</div>
<div class="container">

<?php if ($success): ?><div class="alert"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if (!empty($_SESSION['dj_user']['on_leave'])): ?>
<div class="alert" style="background:rgba(250,204,21,.1);border:1px solid rgba(250,204,21,.2);color:#facc15">🌴 You are currently <strong>On Leave</strong>. You can log in and manage your profile, but streaming / DJ takeover is disabled. Contact the station owner to reactivate.</div>
<?php endif; ?>

<div class="dj-tabs">
    <div class="dj-tab act" onclick="sw(event,'overview')">Overview</div>
    <?php
    // Add station tabs for all assigned streams
    $userId = $_SESSION['dj_user']['id'] ?? 0;
    $isOwner = !empty($_SESSION['dj_user']['is_owner']);
    if ($isOwner || $userId > 0) {
        // Get all streams for this user (owner mode) or DJ (non-owner)
        if ($isOwner) {
            $userId = $hostingId ?? 0;
            $stationQuery = $pdo->prepare("SELECT id, name, engine, port, dj_port, mount_point, bitrate, status FROM streaming_stations WHERE user_id=? ORDER BY name");
            $stationQuery->execute([$userId]);
        } else {
            // DJ login: only streams assigned to this DJ (primary + radio_dj_streams junction)
            $djId = (int)($_SESSION['dj_user']['id'] ?? 0);
            $stationQuery = $pdo->prepare("SELECT DISTINCT ss.id, ss.name, ss.engine, ss.port, ss.dj_port, ss.mount_point, ss.bitrate, ss.status
                FROM streaming_stations ss
                LEFT JOIN radio_dj_streams rjds ON rjds.stream_id = ss.id
                WHERE ss.id = (SELECT stream_id FROM radio_djs WHERE id = ?) OR rjds.dj_id = ?
                ORDER BY ss.name");
            $stationQuery->execute([$djId, $djId]);
        }
        $userStations = $stationQuery->fetchAll(PDO::FETCH_OBJ);
        
        if (!empty($userStations)) {
            foreach ($userStations as $index => $station) {
                $tabId = $index + 1; // 1, 2, 3, etc.
                $tabName = $station->name ?? "Stream #{$station->id}";
                if ($station->id == $_SESSION['dj_user']['stream_id']) {
                    echo "<div class=\"dj-tab active-station act\" onclick=\"sw(event,'station-{$station->id}')\">" . htmlspecialchars($tabName) . "</div>";
                } else {
                    echo "<div class=\"dj-tab active-station\" onclick=\"sw(event,'station-{$station->id}')\">" . htmlspecialchars($tabName) . "</div>";
                }
            }
        }
    }
    ?>
    <div class="dj-tab" onclick="sw(event,'schedule')">Schedule</div>
    <div class="dj-tab" onclick="sw(event,'requests')">Requests</div>
    <div class="dj-tab" onclick="sw(event,'downloads')">Downloads</div>
    <div class="dj-tab" onclick="sw(event,'profile')">Profile</div>
    <div class="dj-tab" onclick="sw(event,'gallery')">Gallery</div>
    <div class="dj-tab" onclick="sw(event,'api')">API</div>
</div>

<!-- Station tabs content -->
    <?php
    $userId = $_SESSION['dj_user']['id'] ?? 0;
    $isOwner = !empty($_SESSION['dj_user']['is_owner']);
    if ($isOwner || $userId > 0) {
        if ($isOwner) {
            $userId = $hostingId ?? 0;
            $stationQuery = $pdo->prepare("SELECT id, name, engine, port, dj_port, mount_point, bitrate, status FROM streaming_stations WHERE user_id=? ORDER BY name");
            $stationQuery->execute([$userId]);
        } else {
            // DJ login: only streams assigned to this DJ (primary + radio_dj_streams junction)
            $djId = (int)($_SESSION['dj_user']['id'] ?? 0);
            $stationQuery = $pdo->prepare("SELECT DISTINCT ss.id, ss.name, ss.engine, ss.port, ss.dj_port, ss.mount_point, ss.bitrate, ss.status
                FROM streaming_stations ss
                LEFT JOIN radio_dj_streams rjds ON rjds.stream_id = ss.id
                WHERE ss.id = (SELECT stream_id FROM radio_djs WHERE id = ?) OR rjds.dj_id = ?
                ORDER BY ss.name");
            $stationQuery->execute([$djId, $djId]);
        }
        $userStations = $stationQuery->fetchAll(PDO::FETCH_OBJ);
        $djHost = 'planet-hosts.com';
        
        foreach ($userStations as $station) {
            $stationId = $station->id;
            $isActiveStation = $stationId == $_SESSION['dj_user']['stream_id'];
            echo "<div class=\"dj-panel" . ($isActiveStation ? ' act' : '') . "\" id=\"pn-station-{$stationId}\">\n";
            
            // Full Broadcaster Info card for each station
            $sPort = $station->dj_port ?? $station->port ?? 8000;
            $sPass = $station->plain_password ?? '';
            $sEngine = strtolower($station->engine ?? 'icecast');
            $sLabel = ($sEngine === 'shoutcast' || $sEngine === 'shoutcast1' || $sEngine === 'shoutcast2') ? 'SHOUTcast' : 'Icecast';
            $sUser = $_SESSION['dj_user']['username'] ?? '';
            $djName = htmlspecialchars($station->name ?? "Stream #{$stationId}");
            $djUserE = htmlspecialchars($sUser);
            
            echo "<div style=\"background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.1);border-radius:14px;padding:18px;margin-bottom:14px\">\n";
            echo "<div style=\"display:flex;align-items:center;gap:8px;margin-bottom:12px\">\n";
            echo "<div style=\"width:32px;height:32px;border-radius:8px;background:rgba(56,189,248,.12);display:flex;align-items:center;justify-content:center;font-size:16px\">📡</div>\n";
            echo "<div><div style=\"font-size:14px;font-weight:700;color:#e0e0e0\">Broadcaster Info — {$djName}</div><div style=\"font-size:10px;color:#64748b\">{$sLabel} · Port {$station->port} · <span style=\"color:" . ($station->status === 'running' ? '#4ade80' : '#f87171') . "\">{$station->status}</span></div></div>\n";
            echo "</div>\n";
            
            // SAM Credentials
            $sp = $pdo->prepare("SELECT plain_password FROM radio_djs WHERE id=?");
            $sp->execute([$_SESSION['dj_user']['id'] ?? 0]);
            $pw = $sp->fetchColumn() ?: 'password';
            
            echo "<div style=\"background:rgba(250,204,21,.06);border:1px solid rgba(250,204,21,.15);border-radius:8px;padding:10px;margin-bottom:10px\">\n";
            echo "<div style=\"font-size:11px;color:#facc15;font-weight:600;margin-bottom:4px\">📻 SAM Users</div>\n";
            echo "<div style=\"font-size:11px;color:#94a3b8\">Enter as <strong style=\"color:#e0e0e0\">djusername:djpassword</strong> in the <strong style=\"color:#e0e0e0\">Password</strong> field.</div>\n";
            echo "<div style=\"margin-top:6px;display:flex;gap:6px;align-items:center;background:rgba(0,0,0,.3);border-radius:6px;padding:6px 10px;font-family:monospace;font-size:12px\">\n";
            echo "<span style=\"color:#4ade80\" id=\"sam-user-{$stationId}\">{$djUserE}</span>\n";
            echo "<span style=\"color:#facc15\">:</span>\n";
            echo "<span style=\"color:#facc15\" id=\"sam-pass-display-{$stationId}\">••••••••</span>\n";
            echo "<span style=\"color:#facc15;display:none\" id=\"sam-pass-value-{$stationId}\">" . htmlspecialchars($pw) . "</span>\n";
            echo "<button class=\"copy-btn\" onclick=\"sc2({$stationId})\">Copy</button>\n";
            echo "<button class=\"copy-btn\" onclick=\"stp2({$stationId})\" id=\"sam-toggle-{$stationId}\">Show</button>\n";
            echo "</div></div>\n";
            
            // Connection details
            echo "<div style=\"background:rgba(0,0,0,.3);border-radius:10px;padding:14px;font-family:monospace;font-size:12px\">\n";
            echo "<div style=\"display:flex;justify-content:space-between;align-items:center;padding:4px 0\"><span style=\"color:#64748b\">Server:</span><span style=\"color:#4ade80\" id=\"bi-server-{$stationId}\">{$djHost}</span><button class=\"copy-btn\" onclick=\"cf('bi-server-{$stationId}')\">Copy</button></div>\n";
            echo "<div style=\"display:flex;justify-content:space-between;align-items:center;padding:4px 0\"><span style=\"color:#64748b\">DJ Port:</span><span style=\"color:#38bdf8\" id=\"bi-port-{$stationId}\">{$sPort}</span><button class=\"copy-btn\" onclick=\"cf('bi-port-{$stationId}')\">Copy</button></div>\n";
            // Mount point is required for Icecast and SHOUTcast v2 (not used by SHOUTcast v1)
            $mountPoint = $station->mount_point ?? '';
            if ($mountPoint !== '') {
                if (!str_starts_with($mountPoint, '/')) $mountPoint = '/' . $mountPoint;
                echo "<div style=\"display:flex;justify-content:space-between;align-items:center;padding:4px 0\"><span style=\"color:#64748b\">Mount:</span><span style=\"color:#38bdf8\" id=\"bi-mount-{$stationId}\">{$mountPoint}</span><button class=\"copy-btn\" onclick=\"cf('bi-mount-{$stationId}')\">Copy</button></div>\n";
            }
            echo "<div style=\"display:flex;justify-content:space-between;align-items:center;padding:4px 0\"><span style=\"color:#64748b\">User:</span><span style=\"color:#38bdf8\">{$djUserE}</span></div>\n";
            echo "<div style=\"display:flex;justify-content:space-between;align-items:center;padding:4px 0\"><span style=\"color:#64748b\">Password:</span><span style=\"color:#facc15\" id=\"bi-pass-{$stationId}\">" . ($isOwner ? htmlspecialchars($pw) : '••••••••') . "</span><button class=\"copy-btn\" onclick=\"tp2({$stationId})\">" . ($isOwner ? 'Hide' : 'Show') . "</button></div>\n";
            echo "<div style=\"display:flex;justify-content:space-between;align-items:center;padding:4px 0\"><span style=\"color:#64748b\">Format:</span><span style=\"color:#94a3b8\">MP3 · " . ((int)($station->bitrate ?? 128)) . " kbps</span></div>\n";
            echo "</div>\n";
            
            // Copy All and Kick buttons
            echo "<div style=\"display:flex;gap:6px;margin-top:10px\">\n";
            if (!empty($_SESSION['dj_user']['on_leave'])) {
                echo "<div style=\"font-size:12px;color:#facc15;background:rgba(250,204,21,.08);border:1px solid rgba(250,204,21,.2);border-radius:8px;padding:10px;width:100%\">🌴 On Leave — streaming &amp; DJ controls are disabled. You can still manage your profile.</div>\n";
            } else {
                echo "<button class=\"btn btn-primary btn-sm\" onclick=\"ca2({$stationId},'{$djHost}','{$sPort}','{$djUserE}','" . htmlspecialchars($pw) . "','" . addslashes($mountPoint) . "')\">📋 Copy All</button>\n";
                echo "<button class=\"btn btn-danger btn-sm\" onclick=\"window.location.href='/dj_panel.php?action=takeover'\">🎤 Stop AutoDJ</button>\n";
            }
            echo "</div>\n";
            echo "</div>\n";
            echo "</div>\n";
        }
    }
    ?>



<div class="dj-panel act" id="pn-overview">
<?php
$streamId = $djData->stream_id ?? 0;
$ss = $pdo->prepare("SELECT * FROM streaming_stations WHERE id = ?");
$ss->execute([$streamId]);
$station = $ss->fetch(PDO::FETCH_OBJ);
$djPort = $station->dj_port ?? $station->port ?? 8000;
// Live-probe the actual Icecast/Shoutcast engine — authoritive ON AIR state
$probe = probe_stream($pdo, $station);
$autoDjOn   = (int)($station->autodj_enabled ?? 0);
$liveNow    = !$autoDjOn && $probe['live'];
$srcState   = $autoDjOn ? 'autodj' : ($probe['live'] ? 'live' : 'offline');
$srcLabel   = ['autodj' => 'AutoDJ', 'live' => 'Live DJ', 'offline' => 'Offline'][$srcState] ?? 'Offline';
$monSong    = $probe['song'] ?: ($station->current_song ?? '');
$monListeners = $probe['listeners'] ?: (int)($station->listener_count ?? 0);
// The DJ's own login password (radio_djs.plain_password), not the station source password
$djPass = $pdo->prepare("SELECT plain_password FROM radio_djs WHERE id=?");
$djPass->execute([$_SESSION['dj_user']['id'] ?? 0]);
$djPass = $djPass->fetchColumn() ?: '';
$djHost = 'planet-hosts.com';
$djUsername = $_SESSION['dj_user']['username'] ?? '';
$isOwner = !empty($_SESSION['dj_user']['is_owner']);

// Get streams for kick feature
$hSt = $pdo->prepare("SELECT user_id FROM streaming_stations WHERE id=?");
$hSt->execute([$streamId]);
$hRow = $hSt->fetch(PDO::FETCH_OBJ);
$hostingId = $hRow->user_id ?? 0;
$userStreams = $pdo->prepare("SELECT id, name, engine, port, dj_port, mount_point, bitrate, status FROM streaming_stations WHERE user_id=? ORDER BY id");
$userStreams->execute([$hostingId]);
$myStreams = $userStreams->fetchAll(PDO::FETCH_OBJ);
?>

<!-- Live Monitor -->
<div class="hero">
<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;position:relative">
<div style="flex:1;min-width:200px">
<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
<span class="pill <?php echo $srcState === 'live' ? 'live' : ($srcState === 'autodj' ? 'autodj' : 'off'); ?>" id="dj-source-status"><span class="livedot"></span><?php echo $srcLabel; ?></span>
<span style="font-size:11px;color:#64748b"><?php echo htmlspecialchars($station->name ?? 'Stream'); ?> · <?php echo strtoupper($probe['engine']); ?><?php echo $probe['live'] ? ' · source connected' : ($station->status === 'running' ? ' · server running' : ' · server stopped'); ?></span>
</div>
<div class="np-title" id="dj-np-song" style="margin-top:10px"><?php echo htmlspecialchars($monSong ?: ($djData->current_dj ? 'Live with ' . $djData->current_dj : 'Station is idle — waiting for a source')); ?></div>
<div class="np-sub" id="dj-np-sub" style="margin-top:3px"><?php echo $liveNow || $djData->current_dj ? ('On air with <b style="color:#a78bfa">' . htmlspecialchars($station->current_dj ?? $djData->current_dj ?? '') . '</b>') : ($autoDjOn ? 'AutoDJ is playing the playlist' : 'No source connected'); ?></div>
</div>
<div style="text-align:right">
<div style="font-size:34px;font-weight:800;color:<?php echo $liveNow ? '#34d399' : '#38bdf8'; ?>" id="dj-live-listeners"><?php echo (int)$monListeners; ?></div>
<div style="font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600">Listeners now</div>
</div>
</div>
</div>

<div class="mgrid">
  <div class="tile"><div class="ticon">🌐</div><div class="tnum" style="color:#4ade80"><?php echo htmlspecialchars($djData->stream_status ?? '—'); ?></div><div class="tlabel">Status</div></div>
  <div class="tile"><div class="ticon">📻</div><div class="tnum" style="color:#38bdf8" id="dj-stat-listeners"><?php echo (int)$monListeners; ?></div><div class="tlabel">Listeners</div></div>
  <div class="tile"><div class="ticon">🎵</div><div class="tnum" style="color:#facc15"><?php echo (int)($djData->track_count ?? 0); ?></div><div class="tlabel">Tracks</div></div>
  <div class="tile"><div class="ticon">🎙️</div><div class="tnum" style="color:#a78bfa" id="src-tile"><?php echo $srcLabel; ?></div><div class="tlabel">Source</div></div>
</div>

<!-- Stream Player -->
<?php
$compId = 10000 + (int)($station->id ?? 0);
$streamUrl = "/radio/stream-proxy.php?stream={$compId}";
$playerId = "player-" . ($station->id ?? 0);
?>
<div style="background:linear-gradient(135deg,rgba(15,23,42,.6),rgba(30,41,59,.4));border:1px solid rgba(56,189,248,.12);border-radius:20px;padding:20px;margin-bottom:16px;position:relative;overflow:hidden">
<div style="position:absolute;top:-60px;right:-60px;width:200px;height:200px;background:radial-gradient(circle,rgba(56,189,248,.08),transparent 70%);pointer-events:none"></div>
<div style="position:absolute;bottom:-40px;left:-40px;width:150px;height:150px;background:radial-gradient(circle,rgba(168,85,247,.06),transparent 70%);pointer-events:none"></div>
<div style="display:flex;gap:18px;align-items:center;flex-wrap:wrap;position:relative">
<div style="width:80px;height:80px;border-radius:16px;background:linear-gradient(135deg,#008cff,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:36px;flex-shrink:0;box-shadow:0 8px 32px rgba(0,140,255,.25);position:relative;overflow:hidden">
<div id="<?=$playerId?>-eq" style="position:absolute;bottom:4px;left:4px;right:4px;display:flex;gap:2px;justify-content:center;align-items:flex-end;height:16px">
<span style="width:3px;background:#fff;border-radius:2px;height:40%;animation:eq 0.8s ease-in-out infinite alternate;animation-delay:0s"></span>
<span style="width:3px;background:#fff;border-radius:2px;height:70%;animation:eq 1.0s ease-in-out infinite alternate;animation-delay:0.2s"></span>
<span style="width:3px;background:#fff;border-radius:2px;height:50%;animation:eq 0.6s ease-in-out infinite alternate;animation-delay:0.4s"></span>
<span style="width:3px;background:#fff;border-radius:2px;height:90%;animation:eq 1.2s ease-in-out infinite alternate;animation-delay:0.1s"></span>
<span style="width:3px;background:#fff;border-radius:2px;height:60%;animation:eq 0.7s ease-in-out infinite alternate;animation-delay:0.3s"></span>
</div>
</div>
<div style="flex:1;min-width:150px">
<div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:1px;font-weight:600">Now Playing</div>
<div id="<?=$playerId?>-song" style="font-size:16px;font-weight:700;color:#e0e0e0;margin:2px 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo htmlspecialchars($station->current_song ?? 'Station offline'); ?></div>
<div id="<?=$playerId?>-artist" style="font-size:12px;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo htmlspecialchars($station->current_artist ?? ''); ?></div>
</div>
<div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
<div style="text-align:center">
<div style="font-size:20px;font-weight:800;color:#38bdf8"><?php echo (int)($station->listener_count ?? 0); ?></div>
<div style="font-size:9px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;font-weight:600">Listeners</div>
</div>
<button id="<?=$playerId?>-btn" class="btn" style="width:44px;height:44px;border-radius:50%;padding:0;display:flex;align-items:center;justify-content:center;font-size:18px;background:linear-gradient(135deg,#008cff,#38bdf8);color:#fff;box-shadow:0 4px 16px rgba(0,140,255,.3);border:none;cursor:pointer;transition:all .2s" onclick="togglePlayer('<?=$playerId?>','<?=$streamUrl?>')">▶</button>
</div>
</div>
<div style="margin-top:12px;display:flex;align-items:center;gap:10px;position:relative">
<span style="font-size:10px;color:#64748b;width:30px;text-align:right" id="<?=$playerId?>-time">0:00</span>
<div style="flex:1;height:4px;background:rgba(255,255,255,.08);border-radius:4px;overflow:hidden;cursor:pointer" id="<?=$playerId?>-progress-bg" onclick="seekPlayer(event,'<?=$playerId?>')">
<div id="<?=$playerId?>-progress" style="width:0%;height:100%;background:linear-gradient(90deg,#008cff,#7c3aed);border-radius:4px;transition:width .3s"></div>
</div>
<span style="font-size:10px;color:#64748b;width:30px" id="<?=$playerId?>-duration">0:00</span>
<input type="range" id="<?=$playerId?>-volume" min="0" max="1" step="0.05" value="0.7" style="width:60px;height:4px;appearance:none;background:rgba(255,255,255,.12);border-radius:4px;outline:none;cursor:pointer" oninput="setVolume('<?=$playerId?>',this.value)">
</div>
<audio id="<?=$playerId?>-audio" style="display:none" preload="none" ontimeupdate="updatePlayer('<?=$playerId?>')" onended="playerEnded('<?=$playerId?>')" onerror="playerError('<?=$playerId?>')"></audio>
</div>

<style>
@keyframes eq{0%{height:20%}100%{height:90%}}
#<?=$playerId?>-volume::-webkit-slider-thumb{appearance:none;width:12px;height:12px;border-radius:50%;background:#38bdf8;cursor:pointer}
</style>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">

<!-- Broadcaster Info -->
<div style="background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.1);border-radius:14px;padding:18px">
<div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
<div style="width:32px;height:32px;border-radius:8px;background:rgba(56,189,248,.12);display:flex;align-items:center;justify-content:center;font-size:16px">📡</div>
<div><div style="font-size:14px;font-weight:700;color:#e0e0e0">Broadcaster Info</div><div style="font-size:10px;color:#64748b">Connection details for your encoder</div></div>
</div>
<div class="sam-notice">
<div class="sam-title">📻 SAM Users</div>
<div class="sam-text">Enter as <strong class="text-bright">djusername:djpassword</strong> in the <strong class="text-bright">Password</strong> field.</div>
<div style="margin-top:6px;display:flex;gap:6px;align-items:center;background:rgba(0,0,0,.3);border-radius:6px;padding:6px 10px;font-family:monospace;font-size:12px">
<span style="color:#4ade80;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" id="sam-user"><?php echo htmlspecialchars($djUsername); ?></span>
<span style="color:#facc15" id="sam-pass-sep">:</span>
<span style="color:#facc15" id="sam-pass-display">••••••••</span>
<span style="color:#facc15;display:none" id="sam-pass-value"><?php 
$samPass = $_SESSION['dj_user']['plain_password'] ?? '';
if (!$samPass) {
    $sp = $pdo->prepare("SELECT plain_password FROM radio_djs WHERE id=?");
    $sp->execute([$_SESSION['dj_user']['id'] ?? 0]);
    $samPass = $sp->fetchColumn() ?: 'password';
}
echo htmlspecialchars($samPass);
?></span>
<button class="copy-btn" onclick="sc()">Copy</button>
<button class="copy-btn" onclick="stp()" id="sam-toggle-btn">Show</button>
</div>
</div>
<div style="background:rgba(0,0,0,.3);border-radius:10px;padding:14px;font-family:monospace;font-size:12px">
<div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0"><span style="color:#64748b">Server:</span><span style="color:#4ade80" id="bi-server"><?php echo $djHost; ?></span><button class="copy-btn" onclick="cf('bi-server')">Copy</button></div>
<div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0"><span style="color:#64748b">DJ Port:</span><span style="color:#38bdf8" id="bi-port"><?php echo $djPort; ?></span><button class="copy-btn" onclick="cf('bi-port')">Copy</button></div>
<div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0"><span style="color:#64748b">User:</span><span style="color:#38bdf8" id="bi-user"><?php echo htmlspecialchars($djUsername); ?></span><button class="copy-btn" onclick="cf('bi-user')">Copy</button></div>
<div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0"><span style="color:#64748b">Password:</span><span style="color:#facc15" id="bi-pass"><?php echo $isOwner ? htmlspecialchars($djPass) : '••••••••'; ?></span><button class="copy-btn" onclick="tp()"><?php echo $isOwner ? 'Hide' : 'Show'; ?></button></div>
<div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0"><span style="color:#64748b">Format:</span><span style="color:#94a3b8">MP3 · <?php echo $station->bitrate ?? 128; ?> kbps</span></div>
</div>
<div style="display:flex;gap:6px;margin-top:10px">
<?php if (!empty($_SESSION['dj_user']['on_leave'])): ?>
<div style="font-size:12px;color:#facc15;background:rgba(250,204,21,.08);border:1px solid rgba(250,204,21,.2);border-radius:8px;padding:10px;width:100%">🌴 On Leave — streaming &amp; DJ controls are disabled. You can still manage your profile.</div>
<?php else: ?>
<button class="btn btn-primary btn-sm" onclick="ca()">📋 Copy All</button>
<button class="btn btn-danger btn-sm" onclick="window.location.href='/dj_panel.php?action=takeover'">🎤 Stop AutoDJ</button>
<?php endif; ?>
</div>
</div>

<!-- API Connection + Live Status -->
<div style="display:flex;flex-direction:column;gap:14px">
<div style="background:rgba(15,23,42,.5);border:1px solid rgba(168,85,247,.1);border-radius:14px;padding:18px">
<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
<div style="width:32px;height:32px;border-radius:8px;background:rgba(168,85,247,.12);display:flex;align-items:center;justify-content:center;font-size:16px">🔌</div>
<div><div style="font-size:14px;font-weight:700;color:#e0e0e0">API</div><div style="font-size:10px;color:#64748b">Programmatic access</div></div>
</div>
<div style="background:rgba(0,0,0,.3);border-radius:10px;padding:14px;font-family:monospace;font-size:12px">
<div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0"><span style="color:#64748b">Base URL:</span><span style="color:#a855f7;font-size:11px" id="api-base">/api/studio/station/<?php echo $_SESSION['dj_user']['stream_id'] ?? 0; ?></span><button class="copy-btn" onclick="cf('api-base')">Copy</button></div>
<div style="font-size:10px;color:#64748b;margin-top:4px"><code style="color:#a855f7">GET /connection</code> · <code style="color:#a855f7">GET /djs</code></div>
</div>
</div>

<!-- Live Now Status (probe-based) -->
<div style="background:var(--panel);border:1px solid rgba(74,222,128,.14);border-radius:14px;padding:18px">
<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
<div style="width:32px;height:32px;border-radius:50%;background:<?php echo $liveNow ? 'rgba(74,222,128,.15)' : 'rgba(100,116,139,.12)'; ?>;display:flex;align-items:center;justify-content:center;font-size:16px"><?php echo $liveNow ? '🔴' : '⏹'; ?></div>
<div><div style="font-size:14px;font-weight:700;color:#e0e0e0" id="live-now-title"><?php echo $liveNow ? 'Live Now' : ($autoDjOn ? 'AutoDJ on air' : 'Offline'); ?></div><div style="font-size:10px;color:#64748b" id="live-now-sub"><?php echo $liveNow ? htmlspecialchars($station->current_dj ?? $djData->current_dj ?? 'Live source') : ($autoDjOn ? 'Playlist is playing' : 'Connect your encoder to go live'); ?></div></div>
</div>
<div style="background:rgba(0,0,0,.3);border-radius:8px;padding:10px;font-size:12px;line-height:1.6">
<div><span style="color:#64748b">Song:</span> <span style="color:#e0e0e0" id="live-now-song"><?php echo htmlspecialchars($monSong ?: '—'); ?></span></div>
<div><span style="color:#64748b">Listeners:</span> <span style="color:#4ade80" id="live-now-listeners"><?php echo (int)$monListeners; ?></span></div>
</div>
</div>
</div>

</div>

<!-- Banner & Upload row -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">

<div style="background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.06);border-radius:14px;overflow:hidden;position:relative;min-height:120px;display:flex;align-items:center;justify-content:center">
<?php if ($djData->banner): ?>
<img src="/<?php echo $djData->banner; ?>" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0">
<?php else: ?>
<div style="text-align:center;color:#475569"><i class="fas fa-image" style="font-size:28px;opacity:.3;display:block;margin-bottom:4px"></i><span style="font-size:12px">No Banner</span></div>
<?php endif; ?>
</div>

<div style="background:rgba(15,23,42,.5);border:1px solid rgba(250,204,21,.08);border-radius:14px;padding:16px">
<div style="font-size:13px;font-weight:700;color:#e0e0e0;margin-bottom:8px">📷 Profile Banner</div>
<?php if ($djData->banner): ?>
<img src="/<?php echo $djData->banner; ?>" style="width:100%;max-height:60px;object-fit:cover;border-radius:6px;margin-bottom:6px">
<?php endif; ?>
<form method="POST" enctype="multipart/form-data" style="display:flex;gap:6px">
<input type="hidden" name="action" value="upload_banner">
<input type="file" name="file" accept="image/*" style="flex:1;font-size:11px;color:#94a3b8;padding:4px 0">
<button class="btn btn-warning btn-sm">Upload</button>
</form>
</div>

</div>

<!-- Kick Source -->
<div style="background:rgba(15,23,42,.5);border:1px solid rgba(248,113,113,.1);border-radius:14px;padding:16px;margin-bottom:14px">
<div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
<div style="width:32px;height:32px;border-radius:8px;background:rgba(248,113,113,.12);display:flex;align-items:center;justify-content:center;font-size:16px">⛔</div>
<div><div style="font-size:14px;font-weight:700;color:#e0e0e0">Kick Source</div><div style="font-size:10px;color:#64748b">Force-disconnect current source from a stream</div></div>
</div>
<?php if (empty($myStreams)): ?>
<p class="empty-text">No streams available.</p>
<?php else: ?>
<?php foreach ($myStreams as $st): 
  $stEngine = strtolower($st->engine ?? $st->server_type ?? 'icecast');
  $stLabel = strtoupper($stEngine === 'shoutcast' || $stEngine === 'shoutcast1' || $stEngine === 'shoutcast2' ? 'SHOUTcast' : 'Icecast');
?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04)">
<div><div style="font-weight:600;font-size:13px;color:#e0e0e0"><?php echo htmlspecialchars($st->name ?? "Stream #{$st->id}"); ?></div>
<div style="font-size:11px;color:#64748b"><?php echo $stLabel; ?> · Port <?php echo $st->port; ?> · <span style="color:<?php echo $st->status === 'running' ? '#4ade80' : '#f87171'; ?>"><?php echo $st->status; ?></span></div></div>
<?php if (!empty($_SESSION['dj_user']['on_leave'])): ?>
<span style="font-size:10px;color:#facc15">🔒 On leave</span>
<?php else: ?>
<form method="POST" action="/dj_panel.php?action=kick" onsubmit="return confirm('Kick source on <?php echo htmlspecialchars($st->name ?? 'this stream'); ?>?');">
<input type="hidden" name="stream_id" value="<?php echo $st->id; ?>">
<button class="btn btn-danger btn-sm">Kick</button>
</form>
<?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<script>
function cf(id){var t=document.getElementById(id).textContent;navigator.clipboard.writeText(t);var b=event.target;b.textContent='Copied!';setTimeout(function(){b.textContent='Copy'},1500);}
function sc(){var u=document.getElementById('sam-user').textContent,p=document.getElementById('sam-pass-value').textContent;navigator.clipboard.writeText(u+':'+p);event.target.textContent='Copied!';setTimeout(function(){event.target.textContent='Copy'},1500);}
function stp(){var p=document.getElementById('sam-pass-display'),v=document.getElementById('sam-pass-value'),b=document.getElementById('sam-toggle-btn');if(p.style.display==='none'){p.style.display='';v.style.display='none';b.textContent='Show'}else{p.style.display='none';v.style.display='';b.textContent='Hide'}}
function tp(){var p=document.getElementById('bi-pass');if(p.textContent=='••••••••'){p.textContent='<?php echo addslashes($djPass); ?>';event.target.textContent='Hide'}else{p.textContent='••••••••';event.target.textContent='Show'}}
function ca(){navigator.clipboard.writeText('Server: <?php echo addslashes($djHost); ?>\nPort: <?php echo $djPort; ?>\nUsername: <?php echo addslashes($djUsername); ?>\nPassword: <?php echo $isOwner ? addslashes($djPass) : '<your DJ password>'; ?>\nFormat: MP3 <?php echo $station->bitrate ?? 128; ?>kbps');event.target.textContent='Copied!';setTimeout(function(){event.target.textContent='📋 Copy All'},2000);}
function sc2(s){var u=document.getElementById('sam-user-'+s).textContent,p=document.getElementById('sam-pass-value-'+s).textContent;navigator.clipboard.writeText(u+':'+p);event.target.textContent='Copied!';setTimeout(function(){event.target.textContent='Copy'},1500);}
function stp2(s){var p=document.getElementById('sam-pass-display-'+s),v=document.getElementById('sam-pass-value-'+s),b=document.getElementById('sam-toggle-'+s);if(p.style.display==='none'){p.style.display='';v.style.display='none';b.textContent='Show'}else{p.style.display='none';v.style.display='';b.textContent='Hide'}}
function tp2(s){var p=document.getElementById('bi-pass-'+s);if(p.textContent=='••••••••'){p.textContent='<?php echo addslashes($djPass); ?>';event.target.textContent='Hide'}else{p.textContent='••••••••';event.target.textContent='Show'}}
function ca2(s,h,pt,u,pw,m){m=m||'';navigator.clipboard.writeText('Server: '+h+'\nDJ Port: '+pt+(m?'\nMount: '+m:'')+'\nUsername: '+u+'\nPassword: '+pw+'\nFormat: MP3 128kbps');event.target.textContent='Copied!';setTimeout(function(){event.target.textContent='📋 Copy All'},2000);}
</script>
</div>
</div>

<div class="dj-panel" id="pn-schedule">
<?php
$sId = $_SESSION['dj_user']['stream_id'] ?? 0;
$djId = $_SESSION['dj_user']['id'] ?? 0;

// Fetch all schedule entries for this station
$mySchedule = [];
try {
    $schStmt = $pdo->prepare("SELECT * FROM radio_dj_schedule WHERE stream_id = ? AND (dj_id = ? OR dj_id = 0 OR dj_id IS NULL) ORDER BY day_of_week, start_time");
    $schStmt->execute([$sId, $djId]);
    $mySchedule = $schStmt->fetchAll(PDO::FETCH_OBJ);
} catch (\Exception $e) {}

// Build calendar data
$month = (int)($_GET['sched_month'] ?? date('n'));
$year = (int)($_GET['sched_year'] ?? date('Y'));
if ($month < 1) { $month = 1; $year--; }
if ($month > 12) { $month = 12; $year++; }
$firstDay = mktime(0,0,0,$month,1,$year);
$daysInMonth = date('t', $firstDay);
$startWeekday = date('w', $firstDay);
$prevMonth = $month - 1; $prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1; $nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
$dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
$fullDayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
?>
<div style="max-width:700px;margin:0 auto">
<div class="card" style="text-align:center">
<h3><i class="fas fa-calendar-alt"></i> My Schedule — <?php echo date('F Y', $firstDay); ?></h3>
<div style="display:flex;justify-content:center;gap:8px;margin-bottom:16px">
<a href="?action=dashboard&tab=schedule&sched_month=<?=$prevMonth?>&sched_year=<?=$prevYear?>" class="btn btn-sm btn-secondary">◀ Prev</a>
<a href="?action=dashboard&tab=schedule&sched_month=<?=$nextMonth?>&sched_year=<?=$nextYear?>" class="btn btn-sm btn-secondary">Next ▶</a>
</div>
<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;max-width:500px;margin:0 auto">
<?php foreach ($dayNames as $dn): ?>
<div style="text-align:center;font-size:11px;color:#64748b;font-weight:600;padding:4px 0"><?=$dn?></div>
<?php endforeach; ?>
<?php for ($i=0; $i<$startWeekday; $i++): ?>
<div></div>
<?php endfor; ?>
<?php for ($d=1; $d<=$daysInMonth; $d++): 
    $ts = mktime(0,0,0,$month,$d,$year);
    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
    $daySched = array_filter($mySchedule, function($s) use ($dateStr) { return ($s->scheduled_date ?? '') === $dateStr; });
    $isBooked = !empty($daySched);
    $isToday = (date('Y-m-d') === $dateStr);
?>
<div style="text-align:center;padding:6px 2px;border-radius:8px;background:<?=$isBooked?'rgba(74,222,128,.15)':($isToday?'rgba(56,189,248,.1)':'rgba(0,0,0,.15)')?>;border:1px solid <?=$isToday?'rgba(56,189,248,.3)':'transparent'?>;cursor:pointer;font-size:12px;position:relative" onclick="toggleDate(this,'<?=$dateStr?>')" title="<?=$isBooked?'Click to unbook':'Click to book'?>">
<div style="font-weight:<?=$isToday?'700':'400'?>;color:<?=$isBooked?'#4ade80':($isToday?'#38bdf8':'#94a3b8')?>"><?=$d?></div>
<?php if ($isBooked): $firstSched = reset($daySched); ?>
<div style="font-size:8px;color:#4ade80;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($firstSched->show_name??'Booked')?></div>
<?php endif; ?>
</div>
<?php endfor; ?>
</div>

<!-- Show details for selected date -->
<div id="sched-detail" style="display:none;margin-top:16px;padding:16px;background:rgba(0,0,0,.2);border-radius:10px;text-align:center"></div>

<!-- Add schedule form (hidden, shown on date click) -->
<div id="sched-form" style="display:none;margin-top:12px;padding:16px;background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.1);border-radius:12px;max-width:400px;margin-left:auto;margin-right:auto">
<h4 style="margin-bottom:10px;color:#38bdf8">📅 Book Show</h4>
<form method="POST" action="/dj_panel.php?action=add_schedule" onsubmit="document.getElementById('sched_date_input').value=document.getElementById('sched-form').dataset.date">
<input type="hidden" name="scheduled_date" id="sched_date_input">
<div class="form-group"><label>Show Name</label><input name="show_name" id="sched_name" required placeholder="My Show"></div>
<div style="display:flex;gap:8px">
<div class="form-group" style="flex:1"><label>Start</label><input name="start_time" id="sched_start" type="time" required></div>
<div class="form-group" style="flex:1"><label>End</label><input name="end_time" id="sched_end" type="time" required></div>
</div>
<button type="submit" class="btn btn-primary btn-sm" style="width:100%">Book Show</button>
</form>
</div>

<?php if (!empty($mySchedule)): ?>
<div style="margin-top:20px;text-align:center">
<h4 style="font-size:13px;color:#94a3b8;margin-bottom:8px">All Shows</h4>
<div style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center">
<?php 
$shown = [];
foreach ($mySchedule as $sh): 
    $key = $sh->scheduled_date . '_' . $sh->time_slot;
    if (in_array($key, $shown)) continue;
    $shown[] = $key;
?>
<div style="background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.12);border-radius:8px;padding:8px 12px;font-size:11px;text-align:center">
<div style="font-weight:600;color:#e0e0e0;font-size:12px"><?=htmlspecialchars($sh->show_name??'Show')?></div>
<div style="color:#64748b;font-size:10px"><?=htmlspecialchars($sh->scheduled_date)?> · <?=htmlspecialchars($sh->time_slot)?></div>
<a href="/dj_panel.php?action=remove_schedule&id=<?=$sh->id?>" style="color:#f87171;font-size:10px;text-decoration:none" onclick="return confirm('Unbook this show?')">✕ Unbook</a>
</div>
<?php endforeach; ?>
</div></div>
<?php endif; ?>
</div>
</div>

<script>
function toggleDate(el,date){
  var detail=document.getElementById('sched-detail'),form=document.getElementById('sched-form');
  var alreadyBooked=el.querySelector('div[style*="color:#4ade80"]');
  if(alreadyBooked){
    detail.style.display='block';
    detail.innerHTML='<div style="color:#4ade80;font-size:13px;font-weight:600">✅ Booked</div><div style="color:#64748b;font-size:11px;margin-top:4px">'+date+'</div><a href="#" style="color:#f87171;font-size:12px;text-decoration:none;margin-top:8px;display:inline-block" onclick="document.getElementById(\'sched-form\').style.display=\'block\';document.getElementById(\'sched-form\').dataset.date=date;document.getElementById(\'sched_name\').value=\'\';document.getElementById(\'sched_start\').value=\'\';document.getElementById(\'sched_end\').value=\'\';return false">✕ Remove or Rebook</a>';
    form.style.display='none';
  } else {
    form.style.display='block';
    form.dataset.date=date;
    document.getElementById('sched_date_input').value=date;
    document.getElementById('sched_name').value='';
    document.getElementById('sched_start').value='';
    document.getElementById('sched_end').value='';
    detail.style.display='none';
  }
}
// Preselect today's date on load
(function(){var d=new Date();var ds=d.getFullYear()+'-'+('0'+(d.getMonth()+1)).slice(-2)+'-'+('0'+d.getDate()).slice(-2);document.getElementById('sched-form').dataset.date=ds;document.getElementById('sched_date_input').value=ds;})();
</script>
</div>

<div class="dj-panel" id="pn-requests">
<?php
// Show pending requests across ALL stations this DJ can access, with the station name
$djIdReqs = $_SESSION['dj_user']['id'] ?? 0;
$reqStmt = $pdo->prepare("SELECT r.*, ss.name AS station_name, ss.engine AS station_engine,
    rb.brand_logo, rb.brand_primary_color, rb.brand_slogan
    FROM radio_requests r
    JOIN streaming_stations ss ON ss.id = r.stream_id
    LEFT JOIN radio_branding rb ON rb.station_id = ss.id
    WHERE r.status = 'pending'
      AND (r.stream_id = (SELECT stream_id FROM radio_djs WHERE id = ?)
           OR r.stream_id IN (SELECT stream_id FROM radio_dj_streams WHERE dj_id = ?))
    ORDER BY r.created_at ASC");
$reqStmt->execute([$djIdReqs, $djIdReqs]);
$requests = $reqStmt->fetchAll(PDO::FETCH_OBJ);
?>
<div class="dj-grid">
<div class="card">
<h3><i class="fas fa-music"></i> Song Requests (<span id="req-count"><?php echo count($requests); ?></span>)</h3>
<div id="req-list">
<?php if (empty($requests)): ?>
<p class="empty-text">No pending requests.</p>
<?php else: ?>
<?php foreach ($requests as $r): ?>
<div class="req-item">
<div style="display:flex;gap:8px;align-items:center;min-width:0">
<?php if (!empty($r->brand_logo)): ?>
  <img src="https://planet-hosts.com<?=htmlspecialchars($r->brand_logo)?>" alt="" style="width:34px;height:34px;border-radius:8px;object-fit:cover;flex-shrink:0;border:1px solid rgba(255,255,255,.08)">
<?php endif; ?>
<div style="min-width:0">
<div class="req-title"><?php echo htmlspecialchars($r->artist . ' - ' . $r->title); ?></div>
<?php if ($r->guest_name): ?><div class="req-meta">Requested by: <?php echo htmlspecialchars($r->guest_name); ?></div><?php endif; ?>
<?php if ($r->station_name): ?><div class="req-meta" style="color:<?php echo htmlspecialchars($r->brand_primary_color ?? '#38bdf8'); ?>">📡 Station: <?php echo htmlspecialchars($r->station_name); ?></div><?php endif; ?>
<?php if ($r->message): ?><div class="req-msg">"<?php echo htmlspecialchars($r->message); ?>"</div><?php endif; ?>
</div>
</div>
<div style="display:flex;gap:6px;flex-shrink:0">
<a href="/dj_panel.php?action=played_request&req_id=<?php echo $r->id; ?>" class="btn btn-success btn-xs" title="Mark as played"><i class="fas fa-check"></i> Played</a>
<a href="/dj_panel.php?action=remove_request&req_id=<?php echo $r->id; ?>" class="btn btn-danger btn-xs">✕ Remove</a>
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>
</div>
</div>

<div class="dj-panel" id="pn-downloads">
<?php
try {
    $sid = $_SESSION['dj_user']['stream_id'] ?? 0;
    $dlStmt = $pdo->prepare("SELECT * FROM radio_downloads WHERE station_id IS NULL OR station_id = ? OR station_id IN (SELECT stream_id FROM radio_dj_streams WHERE dj_id = ? AND is_active = 'yes') ORDER BY created_at DESC");
    $dlStmt->execute([$sid, $_SESSION['dj_user']['id'] ?? 0]);
    $dls = $dlStmt->fetchAll(PDO::FETCH_OBJ);
?>
<div class="card">
<h3><i class="fas fa-download"></i> Downloads</h3>
<?php if (!empty($dls)): ?>
<div style="display:flex;flex-direction:column;gap:6px">
<?php foreach ($dls as $d): ?>
<a href="/admin/radio/downloads/serve/<?=$d->id?>" class="btn btn-sm btn-primary" style="text-align:left;justify-content:flex-start;margin:0" target="_blank">
📥 <?=htmlspecialchars($d->name)?>
<?php if ($d->description): ?><span style="font-weight:400;font-size:10px;color:#94a3b8;margin-left:6px">— <?=htmlspecialchars($d->description)?></span><?php endif; ?>
</a>
<?php endforeach; ?>
</div>
<?php else: ?>
<p class="empty-text">No downloads available.</p>
<?php endif; ?>
</div>
<?php
} catch (\Exception $e) {
    echo '<p class="empty-text">No downloads available.</p>';
} ?>
</div>

<div class="dj-panel" id="pn-profile">
<?php
$pd = $djData->profile_data ? json_decode($djData->profile_data, true) : [];
function pf($k, $d=''){global $pd; return htmlspecialchars($pd[$k] ?? $d);}
?>
<form method="POST" action="/dj_panel.php?action=save_profile_data">
<div class="dj-grid">
<div class="card">
<h3><i class="fas fa-camera"></i> Photo</h3>
<div class="profile-photo-row">
<?php if ($djData->avatar): ?>
<img src="/<?php echo $djData->avatar; ?>" class="avatar-pic">
<?php else: ?><div class="avatar-placeholder">🎤</div><?php endif; ?>
<label class="upload-btn">Change Photo<input type="file" name="file" style="display:none" onchange="var f=this.form;f.action='/dj_panel.php?action=upload_avatar';f.submit()"></label>
<label class="upload-btn" style="background:rgba(250,204,21,.1);border-color:rgba(250,204,21,.2)">Change Banner<input type="file" name="file" style="display:none" onchange="var f=this.form;f.action='/dj_panel.php?action=upload_banner';f.submit()"></label>
</div>
</div>

<div class="card"><h3>Basic Info</h3>
<div class="form-group"><label>Display Name</label><input name="name" value="<?php echo htmlspecialchars($djData->name ?? ''); ?>"></div>
<div class="form-group"><label>Real Name</label><input name="real_name" value="<?php echo pf('real_name'); ?>"></div>
<div class="form-group"><label>Nickname / Stage Name</label><input name="stage_name" value="<?php echo pf('stage_name'); ?>"></div>
<div class="form-group"><label>Years as DJ</label><input name="years_as_dj" value="<?php echo pf('years_as_dj'); ?>"></div>
<div class="form-group"><label>Hometown</label><input name="hometown" value="<?php echo pf('hometown'); ?>"></div>
<div class="form-group"><label>Country</label><input name="country" value="<?php echo pf('country'); ?>"></div>
<div class="form-group"><label>Languages</label><input name="languages" value="<?php echo pf('languages'); ?>" placeholder="English, Spanish"></div>
<div class="form-group"><label>Short Bio</label><textarea name="bio" rows="3"><?php echo htmlspecialchars($djData->bio ?? ''); ?></textarea></div>
<div class="form-group"><label>Full Biography</label><textarea name="full_bio" rows="5"><?php echo pf('full_bio'); ?></textarea></div>
</div>

<div class="card"><h3>Contact</h3>
<div class="form-group"><label>Website</label><input name="website_url" value="<?php echo htmlspecialchars($djData->website_url ?? ''); ?>"></div>
<div class="form-group"><label>Booking Email</label><input name="booking_email" value="<?php echo pf('booking_email'); ?>"></div>
<div class="form-group"><label>Phone</label><input name="phone" value="<?php echo pf('phone'); ?>"></div>
</div>

<div class="card"><h3>Social Media</h3>
<?php foreach(['facebook'=>'Facebook','instagram'=>'Instagram','twitter'=>'X (Twitter)','tiktok'=>'TikTok','youtube'=>'YouTube','twitch'=>'Twitch','discord'=>'Discord','spotify'=>'Spotify','apple_music'=>'Apple Music','soundcloud'=>'SoundCloud','mixcloud'=>'Mixcloud','beatport'=>'Beatport'] as $k=>$l): ?>
<div class="form-group"><label><?php echo $l; ?></label><input name="<?php echo $k; ?>" value="<?php echo pf($k); ?>" placeholder="https://"></div>
<?php endforeach; ?>
</div>

<div class="card"><h3>Favorites</h3>
<div class="form-group"><label>Favorite Genres</label><input name="favorite_genres" value="<?php echo pf('favorite_genres'); ?>" placeholder="Rock, Country, EDM"></div>
<div class="form-group"><label>Favorite Artists</label><textarea name="favorite_artists" rows="3"><?php echo pf('favorite_artists'); ?></textarea></div>
<div class="form-group"><label>Favorite Songs</label><textarea name="favorite_songs" rows="3"><?php echo pf('favorite_songs'); ?></textarea></div>
<div class="form-group"><label>Favorite Albums</label><textarea name="favorite_albums" rows="3"><?php echo pf('favorite_albums'); ?></textarea></div>
<div class="form-group"><label>Favorite DJs</label><textarea name="favorite_djs" rows="3"><?php echo pf('favorite_djs'); ?></textarea></div>
</div>

<div class="card"><h3>Station Info</h3>
<div class="form-group"><label>Position</label><input name="position" value="<?php echo pf('position'); ?>" placeholder="Music Director, Host"></div>
<div class="form-group"><label>On Air Since</label><input name="on_air_since" value="<?php echo pf('on_air_since'); ?>" placeholder="2024"></div>
<div class="form-group"><label>Department</label><input name="department" value="<?php echo pf('department'); ?>"></div>
</div>

<div class="card"><h3>Show Info</h3>
<div class="form-group"><label>Show Name</label><input name="show_name" value="<?php echo pf('show_name'); ?>"></div>
<div class="form-group"><label>Show Description</label><textarea name="show_description" rows="3"><?php echo pf('show_description'); ?></textarea></div>
<div class="form-group"><label>Time Zone</label><input name="timezone" value="<?php echo pf('timezone'); ?>" placeholder="America/New_York"></div>
<div class="form-group"><label>Duration (minutes)</label><input name="show_duration" value="<?php echo pf('show_duration'); ?>"></div>
</div>

<div class="card"><h3>Music Preferences</h3>
<div class="form-group"><label>Preferred Genres</label><input name="preferred_genres" value="<?php echo pf('preferred_genres'); ?>" placeholder="Rock, Pop, EDM"></div>
<div class="form-group"><label>Preferred Decades</label><input name="preferred_decades" value="<?php echo pf('preferred_decades'); ?>" placeholder="80s, 90s, 2000s"></div>
<label class="check-label"><input type="checkbox" name="clean_music_only" value="1" <?php echo pf('clean_music_only')?'checked':''; ?>> Clean Music Only</label>
<label class="check-label"><input type="checkbox" name="explicit_allowed" value="1" <?php echo pf('explicit_allowed')?'checked':''; ?>> Explicit Allowed</label>
<label class="check-label"><input type="checkbox" name="request_friendly" value="1" <?php echo pf('request_friendly')?'checked':''; ?>> Request Friendly</label>
<label class="check-label"><input type="checkbox" name="open_format" value="1" <?php echo pf('open_format')?'checked':''; ?>> Open Format</label>
</div>

<div class="card"><h3>Skills</h3>
<div class="form-group"><label>Skills (comma separated)</label><input name="skills" value="<?php echo pf('skills'); ?>" placeholder="Radio Host, Club DJ, Producer, Voice Over"></div>
</div>

<div class="card"><h3>Equipment</h3>
<div class="form-group"><label>Mixer</label><input name="mixer" value="<?php echo pf('mixer'); ?>"></div>
<div class="form-group"><label>Controller</label><input name="controller" value="<?php echo pf('controller'); ?>"></div>
<div class="form-group"><label>Microphone</label><input name="microphone" value="<?php echo pf('microphone'); ?>"></div>
<div class="form-group"><label>Headphones</label><input name="headphones" value="<?php echo pf('headphones'); ?>"></div>
<div class="form-group"><label>Streaming Software</label><input name="streaming_software" value="<?php echo pf('streaming_software'); ?>"></div>
<div class="form-group"><label>Preferred Software</label><input name="preferred_software" value="<?php echo pf('preferred_software'); ?>" placeholder="SAM Broadcaster, OBS, Mixxx"></div>
</div>

<div class="card"><h3>Personal</h3>
<div class="form-group"><label>Birthday</label><input name="birthday" type="date" value="<?php echo pf('birthday'); ?>"></div>
<div class="form-group"><label>Favorite Food</label><input name="favorite_food" value="<?php echo pf('favorite_food'); ?>"></div>
<div class="form-group"><label>Favorite Drink</label><input name="favorite_drink" value="<?php echo pf('favorite_drink'); ?>"></div>
<div class="form-group"><label>Favorite Movie</label><input name="favorite_movie" value="<?php echo pf('favorite_movie'); ?>"></div>
<div class="form-group"><label>Hobbies</label><input name="hobbies" value="<?php echo pf('hobbies'); ?>"></div>
<div class="form-group"><label>Fun Fact</label><textarea name="fun_fact" rows="2"><?php echo pf('fun_fact'); ?></textarea></div>
</div>

<div class="card"><h3>Listener Interaction</h3>
<label class="check-label"><input type="checkbox" name="accept_requests" value="1" <?php echo pf('accept_requests')?'checked':''; ?>> Accept Song Requests</label>
<label class="check-label"><input type="checkbox" name="accept_dedications" value="1" <?php echo pf('accept_dedications')?'checked':''; ?>> Accept Dedications</label>
<label class="check-label"><input type="checkbox" name="live_chat_enabled" value="1" <?php echo pf('live_chat_enabled')?'checked':''; ?>> Live Chat Enabled</label>
</div>

<div class="card"><h3>Privacy</h3>
<label class="check-label"><input type="checkbox" name="public_profile" value="1" <?php echo pf('public_profile', '1')?'checked':''; ?>> Public Profile</label>
<label class="check-label"><input type="checkbox" name="hidden_email" value="1" <?php echo pf('hidden_email')?'checked':''; ?>> Hide Email</label>
<label class="check-label"><input type="checkbox" name="hidden_birthday" value="1" <?php echo pf('hidden_birthday')?'checked':''; ?>> Hide Birthday</label>
</div>

<div class="card"><h3>Custom Theme</h3>
<div class="color-picker">
<div><label>Profile Color</label><input name="profile_color" type="color" value="<?php echo pf('profile_color','#008cff'); ?>"></div>
<div><label>Accent Color</label><input name="accent_color" type="color" value="<?php echo pf('accent_color','#a855f7'); ?>"></div>
</div>
</div>

</div>
<div class="save-row"><button class="btn btn-primary" style="padding:12px 40px;font-size:14px">Save All Profile Changes</button></div>
</form>
</div>

<div class="dj-panel" id="pn-gallery">
<div class="dj-grid">
<div class="card">
<h3><i class="fas fa-images"></i> Gallery <span class="gallery-sub">Photos &amp; Clips</span></h3>
<form method="POST" enctype="multipart/form-data" class="upload-zone">
<input type="hidden" name="action" value="upload_gallery">
<input type="file" name="file">
<button class="btn btn-primary btn-sm" style="margin-left:6px">Upload</button>
<small class="upload-hint">JPG, PNG, GIF, WEBP, MP4, MOV — max 20MB</small>
</form>
<?php
$galleryData = $djData->gallery ? json_decode($djData->gallery, true) : [];
if (!empty($galleryData)): ?>
<div class="gallery-grid">
<?php foreach ($galleryData as $i=>$item): ?>
<div class="gallery-item">
<?php if (($item['type']??'image') === 'video'): ?>
<video src="<?php echo htmlspecialchars($item['url']); ?>"></video>
<?php else: ?>
<img src="<?php echo htmlspecialchars($item['url']); ?>">
<?php endif; ?>
<a href="/dj_panel.php?action=delete_gallery&idx=<?php echo $i; ?>" class="gallery-del">✕</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>
</div>

<div class="dj-panel" id="pn-api">
<div class="card" style="max-width:600px;margin:0 auto">
<h3><i class="fas fa-code"></i> DJ API Credentials</h3>
<p class="card-desc">Copy these credentials into Planet Hosts Studio (Edit Stream → DJ API & Request Settings tab).</p>
<div id="dj-api-config">
<div style="text-align:center;padding:20px;color:#64748b;font-size:13px">Loading...</div>
</div>
</div>
</div>

<script>
(function(){
  var x=new XMLHttpRequest();
  x.open('GET','/dj_panel.php?action=get_api_config',true);
  x.onload=function(){
    try{
      var r=JSON.parse(x.responseText);
      if(r.success&&r.data){
        var d=r.data;
        var configs=r.configs||[];
        var apiUrl = d.api_url || 'https://planet-hosts.com/api';
        var apiKey = d.api_key || '';
        var reqUrl = d.request_api_url || 'https://planet-hosts.com/connector/station/'+(d.slug||d.stream_id)+'/requests';
        var h='';
        // Station picker when the DJ has configs for multiple stations
        if(configs.length>1){
          h+='<div style="margin-bottom:12px"><span class="conn-label">Station:</span> <select id="api-station-sel" onchange="loadApiConfig(this.value)" style="margin-left:6px;padding:4px 8px;border-radius:5px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px">';
          configs.forEach(function(c){
            var sid=c.stream_id;
            var label=c.station_name || ('Station #'+sid);
            h+='<option value="'+sid+'"'+(sid==d.stream_id?' selected':'')+'>'+escapeHtml(label)+'</option>';
          });
          h+='</select></div>';
        }
        h+='<div class="conn-box">';
        h+='<div class="conn-row"><span><span class="conn-label">API URL:</span> <span class="conn-value api" id="api-apiurl">'+escapeHtml(apiUrl)+'</span></span><button class="copy-btn" onclick="cf(\'api-apiurl\')">Copy</button></div>';
        h+='<div class="conn-row"><span><span class="conn-label">API Key:</span> <span class="conn-value pw" id="api-apikey">'+escapeHtml(apiKey)+'</span></span><button class="copy-btn" onclick="cf(\'api-apikey\')">Copy</button></div>';
        h+='<div class="conn-row"><span><span class="conn-label">Requests URL:</span> <span class="conn-value api" id="api-requrl">'+escapeHtml(reqUrl)+'</span></span><button class="copy-btn" onclick="cf(\'api-requrl\')">Copy</button></div>';
        h+='</div>';
        h+='<div style="margin-top:12px;padding:10px;background:rgba(56,189,248,.06);border:1px solid rgba(56,189,248,.1);border-radius:8px;font-size:11px;color:#94a3b8;line-height:1.6">';
        h+='Each station has its own API key and Request URL. In Planet Hosts Studio, enter these under <strong style="color:#e0e0e0">Edit Stream → DJ API &amp; Request Settings</strong> for the matching station.';
        h+='</div>';
        document.getElementById('dj-api-config').innerHTML = h;
        window._apiConfigs = configs;
      } else {
        document.getElementById('dj-api-config').innerHTML='<div style="text-align:center;padding:20px;color:#64748b;font-size:13px">Could not load API config.</div>';
      }
    }catch(e){
      document.getElementById('dj-api-config').innerHTML='<div style="text-align:center;padding:20px;color:#64748b;font-size:13px">Error loading config.</div>';
    }
  };
  x.onerror=function(){
    document.getElementById('dj-api-config').innerHTML='<div style="text-align:center;padding:20px;color:#64748b;font-size:13px">Could not connect.</div>';
  };
  x.send();
})();
function loadApiConfig(sid){
  var configs = window._apiConfigs || [];
  var c = null;
  for(var i=0;i<configs.length;i++){ if(String(configs[i].stream_id)==String(sid)){ c=configs[i]; break; } }
  if(!c) return;
  var apiUrl = c.api_url || 'https://planet-hosts.com/api';
  var apiKey = c.api_key || '';
  var reqUrl = c.request_api_url || 'https://planet-hosts.com/connector/station/'+(c.slug||c.stream_id)+'/requests';
  document.getElementById('api-apiurl').textContent = apiUrl;
  document.getElementById('api-apikey').textContent = apiKey;
  document.getElementById('api-requrl').textContent = reqUrl;
}
function escapeHtml(t){var d=document.createElement('div');d.textContent=t;return d.innerHTML;}
</script>
<script>
function sw(e,id){
  document.querySelectorAll('.dj-tab').forEach(function(t){t.classList.remove('act')});
  document.querySelectorAll('.dj-panel').forEach(function(p){p.classList.remove('act')});
  e.currentTarget.classList.add('act');
  var el=document.getElementById('pn-'+id);
  if(el) el.classList.add('act');
  history.replaceState(null,'','?action=dashboard&tab='+id);
}
// Restore tab from URL
var t = new URLSearchParams(window.location.search).get('tab');
if (t) { var el = document.querySelector('.dj-tab[onclick*="'+t+'"]'); if(el) el.click(); }
// Stream Player
var players = {};
function togglePlayer(id, url){
  var a = document.getElementById(id+'-audio');
  var b = document.getElementById(id+'-btn');
  if(a.paused){
    if(!a.src) a.src = url;
    a.play().then(function(){b.textContent='⏸';b.style.background='linear-gradient(135deg,#7c3aed,#6366f1)';}).catch(function(){b.textContent='▶';});
  }else{
    a.pause();
    b.textContent='▶';
    b.style.background='linear-gradient(135deg,#008cff,#38bdf8)';
  }
}
function updatePlayer(id){
  var a = document.getElementById(id+'-audio');
  if(!a || !a.duration) return;
  var pct = (a.currentTime / a.duration) * 100;
  document.getElementById(id+'-progress').style.width = pct + '%';
  document.getElementById(id+'-time').textContent = fmt(a.currentTime);
  document.getElementById(id+'-duration').textContent = isFinite(a.duration) ? fmt(a.duration) : 'LIVE';
}
function seekPlayer(e, id){
  var bg = document.getElementById(id+'-progress-bg');
  var a = document.getElementById(id+'-audio');
  if(!a || !a.duration) return;
  var rect = bg.getBoundingClientRect();
  var pct = (e.clientX - rect.left) / rect.width;
  a.currentTime = pct * a.duration;
}
function setVolume(id, v){
  var a = document.getElementById(id+'-audio');
  if(a) a.volume = parseFloat(v);
}
function playerEnded(id){
  document.getElementById(id+'-btn').textContent='▶';
  document.getElementById(id+'-btn').style.background='linear-gradient(135deg,#008cff,#38bdf8)';
  document.getElementById(id+'-progress').style.width='0%';
}
function playerError(id){
  document.getElementById(id+'-btn').textContent='↻';
}
function fmt(s){
  if(!s || isNaN(s)) return '0:00';
  var m = Math.floor(s/60);
  var sc = Math.floor(s % 60);
  return m + ':' + (sc<10?'0':'') + sc;
}
</script>
<script>
// ─── LIVE MONITOR: probes the real stream engine every 8s ───
(function(){
  function refreshStatus(){
    var x=new XMLHttpRequest();
    x.open('GET','/dj_panel.php?action=status',true);
    x.onload=function(){
      try{
        var r=JSON.parse(x.responseText);
        if(!r || !r.ok) return;
        var state=r.state||'offline';
        var lbl=state==='live'?'Live DJ':(state==='autodj'?'AutoDJ':'Offline');
        var stEl=document.getElementById('dj-source-status');
        if(stEl){ stEl.className='pill '+(state==='live'?'live':(state==='autodj'?'autodj':'off')); stEl.innerHTML='<span class="livedot"></span>'+lbl; }
        var lt=document.getElementById('live-now-title'); if(lt) lt.textContent=state==='live'?'Live Now':(state==='autodj'?'AutoDJ on air':'Offline');
        var lsub=document.getElementById('live-now-sub'); if(lsub) lsub.textContent=state==='live'?(r.current_dj||'Live source'):(state==='autodj'?'Playlist is playing':'Connect your encoder to go live');
        var song=(r.song||'').trim();
        var ns=document.getElementById('dj-np-song');
        if(ns) ns.textContent=song||(state==='live'?'Live with '+(r.current_dj||''):'Station is idle — waiting for a source');
        var sub=document.getElementById('dj-np-sub');
        if(sub){
          if(state==='live') sub.innerHTML='On air with <b style="color:#a78bfa">'+((r.current_dj&&r.current_dj!=='Live DJ')?r.current_dj:'Live DJ')+'</b>';
          else if(state==='autodj') sub.innerHTML='AutoDJ is playing the playlist';
          else sub.innerHTML='No source connected';
        }
        var st=document.getElementById('src-tile');
        if(st){ st.textContent=lbl; st.style.color=state==='live'?'#34d399':(state==='autodj'?'#facc15':'#64748b'); }
        var npEl=document.querySelector('[id^="player-"][id$="-song"]');
        if(npEl && song) npEl.textContent=song;
        var npArt=document.querySelector('[id^="player-"][id$="-artist"]');
        if(npArt && song && state==='live') npArt.textContent=r.current_dj||'';
        var ln=document.getElementById('dj-live-listeners'); if(ln){ ln.textContent=r.listeners||0; ln.style.color=state==='live'?'#34d399':'#38bdf8'; }
        var sl=document.getElementById('dj-stat-listeners'); if(sl) sl.textContent=r.listeners||0;
        var lsn=document.getElementById('live-now-song'); if(lsn) lsn.textContent=song||'—';
        var lln=document.getElementById('live-now-listeners'); if(lln) lln.textContent=r.listeners||0;
      }catch(e){}
    };
    x.send();
  }
  refreshStatus();
  setInterval(refreshStatus, 8000);
})();

// ─── Requests Auto-Refresh (every 10s) ───
(function(){
  var countEl = document.getElementById('req-count');
  var listEl = document.getElementById('req-list');
  if (!listEl) return;
  var lastCount = null;
  function refreshRequests(){
    var x = new XMLHttpRequest();
    x.open('GET','/dj_panel.php?action=requests_refresh', true);
    x.onload = function(){
      if (x.status !== 200) return;
      var html = x.responseText;
      var count = (html.match(/class="req-item"/g) || []).length;
      if (lastCount !== null && count > lastCount) {
        try { var beep = new Audio('data:audio/wav;base64,UklGRlwAAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoAAACAgICAPz8/PwAAAAAAAAA='); beep.play(); } catch(e){}
      }
      if (lastCount !== null && count !== lastCount) {
        listEl.innerHTML = html;
        if (countEl) countEl.textContent = count;
      }
      lastCount = count;
    };
    x.send();
  }
  setInterval(refreshRequests, 10000);
})();
</script>
</body></html>

