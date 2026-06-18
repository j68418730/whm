<?php
session_start();
$action = $_GET['action'] ?? 'login';
$error = '';
$success = '';
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// ─── LOGIN ───
if ($_POST && $action === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT d.*, s.port, s.status as stream_status, s.current_dj, s.autodj_active,
        (SELECT COUNT(*) FROM radio_listener_analytics WHERE stream_id = d.stream_id AND date = CURDATE()) as today_listeners
        FROM radio_djs d JOIN radio_streams s ON d.stream_id = s.id WHERE d.username = ? AND d.status = 'active'");
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
        $dir = 'storage/dj/' . $_SESSION['dj_user']['id'] . '/';
        @mkdir($dir, 0755, true);
        $name = 'banner_' . bin2hex(random_bytes(8)) . '.' . $ext;
        move_uploaded_file($_FILES['file']['tmp_name'], $dir . $name);
        $pdo->prepare("UPDATE radio_djs SET banner = ? WHERE id = ?")->execute([$dir . $name, $_SESSION['dj_user']['id']]);
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
        $dir = 'storage/dj/' . $_SESSION['dj_user']['id'] . '/';
        @mkdir($dir, 0755, true);
        $name = 'avatar_' . bin2hex(random_bytes(8)) . '.' . $ext;
        move_uploaded_file($_FILES['file']['tmp_name'], $dir . $name);
        $pdo->prepare("UPDATE radio_djs SET avatar = ? WHERE id = ?")->execute([$dir . $name, $_SESSION['dj_user']['id']]);
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
    $stmt = $pdo->prepare("SELECT d.*, s.port, s.status as stream_status, s.listener_count, s.current_dj, s.autodj_active,
        (SELECT COUNT(*) FROM radio_playlist_items pi JOIN radio_playlists p ON pi.playlist_id = p.id WHERE p.stream_id = d.stream_id) as track_count
        FROM radio_djs d JOIN radio_streams s ON d.stream_id = s.id WHERE d.id = ?");
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

<!-- Stats -->
<div class="grid">
<div class="stat-card" style="--c:#4ade80"><div class="num"><?php echo $djData->stream_status ?? 'N/A'; ?></div><div class="label">Stream Status</div></div>
<div class="stat-card" style="--c:#38bdf8"><div class="num"><?php echo $djData->listener_count ?? 0; ?></div><div class="label">Current Listeners</div></div>
<div class="stat-card" style="--c:#facc15"><div class="num"><?php echo $djData->track_count ?? 0; ?></div><div class="label">Library Tracks</div></div>
<div class="stat-card" style="--c:#a78bfa"><div class="num"><?php echo $djData->autodj_active ? 'AutoDJ' : ($djData->current_dj ? 'Live DJ' : 'Offline'); ?></div><div class="label">Source</div></div>
</div>

<!-- Banner -->
<div class="banner">
<?php if ($djData->banner && file_exists($djData->banner)): ?>
<img src="/<?php echo $djData->banner; ?>" alt="Banner">
<?php else: ?>
<i class="fas fa-image" style="font-size:32px;opacity:.3"></i> No banner set
<?php endif; ?>
</div>

<!-- Profile -->
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
<div class="form-group"><label>Bio</label><textarea name="bio"><?php echo htmlspecialchars($djData->bio ?? ''); ?></textarea></div>
<div class="form-group"><label>Website / Social Link</label><input name="website_url" value="<?php echo htmlspecialchars($djData->website_url ?? ''); ?>" placeholder="https://"></div>
<button type="submit" class="btn btn-primary">Save Profile</button>
</form>
</div>

<!-- Song Requests -->
<?php
$reqs = $pdo->prepare("SELECT * FROM radio_requests WHERE stream_id = ? AND status = 'pending' ORDER BY created_at ASC");
$reqs->execute([$_SESSION['dj_user']['stream_id']]);
$requests = $reqs->fetchAll(PDO::FETCH_OBJ);
?>
<div class="card">
<h3><i class="fas fa-music"></i> Song Requests (<?php echo count($requests); ?>)</h3>
<?php if (empty($requests)): ?>
<p style="color:#64748b;font-size:13px">No pending requests.</p>
<?php else: ?>
<div style="<?php echo count($requests) > 5 ? 'max-height:200px;overflow-y:auto' : ''; ?>">
<?php foreach ($requests as $r): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04)">
<div>
<strong style="font-size:14px"><?php echo htmlspecialchars($r->artist . ' - ' . $r->title); ?></strong>
<?php if ($r->guest_name): ?><div style="font-size:11px;color:#64748b">Requested by: <?php echo htmlspecialchars($r->guest_name); ?></div><?php endif; ?>
<?php if ($r->message): ?><div style="font-size:11px;color:#94a3b8;font-style:italic">"<?php echo htmlspecialchars($r->message); ?>"</div><?php endif; ?>
</div>
<a href="/dj_panel.php?action=remove_request&req_id=<?php echo $r->id; ?>" class="btn" style="padding:4px 10px;font-size:11px;width:auto;background:rgba(248,113,113,.15);color:#f87171">✕ Remove</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<!-- Tools -->
<div class="card">
<h3><i class="fas fa-tools"></i> DJ Tools</h3>
<a href="/dj_panel.php?action=download_playlist" class="btn" style="display:inline-flex;width:auto;padding:8px 16px;font-size:12px;margin-bottom:8px">📥 Download SAM Playlist (.lst)</a>
<p style="font-size:11px;color:#64748b">Downloads a SAM Broadcaster compatible playlist file with all your tracks.</p>
</div>

<!-- Embed Player -->
<div class="card">
<h3><i class="fas fa-code"></i> Embed Now Playing Widget</h3>
<div style="background:rgba(0,0,0,.3);padding:10px;border-radius:6px;font-family:monospace;font-size:11px;color:#4ade80;word-break:break-all;margin-bottom:8px">
&lt;iframe src="http://45.61.59.55/radio/nowplaying.php?stream=<?php echo $_SESSION['dj_user']['stream_id']; ?>&scroll=yes" width="100%" height="400"&gt;&lt;/iframe&gt;
</div>
<p style="font-size:11px;color:#64748b">Add <code>?scroll=no</code> to disable scrolling.</p>
</div>

<!-- Connection Info -->
<div class="card">
<h3><i class="fas fa-plug"></i> Stream Connection</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:13px">
<div><span style="color:#64748b">Server</span><br><strong><?php echo $_SERVER['SERVER_NAME'] ?? '45.61.59.55'; ?></strong></div>
<div><span style="color:#64748b">Port</span><br><strong><?php echo $djData->port ?? 'N/A'; ?></strong></div>
<div><span style="color:#64748b">Mount</span><br><strong>/stream.ogg</strong></div>
<div><span style="color:#64748b">Username</span><br><strong><?php echo htmlspecialchars($djData->username); ?></strong></div>
</div>
<div style="margin-top:12px;padding:10px;background:rgba(0,0,0,.2);border-radius:8px;font-size:11px;color:#64748b">
<i class="fas fa-info-circle"></i> Use these details in your streaming software (Mixxx, SAM Broadcaster, OBS, etc.)
</div>
</div>

</div>
</body></html>
