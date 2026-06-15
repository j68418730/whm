<div class="card" style="max-width:500px">
<form method="POST" action="/admin/settings/company/save">
<h3 style="color:var(--accent);margin-bottom:12px">Company Settings</h3>
<div class="form-group"><label>Company Name</label><input name="company_name" value="<?php echo htmlspecialchars($company_name); ?>"></div>
<div class="form-group"><label>Company Email</label><input name="company_email" type="email" value="<?php echo htmlspecialchars($company_email); ?>"></div>
<div class="form-group"><label>Company Phone</label><input name="company_phone" value="<?php echo htmlspecialchars($company_phone); ?>"></div>
<div class="form-group"><label>Company Address</label><textarea name="company_address" rows="3"><?php echo htmlspecialchars($company_address); ?></textarea></div>
<div class="form-group"><label>Company Website</label><input name="company_website" value="<?php echo htmlspecialchars($company_website); ?>"></div>
<button type="submit" class="btn primary">Save</button>
<a href="/admin/settings" class="btn secondary">Back</a>
</form></div>
