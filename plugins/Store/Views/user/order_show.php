<div style="max-width:700px;margin:0 auto">
<a href="/store/orders" class="btn btn-sm secondary" style="margin-bottom:12px"><i class="bi bi-arrow-left"></i> Back to Orders</a>
<div class="card" style="padding:24px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px">
<div><h3 style="margin:0">Order #<?php echo $order->id; ?></h3>
<span style="font-size:11px;color:#64748b">Placed <?php echo date("F j, Y g:i A", strtotime($order->created_at)); ?></span></div>
<div><span class="badge" style="background:<?php echo $order->status === 'completed' ? '#4ade80' : ($order->status === 'pending' ? '#fbbf24' : ($order->status === 'cancelled' ? '#f87171' : '#64748b')); ?>;padding:4px 12px"><?php echo $order->status; ?></span></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
<div><strong style="font-size:12px">Payment</strong><br><span style="font-size:11px;color:#94a3b8"><?php echo $order->payment_status; ?></span></div>
<div><strong style="font-size:12px">Total</strong><br><span style="font-size:20px;font-weight:700;color:var(--accent)">$<?php echo number_format($order->total,2); ?></span></div>
</div>
<?php if ($order->address_line1): ?>
<div style="margin-bottom:16px;font-size:12px;color:#94a3b8">
<strong style="color:#e0e0e0">Shipping Address</strong><br>
<?php echo htmlspecialchars($order->address_line1); ?><?php if ($order->address_line2): ?>, <?php echo htmlspecialchars($order->address_line2); endif; ?><br>
<?php echo htmlspecialchars($order->city); ?>, <?php echo htmlspecialchars($order->state); ?> <?php echo htmlspecialchars($order->zip); ?>
</div>
<?php endif; ?>
<h4 style="font-size:13px;margin-bottom:8px">Items</h4>
<div style="background:rgba(0,0,0,.15);border-radius:8px;overflow:hidden">
<table style="width:100%;border-collapse:collapse">
<thead><tr style="font-size:10px;text-transform:uppercase;color:#64748b;background:rgba(0,0,0,.2)">
<th style="padding:8px 12px;text-align:left">Item</th><th style="padding:8px 12px;text-align:center">Qty</th><th style="padding:8px 12px;text-align:right">Price</th><th style="padding:8px 12px;text-align:right">Total</th>
</tr></thead>
<tbody>
<?php foreach ($items as $item): ?>
<tr style="border-top:1px solid rgba(255,255,255,.04)">
<td style="padding:8px 12px;font-size:12px"><?php echo htmlspecialchars($item->product_name); ?></td>
<td style="padding:8px 12px;text-align:center;font-size:12px"><?php echo $item->qty; ?></td>
<td style="padding:8px 12px;text-align:right;font-size:12px">$<?php echo number_format($item->unit_price,2); ?></td>
<td style="padding:8px 12px;text-align:right;font-size:12px;font-weight:600">$<?php echo number_format($item->total,2); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</div>
