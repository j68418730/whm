<?php
$loggedIn = isset($loggedIn) ? $loggedIn : false;
$user = isset($user) ? $user : null;
$title = isset($title) ? $title : 'Planet Hosts';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
</head>
<body>
<div class="bg-overlay"></div>
<div class="grid-overlay"></div>

<header class="header">
<div class="container nav">
<div class="logo">
<img src="/theme/assets/img/logo.png" alt="logo">
<div>
<h1>PLANET-<span>HOSTS</span></h1>
<p>Hosting Panel</p>
</div>
</div>
<nav>
<a href="/">Home</a>
<a href="/?login#services">Services</a>
<?php if ($loggedIn && $user): ?>
<a href="/admin/dashboard">Dashboard</a>
<a href="/admin/logout" style="color:#ff6b6b">Logout</a>
<?php else: ?>
<a href="/?login">Login</a>
<?php endif; ?>
</nav>
</div>
</header>

<div class="container main-content">
<?php echo $content; ?>
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
