<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="card" style="max-width:800px">
<form method="POST" action="/admin/package/create">
<?php echo $csrfField; ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
<div class="form-group"><label>Name</label><input name="name" required></div>
<div class="form-group"><label>Type</label>
<select name="type" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.4);color:#e0e0e0;font-size:14px;outline:none">
<?php foreach ($categories as $cat): ?><option value="<?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($cat->icon . ' ' . $cat->name, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
</select></div>
</div>
<div class="form-group"><label>Description</label><textarea name="description" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:14px;outline:none;resize:vertical;min-height:60px"></textarea></div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
<div class="form-group"><label>Monthly ($)</label><input name="monthly_price" type="number" step="0.01" value="0" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:13px;outline:none"></div>
<div class="form-group"><label>Quarterly ($)</label><input name="quarterly_price" type="number" step="0.01" value="0" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:13px;outline:none"></div>
<div class="form-group"><label>Semi-Annual ($)</label><input name="semi_annual_price" type="number" step="0.01" value="0" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:13px;outline:none"></div>
<div class="form-group"><label>Annual ($)</label><input name="annual_price" type="number" step="0.01" value="0" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:13px;outline:none"></div>
<div class="form-group"><label>Setup Fee ($)</label><input name="setup_fee" type="number" step="0.01" value="0" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:13px;outline:none"></div>
<div class="form-group"><label>Sort Order</label><input name="sort_order" type="number" value="0" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:13px;outline:none"></div>
<div class="form-group"><label>Disk Space (GB)</label><input name="disk_space" type="number" value="0" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:13px;outline:none"></div>
<div class="form-group"><label>Bandwidth (GB)</label><input name="bandwidth" type="number" value="0" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:13px;outline:none"></div>
<div class="form-group"><label>Max Domains</label><input name="max_domains" type="number" value="1" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:13px;outline:none"></div>
<div class="form-group"><label>Max Subdomains</label><input name="max_subdomains" type="number" value="0" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:13px;outline:none"></div>
</div>

<div class="form-group"><label>Feature List <a href="/admin/feature-lists" style="color:#0A84FF;font-size:11px">(Manage Feature Lists)</a></label>
<select name="feature_list_id" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.4);color:#e0e0e0;font-size:13px;outline:none">
<option value="">— None —</option>
<?php foreach ($featureLists as $fl): ?>
<option value="<?php echo $fl->id; ?>"><?php echo htmlspecialchars($fl->name); ?></option>
<?php endforeach; ?>
</select>
</div>

<div style="margin-top:16px;border-top:1px solid rgba(255,255,255,.06);padding-top:12px">
<label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;margin-bottom:8px"><input type="checkbox" name="icecast_enabled" value="1" onchange="document.getElementById('radioFields').style.display=this.checked?'':'none'"> <strong>Enable Icecast / Radio Streaming</strong></label>
<div id="radioFields" style="display:none;border:1px solid rgba(0,191,255,.1);border-radius:8px;padding:12px;margin-bottom:12px;display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div class="form-group"><label>Listener Limit</label><input name="listener_limit" type="number" value="0" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:13px;outline:none"></div>
<div class="form-group"><label>Bitrate (kbps)</label><input name="bitrate" type="number" value="0" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:13px;outline:none"></div>
<div class="form-group"><label>Storage Limit (GB)</label><input name="storage_limit" type="number" value="0" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:13px;outline:none"></div>
<div class="form-group"><label>DJ Accounts</label><input name="dj_accounts" type="number" value="0" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:13px;outline:none"></div>
</div>
<label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;margin-bottom:8px"><input type="checkbox" name="dj_panel_enabled" value="1"> <strong>Enable DJ Panel</strong></label>
</div>

<div style="margin-top:16px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input name="live_chat_enabled" type="checkbox" value="1"> Live Chat</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input name="chatroom_enabled" type="checkbox" value="1"> Chat Room</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input name="chatroom_voice_enabled" type="checkbox" value="1"> Chat Voice</label>
</div>

<div style="margin-top:24px;display:flex;gap:12px">
<button type="submit" class="btn primary">Create Package</button>
<a href="/admin/packages" class="btn secondary">Cancel</a>
</div>
</form>
</div>

<script>
document.querySelector('[name="icecast_enabled"]').addEventListener('change', function() {
    document.getElementById('radioFields').style.display = this.checked ? 'grid' : 'none';
});
</script>
