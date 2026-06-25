<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['validation_warnings'])): ?>
<div class="alert" style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.3);color:#fbbf24">
<strong>Warnings:</strong>
<ul style="margin:4px 0 0 16px">
<?php foreach ($_SESSION['validation_warnings'] as $w): ?>
<li><?php echo htmlspecialchars($w, ENT_QUOTES, 'UTF-8'); ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php unset($_SESSION['validation_warnings']); endif; ?>

<?php if (!empty($_SESSION['rebuild_log'])): ?>
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:8px">Rebuild Log</h3>
<pre style="background:rgba(0,0,0,.3);padding:12px;border-radius:6px;font-size:11px;max-height:300px;overflow-y:auto;color:#94a3b8"><?php echo htmlspecialchars(implode("\n", $_SESSION['rebuild_log']), ENT_QUOTES, 'UTF-8'); ?></pre>
</div>
<?php unset($_SESSION['rebuild_log']); endif; ?>

<!-- Status Cards -->
<div class="stats-grid" style="margin-bottom:16px;grid-template-columns:repeat(auto-fit,minmax(160px,1fr))">
<div class="stat-card">
<h3>Hostname</h3>
<div class="value" style="font-size:15px"><?php echo htmlspecialchars($currentHostname); ?></div>
<div class="label">System</div>
</div>
<div class="stat-card">
<h3>Panel URL</h3>
<div class="value" style="font-size:15px"><?php echo htmlspecialchars($panelUrl); ?></div>
<div class="label">Saved setting</div>
</div>
<div class="stat-card">
<h3>Public IP</h3>
<div class="value" style="font-size:15px"><?php echo htmlspecialchars($publicIp); ?></div>
<div class="label"><?php echo htmlspecialchars($serverIp); ?> (local)</div>
</div>
<div class="stat-card">
<h3>SSL</h3>
<div class="value" style="font-size:15px;color:<?php echo $sslStatus['status'] === 'valid' ? '#4ade80' : '#f87171'; ?>">
<?php echo $sslStatus['status'] === 'valid' ? 'Valid' : 'Missing'; ?>
</div>
<div class="label"><?php echo $sslStatus['days_left'] ? $sslStatus['days_left'] . ' days left' : 'No certificate'; ?></div>
</div>
</div>

<!-- Health Check -->
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">Hostname Health</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:13px">
<div>System Hostname: <strong><?php echo htmlspecialchars($health['system_hostname'] ?? '?'); ?></strong></div>
<div>Matches Config: <strong style="color:<?php echo ($health['hostname_match'] ?? false) ? '#4ade80' : '#f87171'; ?>"><?php echo ($health['hostname_match'] ?? false) ? 'Yes' : 'No'; ?></strong></div>
<div>DNS Resolves: <strong style="color:<?php echo ($health['dns_resolves'] ?? false) ? '#4ade80' : '#f87171'; ?>"><?php echo ($health['dns_resolves'] ?? false) ? 'Yes' : 'No'; ?></strong></div>
<div>Resolved IP: <strong><?php echo htmlspecialchars($health['resolved_ip'] ?? '?'); ?></strong></div>
<div>Apache: <strong style="color:<?php echo ($health['apache_running'] ?? false) ? '#4ade80' : '#f87171'; ?>"><?php echo ($health['apache_running'] ?? false) ? 'Running' : 'Stopped'; ?></strong></div>
<div>VHost Exists: <strong style="color:<?php echo ($health['vhost_exists'] ?? false) ? '#4ade80' : '#f87171'; ?>"><?php echo ($health['vhost_exists'] ?? false) ? 'Yes' : 'No'; ?></strong></div>
<div>HTTP Access: <strong style="color:<?php echo ($health['http_works'] ?? false) ? '#4ade80' : '#f87171'; ?>"><?php echo ($health['http_works'] ?? false) ? 'OK' : 'Failed'; ?></strong></div>
<div>HTTPS Access: <strong style="color:<?php echo ($health['https_works'] ?? false) ? '#4ade80' : '#f87171'; ?>"><?php echo ($health['https_works'] ?? false) ? 'OK' : 'Failed'; ?></strong></div>
</div>
</div>

<!-- Hostname Form -->
<div class="card" style="max-width:700px;margin-bottom:16px">
<form method="POST" action="/admin/hostname/save">
<h3 style="color:var(--accent);margin-bottom:12px">Configure Hostname</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="form-group"><label>Hostname (FQDN)</label>
<input name="hostname" value="<?php echo htmlspecialchars($savedHostname); ?>" placeholder="server.example.com" required>
<div style="font-size:11px;color:#64748b;margin-top:2px">Fully qualified domain name (e.g. server.planet-hosts.com)</div>
</div>
<div class="form-group"><label>Admin Email</label>
<input name="admin_email" value="<?php echo htmlspecialchars($adminEmail); ?>" placeholder="admin@<?php echo htmlspecialchars($savedHostname); ?>">
</div>
<div class="form-group"><label>Primary Nameserver</label>
<input name="ns1" value="<?php echo htmlspecialchars($ns1); ?>" placeholder="ns1.planet-hosts.com">
</div>
<div class="form-group"><label>Secondary Nameserver</label>
<input name="ns2" value="<?php echo htmlspecialchars($ns2); ?>" placeholder="ns2.planet-hosts.com">
</div>
</div>
<div class="form-group" style="margin-top:8px">
<label style="display:flex;align-items:center;gap:8px;cursor:pointer">
<input type="checkbox" name="auto_ssl" value="1" checked>
<span>Auto-provision SSL certificate via Let's Encrypt</span>
</label>
</div>
<button type="submit" class="btn primary">Save Hostname</button>
</form>
</div>

<!-- Rebuild Button -->
<div class="card" style="max-width:700px;margin-bottom:16px">
<form method="POST" action="/admin/hostname/rebuild">
<h3 style="color:var(--accent);margin-bottom:12px">Rebuild Hostname Configuration</h3>
<p style="color:#64748b;font-size:13px;margin-bottom:12px">
This will re-create the Apache vhost, re-issue the SSL certificate, and reload services.
Use this if the panel is not accessible via the hostname.
</p>
<input type="hidden" name="hostname" value="<?php echo htmlspecialchars($savedHostname); ?>">
<input type="hidden" name="admin_email" value="<?php echo htmlspecialchars($adminEmail); ?>">
<button type="submit" class="btn primary" onclick="return confirm('Rebuild all hostname configuration? Apache will reload.')">
<i class="bi bi-arrow-repeat"></i> Rebuild Hostname Configuration
</button>
</form>
</div>

<!-- AutoSSL -->
<div class="card" style="max-width:700px">
<form method="POST" action="/admin/hostname/autossl">
<h3 style="color:var(--accent);margin-bottom:12px">Manual SSL</h3>
<p style="color:#64748b;font-size:13px;margin-bottom:12px">
Request or renew the SSL certificate for the hostname immediately.
</p>
<button type="submit" class="btn secondary"><i class="bi bi-shield-check"></i> Run AutoSSL for Hostname</button>
</form>
</div>
