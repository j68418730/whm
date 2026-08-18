<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<a href="/admin/billing" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📊 Dashboard</a>
<a href="/admin/billing/cart" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🛒 Cart</a>
<a href="/admin/billing/products" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📦 Products</a>
<a href="/admin/billing/categories" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff">🏷️ Categories</a>
<a href="/admin/billing/orders" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📋 Orders</a>
<a href="/admin/billing/services" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🖥 Services</a>
<a href="/admin/billing/invoices" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">💰 Invoices</a>
</div>

<div style="display:flex;gap:12px;align-items:start;flex-wrap:wrap;margin-bottom:20px">
<a href="/admin/billing/products" class="btn secondary" style="font-size:12px;padding:6px 14px">← Back to Products</a>
</div>

<div style="max-width:700px;margin-bottom:24px;background:rgba(8,16,28,.7);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:20px">
<h3 style="color:#0A84FF;font-size:15px;margin:0 0 14px">Add Category</h3>
<form method="POST" action="/admin/billing/categories/store">
<div style="display:grid;grid-template-columns:2fr 2fr 1fr 1fr;gap:10px;align-items:end">
<div><label style="display:block;font-size:10px;color:#64748b;margin-bottom:3px;text-transform:uppercase;font-weight:600">Name</label><input name="name" required placeholder="e.g. Web Hosting" style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></div>
<div><label style="display:block;font-size:10px;color:#64748b;margin-bottom:3px;text-transform:uppercase;font-weight:600">Slug</label><input name="slug" required placeholder="e.g. web-hosting" style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></div>
<div><label style="display:block;font-size:10px;color:#64748b;margin-bottom:3px;text-transform:uppercase;font-weight:600">Icon</label><input name="icon" value="📦" style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></div>
<div><button type="submit" style="width:100%;padding:7px 14px;border-radius:6px;border:none;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;font-weight:600;font-size:12px;cursor:pointer">Add</button></div>
</div>
</form>
</div>

<div style="max-width:700px">
<?php if (empty($categories)): ?>
<div style="text-align:center;padding:24px;color:#64748b">No categories yet.</div>
<?php else: ?>
<table style="width:100%;border-collapse:collapse">
<tr style="border-bottom:1px solid rgba(255,255,255,.06)">
<th style="text-align:left;padding:8px 10px;font-size:11px;color:#64748b;text-transform:uppercase">Icon</th>
<th style="text-align:left;padding:8px 10px;font-size:11px;color:#64748b;text-transform:uppercase">Name</th>
<th style="text-align:left;padding:8px 10px;font-size:11px;color:#64748b;text-transform:uppercase">Slug</th>
<th style="text-align:left;padding:8px 10px;font-size:11px;color:#64748b;text-transform:uppercase">Sort</th>
<th style="text-align:left;padding:8px 10px;font-size:11px;color:#64748b;text-transform:uppercase">Status</th>
<th style="text-align:left;padding:8px 10px;font-size:11px;color:#64748b;text-transform:uppercase">Actions</th>
</tr>
<?php foreach ($categories as $cat): ?>
<tr style="border-bottom:1px solid rgba(255,255,255,.04)">
<form method="POST" action="/admin/billing/categories/update/<?php echo $cat->id; ?>" style="display:contents">
<td style="padding:8px 10px;font-size:22px"><?php echo htmlspecialchars($cat->icon ?? '📦'); ?></td>
<td style="padding:8px 10px"><input name="name" value="<?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?>" style="width:100%;padding:4px 8px;border-radius:4px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></td>
<td style="padding:8px 10px"><input name="slug" value="<?php echo htmlspecialchars($cat->slug ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="width:100%;padding:4px 8px;border-radius:4px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></td>
<td style="padding:8px 10px"><input name="sort_order" type="number" value="<?php echo $cat->sort_order ?? 0; ?>" style="width:50px;padding:4px;border-radius:4px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></td>
<td style="padding:8px 10px"><a href="/admin/billing/categories/toggle/<?php echo $cat->id; ?>" style="display:inline-block;padding:2px 10px;border-radius:10px;font-size:10px;font-weight:600;text-decoration:none;<?php echo $cat->is_active ? 'background:rgba(74,222,128,.15);color:#4ade80' : 'background:rgba(248,113,113,.15);color:#f87171' ?>"><?php echo $cat->is_active ? 'Active' : 'Inactive' ?></a></td>
<td style="padding:8px 10px;display:flex;gap:4px">
<button type="submit" style="padding:3px 10px;border-radius:4px;border:1px solid rgba(0,140,255,.2);background:rgba(0,140,255,.1);color:#0A84FF;font-size:11px;cursor:pointer">Save</button>
<a href="/admin/billing/categories/delete/<?php echo $cat->id; ?>" style="padding:3px 10px;border-radius:4px;border:1px solid rgba(255,50,50,.2);background:rgba(255,50,50,.15);color:#ff6b6b;font-size:11px;text-decoration:none" onclick="return confirm('Delete this category?')">Delete</a>
</td>
</form>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>
