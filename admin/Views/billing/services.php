<h3 style="color:var(--accent);margin-bottom:12px">Services</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:10px">
<?php if (!empty($services)): foreach ($services as $s): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="display:flex;justify-content:space-between;align-items:start">
<div><span style="font-weight:600;font-size:14px">#<?php echo $s->id; ?></span>
<span class="status-badge status-<?php echo $s->status === 'active' ? 'active' : 'terminated'; ?>" style="margin-left:6px;font-size:10px"><?php echo $s->status; ?></span></div>
<span style="font-size:11px;color:#64748b"><?php echo $s->domain ? htmlspecialchars($s->domain) : '-'; ?></span>
</div>
<div style="font-size:12px;color:#94a3b8;margin-top:4px">User: <?php echo $s->user_id; ?> · Product: <?php echo $s->product_id ?? '-'; ?></div>
<div style="font-size:11px;color:#64748b;margin-top:2px">Cycle: <?php echo $s->billing_cycle; ?> · $<?php echo number_format($s->price, 2); ?> · Next: <?php echo $s->next_due_date ?? '-'; ?></div>
<div style="margin-top:8px"><form method="POST" action="/admin/billing/services/update/<?php echo $s->id; ?>" style="display:flex;gap:4px;flex-wrap:wrap">
<select name="status" style="flex:1"><option value="active" <?php echo $s->status==='active'?'selected':''; ?>>Active</option><option value="suspended" <?php echo $s->status==='suspended'?'selected':''; ?>>Suspended</option><option value="terminated" <?php echo $s->status==='terminated'?'selected':''; ?>>Terminated</option></select>
<input name="next_due_date" type="date" value="<?php echo $s->next_due_date ?? ''; ?>" style="width:130px">
<button type="submit" class="btn btn-sm primary">Update</button></form></div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:20px;grid-column:1/-1;color:#64748b">No services yet.</div>
<?php endif; ?>
</div>