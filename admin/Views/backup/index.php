<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<a href="/admin/backup" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo empty($historyView) && empty($settingsView) && empty($reportView) && empty($restorePointsView) && empty($jobsView) && empty($editProfile) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>">📊 Dashboard</a>
<a href="/admin/backup/history" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo !empty($historyView) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>">📋 History</a>
<a href="/admin/backup/reports" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo !empty($reportView) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>">📈 Reports</a>
<a href="/admin/backup/restore-points" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo !empty($restorePointsView) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>">🔖 Restore Points</a>
<a href="/admin/backup/jobs" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo !empty($jobsView) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>">⏰ Jobs</a>
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

<div class="card" style="margin-bottom:16px">
<h4 style="color:var(--accent);margin-bottom:12px">Create Restore Point</h4>
<form method="POST" action="/admin/backup/restore-points/create">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Scope</label><select name="scope" id="rpScope" class="form-control" onchange="rpScopeChange(this.value)"><option value="core">Core — Full system (all accounts, DB, configs)</option><option value="system">System — OS configs, services, firewall</option><option value="user">User — Single hosting account</option><option value="domain">Domain — Single domain/zone</option></select></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Name</label><input name="name" required class="form-control" placeholder="Pre-update 2026-09-10"></div>
</div>
<div id="rpUserPicker" style="display:none;margin-top:10px"><div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Select User</label><select name="user_id" class="form-control"><option value="0">— Select user —</option><?php foreach (($allUsers ?? []) as $u): ?><option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->username . ' - ' . ($u->domain ?: 'no domain') . ' (' . $u->email . ')'); ?></option><?php endforeach; ?></select></div></div>
<div id="rpDomainPicker" style="display:none;margin-top:10px"><div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Select Domain</label><select name="domain" class="form-control"><option value="">— Select domain —</option><?php foreach (($allDomains ?? []) as $d): ?><option value="<?php echo htmlspecialchars($d->domain); ?>"><?php echo htmlspecialchars($d->domain); ?></option><?php endforeach; ?></select></div></div>
<div class="form-group" style="margin-top:10px"><label style="font-size:12px;color:var(--text-secondary)">Notes</label><textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea></div>
<button type="submit" class="btn primary">Create Restore Point</button>
</form>
<script>
function rpScopeChange(v){
  document.getElementById('rpUserPicker').style.display = v==='user' ? 'block' : 'none';
  document.getElementById('rpDomainPicker').style.display = v==='domain' ? 'block' : 'none';
}
</script>
</div>

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
Scope: <?php echo htmlspecialchars($pt['type'] ?? ''); ?> · User: <?php echo htmlspecialchars($pt['user_id'] ?? ''); ?> · <?php echo $pt['created_at'] ?? ''; ?>
</div>
<?php if (!empty($pt['notes'])): ?><div style="font-size:11px;color:#94a3b8;margin-top:4px"><?php echo htmlspecialchars($pt['notes']); ?></div><?php endif; ?>
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
<?php
$__dfmt = function($b) use (&$__dfmt) {
    $units = ['B','KB','MB','GB','TB'];
    $b = max(0, (float)$b);
    $pow = min((int)floor($b ? log($b)/log(1024) : 0), count($units)-1);
    return round($b / pow(1024, $pow), 2) . ' ' . $units[$pow];
};
if (!empty($destDetail)): ?>
<h3 style="color:var(--accent);margin-bottom:12px">📤 Destination: <?php echo htmlspecialchars($destDetail->name); ?></h3>
<div style="margin-bottom:14px"><a href="/admin/backup/destinations" class="btn btn-sm secondary" style="padding:6px 12px">← Back to Destinations</a></div>
<div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));margin-bottom:16px">
<div class="stat-card"><h3>Type</h3><div class="value" style="font-size:14px"><?php echo htmlspecialchars(strtoupper($destDetail->type)); ?></div></div>
<div class="stat-card"><h3>Host</h3><div class="value" style="font-size:14px"><?php echo htmlspecialchars($destDetail->host ?: ($destDetail->bucket ?: '-')); ?></div></div>
<div class="stat-card"><h3>Uploads</h3><div class="value"><?php echo $destUsage['count'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Stored</h3><div class="value" style="font-size:16px"><?php echo $__dfmt($destUsage['bytes'] ?? 0); ?></div></div>
<div class="stat-card"><h3>Quota</h3><div class="value" style="font-size:16px"><?php echo ($destUsage['quota'] ?? 0) > 0 ? $__dfmt($destUsage['quota']) : 'Unlimited'; ?></div></div>
</div>
<div class="card" style="margin-bottom:16px">
<h4 style="color:var(--accent);margin:0 0 10px;font-size:14px">Transfer History</h4>
<?php if (empty($destHistory)): ?><div style="color:#64748b;padding:10px;font-size:12px">No transfers yet.</div>
<?php else: ?>
<div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse;font-size:11px">
<thead><tr style="background:var(--bg-card);border-bottom:1px solid rgba(255,255,255,.06)">
<th style="padding:6px;text-align:left">ID</th><th style="padding:6px;text-align:left">Action</th><th style="padding:6px;text-align:left">File</th><th style="padding:6px;text-align:left">Status</th><th style="padding:6px;text-align:left">Size</th><th style="padding:6px;text-align:left">Duration</th><th style="padding:6px;text-align:left">Message</th><th style="padding:6px;text-align:left">Date</th></tr></thead>
<tbody>
<?php foreach ($destHistory as $h): ?>
<tr style="border-bottom:1px solid rgba(255,255,255,.04)">
<td style="padding:6px">#<?php echo $h['id'] ?? '-'; ?></td>
<td style="padding:6px"><?php echo htmlspecialchars($h['action'] ?? '-'); ?></td>
<td style="padding:6px;max-width:180px;overflow:hidden;text-overflow:ellipsis"><?php echo htmlspecialchars($h['file_path'] ?? '-'); ?></td>
<td style="padding:6px"><span class="status-badge status-<?php echo ($h['status']??'')==='completed' ? 'active' : 'terminated'; ?>"><?php echo $h['status'] ?? '-'; ?></span></td>
<td style="padding:6px"><?php echo $__dfmt($h['file_size'] ?? 0); ?></td>
<td style="padding:6px"><?php echo isset($h['duration_ms']) && $h['duration_ms'] ? round($h['duration_ms']/1000, 1) . 's' : '-'; ?></td>
<td style="padding:6px;max-width:220px;overflow:hidden;text-overflow:ellipsis"><?php echo htmlspecialchars($h['message'] ?? ''); ?></td>
<td style="padding:6px"><?php echo $h['created_at'] ?? '-'; ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</div>
<div class="card">
<h4 style="color:var(--accent);margin:0 0 10px;font-size:14px">Errors</h4>
<?php if (empty($destErrors)): ?><div style="color:#64748b;padding:10px;font-size:12px">No errors recorded.</div>
<?php else: ?>
<div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse;font-size:11px">
<thead><tr style="background:var(--bg-card);border-bottom:1px solid rgba(255,255,255,.06)">
<th style="padding:6px;text-align:left">ID</th><th style="padding:6px;text-align:left">File</th><th style="padding:6px;text-align:left">Message</th><th style="padding:6px;text-align:left">Date</th></tr></thead>
<tbody>
<?php foreach ($destErrors as $h): ?>
<tr style="border-bottom:1px solid rgba(255,255,255,.04)">
<td style="padding:6px">#<?php echo $h['id'] ?? '-'; ?></td>
<td style="padding:6px;max-width:180px;overflow:hidden;text-overflow:ellipsis"><?php echo htmlspecialchars($h['file_path'] ?? '-'); ?></td>
<td style="padding:6px;max-width:360px;overflow:hidden;text-overflow:ellipsis;color:#f87171"><?php echo htmlspecialchars($h['message'] ?? '-'); ?></td>
<td style="padding:6px"><?php echo $h['created_at'] ?? '-'; ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</div>

<?php else: ?>
<h3 style="color:var(--accent);margin-bottom:12px">📤 Backup Destinations</h3>

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
<a href="#" onclick="document.getElementById('newDestForm').style.display='block';return false" class="btn primary">+ Add Destination</a>
<a href="#" onclick="document.getElementById('restorePanel').style.display='block';return false" class="btn secondary">⬇ Restore From Remote</a>
</div>

<?php if (!empty($editDest)): ?>
<div class="card" style="margin-bottom:16px;border-color:rgba(0,191,255,.35)" id="editDestCard">
<h4 style="color:var(--accent);margin:0 0 12px;font-size:14px">✏️ Edit Destination: <?php echo htmlspecialchars($editDest->name); ?></h4>
<form method="POST" action="/admin/backup/destination/update/<?php echo $editDest->id; ?>" onsubmit="return true">
<div class="form-row-3">
<div class="form-group"><label>Name</label><input name="name" required class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->name); ?>"></div>
<div class="form-group"><label>Type</label><select name="type" class="inp inp-sm" onchange="destTypeFields('<?php echo 'edit'; ?>',this.value)">
<?php foreach (($destTypes ?? []) as $tv=>$tl): ?><option value="<?php echo $tv; ?>" <?php echo $editDest->type===$tv?'selected':''; ?>><?php echo $tl; ?></option><?php endforeach; ?>
</select></div>
<div class="form-group"><label>Host / URL</label><input name="host" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->host ?? ''); ?>" placeholder="ftp.example.com or https://dav.example.com"></div>
</div>
<div class="form-row-3">
<div class="form-group fld" data-dtype="ftp ftps sftp rsync webdav"><label>Port</label><input name="port" type="number" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->port ?? ''); ?>"></div>
<div class="form-group fld" data-dtype="googledrive"><label>Rclone Remote Name</label><input name="username" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->username ?? ''); ?>" placeholder="gdrive:"></div>
<div class="form-group fld" data-dtype="ftp ftps sftp rsync webdav"><label>Username</label><input name="username" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->username ?? ''); ?>"></div>
<div class="form-group fld" data-dtype="ftp ftps sftp rsync webdav"><label>Password</label><input name="password" type="password" class="inp inp-sm" placeholder="Leave blank to keep current"></div>
</div>
<div class="form-row-3">
<div class="form-group fld" data-dtype="sftp rsync"><label>SSH Key Path</label><input name="private_key" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->private_key ?? ''); ?>" placeholder="/root/.ssh/id_rsa"></div>
<div class="form-group fld" data-dtype="ftp ftps sftp rsync local googledrive webdav"><label>Remote / Local Path</label><input name="path" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->path ?? '/'); ?>"></div>
<div class="form-group fld" data-dtype="ftp ftps"><label>Options</label>
<div style="display:flex;gap:10px;padding-top:6px">
<label style="font-size:11px;color:var(--text-secondary)"><input type="checkbox" name="passive" value="1" <?php echo !empty($editDest->passive)?'checked':''; ?>> Passive</label>
<label style="font-size:11px;color:var(--text-secondary)"><input type="checkbox" name="ssl" value="1" <?php echo !empty($editDest->ssl)?'checked':''; ?>> SSL/TLS</label>
</div></div>
</div>
<div class="form-row-3">
<div class="form-group fld" data-dtype="s3 s3-compat b2"><label>Bucket</label><input name="bucket" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->bucket ?? ''); ?>"></div>
<div class="form-group fld" data-dtype="s3 s3-compat"><label>Region</label><input name="region" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->region ?? 'us-east-1'); ?>"></div>
<div class="form-group fld" data-dtype="s3 s3-compat b2"><label>Access Key / Key ID</label><input name="access_key" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->access_key ?? ''); ?>"></div>
</div>
<div class="form-row-3">
<div class="form-group fld" data-dtype="s3 s3-compat b2"><label>Secret Key</label><input name="secret_key" type="password" class="inp inp-sm" placeholder="Leave blank to keep current"></div>
<div class="form-group fld" data-dtype="s3 s3-compat b2"><label>Endpoint</label><input name="endpoint" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->endpoint ?? ''); ?>" placeholder="https://s3.custom.com (optional for AWS)"></div>
<div class="form-group fld" data-dtype="custom"><label>Command Template</label><input name="command_template" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->command_template ?? ''); ?>" placeholder="rclone copy {local} {host}:{path}/ 2>/dev/null"></div>
</div>
<div class="form-group fld" data-dtype="custom"><label>Custom Hints</label><div style="font-size:10px;color:#64748b">Placeholders: {local} {remote}/{file} {path} {user} {host} {port} {password} {op}</div></div>
<div class="form-row-3">
<div class="form-group"><label>Retain Daily</label><input name="retention_daily" type="number" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->retention_daily ?? 7); ?>"></div>
<div class="form-group"><label>Retain Weekly</label><input name="retention_weekly" type="number" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->retention_weekly ?? 4); ?>"></div>
<div class="form-group"><label>Retain Monthly</label><input name="retention_monthly" type="number" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->retention_monthly ?? 3); ?>"></div>
</div>
<div class="form-row-3">
<div class="form-group"><label>Retain Yearly</label><input name="retention_yearly" type="number" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->retention_yearly ?? 1); ?>"></div>
<div class="form-group"><label>Max Retries</label><input name="max_retries" type="number" class="inp inp-sm" value="<?php echo htmlspecialchars($editDest->max_retries ?? 3); ?>"></div>
<div class="form-group"><label>Default</label><div style="padding-top:8px"><label style="font-size:11px;color:var(--text-secondary)"><input type="checkbox" name="is_default" value="1" <?php echo !empty($editDest->is_default)?'checked':''; ?>> Set as default</label></div></div>
</div>
<div class="form-group"><label>Notes</label><textarea name="notes" class="inp inp-sm" rows="2"><?php echo htmlspecialchars($editDest->notes ?? ''); ?></textarea></div>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<button type="submit" class="btn btn-sm primary">Save Changes</button>
<a href="/admin/backup/destinations" class="btn btn-sm secondary">Cancel</a>
</div>
</form>
<script>document.addEventListener('DOMContentLoaded', function(){ destTypeFields('edit', <?php echo json_encode($editDest->type); ?>); });</script>
</div>
<?php endif; ?>

