<h3 style="color:var(--accent);margin-bottom:12px">Orders</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:10px">
<?php if (!empty($orders)): foreach ($orders as $o): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="display:flex;justify-content:space-between;align-items:start">
<div><span style="font-weight:600;font-size:14px">#<?php echo $o->id; ?></span>
<span class="status-badge status-<?php echo $o->status === 'active' ? 'active' : ($o->status === 'cancelled' ? 'terminated' : ''); ?>" style="margin-left:6px;font-size:10px"><?php echo $o->status; ?></span></div>
<span style="font-size:11px;color:#64748b"><?php echo $o->created_at; ?></span>
</div>
<div style="font-size:12px;color:#94a3b8;margin-top:4px">User: <?php echo $o->user_id; ?> · Product: <?php echo $o->product_id ?? '-'; ?></div>
<div style="font-size:14px;font-weight:600;margin-top:6px">$<?php echo number_format($o->total, 2); ?></div>
<div style="margin-top:8px"><form method="POST" action="/admin/billing/orders/update/<?php echo $o->id; ?>" style="display:flex;gap:4px">
<select name="status" style="flex:1"><option value="pending" <?php echo $o->status==='pending'?'selected':''; ?>>Pending</option><option value="active" <?php echo $o->status==='active'?'selected':''; ?>>Active</option><option value="suspended" <?php echo $o->status==='suspended'?'selected':''; ?>>Suspended</option><option value="cancelled" <?php echo $o->status==='cancelled'?'selected':''; ?>>Cancelled</option></select>
<button type="submit" class="btn btn-sm primary">Update</button></form></div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:20px;grid-column:1/-1;color:#64748b">No orders yet.</div>
<?php endif; ?>
</div>