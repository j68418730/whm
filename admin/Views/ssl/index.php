<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<?php if (!empty($autossl)): ?>
<div class="card" style="max-width:600px;margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">AutoSSL (Let's Encrypt)</h3>
<p style="color:var(--text-secondary);margin-bottom:12px">AutoSSL automatically provisions and renews free SSL certificates from Let's Encrypt for all domains on the server.</p>
<form method="POST" action="/admin/ssl/autossl-enable">
<div class="form-group"><label>Enable AutoSSL</label>
<select name="enabled"><option value="1">Enabled</option><option value="0" selected>Disabled</option></select></div>
<div class="form-group"><label>Notification Email</label><input name="email" type="email" placeholder="admin@planet-hosts.com"></div>
<button type="submit" class="btn primary">Save Settings</button>
</form></div>

<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">AutoSSL Status</h3>
<p style="color:var(--text-secondary)">AutoSSL is currently <strong style="color:<?php echo !empty($autossl_enabled) ? '#4ade80' : '#f87171'; ?>"><?php echo !empty($autossl_enabled) ? 'enabled' : 'disabled'; ?></strong>. <?php if (!empty($autossl_enabled)): ?>Cron runs every 6 hours to check and renew certificates.<?php else: ?>Enable it above to automatically issue Let's Encrypt certificates.<?php endif; ?></p>
<?php if (!empty($certs)): ?>
<div style="margin-top:12px"><strong>Issued Certificates:</strong></div>
<?php foreach ($certs as $c): ?>
<div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;border-bottom:1px solid rgba(255,255,255,.04)">
<span><?php echo htmlspecialchars($c->domain); ?></span>
<span style="color:<?php echo $c->status === 'active' ? '#4ade80' : '#facc15'; ?>"><?php echo $c->status; ?> · Expires: <?php echo $c->expires_at ?? 'N/A'; ?></span>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<?php else: ?>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Certificates</h3><div class="value"><?php echo $domainCount; ?></div></div>
<div class="stat-card"><h3>Expiring Soon</h3><div class="value" style="color:<?php echo $expiringSoon > 0 ? '#f87171' : '#4ade80'; ?>"><?php echo $expiringSoon; ?></div></div>
<div class="stat-card"><h3>AutoSSL</h3><div class="value" style="font-size:16px"><?php echo $expiringSoon > 0 ? '⚠ Issues' : '✓ Active'; ?></div></div>
</div>

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
<a href="#install" class="btn primary" onclick="document.getElementById('sslForm').classList.toggle('hidden')">Install Certificate</a>
<a href="/admin/ssl/autossl" class="btn secondary">AutoSSL Settings</a>
</div>

<div id="sslForm" class="card hidden" style="max-width:600px;margin-bottom:20px">
<form method="POST" action="/admin/ssl/install">
<h3 style="color:var(--accent);margin-bottom:12px">Install SSL Certificate</h3>
<div class="form-group"><label>Domain</label><input name="domain" required placeholder="example.com"></div>
<div class="form-group"><label>Certificate (PEM)</label><textarea name="certificate" rows="4" required style="font-family:monospace;font-size:12px"></textarea></div>
<div class="form-group"><label>Private Key (PEM)</label><textarea name="private_key" rows="4" required style="font-family:monospace;font-size:12px"></textarea></div>
<button type="submit" class="btn primary">Install</button>
</form></div>

<h3 style="margin-bottom:10px">Installed Certificates</h3>
<table><tr><th>Domain</th><th>Status</th><th>Installed</th><th>Expires</th></tr>
<?php if (!empty($certs)): foreach ($certs as $c): ?>
<tr>
<td><?php echo htmlspecialchars($c->domain); ?></td>
<td><span class="status-badge status-<?php echo $c->status === 'active' ? 'active' : 'terminated'; ?>"><?php echo htmlspecialchars($c->status); ?></span></td>
<td><?php echo htmlspecialchars($c->created_at); ?></td>
<td><?php echo htmlspecialchars($c->expires_at ?? 'N/A'); ?></td></tr>
<?php endforeach; else: ?>
<tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No SSL certificates installed yet.</td></tr>
<?php endif; ?></table>
<?php endif; ?>
