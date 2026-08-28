<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
<h3 style="margin:0">Products — <?php echo htmlspecialchars($reseller->company_name); ?></h3>
<a href="/admin/reseller/show/<?php echo $reseller->id; ?>" class="btn btn-sm secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
<p style="color:#64748b;font-size:13px;max-width:900px;margin-bottom:14px">
Resellers retail Planet Hosts billing products at their own margins. Once the reseller panel is live, per-reseller product pricing and enable/disable toggles will live here. Products are filtered by reseller type (<?php echo ($reseller->type ?? 'web_reseller') === 'icecast_reseller' ? 'Radio/streaming-first' : 'Web/VPS-first'; ?>) via margins above.
</p>
<div class="card">
<table style="font-size:13px">
<tr><th>ID</th><th>Product</th><th>Type</th><th>Price</th><th>Cycle</th><th>Status</th></tr>
<?php
$typeFilter = ($reseller->type ?? 'web_reseller') === 'icecast_reseller' ? 'radio' : null;
foreach ($products as $p):
    $match = $typeFilter === null || in_array($p->type, ['radio','hosting','game','vps','ssl','domain','addon']);
?>
<?php if ($match): ?>
<tr>
<td><?php echo $p->id; ?></td>
<td><?php echo htmlspecialchars($p->name); ?></td>
<td><?php echo htmlspecialchars($p->type); ?></td>
<td>$<?php echo number_format((float)$p->price, 2); ?></td>
<td><?php echo htmlspecialchars($p->billing_cycle ?? 'monthly'); ?></td>
<td><?php echo $p->is_active ? '✅' : '🔴'; ?></td>
</tr>
<?php endif; ?>
<?php endforeach; ?>
</table>
</div>