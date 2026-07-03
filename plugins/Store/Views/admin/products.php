<div class="card" style="padding:20px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
<h3 style="margin:0">Products</h3>
<a href="/admin/store/products/create" class="btn btn-sm primary"><i class="bi bi-plus-lg"></i> New Product</a>
</div>
<?php if (empty($products)): ?>
<p style="color:#64748b">No products yet.</p>
<?php else: ?>
<div style="overflow-x:auto">
<table style="width:100%;border-collapse:collapse;font-size:12px">
<thead><tr style="background:rgba(0,0,0,.2);text-transform:uppercase;font-size:10px;color:#64748b">
<th style="padding:8px 10px;text-align:left">Name</th><th style="padding:8px 10px;text-align:left">Category</th><th style="padding:8px 10px;text-align:right">Price</th><th style="padding:8px 10px;text-align:center">Stock</th><th style="padding:8px 10px;text-align:center">Status</th><th style="padding:8px 10px;text-align:center">Actions</th>
</tr></thead>
<tbody>
<?php foreach ($products as $p): ?>
<tr style="border-top:1px solid rgba(255,255,255,.04)">
<td style="padding:8px 10px"><?php echo htmlspecialchars($p->name); ?></td>
<td style="padding:8px 10px;color:#94a3b8"><?php echo htmlspecialchars($p->category_name ?? 'Uncategorized'); ?></td>
<td style="padding:8px 10px;text-align:right;font-weight:600">$<?php echo number_format($p->price,2); ?></td>
<td style="padding:8px 10px;text-align:center"><?php echo $p->stock < 0 ? '∞' : $p->stock; ?></td>
<td style="padding:8px 10px;text-align:center"><span class="badge" style="background:<?php echo $p->status === 'active' ? '#4ade80' : ($p->status === 'draft' ? '#64748b' : '#f87171'); ?>"><?php echo $p->status; ?></span></td>
<td style="padding:8px 10px;text-align:center"><a href="/admin/store/products/edit/<?php echo $p->id; ?>" class="btn btn-sm secondary" style="font-size:9px;padding:3px 8px">Edit</a> <a href="/admin/store/products/delete/<?php echo $p->id; ?>" class="btn btn-sm" style="font-size:9px;padding:3px 8px;background:#ef4444;color:#fff" onclick="return confirm('Delete this product?')">Delete</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
