<form method="POST" action="/admin/reseller/store">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:900px">

<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">Reseller Details</h4>
<div class="form-group"><label>Company Name *</label><input name="company_name" required style="width:100%"></div>
<div class="form-group"><label>Contact Name</label><input name="contact_name" style="width:100%"></div>
<div class="form-group"><label>Email *</label><input name="email" type="email" required style="width:100%"></div>
<div class="form-group"><label>Phone</label><input name="phone" style="width:100%"></div>
<div class="form-group"><label>Website</label><input name="website" style="width:100%"></div>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;margin-top:8px"><input name="is_active" type="checkbox" value="1" checked> Active</label>
</div>

<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">Reseller Package &amp; Type</h4>
<div class="form-group"><label>Reseller Package *</label>
<select name="package_id" required style="width:100%" onchange="var s=this.options[this.selectedIndex].text;var t=s.match(/Radio/i)?'icecast':'web';document.getElementById('typeLabel').textContent=t==='icecast'?'🎵 Radio Reseller':'🌐 Web Reseller'">
<option value="">— Select package —</option>
<?php if (!empty($pkgs)): foreach ($pkgs as $p): ?>
<option value="<?php echo $p->id; ?>"><?php echo htmlspecialchars($p->name . ' (' . $p->type . ')'); ?></option>
<?php endforeach; endif; ?>
</select>
<p style="font-size:11px;color:#64748b;margin-top:6px">Determines reseller type <b id="typeLabel">🌐 Web Reseller</b> and which products they can retail. Choose a Radio Reseller package for icecast/radio resellers.</p>
</div>
<div class="form-group"><label>Feature List (per-account limits)</label>
<select name="feature_list_id" style="width:100%">
<option value="">— No feature list —</option>
<?php if (!empty($featureLists)): foreach ($featureLists as $fl): ?>
<option value="<?php echo $fl->id; ?>"><?php echo htmlspecialchars($fl->name); ?></option>
<?php endforeach; endif; ?>
</select>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div class="form-group"><label>Monthly Fee ($)</label><input name="monthly_fee" type="number" step="0.01" value="0.00" style="width:100%"></div>
<div class="form-group"><label>Billing Cycle</label>
<select name="billing_cycle" style="width:100%"><option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="semiannual">Semi-annual</option><option value="annual">Annual</option></select>
</div>
</div>
</div>

</div>
<div style="display:flex;gap:12px;margin-top:20px;max-width:900px">
<button type="submit" class="btn primary"><i class="bi bi-check-lg"></i> Create Reseller</button>
<a href="/admin/reseller" class="btn secondary">Cancel</a>
</div>
</form>