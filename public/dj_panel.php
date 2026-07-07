<?php
session_start();
$action = $_POST['action'] ?? $_GET['action'] ?? 'login';
$error = '';
$success = '';
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// ─── AUTO-LOGIN for account owners ───
if (!isset($_SESSION['dj_user']) && isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
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
        $pdo->prepare("UPDATE radio_djs SET last_login = NOW() WHERE id = ?")->execute([$dj->id]);
        header('Location: /dj_panel.php?action=dashboard');
        exit;
    }
    $error = 'Invalid DJ name or password, or account inactive.';
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
        $success = 'AutoDJ stopped. Connect your broadcasting software to port ' . ((int)$sid + 8998) . ' with your DJ username and password.';
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
    $dw = (int)($_POST['day_of_week'] ?? 0);
    $st = trim($_POST['start_time'] ?? '');
    $et = trim($_POST['end_time'] ?? '');
    if ($sn && $st && $et && $sId) {
        try {
            $pdo->prepare("INSERT INTO radio_schedule (stream_id, dj_id, show_name, day_of_week, start_time, end_time, is_active, created_by) VALUES (?,?,?,?,?,?,1,'dj')")
                ->execute([$sId, $djId, $sn, $dw, $st, $et]);
            $success = 'Show added to your schedule.';
        } catch (\Exception $e) { $error = 'Failed to add show.'; }
    } else { $error = 'Please fill all fields.'; }
    header('Location: /dj_panel.php?action=dashboard');
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
        $dir = '/var/www/radiohosting/storage/dj/' . $_SESSION['dj_user']['id'] . '/';
        @mkdir($dir, 0755, true);
        $name = 'banner_' . bin2hex(random_bytes(8)) . '.' . $ext;
        move_uploaded_file($_FILES['file']['tmp_name'], $dir . $name);
        $urlPath = '/storage/dj/' . $_SESSION['dj_user']['id'] . '/' . $name;
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
        $dir = '/var/www/radiohosting/storage/dj/' . $_SESSION['dj_user']['id'] . '/';
        @mkdir($dir, 0755, true);
        $name = 'avatar_' . bin2hex(random_bytes(8)) . '.' . $ext;
        move_uploaded_file($_FILES['file']['tmp_name'], $dir . $name);
        $urlPath = '/storage/dj/' . $_SESSION['dj_user']['id'] . '/' . $name;
        $pdo->prepare("UPDATE radio_djs SET avatar = ? WHERE id = ?")->execute([$urlPath, $_SESSION['dj_user']['id']]);
        $success = 'Avatar updated.';
    } else {
        $error = 'Invalid file. Allowed: jpg, png, gif, webp. Max 2MB.';
    }
    $action = 'dashboard';
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
    $stmt = $pdo->prepare("SELECT d.*, ss.status as stream_status, ss.listener_count, ss.current_song, ss.autodj_enabled as autodj_active,
        (SELECT COUNT(*) FROM radio_playlist_items pi JOIN radio_playlists p ON pi.playlist_id = p.id WHERE p.stream_id = d.stream_id) as track_count
        FROM radio_djs d JOIN streaming_stations ss ON d.stream_id = ss.id WHERE d.id = ?");
    $stmt->execute([$_SESSION['dj_user']['id']]);
    $djData = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$djData) { session_destroy(); header('Location: /dj_panel.php'); exit; }
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
body{background:#02050e;color:#fff;font-family:'Inter',sans-serif}
.bg{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.92),rgba(2,8,23,.98)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.topbar{background:rgba(8,16,28,.9);border-bottom:1px solid rgba(0,191,255,.1);padding:14px 24px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:100}
.topbar h2{font-size:18px;font-weight:800}
.topbar h2 span{color:#008cff}
.topbar a{color:#f87171;text-decoration:none;font-size:13px}
.container{max-width:900px;margin:0 auto;padding:24px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:24px}
.stat-card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:20px;text-align:center}
.stat-card .num{font-size:28px;font-weight:800;color:var(--c,#008cff)}
.stat-card .label{font-size:12px;color:#64748b;margin-top:4px}
.card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:24px;margin-bottom:16px}
.card h3{font-size:15px;color:var(--accent,#008cff);margin-bottom:12px}
.profile-section{display:flex;gap:20px;align-items:start;flex-wrap:wrap}
.avatar-box{width:120px;height:120px;border-radius:50%;border:3px solid rgba(0,191,255,.2);overflow:hidden;flex-shrink:0;background:rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;font-size:40px}
.avatar-box img{width:100%;height:100%;object-fit:cover}
.upload-btn{display:inline-block;padding:8px 16px;border-radius:6px;background:rgba(0,140,255,.1);border:1px solid rgba(0,191,255,.15);color:#e0e0e0;cursor:pointer;font-size:12px;transition:.3s}
.upload-btn:hover{background:rgba(0,140,255,.2)}
input,textarea{width:100%;padding:10px 14px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:8px;color:#fff;font-size:13px;outline:none;box-sizing:border-box;font-family:'Inter',sans-serif}
input:focus,textarea:focus{border-color:rgba(0,191,255,.3)}
textarea{min-height:80px;resize:vertical}
.form-group{margin-bottom:12px}
.form-group label{display:block;font-size:12px;color:#94a3b8;margin-bottom:4px;font-weight:600}
.btn{padding:10px 20px;border-radius:8px;border:none;font-weight:600;font-size:13px;cursor:pointer;transition:.3s;font-family:'Inter',sans-serif}
.btn-primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn-primary:hover{transform:translateY(-2px)}
.banner{width:100%;height:180px;border-radius:12px;overflow:hidden;margin-bottom:20px;background:rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;font-size:14px;color:#64748b}
.banner img{width:100%;height:100%;object-fit:cover}
.banner-upload{margin-top:8px}
.stream-status{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600}
.stream-status.online{background:rgba(74,222,128,.12);color:#4ade80}
.stream-status.offline{background:rgba(248,113,113,.12);color:#f87171}
.dj-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;margin-bottom:0}
.dj-grid .card{margin-bottom:0}
@media(min-width:1200px){.dj-grid{grid-template-columns:repeat(5,1fr)}}
.dj-tabs{display:flex;gap:4px;margin-bottom:20px;flex-wrap:wrap}
.dj-tab{padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;transition:.15s;color:#64748b;background:rgba(255,255,255,.04);border:1px solid transparent}
.dj-tab:hover{background:rgba(0,140,255,.08);color:#0A84FF}
.dj-tab.act{background:rgba(0,140,255,.15);border-color:rgba(0,140,255,.3);color:#0A84FF}
.dj-panel{display:none}
.dj-panel.act{display:block}
</style></head><body>
<div class="bg"></div>
<div class="topbar">
<h2>🎤 Planet <span>DJ</span></h2>
<div style="display:flex;align-items:center;gap:12px">
<span style="font-size:13px;color:#94a3b8"><?php echo htmlspecialchars($_SESSION['dj_user']['name'] ?? ''); ?></span>
<a href="/dj_panel.php?action=logout">Logout</a>
</div>
</div>
<div class="container">

<?php if ($success): ?><div class="alert" style="background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;padding:10px 14px;color:#4ade80;font-size:13px;margin-bottom:16px"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert" style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);border-radius:8px;padding:10px 14px;color:#f87171;font-size:13px;margin-bottom:16px"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="dj-tabs">
<div class="dj-tab act" onclick="sw(event,'overview')">Overview</div>
<div class="dj-tab" onclick="sw(event,'schedule')">Schedule</div>
<div class="dj-tab" onclick="sw(event,'requests')">Requests</div>
<div class="dj-tab" onclick="sw(event,'profile')">Profile</div>
<div class="dj-tab" onclick="sw(event,'gallery')">Gallery</div>
</div>

<div class="dj-panel act" id="pn-overview">
<!-- Stats -->
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
$djPort = $station->port ?? 8000;
$djPass = $station->plain_password ?? '';
$djHost = 'planet-hosts.com';
$djUsername = $_SESSION['dj_user']['username'] ?? '';
$djRealPass = $_SESSION['dj_user']['real_password'] ?? 'your-dj-password'; // Will be set below if available
// Try to get the actual DJ password from the DB (if this user owns the stream, show the source password instead)
$isOwner = !empty($_SESSION['dj_user']['is_owner']);
?>

<div class="dj-grid">

<!-- Broadcaster Info -->
<div class="card" style="border-color:rgba(0,191,255,.2)">
<h3 style="color:#0A84FF"><i class="fas fa-broadcast-tower"></i> Broadcaster Info</h3>
<div style="margin-bottom:12px;font-size:13px;color:#94a3b8;line-height:1.5">
Connect your broadcasting software with these details.
</div>
<div class="card" style="background:rgba(250,204,21,.06);border:1px solid rgba(250,204,21,.15);border-radius:8px;padding:12px;margin-bottom:12px">
<div style="font-size:11px;color:#facc15;font-weight:600;margin-bottom:4px">📻 SAM Broadcaster Users</div>
<div style="font-size:11px;color:#94a3b8">Enter your credentials as <strong style="color:#e0e0e0">djusername:djpassword</strong> in the <strong style="color:#e0e0e0">Password</strong> field. SAM only has one password field — combine them with a colon.</div>
</div>
<div style="background:rgba(0,0,0,.3);border-radius:8px;padding:16px;font-family:monospace;font-size:12px;line-height:2">
<div style="display:flex;justify-content:space-between;align-items:center">
<span><strong style="color:#64748b">Server:</strong> <span style="color:#4ade80" id="bi-server"><?php echo $djHost; ?></span></span>
<button class="btn" style="padding:2px 8px;font-size:10px;background:rgba(255,255,255,.06);color:#94a3b8;border:none;border-radius:4px;cursor:pointer" onclick="copyField('bi-server')">Copy</button>
</div>
<div style="display:flex;justify-content:space-between;align-items:center">
<span><strong style="color:#64748b">Port:</strong> <span style="color:#4ade80" id="bi-port"><?php echo $djPort + 2; ?></span> <span style="color:#64748b;font-size:10px">(DJ relay)</span></span>
<button class="btn" style="padding:2px 8px;font-size:10px;background:rgba(255,255,255,.06);color:#94a3b8;border:none;border-radius:4px;cursor:pointer" onclick="copyField('bi-port')">Copy</button>
</div>
<div style="display:flex;justify-content:space-between;align-items:center">
<span><strong style="color:#64748b">Username:</strong> <span style="color:#38bdf8" id="bi-user"><?php echo htmlspecialchars($djUsername); ?></span></span>
<button class="btn" style="padding:2px 8px;font-size:10px;background:rgba(255,255,255,.06);color:#94a3b8;border:none;border-radius:4px;cursor:pointer" onclick="copyField('bi-user')">Copy</button>
</div>
<div style="display:flex;justify-content:space-between;align-items:center">
<span><strong style="color:#64748b">Password:</strong> <span style="color:#facc15" id="bi-pass"><?php echo $isOwner ? htmlspecialchars($djPass) : '••••••••'; ?></span></span>
<button class="btn" style="padding:2px 8px;font-size:10px;background:rgba(255,255,255,.06);color:#94a3b8;border:none;border-radius:4px;cursor:pointer" onclick="togglePass()"><?php echo $isOwner ? 'Hide' : 'Show'; ?></button>
</div>
<div style="display:flex;justify-content:space-between;align-items:center">
<span><strong style="color:#64748b">Format:</strong> <span style="color:#94a3b8">MP3 · <?php echo $station->bitrate ?? 128; ?> kbps</span></span>
</div>
</div>
<div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
<button class="btn btn-primary" style="font-size:12px;padding:8px 16px" onclick="copyAll()">📋 Copy All</button>
<?php if (!$isOwner): ?>
<button class="btn" style="font-size:12px;padding:8px 16px;background:rgba(255,255,255,.06);color:#e0e0e0" onclick="window.location.href='/dj_panel.php?action=takeover'">🎤 Stop AutoDJ</button>
<?php endif; ?>
</div>
</div>
<script>
function copyField(id){var t=document.getElementById(id).textContent;navigator.clipboard.writeText(t);var b=event.target;b.textContent='Copied!';setTimeout(function(){b.textContent='Copy'},1500);}
function togglePass(){var p=document.getElementById('bi-pass');if(p.textContent=='••••••••'){p.textContent='<?php echo addslashes($djPass); ?>';event.target.textContent='Hide'}else{p.textContent='••••••••';event.target.textContent='Show'}}
function copyAll(){var t='Server: <?php echo addslashes($djHost); ?>\nPort: <?php echo $djPort + 2; ?>\nUsername: <?php echo addslashes($djUsername); ?>\nPassword: <?php echo $isOwner ? addslashes($djPass) : '<your DJ password>'; ?>\nFormat: MP3 <?php echo $station->bitrate ?? 128; ?>kbps';navigator.clipboard.writeText(t);var b=event.target;b.textContent='Copied All!';setTimeout(function(){b.textContent='📋 Copy All'},2000);}
</script>

</div>

<!-- Banner -->
<div class="banner">
<?php if ($djData->banner && file_exists($djData->banner)): ?>
<img src="/<?php echo $djData->banner; ?>" alt="Banner">
<?php else: ?>
<i class="fas fa-image" style="font-size:32px;opacity:.3"></i> No banner set
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

<!-- Kick Stream -->
<div class="card" style="border-color:rgba(248,113,113,.2)">
<h3 style="color:#f87171"><i class="fas fa-ban"></i> Kick Source</h3>
<p style="font-size:12px;color:#94a3b8;margin-bottom:12px">Force-disconnect the current source (AutoDJ or Live DJ). The stream will stop until someone reconnects.</p>
<?php if (empty($myStreams)): ?>
<p style="color:#64748b;font-size:13px">No streams available.</p>
<?php else: ?>
<?php foreach ($myStreams as $st): 
  $stEngine = strtolower($st->engine ?? $st->server_type ?? 'icecast');
  $stLabel = strtoupper($stEngine === 'shoutcast' || $stEngine === 'shoutcast1' || $stEngine === 'shoutcast2' ? 'SHOUTcast' : 'Icecast');
?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04)">
<div>
<strong><?php echo htmlspecialchars($st->name ?? "Stream #{$st->id}"); ?></strong>
<div style="font-size:11px;color:#64748b"><?php echo $stLabel; ?> · Port <?php echo $st->port; ?> · <span style="color:<?php echo $st->status === 'running' ? '#4ade80' : '#f87171'; ?>"><?php echo $st->status; ?></span></div>
</div>
<form method="POST" action="/dj_panel.php?action=kick" style="display:inline" onsubmit="return confirm('Kick the source on <?php echo htmlspecialchars($st->name ?? 'this stream'); ?>?');">
<input type="hidden" name="stream_id" value="<?php echo $st->id; ?>">
<button class="btn" style="padding:6px 14px;font-size:11px;background:rgba(248,113,113,.15);color:#f87171;width:auto">Kick</button>
</form>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>

</div>
</div>

<div class="dj-panel" id="pn-schedule">
<div class="dj-grid">
<!-- My Schedule -->
<?php
$sId = $_SESSION['dj_user']['stream_id'] ?? 0;
$djId = $_SESSION['dj_user']['id'] ?? 0;
$schStmt = $pdo->prepare("SELECT * FROM radio_schedule WHERE stream_id = ? AND (dj_id = ? OR dj_id = 0 OR dj_id IS NULL) ORDER BY day_of_week, start_time");
$schStmt->execute([$sId, $djId]);
$mySchedule = $schStmt->fetchAll(PDO::FETCH_OBJ);
$days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
?>
<div class="card">
<h3><i class="fas fa-calendar-alt"></i> My Schedule</h3>
<form method="POST" action="/dj_panel.php?action=add_schedule" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px">
<input name="show_name" placeholder="Show name" required style="flex:1;min-width:100px;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<select name="day_of_week" style="padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<?php foreach($days as $i=>$d): ?><option value="<?=$i?>"><?=$d?></option><?php endforeach; ?>
</select>
<input name="start_time" type="time" required style="padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<input name="end_time" type="time" required style="padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<button class="btn btn-primary" style="padding:7px 14px;font-size:11px;width:auto">Add Show</button>
</form>
<?php if (empty($mySchedule)): ?>
<p style="color:#64748b;font-size:13px">No shows scheduled yet.</p>
<?php else: ?>
<table style="width:100%;border-collapse:collapse;font-size:12px">
<tr style="border-bottom:1px solid rgba(255,255,255,.06)"><th style="padding:8px 6px;text-align:left;color:#64748b;font-weight:600">Show</th><th style="padding:8px 6px;text-align:left;color:#64748b;font-weight:600">Day</th><th style="padding:8px 6px;text-align:left;color:#64748b;font-weight:600">Time</th></tr>
<?php foreach ($mySchedule as $sh): ?>
<tr style="border-bottom:1px solid rgba(255,255,255,.04)">
<td style="padding:8px 6px"><?php echo htmlspecialchars($sh->show_name ?? 'Untitled'); ?></td>
<td style="padding:8px 6px"><?php echo $days[$sh->day_of_week] ?? $sh->day_of_week; ?></td>
<td style="padding:8px 6px"><?php echo htmlspecialchars($sh->start_time ?? '') . ' - ' . htmlspecialchars($sh->end_time ?? ''); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>
</div>
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
<p style="color:#64748b;font-size:13px">No pending requests.</p>
<?php else: ?>
<?php foreach ($requests as $r): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04)">
<div><strong style="font-size:14px"><?php echo htmlspecialchars($r->artist . ' - ' . $r->title); ?></strong>
<?php if ($r->guest_name): ?><div style="font-size:11px;color:#64748b">Requested by: <?php echo htmlspecialchars($r->guest_name); ?></div><?php endif; ?>
<?php if ($r->message): ?><div style="font-size:11px;color:#94a3b8;font-style:italic">"<?php echo htmlspecialchars($r->message); ?>"</div><?php endif; ?>
</div>
<a href="/dj_panel.php?action=remove_request&req_id=<?php echo $r->id; ?>" class="btn" style="padding:4px 10px;font-size:11px;width:auto;background:rgba(248,113,113,.15);color:#f87171">✕ Remove</a>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<div class="card">
<h3><i class="fas fa-tools"></i> DJ Tools</h3>
<a href="/dj_panel.php?action=download_playlist" class="btn" style="display:inline-flex;width:auto;padding:8px 16px;font-size:12px;margin-bottom:8px">📥 Download SAM Playlist (.lst)</a>
<p style="font-size:11px;color:#64748b">Downloads a SAM Broadcaster compatible playlist file.</p>
</div>
</div>
</div>

<div class="dj-panel" id="pn-profile">
<div class="dj-grid">
<div class="card">
<h3><i class="fas fa-user"></i> My Profile</h3>
<div class="profile-section">
<div class="avatar-box">
<?php if ($djData->avatar && file_exists($djData->avatar)): ?>
<img src="/<?php echo $djData->avatar; ?>" alt="Avatar">
<?php else: ?>
<i class="fas fa-microphone"></i>
<?php endif; ?>
</div>
<div style="flex:1;min-width:200px">
<form method="POST" enctype="multipart/form-data" style="margin-bottom:12px">
<input type="file" name="file" accept="image/*" style="display:none" id="avatarInput" onchange="this.form.submit()">
<input type="hidden" name="action" value="upload_avatar">
<label for="avatarInput" class="upload-btn"><i class="fas fa-camera"></i> Change Avatar</label>
</form>
<form method="POST" enctype="multipart/form-data">
<input type="file" name="file" accept="image/*" style="display:none" id="bannerInput" onchange="this.form.submit()">
<input type="hidden" name="action" value="upload_banner">
<label for="bannerInput" class="upload-btn"><i class="fas fa-image"></i> Change Banner</label>
</form>
</div>
</div>
<form method="POST" action="/dj_panel.php?action=save_profile" style="margin-top:16px">
<div class="form-group"><label>Display Name</label><input name="name" value="<?php echo htmlspecialchars($djData->name ?? ''); ?>"></div>
<div class="form-group"><label>Bio</label><textarea name="bio" rows="3"><?php echo htmlspecialchars($djData->bio ?? ''); ?></textarea></div>
<div class="form-group"><label>Website</label><input name="website_url" value="<?php echo htmlspecialchars($djData->website_url ?? ''); ?>" placeholder="https://"></div>
<button type="submit" class="btn btn-primary">Save Profile</button>
</form>
</div>
</div>
</div>

<div class="dj-panel" id="pn-gallery">
<div class="dj-grid">
<div class="card">
<h3><i class="fas fa-images"></i> Gallery</h3>
<p style="color:#64748b;font-size:13px">Gallery feature coming soon. Upload photos and show clips here.</p>
</div>
</div>
</div>

<script>
function sw(e,id){
  document.querySelectorAll('.dj-tab').forEach(function(t){t.classList.remove('act')});
  document.querySelectorAll('.dj-panel').forEach(function(p){p.classList.remove('act')});
  e.currentTarget.classList.add('act');
  document.getElementById('pn-'+id).classList.add('act');
}
</script>
<div class="form-group"><label>Display Name</label><input name="name" value="<?php echo htmlspecialchars($djData->name ?? ''); ?>"></div>
<div class="form-group"><label>Bio</label><textarea name="bio"><?php echo htmlspecialchars($djData->bio ?? ''); ?></textarea></div>
<div class="form-group"><label>Website / Social Link</label><input name="website_url" value="<?php echo htmlspecialchars($djData->website_url ?? ''); ?>" placeholder="https://"></div>
<button type="submit" class="btn btn-primary">Save Profile</button>
</form>
</div>

</div>
</body></html>

