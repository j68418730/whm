<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Status</h3>
<div class="value" style="font-size:16px;color:<?php echo $status['valid'] ? '#4ade80' : ($status['trial'] && ($status['trial_days_left'] ?? 0) > 0 ? '#facc15' : '#f87171'); ?>">
<?php if ($status['valid']): ?>✓ ACTIVE
<?php elseif ($status['trial'] && ($status['trial_days_left'] ?? 0) > 0): ?>⚠ TRIAL (<?php echo $status['trial_days_left']; ?> days left)
<?php else: ?>✗ LOCKED<?php endif; ?>
</div></div>
<div class="stat-card"><h3>Type</h3><div class="value" style="font-size:16px"><?php echo strtoupper($status['type'] ?? 'N/A'); ?></div></div>
<?php if ($status['valid']): ?>
<div class="stat-card"><h3>Licensee</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($status['data']['licensee'] ?? ''); ?></div></div>
<div class="stat-card"><h3>Expiry</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($status['data']['expiry'] ?? 'N/A'); ?></div></div>
<?php else: ?>
<div class="stat-card"><h3>Error</h3><div class="value" style="font-size:14px;color:#f87171"><?php echo htmlspecialchars($status['error'] ?? ''); ?></div></div>
<div class="stat-card"><h3>Contact</h3><div class="value" style="font-size:14px">nd2no_19@hotmail.com</div></div>
<?php endif; ?>
</div>

<div class="card" style="margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">Current License Key</h3>
<?php $keyContent = @file_get_contents(BASE_PATH . '/license.key'); ?>
<?php if ($keyContent): ?>
<textarea readonly style="width:100%;height:120px;font-family:monospace;font-size:11px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);color:#8b949e;border-radius:6px;padding:10px"><?php echo htmlspecialchars($keyContent); ?></textarea>
<?php else: ?>
<p style="color:var(--text-secondary)">No license key file found.</p>
<?php endif; ?>
</div>

<div class="card" style="max-width:600px">
<h3 style="color:var(--accent);margin-bottom:12px">Change License Key</h3>
<form method="POST" action="/admin/licensing/upload" enctype="multipart/form-data">
<div class="form-group"><label>Upload new license.key file</label><input name="license_file" type="file" accept=".key,.txt"></div>
<p style="color:var(--text-secondary);text-align:center;margin:8px 0">— or paste new key below —</p>
<div class="form-group"><label>Paste license key content</label><textarea name="license_content" rows="6" style="font-family:monospace;font-size:12px"></textarea></div>
<button type="submit" class="btn primary">Update License</button>
</form></div>

<div class="card" style="margin-top:16px"><h3 style="color:var(--accent);margin-bottom:12px">Feature Access</h3>
<table><tr><th>Feature</th><th>Status</th></tr>
<?php foreach (['accounts'=>'Hosting Accounts','packages'=>'Packages','dns'=>'DNS Zones','email'=>'Email','ftp'=>'FTP','databases'=>'Databases','backups'=>'Backups','ssl'=>'SSL','domains'=>'Domains','radio'=>'Radio Streaming','streams'=>'Stream Management','autodj'=>'AutoDJ'] as $k => $v): ?>
<tr><td><?php echo $v; ?></td>
<td><span class="status-badge status-<?php echo ($features[$k] ?? false) ? 'active' : 'terminated'; ?>"><?php echo ($features[$k] ?? false) ? '✓ Enabled' : '— Locked'; ?></span></td></tr>
<?php endforeach; ?></table>
</div>
