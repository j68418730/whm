<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<div class="card" style="max-width:500px">
<form method="POST" action="/admin/branding/save">
<h3 style="color:var(--accent);margin-bottom:12px">Branding Settings</h3>
<div class="form-group"><label>Company Name</label><input name="company_name" value="<?php echo htmlspecialchars($company_name); ?>"></div>
<div class="form-group"><label>Company Email</label><input name="company_email" type="email" value="<?php echo htmlspecialchars($company_email); ?>"></div>
<div class="form-group"><label>Website</label><input name="company_website" value="<?php echo htmlspecialchars($company_website); ?>"></div>
<p style="color:var(--text-secondary);font-size:13px;margin-top:12px">Branding settings are used across the panel for company info display.</p>
<button type="submit" class="btn primary" style="margin-top:12px">Save Branding</button>
</form></div>
