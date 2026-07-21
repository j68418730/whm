<?php
session_start();
$action = $_POST['action'] ?? $_GET['action'] ?? 'login';
$error = '';
$success = '';
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

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

// ─── LOGIN ───
if ($_POST && $action === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT d.*, ss.port, ss.status as stream_status, ss.autodj_enabled as autodj_active,
        (SELECT COUNT(*) FROM radio_listener_analytics WHERE stream_id = d.stream_id AND date = CURDATE()) as today_listeners
        FROM radio_djs d JOIN streaming_stations ss ON d.stream_id = ss.id WHERE d.username = ? AND d.status = 'active'");
    $stmt->execute([$username]);
    $dj = $stmt->fetch(PDO::FETCH_OBJ);
    if ($dj && password_verify($password, $dj->password)) {
        $_SESSION['dj_user'] = [
            'id' => $dj->id, 'stream_id' => $dj->stream_id, 'username' => $dj->username,
            'name' => $dj->name ?: $dj->username, 'stream_name' => 'Stream',
            'port' => $dj->port, 'stream_status' => $dj->stream_status,
        ];
        $pdo->prepare("UPDATE radio_djs SET last_active = NOW() WHERE id = ?")->execute([$dj->id]);
    header('Location: /dj_panel.php?action=dashboard');
    exit;
}
    $error = 'Invalid DJ name or password, or account inactive.';
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
            $pdo->exec("UPDATE streaming_stations SET autodj_enabled=0 WHERE id=" . (int)$sid);
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
    $pdo->prepare("UPDATE radio_requests SET status = 'removed' WHERE id = ? AND stream_id = ?")
        ->execute([$reqId, $_SESSION['dj_user']['stream_id']]);
    header('Location: /dj_panel.php?action=dashboard');
    exit;
}

