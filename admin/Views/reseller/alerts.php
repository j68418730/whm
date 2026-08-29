<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
<h3 style="margin:0"><i class="bi bi-bell"></i> Reseller Alerts</h3>
<a href="/admin/reseller" class="btn btn-sm secondary"><i class="bi bi-arrow-left"></i> Back to Resellers</a>
</div>

<div style="font-size:12px;color:#64748b;margin-bottom:14px">
Unified alert feed across all resellers — due/past-due invoices, new clients, new orders, low-quota warnings, and support messages.
</div>

<?php if (empty($items)): ?>
<div class="card" style="padding:30px;text-align:center">
<div style="font-size:34px;margin-bottom:10px">🔔</div>
<div style="color:#64748b;font-size:14px">No alerts right now.</div>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:10px">
<?php foreach ($items as $it): ?>
<div class="card" style="margin:0;padding:14px 16px;display:flex;align-items:flex-start;gap:12px;<?php echo !empty($it['unread']) ? 'border-left:3px solid ' . $it['color'] . ';' : ''; ?>">
<div style="font-size:20px;line-height:1"><?php echo $it['icon']; ?></div>
<div style="flex:1;min-width:0">
<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
<span style="color:<?php echo $it['color']; ?>;font-weight:700;font-size:13px"><?php echo htmlspecialchars($it['title']); ?></span>
<?php if (!empty($it['unread'])): ?><span style="font-size:9px;background:rgba(56,189,248,.15);color:#38bdf8;padding:1px 7px;border-radius:99px">NEW</span><?php endif; ?>
</div>
<?php if (!empty($it['detail'])): ?><div style="color:#64748b;font-size:13px;margin-top:3px"><?php echo $it['detail']; ?></div><?php endif; ?>
<div style="display:flex;align-items:center;gap:12px;margin-top:8px;flex-wrap:wrap">
<?php if (!empty($it['time'])): ?><span style="font-size:11px;color:#94a3b8"><?php echo date('M j, Y g:i A', strtotime($it['time'])); ?></span><?php endif; ?>
<?php if (!empty($it['link'])): ?><a href="<?php echo $it['link']; ?>" style="font-size:12px;color:var(--accent,#008cff);text-decoration:none">View →</a><?php endif; ?>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
