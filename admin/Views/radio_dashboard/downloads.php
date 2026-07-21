<style>
.card{background:rgba(8,16,28,.5);border:1px solid rgba(56,189,248,.06);border-radius:12px;padding:20px;margin-bottom:16px}
.card h3{font-size:15px;color:#e0e0e0;margin:0 0 12px}
table{width:100%;border-collapse:collapse;font-size:12px}
th{text-align:left;padding:10px 8px;color:#94a3b8;font-weight:600;border-bottom:1px solid rgba(255,255,255,.06)}
td{padding:10px 8px;border-bottom:1px solid rgba(255,255,255,.04)}
.btn{padding:6px 14px;border-radius:6px;font-size:11px;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-block}
.btn-p{background:rgba(56,189,248,.15);color:#38bdf8}
.btn-d{background:rgba(248,113,113,.12);color:#f87171}
.form-group{margin-bottom:10px}
.form-group label{display:block;font-size:11px;color:#64748b;margin-bottom:3px;font-weight:600}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;box-sizing:border-box}
</style>

<h2 style="margin-bottom:6px">Radio Downloads</h2>
<p style="color:#64748b;font-size:13px;margin-bottom:20px">Upload files that DJs can download from their DJ panel.</p>

<div class="card">
<h3>Upload File</h3>
<form method="POST" action="/admin/radio/downloads/upload" enctype="multipart/form-data">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div class="form-group"><label>File</label><input type="file" name="file" required></div>
<div class="form-group"><label>Name</label><input name="name" placeholder="Leave empty to use filename"></div>
<div class="form-group"><label>Station (optional)</label>
<select name="station_id">
<option value="">All Stations</option>
<?php
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','Skylinehosting171');
$sts = $pdo->query("SELECT id,name FROM streaming_stations ORDER BY name")->fetchAll(PDO::FETCH_OBJ);
foreach ($sts as $s): ?>
<option value="<?=$s->id?>"><?=htmlspecialchars($s->name)?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Description</label><textarea name="description" rows="2" placeholder="Optional description"></textarea></div>
</div>
<button type="submit" class="btn btn-p" style="margin-top:8px">Upload</button>
</form>
</div>

<div class="card">
<h3>All Downloads</h3>
<table>
<tr><th>File</th><th>Station</th><th>Size</th><th>Uploaded</th><th>Actions</th></tr>
<?php if (empty($downloads)): ?>
<tr><td colspan="5" style="text-align:center;color:#64748b;padding:20px">No downloads yet.</td></tr>
<?php else: ?>
<?php foreach ($downloads as $d): ?>
<tr>
<td><strong><?=htmlspecialchars($d->name)?></strong><?php if ($d->description): ?><div style="font-size:10px;color:#64748b"><?=htmlspecialchars($d->description)?></div><?php endif; ?></td>
<td><?=htmlspecialchars($d->station_name ?? 'All Stations')?></td>
<td><?=number_format($d->file_size / 1024, 1)?> KB</td>
<td style="font-size:11px;color:#64748b"><?=date('M j, Y', strtotime($d->created_at))?></td>
<td>
<a href="/admin/radio/downloads/delete/<?=$d->id?>" class="btn btn-d" onclick="return confirm('Delete <?=htmlspecialchars($d->name)?>?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>
</div>
