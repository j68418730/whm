<div class="card" style="padding:20px">
<h3 style="margin-bottom:12px"><i class="bi bi-robot" style="color:#8b5cf6"></i> AI Builder Overview</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:14px;margin-bottom:16px">
<div class="card" style="padding:14px;background:rgba(139,92,246,.06);border:1px solid rgba(139,92,246,.1)"><strong style="font-size:24px"><?php echo $stats['total_sites'] ?? 0; ?></strong><br><span style="font-size:11px;color:#94a3b8">Total Sites</span></div>
<div class="card" style="padding:14px;background:rgba(10,132,255,.06);border:1px solid rgba(10,132,255,.1)"><strong style="font-size:24px"><?php echo $stats['ai_sites'] ?? 0; ?></strong><br><span style="font-size:11px;color:#94a3b8">AI-Generated</span></div>
<div class="card" style="padding:14px;background:rgba(48,209,88,.06);border:1px solid rgba(48,209,88,.1)"><strong style="font-size:24px"><?php echo $stats['total_requests'] ?? 0; ?></strong><br><span style="font-size:11px;color:#94a3b8">Total AI Requests</span></div>
<div class="card" style="padding:14px;background:rgba(255,159,10,.06);border:1px solid rgba(255,159,10,.1)"><strong style="font-size:24px"><?php echo $stats['active_memory'] ?? 0; ?></strong><br><span style="font-size:11px;color:#94a3b8">Active Memory Records</span></div>
</div>
<?php if (!empty($activity)): ?>
<h4 style="font-size:13px;margin-bottom:8px">Recent AI Activity</h4>
<div style="font-size:11px">
<?php foreach ($activity as $a): ?>
<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04)">
<span><?php echo htmlspecialchars($a->action); ?> — <?php echo htmlspecialchars($a->site_name ?? ''); ?></span>
<span style="color:#64748b"><?php echo $a->created_at; ?></span>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
