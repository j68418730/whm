<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Edit Package - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
body{font-family:Inter,sans-serif;background:#000;color:#fff;margin:0;padding:40px}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.card{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;max-width:900px;margin:auto;position:relative;z-index:1}
h1{color:#0A84FF;margin-bottom:24px}
.form-group{margin-bottom:14px}
label{display:block;margin-bottom:4px;color:#94a3b8;font-weight:600;font-size:13px}
input,select,textarea{width:100%;padding:8px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.4);color:#e0e0e0;font-size:13px;outline:none;box-sizing:border-box}
input:focus,select:focus{border-color:#0A84FF}
.row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.btn{padding:10px 20px;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px;transition:.3s;text-decoration:none;display:inline-block}
.btn.primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn.primary:hover{transform:translateY(-2px)}
.btn.secondary{background:rgba(255,255,255,.06);color:#ccc;border:1px solid rgba(255,255,255,.1);text-decoration:none}
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
<div class="form-group"><label>Description</label><textarea name="description" style="min-height:50px"><?php echo htmlspecialchars($package->description ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea></div>
<div class="row">
<div class="form-group"><label>Monthly ($)</label><input name="monthly_price" type="number" step="0.01" value="<?php echo $package->monthly_price ?? 0; ?>"></div>
<div class="form-group"><label>Quarterly ($)</label><input name="quarterly_price" type="number" step="0.01" value="<?php echo $package->quarterly_price ?? 0; ?>"></div>
<div class="form-group"><label>Semi-Annual ($)</label><input name="semi_annual_price" type="number" step="0.01" value="<?php echo $package->semi_annual_price ?? 0; ?>"></div>
<div class="form-group"><label>Annual ($)</label><input name="annual_price" type="number" step="0.01" value="<?php echo $package->annual_price ?? 0; ?>"></div>
<div class="form-group"><label>Setup Fee ($)</label><input name="setup_fee" type="number" step="0.01" value="<?php echo $package->setup_fee ?? 0; ?>"></div>
<div class="form-group"><label>Sort Order</label><input name="sort_order" type="number" value="<?php echo $package->sort_order ?? 0; ?>"></div>
<div class="form-group"><label>Disk Space (GB)</label><input name="disk_space" type="number" value="<?php echo $package->disk_space ?? 0; ?>"></div>
<div class="form-group"><label>Bandwidth (GB)</label><input name="bandwidth" type="number" value="<?php echo $package->bandwidth ?? 0; ?>"></div>
<div class="form-group"><label>Max Domains</label><input name="max_domains" type="number" value="<?php echo $package->max_domains ?? 1; ?>"></div>
<div class="form-group"><label>Max Subdomains</label><input name="max_subdomains" type="number" value="<?php echo $package->max_subdomains ?? 0; ?>"></div>
</div>

<div class="form-group"><label>Feature List <a href="/admin/feature-lists" style="color:#0A84FF;font-size:12px">(Manage)</a></label>
<select name="feature_list_id">
<option value="">— None —</option>
<?php foreach ($featureLists as $fl): ?>
<option value="<?php echo $fl->id; ?>" <?php echo ($package->feature_list_id ?? '') == $fl->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($fl->name); ?></option>
<?php endforeach; ?>
</select>
</div>

<div style="margin:16px 0;border-top:1px solid rgba(255,255,255,.06);padding-top:12px">
<label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;margin-bottom:8px"><input type="checkbox" name="icecast_enabled" value="1" <?php echo ($package->icecast_enabled ?? 0) ? 'checked' : ''; ?> onchange="document.getElementById('radioFields').style.display=this.checked?'':'none'"> <strong>Enable Icecast / Radio Streaming</strong></label>
<div id="radioFields" style="display:<?php echo ($package->icecast_enabled ?? 0) ? 'grid' : 'none'; ?>;border:1px solid rgba(0,191,255,.1);border-radius:8px;padding:12px;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px">
<div class="form-group"><label>Listener Limit</label><input name="listener_limit" type="number" value="<?php echo $package->listener_limit ?? 0; ?>"></div>
<div class="form-group"><label>Bitrate (kbps)</label><input name="bitrate" type="number" value="<?php echo $package->bitrate ?? 0; ?>"></div>
<div class="form-group"><label>Storage (GB)</label><input name="storage_limit" type="number" value="<?php echo $package->storage_limit ?? 0; ?>"></div>
<div class="form-group"><label>DJ Accounts</label><input name="dj_accounts" type="number" value="<?php echo $package->dj_accounts ?? 0; ?>"></div>
</div>
<label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;margin-bottom:8px"><input type="checkbox" name="dj_panel_enabled" value="1" <?php echo ($package->dj_panel_enabled ?? 0) ? 'checked' : ''; ?>> <strong>Enable DJ Panel</strong></label>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin:12px 0">
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="live_chat_enabled" value="1" <?php echo ($package->live_chat_enabled ?? 0) ? 'checked' : ''; ?>> Live Chat</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="chatroom_enabled" value="1" <?php echo ($package->chatroom_enabled ?? 0) ? 'checked' : ''; ?>> Chat Room</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="chatroom_voice_enabled" value="1" <?php echo ($package->chatroom_voice_enabled ?? 0) ? 'checked' : ''; ?>> Chat Voice</label>
</div>

<div style="margin-top:20px;display:flex;gap:12px">
<button type="submit" class="btn primary">Update Package</button>
<a href="/admin/packages" class="btn secondary">Cancel</a>
</div>
</form>
</div>
</body>
</html>
