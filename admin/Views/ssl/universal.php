<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom:16px;grid-template-columns:repeat(auto-fit,minmax(140px,1fr))">
<div class="stat-card"><h3>Certificates</h3><div class="value"><?php echo count($certs); ?></div></div>
<div class="stat-card"><h3>SSL Services</h3><div class="value"><?php echo $activeSsl; ?> / <?php echo $totalServices; ?></div><div class="label">enabled / total</div></div>
<div class="stat-card"><h3>Expiring Soon</h3><div class="value" style="color:<?php echo $expiringSoon > 0 ? '#f87171' : '#4ade80'; ?>"><?php echo $expiringSoon; ?></div></div>
<div class="stat-card"><h3>Detected Ports</h3><div class="value"><?php echo count($ports); ?></div></div>
</div>

<!-- Service Profiles / Quick Configure -->
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">Detected Services</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px">
<?php foreach ($profiles as $type => $p): ?>
<div style="background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.08);border-radius:8px;padding:14px;text-align:center">
<div style="font-size:24px;margin-bottom:4px">
<?php
$icons = ['apache'=>'🕸️','nginx'=>'🌐','icecast'=>'📻','ftp'=>'📂','postfix'=>'📧','dovecot'=>'📨','liquidsoap'=>'🎵'];
echo $icons[$type] ?? '🔌';
?>
</div>
<div style="font-weight:600;font-size:14px"><?php echo $p['name']; ?></div>
<div style="font-size:11px;color:#64748b;margin:4px 0">
<?php if ($p['installed'] ?? false): ?>
<span style="color:#4ade80">● Installed</span>
<?php if ($p['running'] ?? false): ?> <span style="color:#4ade80">Running</span><?php else: ?> <span style="color:#f87171">Stopped</span><?php endif; ?>
<?php else: ?>
<span style="color:#64748b">Not installed</span>
<?php endif; ?>
</div>
<?php if (!empty($p['ports'])): ?>
<div style="font-size:11px;color:#64748b">Ports: <?php echo implode(', ', $p['ports']); ?></div>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- Configure SSL Form -->
<div class="card" style="margin-bottom:16px;max-width:700px">
<form method="POST" action="/admin/ssl/universal/configure">
<h3 style="color:var(--accent);margin-bottom:12px">Configure SSL for Service</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="form-group"><label>Service Type</label>
<select name="service_type">
<?php foreach ($profiles as $type => $p): ?>
<option value="<?php echo $type; ?>"><?php echo $p['name']; ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Domain / Hostname</label>
<input name="domain" placeholder="server.planet-hosts.com" value="<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>"></div>
<div class="form-group"><label>Port (optional)</label>
<input name="port" type="number" placeholder="443, 8443, 990..."></div>
<div class="form-group"><label>SSL Mode (Icecast)</label>
<select name="ssl_mode"><option value="native">Native TLS</option><option value="reverse-proxy">Nginx/Apache Reverse Proxy</option></select></div>
</div>
<div class="form-group"><label>Admin Email</label>
<input name="email" placeholder="admin@example.com"></div>
<button type="submit" class="btn primary"><i class="bi bi-shield-check"></i> Configure SSL</button>
</form>
</div>

<!-- SSL Services Table -->
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">SSL Services</h3>
<?php if (!empty($services)): ?>
<table>
<thead><tr><th>Service</th><th>Domain</th><th>Port</th><th>Mode</th><th>Status</th><th>Verified</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($services as $svc): ?>
<tr>
<td><?php echo htmlspecialchars($svc->service_name); ?></td>
<td><?php echo htmlspecialchars($svc->domain); ?></td>
<td><?php echo $svc->port; ?></td>
<td><?php echo $svc->ssl_mode; ?></td>
<td><span class="status-badge status-<?php echo $svc->status === 'active' ? 'active' : 'terminated'; ?>"><?php echo htmlspecialchars($svc->status); ?></span></td>
<td><?php echo $svc->last_verified ? htmlspecialchars(date('Y-m-d', strtotime($svc->last_verified))) : '-'; ?></td>
<td>
<a href="/admin/ssl/universal/repair?service_id=<?php echo $svc->id; ?>" class="btn btn-sm secondary" onclick="return confirm('Repair this service?')">Repair</a>
<a href="/admin/ssl/universal/delete?id=<?php echo $svc->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171" onclick="return confirm('Remove this service record?')">Remove</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p style="color:#64748b">No SSL services configured yet. Use the form above to configure a service.</p>
<?php endif; ?>
</div>

