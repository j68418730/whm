<style>
.backup-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:16px}
.backup-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:14px;text-align:center}
.backup-stat .num{font-size:22px;font-weight:800}
.backup-stat .lbl{font-size:11px;color:#64748b}
</style>
<h2>💾 Backups</h2>
<p style="color:#64748b;margin-bottom:16px">Create and restore backups of your hosting account.</p>
<?php
$homeDir = '/home/' . ($hosting->username ?? '');
$backupFiles = [];
if (is_dir($homeDir)) {
    $backupFiles = glob("{$homeDir}/backup_*.tar.gz") ?: [];
    $backupFiles = array_merge($backupFiles, glob("{$homeDir}/backup_*.zip") ?: []);
    rsort($backupFiles);
}
$totalSize = 0;
foreach ($backupFiles as $bf) $totalSize += filesize($bf);
?>
<div class="backup-grid">
<div class="backup-stat"><div class="num" style="color:#0A84FF"><?php echo count($backupFiles); ?></div><div class="lbl">Backups</div></div>
<div class="backup-stat"><div class="num" style="color:#4ade80"><?php echo $totalSize > 1048576 ? round($totalSize/1048576,1).'MB' : ($totalSize > 1024 ? round($totalSize/1024,1).'KB' : $totalSize.'B'); ?></div><div class="lbl">Total Size</div></div>
<div class="backup-stat"><div class="num" style="color:#38bdf8"><?php echo $package->disk_space ?? 0; ?>GB</div><div class="lbl">Disk Limit</div></div>
</div>
<div style="display:flex;gap:8px;margin-bottom:16px">
<a href="/user/backup/create" class="btn btn-primary" style="padding:8px 16px;background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);border-radius:6px;text-decoration:none;font-size:13px">📦 Create Backup Now</a>
</div>
<div class="card">
<h3>Available Backups</h3>
<?php if (empty($backupFiles)): ?>
<p style="color:#64748b;font-size:13px;text-align:center;padding:20px">No backups found.</p>
<?php else: ?>
<table class="table"><thead><tr><th>Filename</th><th>Size</th><th>Date</th><th></th></tr></thead>
<tbody><?php foreach (array_slice($backupFiles, 0, 10) as $bf): $bn = basename($bf); $sz = filesize($bf); $dt = date('M j, Y g:i a', filemtime($bf)); ?>
<tr><td style="font-size:12px"><?php echo htmlspecialchars($bn); ?></td>
<td><?php echo $sz > 1048576 ? round($sz/1048576,1).' MB' : ($sz > 1024 ? round($sz/1024,1).' KB' : $sz.' B'); ?></td>
<td style="font-size:12px;color:#64748b"><?php echo $dt; ?></td>
<td style="display:flex;gap:4px">
<a href="/user/backup/restore?file=<?php echo urlencode($bn); ?>" class="btn btn-sm btn-success" onclick="return confirm('Restore this backup?')">Restore</a>
<a href="/user/backup/download?file=<?php echo urlencode($bn); ?>" class="btn btn-sm btn-primary">Download</a>
<a href="/user/backup/delete?file=<?php echo urlencode($bn); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this backup?')">Delete</a>
</td></tr>
<?php endforeach; ?></tbody></table>
<?php endif; ?>
</div>
