<?php
$user = isset($user) ? $user : null;
$title = isset($title) ? $title : 'Dashboard';
$pluginManager = \Core\Application::getInstance()->getPluginManager();
$addons = $pluginManager ? $pluginManager->loadedMetadata() : [];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?> - Planet Hosts</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#000;color:#fff;overflow-x:hidden}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;background-position:center;z-index:-2}
.admin-shell{display:grid;grid-template-columns:260px 1fr;min-height:100vh;position:relative;z-index:1}
.sidebar{background:rgba(8,16,28,.95);border-right:1px solid rgba(0,191,255,.1);padding:24px 16px;position:sticky;top:0;height:100vh;overflow-y:auto}
.sidebar .logo{font-family:'Orbitron',sans-serif;font-size:18px;color:#0A84FF;margin-bottom:28px;padding-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06)}
.sidebar .logo span{color:#fff}
.sidebar .nav-section{margin-bottom:20px}
.sidebar .nav-section .nav-label{font-size:11px;text-transform:uppercase;color:#64748b;letter-spacing:1px;margin-bottom:8px;padding:0 10px}
.sidebar a{display:block;color:#94a3b8;text-decoration:none;padding:10px 14px;border-radius:8px;margin-bottom:2px;font-size:14px;transition:.2s}
.sidebar a:hover{background:rgba(0,191,255,.08);color:#fff}
.sidebar a.active{background:rgba(0,191,255,.12);color:#0A84FF;font-weight:600}
.main{display:flex;flex-direction:column}
.topbar{display:flex;justify-content:space-between;align-items:center;padding:20px 32px;border-bottom:1px solid rgba(255,255,255,.05)}
.topbar h1{font-size:22px;font-weight:700}
.topbar .user-badge{display:flex;align-items:center;gap:10px;color:#94a3b8;font-size:14px}
.content{padding:28px 32px;flex:1}
.card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:24px 28px;margin-bottom:20px}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:24px}
.stat-card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:24px;text-align:center}
.stat-card h3{color:#94a3b8;font-size:13px;margin:0 0 8px;text-transform:uppercase;letter-spacing:.5px}
.stat-card .value{font-size:32px;font-weight:700;color:#fff}
.stat-card .label{color:#64748b;font-size:13px;margin-top:4px}
table{width:100%;border-collapse:collapse;margin:12px 0}
th,td{padding:10px 14px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);font-size:14px}
th{color:#0A84FF;font-weight:600;font-size:13px;text-transform:uppercase;letter-spacing:.5px}
td{color:#cbd5e1}
tr:hover{background:rgba(255,255,255,.02)}
.btn{display:inline-block;padding:10px 20px;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px;transition:.3s;text-decoration:none}
.btn.primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn.primary:hover{transform:translateY(-2px);box-shadow:0 0 20px rgba(0,140,255,.3)}
.btn.secondary{background:rgba(255,255,255,.06);color:#ccc;border:1px solid rgba(255,255,255,.1)}
.btn.secondary:hover{background:rgba(255,255,255,.1)}
.btn.danger{background:rgba(255,50,50,.15);color:#ff6b6b;border:1px solid rgba(255,50,50,.2)}
.btn.danger:hover{background:rgba(255,50,50,.25)}
.btn-sm{padding:6px 14px;font-size:12px}
.status-badge{display:inline-block;padding:3px 10px;border-radius:5px;font-size:12px;font-weight:600}
.status-active,.status-running{background:#1a3a2a;color:#4ade80}
.status-suspended,.status-stopped{background:#3a3a1a;color:#facc15}
.status-terminated,.status-error{background:#3a1a1a;color:#f87171}
.form-group{margin-bottom:16px}
.form-group label{display:block;color:#94a3b8;font-size:13px;font-weight:600;margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:14px;outline:none;box-sizing:border-box;font-family:inherit}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:#0A84FF}
.alert-success{background:rgba(50,255,50,.08);border:1px solid rgba(50,255,50,.2);border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#4ade80;font-size:14px}
.alert-error{background:rgba(255,50,50,.1);border:1px solid rgba(255,50,50,.2);border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#ff6b6b;font-size:14px}
@media(max-width:900px){.admin-shell{grid-template-columns:1fr}.sidebar{display:none}}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="admin-shell">
<div class="sidebar">
<div class="logo">PLANET <span>HOSTS</span></div>
<div class="nav-section">
<div class="nav-label">Main</div>
<a href="/admin/dashboard" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/dashboard') ? 'active' : ''; ?>">Dashboard</a>
<a href="/admin/server" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/server') ? 'active' : ''; ?>">Server Overview</a>
</div>
<div class="nav-section">
<div class="nav-label">Accounts</div>
<a href="/admin/account" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/account') ? 'active' : ''; ?>">Account Functions</a>
<a href="/admin/packages" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/packages') ? 'active' : ''; ?>">Packages</a>
<a href="/admin/reseller" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/reseller') ? 'active' : ''; ?>">Resellers</a>
<a href="/admin/userfeatures" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/userfeatures') ? 'active' : ''; ?>">Feature Manager</a>
</div>
<div class="nav-section">
<div class="nav-label">Services</div>
<a href="/admin/dns" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/dns') ? 'active' : ''; ?>">DNS Zones</a>
<a href="/admin/email" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/email') ? 'active' : ''; ?>">Email</a>
<a href="/admin/mysql" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/mysql') ? 'active' : ''; ?>">Databases</a>
<a href="/admin/ftp" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/ftp') ? 'active' : ''; ?>">FTP</a>
<a href="/admin/ssl" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/ssl') ? 'active' : ''; ?>">SSL/TLS</a>
</div>
<div class="nav-section">
<div class="nav-label">Radio</div>
<a href="/admin/streams" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/streams') ? 'active' : ''; ?>">Streams</a>
<a href="/admin/radio_dashboard" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/radio_dashboard') ? 'active' : ''; ?>">Radio Dashboard</a>
<a href="/admin/radiosettings" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/radiosettings') ? 'active' : ''; ?>">Radio Settings</a>
<a href="/admin/autodj" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/autodj') ? 'active' : ''; ?>">AutoDJ</a>
<a href="/admin/djs" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/djs') ? 'active' : ''; ?>">DJ Accounts</a>
</div>
<div class="nav-section">
<div class="nav-label">System</div>
<a href="/admin/backup" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/backup') ? 'active' : ''; ?>">Backups</a>
<a href="/admin/monitoring" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/monitoring') ? 'active' : ''; ?>">Monitoring</a>
<a href="/admin/apache" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/apache') ? 'active' : ''; ?>">Apache</a>
<a href="/admin/php" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/php') ? 'active' : ''; ?>">PHP</a>
<a href="/admin/security" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/security') ? 'active' : ''; ?>">Security</a>
<a href="/admin/cron" class="<?php echo str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/cron') ? 'active' : ''; ?>">Cron</a>
</div>
<div class="nav-section" style="margin-top:24px;border-top:1px solid rgba(255,255,255,.06);padding-top:16px">
<a href="/admin/logout" style="color:#ff6b6b">Logout</a>
</div>
</div>
<div class="main">
<div class="topbar">
<h1><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
<?php if ($user): ?>
<div class="user-badge"><?php echo htmlspecialchars($user->name ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>
</div>
<div class="content">
<?php echo $content; ?>
</div>
</div>
</div>
</body>
</html>
