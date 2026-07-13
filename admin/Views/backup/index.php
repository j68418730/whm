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
<a href="/admin/backup/destinations" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo !empty($destinationsView) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>">📤 Destinations</a>
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

<?php elseif (!empty($destinationsView)): ?>
<h3 style="color:var(--accent);margin-bottom:12px">Backup Destinations</h3>
<div style="margin-bottom:16px">
<a href="#" onclick="document.getElementById('newDestForm').style.display='block';return false" class="btn primary">+ Add Destination</a>
</div>
<div id="newDestForm" style="display:none;margin-bottom:16px">
<div class="card">
<h4 style="color:var(--accent);margin-bottom:10px">New Destination</h4>
<form method="POST" action="/admin/backup/destination/store">
<div class="form-row-3">
<div class="form-group"><label>Name</label><input name="name" required class="inp inp-sm" placeholder="My Backup Server"></div>
<div class="form-group"><label>Type</label><select name="type" class="inp inp-sm"><option value="ftp">FTP</option><option value="ftps">FTPS</option><option value="sftp">SFTP</option></select></div>
<div class="form-group"><label>Host</label><input name="host" required class="inp inp-sm" placeholder="ftp.example.com"></div>
</div>
<div class="form-row-3">
<div class="form-group"><label>Port</label><input name="port" type="number" value="21" class="inp inp-sm"></div>
<div class="form-group"><label>Username</label><input name="username" class="inp inp-sm"></div>
<div class="form-group"><label>Password</label><input name="password" type="password" class="inp inp-sm"></div>
</div>
<div class="form-row-3">
<div class="form-group"><label>Remote Path</label><input name="path" value="/" class="inp inp-sm" placeholder="/backups/planet-hosts"></div>
<div class="form-group" style="display:flex;gap:10px;align-items:center;padding-top:18px">
<label><input type="checkbox" name="passive" value="1" checked> Passive Mode</label>
<label><input type="checkbox" name="ssl" value="1"> SSL/TLS</label>
</div>
<div class="form-group" style="display:flex;gap:10px;align-items:center;padding-top:18px">
<label><input type="checkbox" name="is_default" value="1"> Default</label>
<label><input type="checkbox" name="test_after_create" value="1" checked> Test</label>
</div>
</div>
<div class="form-group"><label>Notes</label><textarea name="notes" class="inp inp-sm" rows="2"></textarea></div>
<button type="submit" class="btn btn-sm primary">Create Destination</button>
<a href="#" onclick="document.getElementById('newDestForm').style.display='none';return false" class="btn btn-sm secondary">Cancel</a>
</form>
</div>
</div>
<?php if (empty($destinations)): ?>
<div class="card" style="text-align:center;padding:24px;color:#64748b">No destinations configured yet.</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(400px,1fr));gap:12px">
<?php foreach ($destinations as $d): ?>
<div class="card" style="margin-bottom:0">
<div style="display:flex;justify-content:space-between;align-items:start">
<div>
<strong style="font-size:14px;color:#e0e0e0"><?php echo htmlspecialchars($d->name); ?></strong>
<?php if ($d->is_default): ?><span class="status-badge status-running" style="margin-left:6px">Default</span><?php endif; ?>
<span class="status-badge <?php echo $d->is_active ? 'status-running' : 'status-stopped'; ?>" style="margin-left:4px"><?php echo $d->is_active ? 'Active' : 'Inactive'; ?></span>
</div>
<div style="display:flex;gap:4px">
<a href="/admin/backup/destination/test/<?php echo $d->id; ?>" class="btn btn-sm btn-primary" title="Test Connection">Test</a>
<a href="/admin/backup/destination/upload/<?php echo $d->id; ?>" class="btn btn-sm btn-success" title="Upload Latest Backup" onclick="return confirm('Upload latest backup to this destination?')">Upload</a>
<a href="/admin/backup/destination/delete/<?php echo $d->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this destination?')">Delete</a>
</div>
</div>
<div style="margin-top:8px;font-size:12px;color:#94a3b8">
<?php echo strtoupper($d->type); ?> · <?php echo htmlspecialchars($d->host); ?>:<?php echo $d->port; ?>
<?php if ($d->path && $d->path !== '/'): ?> · Path: <?php echo htmlspecialchars($d->path); ?><?php endif; ?>
</div>
<div style="margin-top:4px;font-size:11px;color:#64748b">
User: <?php echo htmlspecialchars($d->username); ?> · Created: <?php echo $d->created_at; ?>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php elseif (!empty($settingsView)): ?>
<h3 style="color:var(--accent);margin-bottom:12px">⚙️ Settings</h3>
<form method="POST" action="/admin/backup/settings/save">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Enable Backups</label><input type="checkbox" name="backup_enabled" value="1" <?php echo !empty($settings['backup_enabled']) ? 'checked' : ''; ?> style="margin-top:4px"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Enable Restore</label><input type="checkbox" name="backup_restore_enabled" value="1" <?php echo !empty($settings['backup_restore_enabled']) ? 'checked' : ''; ?> style="margin-top:4px"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Backup Type</label><select name="backup_type" class="form-control"><?php foreach (['full'=>'Full','incremental'=>'Incremental','differential'=>'Differential'] as $v=>$l): ?><option value="<?php echo $v; ?>" <?php echo ($settings['backup_type']??'')===$v?'selected':''; ?>><?php echo $l; ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Compression</label><select name="backup_compression" class="form-control"><?php foreach (['gzip'=>'GZip','bzip2'=>'BZip2','xz'=>'XZ','none'=>'None'] as $v=>$l): ?><option value="<?php echo $v; ?>" <?php echo ($settings['backup_compression']??'')===$v?'selected':''; ?>><?php echo $l; ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Encryption</label><select name="backup_encryption" class="form-control"><?php foreach (['none'=>'None','aes256'=>'AES-256','gpg'=>'GPG'] as $v=>$l): ?><option value="<?php echo $v; ?>" <?php echo ($settings['backup_encryption']??'')===$v?'selected':''; ?>><?php echo $l; ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Schedule</label><select name="backup_schedule" class="form-control"><?php foreach (['manual'=>'Manual','hourly'=>'Hourly','daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly'] as $v=>$l): ?><option value="<?php echo $v; ?>" <?php echo ($settings['backup_schedule']??'')===$v?'selected':''; ?>><?php echo $l; ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Retain Daily</label><input type="number" name="backup_retention_daily" value="<?php echo htmlspecialchars($settings['backup_retention_daily']??'7'); ?>" class="form-control" min="1" max="365"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Retain Weekly</label><input type="number" name="backup_retention_weekly" value="<?php echo htmlspecialchars($settings['backup_retention_weekly']??'4'); ?>" class="form-control" min="1" max="365"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Retain Monthly</label><input type="number" name="backup_retention_monthly" value="<?php echo htmlspecialchars($settings['backup_retention_monthly']??'3'); ?>" class="form-control" min="1" max="365"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Retain Yearly</label><input type="number" name="backup_retention_yearly" value="<?php echo htmlspecialchars($settings['backup_retention_yearly']??'1'); ?>" class="form-control" min="1" max="365"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Max Backups</label><input type="number" name="backup_max_backups" value="<?php echo htmlspecialchars($settings['backup_max_backups']??'10'); ?>" class="form-control" min="1" max="365"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Compress Level (1-9)</label><input type="number" name="backup_compress_level" value="<?php echo htmlspecialchars($settings['backup_compress_level']??'6'); ?>" class="form-control" min="1" max="9"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Encryption Password</label><input type="password" name="backup_encryption_password" value="<?php echo htmlspecialchars($settings['backup_encryption_password']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Notify Email</label><input type="email" name="backup_notify_email" value="<?php echo htmlspecialchars($settings['backup_notify_email']??''); ?>" class="form-control"></div>
</div>

