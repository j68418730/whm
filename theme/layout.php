<?php
$loggedIn = isset($loggedIn) ? $loggedIn : false;
$user = isset($user) ? $user : null;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title ?? 'Planet Hosts'; ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
body{font-family:'Inter',sans-serif;background:#000;color:#fff;margin:0;padding:0;overflow-x:hidden}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url('/theme/assets/img/background.png');background-size:cover;background-position:center;z-index:-2}
.header{position:sticky;top:0;z-index:100;padding:16px 0;backdrop-filter:blur(20px);background:rgba(0,0,0,.45);border-bottom:1px solid rgba(0,191,255,.1)}
.container{width:min(1400px,94%);margin:auto}
.nav{display:flex;align-items:center;justify-content:space-between}
.logo{display:flex;align-items:center;gap:14px}
.logo img{width:50px;height:50px;border-radius:12px}
.logo h1{font-family:'Orbitron',sans-serif;font-size:1.5rem;margin:0}
.logo span{color:#0A84FF}
.logo p{color:#94a3b8;font-size:.75rem;letter-spacing:4px;text-transform:uppercase;margin:0}
nav a{color:#fff;text-decoration:none;margin-left:24px;transition:.3s;font-weight:500}
nav a:hover{color:#00BFFF}
.btn{padding:10px 24px;border:none;border-radius:8px;font-weight:700;text-decoration:none;display:inline-block;cursor:pointer;transition:.3s;font-size:14px}
.btn.primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;box-shadow:0 0 20px rgba(0,140,255,.3)}
.btn.primary:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(0,140,255,.5)}
.btn.secondary{background:transparent;border:1px solid rgba(255,255,255,.15);color:#fff}
.btn.secondary:hover{background:rgba(255,255,255,.06)}
.main-content{min-height:60vh;padding:40px 0}
.footer{padding:60px 0;text-align:center;border-top:1px solid rgba(0,191,255,.1)}
.footer h2{font-family:'Orbitron',sans-serif;font-size:1.8rem}
.footer span{color:#0A84FF}
.footer p{color:#94a3b8;margin:12px 0 24px}
.footer-links{margin-bottom:24px}
.footer-links a{color:#fff;text-decoration:none;margin:0 12px;font-size:14px}
.copyright{color:#64748b;font-size:13px}
@media(max-width:768px){
.nav{flex-direction:column;gap:16px}
nav a{margin:0 10px}
}
</style>
</head>
<body>
<div class="bg-overlay"></div>

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
<h2>PLANET-<span>HOSTS</span></h2>
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
