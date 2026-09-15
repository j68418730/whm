<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="page-header">
<div class="d-flex justify-content-between align-items-center">
<h2 style="margin:0; color:var(--accent, #008cff)">🖥️ Server Overview</h2>
<a href="/admin/dashboard" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i> Dashboard</a>
</div>
</div>

<div class="stats-grid">
<div class="stat-card"><h3>Hostname</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($serverStats['hostname'] ?? 'N/A'); ?></div></div>
<div class="stat-card"><h3>Server IP</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($serverStats['public_ip'] ?? 'N/A'); ?></div></div>
<div class="stat-card"><h3>Uptime</h3><div class="value" style="font-size:14px"><?php echo htmlspecialchars(trim(shell_exec('uptime -p 2>/dev/null') ?: 'N/A')); ?></div></div>
<div class="stat-card"><h3>Load</h3><div class="value" style="font-size:14px"><?php echo htmlspecialchars(trim(shell_exec('cat /proc/loadavg 2>/dev/null | awk "{print \$1\" / \"\$2\" / \"\$3}"') ?: 'N/A')); ?></div></div>
<div class="stat-card"><h3>RAM</h3><div class="value" style="font-size:14px"><?php echo htmlspecialchars(trim(shell_exec('free -h 2>/dev/null | grep Mem | awk "{print \$3\" / \"\$2}"') ?: 'N/A')); ?></div></div>
<div class="stat-card"><h3>Disk</h3><div class="value" style="font-size:14px"><?php echo htmlspecialchars(trim(shell_exec('df -h / 2>/dev/null | tail -1 | awk "{print \$3\" / \"\$2}"') ?: 'N/A')); ?></div></div>
</div>
</div>

<div class="table-responsive">
<table class="table table-bordered align-middle mb-0">
<thead><tr><th>Service</th><th>Status</th></tr></thead>
<tbody>
<?php
$serviceNames = ['apache2' => 'Apache', 'mariadb' => 'MariaDB', 'icecast2' => 'Icecast', 'postfix' => 'Postfix', 'dovecot' => 'Dovecot', 'named' => 'DNS', 'vsftpd' => 'FTP', 'firewalld' => 'Firewall'];
foreach ($serviceNames as $sName => $sLabel) {
    $active = trim(shell_exec("systemctl is-active {$sName} 2>/dev/null") ?: '') === 'active';
    echo '<tr><td>' . $sLabel . '</td><td><span class="status-badge status-' . ($active ? 'active' : 'terminated') . '">' . ($active ? 'active' : 'inactive') . '</span></td></tr>';
}
?>
</tbody>
</table>
</div>

<div class="grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px">
<div class="card">
<h3 style="color:var(--accent, #008cff);margin-bottom:16px">System Resources</h3>
<p>Hostname: <?php echo htmlspecialchars($serverStats['hostname'] ?? 'N/A'); ?></p>
<p>IP: <?php echo htmlspecialchars($serverStats['public_ip'] ?? 'N/A'); ?></p>
<p>Uptime: <?php echo htmlspecialchars(trim(shell_exec('uptime -p 2>/dev/null') ?: 'N/A')); ?></p>
</div>
<div class="card">
<h3 style="color:var(--accent, #008cff);margin-bottom:16px">Streaming Engines</h3>
<p>SHOUTcast v2: <?php echo file_exists('/opt/planethosts/shoutcast/sc_serv') ? 'Installed' : 'Not Installed'; ?></p>
<p>SHOUTcast v1: <?php echo file_exists('/opt/planethosts/shoutcast1/sc_serv') ? 'Installed' : 'Not Installed'; ?></p>
<p>Icecast: <?php echo trim(shell_exec('systemctl is-active icecast2 2>/dev/null') ?: '') === 'active' ? 'Running' : 'Not Running'; ?></p>
</div>
</div>
</div>
</div>