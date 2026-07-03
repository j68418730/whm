<div class="card" style="padding:20px;max-width:800px">
<h3 style="margin-bottom:12px"><?php echo $product ? 'Edit' : 'New'; ?> Product</h3>
<form method="POST" action="<?php echo $product ? '/admin/store/products/update/'.$product->id : '/admin/store/products/store'; ?>">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
<div><label style="font-size:11px;color:#94a3b8">Name</label><input name="name" value="<?php echo $product ? htmlspecialchars($product->name) : ''; ?>" required style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div><label style="font-size:11px;color:#94a3b8">Category</label><select name="category_id" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px">
<option value="">None</option>
<?php foreach ($categories as $c): ?>
<option value="<?php echo $c->id; ?>" <?php echo $product && $product->category_id == $c->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($c->name); ?></option>
<?php endforeach; ?>
</select></div>
</div>
<div style="margin-bottom:12px"><label style="font-size:11px;color:#94a3b8">Short Description</label><input name="short_description" value="<?php echo $product ? htmlspecialchars($product->short_description ?? '') : ''; ?>" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div style="margin-bottom:12px"><label style="font-size:11px;color:#94a3b8">Description</label><textarea name="description" rows="4" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"><?php echo $product ? htmlspecialchars($product->description ?? '') : ''; ?></textarea></div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px">
<div><label style="font-size:11px;color:#94a3b8">Price ($)</label><input name="price" type="number" step="0.01" value="<?php echo $product ? $product->price : '0'; ?>" required style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div><label style="font-size:11px;color:#94a3b8">Compare Price ($)</label><input name="compare_price" type="number" step="0.01" value="<?php echo $product ? $product->compare_price : ''; ?>" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div><label style="font-size:11px;color:#94a3b8">Stock (-1 = unlimited)</label><input name="stock" type="number" value="<?php echo $product ? $product->stock : '-1'; ?>" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px">
<div><label style="font-size:11px;color:#94a3b8">Type</label><select name="type" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px">
<option value="digital" <?php echo $product && $product->type === 'digital' ? 'selected' : ''; ?>>Digital</option>
<option value="service" <?php echo $product && $product->type === 'service' ? 'selected' : ''; ?>>Service</option>
<option value="physical" <?php echo $product && $product->type === 'physical' ? 'selected' : ''; ?>>Physical</option>
</select></div>
<div><label style="font-size:11px;color:#94a3b8">SKU</label><input name="sku" value="<?php echo $product ? htmlspecialchars($product->sku ?? '') : ''; ?>" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div><label style="font-size:11px;color:#94a3b8">Status</label><select name="status" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px">
<option value="draft" <?php echo $product && $product->status === 'draft' ? 'selected' : ''; ?>>Draft</option>
<option value="active" <?php echo $product && $product->status === 'active' ? 'selected' : ''; ?>>Active</option>
<option value="inactive" <?php echo $product && $product->status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
</select></div>
</div>
<div style="margin-bottom:12px"><label style="font-size:11px;color:#94a3b8">Images (JSON array of URLs)</label><input name="images" value="<?php echo $product ? htmlspecialchars($product->images ?? '') : ''; ?>" placeholder='["https://..."]' style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div style="margin-bottom:12px"><label style="font-size:11px;color:#94a3b8">Metadata (JSON)</label><textarea name="metadata" rows="3" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"><?php echo $product ? htmlspecialchars($product->metadata ?? '') : ''; ?></textarea></div>
<div style="margin-bottom:12px"><label><input type="checkbox" name="featured" value="1" <?php echo $product && $product->featured ? 'checked' : ''; ?>> Featured</label></div>
<button type="submit" class="btn primary"><i class="bi bi-save"></i> <?php echo $product ? 'Update' : 'Create'; ?> Product</button>
<a href="/admin/store/products" class="btn btn-sm secondary">Cancel</a>
</form>
</div>
