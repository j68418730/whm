<form method="POST" action="/admin/reseller/update/<?php echo $reseller->id; ?>">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:900px">

<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">Edit: <?php echo htmlspecialchars($reseller->company_name); ?></h4>
<div class="form-group"><label>Company Name *</label><input name="company_name" value="<?php echo htmlspecialchars($reseller->company_name); ?>" required style="width:100%"></div>
<div class="form-group"><label>Contact Name</label><input name="contact_name" value="<?php echo htmlspecialchars($reseller->contact_name ?? ''); ?>" style="width:100%"></div>
<div class="form-group"><label>Email *</label><input name="email" type="email" value="<?php echo htmlspecialchars($reseller->email); ?>" required style="width:100%"></div>
<div class="form-group"><label>Phone</label><input name="phone" value="<?php echo htmlspecialchars($reseller->phone ?? ''); ?>" style="width:100%"></div>
<div class="form-group"><label>Account</label>
<select name="website" style="width:100%">
<option value="">— Select account —</option>
<?php if (!empty($allAccounts)): foreach ($allAccounts as $a): ?>
<option value="<?php echo htmlspecialchars($a->domain ?: $a->username); ?>" <?php echo ($reseller->website == $a->domain || $reseller->website == $a->username) ? 'selected' : ''; ?>><?php echo htmlspecialchars($a->username); ?> (<?php echo htmlspecialchars($a->domain ?: 'no domain'); ?>)</option>
<?php endforeach; endif; ?>
</select>
</div>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;margin-top:8px"><input name="is_active" type="checkbox" value="1" <?php echo $reseller->is_active ? 'checked' : ''; ?>> Active</label>
</div>

<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">Reseller Package &amp; Type</h4>
<div class="form-group"><label>Reseller Package *</label>
<select name="package_id" required style="width:100%" onchange="var s=this.options[this.selectedIndex].text;var t=s.match(/Radio/i)?'icecast':'web';document.getElementById('typeLabel').textContent=t==='icecast'?'🎵 Radio Reseller':'🌐 Web Reseller'">
<option value="">— Select package —</option>
<?php if (!empty($pkgs)): foreach ($pkgs as $p): ?>
<option value="<?php echo $p->id; ?>" <?php echo ($reseller->package_id ?? '') == $p->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name . ' (' . $p->type . ')'); ?></option>
<?php endforeach; endif; ?>
</select>
<p style="font-size:11px;color:#64748b;margin-top:6px">Type: <b id="typeLabel"><?php echo ($reseller->type ?? 'web_reseller') === 'icecast_reseller' ? '🎵 Radio Reseller' : '🌐 Web Reseller'; ?></b></p>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div class="form-group"><label>Monthly Fee ($)</label><input name="monthly_fee" type="number" step="0.01" value="<?php echo number_format((float)($reseller->monthly_fee ?? 0),2); ?>" style="width:100%"></div>
<div class="form-group"><label>Billing Cycle</label>
<select name="billing_cycle" style="width:100%"><option value="monthly" <?php echo ($reseller->billing_cycle ?? 'monthly')==='monthly'?'selected':''; ?>>Monthly</option><option value="quarterly" <?php echo ($reseller->billing_cycle ?? '')==='quarterly'?'selected':''; ?>>Quarterly</option><option value="semiannual" <?php echo ($reseller->billing_cycle ?? '')==='semiannual'?'selected':''; ?>>Semi-annual</option><option value="annual" <?php echo ($reseller->billing_cycle ?? '')==='annual'?'selected':''; ?>>Annual</option></select>
</div>
</div>
</div>

<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">Feature List</h4>
<select name="feature_list_id" style="width:100%">
<option value="">— No feature list —</option>
<?php if (!empty($featureLists)): foreach ($featureLists as $fl): ?>
<option value="<?php echo $fl->id; ?>" <?php echo ($reseller->feature_list_id ?? '') == $fl->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($fl->name); ?></option>
<?php endforeach; endif; ?>
</select>
</div>

</div>

<div class="card" style="max-width:900px;margin-top:16px">
<h4 style="color:var(--accent);margin-bottom:12px">Permissions</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px;font-size:12px">
<?php
$resFeats = is_string($reseller->features ?? null) ? json_decode($reseller->features, true) ?? [] : ($reseller->features ?? []);
$permFeatures = ['cron'=>'Cron','ssh'=>'SSH','ssl'=>'SSL','git'=>'Git','nodejs'=>'Node.js','python'=>'Python','ruby'=>'Ruby','terminal'=>'Terminal','backups'=>'Backups','installer'=>'Installer','builder'=>'Website Builder','ai_builder'=>'AI Builder','ai_assistant'=>'AI Assistant','marketplace'=>'Marketplace','api'=>'API','webhooks'=>'Webhooks','chat'=>'Chatbox','chat_voice'=>'+ Voice','chat_video'=>'+ Video','dj_panel'=>'DJ Panel','streaming'=>'Streaming','game_servers'=>'Game Servers','vps'=>'VPS','clients'=>'👥 Manage Clients','packages'=>'📦 Reseller Packages','provisioning'=>'⚙️ Provisioning','billing'=>'💰 Client Billing System','support'=>'🎧 Client Support System','staff'=>'🛡️ Roles & Staff','branding'=>'🎨 Branding'];
foreach ($permFeatures as $k=>$l):
$isSub = in_array($k, ['chat_voice','chat_video']);
$checked = in_array($k, (array)$resFeats) || empty($resFeats) ? 'checked' : '';
?>
<label class="feature-check" style="<?php echo $isSub ? 'padding-left:16px;font-size:11px' : ''; ?>">
<input type="checkbox" name="features[]" value="<?php echo $k; ?>" <?php echo $checked; ?>> <?php echo $l; ?>
</label>
<?php endforeach; ?>
</div>
</div>

<div class="card" style="max-width:900px;margin-top:16px">
<h4 style="color:var(--accent);margin-bottom:12px">Assigned Accounts</h4>
<p style="font-size:12px;color:#64748b;margin-bottom:10px">Select which accounts belong to this reseller:</p>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:6px;max-height:300px;overflow-y:auto">
<?php
$owned = [];
if (!empty($accounts)) foreach ($accounts as $a) $owned[$a->id] = true;
$allAccs = array_merge($accounts ?? [], $unassigned ?? []);
$seen = [];
?>
<?php if (!empty($allAccs)): foreach ($allAccs as $a):
if (isset($seen[$a->id])) continue; $seen[$a->id] = true;
$isOwned = isset($owned[$a->id]);
?>
<label style="display:flex;align-items:center;gap:6px;font-size:12px;padding:4px 8px;background:<?php echo $isOwned ? 'rgba(0,140,255,.06)' : 'rgba(255,255,255,.02)'; ?>;border-radius:4px;cursor:pointer">
<input type="checkbox" name="assigned_accounts[]" value="<?php echo $a->id; ?>" <?php echo $isOwned ? 'checked' : ''; ?>>
<?php echo htmlspecialchars($a->username); ?> <span style="color:#64748b">(<?php echo htmlspecialchars($a->domain ?? '-'); ?>)</span>
</label>
<?php endforeach; else: ?>
<p style="font-size:12px;color:#64748b">No accounts available.</p>
<?php endif; ?>
</div>
</div>

<div style="display:flex;gap:12px;margin-top:20px">
<button type="submit" class="btn primary"><i class="bi bi-check-lg"></i> Save</button>
<a href="/admin/reseller" class="btn secondary">Cancel</a>
</div>
</form>