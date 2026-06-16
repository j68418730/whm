<?php
$userData = isset($user) ? $user : null;
$title = isset($title) ? $title : 'User Panel';
$hostingUser = isset($hosting) ? $hosting : null;
$username = $hostingUser->username ?? ($userData->name ?? 'User');
$userEmail = $hostingUser->email ?? ($userData->email ?? '');
$serverHost = $_SERVER['HTTP_HOST'] ?? 'planet-hosts.com';
$serverIp = $_SERVER['SERVER_ADDR'] ?? '45.61.59.55';
$mainDomain = 'planet-hosts.com';
$userPort = '2082';
$webmailPort = '2096';
?><!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?> - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
.user-shell{display:grid;grid-template-columns:240px 1fr;min-height:calc(100vh - 80px)}
.user-sidebar{background:rgba(8,16,28,.95);border-right:1px solid rgba(0,191,255,.1);padding:20px 12px;overflow-y:auto}
.user-sidebar .user-info{display:flex;align-items:center;gap:10px;padding-bottom:16px;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06)}
.user-sidebar .user-info .avatar{width:40px;height:40px;border-radius:50%;background:var(--accent-gradient);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#fff}
.user-sidebar .user-info .name{font-size:14px;font-weight:600;color:#fff}
.user-sidebar .user-info .email{font-size:11px;color:var(--text-muted)}
.user-sidebar .nav-group{margin-bottom:12px}
.user-sidebar .nav-group .label{font-size:10px;text-transform:uppercase;color:var(--text-muted);letter-spacing:1px;padding:0 10px;margin-bottom:4px}
.user-sidebar a{display:block;padding:8px 12px;border-radius:6px;color:var(--text-secondary);text-decoration:none;font-size:13px;margin-bottom:1px;transition:.15s}
.user-sidebar a:hover{background:rgba(0,191,255,.08);color:#fff}
.user-main{padding:24px 28px;flex:1}
.page-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}
.action-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.1);border-radius:10px;padding:20px;text-align:center;transition:.2s;text-decoration:none;color:#fff;display:block}
.action-card:hover{transform:translateY(-3px);border-color:var(--accent);box-shadow:0 0 20px rgba(0,140,255,.1)}
.action-card .icon{font-size:36px;margin-bottom:8px}
.action-card .name{font-size:14px;font-weight:600}
@media(max-width:768px){.user-shell{grid-template-columns:1fr}.user-sidebar{display:none}}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="grid-overlay"></div>
<header class="header">
<div class="container nav" style="justify-content:space-between">
<div class="logo" style="gap:8px"><img src="/theme/assets/img/logo.png" style="width:36px;height:36px;border-radius:8px"><div><h1 style="font-size:1.1rem">PLANET-<span>HOSTS</span></h1></div></div>
<nav><a href="/">Home</a><a href="/user">Dashboard</a><a href="/user/logout" style="color:#ff6b6b">Logout</a></nav>
</div>
</header>
<div class="user-shell">
<div class="user-sidebar">
<div class="user-info"><div class="avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div><div><div class="name"><?php echo htmlspecialchars($username); ?></div><div class="email"><?php echo htmlspecialchars($userEmail ?: $username); ?></div></div></div>
<div class="nav-group"><div class="label">Home</div><a href="/user">Dashboard</a></div>
<div class="nav-group"><div class="label">Services</div><a href="/user/services">My Services</a><a href="/user/services/web">Web Hosting</a><a href="/user/services/radio">Radio Hosting</a><a href="/user/services/vps">VPS</a><a href="/user/services/domains">Domains</a></div>
<div class="nav-group"><div class="label">Domains</div><a href="/user/domains">Domain List</a><a href="/user/domains/add">Add Domain</a><a href="/user/subdomains">Subdomains</a><a href="/user/redirects">Redirects</a></div>
<div class="nav-group"><div class="label">Email</div><a href="/user/email">Email Accounts</a><a href="http://<?php echo $serverHost; ?>:<?php echo $webmailPort; ?>/" target="_blank">Webmail</a></div>
<div class="nav-group"><div class="label">Management</div><a href="/user/files">File Manager</a><a href="/user/databases">Databases</a><a href="/user/apps/node">Node.js Apps</a><a href="/user/apps/python">Python Apps</a><a href="/user/usage">Resource Usage</a></div>
<div class="nav-group"><div class="label">Support</div><a href="/user/tickets">Support Tickets</a><a href="/user/invoices">Invoices</a></div>
<div class="nav-group"><div class="label">Account</div><a href="/user/profile">Profile</a><a href="/user/security">Security</a></div>
<div class="nav-group" style="margin-top:8px"><a href="/user/logout" style="color:#ff6b6b">Logout</a></div>
</div>
<div class="user-main">
<div class="topbar" style="padding:0 0 16px 0;border-bottom:1px solid rgba(255,255,255,.05);margin-bottom:20px">
<h2 style="font-size:20px"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h2>
</div>
<?php if (isset($_SESSION['success'])): ?><div class="alert alert-success" style="margin-bottom:16px"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div><?php endif; ?>
<?php echo $content ?? ''; ?>
</div>
</div>
<footer class="footer"><div class="container"><div class="footer-logo">PLANET-<span>HOSTS</span></div><p>&copy; 2026 Planet-Hosts.</p></div></footer>
</body>
</html>
