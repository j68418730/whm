<h3 style="color:var(--accent);margin-bottom:12px">Refunds</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:10px">
<?php if (!empty($refunds)): foreach ($refunds as $r): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="display:flex;justify-content:space-between">
<div><span style="font-weight:600;font-size:14px">#<?php echo $r->id; ?> · <?php echo htmlspecialchars($r->username ?? "User #{$r->user_id}"); ?></span></div>
<span style="font-size:16px;font-weight:700;color:#f87171">-$<?php echo number_format($r->amount, 2); ?></span>
</div>
<div style="font-size:11px;color:#64748b;margin-top:4px">Reason: <?php echo htmlspecialchars($r->reason ?: '-'); ?></div>
<div style="font-size:11px;color:#64748b">Payment: <?php echo $r->payment_id ?? '-'; ?> · Invoice: <?php echo $r->invoice_id ?? '-'; ?></div>
<div style="font-size:10px;color:#64748b;margin-top:2px"><?php echo $r->created_at; ?></div>
<div style="margin-top:6px"><a href="/admin/billing/refunds/delete/<?php echo $r->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete refund record?')">Delete</a></div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:20px;grid-column:1/-1;color:#64748b">No refunds processed.</div>
<?php endif; ?>
</div>