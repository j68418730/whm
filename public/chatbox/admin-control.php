<?php
/**
 * Chat Control Center — ROOT ONLY
 * Super-admin overview of all chatbox tenants, their assignments, stats, and controls.
 * Per-tenant deep management stays in admin.php (unchanged).
 */
session_start();

// Root auth: admin panel session
$isRoot = !empty($_SESSION['is_admin']) || !empty($_SESSION['user']->is_admin);
if (!$isRoot) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><title>403</title></head><body style="background:#02050e;color:#fff;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh"><div style="text-align:center"><h1>403 — Root access only</h1><p style="color:#94a3b8">Sign in to the admin panel first.</p><a href="/admin/login" style="color:#38bdf8">Go to admin login</a></div></body></html>';
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$error = '';
$success = '';

// CSRF guard for POST actions (panel session token)
if ($_POST && isset($_POST['action'])) {
    $token = $_POST['_csrf'] ?? '';
    $valid = !empty($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
    if (!$valid) {
        $error = 'Invalid security token — please retry.';
    } else {
        $act = $_POST['action'];
        try {
            if ($act === 'create_tenant') {
                $userId = (int)($_POST['hosting_user_id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                if (!$userId || !$name) {
                    $error = 'Select a hosting user and enter a chat name.';
                } else {
                    // One tenant per hosting user (UNI key)
                    $dup = $pdo->prepare("SELECT id FROM chatbox_tenants WHERE hosting_user_id = ?");
                    $dup->execute([$userId]);
                    if ($dup->fetch()) {
                        $error = 'That hosting user already has a chat.';
                    } else {
                        $pdo->prepare("INSERT INTO chatbox_tenants (hosting_user_id, name, widget_title) VALUES (?,?,?)")
                            ->execute([$userId, $name, $name]);
                        $success = "Chat \"{$name}\" created and assigned.";
                    }
                }
            }
            if ($act === 'reassign_tenant') {
                $tenantId = (int)($_POST['tenant_id'] ?? 0);
                $newUser = (int)($_POST['hosting_user_id'] ?? 0);
                if ($tenantId && $newUser) {
                    $pdo->prepare("UPDATE chatbox_tenants SET hosting_user_id = ? WHERE id = ?")
                        ->execute([$newUser, $tenantId]);
                    $success = 'Tenant reassigned.';
                }
            }
            if ($act === 'toggle_tenant') {
                $tenantId = (int)($_POST['tenant_id'] ?? 0);
                $pdo->prepare("UPDATE chatbox_tenants SET is_active = 1 - is_active WHERE id = ?")->execute([$tenantId]);
                $success = 'Tenant status toggled.';
            }
            if ($act === 'delete_tenant') {
                $tenantId = (int)($_POST['tenant_id'] ?? 0);
                // Cascade: delete dependent rows then the tenant
                foreach (['chatbox_messages', 'chatbox_users', 'chatbox_rooms', 'chatbox_bans', 'chatbox_tokens', 'chatbox_moderation_log', 'chatbox_room_profiles', 'chatbox_emojis', 'chatbox_signals', 'chatbox_online'] as $tbl) {
                    $pdo->prepare("DELETE FROM $tbl WHERE tenant_id = ?")->execute([$tenantId]);
                }
                $pdo->prepare("DELETE FROM chatbox_tenants WHERE id = ?")->execute([$tenantId]);
                $success = 'Tenant and its data deleted.';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . htmlspecialchars($e->getMessage());
        }
        if ($success) header('Location: /chatbox/admin-control.php?ok=1');
        else header('Location: /chatbox/admin-control.php?err=1');
        exit;
    }
}

// Totals + per-tenant stats
$totals = (object)[
    'tenants' => (int)$pdo->query("SELECT COUNT(*) FROM chatbox_tenants")->fetchColumn(),
    'users' => (int)$pdo->query("SELECT COUNT(*) FROM chatbox_users")->fetchColumn(),
    'rooms' => (int)$pdo->query("SELECT COUNT(*) FROM chatbox_rooms")->fetchColumn(),
    'messages' => (int)$pdo->query("SELECT COUNT(*) FROM chatbox_messages")->fetchColumn(),
    'online' => (int)$pdo->query("SELECT COUNT(*) FROM chatbox_users WHERE last_active > DATE_SUB(NOW(), INTERVAL 5 MINUTE)")->fetchColumn(),
];

$tenants = $pdo->query("
    SELECT t.*, u.username AS h_username, u.email AS h_email,
        (SELECT COUNT(*) FROM chatbox_users cu WHERE cu.tenant_id = t.id) AS user_count,
        (SELECT COUNT(*) FROM chatbox_rooms cr WHERE cr.tenant_id = t.id) AS room_count,
        (SELECT COUNT(*) FROM chatbox_messages cm WHERE cm.tenant_id = t.id) AS msg_count,
        (SELECT COUNT(*) FROM chatbox_users co WHERE co.tenant_id = t.id AND co.last_active > DATE_SUB(NOW(), INTERVAL 5 MINUTE)) AS online
    FROM chatbox_tenants t
    LEFT JOIN hosting_users u ON u.id = t.hosting_user_id
    ORDER BY t.id
")->fetchAll(PDO::FETCH_OBJ);

$hostingUsers = $pdo->query("SELECT id, username, email FROM hosting_users ORDER BY username")->fetchAll(PDO::FETCH_OBJ);
$assignedIds = array_map(fn($t) => (int)$t->hosting_user_id, $tenants);
$unassigned = array_filter($hostingUsers, fn($u) => !in_array((int)$u->id, $assignedIds));

$okFlag = isset($_GET['ok']);
$errFlag = isset($_GET['err']);
$csrf = $_SESSION['_csrf_token'] ?? ($_SESSION['_csrf_token'] = bin2hex(random_bytes(32)));
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Chat Control Center — Root</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#02050e;color:#fff;font-family:Inter,sans-serif;padding:22px;max-width:1200px;margin:0 auto}
.top{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px}
.top h1{font-size:22px}h1 span{color:#008cff}
.badge{background:rgba(0,191,255,.12);color:#38bdf8;border:1px solid rgba(0,191,255,.25);padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px}
.stat{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:16px;text-align:center}
.stat .v{font-size:26px;font-weight:800}.stat .l{font-size:11px;color:#64748b;margin-top:2px}
.stat .v.blue{color:#38bdf8}.stat .v.green{color:#4ade80}.stat .v.yellow{color:#facc15}.stat .v.red{color:#f87171}
.card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:18px;margin-bottom:18px}
.card h2{font-size:15px;color:#008cff;margin-bottom:12px}
.card h2 span{color:#64748b;font-size:12px;font-weight:400}
.alert{padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px}
.alert.ok{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.3);color:#4ade80}
.alert.err{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#f87171}
table{width:100%;border-collapse:collapse;font-size:13px}
th{text-align:left;padding:8px;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid rgba(255,255,255,.06)}
td{padding:8px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle}
.tag{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600}
.tag.on{background:rgba(74,222,128,.12);color:#4ade80}.tag.off{background:rgba(248,113,113,.12);color:#f87171}
.tag.assigned{background:rgba(0,191,255,.12);color:#38bdf8}
.btn{padding:6px 12px;border-radius:6px;border:none;font-weight:600;cursor:pointer;font-size:12px;font-family:Inter;color:#fff}
.btn-primary{background:linear-gradient(135deg,#008cff,#3bb8ff)}
.btn-danger{background:rgba(248,113,113,.2);color:#f87171}
.btn-danger:hover{background:rgba(248,113,113,.35)}
.btn-ghost{background:rgba(255,255,255,.06);color:#e2e8f0}
.btn-sm{padding:3px 8px;font-size:11px}
label{display:block;font-size:12px;color:#94a3b8;margin-bottom:4px;font-weight:600}
input,select{width:100%;padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none;font-size:13px;margin-bottom:8px;font-family:Inter}
.form-row{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
.form-row > div{flex:1;min-width:180px}
.form-row .btn{margin-bottom:8px}
a{color:#38bdf8;text-decoration:none}
.muted{color:#64748b;font-size:12px}
.empty{padding:20px;text-align:center;color:#64748b;font-size:13px}
</style></head><body>

<div class="top">
    <h1>💬 Chat <span>Control Center</span> <span class="badge">ROOT</span></h1>
    <div>
        <a class="btn btn-ghost" href="/admin/dashboard" style="margin-right:8px">← Admin Panel</a>
        <a class="btn btn-ghost" href="/chatbox/admin.php">Per-Tenant Admin</a>
    </div>
</div>

<?php if ($okFlag): ?><div class="alert ok">Saved.</div><?php endif; ?>
<?php if ($errFlag): ?><div class="alert err"><?php echo $error ?: 'Something went wrong.'; ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="stats">
    <div class="stat"><div class="v blue"><?php echo $totals->tenants; ?></div><div class="l">Tenants</div></div>
    <div class="stat"><div class="v green"><?php echo $totals->users; ?></div><div class="l">Total Users</div></div>
    <div class="stat"><div class="v yellow"><?php echo $totals->rooms; ?></div><div class="l">Rooms</div></div>
    <div class="stat"><div class="v red"><?php echo $totals->messages; ?></div><div class="l">Messages</div></div>
    <div class="stat"><div class="v green"><?php echo $totals->online; ?></div><div class="l">Online Now</div></div>
</div>

<div class="card">
    <h2>➕ Create &amp; Assign a Chat</h2>
    <form method="POST" class="form-row">
        <input type="hidden" name="action" value="create_tenant">
        <input type="hidden" name="_csrf" value="<?php echo $csrf; ?>">
        <div style="flex:2"><label>Hosting User</label>
        <select name="hosting_user_id" required>
            <option value="">— Select hosting user —</option>
            <?php foreach ($hostingUsers as $u): ?>
                <option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->username); ?> (<?php echo htmlspecialchars($u->email); ?>)</option>
            <?php endforeach; ?>
        </select></div>
        <div><label>Chat Name</label><input name="name" placeholder="e.g. My Station Chat" required></div>
        <button class="btn btn-primary">Create</button>
    </form>
</div>

<div class="card">
    <h2>📋 All Chat Tenants <span>(<?php echo count($tenants); ?>)</span></h2>
    <?php if (!$tenants): ?>
        <div class="empty">No chat tenants yet. Use the form above to create one.</div>
    <?php else: ?>
    <table>
        <tr><th>#</th><th>Chat</th><th>Assigned To</th><th>Users</th><th>Rooms</th><th>Msgs</th><th>Online</th><th>Status</th><th>Actions</th></tr>
        <?php foreach ($tenants as $t): ?>
        <tr>
            <td class="muted"><?php echo $t->id; ?></td>
            <td><strong><?php echo htmlspecialchars($t->name); ?></strong><br><span class="muted"><?php echo htmlspecialchars($t->widget_title ?? ''); ?></span></td>
            <td>
                <?php if ($t->h_username): ?>
                    <span class="tag assigned"><?php echo htmlspecialchars($t->h_username); ?></span>
                    <div class="muted" style="margin-top:2px"><?php echo htmlspecialchars($t->h_email); ?></div>
                <?php else: ?><span class="muted">Unassigned</span><?php endif; ?>
            </td>
            <td><?php echo $t->user_count; ?></td>
            <td><?php echo $t->room_count; ?></td>
            <td><?php echo $t->msg_count; ?></td>
            <td><?php echo $t->online ?: '0'; ?></td>
            <td><span class="tag <?php echo $t->is_active ? 'on' : 'off'; ?>"><?php echo $t->is_active ? 'Active' : 'Disabled'; ?></span></td>
            <td>
                <form method="POST" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                    <input type="hidden" name="_csrf" value="<?php echo $csrf; ?>">
                    <a class="btn btn-primary btn-sm" href="/chatbox/admin.php?tenant_id=<?php echo $t->id; ?>" target="_blank">Manage</a>
                    <select name="hosting_user_id" style="width:auto;margin:0" onchange="this.form.submit()">
                        <option value="">Reassign…</option>
                        <?php foreach ($hostingUsers as $u): ?>
                            <option value="<?php echo $u->id; ?>" <?php echo (int)$u->id === (int)$t->hosting_user_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->username); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="action" value="reassign_tenant">
                    <input type="hidden" name="tenant_id" value="<?php echo $t->id; ?>">
                </form>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="_csrf" value="<?php echo $csrf; ?>">
                    <input type="hidden" name="action" value="toggle_tenant">
                    <input type="hidden" name="tenant_id" value="<?php echo $t->id; ?>">
                    <button class="btn btn-sm <?php echo $t->is_active ? 'btn-danger' : 'btn-ghost'; ?>" title="Toggle active"><?php echo $t->is_active ? 'Disable' : 'Enable'; ?></button>
                </form>
                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this chat and ALL its users, rooms, messages, and bans? This cannot be undone.')">
                    <input type="hidden" name="_csrf" value="<?php echo $csrf; ?>">
                    <input type="hidden" name="action" value="delete_tenant">
                    <input type="hidden" name="tenant_id" value="<?php echo $t->id; ?>">
                    <button class="btn btn-sm btn-danger" title="Delete chat">✕</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>👥 Hosting Users Without a Chat <span>(<?php echo count($unassigned); ?>)</span></h2>
    <?php if (!$unassigned): ?>
        <div class="empty">Every hosting user has a chat assigned.</div>
    <?php else: ?>
    <table>
        <tr><th>User</th><th>Email</th><th></th></tr>
        <?php foreach ($unassigned as $u): ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($u->username); ?></strong></td>
            <td class="muted"><?php echo htmlspecialchars($u->email); ?></td>
            <td style="text-align:right">
                <form method="POST" style="display:inline">
                    <input type="hidden" name="_csrf" value="<?php echo $csrf; ?>">
                    <input type="hidden" name="action" value="create_tenant">
                    <input type="hidden" name="hosting_user_id" value="<?php echo $u->id; ?>">
                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($u->username) . "'s Chat"; ?>">
                    <button class="btn btn-primary btn-sm">Create Chat</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>

</body></html>