<div id="newDestForm" style="display:none;margin-bottom:16px">
<div class="card">
<h4 style="color:var(--accent);margin:0 0 12px;font-size:14px">New Destination</h4>
<form method="POST" action="/admin/backup/destination/store">
<div class="form-row-3">
<div class="form-group"><label>Name</label><input name="name" required class="inp inp-sm" placeholder="My Backup Server"></div>
<div class="form-group"><label>Type</label><select name="type" id="dtypeSel" class="inp inp-sm" onchange="destTypeFields('create',this.value)"><?php foreach (($destTypes ?? []) as $tv=>$tl): ?><option value="<?php echo $tv; ?>"><?php echo $tl; ?></option><?php endforeach; ?></select></div>
<div class="form-group fld" data-dtype="ftp ftps sftp rsync webdav"><label>Host / URL</label><input name="host" class="inp inp-sm" placeholder="ftp.example.com or https://dav.example.com"></div>
</div>
<div class="form-row-3">
<div class="form-group fld" data-dtype="ftp ftps sftp rsync webdav"><label>Port</label><input name="port" type="number" class="inp inp-sm" value="21"></div>
<div class="form-group fld" data-dtype="ftp ftps sftp rsync webdav"><label>Username</label><input name="username" class="inp inp-sm"></div>
<div class="form-group fld" data-dtype="googledrive"><label>Rclone Remote Name</label><input name="username" class="inp inp-sm" placeholder="gdrive:"></div>
<div class="form-group fld" data-dtype="ftp ftps sftp rsync webdav"><label>Password</label><input name="password" type="password" class="inp inp-sm"></div>
</div>
<div class="form-row-3">
<div class="form-group fld" data-dtype="sftp rsync"><label>SSH Key Path</label><input name="private_key" class="inp inp-sm" placeholder="/root/.ssh/id_rsa (optional)"></div>
<div class="form-group fld" data-dtype="ftp ftps sftp rsync googledrive webdav"><label>Remote Path</label><input name="path" class="inp inp-sm" value="/" placeholder="/backups"></div>
<div class="form-group fld" data-dtype="ftp ftps"><label>Options</label>
<div style="display:flex;gap:10px;padding-top:6px">
<label style="font-size:11px;color:var(--text-secondary)"><input type="checkbox" name="passive" value="1" checked> Passive</label>
<label style="font-size:11px;color:var(--text-secondary)"><input type="checkbox" name="ssl" value="1"> SSL/TLS</label>
</div></div>
</div>
<div class="form-row-3">
<div class="form-group fld" data-dtype="s3 s3-compat b2"><label>Bucket</label><input name="bucket" class="inp inp-sm" placeholder="my-backups"></div>
<div class="form-group fld" data-dtype="s3 s3-compat"><label>Region</label><input name="region" class="inp inp-sm" value="us-east-1" placeholder="us-east-1"></div>
<div class="form-group fld" data-dtype="s3 s3-compat b2"><label>Access Key / Key ID</label><input name="access_key" class="inp inp-sm"></div>
</div>
<div class="form-row-3">
<div class="form-group fld" data-dtype="s3 s3-compat b2"><label>Secret Key</label><input name="secret_key" type="password" class="inp inp-sm"></div>
<div class="form-group fld" data-dtype="s3 s3-compat b2"><label>Endpoint</label><input name="endpoint" class="inp inp-sm" placeholder="https://s3.custom.com (required for S3-compat / B2)"></div>
<div class="form-group fld" data-dtype="custom"><label>Command Template</label><input name="command_template" class="inp inp-sm" placeholder="rclone copy {local} {host}:{path}/ 2>/dev/null"></div>
</div>
<div class="form-row-3">
<div class="form-group fld" data-dtype="custom" style="grid-column:1/-1"><label>Custom Hints</label><div style="font-size:10px;color:#64748b">Placeholders: {local} {remote}/{file} {path} {user} {host} {port} {password} {op}</div></div>
<div class="form-group fld" data-dtype="local"><label>Local Path</label><input name="path" class="inp inp-sm" value="/var/backups/offsite" placeholder="/var/backups/offsite"></div>
</div>
<div class="form-row-3">
<div class="form-group"><label>Retain Daily</label><input name="retention_daily" type="number" value="7" class="inp inp-sm" min="1" max="365"></div>
<div class="form-group"><label>Retain Weekly</label><input name="retention_weekly" type="number" value="4" class="inp inp-sm" min="1" max="365"></div>
<div class="form-group"><label>Retain Monthly</label><input name="retention_monthly" type="number" value="3" class="inp inp-sm" min="1" max="365"></div>
</div>
<div class="form-row-3">
<div class="form-group"><label>Retain Yearly</label><input name="retention_yearly" type="number" value="1" class="inp inp-sm" min="1" max="365"></div>
<div class="form-group"><label>Max Retries</label><input name="max_retries" type="number" value="3" class="inp inp-sm" min="1" max="10"></div>
<div class="form-group" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">
<div style="padding-top:6px"><label style="font-size:11px;color:var(--text-secondary)"><input type="checkbox" name="is_default" value="1"> Default</label></div>
<div style="padding-top:6px"><label style="font-size:11px;color:var(--text-secondary)"><input type="checkbox" name="test_after_create" value="1" checked> Test after create</label></div>
</div>
</div>
<div class="form-group"><label>Notes</label><textarea name="notes" class="inp inp-sm" rows="2"></textarea></div>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<button type="submit" class="btn btn-sm primary">Create Destination</button>
<a href="#" onclick="document.getElementById('newDestForm').style.display='none';return false" class="btn btn-sm secondary">Cancel</a>
</div>
</form>
</div>
</div>

