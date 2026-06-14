<div class="stats-grid">
<div class="stat-card" style="grid-column:1/-1;text-align:left">
<h3>System Information</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:12px">
<div><strong style="color:var(--text-secondary);font-size:12px;text-transform:uppercase">Hostname</strong><br><?php echo htmlspecialchars($serverStats['hostname'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
<div><strong style="color:var(--text-secondary);font-size:12px;text-transform:uppercase">OS</strong><br><?php echo htmlspecialchars($serverStats['os'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
<div><strong style="color:var(--text-secondary);font-size:12px;text-transform:uppercase">Kernel</strong><br><?php echo htmlspecialchars($serverStats['kernel'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
<div><strong style="color:var(--text-secondary);font-size:12px;text-transform:uppercase">CPU</strong><br><?php echo htmlspecialchars($serverStats['cpu_model'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
<div><strong style="color:var(--text-secondary);font-size:12px;text-transform:uppercase">Uptime</strong><br><?php echo $serverStats['uptime'] ?? 'N/A'; ?></div>
</div>
</div>
<div class="stat-card"><h3>CPU Load</h3><div class="value"><?php echo $serverStats['cpu_load']; ?>%</div><div class="label">Load: <?php echo $serverStats['load_average']['1min'] ?? '?'; ?> / <?php echo $serverStats['load_average']['5min'] ?? '?'; ?> / <?php echo $serverStats['load_average']['15min'] ?? '?'; ?></div></div>
<div class="stat-card"><h3>RAM Usage</h3><div class="value"><?php echo $serverStats['ram_usage']; ?>%</div><div class="label"><?php echo $serverStats['ram_total']; ?> GB total</div></div>
<div class="stat-card"><h3>Disk Usage</h3><div class="value"><?php echo $serverStats['disk_usage']; ?>%</div><div class="label"><?php echo $serverStats['disk_total']; ?> total</div></div>
<div class="stat-card"><h3>Active Accounts</h3><div class="value"><?php echo $serverStats['active_accounts']; ?></div><div class="label">Hosting accounts</div></div>
</div>
<div class="card">
<h3 style="color:var(--accent);margin-bottom:16px">Service Status</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:8px">
<?php foreach ($serverStats['service_status'] as $svc => $st): ?>
<div style="display:flex;justify-content:space-between;padding:8px 12px;background:rgba(255,255,255,.02);border-radius:6px">
<span style="font-size:14px"><?php echo htmlspecialchars(ucfirst($svc), ENT_QUOTES, 'UTF-8'); ?></span>
<span style="font-size:12px;padding:2px 10px;border-radius:4px;<?php echo $st === 'active' ? 'background:#1a3a2a;color:#4ade80' : 'background:#3a1a1a;color:#f87171'; ?>"><?php echo $st; ?></span>
</div>
<?php endforeach; ?>
</div>
</div>
