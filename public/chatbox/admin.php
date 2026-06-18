<?php
session_start();
$action = $_GET['action'] ?? 'login';
$error = '';
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Admin login
if ($action === 'login' && $_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT u.*, t.id as tenant_id FROM chatbox_users u JOIN chatbox_tenants t ON u.tenant_id = t.id WHERE u.username = ? AND u.role IN ('owner','admin')");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_OBJ);
    if ($admin && password_verify($password, $admin->password_hash)) {
        $_SESSION['chatbox_admin'] = ['id' => $admin->id, 'tenant_id' => $admin->tenant_id, 'username' => $admin->username, 'role' => $admin->role];
        header('Location: /chatbox/admin.php?action=dashboard');
        exit;
    }
    $error = 'Invalid credentials';
}

if (!isset($_SESSION['chatbox_admin'])) {
    ?>
    <!DOCTYPE html><html><head><title>Chat Admin Login</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body{background:#02050e;color:#fff;font-family:Inter,sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh}
    .card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:36px;max-width:380px;width:92%}
    h1{text-align:center;font-size:20px;margin-bottom:20px}h1 span{color:#008cff}
    .form-group{margin-bottom:14px}
    .form-group label{display:block;font-size:12px;color:#94a3b8;margin-bottom:4px}
    .form-group input{width:100%;padding:10px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff;outline:none}
    .btn{width:100%;padding:12px;background:linear-gradient(135deg,#008cff,#3bb8ff);border:none;border-radius:8px;color:#fff;font-weight:700;cursor:pointer}
    .error{color:#f87171;font-size:13px;margin-bottom:12px;text-align:center}</style></head><body>
    <div class="card"><h1>Chat <span>Admin</span></h1>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="POST"><div class="form-group"><label>Username</label><input name="username" required></div>
    <div class="form-group"><label>Password</label><input name="password" type="password" required></div>
    <button class="btn">Login</button></form></div></body></html>
    <?php exit;
}

$admin = $_SESSION['chatbox_admin'];
$tenantId = $admin['tenant_id'];

// Handle POST actions
if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_settings') {
        $pdo->prepare("UPDATE chatbox_tenants SET widget_title=?, widget_color=?, widget_bg=?, widget_text_color=?, font_family=?, player_html=?, guest_enabled=?, registration_enabled=?, voice_enabled=? WHERE id=?")
            ->execute([$_POST['title'], $_POST['color'], $_POST['bg'], $_POST['text_color'], $_POST['font'], $_POST['player_html'], (int)$_POST['guest'], (int)$_POST['reg'], (int)$_POST['voice'], $tenantId]);
    }
    if ($_POST['action'] === 'add_room') {
        $pdo->prepare("INSERT INTO chatbox_rooms (tenant_id, name, type, password) VALUES (?, ?, ?, ?)")
            ->execute([$tenantId, $_POST['name'], $_POST['type'], $_POST['type'] === 'password' ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null]);
    }
    if ($_POST['action'] === 'delete_room') {
        $pdo->prepare("DELETE FROM chatbox_rooms WHERE id = ? AND tenant_id = ?")->execute([(int)$_POST['room_id'], $tenantId]);
    }
    if ($_POST['action'] === 'add_user') {
        $pdo->prepare("INSERT INTO chatbox_users (tenant_id, username, password_hash, display_name, role, email) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$tenantId, $_POST['username'], password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['display_name'], $_POST['role'], $_POST['email']]);
    }
    if ($_POST['action'] === 'delete_user') {
        $pdo->prepare("DELETE FROM chatbox_users WHERE id = ? AND tenant_id = ?")->execute([(int)$_POST['user_id'], $tenantId]);
    }
    header('Location: /chatbox/admin.php?action=dashboard');
    exit;
}

$tenant = $pdo->prepare("SELECT * FROM chatbox_tenants WHERE id = ?");
$tenant->execute([$tenantId]);
$tenant = $tenant->fetch(PDO::FETCH_OBJ);

$users = $pdo->prepare("SELECT * FROM chatbox_users WHERE tenant_id = ? ORDER BY role, username");
$users->execute([$tenantId]);
$usersList = $users->fetchAll(PDO::FETCH_OBJ);

$rooms = $pdo->prepare("SELECT * FROM chatbox_rooms WHERE tenant_id = ? ORDER BY sort_order");
$rooms->execute([$tenantId]);
$roomsList = $rooms->fetchAll(PDO::FETCH_OBJ);

$bans = $pdo->prepare("SELECT b.*, u.username as uname FROM chatbox_bans b LEFT JOIN chatbox_users u ON b.user_id = u.id WHERE b.tenant_id = ? ORDER BY b.created_at DESC LIMIT 20");
$bans->execute([$tenantId]);
$bansList = $bans->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Chat Admin - <?php echo htmlspecialchars($admin['username']); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#02050e;color:#fff;font-family:Inter,sans-serif;padding:20px}
h1{font-size:22px;margin-bottom:16px}h1 span{color:#008cff}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px}
.card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:20px;margin-bottom:16px}
.card h2{font-size:15px;color:#008cff;margin-bottom:12px}
label{display:block;font-size:12px;color:#94a3b8;margin-bottom:4px;font-weight:600}
input,select,textarea{width:100%;padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none;font-size:13px;margin-bottom:8px;font-family:Inter}
.btn{padding:8px 16px;border-radius:6px;border:none;font-weight:600;cursor:pointer;font-size:12px;font-family:Inter}
.btn-primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn-danger{background:rgba(248,113,113,.15);color:#f87171}
.btn-sm{padding:4px 10px;font-size:11px}
table{width:100%;border-collapse:collapse;font-size:13px}
td,th{padding:8px;text-align:left;border-bottom:1px solid rgba(255,255,255,.04)}
.embed-box{background:rgba(0,0,0,.3);padding:12px;border-radius:6px;font-family:monospace;font-size:12px;color:#4ade80;word-break:break-all;margin-bottom:8px}
a{color:#38bdf8;text-decoration:none;font-size:13px}
iframe{width:100%;height:400px;border:1px solid rgba(255,255,255,.1);border-radius:8px;margin-top:8px}
</style></head><body>
<h1>💬 Chat <span>Admin</span> — <?php echo htmlspecialchars($admin['username']); ?> <a href="?action=logout" style="float:right;color:#f87171;font-size:13px">Logout</a></h1>

<div class="grid">

<div class="card">
<h2>⚙️ Settings</h2>
<form method="POST">
<input type="hidden" name="action" value="update_settings">
<label>Widget Title</label><input name="title" value="<?php echo htmlspecialchars($tenant->widget_title ?? ''); ?>">
<div style="display:flex;gap:8px">
<div style="flex:1"><label>Accent Color</label><input name="color" type="color" value="<?php echo $tenant->widget_color ?? '#008cff'; ?>"></div>
<div style="flex:1"><label>Background</label><input name="bg" type="color" value="<?php echo $tenant->widget_bg ?? '#0a0e1a'; ?>"></div>
<div style="flex:1"><label>Text Color</label><input name="text_color" type="color" value="<?php echo $tenant->widget_text_color ?? '#ffffff'; ?>"></div>
</div>
<label>Font Family</label><input name="font" value="<?php echo htmlspecialchars($tenant->font_family ?? 'Inter, sans-serif'); ?>">
<label>Player Embed Code (HTML/iframe)</label><textarea name="player_html" rows="3" placeholder="&lt;iframe src=&quot;https://player.example.com/stream&quot; width=&quot;100%&quot; height=&quot;150&quot;&gt;&lt;/iframe&gt;"><?php echo htmlspecialchars($tenant->player_html ?? ''); ?></textarea>
<label><input type="checkbox" name="guest" value="1" <?php echo $tenant->guest_enabled ? 'checked' : ''; ?>> Allow Guests</label>
<label><input type="checkbox" name="reg" value="1" <?php echo $tenant->registration_enabled ? 'checked' : ''; ?>> Allow Registration</label>
<label><input type="checkbox" name="voice" value="1" <?php echo $tenant->voice_enabled ? 'checked' : ''; ?>> Enable Voice</label>
<button class="btn btn-primary" style="margin-top:8px">Save</button>
</form>
</div>

<div class="card">
<h2>🪪 Embed Code</h2>
<div class="embed-box">&lt;script src="http://45.61.59.55/chatbox/widget.js.php?tenant_id=<?php echo $tenantId; ?>"&gt;&lt;/script&gt;</div>
<button class="btn btn-sm btn-primary" onclick="navigator.clipboard.writeText('&lt;script src=&quot;http://45.61.59.55/chatbox/widget.js.php?tenant_id=<?php echo $tenantId; ?>&quot;&gt;&lt;/script&gt;')">📋 Copy Embed</button>
<div style="margin-top:12px;font-size:12px;color:#64748b">Or use iframe:</div>
<div class="embed-box">&lt;iframe src="http://45.61.59.55/chatbox/embed.php?tenant_id=<?php echo $tenantId; ?>" width="360" height="500"&gt;&lt;/iframe&gt;</div>
<button class="btn btn-sm btn-primary" onclick="navigator.clipboard.writeText('&lt;iframe src=&quot;http://45.61.59.55/chatbox/embed.php?tenant_id=<?php echo $tenantId; ?>&quot; width=&quot;360&quot; height=&quot;500&quot;&gt;&lt;/iframe&gt;')">📋 Copy Iframe</button>
</div>

<div class="card">
<h2>🚪 Rooms</h2>
<form method="POST" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px">
<input type="hidden" name="action" value="add_room">
<input name="name" placeholder="Room name" required style="flex:1;margin:0">
<select name="type" style="width:auto;margin:0"><option value="public">Public</option><option value="private">Private</option><option value="password">Password</option></select>
<input name="password" placeholder="Password" style="width:auto;margin:0">
<button class="btn btn-sm btn-primary">+ Add</button>
</form>
<?php foreach ($roomsList as $r): ?>
<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px">
<span><?php echo htmlspecialchars($r->name); ?> <span style="color:#64748b;font-size:11px">(<?php echo $r->type; ?>)</span></span>
<form method="POST" style="display:inline"><input type="hidden" name="action" value="delete_room"><input type="hidden" name="room_id" value="<?php echo $r->id; ?>"><button class="btn btn-sm btn-danger">✕</button></form>
</div>
<?php endforeach; ?>
</div>

<div class="card">
<h2>👥 Users (<?php echo count($usersList); ?>)</h2>
<form method="POST" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px">
<input type="hidden" name="action" value="add_user">
<input name="username" placeholder="Username" required style="flex:1;min-width:100px;margin:0">
<input name="password" placeholder="Password" type="password" required style="width:auto;margin:0">
<input name="display_name" placeholder="Display name" style="width:auto;margin:0">
<select name="role" style="width:auto;margin:0"><option value="member">Member</option><option value="mod">Mod</option><option value="admin">Admin</option></select>
<button class="btn btn-sm btn-primary">+ Add</button>
</form>
<table><tr><th>User</th><th>Role</th><th>Status</th><th>Action</th></tr>
<?php foreach ($usersList as $u): ?>
<tr>
<td><?php echo htmlspecialchars($u->display_name ?: $u->username); ?></td>
<td><?php echo $u->role; ?></td>
<td><?php if ($u->is_banned): ?><span style="color:#f87171">Banned</span><?php elseif ($u->voice_denied): ?><span style="color:#facc15">No Voice</span><?php else: ?><span style="color:#4ade80">OK</span><?php endif; ?></td>
<td>
<form method="POST" style="display:inline" action="/chatbox/api.php?action=ban">
<input type="hidden" name="user_id" value="<?php echo $u->id; ?>">
<input type="hidden" name="reason" placeholder="Reason" style="display:inline;width:80px;padding:2px 6px;font-size:11px">
<button class="btn btn-sm btn-danger">Ban</button>
</form>
<form method="POST" style="display:inline"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="<?php echo $u->id; ?>"><button class="btn btn-sm btn-danger">✕</button></form>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>

</div>

<!-- Preview -->
<div class="card">
<h2>👁️ Preview</h2>
<iframe src="/chatbox/embed.php?tenant_id=<?php echo $tenantId; ?>"></iframe>
</div>

</body></html>