<div id="restorePanel" style="display:none;margin-bottom:16px">
<div class="card">
<h4 style="color:var(--accent);margin:0 0 12px;font-size:14px">⬇ Restore From Remote</h4>
<div class="form-row-3">
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Destination</label>
<select id="restoreDestSel" class="inp inp-sm" onchange="loadRemoteFiles(this.value)"><option value="">— Select destination —</option><?php foreach ($destinations as $d): ?><option value="<?php echo $d->id; ?>"><?php echo htmlspecialchars($d->name); ?> (<?php echo strtoupper($d->type); ?>)</option><?php endforeach; ?></select></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Remote File</label>
<select id="restoreFileSel" name="remote_file" form="restoreForm" class="inp inp-sm" disabled><option>Select a destination first</option></select></div>
<div class="form-group" style="align-self:end"><form id="restoreForm" method="POST" action="/admin/backup/destination/restore"><input type="hidden" name="destination_id" id="restoreDestId"><button type="submit" class="btn btn-sm primary" onclick="return confirm('Download, verify and stage this backup for restore?')">Download & Stage</button></form></div>
</div>
<div style="font-size:10px;color:#64748b;margin-top:4px">Restore flow: Remote Download → Checksum/Archive Verify → Staging → appears under Backups for Restore.</div>
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
<div style="display:flex;gap:4px;flex-wrap:wrap;justify-content:flex-end">
<a href="/admin/backup/destination/history/<?php echo $d->id; ?>" class="btn btn-sm btn-primary" title="History & Errors">📜</a>
<a href="/admin/backup/destination/edit/<?php echo $d->id; ?>" class="btn btn-sm btn-secondary" title="Edit">✏️</a>
<a href="/admin/backup/destination/toggle/<?php echo $d->id; ?>" class="btn btn-sm <?php echo $d->is_active ? 'btn-warning' : 'btn-success'; ?>" title="<?php echo $d->is_active ? 'Disable' : 'Enable'; ?>"><?php echo $d->is_active ? '⏸' : '▶'; ?></a>
<a href="/admin/backup/destination/test/<?php echo $d->id; ?>" class="btn btn-sm btn-primary" title="Test Connection">Test</a>
<a href="/admin/backup/destination/run/<?php echo $d->id; ?>" class="btn btn-sm btn-success" title="Upload latest backup now">Run</a>
<a href="/admin/backup/destination/retention/<?php echo $d->id; ?>" class="btn btn-sm btn-secondary" title="Enforce retention">🧹</a>
<a href="/admin/backup/destination/delete/<?php echo $d->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this destination?')" title="Delete">🗑</a>
</div>
</div>
<div style="margin-top:8px;font-size:12px;color:#94a3b8">
<?php echo htmlspecialchars(strtoupper($d->type)); ?> · <?php echo htmlspecialchars($d->host ?: ($d->bucket ?: ($d->type === 'local' ? 'local' : '-')) ); ?><?php if (!empty($d->port) && (int)$d->port > 0): ?>:<?php echo (int)$d->port; ?><?php endif; ?>
<?php if ($d->path && $d->path !== '/'): ?> · Path: <?php echo htmlspecialchars($d->path); ?><?php endif; ?>
</div>
<div style="margin-top:4px;font-size:11px;color:#64748b">
Retention: <?php echo (int)($d->retention_daily ?? 7); ?>/<?php echo (int)($d->retention_weekly ?? 4); ?>/<?php echo (int)($d->retention_monthly ?? 3); ?> (D/W/M) · User: <?php echo htmlspecialchars($d->username ?: '-'); ?>
</div>
<?php if (!empty($d->last_test_message)): ?>
<div style="margin-top:4px;font-size:10px;color:#64748b">Last test: <?php echo htmlspecialchars($d->last_test_message); ?> <?php echo $d->last_tested_at ? '(' . $d->last_tested_at . ')' : ''; ?></div>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card" style="margin-top:16px">
<h4 style="color:var(--accent);margin:0 0 6px;font-size:14px">📤 Transfer Queue</h4>
<div style="font-size:11px;color:#64748b;margin-bottom:10px">Transfers are processed automatically. Use <strong>Run</strong> on a destination card to push the latest backup now, or the Restore panel to pull remote backups back.</div>
<?php if (empty($queue)): ?>
<div style="color:#64748b;padding:10px;font-size:12px">Queue is empty.</div>
<?php else: ?>
<div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse;font-size:11px">
<thead><tr style="background:var(--bg-card);border-bottom:1px solid rgba(255,255,255,.06)">
<th style="padding:6px;text-align:left">ID</th><th style="padding:6px;text-align:left">Dest</th><th style="padding:6px;text-align:left">Action</th><th style="padding:6px;text-align:left">File</th><th style="padding:6px;text-align:left">Status</th><th style="padding:6px;text-align:left">Stage</th><th style="padding:6px;text-align:left">Att</th><th style="padding:6px;text-align:left">Error</th><th style="padding:6px;text-align:left">Date</th></tr></thead>
<tbody>
<?php foreach ($queue as $t): ?>
<tr style="border-bottom:1px solid rgba(255,255,255,.04)">
<td style="padding:6px">#<?php echo $t['id'] ?? '-'; ?></td>
<td style="padding:6px;max-width:120px;overflow:hidden;text-overflow:ellipsis"><?php echo htmlspecialchars($t['destination_id'] ?? '-'); ?></td>
<td style="padding:6px"><?php echo isset($t['action']) ? strtoupper($t['action']) : '-'; ?></td>
<td style="padding:6px;max-width:180px;overflow:hidden;text-overflow:ellipsis"><?php echo htmlspecialchars($t['filename'] ?? '-'); ?></td>
<td style="padding:6px"><span class="status-badge status-<?php echo ($t['status']??'')==='completed' ? 'active' : (($t['status']??'')==='failed' ? 'terminated' : 'pending'); ?>"><?php echo $t['status'] ?? '-'; ?></span></td>
<td style="padding:6px"><?php echo htmlspecialchars($t['stage'] ?? '-'); ?></td>
<td style="padding:6px"><?php echo $t['attempts'] ?? 0; ?>/<?php echo $t['max_attempts'] ?? 3; ?></td>
<td style="padding:6px;max-width:200px;overflow:hidden;text-overflow:ellipsis;color:#f87171"><?php echo htmlspecialchars($t['error_message'] ?? ''); ?></td>
<td style="padding:6px"><?php echo $t['created_at'] ?? '-'; ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</div>

