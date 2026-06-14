<div class="stats-grid">
<div class="stat-card"><h3>Apache Version</h3><div class="value" style="font-size:18px"><?php echo htmlspecialchars($apacheStats['apache_version'] ?? 'N/A'); ?></div></div>
<div class="stat-card"><h3>PHP Version</h3><div class="value" style="font-size:18px"><?php echo $apacheStats['php_version'] ?? 'N/A'; ?></div></div>
<div class="stat-card"><h3>Modules</h3><div class="value"><?php echo $apacheStats['enabled_modules'] ?? 0; ?></div></div>
<div class="stat-card"><h3>VHosts</h3><div class="value"><?php echo $apacheStats['total_vhosts'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Service</h3><div class="value"><span class="status-badge status-<?php echo ($serviceStatus ?? 'unknown') === 'active' ? 'active' : 'terminated'; ?>"><?php echo $serviceStatus ?? 'unknown'; ?></span></div></div>
</div>
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px">
<a href="/admin/apache/restart" class="btn secondary">🔄 Restart</a>
<a href="/admin/apache/stop" class="btn danger">⏹ Stop</a>
<a href="/admin/apache/start" class="btn primary">▶ Start</a>
</div>
<div class="card"><h3 style="color:var(--accent)">Server Information</h3>
<p style="color:var(--text-secondary);margin-top:8px">Apache is the web server that serves your hosting panel and client websites. Use the controls above to manage the service.</p>
</div>
