<div class="card" style="max-width:500px">
<form method="POST" action="/admin/reseller/update/<?php echo $reseller->id; ?>">
<h3 style="color:var(--accent);margin-bottom:12px">Edit Reseller: <?php echo htmlspecialchars($reseller->company_name); ?></h3>
<div class="form-group"><label>Company Name</label><input name="company_name" value="<?php echo htmlspecialchars($reseller->company_name); ?>" required></div>
<div class="form-group"><label>Contact Name</label><input name="contact_name" value="<?php echo htmlspecialchars($reseller->contact_name ?? ''); ?>"></div>
<div class="form-group"><label>Email</label><input name="email" type="email" value="<?php echo htmlspecialchars($reseller->email); ?>" required></div>
<div class="form-group"><label>Phone</label><input name="phone" value="<?php echo htmlspecialchars($reseller->phone ?? ''); ?>"></div>
<div class="form-group"><label>Website</label><input name="website" value="<?php echo htmlspecialchars($reseller->website ?? ''); ?>"></div>
<div class="form-group"><label><input name="is_active" type="checkbox" value="1" <?php echo $reseller->is_active ? 'checked' : ''; ?>> Active</label></div>
<button type="submit" class="btn primary">Save</button>
<a href="/admin/reseller" class="btn secondary">Cancel</a>
</form></div>
