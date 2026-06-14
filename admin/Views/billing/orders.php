<h3 style="color:var(--accent);margin-bottom:16px">Orders</h3>
<table><tr><th>#</th><th>User ID</th><th>Product</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr>
<?php if (!empty($orders)): foreach ($orders as $o): ?>
<tr><td><?php echo $o->id; ?></td><td><?php echo $o->user_id; ?></td><td><?php echo $o->product_id ?? '-'; ?></td><td>$<?php echo number_format($o->total, 2); ?></td>
<td><span class="status-badge status-<?php echo $o->status === 'active' ? 'active' : ($o->status === 'cancelled' ? 'terminated' : ''); ?>"><?php echo $o->status; ?></span></td>
<td><?php echo $o->created_at; ?></td>
<td><form method="POST" action="/admin/billing/orders/update/<?php echo $o->id; ?>" style="display:flex;gap:4px">
<select name="status"><option value="pending" <?php echo $o->status==='pending'?'selected':''; ?>>Pending</option><option value="active" <?php echo $o->status==='active'?'selected':''; ?>>Active</option><option value="suspended" <?php echo $o->status==='suspended'?'selected':''; ?>>Suspended</option><option value="cancelled" <?php echo $o->status==='cancelled'?'selected':''; ?>>Cancelled</option></select>
<button type="submit" class="btn btn-sm primary">Update</button></form></td></tr>
<?php endforeach; else: ?><tr><td colspan="7" style="text-align:center;padding:20px;color:#64748b">No orders yet.</td></tr>
<?php endif; ?></table>
