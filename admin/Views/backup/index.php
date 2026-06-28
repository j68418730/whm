<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<a href="/admin/backup" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo empty($historyView) && empty($settingsView) && empty($reportView) && empty($restorePointsView) && empty($editProfile) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>">📊 Dashboard</a>
<a href="/admin/backup/history" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo !empty($historyView) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>">📋 History</a>
<a href="/admin/backup/reports" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo !empty($reportView) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>">📈 Reports</a>
<a href="/admin/backup/restore-points" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo !empty($restorePointsView) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>">🔖 Restore Points</a>
<a href="/admin/backup/settings" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo !empty($settingsView) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>">⚙️ Settings</a>
</div>

<?php if (!empty($reportView)): ?>
<h3 style="color:var(--accent);margin-bottom:12px">📈 Backup Reports (Last 30 Days)</h3>
<?php if (empty($stats)): ?>
<div class="card" style="text-align:center;padding:24px;color:#64748b">No backup data yet.</div>
<?php else: ?>
<div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));margin-bottom:16px">
<div class="stat-card"><h3>Total</h3><div class="value"><?php echo $stats['total'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Successful</h3><div class="value" style="color:#4ade80"><?php echo $stats['success'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Failed</h3><div class="value" style="color:#f87171"><?php echo $stats['failed'] ?? 0; ?></div></div>
</div>
<?php endif; ?>
<h4 style="color:var(--accent);margin:14px 0 8px">Full History (Last 100)</h4>
<?php if (empty($history)): ?>
<div class="card" style="text-align:center;padding:16px;color:#64748b">No backup history yet.</div>
<?php else: ?>
<div style="overflow-x:auto">
<table style="width:100%;border-collapse:collapse;font-size:11px">
<thead><tr style="background:var(--bg-card);border-bottom:1px solid rgba(255,255,255,.06)">
<th style="padding:6px;text-align:left">ID</th><th style="padding:6px;text-align:left">Action</th><th style="padding:6px;text-align:left">Filename</th><th style="padding:6px;text-align:left">Status</th><th style="padding:6px;text-align:left">Date</th></tr></thead>
<tbody>
<?php foreach ($history as $h): ?>
<tr style="border-bottom:1px solid rgba(255,255,255,.04)">
<td style="padding:6px">#<?php echo $h['id'] ?? '-'; ?></td>
<td style="padding:6px"><?php echo htmlspecialchars($h['action'] ?? '-'); ?></td>
<td style="padding:6px;max-width:200px;overflow:hidden;text-overflow:ellipsis"><?php echo htmlspecialchars($h['filename'] ?? '-'); ?></td>
<td style="padding:6px"><span class="status-badge status-<?php echo ($h['status'] ?? '') === 'completed' ? 'active' : (($h['status'] ?? '') === 'failed' ? 'terminated' : ''); ?>"><?php echo $h['status'] ?? '-'; ?></span></td>
<td style="padding:6px"><?php echo $h['created_at'] ?? '-'; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>

<?php elseif (!empty($restorePointsView)): ?>
<h3 style="color:var(--accent);margin-bottom:12px">🔖 Restore Points</h3>
<?php if (empty($points)): ?>
<div class="card" style="text-align:center;padding:24px;color:#64748b">No restore points yet.</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:10px">
<?php foreach ($points as $pt): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="display:flex;justify-content:space-between;align-items:center">
<div>
<span style="font-weight:600;font-size:13px">#<?php echo $pt['id']; ?> <?php echo htmlspecialchars($pt['type'] ?? ''); ?></span>
<span class="status-badge status-<?php echo !empty($pt['favorite']) ? 'active' : ''; ?>" style="font-size:9px;margin-left:6px"><?php echo $pt['status'] ?? 'active'; ?></span>
</div>
<div style="display:flex;gap:4px">
<a href="/admin/backup/restore-points/favorite/<?php echo $pt['id']; ?>" class="btn btn-sm secondary"><?php echo empty($pt['favorite']) ? '☆' : '★'; ?></a>
<a href="/admin/backup/restore-points/delete/<?php echo $pt['id']; ?>" class="btn btn-sm danger" onclick="return confirm('Delete this restore point?')">🗑</a>
</div>
</div>
<div style="font-size:10px;color:#64748b;margin-top:4px">
User: <?php echo htmlspecialchars($pt['user_id'] ?? ''); ?> · <?php echo $pt['created_at'] ?? ''; ?>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php elseif (!empty($historyView)): ?>
<h3 style="color:var(--accent);margin-bottom:12px">📋 Backup History</h3>
<?php if (empty($history)): ?>
<div class="card" style="text-align:center;padding:24px;color:#64748b">No backup history yet.</div>
<?php else: ?>
<div style="overflow-x:auto">
<table style="width:100%;border-collapse:collapse;font-size:12px">
<thead><tr style="background:var(--bg-card);border-bottom:1px solid rgba(255,255,255,.06)">
<th style="padding:8px;text-align:left">ID</th><th style="padding:8px;text-align:left">Action</th><th style="padding:8px;text-align:left">Filename</th><th style="padding:8px;text-align:left">Status</th><th style="padding:8px;text-align:left">Date</th></tr></thead>
<tbody>
<?php foreach ($history as $h): ?>
<tr style="border-bottom:1px solid rgba(255,255,255,.04)">
<td style="padding:8px">#<?php echo $h['id'] ?? '-'; ?></td>
<td style="padding:8px"><?php echo htmlspecialchars($h['action'] ?? '-'); ?></td>
<td style="padding:8px"><?php echo htmlspecialchars($h['filename'] ?? '-'); ?></td>
<td style="padding:8px"><span class="status-badge status-<?php echo ($h['status'] ?? '') === 'completed' ? 'active' : (($h['status'] ?? '') === 'failed' ? 'terminated' : ''); ?>"><?php echo $h['status'] ?? '-'; ?></span></td>
<td style="padding:8px"><?php echo $h['created_at'] ?? '-'; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>

<?php elseif (!empty($settingsView)): ?>
<h3 style="color:var(--accent);margin-bottom:12px">⚙️ Settings</h3>
<form method="POST" action="/admin/backup/settings/save" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<?php $fields = [
    'backup_enabled' => ['Enable Backups', 'checkbox'],
    'backup_restore_enabled' => ['Enable Restore', 'checkbox'],
    'backup_type' => ['Backup Type', 'select', ['full' => 'Full', 'incremental' => 'Incremental', 'differential' => 'Differential']],
    'backup_compression' => ['Compression', 'select', ['gzip' => 'GZip', 'bzip2' => 'BZip2', 'xz' => 'XZ', 'none' => 'None']],
    'backup_encryption' => ['Encryption', 'select', ['none' => 'None', 'aes256' => 'AES-256', 'gpg' => 'GPG']],
    'backup_schedule' => ['Schedule', 'select', ['manual' => 'Manual', 'hourly' => 'Hourly', 'daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly']],
    'backup_retention_daily' => ['Retain Daily', 'number', 7],
    'backup_retention_weekly' => ['Retain Weekly', 'number', 4],
    'backup_retention_monthly' => ['Retain Monthly', 'number', 3],
    'backup_retention_yearly' => ['Retain Yearly', 'number', 1],
    'backup_max_backups' => ['Max Backups', 'number', 10],
    'backup_storage_type' => ['Storage Type', 'select', ['local' => 'Local', 'nas' => 'NAS', 'nfs' => 'NFS', 'smb' => 'SMB', 'ftp' => 'FTP', 's3' => 'Amazon S3', 'b2' => 'Backblaze B2', 'wasabi' => 'Wasabi', 'gcs' => 'GCS', 'azure' => 'Azure']],
    'backup_storage_path' => ['Storage Path', 'text'],
    'backup_encryption_password' => ['Encryption Password', 'password'],
    'backup_compress_level' => ['Compress Level (1-9)', 'number', 6],
    'backup_notify_email' => ['Notify Email', 'email'],
]; foreach ($fields as $k => $cfg): ?>
<div class="form-group">
<label style="font-size:12px;color:var(--text-secondary)"><?php echo $cfg[0]; ?></label>
<?php if ($cfg[1] === 'checkbox'): ?>
<input type="checkbox" name="<?php echo $k; ?>" value="1" <?php echo !empty($settings[$k]) ? 'checked' : ''; ?> style="margin-top:4px">
<?php elseif ($cfg[1] === 'select'): ?>
<select name="<?php echo $k; ?>" class="form-control"><?php foreach ($cfg[2] as $v => $l): ?><option value="<?php echo $v; ?>" <?php echo ($settings[$k] ?? '') === $v ? 'selected' : ''; ?>><?php echo $l; ?></option><?php endforeach; ?></select>
<?php else: ?>
<input type="<?php echo $cfg[1]; ?>" name="<?php echo $k; ?>" value="<?php echo htmlspecialchars($settings[$k] ?? ($cfg[2] ?? '')); ?>" class="form-control" <?php echo $cfg[1] === 'number' ? "min='1' max='365'" : ''; ?>>
<?php endif; ?>
</div>
<?php endforeach; ?>
<div style="grid-column:1/-1;margin-top:8px"><button type="submit" class="btn primary">Save Settings</button></div>
</form>

<?php else: ?>
<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Total Backups</h3><div class="value"><?php echo $backupStats['total_backups']; ?></div></div>
<div class="stat-card"><h3>Storage Used</h3><div class="value"><?php echo $backupStats['backup_storage_used']; ?> MB</div></div>
<div class="stat-card"><h3>Last Backup</h3><div class="value" style="font-size:16px"><?php echo $backupStats['last_backup']; ?></div></div>
</div>

<div class="card" style="margin-bottom:20px">
<h2 style="font-size:18px;margin-bottom:16px">Create Backup</h2>
<form method="POST" action="/admin/backup/create" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
<div><label style="display:block;color:var(--text-secondary);font-size:13px;margin-bottom:4px">Username (optional)</label><input name="username" placeholder="Leave empty for full backup" style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;min-width:250px;outline:none"></div>
<div><label style="display:block;color:var(--text-secondary);font-size:13px;margin-bottom:4px">From Profile</label><select name="profile_id" style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;min-width:200px;outline:none">
<option value="">— Select Profile —</option>
<?php foreach ($profiles as $p): ?>
<option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p["name"]); if($p["user_username"]) echo " (" . htmlspecialchars($p["user_username"]) . " @ " . htmlspecialchars($p["user_domain"] ?? "") . ")"; ?></option>
<?php endforeach; ?>
</select></div>
<button type="submit" class="btn primary">Create Backup</button>
</form>
</div>

<div class="card" style="background:linear-gradient(135deg,rgba(0,132,255,.08),rgba(0,191,255,.04));border-color:rgba(0,132,255,.25);margin-bottom:20px">
<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px">
<div style="flex:1;min-width:250px">
<div style="font-size:28px;margin-bottom:4px">🔄</div>
<h2 style="font-size:18px;margin:0 0 4px;color:#0A84FF">Restore Center</h2>
<p style="color:var(--text-secondary);font-size:13px;margin:0">Restore accounts, packages, websites, databases, email, streaming stations, game servers, and more from backups or migrate from other panels.</p>
</div>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<a href="/admin/restore-center" class="btn primary">Open Restore Center</a>
<a href="/admin/restore-center/history" class="btn secondary">View History</a>
<a href="/admin/migration" class="btn secondary" style="background:rgba(255,149,0,.1);color:#f59e0b;border-color:rgba(255,149,0,.2)">Migration Wizard</a>
</div>
</div>
</div>

<div class="card">
<h2 style="font-size:18px;margin-bottom:16px">Backups</h2>
<?php if (!empty($backups)): ?>
<table style="width:100%;border-collapse:collapse">
<thead>
<tr style="border-bottom:1px solid rgba(255,255,255,.06)">
<th style="text-align:left;padding:10px 12px">Filename</th>
<th style="text-align:left;padding:10px 12px">Date</th>
<th style="text-align:left;padding:10px 12px">Size</th>
<th style="text-align:left;padding:10px 12px">Status</th>
<th style="text-align:left;padding:10px 12px">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($backups as $b): ?>
<tr style="border-bottom:1px solid rgba(255,255,255,.04)">
<td style="padding:10px 12px"><?php echo htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8'); ?></td>
<td style="padding:10px 12px"><?php echo $b['date']; ?></td>
<td style="padding:10px 12px"><?php echo round($b['size'] / 1024 / 1024, 2); ?> MB</td>
<td style="padding:10px 12px"><span style="color:#4ade80">Completed</span></td>
<td style="padding:10px 12px">
<a href="/admin/backup/restore/<?php echo urlencode($b['name']); ?>" class="btn secondary" style="padding:6px 14px;font-size:12px" onclick="return confirm('Restore this backup?')">Restore</a>
<a href="/admin/backup/delete/<?php echo urlencode($b['name']); ?>" class="btn danger" style="padding:6px 14px;font-size:12px" onclick="return confirm('Delete this backup?')">Delete</a>
<a href="/admin/backup/preview/<?php echo urlencode($b['name']); ?>" class="btn secondary" style="padding:6px 14px;font-size:12px">Preview</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<div style="text-align:center;color:var(--text-secondary);padding:20px">No backups yet.</div>
<?php endif; ?>
</div>
<?php endif; ?>