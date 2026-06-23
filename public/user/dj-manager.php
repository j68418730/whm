<?php
if (!isset($hosting) || !$hosting) { echo '<div class="card"><p style="color:#64748b;padding:20px;text-align:center">No account found.</p></div>'; exit; }
$streams = $pdo->prepare("SELECT * FROM radio_streams WHERE user_id = ?");
$streams->execute([$hosting->id]);
$streams = $streams->fetchAll(PDO::FETCH_OBJ);
$streamId = (int)($_GET['stream'] ?? ($streams[0]->id ?? 0));
$tab = $_GET['tab'] ?? 'djs';

if ($_POST) {
    if ($_POST['action'] === 'create_dj') {
        $pw = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO radio_djs (stream_id, username, password, name, email, status) VALUES (?,?,?,?,?,?)")->execute([$streamId, $_POST['username'], $pw, $_POST['name'], $_POST['email'], 'active']);
        echo '<meta http-equiv="refresh" content="0">'; exit;
    }
    if ($_POST['action'] === 'delete_dj') {
        $pdo->prepare("DELETE FROM radio_djs WHERE id=? AND stream_id=?")->execute([(int)$_POST['dj_id'], $streamId]);
        echo '<meta http-equiv="refresh" content="0">'; exit;
    }
}

$djList = $pdo->prepare("SELECT * FROM radio_djs WHERE stream_id = ? ORDER BY created_at DESC");
$djList->execute([$streamId]); $djList = $djList->fetchAll(PDO::FETCH_OBJ);
?>
<style>
.tab-btns{display:flex;gap:4px;margin-bottom:14px;flex-wrap:wrap}
.tab-btns a{padding:6px 14px;border-radius:6px;font-size:12px;text-decoration:none;transition:.1s}
input,select{width:100%;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;box-sizing:border-box}
input:focus{border-color:#0A84FF}
</style>
<h2>🎧 DJ Manager</h2>
<p style="color:#64748b;margin-bottom:14px">Manage DJ accounts for your radio streams.</p>
<?php if (count($streams) > 1): ?>
<div style="margin-bottom:12px"><select onchange="location.href='?stream='+this.value+'&tab=<?php echo $tab; ?>'" style="width:auto">
<?php foreach ($streams as $s): ?><option value="<?php echo $s->id; ?>" <?php echo $s->id==$streamId?'selected':'';?>>Stream #<?php echo $s->id; ?> (Port <?php echo $s->port; ?>)</option><?php endforeach; ?></select></div>
<?php endif; ?>
<div class="tab-btns">
<a href="?stream=<?php echo $streamId;?>&tab=djs" style="background:<?php echo $tab==='djs'?'rgba(0,140,255,.15);color:#0A84FF':'rgba(255,255,255,.04);color:#94a3b8';?>">🎤 DJs (<?php echo count($djList);?>)</a>
</div>
<?php if ($tab === 'djs'): ?>
<div class="card" style="max-width:500px"><h3 style="margin-bottom:10px">➕ Create DJ</h3>
<form method="POST" style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
<input type="hidden" name="action" value="create_dj">
<input name="username" placeholder="Username" required>
<input name="password" type="text" placeholder="Password" required>
<input name="name" placeholder="Display Name">
<input name="email" placeholder="Email">
<button type="submit" class="btn btn-sm btn-primary" style="grid-column:span 2;padding:8px">➕ Create DJ</button>
</form></div>
<div class="card"><h3>DJ Accounts <span style="font-size:12px;color:#64748b;font-weight:400">(<?php echo count($djList);?>)</span></h3>
<?php if (empty($djList)):?><p style="color:#64748b;font-size:12px;text-align:center;padding:15px">No DJs yet.</p>
<?php else:?>
<table class="table"><thead><tr><th>Username</th><th>Name</th><th>Status</th><th>Last Active</th><th></th></tr></thead>
<tbody><?php foreach($djList as $d):?><tr>
<td><code><?php echo htmlspecialchars($d->username);?></code></td>
<td><?php echo htmlspecialchars($d->name??'');?></td>
<td><span style="color:<?php echo $d->status==='active'?'#4ade80':'#64748b';?>">● <?php echo $d->status;?></span></td>
<td><?php echo $d->last_active ? date('M j g:i a',strtotime($d->last_active)) : 'Never';?></td>
<td><form method="POST"><input type="hidden" name="action" value="delete_dj"><input type="hidden" name="dj_id" value="<?php echo $d->id;?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">✕</button></form></td></tr>
<?php endforeach;?></tbody></table>
<?php endif;?></div>
<?php endif; ?>
