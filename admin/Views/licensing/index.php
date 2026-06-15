<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>License Status</h3>
<div class="value" style="font-size:16px;color:<?php echo $status['valid'] ? '#4ade80' : '#f87171'; ?>"><?php echo $status['valid'] ? '✓ VALID' : '✗ INVALID'; ?></div></div>
<div class="stat-card"><h3>Licensee</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($status['data']['licensee'] ?? 'N/A'); ?></div></div>
<div class="stat-card"><h3>License ID</h3><div class="value" style="font-size:14px"><?php echo htmlspecialchars($status['data']['license_id'] ?? 'N/A'); ?></div></div>
<div class="stat-card"><h3>Expiry</h3><div class="value" style="font-size:16px;color:<?php echo ($status['data']['expiry'] ?? '') === 'never' ? '#4ade80' : '#facc15'; ?>"><?php echo htmlspecialchars($status['data']['expiry'] ?? 'N/A'); ?></div></div>
</div>

<?php if (!$status['valid']): ?>
<div class="card" style="max-width:600px;margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">Activate License</h3>
<p style="color:var(--text-secondary);margin-bottom:12px"><?php echo htmlspecialchars($status['error'] ?? 'No valid license found.'); ?></p>
<form method="POST" action="/admin/licensing/upload" enctype="multipart/form-data">
<div class="form-group"><label>Upload license.key file</label><input name="license_file" type="file" accept=".key,.txt"></div>
<p style="color:var(--text-secondary);text-align:center;margin:8px 0">— or paste the license content below —</p>
<div class="form-group"><label>Paste license key content</label><textarea name="license_content" rows="8" style="font-family:monospace;font-size:12px"></textarea></div>
<button type="submit" class="btn primary">Activate License</button>
</form></div>
<?php endif; ?>

<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">License Details</h3>
<table><?php if (!empty($status['data'])): foreach ($status['data'] as $k => $v): ?>
<tr><td style="font-weight:600;text-transform:capitalize"><?php echo htmlspecialchars(str_replace('_', ' ', $k)); ?></td><td><?php echo htmlspecialchars(is_array($v) ? json_encode($v) : $v); ?></td></tr>
<?php endforeach; else: ?>
<tr><td colspan="2" style="text-align:center;padding:20px;color:#64748b">No license data available.</td></tr>
<?php endif; ?></table>
</div>

<div class="card" style="margin-top:16px"><h3 style="color:var(--accent);margin-bottom:8px">Public Key</h3>
<p style="color:var(--text-secondary);font-size:12px;margin-bottom:8px">This panel validates licenses using the embedded RSA public key.</p>
<pre style="background:rgba(0,0,0,.3);padding:12px;border-radius:6px;font-size:11px;color:#8b949e;overflow-x:auto;max-height:150px"><?php echo htmlspecialchars(file_get_contents(BASE_PATH . '/config/license_public.pem') ?: 'Public key not found'); ?></pre>
</div>
