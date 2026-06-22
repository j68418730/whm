<?php
$scriptName = $_SERVER['SCRIPT_FILENAME'] ?? '';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (str_ends_with($scriptName, '/public/user/admins.php') && !str_contains($requestUri, '/user/admins.php')) {
    header('Location: /user/admins');
    exit;
}
if (!isset($hosting) || !$hosting) { echo 'No account'; exit; }
require_once BASE_PATH . '/core/ClientPermissions.php';

if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'mod';
        $perms = $_POST['perms'] ?? [];
        $id = (int)($_POST['id'] ?? 0);
        if ($_POST['action'] === 'create') {
            $check = $pdo->prepare("SELECT id FROM client_sub_users WHERE client_id = ? AND username = ?");
            $check->execute([$hosting->id, $username]);
            if ($check->fetch()) { echo 'Username exists'; exit; }
            $pdo->prepare("INSERT INTO client_sub_users (client_id, username, password_hash, email, role, permissions) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$hosting->id, $username, password_hash($password, PASSWORD_DEFAULT), $_POST['email'], $role, json_encode($perms)]);
        } else {
            if ($password) {
                $pdo->prepare("UPDATE client_sub_users SET password_hash = ?, role = ?, permissions = ? WHERE id = ? AND client_id = ?")
                    ->execute([password_hash($password, PASSWORD_DEFAULT), $role, json_encode($perms), $id, $hosting->id]);
            } else {
                $pdo->prepare("UPDATE client_sub_users SET role = ?, permissions = ? WHERE id = ? AND client_id = ?")
                    ->execute([$role, json_encode($perms), $id, $hosting->id]);
            }
        }
    }
    if ($_POST['action'] === 'delete') {
        $pdo->prepare("DELETE FROM client_sub_users WHERE id = ? AND client_id = ?")->execute([(int)$_POST['id'], $hosting->id]);
    }
    header('Location: /user/admins'); exit;
}

$subUsers = getClientSubUsers($hosting->id);
?>
<div class="card">
<h3>My Admins & Moderators</h3>
<p style="color:var(--text_muted);font-size:13px;margin-bottom:14px">Create admins and moderators who can help manage your account.</p>

<h4 style="margin-bottom:10px">Create Admin / Moderator</h4>
<form method="POST">
<input type="hidden" name="action" value="create">
<div class="row g-2">
<div class="col-md-4"><div class="form-group"><label>Username</label><input name="username" class="form-control" required></div></div>
<div class="col-md-4"><div class="form-group"><label>Password</label><input name="password" type="password" class="form-control" required></div></div>
<div class="col-md-4"><div class="form-group"><label>Role</label><select name="role" class="form-select"><option value="admin">Admin</option><option value="mod">Moderator</option></select></div></div>
<div class="col-md-6"><div class="form-group"><label>Email</label><input name="email" type="email" class="form-control"></div></div>
</div>
<label style="font-weight:600;display:block;margin:10px 0 4px">Grant Permissions:</label>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:4px;margin-bottom:10px">
<?php foreach ($allPermissions as $key => $label): ?>
<label style="font-size:12px;display:flex;align-items:center;gap:4px;cursor:pointer"><input type="checkbox" name="perms[]" value="<?php echo $key; ?>" checked> <?php echo $label; ?></label>
<?php endforeach; ?>
</div>
<button class="btn btn-primary">Create User</button>
</form>
</div>

<h4 style="margin:12px 0 8px">Existing Users (<?php echo count($subUsers); ?>)</h4>
<table class="table table-hover"><thead><tr><th>Username</th><th>Role</th><th>Permissions</th><th>Last Login</th><th></th></tr></thead>
<tbody><?php foreach ($subUsers as $su): ?>
<tr>
<td><strong><?php echo htmlspecialchars($su->username); ?></strong></td>
<td><?php echo $su->role; ?></td>
<td style="font-size:11px"><?php $perms = json_decode($su->permissions, true) ?: []; echo implode(', ', array_map(fn($p) => $allPermissions[$p] ?? $p, $perms)); ?></td>
<td><?php echo $su->last_login ?: 'Never'; ?></td>
<td><form method="POST" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo $su->id; ?>"><button class="btn btn-sm btn-danger">&#128465;</button></form></td>
</tr>
<?php endforeach; ?></tbody></table>
