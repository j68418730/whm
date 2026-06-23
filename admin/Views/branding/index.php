<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<div class="card" style="max-width:500px">
<form method="POST" action="/admin/branding/save" enctype="multipart/form-data">
<h3 style="color:var(--accent);margin-bottom:12px">Branding Settings</h3>
<div class="form-group"><label>Company Name</label><input name="company_name" value="<?php echo htmlspecialchars($company_name); ?>"></div>
<div class="form-group"><label>Company Email</label><input name="company_email" type="email" value="<?php echo htmlspecialchars($company_email); ?>"></div>
<div class="form-group"><label>Website</label><input name="company_website" value="<?php echo htmlspecialchars($company_website); ?>"></div>
<div class="form-group"><label>Company Logo</label>
<?php if ($company_logo): ?><div style="margin-bottom:6px"><img src="<?php echo htmlspecialchars($company_logo); ?>" style="max-height:48px;border-radius:6px"></div><?php endif; ?>
<input type="file" name="logo" accept="image/png,image/jpeg,image/gif,image/svg+xml,image/webp" style="width:100%;padding:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px">
<small style="color:#64748b">Upload logo (PNG, JPG, SVG, WebP). Replaces the header logo.</small>
</div>
<p style="color:var(--text-secondary);font-size:13px;margin-top:12px">Branding settings are used across the panel and customer area.</p>
<button type="submit" class="btn primary" style="margin-top:12px">Save Branding</button>
</form></div>
