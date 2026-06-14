<?php
$user = isset($user) ? $user : null;
$title = isset($title) ? $title : 'Dashboard';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?> - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
.sidebar-toggle{position:fixed;top:70px;left:10px;z-index:999;background:linear-gradient(135deg,#008cff,#3bb8ff);border:none;color:#fff;width:40px;height:40px;border-radius:8px;cursor:pointer;font-size:20px;display:none;align-items:center;justify-content:center;box-shadow:0 0 15px rgba(0,140,255,.3)}
.sidebar-toggle:hover{transform:scale(1.05)}
@media(max-width:900px){
.sidebar-toggle{display:flex}
.admin-shell{grid-template-columns:1fr}
.sidebar{position:fixed;left:0;top:80px;height:calc(100vh - 80px);z-index:998;transform:translateX(0);transition:transform .3s}
.sidebar.closed{transform:translateX(-105%)}
}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="grid-overlay"></div>

<header class="header">
<div class="container nav" style="justify-content:center">
<nav>
<a href="/">Home</a>
<a href="/admin/dashboard">Dashboard</a>
<a href="/admin/server">Server</a>
<?php if ($user): ?>
<a href="/admin/logout" style="color:#ff6b6b">Logout</a>
<?php endif; ?>
</nav>
</div>
</header>

<button class="sidebar-toggle" id="sidebarToggle" onclick="document.getElementById('adminSidebar').classList.toggle('closed')">☰</button>
<div class="admin-shell">
<div class="sidebar" id="adminSidebar">
<div class="logo-text">PLANET <span>HOSTS</span></div>
<div class="nav-section">
<div class="nav-label">Main</div>
<a href="/admin/dashboard">Dashboard</a>
<a href="/admin/server">Server Overview</a>
<a href="/admin/server/health">Server Health</a>
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
<div class="nav-label">Billing</div>
<a href="/admin/billing">Billing Dashboard</a>
<a href="/admin/billing/products">Products</a>
<a href="/admin/billing/orders">Orders</a>
<a href="/admin/billing/services">Services</a>
<a href="/admin/billing/invoices">Invoices</a>
</div>
<div class="nav-section">
<div class="nav-label">System</div>
<a href="/admin/backup">Backups</a>
<a href="/admin/monitoring">Monitoring</a>
<a href="/admin/apache">Apache</a>
<a href="/admin/php">PHP</a>
<a href="/admin/plugins">Plugins</a>
<a href="/admin/security">Security Center</a>
<a href="/admin/twofactor">Two-Factor Auth</a>
<a href="/admin/roles">Super Admins & Roles</a>
<a href="/admin/api">API Keys</a>
<a href="/admin/cron">Cron</a>
<a href="/admin/todo">ToDo List</a>
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

<footer class="footer">
<div class="container">
<div class="footer-logo">PLANET-<span>HOSTS</span></div>
<p>Building the future of hosting infrastructure.</p>
<div class="footer-links">
<a href="#">Terms</a>
<a href="#">Privacy</a>
<a href="#">Support</a>
<a href="#">API</a>
</div>
<div class="copyright">&copy; 2026 Planet-Hosts. All rights reserved.</div>
</div>
</footer>
</body>
</html>
