<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Create Package - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#020817;color:#e0e0e0;min-height:100vh}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.92),rgba(2,8,23,.98)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}

.page{max-width:900px;margin:0 auto;padding:24px 20px 80px}
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.page-header h1{font-size:22px;font-weight:700;color:#fff;display:flex;align-items:center;gap:10px}
.page-header h1 i{color:#0A84FF;font-size:20px}
.page-header .actions{display:flex;gap:8px}
.btn{padding:10px 22px;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px;transition:.2s;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-family:'Inter',sans-serif}
.btn:active{transform:scale(.97)}
.btn-primary{background:linear-gradient(135deg,#0A84FF,#00C6FF);color:#fff}
.btn-primary:hover{box-shadow:0 4px 20px rgba(10,132,255,.3);transform:translateY(-1px)}
.btn-secondary{background:rgba(255,255,255,.05);color:#94a3b8;border:1px solid rgba(255,255,255,.08)}
.btn-secondary:hover{border-color:rgba(10,132,255,.3);color:#e0e0e0}

.section{background:rgba(8,16,28,.7);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:20px;margin-bottom:16px}
.section-title{font-size:14px;font-weight:700;color:#e0e0e0;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.section-title i{color:#0A84FF;font-size:14px}

.form-grid{display:grid;gap:12px}
.form-grid.cols-2{grid-template-columns:1fr 1fr}
.form-grid.cols-3{grid-template-columns:1fr 1fr 1fr}
.form-grid.cols-4{grid-template-columns:1fr 1fr 1fr 1fr}
@media(max-width:768px){.form-grid.cols-2,.form-grid.cols-3,.form-grid.cols-4{grid-template-columns:1fr}}

.form-group{display:flex;flex-direction:column;gap:4px}
.form-group label{font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px}
.form-group label .hint{font-weight:400;text-transform:none;letter-spacing:0;color:#475569;font-size:10px}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:9px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none;transition:.2s;font-family:'Inter',sans-serif}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:#0A84FF;box-shadow:0 0 0 3px rgba(10,132,255,.08)}
.form-group input::placeholder,.form-group textarea::placeholder{color:#334155}
.form-group select option{background:#0a0f1a;color:#e0e0e0}
.form-group textarea{resize:vertical;min-height:60px}
.form-group .help{font-size:10px;color:#475569;margin-top:2px}

.feature-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:6px}
.toggle-row{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.04);background:rgba(255,255,255,.02);transition:.15s}
.toggle-row:hover{border-color:rgba(255,255,255,.08);background:rgba(255,255,255,.03)}
.toggle-row .label{font-size:13px;color:#94a3b8}
.toggle-row .label strong{color:#e0e0e0;font-weight:500}
.toggle-row .switch{position:relative;width:36px;height:20px;flex-shrink:0}
.toggle-row .switch input{opacity:0;width:0;height:0}
.toggle-row .switch .slider{position:absolute;inset:0;background:rgba(255,255,255,.08);border-radius:10px;cursor:pointer;transition:.2s}
.toggle-row .switch .slider::after{content:'';position:absolute;top:2px;left:2px;width:16px;height:16px;background:#64748b;border-radius:50%;transition:.2s}
.toggle-row .switch input:checked+.slider{background:rgba(74,222,128,.2)}
.toggle-row .switch input:checked+.slider::after{left:18px;background:#4ade80}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="page">

<div class="page-header">
<h1><i class="fa-solid fa-cube"></i> Create Package</h1>
<div class="actions">
<a href="/admin/packages" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Packages</a>
<button type="submit" form="pkgForm" class="btn btn-primary"><i class="fa-solid fa-check"></i> Create Package</button>
</div>
</div>

<?php if (isset($_SESSION['error_message'])): ?>
<div style="padding:12px 16px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);border-radius:10px;color:#f87171;font-size:13px;margin-bottom:16px"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<form method="POST" action="/admin/package/create" id="pkgForm">
<?php echo $csrfField ?? ''; ?>

<!-- BASIC INFO -->
<div class="section">
<div class="section-title"><i class="fa-solid fa-tag"></i> Basic Info</div>
<div class="form-grid cols-2">
<div class="form-group"><label>Package Name *</label><input name="name" required placeholder="e.g. Starter, Basic, Business, Pro"></div>
<div class="form-group"><label>Category <a href="/admin/packages/categories" style="color:#0A84FF;font-size:11px">(Manage)</a> <a href="javascript:void(0)" onclick="document.getElementById('addCatModal').style.display='flex'" style="color:#4ade80;font-size:11px">+ Add</a></label><select name="type"><?php foreach ($categories as $cat): ?><option value="<?php echo htmlspecialchars($cat->type_key ?? $cat->name, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(($cat->icon ?? '📦') . ' ' . $cat->name, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div>
</div>
<div class="form-group" style="margin-top:12px"><label>Description</label><textarea name="description" rows="2" placeholder="Brief description for internal use..."></textarea></div>
<div class="form-grid cols-2" style="margin-top:12px">
<div class="form-group"><label>Sort Order</label><input name="sort_order" type="number" value="0"></div>
<div class="form-group"></div>
</div>
</div>

<!-- RESOURCE LIMITS -->
<div class="section">
<div class="section-title"><i class="fa-solid fa-server"></i> Resource Limits</div>

<div class="form-grid cols-2">
<div class="form-group"><label>Disk Space (MB)</label><input name="disk_space" type="number" value="5120"><div class="help">1024 MB = 1 GB</div></div>
<div class="form-group"><label>Monthly Bandwidth (MB)</label><input name="bandwidth" type="number" value="51200"><div class="help">10240 MB = 10 GB</div></div>
</div>

<div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,.04)">
<div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px">Account Limits</div>
<div class="form-grid cols-3">
<div class="form-group"><label>Max Domains</label><input name="max_domains" type="number" value="1"></div>
<div class="form-group"><label>Subdomains</label><input name="subdomains" type="number" value="0"></div>
<div class="form-group"><label>Email Accounts</label><input name="email_accounts" type="number" value="5"></div>
<div class="form-group"><label>FTP Accounts</label><input name="ftp_accounts" type="number" value="5"></div>
<div class="form-group"><label>MySQL Databases</label><input name="databases" type="number" value="5"></div>
<div class="form-group"><label>Parked Domains</label><input name="parked_domains" type="number" value="0"></div>
<div class="form-group"><label>Addon Domains</label><input name="addon_domains" type="number" value="0"></div>
</div>
</div>
</div>

<!-- STREAMING LIMITS (only for streaming package types) -->
<div class="section" style="border-color:rgba(10,132,255,.12)">
<div class="section-title"><i class="fa-solid fa-headphones" style="color:#0A84FF"></i> Streaming Limits</div>
<p style="font-size:11px;color:#64748b;margin-bottom:12px">Only applies to Icecast / SHOUTcast package types.</p>
<div class="form-grid cols-4">
<div class="form-group"><label>Listener Limit</label><input name="listener_limit" type="number" value="0"></div>
<div class="form-group"><label>Max Bitrate (kbps)</label><input name="bitrate" type="number" value="0"></div>
<div class="form-group"><label>Storage Limit (GB)</label><input name="storage_limit" type="number" value="0"></div>
<div class="form-group"><label>DJ Accounts</label><input name="dj_accounts" type="number" value="0"></div>
</div>
</div>

<!-- FEATURE LIST -->
<div class="section">
<div class="section-title"><i class="fa-solid fa-puzzle-piece"></i> Feature List</div>
<p style="font-size:11px;color:#64748b;margin-bottom:12px">Controls what the customer can access in their control panel. <a href="/admin/feature-lists" style="color:#0A84FF">Manage feature lists →</a></p>
<div class="form-grid cols-2">
<div class="form-group"><label>Feature List</label>
<select name="feature_list_id">
<option value="">— None (no features) —</option>
<?php foreach ($featureLists as $fl): ?>
<option value="<?php echo $fl->id; ?>"><?php echo htmlspecialchars($fl->name); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="form-group"><label>PHP Version</label><select name="php_version"><option value="8.2">PHP 8.2</option><option value="8.1">PHP 8.1</option><option value="8.0">PHP 8.0</option><option value="7.4">PHP 7.4</option></select></div>
</div>
</div>

<!-- SUBMIT -->
<div style="display:flex;gap:12px;justify-content:end;padding-top:8px">
<a href="/admin/packages" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</a>
<button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Create Package</button>
</div>
</form>

<!-- Inline Add Category Modal -->
<div id="addCatModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.7);align-items:center;justify-content:center">
<div style="background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.2);border-radius:12px;padding:24px;max-width:420px;width:100%">
<h3 style="color:#0A84FF;margin:0 0 16px;font-size:16px">Add Category</h3>
<form method="POST" action="/admin/packages/categories">
<div style="display:grid;gap:10px">
<div><label style="display:block;font-size:11px;color:#94a3b8;margin-bottom:3px;font-weight:600">Name</label><input name="name" required placeholder="e.g. VPS Hosting" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></div>
<div><label style="display:block;font-size:11px;color:#94a3b8;margin-bottom:3px;font-weight:600">Type Key</label><input name="type_key" required placeholder="e.g. vps" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div><label style="display:block;font-size:11px;color:#94a3b8;margin-bottom:3px;font-weight:600">Icon</label><input name="icon" value="📦" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></div>
<div><label style="display:block;font-size:11px;color:#94a3b8;margin-bottom:3px;font-weight:600">Sort</label><input name="sort_order" type="number" value="0" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></div>
</div>
</div>
<div style="display:flex;gap:8px;margin-top:16px">
<button type="submit" class="btn-primary" style="padding:8px 16px;font-size:12px">Save Category</button>
<button type="button" class="btn-secondary" style="padding:8px 16px;font-size:12px" onclick="document.getElementById('addCatModal').style.display='none'">Cancel</button>
</div>
</form>
</div>
</div>

</div>
</body>
</html>
