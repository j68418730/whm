<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="card" style="margin-bottom:12px;padding:10px 14px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
<div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text_muted,#94a3b8)">
<i class="bi bi-building"></i> <b style="color:var(--text,#e0e0e0)"><?php echo htmlspecialchars($reseller->company_name); ?></b>
<span>·</span> <i class="bi bi-people"></i> <?php echo $totalAccounts ?? 0; ?> clients owned
</div>
<div style="display:flex;gap:6px;flex-wrap:wrap">
<a href="/reseller-clients" class="btn btn-sm btn-secondary" style="padding:5px 12px;font-size:12px"><i class="bi bi-person-plus"></i> Clients</a>
<a href="/reseller/packages" class="btn btn-sm btn-secondary" style="padding:5px 12px;font-size:12px"><i class="bi bi-box-seam"></i> Packages</a>
</div>
</div>
</div>

<?php if (isset($quotas['low']) && $quotas['low']): ?>
<?php
    $warn = [];
    if ($quotas['disk_low']) $warn[] = 'disk (' . number_format($quotas['disk_avail_gb'] ?? 0, 1) . ' GB left of ' . number_format($quotas['disk_total_gb'] ?? 0, 1) . ' GB)';
    if ($quotas['bw_low']) $warn[] = 'bandwidth (' . number_format($quotas['bw_avail_gb'] ?? 0, 0) . ' GB left of ' . number_format($quotas['bw_total_gb'] ?? 0, 0) . ' GB)';
?>
<div class="card" style="margin-bottom:12px;padding:14px 16px;border:1px solid #f87171;background:rgba(248,113,113,.08)">
<div style="display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap">
<div style="font-size:20px;line-height:1"><i class="bi bi-exclamation-triangle-fill" style="color:#f87171"></i></div>
<div style="flex:1;min-width:220px">
<div style="color:#f87171;font-weight:700;font-size:14px">Low Resource Warning — <?php echo $quotas['disk_pct'] >= $quotas['bw_pct'] ? 'Disk' : 'Bandwidth'; ?> Quota Reached</div>
<div style="color:#e2e8f0;font-size:13px;margin-top:4px">
You are running low on <?php echo htmlspecialchars(implode(' and ', $warn)); ?>.
<?php if (($quotas['disk_pct'] ?? 0) < 100 && ($quotas['bw_pct'] ?? 0) < 100): ?>
You have reached <b><?php echo $quotas['threshold']; ?>%</b> of your allocation. <b>You cannot create new clients or retail packages</b> until you upgrade your plan.
<?php else: ?>
Your allocation is <b>fully committed</b>. <b>You cannot create new clients or retail packages</b> until you upgrade your plan.
<?php endif; ?>
</div>
<div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap">
<a href="https://planet-hosts.com/store" target="_blank" rel="noopener" class="btn btn-sm btn-danger" style="padding:5px 14px;font-size:12px"><i class="bi bi-upc-scan"></i> Upgrade Plan</a>
<a href="/reseller/plan" class="btn btn-sm btn-secondary" style="padding:5px 14px;font-size:12px"><i class="bi bi-box-seam"></i> Review Packages</a>
</div>
</div>
</div>
</div>
<?php endif; ?>

<!-- Stats bar (mirrors admin stats_bar widget) -->
<div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr))">
<div class="stat-card"><h3>Clients</h3><div class="value"><?php echo $totalAccounts ?? 0; ?></div><div class="label"><?php echo $activeAccounts ?? 0; ?> active / <?php echo $suspendedAccounts ?? 0; ?> suspended</div></div>
<div class="stat-card"><h3>Services</h3><div class="value"><?php echo $activeServices ?? 0; ?></div><div class="label">Active services</div></div>
<div class="stat-card"><h3>Pending Orders</h3><div class="value" style="color:#facc15"><?php echo $pendingOrders ?? 0; ?></div><div class="label">Awaiting provisioning</div></div>
<div class="stat-card"><h3>Tickets</h3><div class="value" style="color:#38bdf8"><?php echo $openTickets ?? 0; ?></div><div class="label">Open client tickets</div></div>
<div class="stat-card"><h3>Revenue (Month)</h3><div class="value" style="color:#4ade80">$<?php echo number_format($revenueMonth ?? 0, 2); ?></div><div class="label">$<?php echo number_format($totalCollected ?? 0, 2); ?> collected</div></div>
</div>

