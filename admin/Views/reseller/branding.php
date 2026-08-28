<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
<h3 style="margin:0">White-Label Branding — <?php echo htmlspecialchars($reseller->company_name); ?></h3>
<a href="/admin/reseller/show/<?php echo $reseller->id; ?>" class="btn btn-sm secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
<form method="POST" action="/admin/reseller/branding/save/<?php echo $reseller->id; ?>">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:900px">
<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">Brand Assets</h4>
<div class="form-group"><label>Logo URL</label><input name="brand_logo" value="<?php echo htmlspecialchars($reseller->brand_logo ?? ''); ?>" style="width:100%"></div>
<div class="form-group"><label>Favicon URL</label><input name="brand_favicon" value="<?php echo htmlspecialchars($reseller->brand_favicon ?? ''); ?>" style="width:100%"></div>
<div class="form-group"><label>Primary Color</label><input name="brand_primary_color" value="<?php echo htmlspecialchars($reseller->brand_primary_color ?? ''); ?>" placeholder="#008cff" style="width:100%"></div>
<div class="form-group"><label>Secondary Color</label><input name="brand_secondary_color" value="<?php echo htmlspecialchars($reseller->brand_secondary_color ?? ''); ?>" placeholder="#a78bfa" style="width:100%"></div>
<div class="form-group"><label>Website URL</label><input name="brand_url" value="<?php echo htmlspecialchars($reseller->brand_url ?? ''); ?>" style="width:100%"></div>
</div>
<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">Contact &amp; Legal</h4>
<div class="form-group"><label>Support Email</label><input name="support_email" type="email" value="<?php echo htmlspecialchars($reseller->support_email ?? ''); ?>" style="width:100%"></div>
<div class="form-group"><label>Billing Email</label><input name="billing_email" type="email" value="<?php echo htmlspecialchars($reseller->billing_email ?? ''); ?>" style="width:100%"></div>
<div class="form-group"><label>Terms URL</label><input name="terms_url" value="<?php echo htmlspecialchars($reseller->terms_url ?? ''); ?>" style="width:100%"></div>
<div class="form-group"><label>Privacy URL</label><input name="privacy_url" value="<?php echo htmlspecialchars($reseller->privacy_url ?? ''); ?>" style="width:100%"></div>
</div>
</div>
<div style="margin-top:16px;max-width:900px">
<button type="submit" class="btn primary"><i class="bi bi-check-lg"></i> Save Branding</button>
</div>
</form>