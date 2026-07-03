<div class="card" style="padding:20px">
<h3 style="margin-bottom:12px">Orders</h3>
<?php if (empty($orders)): ?>
<p style="color:#64748b">No orders yet.</p>
<?php else: ?>
<div style="overflow-x:auto">
<table style="width:100%;border-collapse:collapse;font-size:12px">
<thead><tr style="background:rgba(0,0,0,.2);text-transform:uppercase;font-size:10px;color:#64748b">
<th style="padding:8px 10px;text-align:left">#</th><th style="padding:8px 10px;text-align:left">Customer</th><th style="padding:8px 10px;text-align:right">Total</th><th style="padding:8px 10px;text-align:center">Status</th><th style="padding:8px 10px;text-align:center">Payment</th><th style="padding:8px 10px;text-align:left">Date</th><th style="padding:8px 10px;text-align:center">Actions</th>
</tr></thead>
<tbody>
<?php foreach ($orders as $o): ?>
<tr style="border-top:1px solid rgba(255,255,255,.04)">
<td style="padding:8px 10px;font-weight:600"><?php echo $o->id; ?></td>
<td style="padding:8px 10px"><?php echo htmlspecialchars($o->first_name . ' ' . $o->last_name); ?><br><span style="font-size:10px;color:#64748b"><?php echo htmlspecialchars($o->email); ?></span></td>
<td style="padding:8px 10px;text-align:right;font-weight:600">$<?php echo number_format($o->total,2); ?></td>
<td style="padding:8px 10px;text-align:center"><span class="badge" style="background:<?php echo $o->status === 'completed' ? '#4ade80' : ($o->status === 'pending' ? '#fbbf24' : ($o->status === 'cancelled' ? '#f87171' : '#64748b')); ?>"><?php echo $o->status; ?></span></td>
<td style="padding:8px 10px;text-align:center;font-size:11px"><?php echo $o->payment_status; ?></td>
<td style="padding:8px 10px;font-size:11px;color:#64748b"><?php echo date("M j, Y", strtotime($o->created_at)); ?></td>
<td style="padding:8px 10px;text-align:center"><a href="/admin/store/orders/<?php echo $o->id; ?>" class="btn btn-sm secondary" style="font-size:9px;padding:3px 8px">View</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
