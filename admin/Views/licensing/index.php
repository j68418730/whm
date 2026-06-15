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
<div class="stat-card"><h3>License Type</h3><div class="value" style="font-size:16px"><?php echo strtoupper($status['type'] ?? 'N/A'); ?></div></div>
<?php if ($status['valid']): ?>
<div class="stat-card"><h3>Licensee</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($status['data']['licensee'] ?? ''); ?></div></div>
<div class="stat-card"><h3>Expiry</h3><div class="value" style="font-size:16px;color:<?php echo ($status['data']['expiry'] ?? '') === 'never' ? '#4ade80' : '#facc15'; ?>"><?php echo htmlspecialchars($status['data']['expiry'] ?? 'N/A'); ?></div></div>
<?php else: ?>
<div class="stat-card"><h3>Error</h3><div class="value" style="font-size:14px;color:#f87171"><?php echo htmlspecialchars($status['error'] ?? ''); ?></div></div>
<div class="stat-card"><h3>Contact</h3><div class="value" style="font-size:14px">nd2no_19@hotmail.com</div></div>
<?php endif; ?>
</div>

<div class="card" style="margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">Feature Access</h3>
<table><tr><th>Feature</th><th>Status</th></tr>
<?php foreach (['accounts'=>'Hosting Accounts','packages'=>'Packages','dns'=>'DNS Zones','email'=>'Email','ftp'=>'FTP','databases'=>'Databases','backups'=>'Backups','ssl'=>'SSL','domains'=>'Domains','radio'=>'Radio Streaming','streams'=>'Stream Management','autodj'=>'AutoDJ'] as $k => $v): ?>
<tr><td><?php echo $v; ?></td>
<td><span class="status-badge status-<?php echo ($features[$k] ?? false) ? 'active' : 'terminated'; ?>"><?php echo ($features[$k] ?? false) ? '✓ Enabled' : '— Locked'; ?></span></td></tr>
<?php endforeach; ?></table>
</div>

<?php if (!$status['valid']): ?>
<div class="card" style="max-width:600px">
<h3 style="color:var(--accent);margin-bottom:12px">Activate License</h3>
<p style="color:var(--text-secondary);margin-bottom:12px">To obtain a license key, email <strong>nd2no_19@hotmail.com</strong> with your server IP. Include which license type you need:</p>
<ul style="color:var(--text-secondary);margin:0 0 12px 20px;line-height:1.8;font-size:13px">
<li><strong>Hosting</strong> — Accounts, packages, DNS, email, FTP, databases, backups, SSL</li>
<li><strong>Icecast</strong> — Radio streaming, AutoDJ, DJ accounts, transcoding</li>
<li><strong>Full</strong> — Everything</li>
</ul>
<form method="POST" action="/admin/licensing/upload" enctype="multipart/form-data">
<div class="form-group"><label>Upload license.key file</label><input name="license_file" type="file" accept=".key,.txt"></div>
<p style="color:var(--text-secondary);text-align:center;margin:8px 0">— or paste below —</p>
<div class="form-group"><label>Paste license key</label><textarea name="license_content" rows="6" style="font-family:monospace;font-size:12px"></textarea></div>
<button type="submit" class="btn primary">Activate</button>
</form></div>
<?php endif; ?>