<div class="card" style="margin-bottom:16px;padding:16px">
<h4 style="color:var(--accent);margin:0 0 10px;font-size:14px">📦 Storage Destination</h4>
<div class="form-group" style="margin-bottom:12px">
<label style="font-size:12px;color:var(--text-secondary)">Storage Type</label>
<select name="backup_storage_type" id="storageTypeSelect" class="form-control">
<?php $types = ['local'=>'Local Storage','nas'=>'NAS','nfs'=>'NFS','smb'=>'SMB/CIFS','ftp'=>'FTP','sftp'=>'SFTP','webdav'=>'WebDAV','s3'=>'Amazon S3','b2'=>'Backblaze B2','wasabi'=>'Wasabi','gcs'=>'Google Cloud Storage','azure'=>'Azure Blob Storage','do'=>'DigitalOcean Spaces']; ?>
<?php foreach ($types as $v=>$l): ?><option value="<?php echo $v; ?>" <?php echo ($settings['backup_storage_type']??'local')===$v?'selected':''; ?>><?php echo $l; ?></option><?php endforeach; ?>
</select>
</div>

<div id="Storage-local" class="storage-fields"><div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Local Path</label><input name="backup_storage_path" value="<?php echo htmlspecialchars($settings['backup_storage_path']??'/root/backupfiles'); ?>" class="form-control" placeholder="/root/backupfiles"></div></div>

