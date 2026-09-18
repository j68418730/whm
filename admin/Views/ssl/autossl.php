<div class="card" style="max-width:600px;margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">AutoSSL (Let's Encrypt)</h3>
<p style="color:var(--text-secondary);margin-bottom:12px">AutoSSL automatically provisions and renews free Let's Encrypt certificates. A monthly cron sweep checks every certificate and renews any expiring within the configured window.</p>
<form method="POST" action="/admin/ssl/autossl-enable">
<div class="form-group"><label>Enable AutoSSL</label>
<select name="enabled"><option value="1" <?php echo !empty($enabled) ? 'selected' : ''; ?>>Enabled</option><option value="0" <?php echo empty($enabled) ? 'selected' : ''; ?>>Disabled</option></select></div>
<div class="form-group"><label>Notification / Account Email</label><input name="email" type="email" value="<?php echo htmlspecialchars($email ?? 'admin@planet-hosts.com'); ?>"></div>
<div class="row g-2" style="gap:10px">
<div class="form-group" style="flex:1"><label>Renew if expiring within (days)</label><input name="renew_days" type="number" min="7" max="60" value="<?php echo (int)($renew_days ?? 30); ?>"></div>
<div class="form-group" style="flex:1"><label>Check every (days)</label><input name="interval_days" type="number" min="1" max="90" value="<?php echo (int)($interval_days ?? 30); ?>"></div>
</div>
<button type="submit" class="btn primary">Save Settings</button>
</form>
<?php if (!empty($enabled)): ?>
<p style="font-size:11px;color:#4ade80;margin-top:10px">✅ AutoSSL enabled — monthly sweep every <strong><?php echo (int)($interval_days ?? 30); ?></strong> days, renews certs expiring within <strong><?php echo (int)($renew_days ?? 30); ?></strong> days.</p>
<?php else: ?>
<p style="font-size:11px;color:#64748b;margin-top:10px">AutoSSL is disabled. Enable it above to schedule monthly certificate renewal.</p>
<?php endif; ?>
<p style="font-size:11px;color:#64748b;margin-top:6px">Last AutoSSL run: <strong><?php echo htmlspecialchars($last_run ?? 'Never'); ?></strong><?php if (!empty($last_run_data['renewed'])): ?> — renewed <?php echo count($last_run_data['renewed']); ?> cert(s)<?php endif; ?></p>
</div>

<?php
$certbot = trim(shell_exec('which certbot 2>/dev/null') ?: '');
$certs = shell_exec('certbot certificates 2>/dev/null') ?: 'certbot not installed';
?>
<div class="card" style="margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">Certbot Status</h3>
<pre style="background:rgba(0,0,0,.3);padding:12px;border-radius:6px;font-size:13px;color:#c9d1d9;overflow-x:auto;max-height:260px"><?php echo htmlspecialchars($certbot ? $certs : 'certbot is not installed. Run: apt install certbot python3-certbot-apache'); ?></pre>
<?php if ($certbot): ?>
<a href="/admin/ssl/autossl-run" class="btn primary" onclick="return confirm('Run AutoSSL sweep now? It will check all certificates and renew any expiring within the configured window.')">▶ Run AutoSSL Sweep Now</a>
<?php endif; ?>
</div>

<?php if (!empty($last_certs)): ?>
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Certificates (expiring first)</h3>
<table>
<tr><th>Domain</th><th>Status</th><th>Expires</th><th>Last Renewal</th></tr>
<?php foreach ($last_certs as $c): $days = $c->expires_at ? floor((strtotime($c->expires_at) - time()) / 86400) : null; ?>
<tr>
<td><?php echo htmlspecialchars($c->domain); ?></td>
<td>
<?php if ($days === null): ?><span class="badge bg-secondary">Unknown</span>
<?php elseif ($days < 0): ?><span class="badge bg-danger">Expired</span>
<?php elseif ($days <= 14): ?><span class="badge bg-warning"><?php echo $days; ?>d left</span>
<?php else: ?><span class="badge bg-success"><?php echo $days; ?>d left</span><?php endif; ?>
</td>
<td style="font-size:12px"><?php echo htmlspecialchars($c->expires_at ?? '-'); ?></td>
<td style="font-size:12px;color:#64748b"><?php echo htmlspecialchars($c->last_renewal ?? '-'); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>