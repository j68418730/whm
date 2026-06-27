<form method="POST" action="/admin/reseller/store">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:900px">

<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">Reseller Details</h4>
<div class="form-group"><label>Company Name *</label><input name="company_name" required style="width:100%"></div>
<div class="form-group"><label>Contact Name</label><input name="contact_name" style="width:100%"></div>
<div class="form-group"><label>Email *</label><input name="email" type="email" required style="width:100%"></div>
<div class="form-group"><label>Phone</label><input name="phone" style="width:100%"></div>
<div class="form-group"><label>Website</label><input name="website" placeholder="https://" style="width:100%"></div>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;margin-top:8px"><input name="is_active" type="checkbox" value="1" checked> Active</label>
</div>

<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">Feature List</h4>
<select name="feature_list_id" style="width:100%">
<option value="">— No feature list —</option>
<?php if (!empty($featureLists)): foreach ($featureLists as $fl): ?>
<option value="<?php echo $fl->id; ?>"><?php echo htmlspecialchars($fl->name); ?></option>
<?php endforeach; endif; ?>
</select>
<p style="font-size:11px;color:#64748b;margin-top:6px">Feature lists control reseller limits (email, DBs, SSH, etc.)</p>
</div>

</div>

<div class="card" style="max-width:900px;margin-top:16px">
<h4 style="color:var(--accent);margin-bottom:12px">Assign Accounts</h4>
<p style="font-size:12px;color:#64748b;margin-bottom:10px">Select accounts to assign to this reseller:</p>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:6px;max-height:300px;overflow-y:auto">
<?php if (!empty($accounts)): foreach ($accounts as $a): ?>
<label style="display:flex;align-items:center;gap:6px;font-size:12px;padding:4px 8px;background:rgba(255,255,255,.02);border-radius:4px;cursor:pointer">
<input type="checkbox" name="assigned_accounts[]" value="<?php echo $a->id; ?>">
<?php echo htmlspecialchars($a->username); ?> <span style="color:#64748b">(<?php echo htmlspecialchars($a->domain ?? '-'); ?>)</span>
</label>
<?php endforeach; else: ?>
<p style="font-size:12px;color:#64748b">No unassigned accounts available.</p>
<?php endif; ?>
</div>
</div>

<div style="display:flex;gap:12px;margin-top:20px">
<button type="submit" class="btn primary"><i class="bi bi-check-circle"></i> Create Reseller</button>
<a href="/admin/reseller" class="btn secondary">Cancel</a>
</div>
</form>
