<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Hostname</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($hostname); ?></div></div>
<div class="stat-card"><h3>Server IP</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($serverIp); ?></div></div>
<div class="stat-card"><h3>OS</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($os); ?></div></div>
<div class="stat-card"><h3>Kernel</h3><div class="value" style="font-size:14px"><?php echo htmlspecialchars($kernel); ?></div></div>
</div>

<!-- Hostname -->
<div class="card" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/admin/serverconfig/hostname">
<h3 style="color:var(--accent);margin-bottom:8px">Hostname</h3>
<div class="form-group"><label>New Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($hostname); ?>" placeholder="server1.planet-hosts.com"></div>
<button type="submit" class="btn primary">Update Hostname</button>
</form>
</div>

<!-- Root Password -->
<div class="card" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/admin/serverconfig/rootpass">
<h3 style="color:var(--accent);margin-bottom:8px">Root Password</h3>
<div class="form-group"><label>New Password</label><input name="root_password" type="password" required></div>
<div class="form-group"><label>Apply To</label>
<select name="type"><option value="both">MySQL + System Root</option><option value="mysql">MySQL Only</option><option value="system">System Root Only</option></select></div>
<button type="submit" class="btn primary">Change Password</button>
</form>
</div>

<!-- Port Configuration -->
<div class="card" style="max-width:600px;margin-bottom:16px">
<form method="POST" action="/admin/serverconfig/ports">
<h3 style="color:var(--accent);margin-bottom:8px">Service Ports</h3>
<p style="color:var(--text-secondary);font-size:13px;margin-bottom:12px">Configure access ports like cPanel-style (2082, 2086, 2087, 2096). These create Apache vhosts on the specified ports.</p>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div><label>Admin Panel Port</label><input name="admin_port" value="2087"></div>
<div><label>Reseller Port</label><input name="reseller_port" value="2086"></div>
<div><label>User Portal Port</label><input name="user_port" value="2082"></div>
<div><label>Webmail Port</label><input name="webmail_port" value="2096"></div>
</div>
<div class="form-group" style="margin-top:8px"><label>Main Domain</label><input name="domain" value="planet-hosts.com" placeholder="planet-hosts.com"></div>
<button type="submit" class="btn primary">Configure Ports & Vhosts</button>
</form>
</div>

<!-- Service Controls -->
<div class="card"><h3 style="color:var(--accent);margin-bottom:12px)">Service Controls</h3>
<div style="display:flex;flex-wrap:wrap;gap:8px">
<?php foreach (['apache2','mariadb','postfix','dovecot','vsftpd','bind9','icecast2','ssh','firewalld','fail2ban'] as $svc):
$st = trim(shell_exec("systemctl is-active {$svc} 2>/dev/null") ?: 'unknown');
?>
<div style="padding:10px 14px;border:1px solid rgba(255,255,255,.06);border-radius:8px;text-align:center;min-width:120px">
<div style="font-size:12px;font-weight:600;margin-bottom:4px"><?php echo ucfirst($svc); ?></div>
<div style="font-size:11px;color:<?php echo $st === 'active' ? '#4ade80' : '#f87171'; ?>;margin-bottom:6px"><?php echo $st; ?></div>
<div style="display:flex;gap:4px;justify-content:center">
<a href="/admin/service/restart/<?php echo $svc; ?>" class="btn btn-sm secondary" style="font-size:10px">R</a>
<a href="/admin/service/start/<?php echo $svc; ?>" class="btn btn-sm primary" style="font-size:10px">▶</a>
<a href="/admin/service/stop/<?php echo $svc; ?>" class="btn btn-sm danger" style="font-size:10px">⏹</a>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