<div id="Storage-nas" class="storage-fields" style="display:none">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">NAS Host</label><input name="backup_nas_host" value="<?php echo htmlspecialchars($settings['backup_nas_host']??''); ?>" class="form-control" placeholder="nas.example.com"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">NAS Path</label><input name="backup_nas_path" value="<?php echo htmlspecialchars($settings['backup_nas_path']??''); ?>" class="form-control" placeholder="/mnt/backups"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Username</label><input name="backup_nas_username" value="<?php echo htmlspecialchars($settings['backup_nas_username']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Password</label><input type="password" name="backup_nas_password" value="<?php echo htmlspecialchars($settings['backup_nas_password']??''); ?>" class="form-control"></div>
</div>

<div id="Storage-nfs" class="storage-fields" style="display:none">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">NFS Host</label><input name="backup_nfs_host" value="<?php echo htmlspecialchars($settings['backup_nfs_host']??''); ?>" class="form-control" placeholder="nfs.example.com"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">NFS Export</label><input name="backup_nfs_export" value="<?php echo htmlspecialchars($settings['backup_nfs_export']??''); ?>" class="form-control" placeholder="/export/backups"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Mount Options</label><input name="backup_nfs_options" value="<?php echo htmlspecialchars($settings['backup_nfs_options']??'rw,hard,intr'); ?>" class="form-control"></div>
</div>

