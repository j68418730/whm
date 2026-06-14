<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Create Package - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
body{font-family:Inter,sans-serif;background:#000;color:#fff;margin:0;padding:40px}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.card{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;max-width:800px;margin:auto;position:relative;z-index:1}
h1{color:#0A84FF;margin-bottom:24px}
.form-group{margin-bottom:16px}
label{display:block;margin-bottom:6px;color:#94a3b8;font-weight:600;font-size:14px}
input,select,textarea{width:100%;padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:14px;outline:none;box-sizing:border-box}
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
<h1>Create Package</h1>
<form method="POST" action="/admin/package/create">
<div class="row">
<div class="form-group"><label>Name</label><input name="name" required></div>
<div class="form-group"><label>Type</label><select name="type"><?php foreach ($types as $t): ?><option value="<?php echo $t; ?>"><?php echo $t; ?></option><?php endforeach; ?></select></div>
</div>
<div class="form-group"><label>Description</label><textarea name="description"></textarea></div>
<div class="row">
<div class="form-group"><label>Monthly Price ($)</label><input name="monthly_price" type="number" step="0.01" value="0"></div>
<div class="form-group"><label>Sort Order</label><input name="sort_order" type="number" value="0"></div>
</div>
<div class="row">
<div class="form-group"><label>Disk Space (GB)</label><input name="disk_space" type="number" value="0"></div>
<div class="form-group"><label>Bandwidth (GB)</label><input name="bandwidth" type="number" value="0"></div>
</div>
<div class="row">
<div class="form-group"><label>Listener Limit</label><input name="listener_limit" type="number" value="0"></div>
<div class="form-group"><label>Bitrate (kbps)</label><input name="bitrate" type="number" value="0"></div>
</div>
<div class="row">
<div class="form-group"><label>Storage Limit (GB)</label><input name="storage_limit" type="number" value="0"></div>
<div class="form-group"><label>DJ Accounts</label><input name="dj_accounts" type="number" value="0"></div>
</div>
<h2 style="color:#0A84FF;font-size:18px;margin:24px 0 16px">Feature List</h2>
<div id="featuresContainer"></div>
<div class="add-feature" onclick="var c=document.getElementById('featuresContainer');var d=document.createElement('div');d.className='feature-item';d.innerHTML='<input name=features[] placeholder=\"e.g. 10 GB Storage\"><button type=button onclick=this.parentElement.remove()>✕</button>';c.appendChild(d)">+ Add Feature</div>
<div style="margin-top:28px;display:flex;gap:12px">
<button type="submit" class="btn primary">Create Package</button>
<a href="/admin/packages" class="btn secondary">Cancel</a>
</div>
</form>
</div>
</body>
</html>