<script>
function destTypeFields(scope, type){
  var root = scope === 'edit' ? document.getElementById('editDestCard') : document.getElementById('newDestForm');
  if (!root) return;
  var flds = root.querySelectorAll('.fld');
  Array.prototype.forEach.call(flds, function(el){
    el.style.display = el.getAttribute('data-dtype').split(' ').indexOf(type) >= 0 ? 'block' : 'none';
  });
}
function loadRemoteFiles(id){
  var sel = document.getElementById('restoreFileSel');
  document.getElementById('restoreDestId').value = id;
  if (!id){ sel.disabled = true; sel.innerHTML = '<option>Select a destination first</option>'; return; }
  sel.disabled = true;
  sel.innerHTML = '<option>Loading…</option>';
  fetch('/admin/backup/destination/list-remote/' + id).then(function(r){ return r.json(); }).then(function(d){
    sel.innerHTML = '';
    if (!d.files || !d.files.length){ sel.innerHTML = '<option value="">No backups found on remote</option>'; }
    else {
      d.files.forEach(function(f){ var o = document.createElement('option'); o.value = f; o.textContent = f; sel.appendChild(o); });
    }
    sel.disabled = false;
  }).catch(function(){ sel.innerHTML = '<option value="">Failed to list remote files</option>'; sel.disabled = false; });
}
document.addEventListener('DOMContentLoaded', function(){
  var s = document.getElementById('dtypeSel');
  if (s) destTypeFields('create', s.value);
  if (document.getElementById('editDestCard')) destTypeFields('edit', <?php echo isset($editDest) ? json_encode($editDest->type) : '""'; ?>);
});
</script>
<?php endif; ?>