<!-- Quota cards -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px">
<div class="card">
<h3 style="color:var(--text_muted,#64748b);font-size:12px;margin:0 0 10px"><i class="bi bi-hdd"></i> Disk Quota</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div class="stat-card"><div class="label">Total</div><div class="value" style="font-size:20px"><?php echo number_format($quotas['disk_total_gb'] ?? 0, 1); ?> GB</div><div class="label">Your allocation (<?php echo number_format($quotas['disk_total_gb'] ?? 0, 0); ?> GB)</div></div>
<div class="stat-card"><div class="label">Available</div><div class="value" style="font-size:20px;color:#4ade80"><?php echo number_format($quotas['disk_avail_gb'] ?? 0, 1); ?> GB</div><div class="label"><?php echo number_format($quotas['disk_sold_gb'] ?? 0, 1); ?> GB sold</div></div>
</div>
<div class="progress" style="height:8px;margin-top:10px"><div class="progress-bar" style="width:<?php echo $quotas['disk_pct'] ?? 0; ?>%;background:<?php echo ($quotas['disk_pct'] ?? 0) >= 90 ? '#f87171' : 'linear-gradient(90deg,#008cff,#3bb8ff)'; ?>"></div></div>
<p style="font-size:11px;color:#64748b;margin-top:6px"><?php echo $quotas['disk_pct'] ?? 0; ?>% of your disk committed to retail packages.</p>
</div>

<div class="card">
<h3 style="color:var(--text_muted,#64748b);font-size:12px;margin:0 0 10px"><i class="bi bi-arrow-left-right"></i> Bandwidth Quota</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div class="stat-card"><div class="label">Total</div><div class="value" style="font-size:20px"><?php echo number_format($quotas['bw_total_gb'] ?? 0, 0); ?> GB</div><div class="label">Your allocation</div></div>
<div class="stat-card"><div class="label">Available</div><div class="value" style="font-size:20px;color:#4ade80"><?php echo number_format($quotas['bw_avail_gb'] ?? 0, 0); ?> GB</div><div class="label"><?php echo number_format($quotas['bw_sold_gb'] ?? 0, 0); ?> GB sold</div></div>
</div>
<div class="progress" style="height:8px;margin-top:10px"><div class="progress-bar" style="width:<?php echo $quotas['bw_pct'] ?? 0; ?>%;background:<?php echo ($quotas['bw_pct'] ?? 0) >= 90 ? '#f87171' : 'linear-gradient(90deg,#008cff,#3bb8ff)'; ?>"></div></div>
<p style="font-size:11px;color:#64748b;margin-top:6px"><?php echo $quotas['bw_pct'] ?? 0; ?>% of your bandwidth committed to retail packages.</p>
</div>
</div>

<div class="card" style="margin-top:14px">
<div style="display:grid;grid-template-columns:1fr auto;align-items:center;gap:8px;padding:10px 14px">
<div style="font-size:12px;color:var(--text_muted,#94a3b8)"><i class="bi bi-server"></i> <b style="color:var(--text,#e0e0e0)">Planet Hosts Infrastructure</b></div>
<div style="font-size:11px;color:#64748b">Shared platform services</div>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:6px;padding:6px 14px 14px">
<?php foreach ($services as $svc): ?>
<div style="display:flex;align-items:center;gap:6px;padding:4px 8px;border-radius:4px;font-size:12px;background:rgba(255,255,255,.02)">
<span style="width:8px;height:8px;border-radius:50%;background:<?php echo $svc['active'] ? '#4ade80' : '#64748b'; ?>;flex-shrink:0"></span>
<span style="color:<?php echo $svc['active'] ? '#e0e0e0' : '#64748b'; ?>"><?php echo htmlspecialchars($svc['name']); ?></span>
</div>
<?php endforeach; ?>
</div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-top:14px">
<div>
<div class="card" style="margin-bottom:14px">
<h3 style="color:var(--text_muted,#64748b);font-size:12px;margin:0 0 10px">Recent Activity</h3>
<?php if (!empty($recentOrders)): ?>
<div style="font-size:12px;color:#64748b;margin-bottom:6px">Recent Orders</div>
<?php foreach ($recentOrders as $ord): ?>
<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px">
<span>#<?php echo (int)$ord->id; ?> — <?php echo htmlspecialchars($ord->client ?? ''); ?> — $<?php echo number_format($ord->total ?? 0, 2); ?></span>
<span style="color:#64748b;font-size:11px"><?php echo htmlspecialchars($ord->status ?? ''); ?></span>
</div>
<?php endforeach; endif; ?>
<?php if (!empty($recentAccounts)): ?>
<div style="font-size:12px;color:#64748b;margin-top:10px;margin-bottom:6px">New Clients</div>
<?php foreach ($recentAccounts as $a): ?>
<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px">
<span><?php echo htmlspecialchars($a->username ?? $a->email ?? ''); ?></span>
<span style="color:#64748b;font-size:11px"><?php echo htmlspecialchars($a->status ?? ''); ?></span>
</div>
<?php endforeach; endif; ?>
<?php if (!empty($recentTickets)): ?>
<div style="font-size:12px;color:#64748b;margin-top:10px;margin-bottom:6px">Client Tickets</div>
<?php foreach ($recentTickets as $t): ?>
<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px">
<span>#<?php echo (int)$t->id; ?> — <?php echo htmlspecialchars(substr($t->subject ?? '', 0, 40)); ?></span>
<span style="color:#64748b;font-size:11px"><?php echo htmlspecialchars($t->status ?? ''); ?></span>
</div>
<?php endforeach; endif; ?>
<?php if (empty($recentOrders) && empty($recentAccounts) && empty($recentTickets)): ?>
<p style="color:#64748b;font-size:13px">No recent activity yet.</p>
<?php endif; ?>
</div>
</div>

