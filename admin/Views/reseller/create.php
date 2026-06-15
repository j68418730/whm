<div class="card" style="max-width:500px">
<form method="POST" action="/admin/reseller/store">
<h3 style="color:var(--accent);margin-bottom:12px">Create Reseller</h3>
<div class="form-group"><label>Company Name</label><input name="company_name" required></div>
<div class="form-group"><label>Contact Name</label><input name="contact_name"></div>
<div class="form-group"><label>Email</label><input name="email" type="email" required></div>
<div class="form-group"><label>Phone</label><input name="phone"></div>
<div class="form-group"><label>Website</label><input name="website" placeholder="https://"></div>
<div class="form-group"><label><input name="is_active" type="checkbox" value="1" checked> Active</label></div>
<button type="submit" class="btn primary">Create</button>
<a href="/admin/reseller" class="btn secondary">Cancel</a>
</form></div>
