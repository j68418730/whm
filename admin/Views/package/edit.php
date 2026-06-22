<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Edit Package - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
body{font-family:Inter,sans-serif;background:#000;color:#fff;margin:0;padding:40px}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.card{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;max-width:800px;margin:auto;position:relative;z-index:1}
h1{color:#0A84FF;margin-bottom:24px}
.form-group{margin-bottom:16px}
label{display:block;margin-bottom:6px;color:#94a3b8;font-weight:600;font-size:14px}
input,select,textarea{width:100%;padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.4);color:#e0e0e0;font-size:14px;outline:none;box-sizing:border-box}
textarea{resize:vertical;min-height:60px}
input:focus,select:focus,textarea:focus{border-color:#0A84FF}
.row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.feature-item{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.feature-item input{flex:1}
.feature-item button{background:rgba(255,50,50,.2);color:#ff6b6b;border:1px solid rgba(255,50,50,.3);border-radius:8px;padding:6px 12px;cursor:pointer}
.add-feature{background:rgba(0,191,255,.08);border:1px dashed rgba(0,191,255,.2);border-radius:10px;padding:10px;text-align:center;cursor:pointer;color:#0A84FF;margin-top:8px}
.add-feature:hover{background:rgba(0,191,255,.15)}
.btn{padding:12px 24px;border:none;border-radius:10px;font-weight:600;cursor:pointer;font-size:14px;transition:.3s;text-decoration:none;display:inline-block}
.btn.primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn.primary:hover{transform:translateY(-2px)}
.btn.secondary{background:rgba(255,255,255,.06);color:#ccc;border:1px solid rgba(255,255,255,.1)}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="card">
<h1>Edit Package</h1>
<form method="POST" action="/admin/package/edit/<?php echo $package->id; ?>">
<div class="row">
<div class="form-group"><label>Name</label><input name="name" value="<?php echo htmlspecialchars($package->name ?? '', ENT_QUOTES, 'UTF-8'); ?>" required></div>
<div class="form-group"><label>Type</label><select name="type"><?php foreach ($categories as $cat): ?><option value="<?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($package->type ?? '') === $cat->name ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat->icon . ' ' . $cat->name, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div>
</div>
<div class="form-group"><label>Description</label><textarea name="description"><?php echo htmlspecialchars($package->description ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea></div>
<div class="row">
<div class="form-group"><label>Monthly Price ($)</label><input name="monthly_price" type="number" step="0.01" value="<?php echo $package->monthly_price ?? 0; ?>"></div>
<div class="form-group"><label>Quarterly Price ($)</label><input name="quarterly_price" type="number" step="0.01" value="<?php echo $package->quarterly_price ?? 0; ?>"></div>
</div>
<div class="row">
<div class="form-group"><label>Semi-Annual ($)</label><input name="semi_annual_price" type="number" step="0.01" value="<?php echo $package->semi_annual_price ?? 0; ?>"></div>
<div class="form-group"><label>Annual Price ($)</label><input name="annual_price" type="number" step="0.01" value="<?php echo $package->annual_price ?? 0; ?>"></div>
</div>
<div class="row">
<div class="form-group"><label>Setup Fee ($)</label><input name="setup_fee" type="number" step="0.01" value="<?php echo $package->setup_fee ?? 0; ?>"></div>
<div class="form-group"><label>Sort Order</label><input name="sort_order" type="number" value="<?php echo $package->sort_order ?? 0; ?>"></div>
</div>
<div class="row">
<div class="form-group"><label>Disk Space (GB)</label><input name="disk_space" type="number" value="<?php echo $package->disk_space ?? 0; ?>"></div>
<div class="form-group"><label>Bandwidth (GB)</label><input name="bandwidth" type="number" value="<?php echo $package->bandwidth ?? 0; ?>"></div>
</div>
<div class="row">
<div class="form-group"><label>Max Domains</label><input name="max_domains" type="number" value="<?php echo $package->max_domains ?? 1; ?>"></div>
<div class="form-group"><label>Max Subdomains</label><input name="max_subdomains" type="number" value="<?php echo $package->max_subdomains ?? 0; ?>"></div>
</div>
<div class="form-group"><label>Feature List</label>
<select name="feature_list_id">
<option value="">— None —</option>
<?php foreach ($featureLists as $fl): ?>
<option value="<?php echo $fl->id; ?>" <?php echo ($package->feature_list_id ?? '') == $fl->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($fl->name); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="row">
<div class="form-group"><label>Listener Limit</label><input name="listener_limit" type="number" value="<?php echo $package->listener_limit ?? 0; ?>"></div>
<div class="form-group"><label>Bitrate (kbps)</label><input name="bitrate" type="number" value="<?php echo $package->bitrate ?? 0; ?>"></div>
</div>
<div class="row">
<div class="form-group"><label>Storage Limit (GB)</label><input name="storage_limit" type="number" value="<?php echo $package->storage_limit ?? 0; ?>"></div>
<div class="form-group"><label>DJ Accounts</label><input name="dj_accounts" type="number" value="<?php echo $package->dj_accounts ?? 0; ?>"></div>
</div>
<div class="form-group"><label><input type="checkbox" name="is_active" <?php echo ($package->is_active ?? 1) ? 'checked' : ''; ?>> Active</label></div>
<div class="form-group"><label><input type="checkbox" name="live_chat_enabled" value="1" <?php echo ($package->live_chat_enabled ?? 0) ? 'checked' : ''; ?>> Live Chat Support</label></div>
<div class="form-group"><label><input type="checkbox" name="chatroom_enabled" value="1" <?php echo ($package->chatroom_enabled ?? 0) ? 'checked' : ''; ?>> Chat Room</label></div>
<div class="form-group"><label><input type="checkbox" name="chatroom_voice_enabled" value="1" <?php echo ($package->chatroom_voice_enabled ?? 0) ? 'checked' : ''; ?>> Chat Room with Voice</label></div>

<h2 style="color:#0A84FF;font-size:18px;margin:24px 0 16px">Feature List</h2>
<div id="featuresContainer">
<?php $features = isset($package->features) && is_array($package->features) ? $package->features : []; ?>
<?php foreach ($features as $f): ?>
<div class="feature-item"><input name="features[]" value="<?php echo htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g. 10 GB Storage"><button type="button" onclick="this.parentElement.remove()">✕</button></div>
<?php endforeach; ?>
<div class="feature-item"><input name="features[]" placeholder="e.g. 10 GB Storage"><button type="button" onclick="this.parentElement.remove()">✕</button></div>
</div>
<div class="add-feature" onclick="addFeature()">+ Add Feature</div>
<script>
function addFeature() {
    var c = document.getElementById('featuresContainer');
    var d = document.createElement('div');
    d.className = 'feature-item';
    d.innerHTML = '<input name="features[]" placeholder="e.g. 10 GB Storage"><button type="button" onclick="this.parentElement.remove()">✕</button>';
    c.appendChild(d);
}
</script>

<div style="margin-top:28px;display:flex;gap:12px">
<button type="submit" class="btn primary">Update Package</button>
<a href="/admin/packages" class="btn secondary">Cancel</a>
</div>
</form>
</div>
</body>
</html>
