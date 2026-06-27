<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Backups - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
body{font-family:Inter,sans-serif;background:#000;color:#fff;margin:0;padding:40px}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.whm-shell{display:grid;grid-template-columns:260px 1fr;min-height:100vh;position:relative;z-index:1}
.card{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:12px;padding:24px 28px;margin-bottom:20px}
h1{color:#0A84FF;margin-top:0}
table{width:100%;border-collapse:collapse;margin:12px 0}
th,td{padding:10px 12px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);font-size:14px}
th{color:#0A84FF;font-weight:600}td{color:#cbd5e1}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:20px}
.stat-card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:20px;text-align:center}
.stat-card h3{color:#94a3b8;font-size:13px;margin:0 0 8px}
.stat-card .value{font-size:28px;font-weight:700;color:#fff}
.stat-card .label{color:#64748b;font-size:13px;margin-top:4px}
.btn{padding:10px 20px;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px;transition:.3s;text-decoration:none;display:inline-block}
.btn.primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn.secondary{background:rgba(255,255,255,.06);color:#ccc;border:1px solid rgba(255,255,255,.1)}
.btn.danger{background:rgba(255,50,50,.15);color:#ff6b6b;border:1px solid rgba(255,50,50,.2)}
.alert-success{background:rgba(50,255,50,.08);border:1px solid rgba(50,255,50,.2);border-radius:8px;padding:12px;color:#4ade80;font-size:14px;margin-bottom:16px}
.sidebar{background:rgba(8,16,28,.95);border-right:1px solid rgba(0,191,255,.1);padding:24px}
.sidebar h2{font-size:16px;color:#0A84FF;margin-bottom:24px}
.sidebar a{display:block;color:#94a3b8;text-decoration:none;padding:10px 14px;border-radius:8px;margin-bottom:4px;font-size:14px}
.sidebar a:hover{background:rgba(0,191,255,.08);color:#fff}
.main{padding:28px}
@media(max-width:768px){.whm-shell{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="whm-shell">
<div class="sidebar">
<h2>Planet Hosts</h2>
<a href="/admin/dashboard">Dashboard</a>
<a href="/admin/account">Accounts</a>
<a href="/admin/packages">Packages</a>
<a href="/admin/dns">DNS</a>
<a href="/admin/server">Server</a>
<a href="/admin/backup" style="color:#fff">Backups</a>
</div>
<div class="main">
<h1>Backup Manager</h1>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="stats-grid">
<div class="stat-card"><h3>Total Backups</h3><div class="value"><?php echo $backupStats['total_backups']; ?></div></div>
<div class="stat-card"><h3>Storage Used</h3><div class="value"><?php echo $backupStats['backup_storage_used']; ?> MB</div></div>
<div class="stat-card"><h3>Last Backup</h3><div class="value" style="font-size:16px"><?php echo $backupStats['last_backup']; ?></div></div>
</div>

<div class="card">
<h2 style="font-size:18px;margin-bottom:16px">Create Backup</h2>
<form method="POST" action="/admin/backup/create" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
<div><label style="display:block;color:#94a3b8;font-size:13px;margin-bottom:4px">Username (optional)</label><input name="username" placeholder="Leave empty for full backup" style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;min-width:250px;outline:none"></div>
<button type="submit" class="btn primary">Create Backup</button>
</form>
</div>

<div class="card" style="background:linear-gradient(135deg,rgba(0,132,255,.08),rgba(0,191,255,.04));border-color:rgba(0,132,255,.25);text-align:center;padding:20px">
<div style="font-size:32px;margin-bottom:8px">🔄</div>
<h2 style="font-size:18px;margin:0 0 4px;color:#0A84FF">Restore Center</h2>
<p style="color:#94a3b8;font-size:13px;margin:0 0 12px">Restore accounts, packages, websites, databases, email, streaming stations, game servers, and more from backups or migrate from other panels.</p>
<a href="/admin/restore-center" class="btn primary">Open Restore Center</a>
<a href="/admin/restore-center/history" class="btn secondary" style="margin-left:8px">View History</a>
</div>

<div class="card">
<table>
<tr><th>Filename</th><th>Date</th><th>Size</th><th>Status</th><th>Actions</th></tr>
<?php if (!empty($backups)): foreach ($backups as $b): ?>
<tr>
<td><?php echo htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo $b['date']; ?></td>
<td><?php echo round($b['size'] / 1024 / 1024, 2); ?> MB</td>
<td><span style="color:#4ade80">Completed</span></td>
<td>
<a href="/admin/backup/restore/<?php echo urlencode($b['name']); ?>" class="btn secondary" style="padding:6px 14px;font-size:12px" onclick="return confirm('Restore?')">Restore</a>
<a href="/admin/backup/delete/<?php echo urlencode($b['name']); ?>" class="btn danger" style="padding:6px 14px;font-size:12px" onclick="return confirm('Delete?')">Delete</a>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5" style="text-align:center;color:#64748b;padding:20px">No backups yet.</td></tr>
<?php endif; ?>
</table>
</div>
</div>
</div>
</body>
</html>