<div>
<div class="card" style="margin-bottom:14px">
<h3 style="color:var(--text_muted,#64748b);font-size:12px;margin:0 0 10px">Revenue Overview</h3>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr">
<div class="stat-card"><div class="label">Total Collected</div><div class="value" style="font-size:20px">$<?php echo number_format($totalCollected ?? 0, 2); ?></div></div>
<div class="stat-card"><div class="label">This Month</div><div class="value" style="font-size:20px">$<?php echo number_format($revenueMonth ?? 0, 2); ?></div></div>
<div class="stat-card"><div class="label">Outstanding</div><div class="value" style="font-size:20px;color:#f87171">$<?php echo number_format($outstanding ?? 0, 2); ?></div></div>
</div>
</div>

<div class="card">
<h3 style="color:var(--text_muted,#64748b);font-size:12px;margin:0 0 10px">Quick Actions</h3>
<div style="display:grid;grid-template-columns:1fr;gap:6px">
<a href="/reseller-clients" class="btn btn-secondary btn-sm" style="display:flex;align-items:center;gap:8px;padding:10px;border-radius:8px;justify-content:flex-start"><i class="bi bi-people"></i> View Clients</a>
<a href="/reseller/packages" class="btn btn-secondary btn-sm" style="display:flex;align-items:center;gap:8px;padding:10px;border-radius:8px;justify-content:flex-start"><i class="bi bi-box-seam"></i> Packages</a>
<?php if (!empty($addons['billing'])): ?>
<a href="/reseller/billing-system" class="btn btn-secondary btn-sm" style="display:flex;align-items:center;gap:8px;padding:10px;border-radius:8px;justify-content:flex-start"><i class="bi bi-credit-card"></i> Billing System</a>
<?php endif; ?>
<?php if (!empty($addons['chat'])): ?>
<a href="/reseller/chat-system" class="btn btn-secondary btn-sm" style="display:flex;align-items:center;gap:8px;padding:10px;border-radius:8px;justify-content:flex-start"><i class="bi bi-chat-dots"></i> Chat System</a>
<?php endif; ?>
<?php if (!empty($addons['support'])): ?>
<a href="/reseller/support-system" class="btn btn-secondary btn-sm" style="display:flex;align-items:center;gap:8px;padding:10px;border-radius:8px;justify-content:flex-start"><i class="bi bi-headset"></i> Support System</a>
<?php endif; ?>
<a href="/reseller/provisioning" class="btn btn-secondary btn-sm" style="display:flex;align-items:center;gap:8px;padding:10px;border-radius:8px;justify-content:flex-start"><i class="bi bi-hdd-network"></i> Provisioning</a>
<a href="/reseller/branding" class="btn btn-secondary btn-sm" style="display:flex;align-items:center;gap:8px;padding:10px;border-radius:8px;justify-content:flex-start"><i class="bi bi-palette"></i> Branding</a>
</div>
</div>
</div>
</div>