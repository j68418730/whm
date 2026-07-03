<div class="card" style="padding:20px;max-width:800px">
<a href="/admin/store/orders" class="btn btn-sm secondary" style="margin-bottom:12px"><i class="bi bi-arrow-left"></i> All Orders</a>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px">
<div><h3 style="margin:0">Order #<?php echo $order->id; ?></h3>
<span style="font-size:11px;color:#64748b"><?php echo date("F j, Y g:i A", strtotime($order->created_at)); ?></span></div>
<form method="POST" action="/admin/store/orders/update-status/<?php echo $order->id; ?>" style="display:flex;gap:6px;align-items:center">
<select name="status" style="padding:6px 8px;border-radius:4px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:11px">
<option value="pending" <?php echo $order->status === 'pending' ? 'selected' : ''; ?>>Pending</option>
<option value="processing" <?php echo $order->status === 'processing' ? 'selected' : ''; ?>>Processing</option>
<option value="completed" <?php echo $order->status === 'completed' ? 'selected' : ''; ?>>Completed</option>
<option value="cancelled" <?php echo $order->status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
<option value="refunded" <?php echo $order->status === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
</select>
<select name="payment_status" style="padding:6px 8px;border-radius:4px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:11px">
<option value="unpaid" <?php echo $order->payment_status === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
<option value="paid" <?php echo $order->payment_status === 'paid' ? 'selected' : ''; ?>>Paid</option>
<option value="refunded" <?php echo $order->payment_status === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
</select>
<button type="submit" class="btn btn-sm primary" style="font-size:10px">Update</button>
</form>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
<div><strong style="font-size:12px">Customer</strong><br><span style="font-size:12px"><?php echo htmlspecialchars($order->first_name . ' ' . $order->last_name); ?></span><br><span style="font-size:11px;color:#94a3b8"><?php echo htmlspecialchars($order->email); ?><?php if ($order->phone): ?> &middot; <?php echo htmlspecialchars($order->phone); endif; ?></span></div>
<div><strong style="font-size:12px">Totals</strong><br><span style="font-size:11px;color:#94a3b8">Subtotal: $<?php echo number_format($order->subtotal,2); ?></span><br><span style="font-size:11px;color:#94a3b8">Tax: $<?php echo number_format($order->tax,2); ?></span><br><span style="font-size:20px;font-weight:700;color:var(--accent)">$<?php echo number_format($order->total,2); ?></span></div>
</div>
<?php if ($order->address_line1): ?>
<div style="margin-bottom:16px;font-size:12px;color:#94a3b8">
<strong style="color:#e0e0e0">Shipping</strong><br>
<?php echo htmlspecialchars($order->address_line1); ?><?php if ($order->address_line2): ?>, <?php echo htmlspecialchars($order->address_line2); endif; ?><br>
<?php echo htmlspecialchars($order->city); ?>, <?php echo htmlspecialchars($order->state); ?> <?php echo htmlspecialchars($order->zip); ?><br>
<?php echo htmlspecialchars($order->country); ?>
</div>
<?php endif; ?>
<h4 style="font-size:13px;margin-bottom:8px">Items</h4>
<div style="background:rgba(0,0,0,.15);border-radius:8px;overflow:hidden">
<table style="width:100%;border-collapse:collapse">
<thead><tr style="font-size:10px;text-transform:uppercase;color:#64748b;background:rgba(0,0,0,.2)">
<th style="padding:8px 12px;text-align:left">Product</th><th style="padding:8px 12px;text-align:center">Qty</th><th style="padding:8px 12px;text-align:right">Price</th><th style="padding:8px 12px;text-align:right">Total</th>
</tr></thead>
<tbody>
<?php foreach ($items as $item): ?>
<tr style="border-top:1px solid rgba(255,255,255,.04)">
<td style="padding:8px 12px;font-size:12px"><?php echo htmlspecialchars($item->product_name); ?></td>
<td style="padding:8px 12px;text-align:center"><?php echo $item->qty; ?></td>
<td style="padding:8px 12px;text-align:right">$<?php echo number_format($item->unit_price,2); ?></td>
<td style="padding:8px 12px;text-align:right;font-weight:600">$<?php echo number_format($item->total,2); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php if ($order->invoice_id): ?>
<div style="margin-top:12px;font-size:12px">
Invoice: <a href="/admin/billing/invoices/<?php echo $order->invoice_id; ?>" style="color:var(--accent)">#<?php echo $order->invoice_id; ?></a>
</div>
<?php endif; ?>
</div>
