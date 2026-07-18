<?php

$showLogin = isset($_GET['login']);
$loginError = isset($loginError) ? $loginError : null;
$loggedIn = isset($loggedIn) ? $loggedIn : false;
$user = isset($user) ? $user : null;
$packagesByType = isset($packagesByType) ? $packagesByType : [];
// Load reviews from DB
$reviews = [];
try {
    $app = \Core\Application::getInstance();
    $pdo = $app->get('db')->pdo();
    $stmt = $pdo->query("SELECT id,name,rating,text,created_at FROM reviews WHERE approved=1 ORDER BY created_at DESC LIMIT 20");
    $reviews = $stmt->fetchAll(\PDO::FETCH_OBJ);
} catch (\Exception $e) {}
// Load all categories for dynamic footer
$allCategories = [];
try {
    $app = \Core\Application::getInstance();
    $allCategories = $app->get('db')->table('package_categories')->orderBy('sort_order', 'ASC')->get() ?: [];
} catch (\Exception $e) {}
// Auto-load packages from DB if not provided
if (empty($packagesByType)) {
    try {
        $app = \Core\Application::getInstance();
        $allPkgs = $app->get('db')->table('hosting_packages')->where('is_active', 1)->orderBy('sort_order', 'ASC')->get() ?: [];
        foreach ($allPkgs as $pkg) {
            $t = $pkg->type ?? 'Other';
            if (!isset($packagesByType[$t])) $packagesByType[$t] = [];
            $packagesByType[$t][] = $pkg;
        }
    } catch (\Exception $e) {}
}
if ($loggedIn && $user):
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Planet Hosts</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#000;color:#fff}
.dash{display:grid;grid-template-columns:260px 1fr;min-height:100vh}
.sidebar{background:#0b1728;border-right:1px solid rgba(0,212,255,.2);padding:20px}
.sidebar h2{font-size:20px;margin-bottom:30px;color:#00d4ff}
.sidebar a{display:block;color:#c9ddf3;text-decoration:none;padding:12px;border-radius:8px;margin-bottom:4px}
.sidebar a:hover{background:rgba(0,212,255,.12);color:#fff}
.main{padding:30px}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:30px}
.card{background:#0d1b2e;border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:24px}
.card h3{color:#00d4ff;font-size:14px;text-transform:uppercase;margin-bottom:8px}
.card .value{font-size:32px;font-weight:700}
.btn{padding:10px 20px;background:linear-gradient(135deg,#008cff,#3bb8ff);border:none;border-radius:8px;color:#fff;text-decoration:none;font-weight:600}
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="dash">
<div class="sidebar">
<h2>Planet Hosts</h2>
<a href="#">Dashboard</a>
<a href="#">Accounts</a>
<a href="#">Streams</a>
<a href="#">Servers</a>
</div>
<div class="main">
<div class="topbar"><h1>Dashboard</h1><a href="/admin/logout" class="btn">Logout</a></div>
<div class="stats">
<div class="card"><h3>Accounts</h3><div class="value">0</div></div>
<div class="card"><h3>Streams</h3><div class="value">0</div></div>
<div class="card"><h3>Servers</h3><div class="value">0</div></div>
<div class="card"><h3>Uptime</h3><div class="value">99.99%</div></div>
</div>
<p style="color:#94a3b8">Welcome, <?php echo htmlspecialchars($user->name ?? 'User', ENT_QUOTES, 'UTF-8'); ?></p>
</div></div>
<script>var img=new Image();img.src='https://planet-hosts.com/track.php?id=planethosts&r='+encodeURIComponent(document.referrer)+'&u='+encodeURIComponent(location.href);img.style.display='none';document.body.appendChild(img);</script>
</body></html>
<?php
exit;
endif;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Planet Hosts - Premium Web Hosting &amp; Radio Streaming Solutions</title>
<meta name="description" content="Planet Hosts offers premium web hosting, SHOUTcast/Icecast radio streaming, reseller hosting, VPS, dedicated servers, and a powerful WHM control panel with 24/7 support.">
<meta name="keywords" content="web hosting, radio streaming, SHOUTcast, Icecast, reseller hosting, VPS, dedicated servers, WHM, cPanel">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{background:#020817;color:#fff;font-family:'Inter',sans-serif;overflow-x:hidden;position:relative}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;background-position:center;z-index:-2}
.grid-overlay{position:fixed;inset:0;background-image:linear-gradient(rgba(0,140,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(0,140,255,.04) 1px,transparent 1px);background-size:80px 80px;z-index:-1;opacity:.35}
.container{width:min(1400px,94%);margin:auto}
.section{padding:80px 0}
.section-title{text-align:center;margin-bottom:50px}
.section-title h2{font-size:2.4rem;margin-bottom:12px}
.section-title h2 span{color:#0A84FF}
.section-title p{color:#94a3b8;font-size:1.05rem;max-width:600px;margin:auto}
.btn{display:inline-block;padding:12px 28px;border-radius:12px;text-decoration:none;transition:.3s;font-weight:600;font-size:14px;cursor:pointer;border:none;font-family:'Inter',sans-serif}
.btn-primary{background:linear-gradient(135deg,#0A84FF,#00E5FF);box-shadow:0 0 25px rgba(0,191,255,.3);color:#fff}
.btn-primary:hover{transform:translateY(-3px);box-shadow:0 0 35px rgba(0,191,255,.4)}
.btn-secondary{border:1px solid rgba(0,191,255,.2);background:rgba(255,255,255,.03);color:#fff}
.btn-secondary:hover{transform:translateY(-3px);border-color:#0A84FF}
.btn-lg{padding:16px 36px;font-size:16px}
/* HEADER */
.header{position:sticky;top:0;z-index:100;backdrop-filter:blur(12px);background:rgba(2,8,23,.7);border-bottom:1px solid rgba(0,191,255,.08)}
.header-inner{display:flex;align-items:center;justify-content:space-between;padding:14px 0}
.logo{display:flex;align-items:center;gap:14px;text-decoration:none}
.logo img{width:55px;height:55px;border-radius:12px}
.logo-text{font-family:'Orbitron',sans-serif;font-size:1.4rem;font-weight:700;color:#fff}
.logo-text span{color:#0A84FF}
.logo-sub{color:#94a3b8;font-size:.7rem;letter-spacing:3px;text-transform:uppercase;margin-top:-2px}
.nav-links{display:flex;align-items:center;gap:6px}
.nav-links a{color:#cbd5e1;text-decoration:none;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:500;transition:.2s}
.nav-links a:hover{color:#fff;background:rgba(0,191,255,.06)}
.nav-links .btn-order{background:linear-gradient(135deg,#0A84FF,#00E5FF);color:#fff;padding:10px 20px}
.nav-links .btn-order:hover{transform:translateY(-2px);box-shadow:0 0 20px rgba(0,191,255,.3)}
.nav-toggle{display:none;background:none;border:none;color:#fff;font-size:24px;cursor:pointer;padding:8px}
/* HERO */
.hero{min-height:90vh;display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:60px;padding:100px 0 60px}
.hero-text h1{font-size:3.8rem;line-height:1.15;margin-bottom:20px}
.hero-text h1 span{color:#0A84FF}
.hero-text p{color:#94a3b8;font-size:1.1rem;line-height:1.9;margin-bottom:30px;max-width:600px}
.hero-buttons{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:40px}
.hero-stats{display:flex;gap:20px;flex-wrap:wrap}
.hero-stat{text-align:center;padding:20px 28px;background:rgba(8,16,28,.8);border:1px solid rgba(0,191,255,.1);border-radius:14px;min-width:150px}
.hero-stat h3{font-size:2rem;color:#0A84FF;margin-bottom:4px}
.hero-stat p{color:#94a3b8;font-size:13px;margin:0}
.hero-image img{width:100%;border-radius:20px;border:1px solid rgba(0,191,255,.15);box-shadow:0 0 60px rgba(0,191,255,.12)}
/* SERVICES GRID */
/* FEATURES GRID */
.features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px}
.feature-card{background:rgba(8,16,28,.8);border:1px solid rgba(0,191,255,.08);border-radius:14px;padding:28px;text-align:center;transition:.3s}
.feature-card:hover{transform:translateY(-4px);border-color:#0A84FF}
.feature-card .icon{font-size:2rem;margin-bottom:14px;display:block}
.feature-card h4{font-size:15px;margin-bottom:8px}
.feature-card p{color:#94a3b8;font-size:13px;margin:0;line-height:1.6}
/* STATISTICS BANNER */
.stat-banner{background:linear-gradient(135deg,rgba(0,140,255,.08),rgba(0,229,255,.04));border:1px solid rgba(0,191,255,.1);border-radius:20px;padding:50px 40px;display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:24px;text-align:center}
.stat-item h3{font-size:2.2rem;color:#0A84FF;margin-bottom:6px}
.stat-item p{color:#94a3b8;font-size:13px;margin:0}
/* WHY CHOOSE */
.why-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px}
.why-card{background:rgba(8,16,28,.8);border:1px solid rgba(0,191,255,.08);border-radius:14px;padding:28px;text-align:center;transition:.3s}
.why-card:hover{transform:translateY(-4px);border-color:#0A84FF}
.why-card .icon{font-size:2rem;margin-bottom:14px;display:block}
.why-card h4{font-size:15px;margin-bottom:8px}
.why-card p{color:#94a3b8;font-size:13px;margin:0;line-height:1.6}
/* TESTIMONIALS */
.testimonial-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px}
.testimonial-card{background:rgba(8,16,28,.8);border:1px solid rgba(0,191,255,.1);border-radius:14px;padding:28px}
.testimonial-card .stars{color:#facc15;margin-bottom:10px;font-size:14px}
.testimonial-card blockquote{color:#cbd5e1;font-size:13px;line-height:1.8;margin-bottom:16px;font-style:italic}
.testimonial-card .author{display:flex;align-items:center;gap:12px}
.testimonial-card .author .avatar{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0}
.testimonial-card .author .name{font-weight:600;font-size:13px}
.testimonial-card .author .role{font-size:11px;color:#64748b}
/* FLOATING CHAT - BIGGER */
.floating-chat{display:none}
/* TESTIMONIAL SCROLL */
.testimonial-scroll-wrap{position:relative;margin-top:24px;display:flex;align-items:center;gap:10px}
.testimonial-scroll{display:flex;gap:16px;overflow-x:auto;padding:8px 4px;scroll-snap-type:x mandatory;scrollbar-width:thin;scrollbar-color:rgba(0,140,255,.3) transparent;flex:1}
.testimonial-scroll::-webkit-scrollbar{height:5px}
.testimonial-scroll::-webkit-scrollbar-track{background:transparent}
.testimonial-scroll::-webkit-scrollbar-thumb{background:rgba(0,140,255,.3);border-radius:4px}
.testimonial-scroll-card{min-width:300px;max-width:320px;background:rgba(8,16,28,.8);border:1px solid rgba(0,191,255,.1);border-radius:14px;padding:24px;scroll-snap-align:start;flex-shrink:0}
.testimonial-scroll-card .stars{color:#facc15;margin-bottom:8px;font-size:13px}
.testimonial-scroll-card blockquote{color:#cbd5e1;font-size:12px;line-height:1.7;margin-bottom:14px;font-style:italic}
.testimonial-scroll-card .author{display:flex;align-items:center;gap:10px}
.testimonial-scroll-card .author .avatar{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0}
.testimonial-scroll-card .author .name{font-weight:600;font-size:12px}
.testimonial-scroll-card .author .role{font-size:10px;color:#64748b}
.scroll-arrow{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.15);color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:.2s}
.scroll-arrow:hover{background:#0A84FF;border-color:#0A84FF}
@media(max-width:768px){.testimonial-scroll-wrap{flex-direction:column}.scroll-arrow{display:none}}

.floating-chat-panel{position:fixed;bottom:260px;right:30px;width:420px;max-width:92vw;background:#0b1728;border:1px solid rgba(0,191,255,.15);border-radius:16px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,.5);display:none;z-index:9998}
.floating-chat-panel.open{display:block}
.floating-chat-panel .panel-header{background:linear-gradient(135deg,#0A84FF,#00E5FF);padding:18px 20px;display:flex;align-items:center;gap:12px}
.floating-chat-panel .panel-header h4{font-size:16px;margin:0;color:#fff}
.floating-chat-panel .panel-header p{font-size:12px;color:rgba(255,255,255,.8);margin:0}
.floating-chat-panel .panel-body{padding:20px}
.floating-chat-panel .panel-body input,.floating-chat-panel .panel-body textarea{width:100%;padding:12px 14px;margin-bottom:10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-family:'Inter',sans-serif;font-size:13px;outline:none;box-sizing:border-box}
.floating-chat-panel .panel-body input:focus,.floating-chat-panel .panel-body textarea:focus{border-color:#0A84FF}
.floating-chat-panel .panel-body textarea{resize:vertical;min-height:80px}
/* FOOTER */
.footer{background:rgba(2,8,23,.9);border-top:1px solid rgba(0,191,255,.08);padding:60px 0 30px}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:40px;margin-bottom:40px}
.footer-brand h3{font-family:'Orbitron',sans-serif;font-size:1.3rem;margin-bottom:12px}
.footer-brand h3 span{color:#0A84FF}
.footer-brand p{color:#94a3b8;font-size:13px;line-height:1.7;margin-bottom:16px}
.footer-brand .social-links{display:flex;gap:10px}
.footer-brand .social-links a{width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);display:flex;align-items:center;justify-content:center;color:#94a3b8;text-decoration:none;transition:.2s}
.footer-brand .social-links a:hover{color:#0A84FF;border-color:#0A84FF}
.footer-col h4{font-size:14px;margin-bottom:16px;color:#fff}
.footer-col a{display:block;color:#94a3b8;text-decoration:none;font-size:13px;padding:4px 0;transition:.2s}
.footer-col a:hover{color:#0A84FF}
.footer-review-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:rgba(0,140,255,.08);border:1px solid rgba(0,191,255,.15);border-radius:8px;color:#0A84FF;text-decoration:none;font-size:12px;font-weight:600;margin-top:8px;transition:.2s}
.footer-review-btn:hover{background:rgba(0,140,255,.15);color:#fff}
.footer-bottom{border-top:1px solid rgba(255,255,255,.04);padding-top:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
.footer-bottom p{color:#64748b;font-size:12px;margin:0}
.footer-bottom a{color:#64748b;font-size:12px;text-decoration:none;margin-left:16px}
.footer-bottom a:hover{color:#0A84FF}
/* RESPONSIVE */
@media(max-width:992px){
.hero{grid-template-columns:1fr;text-align:center;padding:60px 0 40px}
.hero-text p{margin-left:auto;margin-right:auto}
.hero-buttons,.hero-stats{justify-content:center}
.hero-text h1{font-size:2.8rem}
.nav-links{display:none}
.nav-links.open{display:flex;flex-direction:column;position:absolute;top:100%;left:0;right:0;background:rgba(2,8,23,.98);padding:16px;border-bottom:1px solid rgba(0,191,255,.08);gap:4px}
.nav-toggle{display:block}
.header-inner{position:relative}
.footer-grid{grid-template-columns:1fr 1fr}
}
@media(max-width:768px){
.hero-text h1{font-size:2.2rem}
.section-title h2{font-size:1.8rem}
.section{padding:50px 0}
.footer-grid{grid-template-columns:1fr}
.floating-chat-panel{width:calc(100vw - 40px);right:20px;bottom:180px}
.floating-chat .support-badge-img{width:260px}
.floating-chat .chat-bubble{width:70px;height:70px;font-size:28px}
}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="grid-overlay"></div>

<!-- ===== HEADER ===== -->
<header class="header">
<div class="container header-inner">
<a href="/" class="logo">
<img src="/theme/assets/img/logo.png" alt="Planet Hosts">
<div>
<div class="logo-text">PLANET-<span>HOSTS</span></div>
<div class="logo-sub">Hosting Panel</div>
</div>
</a>
<button class="nav-toggle" onclick="document.querySelector('.nav-links').classList.toggle('open')"><i class="fa-solid fa-bars"></i></button>
<nav class="nav-links">
<a href="/">Home</a>
<a href="#services">Web Hosting</a>
<a href="#services">Reseller</a>
<a href="#services">ShoutCast</a>
<a href="#services">Website Builder</a>
<a href="#support">Support</a>
<a href="?contact">Contact</a>
<a href="http://45.61.59.55:2082/" class="btn-secondary" style="padding:8px 16px;font-size:13px"><i class="fa-solid fa-user"></i> Client Login</a>
<a href="/cart.php" class="btn-primary btn-order" style="padding:8px 20px;font-size:13px"><i class="fa-solid fa-cart-plus"></i> Cart</a>
</nav>
</div>
</header>

<!-- ===== HERO ===== -->
<section class="hero container">
<div class="hero-text">
<h1>Premium Hosting &amp; Radio Streaming<br><span>All in One Platform</span></h1>
<p>Planet Hosts delivers powerful web hosting, SHOUTcast/Icecast radio streaming, reseller solutions, and a complete WHM control panel. Built for performance, backed by 24/7 support.</p>
<div class="hero-buttons">
<a href="#packages" class="btn btn-primary btn-lg"><i class="fa-solid fa-layer-group"></i> View Packages</a>
<a href="?login" class="btn btn-secondary btn-lg"><i class="fa-solid fa-cart-plus"></i> Order Hosting</a>
<a href="http://45.61.59.55:2082/" class="btn btn-secondary btn-lg"><i class="fa-solid fa-gauge-high"></i> Client Area</a>
</div>
<div class="hero-stats">
<div class="hero-stat"><h3>99.9%</h3><p>Uptime Guarantee</p></div>
<div class="hero-stat"><h3>24/7</h3><p>Expert Support</p></div>
<div class="hero-stat"><h3>15+</h3><p>Years Experience</p></div>
</div>
</div>
<div class="hero-image">
<img src="/theme/assets/img/dashboard.png" alt="Planet Hosts Control Panel Dashboard">
</div>
</section>

<!-- ===== FEATURES ===== -->
<section class="section" style="background:rgba(8,16,28,.3);border-top:1px solid rgba(0,191,255,.06);border-bottom:1px solid rgba(0,191,255,.06)">
<div class="container">
<div class="section-title">
<h2>Why <span>Planet Hosts</span></h2>
<p>Industry-leading features included with every plan</p>
</div>
<div class="features-grid">
<div class="feature-card"><span class="icon"><i class="fa-solid fa-database"></i></span><h4>SSD Storage</h4><p>Lightning-fast NVMe SSD drives on all servers for maximum read/write performance and reliability.</p></div>
<div class="feature-card"><span class="icon"><i class="fa-solid fa-lock"></i></span><h4>Free SSL Certificates</h4><p>Auto-installed Let's Encrypt SSL certificates on all hosted domains. Full HTTPS encryption included.</p></div>
<div class="feature-card"><span class="icon"><i class="fa-solid fa-chart-line"></i></span><h4>99.9% Uptime SLA</h4><p>Enterprise-grade infrastructure with redundant power, network, and hardware guarantees.</p></div>
<div class="feature-card"><span class="icon"><i class="fa-solid fa-shield-halved"></i></span><h4>DDoS Protection</h4><p>Advanced mitigation filtering at network edge. Stay online during Layer 3/4/7 attacks.</p></div>
<div class="feature-card"><span class="icon"><i class="fa-solid fa-clock-rotate-left"></i></span><h4>Daily Backups</h4><p>Automated daily backups with 7-day retention. One-click restore from your control panel.</p></div>
<div class="feature-card"><span class="icon"><i class="fa-solid fa-rocket"></i></span><h4>One-Click Installs</h4><p>WordPress, Joomla, Laravel, and 400+ apps installable in seconds from your dashboard.</p></div>
<div class="feature-card"><span class="icon"><i class="fa-solid fa-headset"></i></span><h4>24/7 Support</h4><p>Expert technical support available 24 hours a day via tickets, live chat, and knowledgebase.</p></div>
<div class="feature-card"><span class="icon"><i class="fa-solid fa-gauge-high"></i></span><h4>WHM/cPanel</h4><p>Industry-standard control panel with full account, DNS, email, database, and security management.</p></div>
</div>
</div>
</section>

<!-- ===== PACKAGES - ROTATING CARDS ===== -->
<section class="section" id="packages">
<div class="container">
<div class="section-title">
<h2>Simple <span>Pricing</span></h2>
<p>Choose the plan that fits your needs. All plans include our full WHM panel.</p>
</div>
<?php if (!empty($packagesByType)):
$catIcons = [
    'web_hosting'=>'🌐', 'Web Hosting'=>'🌐',
    'web_reseller'=>'🏢', 'Web Hosting Reseller'=>'🏢',
    'shoutcast'=>'📡', 'SHOUTcast'=>'📡',
    'icecast'=>'🎵', 'Icecast Streaming'=>'🎵',
    'icecast_reseller'=>'🎵', 'Icecast Reseller'=>'🎵',
    'vps'=>'🖥', 'VPS Servers'=>'🖥',
    'dedicated'=>'🔧', 'Dedicated Servers'=>'🔧',
    'game_server'=>'🎮', 'Game Servers'=>'🎮',
];
?>
<style>
.pricing-rotate-col{background:rgba(8,16,28,.4);border:1px solid rgba(0,191,255,.08);border-radius:14px;padding:18px;transition:.3s}
.pricing-rotate-col:hover{border-color:rgba(0,191,255,.18);background:rgba(8,16,28,.6)}
.pricing-rotate{position:relative;min-height:320px}
.pkg-rotate{display:none;border:1px solid rgba(0,191,255,.1);border-radius:10px;padding:20px;background:rgba(0,0,0,.2)}
.pkg-rotate.active{display:block}
.pkg-rotate ul{list-style:none;padding:0;margin:12px 0 16px}
.pkg-rotate ul li{padding:6px 0;display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;border-bottom:1px solid rgba(255,255,255,.04)}
.pkg-rotate ul li i{color:#4ade80;font-size:11px;width:16px}
.pkg-rotate .price{font-size:28px;font-weight:800;margin-bottom:4px;color:#0A84FF}
.pkg-rotate .price span{font-size:14px;font-weight:400;color:#64748b}
.pkg-rotate h3{font-size:18px;font-weight:700;margin-bottom:4px}
.pkg-rotate .subtitle{color:#64748b;font-size:13px;margin-bottom:12px}
.pkg-rotate .btn{display:block;text-align:center;padding:12px;background:linear-gradient(135deg,#008cff,#3bb8ff);border-radius:10px;color:#fff;text-decoration:none;font-weight:600;font-size:14px;transition:.3s}
.pkg-rotate .btn:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(0,140,255,.3)}
.pkg-rotate .btn-outline{border:1px solid rgba(0,191,255,.15);background:transparent;color:#e0e0e0;margin-top:6px;padding:8px;font-size:12px;display:block;text-align:center;border-radius:8px;text-decoration:none;transition:.2s}
.pkg-rotate .btn-outline:hover{background:rgba(0,140,255,.08);border-color:rgba(0,140,255,.3)}
.review-dots{display:flex;justify-content:center;gap:6px;margin-top:10px}
.review-dots .dot{width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,.15);cursor:pointer;transition:.3s;flex-shrink:0}
.review-dots .dot.active{background:#008cff;width:18px;border-radius:4px}
.review-nav{display:flex;justify-content:center;gap:8px;margin-top:10px}
.review-nav button{background:rgba(0,140,255,.08);border:1px solid rgba(0,191,255,.12);border-radius:8px;padding:6px 14px;color:#e0e0e0;cursor:pointer;font-size:12px;transition:.3s;font-family:'Inter',sans-serif}
.review-nav button:hover{background:rgba(0,140,255,.15)}
.pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-top:20px}
@media(max-width:768px){.pricing-grid{grid-template-columns:1fr}}
</style>
<div class="pricing-grid" id="pricingGrid">
<?php foreach ($packagesByType as $type => $pkgs):
$icon = $catIcons[$type] ?? '📦';
$label = ucwords(str_replace(['_','-'],' ',$type));
$pkgCount = count($pkgs);
?>
<div class="pricing-rotate-col">
<div style="text-align:center;margin-bottom:10px">
<span style="font-size:28px"><?php echo $icon; ?></span>
<h3 style="font-size:16px;margin:4px 0 2px"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></h3>
<div style="font-size:11px;color:#64748b"><?php echo $pkgCount; ?> plan<?php if ($pkgCount > 1) echo 's'; ?></div>
</div>
<div class="pricing-rotate" id="prota-<?php echo $type; ?>">
<?php foreach ($pkgs as $i => $pkg): ?>
<div class="pkg-rotate<?php if ($i === 0) echo ' active'; ?>" data-type="<?php echo $type; ?>" data-index="<?php echo $i; ?>">
<h3><?php echo htmlspecialchars($pkg->name, ENT_QUOTES, 'UTF-8'); ?></h3>
<div class="subtitle"><?php echo htmlspecialchars($pkg->description ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
<div class="price">$<?php echo number_format((float)($pkg->monthly_price ?? $pkg->price ?? 0), 2); ?><span>/mo</span></div>
<ul>
<?php $pf = is_string($pkg->features ?? null) ? json_decode($pkg->features, true) ?? [] : ($pkg->features ?? []); $sp = $pf['streaming_package'] ?? []; $gp = $pf['game_package'] ?? []; ?>
<?php if (!empty($pkg->disk_space) && $pkg->disk_space > 0): ?><li><i class="fa-solid fa-check"></i> <?php echo $pkg->disk_space; ?> GB Disk</li><?php endif; ?>
<?php if (!empty($pkg->bandwidth) && $pkg->bandwidth > 0): ?><li><i class="fa-solid fa-check"></i> <?php echo $pkg->bandwidth; ?> GB Bandwidth</li><?php endif; ?>
<?php if (!empty($sp['max_listeners'])): ?><li><i class="fa-solid fa-check"></i> <?php echo $sp['max_listeners']; ?> Listeners</li><?php endif; ?>
<?php if (!empty($sp['max_bitrate'])): ?><li><i class="fa-solid fa-check"></i> <?php echo $sp['max_bitrate']; ?> kbps</li><?php endif; ?>
<?php if (!empty($sp['upload_limit'])): ?><li><i class="fa-solid fa-check"></i> <?php echo $sp['upload_limit']; ?> MB Upload</li><?php endif; ?>
<?php if (!empty($pkg->email_accounts) && $pkg->email_accounts > 0): ?><li><i class="fa-solid fa-check"></i> <?php echo $pkg->email_accounts; ?> Emails</li><?php endif; ?>
<?php if (!empty($pkg->databases) && $pkg->databases > 0): ?><li><i class="fa-solid fa-check"></i> <?php echo $pkg->databases; ?> Databases</li><?php endif; ?>
<?php if (!empty($pkg->subdomains) && $pkg->subdomains > 0): ?><li><i class="fa-solid fa-check"></i> <?php echo $pkg->subdomains; ?> Subdomains</li><?php endif; ?>
<?php if (!empty($pkg->addon_domains) && $pkg->addon_domains > 0): ?><li><i class="fa-solid fa-check"></i> <?php echo $pkg->addon_domains; ?> Addon Domains</li><?php endif; ?>
<?php if (!empty($sp['max_djs'])): ?><li><i class="fa-solid fa-check"></i> <?php echo $sp['max_djs']; ?> DJ Accounts</li><?php endif; ?>
<li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=<?php echo (int)$pkg->id; ?>&name=<?php echo urlencode($pkg->name ?? ''); ?>&price=<?php echo (float)($pkg->monthly_price ?? $pkg->price ?? 0); ?>" class="btn">Order Now →</a>
<a href="/product/<?php echo (int)$pkg->id; ?>" class="btn-outline">Read More →</a>
</div>
<?php endforeach; ?>
</div>
<div class="review-dots" id="pdots-<?php echo $type; ?>">
<?php foreach ($pkgs as $i => $pkg): ?>
<span class="dot<?php if ($i === 0) echo ' active'; ?>" onclick="showPkg(<?php echo $i; ?>, '<?php echo $type; ?>')"></span>
<?php endforeach; ?>
</div>
<div class="review-nav" style="margin-top:8px">
<button onclick="prevPkg('<?php echo $type; ?>')">← Prev</button>
<button onclick="nextPkg('<?php echo $type; ?>')">Next →</button>
</div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p style="text-align:center;color:#94a3b8">No packages available yet. <a href="?login" style="color:#0A84FF">Contact us</a> for custom plans.</p>
<?php endif; ?>
</div>
</section>

<!-- ===== WHY CHOOSE ===== -->
<section class="section">
<div class="container">
<div class="section-title">
<h2>Why Choose <span>Planet Hosts</span></h2>
<p>We stand out from the competition</p>
</div>
<div class="why-grid">
<div class="why-card"><span class="icon"><i class="fa-solid fa-server"></i></span><h4>Reliable Infrastructure</h4><p>Enterprise hardware with redundant power, networking, and cooling. Your sites stay online.</p></div>
<div class="why-card"><span class="icon"><i class="fa-solid fa-gauge-high"></i></span><h4>Fast SSD Servers</h4><p>NVMe SSD storage on all plans with optimized server stacks for maximum performance.</p></div>
<div class="why-card"><span class="icon"><i class="fa-solid fa-tag"></i></span><h4>Affordable Pricing</h4><p>Competitive pricing without hidden fees. Get enterprise features at budget-friendly rates.</p></div>
<div class="why-card"><span class="icon"><i class="fa-solid fa-headset"></i></span><h4>Professional Support</h4><p>Experienced system administrators available 24/7 via tickets, live chat, and phone.</p></div>
<div class="why-card"><span class="icon"><i class="fa-solid fa-sliders"></i></span><h4>Powerful Panel</h4><p>Complete WHM control panel with integrated Icecast, billing, chat, and game server management.</p></div>
</div>
</div>
</section>

<!-- ===== TESTIMONIALS (DB-driven) ===== -->
<section class="section" style="background:rgba(8,16,28,.3);border-top:1px solid rgba(0,191,255,.06);border-bottom:1px solid rgba(0,191,255,.06)">
<div class="container">
<div class="section-title">
<h2>What Our <span>Clients Say</span></h2>
<p>Real reviews from real customers</p>
</div>
<?php if (!empty($reviews)):
$rv_count = count($reviews);
$rv_limit = 4;
$rv_main = array_slice($reviews, 0, $rv_limit);
$rv_scroll = array_slice($reviews, $rv_limit);
?>
<div class="testimonial-grid">
<?php foreach ($rv_main as $rv):
$initials2 = strtoupper(substr($rv->name, 0, 1));
$colors2 = ['#008cff,#00e5ff','#a855f7,#d946ef','#10b981,#34d399','#f59e0b,#f97316','#ec4899,#f43f5e','#6366f1,#818cf8'];
$c2 = $colors2[$rv->id % 6];
$rating2 = (int)($rv->rating ?? 5);
$safeName2 = htmlspecialchars($rv->name, ENT_QUOTES, 'UTF-8');
$safeText2 = htmlspecialchars($rv->text ?? '', ENT_QUOTES, 'UTF-8');
?>
<div class="testimonial-card">
<div class="stars"><?php for ($s=0;$s<5;$s++): ?><?php if ($rating2 > $s): ?><i class="fa-solid fa-star"></i><?php else: ?><i class="fa-regular fa-star"></i><?php endif; ?><?php endfor; ?></div>
<blockquote>"<?php echo $safeText2; ?>"</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,<?php echo $c2; ?>);color:#fff"><?php echo $initials2; ?></div><div><div class="name"><?php echo $safeName2; ?></div><div class="role">Verified Client</div></div></div>
</div>
<?php endforeach; ?>
</div>
<?php if (!empty($rv_scroll)): ?>
<div class="testimonial-scroll-wrap">
<button class="scroll-arrow left" onclick="scrollTestimonials(-1)"><i class="fa-solid fa-chevron-left"></i></button>
<div class="testimonial-scroll" id="testimonialScroll">
<?php foreach ($rv_scroll as $rv):
$initials3 = strtoupper(substr($rv->name, 0, 1));
$c3 = $colors2[$rv->id % 6];
$rating3 = (int)($rv->rating ?? 5);
$safeName3 = htmlspecialchars($rv->name, ENT_QUOTES, 'UTF-8');
$safeText3 = htmlspecialchars($rv->text ?? '', ENT_QUOTES, 'UTF-8');
?>
<div class="testimonial-scroll-card">
<div class="stars"><?php for ($s=0;$s<5;$s++): ?><?php if ($rating3 > $s): ?><i class="fa-solid fa-star"></i><?php else: ?><i class="fa-regular fa-star"></i><?php endif; ?><?php endfor; ?></div>
<blockquote>"<?php echo $safeText3; ?>"</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,<?php echo $c3; ?>);color:#fff"><?php echo $initials3; ?></div><div><div class="name"><?php echo $safeName3; ?></div><div class="role">Verified Client</div></div></div>
</div>
<?php endforeach; ?>
</div>
<button class="scroll-arrow right" onclick="scrollTestimonials(1)"><i class="fa-solid fa-chevron-right"></i></button>
</div>
<?php endif; ?>
<?php else: ?>
<div class="testimonial-grid">
<div class="testimonial-card">
<div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
<blockquote>"Outstanding support team. They helped configure our Icecast settings within minutes. Best hosting experience we have ever had."</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,#008cff,#00e5ff);color:#fff">D</div><div><div class="name">David P.</div><div class="role">Radio Station Owner</div></div></div>
</div>
<div class="testimonial-card">
<div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
<blockquote>"The WHM panel combined with Icecast streaming is exactly what we needed. AutoDJ and the widget generator are game changers."</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,#a855f7,#d946ef);color:#fff">S</div><div><div class="name">Sarah M.</div><div class="role">Web Hosting Client</div></div></div>
</div>
<div class="testimonial-card">
<div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
<blockquote>"Moved our entire infrastructure to Planet-Hosts. Uptime has been flawless, support is instant. Highly recommend for radio hosting."</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,#10b981,#34d399);color:#fff">J</div><div><div class="name">Jamie R.</div><div class="role">Radio Broadcaster</div></div></div>
</div>
</div>
<?php endif; ?>
</div>
</section><!-- ===== FLOATING CHAT ===== -->
<div class="floating-chat" id="floatingChat" onclick="toggleChatPanel()">
<img src="/theme/assets/img/livechat/live-online-2.png" alt="Live Support" class="support-badge-img" id="supportBadge" style="width:240px;height:256px;object-fit:contain">
</div>

<div class="floating-chat-panel" id="chatPanel">
<div class="panel-header">
<img src="/theme/assets/img/livechat/live-online-2.png" id="panelBadgeImg" style="height:256px;width:auto;object-fit:contain">
<div><h4 id="panelTitle">Live Support</h4><p id="panelStatus">Checking support status...</p></div>
<button onclick="toggleChatPanel()" style="margin-left:auto;background:none;border:none;color:#fff;font-size:22px;cursor:pointer">&times;</button>
</div>
<div class="panel-body" id="panelBody">
<input type="hidden" id="chatSessionId" value="0">
<div id="chatMessages" style="max-height:280px;overflow-y:auto;margin-bottom:12px;display:none"></div>
<div id="chatForm">
<input type="text" id="chatName" placeholder="Your Name" maxlength="50">
<input type="email" id="chatEmail" placeholder="Your Email">
<div style="display:flex;gap:8px">
<textarea id="chatMessage" placeholder="Your Question (required)" style="flex:1;min-height:60px"></textarea>
<button class="btn btn-primary" style="padding:12px 20px;flex-shrink:0;align-self:flex-end" onclick="sendChatMessage()">Send</button>
</div>
</div>
</div>
</div>

<!-- ===== FOOTER ===== -->
<footer class="footer">
<div class="container">
<div class="footer-grid">
<div class="footer-brand">
<h3>PLANET-<span>HOSTS</span></h3>
<p>Premium web hosting, radio streaming, and server solutions. The complete hosting platform with WHM control panel, Icecast streaming, and 24/7 expert support.</p>
<div class="social-links">
<a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
<a href="#" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
<a href="#" aria-label="Discord"><i class="fa-brands fa-discord"></i></a>
<a href="#" aria-label="GitHub"><i class="fa-brands fa-github"></i></a>
</div>
</div>
<div class="footer-col">
<h4>Quick Links</h4>
<a href="/">Home</a>
<a href="#packages">Web Hosting</a>
<a href="#packages">Reseller Hosting</a>
<a href="#packages">SHOUTcast Hosting</a>
<a href="#services">Website Builder</a>
<a href="?contact">Contact Us</a>
</div>
<div class="footer-col">
<h4>Hosting Services</h4>
<?php if (!empty($allCategories)): ?>
<?php foreach ($allCategories as $cat): ?>
<?php $catName = $cat->name ?? $cat; ?>
<a href="/hosting/<?php echo htmlspecialchars(urlencode($catName), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', $catName)), ENT_QUOTES, 'UTF-8'); ?></a>
<?php endforeach; ?>
<?php else: ?>
<a href="#packages">Shared Hosting</a>
<a href="#packages">Reseller Plans</a>
<a href="#packages">Radio Streaming</a>
<a href="#packages">Icecast Hosting</a>
<a href="#packages">VPS Servers</a>
<a href="#packages">Dedicated Servers</a>
<?php endif; ?>
</div>
<div class="footer-col">
<h4>Support</h4>
<a href="http://45.61.59.55:2082/">Client Login</a>
<a href="/game-servers.php">Game Servers</a>
<a href="/admin/support/tickets">Submit Ticket</a>
<a href="/admin/support/kb">Knowledgebase</a>
<a href="/admin/livechat">Live Chat</a>
<a href="/admin/support/announcements">Announcements</a>
<a href="/admin/reviews" class="footer-review-btn"><i class="fa-solid fa-star"></i> Submit a Review</a>
</div>
</div>
<div class="footer-bottom">
<p>&copy; 2026 Planet-Hosts. All rights reserved.</p>
<div>
<a href="#">Terms of Service</a>
<a href="#">Privacy Policy</a>
<a href="#">Cookie Policy</a>
</div>
</div>
</div>
</footer>

<!-- ===== LOGIN MODAL ===== -->
<?php if ($showLogin): ?>
<div id="loginModal" style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.85);backdrop-filter:blur(8px);">
<div style="background:#0b1728;border:1px solid rgba(0,140,255,.2);border-radius:24px;padding:48px;width:420px;max-width:92vw;">
<div style="text-align:center;margin-bottom:32px;">
<img src="/theme/assets/img/logo.png" style="width:60px;height:60px;border-radius:12px;margin-bottom:12px">
<h2 style="font-size:24px;font-weight:800;margin-bottom:4px;">Welcome Back</h2>
<p style="color:#94a3b8;">Sign in to your dashboard</p>
</div>
<?php if ($loginError): ?>
<div style="background:rgba(255,50,50,.12);border:1px solid rgba(255,50,50,.3);border-radius:12px;padding:12px 16px;margin-bottom:20px;color:#f87171;font-size:14px;"><?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>
<form method="POST" action="/user/login" onsubmit="var u=this.querySelector('[name=email]').value;if(u.includes('@')||u==='root'||u==='admin'||u==='kane'){this.action='/admin/login/post'}else{this.action='/user/login'}">
<div style="margin-bottom:18px;">
<label style="display:block;margin-bottom:6px;font-weight:600;font-size:13px;color:#94a3b8;">Username or Email</label>
<input type="text" name="email" placeholder="root@example.com" required style="width:100%;padding:14px 18px;border-radius:12px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:15px;outline:none">
</div>
<div style="margin-bottom:24px;">
<label style="display:block;margin-bottom:6px;font-weight:600;font-size:13px;color:#94a3b8;">Password</label>
<input type="password" name="password" placeholder="Enter your password" required style="width:100%;padding:14px 18px;border-radius:12px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:15px;outline:none">
</div>
<button type="submit" style="width:100%;padding:16px;border:none;border-radius:14px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;font-size:16px;font-weight:700;cursor:pointer;box-shadow:0 0 20px rgba(0,140,255,.35);">Sign In</button>
</form>
<div style="text-align:center;margin-top:18px;">
<a href="/" style="color:#5e8eb0;text-decoration:none;font-size:13px;">&larr; Back to home</a>
</div>
</div></div>
<script>
document.getElementById('loginModal')?.addEventListener('click',function(e){if(e.target===this)window.location.href='/';});
</script>
<?php endif; ?>

<script>
// Category scroll buttons
var pkgIntervals = {};
function showPkg(idx, type) {
    var cards = document.querySelectorAll('#prota-' + type + ' .pkg-rotate');
    var dots = document.querySelectorAll('#pdots-' + type + ' .dot');
    cards.forEach(function(c, i) { c.classList.toggle('active', i === idx); });
    dots.forEach(function(d, i) { d.classList.toggle('active', i === idx); });
    if (pkgIntervals[type]) { clearInterval(pkgIntervals[type]); }
    startPkgRotation(type);
}
function nextPkg(type) {
    var cards = document.querySelectorAll('#prota-' + type + ' .pkg-rotate');
    if (!cards.length) return;
    var current = 0;
    cards.forEach(function(c, i) { if (c.classList.contains('active')) current = i; });
    showPkg((current + 1) % cards.length, type);
}
function prevPkg(type) {
    var cards = document.querySelectorAll('#prota-' + type + ' .pkg-rotate');
    if (!cards.length) return;
    var current = 0;
    cards.forEach(function(c, i) { if (c.classList.contains('active')) current = i; });
    showPkg((current - 1 + cards.length) % cards.length, type);
}
function startPkgRotation(type) {
    var cards = document.querySelectorAll('#prota-' + type + ' .pkg-rotate');
    if (cards.length <= 1) return;
    if (pkgIntervals[type]) clearInterval(pkgIntervals[type]);
    pkgIntervals[type] = setInterval(function() { nextPkg(type); }, 7000);
}
// Chat panel
function toggleChatPanel(){
var p=document.getElementById('chatPanel');
p.classList.toggle('open');
}
var chatSid=0;
function sendChatMessage(){
var name=document.getElementById('chatName').value.trim();
var email=document.getElementById('chatEmail').value.trim();
var msg=document.getElementById('chatMessage').value.trim();
if(!name||!email||!msg){alert('Please fill in all fields');return;}
document.getElementById('chatMessage').value='';
if(chatSid===0){
var x1=new XMLHttpRequest();
x1.open('POST','/chat/start',true);
x1.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
x1.onload=function(){
try{var d=JSON.parse(x1.responseText);chatSid=d.session_id||d.id||0;
document.getElementById('chatSessionId').value=chatSid;
addChatMsg('visitor',name,msg);
sendMsg(chatSid,name,msg);
startChatPoll(chatSid);
document.getElementById('chatForm').innerHTML='<div style="display:flex;gap:8px"><textarea id="chatMessage" placeholder="Type your message..." style="flex:1;min-height:50px"></textarea><button class="btn btn-primary" style="padding:12px 20px;flex-shrink:0;align-self:flex-end" onclick="sendChatMessage()">Send</button></div>';
}catch(e){console.log(e);}
};
x1.send('name='+encodeURIComponent(name)+'&email='+encodeURIComponent(email)+'&subject=Website Contact');
}else{sendMsg(chatSid,name,msg);addChatMsg('visitor',name,msg);}
}
function sendMsg(sid,n,m){
var x=new XMLHttpRequest();
x.open('POST','/chat/send',true);
x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
x.send('session_id='+sid+'&message='+encodeURIComponent(m)+'&name='+encodeURIComponent(n));
}
function addChatMsg(type,name,text){
var box=document.getElementById('chatMessages');
box.style.display='block';
var d=document.createElement('div');
d.style.cssText='padding:8px 12px;margin-bottom:6px;border-radius:10px;font-size:13px;line-height:1.5;word-wrap:break-word';
if(type==='visitor'){d.style.cssText+='background:rgba(0,140,255,.12);text-align:right;margin-left:40px';}
else if(type==='operator'){d.style.cssText+='background:rgba(168,85,247,.12);margin-right:40px';}
else{d.style.cssText+='background:rgba(255,255,255,.04);text-align:center;font-size:11px;color:#64748b';}
d.innerHTML='<strong style="font-size:11px;color:#94a3b8">'+name+'</strong><br>'+text;
box.appendChild(d);
box.scrollTop=box.scrollHeight;
}
var chatPollTimer=null;
function startChatPoll(sid){
if(chatPollTimer)clearInterval(chatPollTimer);
chatPollTimer=setInterval(function(){
var x=new XMLHttpRequest();
x.open('GET','/chat/poll/'+sid,true);
x.onload=function(){
try{
var msgs=JSON.parse(x.responseText);
if(msgs&&msgs.length){
var lastMsg=msgs[msgs.length-1];
if(lastMsg.sender_type==='operator'||lastMsg.sender_type==='system'){
addChatMsg(lastMsg.sender_type,lastMsg.sender_name||'Support',lastMsg.message);
}
}
}catch(e){}
};
x.send();
},3000);
}


// Visitor tracking
(function(){var x=new XMLHttpRequest();x.open('POST','/admin/livechat/track',true);x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');x.send('page='+encodeURIComponent(window.location.pathname)+'&referrer='+encodeURIComponent(document.referrer)+'&url='+encodeURIComponent(window.location.href));})();
function scrollTestimonials(d){var el=document.getElementById('testimonialScroll');if(el){el.scrollBy({left:d*320,behavior:'smooth'});}}
// Auto-scroll testimonials
var testimonialTimer;
function startTestimonialAutoScroll(){
var el=document.getElementById('testimonialScroll');
if(!el)return;
var scrolled=false;
testimonialTimer=setInterval(function(){
if(el.scrollLeft+el.clientWidth>=el.scrollWidth-10){el.scrollTo({left:0,behavior:'smooth'});}
else{el.scrollBy({left:320,behavior:'smooth'});}
},4000);
el.addEventListener('mouseenter',function(){clearInterval(testimonialTimer);});
el.addEventListener('mouseleave',function(){startTestimonialAutoScroll();});
}
setTimeout(startTestimonialAutoScroll,1000);
// Support status check

// Live chat status
fetch('/admin/support-status/public').then(function(r){return r.json()}).then(function(d){
var txt=document.getElementById('panelStatus');
var ttl=document.getElementById('panelTitle');
var img=document.getElementById('supportBadge');
var pnl=document.getElementById('panelBadgeImg');
var onlineImg=d.images&&d.images.online||'/theme/assets/img/livechat/live-online-2.png';
var awayImg=d.images&&d.images.away||'/theme/assets/img/livechat/live-away-2.png';
var offlineImg=d.images&&d.images.offline||'/theme/assets/img/livechat/live-offline-2.png';
var src=offlineImg;
if(d.status==='online'){src=onlineImg;ttl.textContent='Live Support';txt.textContent='We are online - reply within minutes';}
else if(d.status==='away'){src=awayImg;ttl.textContent='Away';txt.textContent='Leave a message and someone will get back to you shortly';}
else{src=offlineImg;ttl.textContent='Offline';txt.textContent='Sorry, Support is offline. Leave us a message';}
img.src=src;if(pnl)pnl.src=src;
var phOnline=document.getElementById('phOnline');var phOffline=document.getElementById('phOffline');var phAway=document.getElementById('phAway');
if(phOnline&&d.images&&d.images.online)phOnline.innerHTML='<img src="'+d.images.online+'" style="height:20px;vertical-align:middle;margin-right:4px"> Online';
if(phOffline&&d.images&&d.images.offline)phOffline.innerHTML='<img src="'+d.images.offline+'" style="height:20px;vertical-align:middle;margin-right:4px"> Offline';
if(phAway&&d.images&&d.images.away)phAway.innerHTML='<img src="'+d.images.away+'" style="height:20px;vertical-align:middle;margin-right:4px"> Away';
}).catch(function(){});
</script>
<div style="text-align:center;padding:24px;background:rgba(8,16,28,.6);border-top:1px solid rgba(255,255,255,.04)">
<div style="display:inline-block;padding:20px;background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;max-width:400px">
<div style="font-size:32px;margin-bottom:6px">🎧</div>
<strong style="font-size:15px">24/7 Live Support</strong>
<p style="color:#94a3b8;font-size:11px;margin:6px 0 10px">Experienced system administrators available 24/7 via tickets, live chat, and phone.</p>
<div id="phChatStatus" style="display:flex;justify-content:center;gap:16px;margin-bottom:10px;font-size:11px;color:#94a3b8">
<span id="phOnline"><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#4ade80;vertical-align:middle;margin-right:4px"></span> Loading...</span>
<span id="phOffline"><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#ef4444;vertical-align:middle;margin-right:4px"></span> Loading...</span>
<span id="phAway"><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#facc15;vertical-align:middle;margin-right:4px"></span> Loading...</span>
</div>
<a href="#" onclick="window.open('https://planet-hosts.com/livechat_popup.php','ph_chat','width=400,height=600');return false" style="color:#0A84FF;text-decoration:none;font-size:13px;padding:8px 20px;border-radius:6px;background:rgba(0,140,255,.15);display:inline-block">💬 Start Live Chat</a>
</div>
</div>
<script src="/theme/assets/js/app.js"></script>
<script>var img=new Image();img.src='https://planet-hosts.com/track.php?id=planethosts&r='+encodeURIComponent(document.referrer)+'&u='+encodeURIComponent(location.href);img.style.display='none';document.body.appendChild(img);</script>
</body>
</html>