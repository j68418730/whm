<div class="stats-grid">
<div class="stat-card"><h3>Total Stations</h3><div class="value"><?php echo $stats['total'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Active</h3><div class="value" style="color:#4ade80"><?php echo $stats['active'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Suspended</h3><div class="value" style="color:#facc15"><?php echo $stats['suspended'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Disk Usage</h3><div class="value" style="font-size:20px"><?php echo $diskUsed ?? 0; ?> / <?php echo $diskTotal ?? 10; ?> GB</div></div>
</div>

<?php if (!empty($notifications)): foreach ($notifications as $n): ?>
<div class="alert alert-<?php echo $n['type'] === 'error' ? 'error' : 'success'; ?>" style="margin-bottom:12px;<?php echo $n['type'] === 'warning' ? 'background:rgba(250,204,21,.08);border-color:rgba(250,204,21,.2);color:#facc15' : ''; ?>"><?php echo htmlspecialchars($n['msg']); ?></div>
<?php endforeach; endif; ?>

<div class="card"><h3 style="color:var(--accent)">Account Overview</h3>
<div style="display:grid;grid-template-columns:140px 1fr;gap:6px;margin-top:8px">
<div style="color:var(--text-muted);font-size:13px">Username</div><div><?php echo htmlspecialchars($hosting->username ?? '-'); ?></div>
<div style="color:var(--text-muted);font-size:13px">Domain</div><div><?php echo htmlspecialchars($hosting->domain ?? '-'); ?></div>
<div style="color:var(--text-muted);font-size:13px">Package</div><div><?php echo $package ? htmlspecialchars($package->name) : 'None'; ?></div>
<div style="color:var(--text-muted);font-size:13px">Status</div><div><span class="status-badge status-<?php echo ($hosting->status ?? 'active') === 'active' ? 'active' : 'suspended'; ?>"><?php echo ucfirst($hosting->status ?? 'active'); ?></span></div>
<div style="color:var(--text-muted);font-size:13px">PHP Version</div><div><?php echo $hosting->php_version ?: '8.2'; ?></div>
</div>
</div>

<div class="card"><h3 style="color:var(--accent)">Resource Usage</h3>
<div style="margin-top:8px">
<?php $diskPct = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100) : 0; ?>
<div style="margin-bottom:12px"><div style="display:flex;justify-content:space-between;font-size:13px"><span>Disk Storage</span><span><?php echo $diskUsed; ?> GB / <?php echo $diskTotal; ?> GB</span></div>
<div style="height:8px;background:rgba(255,255,255,.08);border-radius:4px;overflow:hidden;margin-top:4px"><div style="height:100%;width:<?php echo min(100, $diskPct); ?>%;background:<?php echo $diskPct > 90 ? '#f87171' : ($diskPct > 70 ? '#facc15' : '#4ade80'); ?>;border-radius:4px"></div></div>
</div>
<div><div style="display:flex;justify-content:space-between;font-size:13px"><span>Bandwidth</span><span>0 GB / <?php echo $package->bandwidth ?? 'N/A'; ?> GB</span></div>
<div style="height:8px;background:rgba(255,255,255,.08);border-radius:4px;overflow:hidden;margin-top:4px"><div style="height:100%;width:0%;background:#4ade80;border-radius:4px"></div></div>
</div>
</div>
</div>

<?php if (!empty($streams)): foreach ($streams as $s): ?>
<div class="card" style="padding:14px 20px"><div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
<div><strong><?php echo htmlspecialchars($s->server_type ?? 'Stream'); ?></strong> on port <?php echo $s->port; ?> <span class="status-badge status-<?php echo $s->status === 'running' ? 'active' : 'terminated'; ?>"><?php echo $s->status; ?></span></div>
<div style="display:flex;gap:6px"><a href="/user/start/<?php echo $s->id; ?>" class="btn btn-sm primary">▶ Start</a><a href="/user/stop/<?php echo $s->id; ?>" class="btn btn-sm danger">⏹ Stop</a></div>
</div></div>
<?php endforeach; endif; ?>
