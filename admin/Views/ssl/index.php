<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<style>
.ssl-wrap{max-width:1200px;margin:0 auto}
.ssl-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:20px}
.ssl-card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;text-align:center}
.ssl-card .num{font-size:24px;font-weight:700;margin-top:4px}
.ssl-card .lbl{font-size:11px;color:#64748b}
.ssl-toolbar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center}
</style>

<div class="ssl-wrap">
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:16px">
<h2 style="margin:0;font-size:18px">🔐 SSL / TLS Manager</h2>
<div style="display:flex;gap:8px">
<a href="/admin/ssl/universal" class="btn primary" style="text-decoration:none"><i class="bi bi-shield-lock"></i> Universal SSL Manager</a>
<a href="/admin/ssl/autossl" class="btn secondary" style="text-decoration:none"><i class="bi bi-magic"></i> AutoSSL</a>
</div>
</div>

<div class="ssl-grid">
<div class="ssl-card"><div class="lbl">Certificates</div><div class="num"><?php echo $domainCount; ?></div></div>
<div class="ssl-card"><div class="lbl">Expiring ≤30d</div><div class="num" style="color:<?php echo $expiringSoon > 0 ? '#f87171' : '#4ade80'; ?>"><?php echo $expiringSoon; ?></div></div>
<div class="ssl-card"><div class="lbl">Status</div><div class="num" style="font-size:18px;color:<?php echo $expiringSoon > 0 ? '#fbbf24' : '#4ade80'; ?>"><?php echo $expiringSoon > 0 ? '⚠ Review' : '✓ Healthy'; ?></div></div>
</div>

<div class="ssl-toolbar">
<a href="/admin/ssl/universal/fix-all" class="btn" style="background:linear-gradient(135deg,#008cff,#0066cc);color:#fff;text-decoration:none;font-weight:700" onclick="return confirm('Run Fix All to repair all missing/expiring certificates?')">🔧 Fix All Missing Certs</a>
<a href="#" class="btn secondary" onclick="document.getElementById('sslForm').classList.toggle('hidden');return false"><i class="bi bi-plus-circle"></i> Install Certificate</a>
<a href="/admin/ssl/universal/health" class="btn secondary" target="_blank">Health JSON</a>
</div>

<div id="sslForm" class="card hidden" style="max-width:620px;margin-bottom:20px">
<form method="POST" action="/admin/ssl/install">
<h3 style="color:var(--accent);margin-bottom:12px">Install SSL Certificate</h3>
<div class="form-group"><label>Domain</label><input name="domain" required placeholder="example.com"></div>
<div class="form-group"><label>Certificate (PEM)</label><textarea name="certificate" rows="4" required style="font-family:monospace;font-size:12px"></textarea></div>
<div class="form-group"><label>Private Key (PEM)</label><textarea name="private_key" rows="4" required style="font-family:monospace;font-size:12px"></textarea></div>
<button type="submit" class="btn primary">Install</button>
</form></div>

<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">Installed Certificates</h3>
<table>
<thead><tr><th>Domain</th><th>Status</th><th>Installed</th><th>Expires</th><th>Days Left</th><th>Actions</th></tr></thead>
<tbody>
<?php if (!empty($certs)): foreach ($certs as $c):
$certPath = "/etc/letsencrypt/live/{$c->domain}/fullchain.pem";
$certExists = file_exists($certPath);
$daysLeft = ($c->expires_at && $certExists) ? max(0, floor((strtotime($c->expires_at) - time()) / 86400)) : 0;
?>
<tr>
<td><strong><?php echo htmlspecialchars($c->domain); ?></strong></td>
<td><span class="status-badge status-<?php echo $c->status === 'active' ? 'active' : 'terminated'; ?>"><?php echo htmlspecialchars($c->status); ?></span></td>
<td><?php echo htmlspecialchars($c->created_at); ?></td>
<td><?php echo $certExists ? htmlspecialchars($c->expires_at ?? 'N/A') : '<span style="color:#f87171">Missing</span>'; ?></td>
<td style="color:<?php echo !$certExists ? '#f87171' : ($daysLeft < 14 ? '#f87171' : ($daysLeft < 30 ? '#fbbf24' : '#4ade80')); ?>"><?php echo !$certExists ? 'No file' : $daysLeft . ' days'; ?></td>
<td style="display:flex;gap:4px">
<a href="/admin/ssl/universal/renew?domain=<?php echo urlencode($c->domain); ?>" class="btn btn-sm secondary" onclick="return confirm('Renew <?php echo htmlspecialchars($c->domain); ?>?')">Renew</a>
<?php if (!$certExists): ?>
<a href="/admin/ssl/universal/renew?domain=<?php echo urlencode($c->domain); ?>" class="btn btn-sm primary" style="background:rgba(248,113,113,.2);color:#f87171">Issue</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="6" style="text-align:center;padding:20px;color:#64748b">No SSL certificates installed yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:8px">💡 Tip</h3>
<p style="font-size:13px;color:#94a3b8;margin:0">Use the <a href="/admin/ssl/universal" style="color:#38bdf8">Universal SSL Manager</a> to auto-detect services, issue Let's Encrypt certificates, run health checks, and repair broken SSL — or use <strong>Fix All Missing Certs</strong> to repair every broken certificate in one click.</p>
</div>
</div>