<div id="Storage-smb" class="storage-fields" style="display:none">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">SMB Host</label><input name="backup_smb_host" value="<?php echo htmlspecialchars($settings['backup_smb_host']??''); ?>" class="form-control" placeholder="smb.example.com"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">SMB Share</label><input name="backup_smb_share" value="<?php echo htmlspecialchars($settings['backup_smb_share']??''); ?>" class="form-control" placeholder="//server/backups"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Domain</label><input name="backup_smb_domain" value="<?php echo htmlspecialchars($settings['backup_smb_domain']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Username</label><input name="backup_smb_username" value="<?php echo htmlspecialchars($settings['backup_smb_username']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Password</label><input type="password" name="backup_smb_password" value="<?php echo htmlspecialchars($settings['backup_smb_password']??''); ?>" class="form-control"></div>
</div>

<div id="Storage-ftp" class="storage-fields" style="display:none">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">FTP Host</label><input name="backup_ftp_host" value="<?php echo htmlspecialchars($settings['backup_ftp_host']??''); ?>" class="form-control" placeholder="ftp.example.com"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Port</label><input type="number" name="backup_ftp_port" value="<?php echo htmlspecialchars($settings['backup_ftp_port']??'21'); ?>" class="form-control" min="1" max="65535"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Username</label><input name="backup_ftp_username" value="<?php echo htmlspecialchars($settings['backup_ftp_username']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Password</label><input type="password" name="backup_ftp_password" value="<?php echo htmlspecialchars($settings['backup_ftp_password']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Remote Path</label><input name="backup_ftp_path" value="<?php echo htmlspecialchars($settings['backup_ftp_path']??'/'); ?>" class="form-control" placeholder="/"></div>
</div>

<div id="Storage-sftp" class="storage-fields" style="display:none">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">SFTP Host</label><input name="backup_sftp_host" value="<?php echo htmlspecialchars($settings['backup_sftp_host']??''); ?>" class="form-control" placeholder="sftp.example.com"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Port</label><input type="number" name="backup_sftp_port" value="<?php echo htmlspecialchars($settings['backup_sftp_port']??'22'); ?>" class="form-control" min="1" max="65535"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Username</label><input name="backup_sftp_username" value="<?php echo htmlspecialchars($settings['backup_sftp_username']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Password / Key</label><input type="password" name="backup_sftp_password" value="<?php echo htmlspecialchars($settings['backup_sftp_password']??''); ?>" class="form-control" placeholder="Password or path to SSH key"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Remote Path</label><input name="backup_sftp_path" value="<?php echo htmlspecialchars($settings['backup_sftp_path']??'/'); ?>" class="form-control" placeholder="/"></div>
</div>

<div id="Storage-webdav" class="storage-fields" style="display:none">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">WebDAV URL</label><input name="backup_webdav_url" value="<?php echo htmlspecialchars($settings['backup_webdav_url']??''); ?>" class="form-control" placeholder="https://webdav.example.com/backups"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Username</label><input name="backup_webdav_username" value="<?php echo htmlspecialchars($settings['backup_webdav_username']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Password</label><input type="password" name="backup_webdav_password" value="<?php echo htmlspecialchars($settings['backup_webdav_password']??''); ?>" class="form-control"></div>
</div>

<div id="Storage-s3" class="storage-fields" style="display:none">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">S3 Bucket</label><input name="backup_s3_bucket" value="<?php echo htmlspecialchars($settings['backup_s3_bucket']??''); ?>" class="form-control" placeholder="my-backups"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Region</label><input name="backup_s3_region" value="<?php echo htmlspecialchars($settings['backup_s3_region']??'us-east-1'); ?>" class="form-control" placeholder="us-east-1"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Access Key</label><input name="backup_s3_key" value="<?php echo htmlspecialchars($settings['backup_s3_key']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Secret Key</label><input type="password" name="backup_s3_secret" value="<?php echo htmlspecialchars($settings['backup_s3_secret']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Endpoint (optional)</label><input name="backup_s3_endpoint" value="<?php echo htmlspecialchars($settings['backup_s3_endpoint']??''); ?>" class="form-control" placeholder="https://s3.custom.com"></div>
</div>

<div id="Storage-b2" class="storage-fields" style="display:none">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">B2 Key ID</label><input name="backup_b2_key_id" value="<?php echo htmlspecialchars($settings['backup_b2_key_id']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">B2 App Key</label><input type="password" name="backup_b2_app_key" value="<?php echo htmlspecialchars($settings['backup_b2_app_key']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">B2 Bucket</label><input name="backup_b2_bucket" value="<?php echo htmlspecialchars($settings['backup_b2_bucket']??''); ?>" class="form-control" placeholder="my-bucket"></div>
</div>

<div id="Storage-wasabi" class="storage-fields" style="display:none">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Wasabi Bucket</label><input name="backup_wasabi_bucket" value="<?php echo htmlspecialchars($settings['backup_wasabi_bucket']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Wasabi Region</label><input name="backup_wasabi_region" value="<?php echo htmlspecialchars($settings['backup_wasabi_region']??'us-east-1'); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Access Key</label><input name="backup_wasabi_key" value="<?php echo htmlspecialchars($settings['backup_wasabi_key']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Secret Key</label><input type="password" name="backup_wasabi_secret" value="<?php echo htmlspecialchars($settings['backup_wasabi_secret']??''); ?>" class="form-control"></div>
</div>

<div id="Storage-gcs" class="storage-fields" style="display:none">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">GCS Bucket</label><input name="backup_gcs_bucket" value="<?php echo htmlspecialchars($settings['backup_gcs_bucket']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Project ID</label><input name="backup_gcs_project" value="<?php echo htmlspecialchars($settings['backup_gcs_project']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Service Account JSON Path</label><input name="backup_gcs_key_file" value="<?php echo htmlspecialchars($settings['backup_gcs_key_file']??'/root/gcs-key.json'); ?>" class="form-control"></div>
</div>

<div id="Storage-azure" class="storage-fields" style="display:none">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Azure Storage Account</label><input name="backup_azure_account" value="<?php echo htmlspecialchars($settings['backup_azure_account']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Azure Access Key</label><input type="password" name="backup_azure_key" value="<?php echo htmlspecialchars($settings['backup_azure_key']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Azure Container</label><input name="backup_azure_container" value="<?php echo htmlspecialchars($settings['backup_azure_container']??''); ?>" class="form-control"></div>
</div>

<div id="Storage-do" class="storage-fields" style="display:none">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">DO Space Name</label><input name="backup_do_space" value="<?php echo htmlspecialchars($settings['backup_do_space']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">DO Region</label><input name="backup_do_region" value="<?php echo htmlspecialchars($settings['backup_do_region']??'nyc3'); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Access Key</label><input name="backup_do_key" value="<?php echo htmlspecialchars($settings['backup_do_key']??''); ?>" class="form-control"></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Secret Key</label><input type="password" name="backup_do_secret" value="<?php echo htmlspecialchars($settings['backup_do_secret']??''); ?>" class="form-control"></div>
</div>
</div>

<button type="submit" class="btn primary">Save Settings</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('storageTypeSelect');
    function showFields() {
        document.querySelectorAll('.storage-fields').forEach(el => el.style.display = 'none');
        const target = document.getElementById('Storage-' + sel.value);
        if (target) target.style.display = 'block';
    }
    sel.addEventListener('change', showFields);
    showFields();
});
</script>

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