<?php elseif (!empty($jobsView)): ?>
<h3 style="color:var(--accent);margin-bottom:6px">⏰ Scheduled Backup Jobs</h3>
<div style="font-size:12px;color:#64748b;margin-bottom:14px">Each job creates a backup of the <strong>contents</strong> you select below and can push it to a <strong>destination</strong> automatically (runner fires every minute via cron). Retention is enforced after each upload using the destination's rules.</div>

<?php if (!empty($editJob)): ?>
<div class="card" id="editJobCard" style="margin-bottom:16px">
<h4 style="color:var(--accent);margin:0 0 12px;font-size:14px">✏️ Edit Job: <?php echo htmlspecialchars($editJob->name); ?></h4>
<form method="POST" action="/admin/backup/job/update/<?php echo $editJob->id; ?>" id="ejJobForm">
<div class="form-row-3">
<div class="form-group"><label>Job Name</label><input name="name" required class="inp inp-sm" value="<?php echo htmlspecialchars($editJob->name); ?>"></div>
<div class="form-group"><label>Schedule</label><select name="schedule_type" id="ejSchedule" class="inp inp-sm" onchange="jobSchedule('ej',this.value)"><?php foreach (['daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly'] as $sv=>$sl): ?><option value="<?php echo $sv; ?>" <?php echo ($editJob->schedule_type??'daily')===$sv?'selected':''; ?>><?php echo $sl; ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>Run Time</label><input name="run_time" type="time" class="inp inp-sm" value="<?php echo htmlspecialchars(substr($editJob->run_time ?? '03:00', 0, 5)); ?>"></div>
</div>
<div id="ejWeekly" style="display:none">
<div class="form-group"><label>Day of Week</label><select name="run_day" class="inp inp-sm"><?php foreach ([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $dv=>$dl): ?><option value="<?php echo $dv; ?>" <?php echo (int)($editJob->run_day ?? 1)===$dv?'selected':''; ?>><?php echo $dl; ?></option><?php endforeach; ?></select></div>
</div>
<div id="ejMonthly" style="display:none">
<div class="form-group"><label>Day of Month</label><input name="run_day" type="number" class="inp inp-sm" min="1" max="31" value="<?php echo (int)($editJob->run_day ?? 1); ?>"></div>
</div>
<div class="form-row-3">
<div class="form-group" style="grid-column:1/2"><label>Upload To</label><select name="destination_id" class="inp inp-sm"><option value="">— Local only (no upload) —</option><?php foreach ($destinations as $d): ?><option value="<?php echo $d->id; ?>" <?php echo (int)($editJob->destination_id ?? 0)===(int)$d->id?'selected':''; ?>><?php echo htmlspecialchars($d->name); ?> (<?php echo strtoupper($d->type); ?>)</option><?php endforeach; ?></select></div>
</div>
<?php $ejUsers = (array)($editJob->contents['users'] ?? []); $ejStations = (array)($editJob->contents['stations'] ?? []); ?>
<div style="margin-top:10px;border-top:1px solid rgba(255,255,255,.06);padding-top:10px">
<div style="font-size:11px;color:var(--text-secondary);margin-bottom:8px;font-weight:600">📦 Backup Contents</div>
<div class="form-group" style="margin-bottom:6px"><label style="font-size:12px;color:var(--text-secondary)"><input type="checkbox" name="contents_full" value="1" <?php echo !empty($editJob->contents['full'])?'checked':''; ?>> Full System <span style="color:#64748b">(entire /home, panel files + database)</span></label></div>
<div class="form-group" style="margin-bottom:6px"><label style="font-size:12px;color:var(--text-secondary)"><input type="checkbox" name="contents_users" value="1" onchange="jobWrap('ej','Users',this.checked)" <?php echo !empty($ejUsers)?'checked':''; ?>> Hosting Accounts</label>
<div id="ejUsersWrap" style="display:<?php echo !empty($ejUsers)?'block':'none'; ?>;margin-top:4px"><select name="contents_users[]" multiple size="5" class="inp inp-sm" style="width:100%"><?php foreach (($catalog['users'] ?? []) as $u): ?><option value="<?php echo $u['id']; ?>" <?php echo in_array($u['id'], $ejUsers)?'selected':''; ?>><?php echo htmlspecialchars($u['username'] . ' — ' . ($u['domain'] ?: 'no domain')); ?></option><?php endforeach; ?></select></div>
</div>
<div class="form-group" style="margin-bottom:6px"><label style="font-size:12px;color:var(--text-secondary)"><input type="checkbox" name="contents_stations" value="1" onchange="jobWrap('ej','Stations',this.checked)" <?php echo !empty($ejStations)?'checked':''; ?>> Streaming Stations</label>
<div id="ejStationsWrap" style="display:<?php echo !empty($ejStations)?'block':'none'; ?>;margin-top:4px"><select name="contents_stations[]" multiple size="5" class="inp inp-sm" style="width:100%"><?php foreach (($catalog['stations'] ?? []) as $s): ?><option value="<?php echo $s['id']; ?>" <?php echo in_array($s['id'], $ejStations)?'selected':''; ?>><?php echo htmlspecialchars($s['name']); ?></option><?php endforeach; ?></select></div>
</div>
<div class="form-group" style="margin-bottom:6px"><label style="font-size:12px;color:var(--text-secondary)"><input type="checkbox" name="contents_games" value="1" <?php echo !empty($editJob->contents['games'])?'checked':''; ?>> Game Servers</label></div>
<div class="form-group" style="margin-bottom:6px"><label style="font-size:12px;color:var(--text-secondary)"><input type="checkbox" name="contents_database" value="1" <?php echo !empty($editJob->contents['database'])?'checked':''; ?>> Panel Database</label></div>
<div class="form-group" style="margin-bottom:6px"><label style="font-size:12px;color:var(--text-secondary)"><input type="checkbox" name="contents_configs" value="1" <?php echo !empty($editJob->contents['configs'])?'checked':''; ?>> Panel Config <span style="color:#64748b">(.env, config/, branding, uploads)</span></label></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Custom Paths <span style="color:#64748b">(absolute, separated by ; or newline)</span></label><textarea name="contents_paths" class="inp inp-sm" rows="2"><?php echo htmlspecialchars($editJob->contents['paths'] ?? ''); ?></textarea></div>
</div>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
<button type="submit" class="btn btn-sm primary">Save Changes</button>
<a href="/admin/backup/jobs" class="btn btn-sm secondary">Cancel</a>
</div>
</form>
<script>document.addEventListener('DOMContentLoaded', function(){ jobSchedule('ej', <?php echo json_encode($editJob->schedule_type ?? 'daily'); ?>); });</script>
</div>
<?php endif; ?>

<div id="newJobForm" style="display:none;margin-bottom:16px">
<div class="card">
<h4 style="color:var(--accent);margin:0 0 12px;font-size:14px">＋ New Scheduled Job</h4>
<form method="POST" action="/admin/backup/job/store" id="njJobForm">
<div class="form-row-3">
<div class="form-group"><label>Job Name</label><input name="name" required class="inp inp-sm" placeholder="Nightly full backup"></div>
<div class="form-group"><label>Schedule</label><select name="schedule_type" id="njSchedule" class="inp inp-sm" onchange="jobSchedule('nj',this.value)"><?php foreach (['daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly'] as $sv=>$sl): ?><option value="<?php echo $sv; ?>"><?php echo $sl; ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>Run Time</label><input name="run_time" type="time" class="inp inp-sm" value="03:00"></div>
</div>
<div id="njWeekly" style="display:none">
<div class="form-group"><label>Day of Week</label><select name="run_day" class="inp inp-sm"><?php foreach ([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $dv=>$dl): ?><option value="<?php echo $dv; ?>"><?php echo $dl; ?></option><?php endforeach; ?></select></div>
</div>
<div id="njMonthly" style="display:none">
<div class="form-group"><label>Day of Month</label><input name="run_day" type="number" class="inp inp-sm" min="1" max="31" value="1"></div>
</div>
<div class="form-group"><label>Upload To</label><select name="destination_id" class="inp inp-sm"><option value="">— Local only (no upload) —</option><?php foreach ($destinations as $d): ?><option value="<?php echo $d->id; ?>"><?php echo htmlspecialchars($d->name); ?> (<?php echo strtoupper($d->type); ?>)</option><?php endforeach; ?></select></div>
<div style="margin-top:10px;border-top:1px solid rgba(255,255,255,.06);padding-top:10px">
<div style="font-size:11px;color:var(--text-secondary);margin-bottom:8px;font-weight:600">📦 Backup Contents</div>
<div class="form-group" style="margin-bottom:6px"><label style="font-size:12px;color:var(--text-secondary)"><input type="checkbox" name="contents_full" value="1"> Full System <span style="color:#64748b">(entire /home, panel files + database)</span></label></div>
<div class="form-group" style="margin-bottom:6px"><label style="font-size:12px;color:var(--text-secondary)"><input type="checkbox" name="contents_users" value="1" onchange="jobWrap('nj','Users',this.checked)"> Hosting Accounts</label>
<div id="njUsersWrap" style="display:none;margin-top:4px"><select name="contents_users[]" multiple size="5" class="inp inp-sm" style="width:100%"><?php foreach (($catalog['users'] ?? []) as $u): ?><option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username'] . ' — ' . ($u['domain'] ?: 'no domain')); ?></option><?php endforeach; ?></select></div>
</div>
<div class="form-group" style="margin-bottom:6px"><label style="font-size:12px;color:var(--text-secondary)"><input type="checkbox" name="contents_stations" value="1" onchange="jobWrap('nj','Stations',this.checked)"> Streaming Stations</label>
<div id="njStationsWrap" style="display:none;margin-top:4px"><select name="contents_stations[]" multiple size="5" class="inp inp-sm" style="width:100%"><?php foreach (($catalog['stations'] ?? []) as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option><?php endforeach; ?></select></div>
</div>
<div class="form-group" style="margin-bottom:6px"><label style="font-size:12px;color:var(--text-secondary)"><input type="checkbox" name="contents_games" value="1"> Game Servers</label></div>
<div class="form-group" style="margin-bottom:6px"><label style="font-size:12px;color:var(--text-secondary)"><input type="checkbox" name="contents_database" value="1"> Panel Database</label></div>
<div class="form-group" style="margin-bottom:6px"><label style="font-size:12px;color:var(--text-secondary)"><input type="checkbox" name="contents_configs" value="1"> Panel Config <span style="color:#64748b">(.env, config/, branding, uploads)</span></label></div>
<div class="form-group"><label style="font-size:12px;color:var(--text-secondary)">Custom Paths <span style="color:#64748b">(absolute, separated by ; or newline)</span></label><textarea name="contents_paths" class="inp inp-sm" rows="2"></textarea></div>
</div>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
<button type="submit" class="btn btn-sm primary">Create Job</button>
<a href="#" onclick="document.getElementById('newJobForm').style.display='none';return false" class="btn btn-sm secondary">Cancel</a>
</div>
</form>
</div>
</div>

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
<a href="#" onclick="document.getElementById('newJobForm').style.display=document.getElementById('newJobForm').style.display==='none'?'block':'none';return false" class="btn btn-sm primary">＋ New Job</a>
</div>

<?php if (empty($jobs)): ?>
<div class="card" style="text-align:center;padding:24px;color:#64748b">No scheduled jobs yet. Create one above.</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(400px,1fr));gap:12px">
<?php foreach ($jobs as $j): $jf = (array)($j->contents ?? []); ?>
<div class="card" style="margin-bottom:0">
<div style="display:flex;justify-content:space-between;align-items:start">
<div>
<strong style="font-size:14px;color:#e0e0e0"><?php echo htmlspecialchars($j->name); ?></strong>
<span class="status-badge <?php echo $j->is_active ? 'status-running' : 'status-stopped'; ?>" style="margin-left:4px"><?php echo $j->is_active ? 'Active' : 'Paused'; ?></span>
</div>
<div style="display:flex;gap:4px;flex-wrap:wrap;justify-content:flex-end">
<a href="/admin/backup/job/run/<?php echo $j->id; ?>" class="btn btn-sm btn-success" title="Run now">▶</a>
<a href="/admin/backup/job/toggle/<?php echo $j->id; ?>" class="btn btn-sm <?php echo $j->is_active ? 'btn-warning' : 'btn-success'; ?>" title="<?php echo $j->is_active ? 'Pause' : 'Resume'; ?>"><?php echo $j->is_active ? '⏸' : '▶'; ?></a>
<a href="/admin/backup/job/edit/<?php echo $j->id; ?>" class="btn btn-sm btn-secondary" title="Edit">✏️</a>
<a href="/admin/backup/job/delete/<?php echo $j->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this job?')" title="Delete">🗑</a>
</div>
</div>
<div style="margin-top:8px;font-size:12px;color:#94a3b8">⏰ <?php echo htmlspecialchars(strtoupper($j->schedule_type) . ' @ ' . $j->run_time . (($j->schedule_type==='weekly' || $j->schedule_type==='monthly') ? ' (day ' . (int)$j->run_day . ')' : '')); ?> · 📤 <?php echo htmlspecialchars($j->destination_name ?: 'Local only'); ?></div>
<?php $cb = []; if (!empty($jf['full'])) $cb[] = 'Full system'; if (!empty($jf['users'])) $cb[] = 'Users (' . count($jf['users']) . ')'; if (!empty($jf['stations'])) $cb[] = 'Stations (' . count($jf['stations']) . ')'; if (!empty($jf['games'])) $cb[] = 'Games'; if (!empty($jf['database'])) $cb[] = 'Database'; if (!empty($jf['configs'])) $cb[] = 'Config'; if (!empty($jf['paths'])) $cb[] = 'Paths'; ?>
<div style="margin-top:4px;font-size:11px;color:#64748b">📦 <?php echo htmlspecialchars($cb ? implode(', ', $cb) : 'Nothing selected'); ?></div>
<?php if (!empty($j->last_run_at)): ?>
<div style="margin-top:4px;font-size:10px;color:#94a3b8">Last run: <?php echo $j->last_run_at; ?> — <span class="status-badge <?php echo ($j->last_status??'')==='completed'?'status-active':(($j->last_status??'')==='failed'?'status-terminated':'status-pending'); ?>"><?php echo $j->last_status ?? '-'; ?></span><?php if (!empty($j->last_message)): ?> · <?php echo htmlspecialchars($j->last_message); ?><?php endif; ?></div>
<?php endif; ?>
<div style="margin-top:4px;font-size:10px;color:#64748b">Next run: <?php echo $j->next_run_at ?? 'not scheduled'; ?></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function jobSchedule(scope, val){
  var w = document.getElementById(scope + 'Weekly');
  var m = document.getElementById(scope + 'Monthly');
  if (!w || !m) return;
  w.style.display = val === 'weekly' ? 'block' : 'none';
  m.style.display = val === 'monthly' ? 'block' : 'none';
}
function jobWrap(scope, kind, checked){
  var el = document.getElementById(scope + kind + 'Wrap');
  if (el) el.style.display = checked ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', function(){
  if (document.getElementById('njSchedule')) jobSchedule('nj', document.getElementById('njSchedule').value);
  if (document.getElementById('ejSchedule')) jobSchedule('ej', document.getElementById('ejSchedule').value);
});
</script>

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