<div class="card" style="max-width:600px;margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">AutoSSL (Let's Encrypt)</h3>
<p style="color:var(--text-secondary);margin-bottom:12px">AutoSSL automatically provisions free SSL certificates from Let's Encrypt for domains on this server.</p>
<form method="POST" action="/admin/ssl/autossl-enable">
<div class="form-group"><label>Enable AutoSSL</label>
<select name="enabled"><option value="1">Enabled</option><option value="0" selected>Disabled</option></select></div>
<div class="form-group"><label>Notification Email</label><input name="email" type="email" value="admin@planet-hosts.com"></div>
<button type="submit" class="btn primary">Save Settings</button>
</form></div>

<?php
$certbot = trim(shell_exec('which certbot 2>/dev/null') ?: '');
$certs = shell_exec('certbot certificates 2>/dev/null') ?: 'certbot not installed';
?>
<div class="card"><h3 style="color:var(--accent);margin-bottom:12px)">Certbot Status</h3>
<pre style="background:rgba(0,0,0,.3);padding:12px;border-radius:6px;font-size:13px;color:#c9d1d9;overflow-x:auto"><?php echo htmlspecialchars($certbot ? $certs : 'certbot is not installed. Run: apt install certbot python3-certbot-apache'); ?></pre>
<?php if ($certbot): ?>
<a href="/admin/ssl/autossl-run" class="btn primary" onclick="return confirm('Run AutoSSL now? This may take a few minutes.')">Run AutoSSL Now</a>
<?php endif; ?>
</div>
