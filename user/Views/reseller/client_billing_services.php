<?php include __DIR__ . '/partials/billing_tabs.php'; ?>
<h3 style="color:var(--accent);margin-bottom:12px">🖥 Services — Your Clients</h3>
<div class="card">
<table class="table">
<tr><th>Service</th><th>Client</th><th>Product</th><th>Price</th><th>Cycle</th><th>Due</th><th>Status</th></tr>
<?php if (!empty($services)): foreach ($services as $s): ?>
<tr>
<td>#<?php echo (int)$s->id; ?></td>
<td><?php echo htmlspecialchars($s->client); ?></td>
<td><?php echo htmlspecialchars($s->product_name ?: '-'); ?></td>
<td>$<?php echo number_format((float)$s->price, 2); ?></td>
<td><?php echo htmlspecialchars($s->billing_cycle ?? '-'); ?></td>
<td><?php echo $s->next_due_date ?? '-'; ?></td>
<td><span class="status-badge status-<?php echo $s->status === 'active' ? 'active' : 'terminated'; ?>"><?php echo htmlspecialchars($s->status); ?></span></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="7" style="text-align:center;padding:20px;color:#64748b">No client services.</td></tr>
<?php endif; ?>
</table>
</div>
<a href="/reseller/billing-system" class="btn secondary" style="margin-top:12px">&larr; Back</a>