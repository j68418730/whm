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

<?php
// Live chat & tracking settings
$settings = [];
try {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM automation_settings WHERE setting_key IN ('live_chat_enabled','visitor_tracking_enabled','chat_image_online','chat_image_offline','chat_image_away')");
    while ($r = $stmt->fetch(PDO::FETCH_OBJ)) $settings[$r->setting_key] = $r->setting_value;
} catch (\Exception $e) {}
$liveChat = ($settings['live_chat_enabled'] ?? '1') === '1';
$tracking = ($settings['visitor_tracking_enabled'] ?? '1') === '1';
?>

<footer class="footer">
<div class="container">
<div class="footer-logo">PLANET-<span>HOSTS</span></div>
<p>Building the future of hosting infrastructure.</p>
<div class="footer-links">
<a href="#">Terms</a>
<a href="#">Privacy</a>
<?php if ($liveChat): ?><a href="#" onclick="window.open('https://planet-hosts.com/livechat.php?popup=1','ph_chat','width=400,height=600');return false">Live Chat</a><?php endif; ?>
<a href="#">API</a>
</div>
<div style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(255,255,255,.04)">
<strong style="font-size:13px">Professional Support</strong>
<p style="font-size:11px;color:#94a3b8;margin:4px 0 0">Experienced system administrators available 24/7 via tickets, live chat, and phone.</p>
</div>
<div class="copyright">&copy; 2026 Planet-Hosts. All rights reserved.</div>
</div>
</footer>
<?php if ($tracking): ?>
<script>
(function() {
    var img = new Image();
    img.src = 'https://planet-hosts.com/track.php?id=planethosts&r=' + encodeURIComponent(document.referrer) + '&u=' + encodeURIComponent(window.location.href);
    img.style.display = 'none';
    document.body.appendChild(img);
})();
</script>
<?php endif; ?>
</body>
</html>
