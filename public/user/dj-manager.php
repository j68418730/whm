<?php
$scriptName = $_SERVER['SCRIPT_FILENAME'] ?? '';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (str_ends_with($scriptName, '/public/user/dj-manager.php') && !str_contains($requestUri, '/user/dj-manager.php')) {
    header('Location: /user/dj-manager');
    exit;
}
// DJ Manager - requires $app, $user, $pdo, $hosting from controller
if (!isset($hosting) || !$hosting) { echo 'No account'; exit; }
$streams = $pdo->prepare("SELECT * FROM radio_streams WHERE user_id = ?");
$streams->execute([$hosting->id]);
$streams = $streams->fetchAll(PDO::FETCH_OBJ);
$streamId = (int)($_GET['stream'] ?? ($streams[0]->id ?? 0));
$tab = $_GET['tab'] ?? 'djs';

if ($_POST) {
    if ($_POST['action'] === 'create_dj') {
        $pw = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO radio_djs (stream_id, username, password, name, email, status) VALUES (?,?,?,?,?,?)")
            ->execute([$streamId, $_POST['username'], $pw, $_POST['name'], $_POST['email'], 'active']);
    }
    if ($_POST['action'] === 'delete_dj') {
        $pdo->prepare("DELETE FROM radio_djs WHERE id=? AND stream_id=?")->execute([(int)$_POST['dj_id'], $streamId]);
    }
    if ($_POST['action'] === 'ban_ip') {
        $pdo->prepare("INSERT INTO radio_dj_bans (stream_id, ip_address, reason) VALUES (?,?,?)")
            ->execute([$streamId, $_POST['ip'], $_POST['reason']]);
    }
    if ($_POST['action'] === 'unban_ip') {
        $pdo->prepare("DELETE FROM radio_dj_bans WHERE id=? AND stream_id=?")->execute([(int)$_POST['ban_id'], $streamId]);
    }
    header('Location: /user/dj-manager?stream=' . $streamId . '&tab=' . $tab);
    exit;
}

$djList = $pdo->prepare("SELECT * FROM radio_djs WHERE stream_id = ? ORDER BY name");
$djList->execute([$streamId]); $djList = $djList->fetchAll(PDO::FETCH_OBJ);
$appList = $pdo->prepare("SELECT * FROM radio_dj_applications WHERE stream_id = ? ORDER BY created_at DESC");
$appList->execute([$streamId]); $appList = $appList->fetchAll(PDO::FETCH_OBJ);
$banList = $pdo->prepare("SELECT * FROM radio_dj_bans WHERE stream_id = ? ORDER BY created_at DESC");
$banList->execute([$streamId]); $banList = $banList->fetchAll(PDO::FETCH_OBJ);
?>
<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
<h3 style="margin:0">DJ Manager</h3>
<?php if (count($streams) > 1): ?>
<select onchange="location.href='?stream='+this.value+'&tab=<?php echo $tab; ?>'" class="form-select" style="width:auto">
<?php foreach ($streams as $s): ?>
<option value="<?php echo $s->id; ?>" <?php echo $s->id == $streamId ? 'selected' : ''; ?>>Stream #<?php echo $s->id; ?> (Port <?php echo $s->port; ?>)</option>
<?php endforeach; ?></select>
<?php endif; ?>
</div>
</div>

<div class="d-flex gap-1 flex-wrap mb-3">
<a href="?stream=<?php echo $streamId; ?>&tab=djs" class="btn btn-sm <?php echo $tab==='djs' ? 'btn-primary' : 'btn-secondary'; ?>">DJs (<?php echo count($djList); ?>)</a>
<a href="?stream=<?php echo $streamId; ?>&tab=applications" class="btn btn-sm <?php echo $tab==='applications' ? 'btn-primary' : 'btn-secondary'; ?>">Applications (<?php echo count($appList); ?>)</a>
<a href="?stream=<?php echo $streamId; ?>&tab=bans" class="btn btn-sm <?php echo $tab==='bans' ? 'btn-primary' : 'btn-secondary'; ?>">Bans (<?php echo count($banList); ?>)</a>
</div>

<?php if ($tab === 'djs'): ?>
<div class="card">
<h4 style="margin-bottom:10px">Create DJ</h4>
<form method="POST" class="row g-2">
<div class="col-auto"><input name="action" type="hidden" value="create_dj"></div>
<div class="col-md-3"><input name="username" class="form-control" placeholder="Username" required></div>
<div class="col-md-3"><input name="password" type="password" class="form-control" placeholder="Password" required></div>
<div class="col-md-3"><input name="name" class="form-control" placeholder="Display Name"></div>
<div class="col-md-3"><input name="email" class="form-control" placeholder="Email"></div>
<div class="col-12"><button class="btn btn-primary">Create DJ</button></div>
</form>
</div>
<table class="table table-hover"><thead><tr><th>Username</th><th>Name</th><th>Status</th><th>Last Active</th><th></th></tr></thead>
<tbody><?php foreach ($djList as $d): ?>
<tr><td><strong><?php echo htmlspecialchars($d->username); ?></strong></td><td><?php echo htmlspecialchars($d->name ?? ''); ?></td>
<td><?php echo $d->status; ?></td><td><?php echo $d->last_active ?: 'Never'; ?></td>
<td><form method="POST"><input type="hidden" name="action" value="delete_dj"><input type="hidden" name="dj_id" value="<?php echo $d->id; ?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">&#128465;</button></form></td></tr>
<?php endforeach; ?></tbody></table>

<?php elseif ($tab === 'applications'): ?>
<div class="card">
<h4 style="margin-bottom:10px">DJ Applications</h4>
<?php if (empty($appList)): ?><p style="color:var(--text_muted)">No applications yet.</p><?php endif; ?>
<?php foreach ($appList as $a): ?>
<div style="padding:10px 0;border-bottom:1px solid var(--border,rgba(0,191,255,.04))">
<strong><?php echo htmlspecialchars($a->name); ?></strong> (<?php echo htmlspecialchars($a->email); ?>)
<p style="color:#94a3b8;font-size:12px;margin:4px 0"><?php echo htmlspecialchars(substr($a->bio ?? '', 0, 200)); ?></p>
<span style="font-size:11px;color:<?php echo $a->status === 'pending' ? '#facc15' : ($a->status === 'approved' ? '#4ade80' : '#f87171'); ?>"><?php echo $a->status; ?></span>
</div>
<?php endforeach; ?>
</div>

<?php elseif ($tab === 'bans'): ?>
<div class="card">
<h4 style="margin-bottom:10px">IP Bans</h4>
<form method="POST" class="row g-2">
<div class="col-auto"><input type="hidden" name="action" value="ban_ip"></div>
<div class="col-md-4"><input name="ip" class="form-control" placeholder="IP Address" required></div>
<div class="col-md-4"><input name="reason" class="form-control" placeholder="Reason"></div>
<div class="col-md-2"><button class="btn btn-primary">Ban IP</button></div>
</form>
<table class="table table-hover mt-2"><thead><tr><th>IP</th><th>Reason</th><th>Date</th><th></th></tr></thead>
<tbody><?php foreach ($banList as $b): ?>
<tr><td><?php echo htmlspecialchars($b->ip_address); ?></td><td><?php echo htmlspecialchars($b->reason ?? ''); ?></td><td><?php echo $b->created_at; ?></td>
<td><form method="POST"><input type="hidden" name="action" value="unban_ip"><input type="hidden" name="ban_id" value="<?php echo $b->id; ?>"><button class="btn btn-sm btn-secondary">Unban</button></form></td></tr>
<?php endforeach; ?></tbody></table>
</div>
<?php endif; ?>
