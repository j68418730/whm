<div style="max-width:800px;margin:0 auto">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
<h3 style="margin:0"><i class="bi bi-receipt" style="color:var(--accent)"></i> My Orders</h3>
<a href="/store" class="btn btn-sm secondary"><i class="bi bi-arrow-left"></i> Back to Store</a>
</div>
<?php if (empty($orders)): ?>
<div class="card" style="padding:40px;text-align:center">
<div style="font-size:48px;color:rgba(255,255,255,.1);margin-bottom:12px"><i class="bi bi-receipt-cutoff"></i></div>
<p style="color:#64748b">No orders yet.</p>
<a href="/store" class="btn primary" style="margin-top:12px">Start Shopping</a>
</div>
<?php else: ?>
<div class="card" style="padding:0;overflow:hidden">
<table style="width:100%;border-collapse:collapse">
<thead><tr style="background:rgba(0,0,0,.2);font-size:11px;text-transform:uppercase;color:#64748b">
<th style="padding:10px 12px;text-align:left">Order</th><th style="padding:10px 12px;text-align:left">Date</th><th style="padding:10px 12px;text-align:left">Total</th><th style="padding:10px 12px;text-align:left">Status</th><th style="padding:10px 12px;text-align:left">Payment</th>
</tr></thead>
<tbody>
<?php foreach ($orders as $o): ?>
<tr style="border-top:1px solid rgba(255,255,255,.04)">
<td style="padding:10px 12px"><a href="/store/orders/<?php echo $o->id; ?>" style="color:var(--accent)">#<?php echo $o->id; ?></a></td>
<td style="padding:10px 12px;font-size:12px"><?php echo date("M j, Y", strtotime($o->created_at)); ?></td>
<td style="padding:10px 12px;font-size:13px;font-weight:600">$<?php echo number_format($o->total,2); ?></td>
<td style="padding:10px 12px"><span class="badge" style="background:<?php echo $o->status === 'completed' ? '#4ade80' : ($o->status === 'pending' ? '#fbbf24' : ($o->status === 'cancelled' ? '#f87171' : '#64748b')); ?>"><?php echo $o->status; ?></span></td>
<td style="padding:10px 12px;font-size:11px"><?php echo $o->payment_status; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
