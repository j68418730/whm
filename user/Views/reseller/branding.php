<div class="card" style="max-width:500px">
<h3 style="color:var(--accent);margin-bottom:12px">Branding Settings</h3>
<p style="color:var(--text-secondary);margin-bottom:12px">Customize your reseller brand. Changes affect your client's panel view.</p>
<form method="POST" action="/reseller/branding/update">
<div class="form-group"><label>Company Name</label><input name="company_name" value="<?php echo htmlspecialchars($reseller->company_name); ?>"></div>
<div class="form-group"><label>Support Email</label><input name="support_email" type="email" value="<?php echo htmlspecialchars($reseller->email); ?>"></div>
<div class="form-group"><label>Website</label><input name="website" value="<?php echo htmlspecialchars($reseller->website ?? ''); ?>"></div>
<button type="submit" class="btn primary">Save</button>
</form></div>
<a href="/reseller" class="btn secondary" style="margin-top:8px">&larr; Back</a>
