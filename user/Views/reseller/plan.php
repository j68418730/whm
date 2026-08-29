<div class="card" style="margin-bottom:12px;padding:14px 16px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
<div style="font-size:13px;color:var(--text_muted,#94a3b8)">
<i class="bi bi-box-seam"></i> <b style="color:var(--text,#e0e0e0)">My Reseller Plan</b>
<span>·</span> <?php echo htmlspecialchars($reseller->company_name); ?>
</div>
<a href="https://planet-hosts.com/store" target="_blank" rel="noopener" class="btn btn-sm btn-primary" style="padding:6px 16px;font-size:12px"><i class="bi bi-upc-scan"></i> Upgrade / Change Plan</a>
</div>
</div>

<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<?php if ($plan): ?>
<?php
    $typeLabels = ['web_reseller' => 'Web Reseller', 'icecast_reseller' => 'Radio / Icecast Reseller', 'shoutcast_reseller' => 'SHOUTcast Reseller'];
    $planType = $plan->type ?? '';
?>
<div class="card" style="margin-bottom:14px">
<h3 style="color:var(--accent);margin:0 0 12px;font-size:15px">Current Plan</h3>
<div style="display:flex;flex-wrap:wrap;gap:18px;align-items:flex-end;margin-bottom:12px">
<div>
<div style="font-size:26px;font-weight:800;color:var(--text,#e0e0e0)"><?php echo htmlspecialchars($plan->name ?? 'Reseller'); ?></div>
<div style="font-size:12px;color:var(--text_muted,#94a3b8)"><?php echo htmlspecialchars($typeLabels[$planType] ?? ucfirst($planType)); ?></div>
</div>
<div style="font-size:20px;font-weight:700;color:#4ade80">$<?php echo number_format((float)($plan->monthly_price ?? 0), 2); ?><small style="font-size:12px;color:var(--text_muted)">/mo</small></div>
</div>
<?php if (!empty($plan->description)): ?>
<p style="font-size:13px;color:var(--text_muted,#94a3b8);margin-bottom:12px"><?php echo htmlspecialchars($plan->description); ?></p>
<?php endif; ?>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px">
<div class="stat-card"><div class="label">Clients</div><div class="value"><?php echo (int)($reseller->customers_limit ?? 500); ?></div><div class="label"><?php echo $clientCount; ?> currently used</div></div>
<div class="stat-card"><div class="label">Hosting Accounts</div><div class="value"><?php echo (int)($reseller->hosting_limit ?? 500); ?></div><div class="label"><?php echo (int)$plan->disk_space ?? 0; ?> GB disk in plan</div></div>
</div>
</div>
<?php else: ?>
<div class="alert alert-warning">No reseller plan package is assigned yet. Contact Planet Hosts or visit the store to choose a plan.</div>
<?php endif; ?>

<!-- Quota / resource allocation -->
<div class="card" style="margin-bottom:14px">
<h3 style="color:var(--accent);margin:0 0 12px;font-size:15px">Resource Allocation</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
<div>
<h4 style="color:var(--text_muted,#64748b);font-size:12px;margin:0 0 10px"><i class="bi bi-hdd"></i> Disk</h4>
<div class="progress" style="height:8px;margin-bottom:6px"><div class="progress-bar" style="width:<?php echo $quotas['disk_pct']; ?>%;background:<?php echo $quotas['disk_pct'] >= 90 ? '#f87171' : 'linear-gradient(90deg,#008cff,#3bb8ff)'; ?>"></div></div>
<p style="font-size:12px;color:var(--text_muted,#94a3b8);margin:0"><?php echo number_format($quotas['disk_sold_gb'],1); ?> of <?php echo number_format($quotas['disk_total_gb'],1); ?> GB sold (<?php echo $quotas['disk_pct']; ?>%) — <?php echo number_format($quotas['disk_avail_gb'],1); ?> GB left</p>
</div>
<div>
<h4 style="color:var(--text_muted,#64748b);font-size:12px;margin:0 0 10px"><i class="bi bi-arrow-left-right"></i> Bandwidth</h4>
<div class="progress" style="height:8px;margin-bottom:6px"><div class="progress-bar" style="width:<?php echo $quotas['bw_pct']; ?>%;background:<?php echo $quotas['bw_pct'] >= 90 ? '#f87171' : 'linear-gradient(90deg,#008cff,#3bb8ff)'; ?>"></div></div>
<p style="font-size:12px;color:var(--text_muted,#94a3b8);margin:0"><?php echo number_format($quotas['bw_sold_gb'],0); ?> of <?php echo number_format($quotas['bw_total_gb'],0); ?> GB sold (<?php echo $quotas['bw_pct']; ?>%) — <?php echo number_format($quotas['bw_avail_gb'],0); ?> GB left</p>
</div>
</div>
</div>

<!-- Retail packages they currently sell (summary) -->
<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
<h3 style="color:var(--accent);margin:0;font-size:15px">Retail Packages You Sell</h3>
<span style="font-size:12px;color:var(--text_muted,#94a3b8)"><?php echo $retailCount; ?> package<?php echo $retailCount !== 1 ? 's' : ''; ?></span>
</div>
<div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap">
<a href="/reseller/packages" class="btn btn-sm btn-secondary" style="padding:6px 14px;font-size:12px"><i class="bi bi-box-seam"></i> Manage Retail Packages</a>
</div>
</div>