<!-- Health Check Results -->
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">SSL Health Check</h3>
<div id="sslHealthResults">
<?php if (!empty($health)): ?>
<table>
<thead><tr><th>Service</th><th>Domain</th><th>Port</th><th>Status</th><th>Days Left</th><th>TLS OK</th><th>Hostname Match</th></tr></thead>
<tbody>
<?php foreach ($health as $h): ?>
<tr>
<td><?php echo htmlspecialchars($h['service_name'] ?? $h['service']); ?></td>
<td><?php echo htmlspecialchars($h['domain']); ?></td>
<td><?php echo $h['port']; ?></td>
<td><span style="color:<?php echo $h['status'] === 'ok' ? '#4ade80' : ($h['status'] === 'expiring_soon' ? '#fbbf24' : '#f87171'); ?>"><?php echo $h['status']; ?></span></td>
<td><?php echo $h['days_left']; ?></td>
<td><?php echo $h['tls_handshake'] ? '<span style="color:#4ade80">✓</span>' : '<span style="color:#f87171">✗</span>'; ?></td>
<td><?php echo $h['hostname_match'] ? '<span style="color:#4ade80">✓</span>' : '<span style="color:#f87171">✗</span>'; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p style="color:#64748b">No SSL services to check. Configure a service first.</p>
<?php endif; ?>
</div>
<button class="btn secondary" style="margin-top:8px" onclick="refreshHealth()">Refresh Health Check</button>
</div>

<!-- Certificates -->
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">Certificates</h3>
<?php if (!empty($certs)): ?>
<table>
<thead><tr><th>Domain</th><th>Issuer</th><th>Expires</th><th>Days Left</th><th>Auto Renew</th><th>Last Renewal</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($certs as $c): ?>
<?php $daysLeft = $c->expires_at ? max(0, floor((strtotime($c->expires_at) - time()) / 86400)) : 0; ?>
<tr>
<td><?php echo htmlspecialchars($c->domain); ?></td>
<td><?php echo htmlspecialchars($c->issuer ?: 'Let\'s Encrypt'); ?></td>
<td><?php echo htmlspecialchars($c->expires_at ?? 'N/A'); ?></td>
<td style="color:<?php echo $daysLeft < 7 ? '#f87171' : ($daysLeft < 30 ? '#fbbf24' : '#4ade80'); ?>"><?php echo $daysLeft; ?> days</td>
<td><?php echo $c->auto_renew ? '<span style="color:#4ade80">Yes</span>' : '<span style="color:#64748b">No</span>'; ?></td>
<td><?php echo $c->last_renewal ? htmlspecialchars(date('Y-m-d', strtotime($c->last_renewal))) : '-'; ?></td>
<td><a href="/admin/ssl/universal/renew?domain=<?php echo urlencode($c->domain); ?>" class="btn btn-sm secondary" onclick="return confirm('Renew certificate for <?php echo htmlspecialchars($c->domain); ?>?')">Renew</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p style="color:#64748b">No certificates found.</p>
<?php endif; ?>
</div>

<!-- Quick Actions -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
<a href="/admin/ssl/universal/renew" class="btn secondary" onclick="return confirm('Renew ALL certificates?')">Renew All Certs</a>
<a href="/admin/ssl/autossl" class="btn secondary">AutoSSL Settings</a>
<a href="/admin/ssl/universal/health" class="btn secondary" target="_blank">Health JSON</a>
</div>

<!-- Recent Logs -->
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">SSL Activity Log</h3>
<?php if (!empty($logs)): ?>
<div style="max-height:200px;overflow-y:auto;font-size:12px">
<?php foreach ($logs as $l): ?>
<div style="display:flex;gap:8px;padding:4px 0;border-bottom:1px solid rgba(255,255,255,.04)">
<span style="color:#64748b;flex-shrink:0"><?php echo htmlspecialchars(date('H:i:s', strtotime($l->created_at))); ?></span>
<span style="color:<?php echo $l->status === 'success' ? '#4ade80' : ($l->status === 'error' ? '#f87171' : '#64748b'); ?>;flex-shrink:0">[<?php echo htmlspecialchars($l->status); ?>]</span>
<span style="color:#94a3b8;flex-shrink:0"><?php echo htmlspecialchars($l->action); ?></span>
<span><?php echo htmlspecialchars($l->domain); ?></span>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p style="color:#64748b">No SSL activity logged yet.</p>
<?php endif; ?>
</div>

<script>
function refreshHealth() {
    var btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Refreshing...';
    fetch('/admin/ssl/universal/health').then(function(r){return r.json()}).then(function(d){
        var el = document.getElementById('sslHealthResults');
        if (d.services && d.services.length) {
            var html = '<table><thead><tr><th>Service</th><th>Domain</th><th>Port</th><th>Status</th><th>Days Left</th><th>TLS OK</th><th>Hostname Match</th></tr></thead><tbody>';
            d.services.forEach(function(h){
                var color = h.status === 'ok' ? '#4ade80' : (h.status === 'expiring_soon' ? '#fbbf24' : '#f87171');
                html += '<tr><td>' + h.service_name + '</td><td>' + h.domain + '</td><td>' + h.port + '</td><td style="color:' + color + '">' + h.status + '</td><td>' + h.days_left + '</td><td>' + (h.tls_handshake ? '<span style=\"color:#4ade80\">✓</span>' : '<span style=\"color:#f87171\">✗</span>') + '</td><td>' + (h.hostname_match ? '<span style=\"color:#4ade80\">✓</span>' : '<span style=\"color:#f87171\">✗</span>') + '</td></tr>';
            });
            html += '</tbody></table>';
            el.innerHTML = html;
        }
        btn.disabled = false;
        btn.textContent = 'Refresh Health Check';
    }).catch(function(){
        btn.disabled = false;
        btn.textContent = 'Refresh Health Check';
    });
}
</script>