// ─── DOWNLOAD SAM PLAYLIST ───
if ($action === 'download_playlist' && isset($_SESSION['dj_user'])) {
    $sid = $_SESSION['dj_user']['stream_id'];
    $pl = $pdo->prepare("SELECT pi.* FROM radio_playlist_items pi JOIN radio_playlists p ON pi.playlist_id = p.id WHERE p.stream_id = ? ORDER BY pi.id");
    $pl->execute([$sid]);
    $tracks = $pl->fetchAll(PDO::FETCH_OBJ);
    $dj = $_SESSION['dj_user'];
    
    // SAM Broadcaster .lst format
    $content = "; SAM Broadcaster Playlist\n";
    $content .= "; Generated for: {$dj['name']}\n";
    $content .= "; Stream: {$dj['stream_name']}\n";
    $content .= "; Date: " . date('Y-m-d H:i') . "\n";
    $content .= "; Total tracks: " . count($tracks) . "\n\n";
    foreach ($tracks as $t) {
        $content .= $t->file_path . "\n";
    }
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="playlist_' . $dj['username'] . '_' . date('Ymd') . '.lst"');
    echo $content;
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
        $stmt = $pdo->prepare("SELECT d.*, ss.status as stream_status, ss.listener_count, ss.current_song, ss.autodj_enabled as autodj_active, hu.username as hosting_username,
            (SELECT COUNT(*) FROM radio_playlist_items pi JOIN radio_playlists p ON pi.playlist_id = p.id WHERE p.stream_id = d.stream_id) as track_count
            FROM radio_djs d JOIN streaming_stations ss ON d.stream_id = ss.id JOIN hosting_users hu ON ss.user_id = hu.id WHERE d.id = ?");
        $stmt->execute([$_SESSION['dj_user']['id']]);
        $djData = $stmt->fetch(PDO::FETCH_OBJ);
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
body{background:#02050e;color:#fff;font-family:'Inter',sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh}
.bg{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.9),rgba(2,8,23,.97)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:36px 28px;max-width:400px;width:92%;text-align:center}
h1{font-size:22px;margin-bottom:4px}h1 span{color:#008cff}
p{color:#64748b;font-size:13px;margin-bottom:20px}
.form-group{margin-bottom:14px;text-align:left}
.form-group label{display:block;margin-bottom:4px;font-size:12px;color:#94a3b8;font-weight:600}
.form-group input{width:100%;padding:11px 14px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff;font-size:14px;outline:none;box-sizing:border-box}
.form-group input:focus{border-color:#008cff}
.btn{width:100%;padding:12px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer}
.alert{padding:10px;border-radius:8px;margin-bottom:14px;font-size:13px}
.alert-error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#f87171}
.alert-success{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);color:#4ade80}
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
*{margin:0;padding:0;box-sizing:border-box}
body{background:#02050e;color:#e2e8f0;font-family:'Inter',sans-serif}
.bg{position:fixed;inset:0;background:linear-gradient(145deg,rgba(2,8,23,.92),rgba(15,23,42,.98)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.topbar{background:rgba(15,23,42,.85);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-bottom:1px solid rgba(56,189,248,.08);padding:14px 24px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:100}
.topbar h2{font-size:18px;font-weight:800;background:linear-gradient(135deg,#e2e8f0,#94a3b8);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.topbar h2 span{-webkit-text-fill-color:#008cff}
.topbar a{color:#f87171;text-decoration:none;font-size:13px;transition:color .2s}
.topbar a:hover{color:#fca5a5}
.container{max-width:960px;margin:0 auto;padding:24px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:24px}
.stat-card{background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.08);border-radius:16px;padding:22px 16px;text-align:center;transition:border-color .3s,transform .2s}
.stat-card:hover{border-color:rgba(56,189,248,.2);transform:translateY(-2px)}
.stat-card .num{font-size:30px;font-weight:800;color:var(--c,#008cff);line-height:1.2}
.stat-card .label{font-size:11px;color:#64748b;margin-top:6px;text-transform:uppercase;letter-spacing:.5px;font-weight:600}
.card{background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.06);border-radius:16px;padding:20px;margin-bottom:16px;transition:border-color .3s}
.card:hover{border-color:rgba(56,189,248,.15)}
.card h3{font-size:14px;color:#008cff;margin-bottom:12px;font-weight:700;display:flex;align-items:center;gap:8px}
.card h3 i{font-size:15px;opacity:.8}
input,textarea,select{width:100%;padding:10px 14px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.06);border-radius:10px;color:#e2e8f0;font-size:13px;outline:none;box-sizing:border-box;font-family:'Inter',sans-serif;transition:border-color .2s}
input:focus,textarea:focus,select:focus{border-color:rgba(56,189,248,.35);box-shadow:0 0 0 3px rgba(56,189,248,.06)}
textarea{min-height:80px;resize:vertical}
select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px}
.form-group{margin-bottom:12px}
.form-group label{display:block;font-size:11px;color:#64748b;margin-bottom:4px;font-weight:600;text-transform:uppercase;letter-spacing:.3px}
.btn{padding:10px 20px;border-radius:10px;border:none;font-weight:600;font-size:13px;cursor:pointer;transition:all .2s;font-family:'Inter',sans-serif;display:inline-flex;align-items:center;gap:6px}
.btn-primary{background:linear-gradient(135deg,#008cff,#38bdf8);color:#fff;box-shadow:0 4px 14px rgba(0,140,255,.2)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,140,255,.3)}
.btn-danger{background:rgba(248,113,113,.12);color:#f87171}
.btn-danger:hover{background:rgba(248,113,113,.2)}
.btn-warning{background:rgba(250,204,21,.1);color:#facc15}
.btn-warning:hover{background:rgba(250,204,21,.18)}
.btn-sm{padding:6px 14px;font-size:11px;border-radius:8px}
.btn-xs{padding:3px 8px;font-size:10px;border-radius:6px}
.banner{width:100%;height:180px;border-radius:16px;overflow:hidden;margin-bottom:20px;background:rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;font-size:14px;color:#475569;border:1px solid rgba(56,189,248,.06)}
.banner img{width:100%;height:100%;object-fit:cover}
.banner-empty-icon{font-size:32px;opacity:.3}
.station-info{font-size:12px;color:#94a3b8;line-height:1.7}
.station-info strong{color:#64748b}
.dj-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px}
.dj-grid .card{margin-bottom:0}
@media(min-width:1200px){.dj-grid{grid-template-columns:repeat(5,1fr)}}
.dj-tabs{display:flex;gap:4px;margin-bottom:20px;flex-wrap:wrap}
.dj-tab{padding:8px 18px;border-radius:10px;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s;color:#64748b;background:rgba(255,255,255,.03);border:1px solid transparent}
.dj-tab:hover{background:rgba(56,189,248,.07);color:#38bdf8}
.dj-tab.act{background:rgba(56,189,248,.12);border-color:rgba(56,189,248,.25);color:#38bdf8}
.dj-panel{display:none}
.dj-panel.act{display:block}
.conn-grid{display:flex;flex-direction:column;gap:8px}
.conn-row{display:flex;justify-content:space-between;align-items:center;padding:6px 0}
.conn-row+.conn-row{border-top:1px solid rgba(255,255,255,.04)}
.conn-label{color:#64748b;font-size:12px}
.conn-value{color:#4ade80;font-family:monospace;font-size:13px;font-weight:600}
.conn-value.pw{color:#facc15}
.conn-value.api{color:#a855f7}
.api-row{font-size:10px;color:#64748b;line-height:1.8}
.api-row code{color:#a855f7;font-size:11px}
.api-row .sep{margin:0 4px;color:rgba(255,255,255,.06)}
.copy-btn{background:rgba(255,255,255,.04);color:#64748b;border:none;border-radius:6px;cursor:pointer;transition:all .2s;padding:4px 8px;font-size:10px}
.copy-btn:hover{background:rgba(255,255,255,.08);color:#94a3b8}
.conn-box{background:rgba(0,0,0,.35);border-radius:12px;padding:16px;font-family:monospace;font-size:12px;line-height:2}
.conn-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.text-bright{color:#e2e8f0}
.text-green{color:#4ade80}
.card-desc{font-size:13px;color:#94a3b8;line-height:1.6;margin-bottom:12px}
.sam-notice{background:rgba(250,204,21,.06);border:1px solid rgba(250,204,21,.15);border-radius:10px;padding:12px;margin-bottom:12px}
.sam-title{font-size:11px;color:#facc15;font-weight:600;margin-bottom:4px}
.sam-text{font-size:11px;color:#94a3b8;line-height:1.6}
.sam-text strong{color:#e2e8f0}
.sch-form{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px}
.sch-table{width:100%;border-collapse:collapse;font-size:12px}
.sch-table th{padding:8px 6px;text-align:left;color:#64748b;font-weight:600;border-bottom:1px solid rgba(255,255,255,.06)}
.sch-table td{padding:8px 6px;border-bottom:1px solid rgba(255,255,255,.04)}
.req-item{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.req-item:last-child{border-bottom:none}
.req-title{font-size:14px;font-weight:600}
.req-meta{font-size:11px;color:#64748b}
.req-msg{font-size:11px;color:#94a3b8;font-style:italic}
.gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px}
.gallery-item{position:relative;border-radius:8px;overflow:hidden;background:rgba(0,0,0,.35);aspect-ratio:3/2}
.gallery-item img,.gallery-item video{width:100%;height:100%;object-fit:cover}
.gallery-del{position:absolute;top:4px;right:4px;padding:2px 6px;font-size:10px;width:auto;background:rgba(248,113,113,.85);color:#fff;border:none;border-radius:4px;cursor:pointer;transition:background .2s}
.gallery-del:hover{background:#ef4444}
.upload-zone{border:1px dashed rgba(56,189,248,.2);border-radius:10px;padding:16px;text-align:center;margin-bottom:12px}
.upload-zone input[type="file"]{display:inline-block;font-size:11px;color:#94a3b8}
.stream-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.stream-row:last-child{border-bottom:none}
.stream-name{font-weight:600;font-size:13px}
.stream-meta{font-size:11px;color:#64748b;margin-top:2px}
.stream-status-badge{font-weight:500}
.banner-preview{width:100%;max-height:100px;object-fit:cover;border-radius:8px;margin-bottom:8px}
.alert{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:10px;padding:10px 14px;color:#4ade80;font-size:13px;margin-bottom:16px}
.alert-error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#f87171}
.color-picker{display:flex;gap:12px;flex-wrap:wrap}
.color-picker label{font-size:11px;color:#94a3b8}
.color-picker input[type="color"]{width:60px;height:40px;padding:2px;cursor:pointer}
.gallery-sub{font-size:11px;color:#64748b;font-weight:400}
.upload-hint{display:block;color:#64748b;margin-top:4px;font-size:10px}
.file-input{font-size:11px;color:#94a3b8;margin-bottom:6px}
.profile-photo-row{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
.avatar-pic{width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid rgba(56,189,248,.15)}
.avatar-placeholder{width:64px;height:64px;border-radius:50%;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;font-size:28px;border:2px solid rgba(255,255,255,.06)}
.upload-btn{display:inline-block;padding:6px 12px;border-radius:8px;background:rgba(56,189,248,.1);border:1px solid rgba(56,189,248,.2);color:#e2e8f0;cursor:pointer;font-size:11px;transition:all .2s}
.upload-btn:hover{background:rgba(56,189,248,.18)}
.empty-text{color:#64748b;font-size:13px}
.save-row{margin-top:12px;text-align:center}
.check-label{display:flex;align-items:center;gap:6px;font-size:12px;color:#c0c0c0;margin-bottom:6px;cursor:pointer}
.check-label input{cursor:pointer}
</style></head><body>
<div class="bg"></div>
<div class="topbar">
<h2>🎤 Planet <span>DJ</span></h2>
<div style="display:flex;align-items:center;gap:12px">
<span style="font-size:13px;color:#94a3b8"><?php echo htmlspecialchars($_SESSION['dj_user']['name'] ?? ''); ?></span>
<a href="/dj_panel.php?action=logout">Logout</a>
<a href="/dj?u=<?php echo urlencode($_SESSION['dj_user']['username'] ?? ''); ?>" target="_blank" style="color:#34d399;text-decoration:none;font-size:13px">📻 My Page</a>
<a href="/studio/index.php" target="_blank" style="color:#a855f7;text-decoration:none;font-size:13px;margin-left:12px">🎛️ Studio</a>
</div>
</div>
<div class="container">

<?php if ($success): ?><div class="alert"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

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
        }
        $stationQuery = $pdo->prepare("SELECT id, name, engine, port, status FROM streaming_stations WHERE user_id=? ORDER BY name");
        $stationQuery->execute([$userId]);
        $userStations = $stationQuery->fetchAll(PDO::FETCH_OBJ);
        
        if (!empty($userStations)) {
            foreach ($userStations as $index => $station) {
                $tabId = $index + 1; // 1, 2, 3, etc.
                $tabName = $station->name ?? "Stream #{$station->id}";
                if ($station->id === $_SESSION['dj_user']['stream_id']) {
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
    <div class="dj-tab" onclick="sw(event,'profile')">Profile</div>
    <div class="dj-tab" onclick="sw(event,'gallery')">Gallery</div>
</div>

<!-- Station tabs content -->
    <?php
    $userId = $_SESSION['dj_user']['id'] ?? 0;
    $isOwner = !empty($_SESSION['dj_user']['is_owner']);
    if ($isOwner || $userId > 0) {
        if ($isOwner) {
            $userId = $hostingId ?? 0;
        }
        $stationQuery = $pdo->prepare("SELECT id, name, engine, port, status FROM streaming_stations WHERE user_id=? ORDER BY name");
        $stationQuery->execute([$userId]);
        $userStations = $stationQuery->fetchAll(PDO::FETCH_OBJ);
        
        foreach ($userStations as $station) {
            $stationId = $station->id;
            $isActiveStation = $stationId === $_SESSION['dj_user']['stream_id'];
            echo "<div class=\"dj-panel" . ($isActiveStation ? ' act' : '') . "\" id=\"pn-station-{$stationId}\">\n";
            
            // Station-specific content
            echo "<div class=\"card\">\n";
            $statusColor = $station->status === 'running' ? '#4ade80' : '#f87171';
            echo "<h3><i class=\"fas fa-broadcast-tower\"></i> " . htmlspecialchars($station->name) . "</h3>\n";
            echo "<div class=\"station-info\">\n";
            echo "<strong>Engine:</strong> " . htmlspecialchars($station->engine) . "<br>\n";
            echo "<strong>Port:</strong> " . htmlspecialchars($station->port) . "<br>\n";
            echo "<strong>Status:</strong> <span style=\"color:{$statusColor};\">" . htmlspecialchars($station->status) . "</span><br>\n";
            echo "<strong>Stream ID:</strong> {$station->id}<br>\n";
            echo "</div>\n";
            echo "<button class=\"btn btn-primary btn-sm\" onclick=\"window.location.href='/dj_panel.php?action=dashboard&stream_id={$stationId}'\" style=\"margin-top:8px;\">\n";
            echo "Go to Station Dashboard\n";
            echo "</button>\n";
            echo "</div>\n";
            echo "</div>\n";
        }
    }
    ?>



<div class="dj-panel act" id="pn-overview">
<div class="grid">
<div class="stat-card" style="--c:#4ade80"><div class="num"><?php echo $djData->stream_status ?? 'N/A'; ?></div><div class="label">Stream Status</div></div>
<div class="stat-card" style="--c:#38bdf8"><div class="num"><?php echo $djData->listener_count ?? 0; ?></div><div class="label">Current Listeners</div></div>
<div class="stat-card" style="--c:#facc15"><div class="num"><?php echo $djData->track_count ?? 0; ?></div><div class="label">Library Tracks</div></div>
<div class="stat-card" style="--c:#a78bfa"><div class="num" id="dj-source-status"><?php echo $djData->autodj_active ? 'AutoDJ' : ($djData->current_dj ? 'Live DJ' : 'Offline'); ?></div><div class="label">Source</div></div>
</div>

<!-- DJ Takeover -->
<?php
$streamId = $djData->stream_id ?? 0;
$ss = $pdo->prepare("SELECT * FROM streaming_stations WHERE id = ?");
$ss->execute([$streamId]);
$station = $ss->fetch(PDO::FETCH_OBJ);
$djPort = $station->dj_port ?? $station->port ?? 8000;
$djPass = $station->plain_password ?? '';
$djHost = 'planet-hosts.com';
$djUsername = $_SESSION['dj_user']['username'] ?? '';
$djRealPass = $_SESSION['dj_user']['real_password'] ?? 'your-dj-password'; // Will be set below if available
// Try to get the actual DJ password from the DB (if this user owns the stream, show the source password instead)
$isOwner = !empty($_SESSION['dj_user']['is_owner']);
?>

<div class="dj-grid">

<!-- Broadcaster Info -->
<div class="card" style="border-color:rgba(56,189,248,.2)">
<h3><i class="fas fa-broadcast-tower"></i> Broadcaster Info</h3>
<div class="card-desc">Connect your broadcasting software with these details.</div>
<div class="sam-notice">
<div class="sam-title">📻 SAM Broadcaster Users</div>
<div class="sam-text">Enter your credentials as <strong class="text-bright">djusername:djpassword</strong> in the <strong class="text-bright">Password</strong> field. SAM only has one password field — combine your DJ username and password with a colon.</div>
</div>
<div class="conn-box">
<div class="conn-row">
<span><span class="conn-label">Server:</span> <span class="conn-value" id="bi-server"><?php echo $djHost; ?></span></span>
<button class="copy-btn" onclick="copyField('bi-server')">Copy</button>
</div>
<div class="conn-row">
<span><span class="conn-label">Port:</span> <span class="conn-value" id="bi-port"><?php echo $djPort; ?></span> <span class="conn-label" style="font-size:10px">(DJ auth)</span></span>
<button class="copy-btn" onclick="copyField('bi-port')">Copy</button>
</div>
<div class="conn-row">
<span><span class="conn-label">Username:</span> <span class="conn-value" style="color:#38bdf8" id="bi-user"><?php echo htmlspecialchars($djUsername); ?></span></span>
<button class="copy-btn" onclick="copyField('bi-user')">Copy</button>
</div>
<div class="conn-row">
<span><span class="conn-label">Password:</span> <span class="conn-value pw" id="bi-pass"><?php echo $isOwner ? htmlspecialchars($djPass) : '••••••••'; ?></span></span>
<button class="copy-btn" onclick="togglePass()"><?php echo $isOwner ? 'Hide' : 'Show'; ?></button>
</div>
<div class="conn-row">
<span><span class="conn-label">Format:</span> <span class="conn-label">MP3 · <?php echo $station->bitrate ?? 128; ?> kbps</span></span>
</div>
</div>
<div class="conn-actions">
<button class="btn btn-primary btn-sm" onclick="copyAll()">📋 Copy All</button>
<button class="btn btn-danger btn-sm" onclick="window.location.href='/dj_panel.php?action=takeover'">🎤 Stop AutoDJ &amp; Connect</button>
</div>
</div>
<script>
function copyField(id){var t=document.getElementById(id).textContent;navigator.clipboard.writeText(t);var b=event.target;b.textContent='Copied!';setTimeout(function(){b.textContent='Copy'},1500);}
function togglePass(){var p=document.getElementById('bi-pass');if(p.textContent=='••••••••'){p.textContent='<?php echo addslashes($djPass); ?>';event.target.textContent='Hide'}else{p.textContent='••••••••';event.target.textContent='Show'}}
function copyAll(){var t='Server: <?php echo addslashes($djHost); ?>\nPort: <?php echo $djPort; ?>\nUsername: <?php echo addslashes($djUsername); ?>\nPassword: <?php echo $isOwner ? addslashes($djPass) : '<your DJ password>'; ?>\nFormat: MP3 <?php echo $station->bitrate ?? 128; ?>kbps';navigator.clipboard.writeText(t);var b=event.target;b.textContent='Copied All!';setTimeout(function(){b.textContent='📋 Copy All'},2000);}
</script>

<!-- API Connection -->
<div class="card" style="border-color:rgba(168,85,247,.2)">
<h3><i class="fas fa-code"></i> API Connection</h3>
<div class="card-desc">Access station data programmatically. Uses your DJ session cookie for auth.</div>
<div class="conn-box">
<div class="conn-row">
<span><span class="conn-label">Base URL:</span> <span class="conn-value api" id="api-base">https://planet-hosts.com/api/studio/station/<?php echo $_SESSION['dj_user']['stream_id'] ?? 0; ?></span></span>
<button class="copy-btn" onclick="copyField('api-base')">Copy</button>
</div>
<div class="api-row">
<code>GET /connection</code> — station info & stream details
<span class="sep">|</span>
<code>GET /djs</code> — list DJs
</div>
</div>
</div>

<!-- Banner -->
<div class="banner">
<?php if ($djData->banner): ?>
<img src="/<?php echo $djData->banner; ?>" alt="Banner">
<?php else: ?>
<i class="fas fa-image banner-empty-icon"></i> No banner set
<?php endif; ?>
</div>



<?php
// Get user's streams for kick feature
$userId = $_SESSION['dj_user']['id'] ?? 0;
$streamId = $_SESSION['dj_user']['stream_id'] ?? 0;
$isOwner = !empty($_SESSION['dj_user']['is_owner']);
// Find hosting_user_id from stream
$hSt = $pdo->prepare("SELECT user_id FROM streaming_stations WHERE id=?");
$hSt->execute([$streamId]);
$hRow = $hSt->fetch(PDO::FETCH_OBJ);
$hostingId = $hRow->user_id ?? 0;
// Get all streams for this user
$userStreams = $pdo->prepare("SELECT id, name, engine, port, status FROM streaming_stations WHERE user_id=? ORDER BY id");
$userStreams->execute([$hostingId]);
$myStreams = $userStreams->fetchAll(PDO::FETCH_OBJ);
?>
</div>

<!-- Banner Upload -->
<div class="card" style="border-color:rgba(250,204,21,.15)">
<h3><i class="fas fa-image"></i> Profile Banner</h3>
<?php if ($djData->banner): ?>
<img src="/<?php echo $djData->banner; ?>" class="banner-preview">
<?php endif; ?>
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="action" value="upload_banner">
<input type="file" name="file" accept="image/*" class="file-input">
<button class="btn btn-warning btn-sm">Upload Banner</button>
</form>
</div>

<!-- Kick Stream -->
<div class="card" style="border-color:rgba(248,113,113,.2)">
<h3><i class="fas fa-ban"></i> Kick Source</h3>
<p class="card-desc">Force-disconnect the current source (AutoDJ or Live DJ). The stream will stop until someone reconnects.</p>
<?php if (empty($myStreams)): ?>
<p class="empty-text">No streams available.</p>
<?php else: ?>
<?php foreach ($myStreams as $st): 
  $stEngine = strtolower($st->engine ?? $st->server_type ?? 'icecast');
  $stLabel = strtoupper($stEngine === 'shoutcast' || $stEngine === 'shoutcast1' || $stEngine === 'shoutcast2' ? 'SHOUTcast' : 'Icecast');
?>
<div class="stream-row">
<div>
<div class="stream-name"><?php echo htmlspecialchars($st->name ?? "Stream #{$st->id}"); ?></div>
<div class="stream-meta"><?php echo $stLabel; ?> · Port <?php echo $st->port; ?> · <span class="stream-status-badge" style="color:<?php echo $st->status === 'running' ? '#4ade80' : '#f87171'; ?>"><?php echo $st->status; ?></span></div>
</div>
<form method="POST" action="/dj_panel.php?action=kick" style="display:inline" onsubmit="return confirm('Kick the source on <?php echo htmlspecialchars($st->name ?? 'this stream'); ?>?');">
<input type="hidden" name="stream_id" value="<?php echo $st->id; ?>">
<button class="btn btn-danger btn-sm">Kick</button>
</form>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>

</div>
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
$reqs = $pdo->prepare("SELECT * FROM radio_requests WHERE stream_id = ? AND status = 'pending' ORDER BY created_at ASC");
$reqs->execute([$_SESSION['dj_user']['stream_id']]);
$requests = $reqs->fetchAll(PDO::FETCH_OBJ);
?>
<div class="dj-grid">
<div class="card">
<h3><i class="fas fa-music"></i> Song Requests (<?php echo count($requests); ?>)</h3>
<?php if (empty($requests)): ?>
<p class="empty-text">No pending requests.</p>
<?php else: ?>
<?php foreach ($requests as $r): ?>
<div class="req-item">
<div>
<div class="req-title"><?php echo htmlspecialchars($r->artist . ' - ' . $r->title); ?></div>
<?php if ($r->guest_name): ?><div class="req-meta">Requested by: <?php echo htmlspecialchars($r->guest_name); ?></div><?php endif; ?>
<?php if ($r->message): ?><div class="req-msg">"<?php echo htmlspecialchars($r->message); ?>"</div><?php endif; ?>
</div>
<a href="/dj_panel.php?action=remove_request&req_id=<?php echo $r->id; ?>" class="btn btn-danger btn-xs">✕ Remove</a>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<div class="card">
<h3><i class="fas fa-tools"></i> DJ Tools</h3>
<a href="/dj_panel.php?action=download_playlist" class="btn btn-primary btn-sm" style="margin-bottom:8px">📥 Download SAM Playlist (.lst)</a>
<p class="upload-hint">Downloads a SAM Broadcaster compatible playlist file.</p>
</div>
</div>
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
</script>
</body></html>

