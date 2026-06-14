<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Hostname</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($serverConfigStats['hostname'] ?? 'N/A'); ?></div></div>
<div class="stat-card"><h3>Kernel</h3><div class="value" style="font-size:14px"><?php echo htmlspecialchars($serverConfigStats['kernel_version'] ?? 'N/A'); ?></div></div>
<div class="stat-card"><h3>Architecture</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($serverConfigStats['architecture'] ?? 'N/A'); ?></div></div>
<div class="stat-card"><h3>Timezone</h3><div class="value" style="font-size:14px"><?php echo htmlspecialchars($serverConfigStats['timezone'] ?? 'N/A'); ?></div></div>
</div>

<div class="page-grid" style="margin-bottom:20px">
<a href="/admin/tweak" class="action-card"><div class="icon">⚙️</div><div class="name">Tweak Settings</div></a>
<a href="/admin/server" class="action-card"><div class="icon">🖥</div><div class="name">Server Overview</div></a>
<a href="/admin/apache" class="action-card"><div class="icon">🌐</div><div class="name">Apache Config</div></a>
<a href="/admin/php" class="action-card"><div class="icon">🐘</div><div class="name">PHP Config</div></a>
<a href="/admin/mysql" class="action-card"><div class="icon">🗄️</div><div class="name">MySQL Manager</div></a>
<a href="/admin/monitoring" class="action-card"><div class="icon">📊</div><div class="name">Monitoring</div></a>
</div>

<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">Service Management</h3>
<table><tr><th>Service</th><th>Status</th><th>Actions</th></tr>
<?php
$services = ['apache2', 'mariadb', 'postfix', 'dovecot', 'vsftpd', 'bind9', 'icecast2', 'ssh', 'docker'];
foreach ($services as $svc):
    $isRunning = trim(shell_exec("systemctl is-active $svc 2>/dev/null") ?: 'unknown');
?>
<tr>
<td><?php echo ucfirst($svc); ?></td>
<td><span class="status-badge status-<?php echo $isRunning === 'active' ? 'active' : 'terminated'; ?>"><?php echo $isRunning; ?></span></td>
<td style="display:flex;gap:4px">
<a href="/admin/service/restart/<?php echo $svc; ?>" class="btn btn-sm secondary">Restart</a>
<a href="/admin/service/start/<?php echo $svc; ?>" class="btn btn-sm primary">Start</a>
<a href="/admin/service/stop/<?php echo $svc; ?>" class="btn btn-sm danger">Stop</a>
</td></tr>
<?php endforeach; ?></table></div>
