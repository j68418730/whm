<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="card" style="max-width:800px">
<form method="POST" action="/admin/package/create">
<?php echo $csrfField; ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
<div class="form-group"><label>Name</label><input name="name" required></div>
<div class="form-group"><label>Type</label>
<select name="type" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(0,0,0,.4);background:rgba(0,0,0,.4);color:#e0e0e0;font-size:14px;outline:none;border:1px solid rgba(255,255,255,.1)">
<?php foreach ($categories as $cat): ?><option value="<?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($cat->icon . ' ' . $cat->name, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
</select></div>
</div>
<div class="form-group"><label>Description</label><textarea name="description" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:14px;outline:none;resize:vertical;min-height:60px"></textarea></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
<div class="form-group"><label>Monthly Price ($)</label><input name="monthly_price" type="number" step="0.01" value="0" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:14px;outline:none"></div>
<div class="form-group"><label>Sort Order</label><input name="sort_order" type="number" value="0" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:14px;outline:none"></div>
<div class="form-group"><label>Disk Space (GB)</label><input name="disk_space" type="number" value="0" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:14px;outline:none"></div>
<div class="form-group"><label>Bandwidth (GB)</label><input name="bandwidth" type="number" value="0" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:14px;outline:none"></div>
<div class="form-group"><label>Listener Limit</label><input name="listener_limit" type="number" value="0" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:14px;outline:none"></div>
<div class="form-group"><label>Bitrate (kbps)</label><input name="bitrate" type="number" value="0" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:14px;outline:none"></div>
<div class="form-group"><label>Storage Limit (GB)</label><input name="storage_limit" type="number" value="0" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:14px;outline:none"></div>
<div class="form-group"><label>DJ Accounts</label><input name="dj_accounts" type="number" value="0" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:14px;outline:none"></div>
</div>

<div style="margin-top:16px" class="form-group"><label><input name="live_chat_enabled" type="checkbox" value="1"> Enable Live Chat Support</label></div>
<div class="form-group"><label><input name="chatroom_enabled" type="checkbox" value="1"> Enable Chat Room</label></div>
<div class="form-group"><label><input name="chatroom_voice_enabled" type="checkbox" value="1"> Enable Chat Room with Voice</label></div>
<h3 style="color:#0A84FF;font-size:18px;margin:24px 0 16px">Feature List</h3>
<div id="featuresContainer">
<div class="feature-item" style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
<input name="features[]" placeholder="e.g. 10 GB Storage" style="flex:1;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:14px;outline:none">
<button type="button" onclick="this.parentElement.remove()" style="background:rgba(255,50,50,.2);color:#ff6b6b;border:1px solid rgba(255,50,50,.3);border-radius:8px;padding:6px 12px;cursor:pointer">✕</button>
</div>
</div>
<div onclick="addFeature()" style="background:rgba(0,191,255,.08);border:1px dashed rgba(0,191,255,.2);border-radius:10px;padding:12px;text-align:center;cursor:pointer;color:#0A84FF;margin-top:8px;font-size:14px">+ Add Feature</div>

<div style="margin-top:24px;display:flex;gap:12px">
<button type="submit" class="btn primary">Create Package</button>
<a href="/admin/packages" class="btn secondary">Cancel</a>
</div>
</form>
</div>

<script>
function addFeature() {
    var c = document.getElementById('featuresContainer');
    var d = document.createElement('div');
    d.className = 'feature-item';
    d.style.cssText = 'display:flex;align-items:center;gap:10px;margin-bottom:8px';
    d.innerHTML = '<input name="features[]" placeholder="e.g. 10 GB Storage" style="flex:1;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:14px;outline:none"><button type="button" onclick="this.parentElement.remove()" style="background:rgba(255,50,50,.2);color:#ff6b6b;border:1px solid rgba(255,50,50,.3);border-radius:8px;padding:6px 12px;cursor:pointer">✕</button>';
    c.appendChild(d);
}
</script>
