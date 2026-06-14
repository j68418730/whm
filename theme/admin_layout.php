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
<link rel="stylesheet" href="/theme/assets/css/style.css">
</head>
<body>
<div class="bg-overlay"></div>
<div class="grid-overlay"></div>
<div class="admin-shell">
<div class="sidebar">
<div class="logo-text">PLANET <span>HOSTS</span></div>
<div class="nav-section">
<div class="nav-label">Main</div>
<a href="/admin/dashboard">Dashboard</a>
<a href="/admin/server">Server Overview</a>
</div>
<div class="nav-section">
<div class="nav-label">Accounts</div>
<a href="/admin/account">Account Functions</a>
<a href="/admin/packages">Packages</a>
<a href="/admin/reseller">Resellers</a>
<a href="/admin/userfeatures">Feature Manager</a>
</div>
<div class="nav-section">
<div class="nav-label">Services</div>
<a href="/admin/dns">DNS Zones</a>
<a href="/admin/email">Email</a>
<a href="/admin/mysql">Databases</a>
<a href="/admin/ftp">FTP</a>
<a href="/admin/ssl">SSL/TLS</a>
</div>
<div class="nav-section">
<div class="nav-label">Radio</div>
<a href="/admin/streams">Streams</a>
<a href="/admin/radio_dashboard">Radio Dashboard</a>
<a href="/admin/radiosettings">Radio Settings</a>
<a href="/admin/autodj">AutoDJ</a>
<a href="/admin/djs">DJ Accounts</a>
</div>
<div class="nav-section">
<div class="nav-label">System</div>
<a href="/admin/backup">Backups</a>
<a href="/admin/monitoring">Monitoring</a>
<a href="/admin/apache">Apache</a>
<a href="/admin/php">PHP</a>
<a href="/admin/security">Security</a>
<a href="/admin/cron">Cron</a>
</div>
<div class="nav-section" style="margin-top:24px;border-top:1px solid rgba(255,255,255,.06);padding-top:16px">
<a href="/admin/logout" style="color:#ff6b6b">Logout</a>
</div>
</div>
<div class="main">
<div class="topbar">
<h1><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
<?php if ($user): ?>
<div style="display:flex;align-items:center;gap:10px;color:var(--text-secondary);font-size:14px">
<img src="/theme/assets/img/logo.png" style="width:32px;height:32px;border-radius:50%">
<?php echo htmlspecialchars($user->name ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
</div>
<div class="content">
<?php echo $content; ?>
</div>
</div>
</div>
</body>
</html>
