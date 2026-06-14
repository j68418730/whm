<h3 style="color:var(--accent);margin-bottom:16px">Services</h3>
<table><tr><th>#</th><th>User ID</th><th>Product</th><th>Domain</th><th>Status</th><th>Cycle</th><th>Price</th><th>Next Due</th><th></th></tr>
<?php if (!empty($services)): foreach ($services as $s): ?>
<tr><td><?php echo $s->id; ?></td><td><?php echo $s->user_id; ?></td><td><?php echo $s->product_id ?? '-'; ?></td><td><?php echo htmlspecialchars($s->domain ?: '-'); ?></td>
<td><span class="status-badge status-<?php echo $s->status === 'active' ? 'active' : 'terminated'; ?>"><?php echo $s->status; ?></span></td>
<td><?php echo $s->billing_cycle; ?></td><td>$<?php echo number_format($s->price, 2); ?></td><td><?php echo $s->next_due_date ?? '-'; ?></td>
<td><form method="POST" action="/admin/billing/services/update/<?php echo $s->id; ?>" style="display:flex;gap:4px;flex-wrap:wrap">
<select name="status"><option value="active" <?php echo $s->status==='active'?'selected':''; ?>>Active</option><option value="suspended" <?php echo $s->status==='suspended'?'selected':''; ?>>Suspended</option><option value="terminated" <?php echo $s->status==='terminated'?'selected':''; ?>>Terminated</option></select>
<input name="next_due_date" type="date" value="<?php echo $s->next_due_date ?? ''; ?>" style="width:130px">
<button type="submit" class="btn btn-sm primary">Update</button></form></td></tr>
<?php endforeach; else: ?><tr><td colspan="9" style="text-align:center;padding:20px;color:#64748b">No services yet.</td></tr>
<?php endif; ?></table>
