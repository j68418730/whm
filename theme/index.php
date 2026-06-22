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
.services-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px}
.service-card{position:relative;background:rgba(8,16,28,.8);border:1px solid rgba(0,191,255,.1);border-radius:16px;padding:32px;transition:.35s;overflow:hidden}
.service-card:hover{transform:translateY(-5px);border-color:#0A84FF;box-shadow:0 0 30px rgba(0,191,255,.08)}
.service-card .icon{font-size:2.2rem;margin-bottom:16px;display:block}
.service-card h3{font-size:1.2rem;margin-bottom:10px}
.service-card p{color:#94a3b8;font-size:14px;line-height:1.7;margin-bottom:16px}
.service-card .learn-more{color:#0A84FF;font-size:13px;font-weight:600;text-decoration:none}
.service-card .learn-more:hover{text-decoration:underline}
/* FEATURES GRID */
.features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px}
.feature-card{background:rgba(8,16,28,.8);border:1px solid rgba(0,191,255,.08);border-radius:14px;padding:28px;text-align:center;transition:.3s}
.feature-card:hover{transform:translateY(-4px);border-color:#0A84FF}
.feature-card .icon{font-size:2rem;margin-bottom:14px;display:block}
.feature-card h4{font-size:15px;margin-bottom:8px}
.feature-card p{color:#94a3b8;font-size:13px;margin:0;line-height:1.6}
/* PACKAGES HORIZONTAL SCROLL */
.pkg-cat-bar{display:flex;gap:8px;overflow-x:auto;padding:4px 0 12px;margin-bottom:24px;scrollbar-width:thin;scrollbar-color:rgba(0,140,255,.3) transparent}
.pkg-cat-bar::-webkit-scrollbar{height:4px}
.pkg-cat-bar::-webkit-scrollbar-track{background:transparent}
.pkg-cat-bar::-webkit-scrollbar-thumb{background:rgba(0,140,255,.3);border-radius:4px}
.pkg-cat-btn{padding:10px 22px;border-radius:10px;border:1px solid rgba(0,191,255,.12);background:rgba(8,16,28,.8);color:#94a3b8;cursor:pointer;font-size:13px;font-weight:600;transition:.3s;white-space:nowrap;font-family:'Inter',sans-serif;flex-shrink:0}
.pkg-cat-btn:hover{color:#fff;border-color:#0A84FF;background:rgba(0,191,255,.08)}
.pkg-cat-btn.active{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border-color:#008cff;box-shadow:0 0 20px rgba(0,140,255,.3)}
.pkg-scroll{display:flex;gap:20px;overflow-x:auto;padding:8px 4px 16px;scroll-snap-type:x mandatory;scrollbar-width:thin;scrollbar-color:rgba(0,140,255,.3) transparent}
.pkg-scroll::-webkit-scrollbar{height:6px}
.pkg-scroll::-webkit-scrollbar-track{background:transparent}
.pkg-scroll::-webkit-scrollbar-thumb{background:rgba(0,140,255,.3);border-radius:4px}
.pkg-scroll-card{min-width:320px;max-width:340px;background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:28px;transition:.35s;scroll-snap-align:start;flex-shrink:0;position:relative;display:flex;flex-direction:column}
.pkg-scroll-card.featured{border-color:#0A84FF;box-shadow:0 0 30px rgba(0,191,255,.12)}
.pkg-scroll-card.featured::before{content:"Featured";position:absolute;top:14px;right:14px;background:linear-gradient(135deg,#0A84FF,#00E5FF);color:#fff;font-size:11px;font-weight:700;padding:4px 12px;border-radius:6px}
.pkg-scroll-card:hover{transform:translateY(-4px);border-color:#0A84FF;box-shadow:0 0 30px rgba(0,191,255,.08)}
.pkg-scroll-card h4{font-size:1.15rem;margin-bottom:4px}
.pkg-scroll-card .price{font-size:1.8rem;font-weight:800;color:#0A84FF;margin-bottom:10px}
.pkg-scroll-card .price small{font-size:.8rem;font-weight:400;color:#64748b}
.pkg-scroll-card p{color:#94a3b8;font-size:.82rem;line-height:1.6;margin-bottom:10px;flex-grow:1}
.pkg-scroll-card .features-list{list-style:none;padding:0;margin-bottom:14px}
.pkg-scroll-card .features-list li{color:#cbd5e1;font-size:.78rem;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.04);display:flex;align-items:center;gap:8px}
.pkg-scroll-card .features-list li:last-child{border-bottom:none}
.pkg-scroll-card .features-list li i{width:16px;color:#4ade80;font-size:10px}
.pkg-scroll-card .btn-row{display:flex;gap:8px;margin-top:auto}
.pkg-scroll-card .btn-row .btn{flex:1;text-align:center;padding:10px 8px;font-size:13px}
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
.floating-chat{position:fixed;bottom:120px;right:30px;z-index:9999;cursor:pointer}
.floating-chat .chat-bubble{width:85px;height:85px;border-radius:50%;background:linear-gradient(135deg,#0A84FF,#00E5FF);display:flex;align-items:center;justify-content:center;font-size:34px;color:#fff;box-shadow:0 4px 25px rgba(0,140,255,.45);transition:.3s}
.floating-chat .chat-bubble:hover{transform:scale(1.08);box-shadow:0 4px 35px rgba(0,140,255,.55)}
.floating-chat .support-badge-img{width:340px;height:auto;aspect-ratio:3/1;border-radius:18px;object-fit:contain;background:rgba(0,0,0,.5);padding:12px 24px;box-shadow:0 8px 45px rgba(0,0,0,.6);transition:.3s}
.floating-chat .support-badge-img:hover{transform:scale(1.06)}
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
<a href="?login" class="btn-primary btn-order" style="padding:8px 20px;font-size:13px"><i class="fa-solid fa-cart-plus"></i> Order Now</a>
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

<!-- ===== SERVICES ===== -->
<section class="section" id="services">
<div class="container">
<div class="section-title">
<h2>Our <span>Services</span></h2>
<p>Comprehensive hosting solutions designed for every need</p>
</div>
<div class="services-grid">
<div class="service-card">
<span class="icon"><i class="fa-solid fa-globe"></i></span>
<h3>Web Hosting</h3>
<p>Fast, secure shared hosting with WHM/cPanel, unlimited databases, email accounts, and one-click installers. Perfect for small to medium websites.</p>
<a href="#packages" class="learn-more">View Plans <i class="fa-solid fa-arrow-right"></i></a>
</div>
<div class="service-card">
<span class="icon"><i class="fa-solid fa-building"></i></span>
<h3>Web Hosting Reseller</h3>
<p>Start your own hosting business with white-label reseller accounts. Full WHM access, custom nameservers, and complete client management tools.</p>
<a href="#packages" class="learn-more">View Plans <i class="fa-solid fa-arrow-right"></i></a>
</div>
<div class="service-card">
<span class="icon"><i class="fa-solid fa-tower-broadcast"></i></span>
<h3>SHOUTcast Hosting</h3>
<p>High-bitrate SHOUTcast streaming with AutoDJ, listener analytics, and easy management. Reach global audiences with zero buffering.</p>
<a href="#packages" class="learn-more">View Plans <i class="fa-solid fa-arrow-right"></i></a>
</div>
<div class="service-card">
<span class="icon"><i class="fa-solid fa-radio"></i></span>
<h3>SHOUTcast Reseller</h3>
<p>Resell SHOUTcast streaming services under your own brand. WHM integration, bulk account management, and automated provisioning included.</p>
<a href="#packages" class="learn-more">View Plans <i class="fa-solid fa-arrow-right"></i></a>
</div>
<div class="service-card">
<span class="icon"><i class="fa-solid fa-paintbrush"></i></span>
<h3>Website Builder</h3>
<p>Create stunning websites with our drag-and-drop builder or AI-powered site generator. Choose from professional templates or generate unique designs.</p>
<a href="?login" class="learn-more">Learn More <i class="fa-solid fa-arrow-right"></i></a>
</div>
<div class="service-card">
<span class="icon"><i class="fa-solid fa-server"></i></span>
<h3>VPS &amp; Dedicated</h3>
<p>Full root access virtual and dedicated servers. Complete isolation, custom configurations, and high-performance hardware for demanding workloads.</p>
<a href="#packages" class="learn-more">View Plans <i class="fa-solid fa-arrow-right"></i></a>
</div>
</div>
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

<!-- ===== PACKAGES - HORIZONTAL SCROLL ===== -->
<section class="section" id="packages">
<div class="container">
<div class="section-title">
<h2>Hosting <span>Plans</span></h2>
<p>Choose the perfect plan for your project. All plans include 24/7 support.</p>
</div>
<?php
if (!empty($packagesByType)):
// Sort categories for consistent order
$catOrder = ['web_hosting','Web Hosting','web_reseller','Web Hosting Reseller','shoutcast','SHOUTcast','icecast','Icecast Streaming','icecast_reseller','Icecast Reseller','vps','VPS Servers','dedicated','Dedicated Servers','game_server','Game Server'];
$catLabels = [
    'web_hosting'=>'Web Hosting','Web Hosting'=>'Web Hosting',
    'web_reseller'=>'Reseller Hosting','Web Hosting Reseller'=>'Reseller Hosting',
    'shoutcast'=>'SHOUTcast','SHOUTcast'=>'SHOUTcast',
    'icecast'=>'Icecast Streaming','Icecast Streaming'=>'Icecast Streaming',
    'icecast_reseller'=>'Icecast Reseller','Icecast Reseller'=>'Icecast Reseller',
    'vps'=>'VPS Servers','VPS Servers'=>'VPS Servers',
    'dedicated'=>'Dedicated Servers','Dedicated Servers'=>'Dedicated Servers',
    'game_server'=>'Game Servers','Game Servers'=>'Game Servers',
];
$sorted = [];
foreach ($catOrder as $c) {
    if (isset($packagesByType[$c])) {
        $sorted[$c] = $packagesByType[$c];
    }
}
foreach ($packagesByType as $type => $pkgs) {
    if (!isset($sorted[$type])) {
        $sorted[$type] = $pkgs;
    }
}
$firstType = array_key_first($sorted);
?>
<div class="pkg-cat-bar" id="pkgCatBar">
<?php foreach ($sorted as $type => $pkgs): ?>
<button class="pkg-cat-btn<?php if ($type === $firstType): ?> active<?php endif; ?>" onclick="showPkgCat('<?php echo $type; ?>')"><?php echo $catLabels[$type] ?? ucwords(str_replace(['_','-'],' ',$type)); ?></button>
<?php endforeach; ?>
</div>
<?php foreach ($sorted as $type => $pkgs): ?>
<div class="pkg-cat-pane" id="pkg-<?php echo $type; ?>"<?php if ($type !== $firstType): ?> style="display:none"<?php endif; ?>>
<div class="pkg-scroll">
<?php $pkgCount = 0; foreach ($pkgs as $i => $pkg): ?>
<?php if ($pkgCount >= 5): continue; endif; $pkgCount++; ?>
<div class="pkg-scroll-card<?php if ($i === 1): ?> featured<?php endif; ?>">
<h4><?php echo htmlspecialchars($pkg->name, ENT_QUOTES, 'UTF-8'); ?></h4>
<div class="price">$<?php echo number_format((float)($pkg->monthly_price ?? $pkg->price ?? 0), 2); ?><small>/mo</small></div>
<p><?php echo htmlspecialchars($pkg->description ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
<ul class="features-list">
<?php if (!empty($pkg->disk_space) && $pkg->disk_space > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $pkg->disk_space; ?> GB Disk</li><?php endif; ?>
<?php if (!empty($pkg->bandwidth) && $pkg->bandwidth > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $pkg->bandwidth; ?> GB Bandwidth</li><?php endif; ?>
<?php if (!empty($pkg->listener_limit) && $pkg->listener_limit > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $pkg->listener_limit; ?> Listeners</li><?php endif; ?>
<?php if (!empty($pkg->bitrate) && $pkg->bitrate > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $pkg->bitrate; ?> kbps Bitrate</li><?php endif; ?>
<?php if (!empty($pkg->storage_limit) && $pkg->storage_limit > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $pkg->storage_limit; ?> GB Storage</li><?php endif; ?>
<?php if (!empty($pkg->email_accounts) && $pkg->email_accounts > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $pkg->email_accounts; ?> Emails</li><?php endif; ?>
<?php if (!empty($pkg->databases) && $pkg->databases > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $pkg->databases; ?> Databases</li><?php endif; ?>
<?php if (!empty($pkg->addon_domains) && $pkg->addon_domains > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $pkg->addon_domains; ?> Addon Domains</li><?php endif; ?>
<?php if (!empty($pkg->dj_accounts) && $pkg->dj_accounts > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $pkg->dj_accounts; ?> DJ Accounts</li><?php endif; ?>
<?php if (!empty($pkg->subdomains) && $pkg->subdomains > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $pkg->subdomains; ?> Subdomains</li><?php endif; ?>
<li><i class="fa-solid fa-circle-check"></i> Free SSL</li>
<li><i class="fa-solid fa-circle-check"></i> 24/7 Support</li>
</ul>
<div class="btn-row">
<a href="?login" class="btn btn-primary"><i class="fa-solid fa-cart-plus"></i> Order Now</a>
<a href="?login" class="btn btn-secondary">Read More</a>
</div>
</div>
<?php endforeach; ?>
<?php if (count($pkgs) > 5): ?>
<div style="text-align:center;padding:16px 0">
<a href="/hosting/<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary">View More <i class="fa-solid fa-arrow-right"></i></a>
</div>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
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
<img src="/theme/assets/img/livechat/live-online-2.png" alt="Live Support" class="support-badge-img" id="supportBadge">
</div>

<div class="floating-chat-panel" id="chatPanel">
<div class="panel-header">
<img src="/theme/assets/img/livechat/live-online-2.png" id="panelBadgeImg" style="height:40px;width:auto;border-radius:6px;object-fit:contain;background:rgba(255,255,255,.1);padding:4px 10px">
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
<form method="POST" action="/admin/login/post">
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
function showPkgCat(type){
var panes=document.querySelectorAll('.pkg-cat-pane');
var btns=document.querySelectorAll('.pkg-cat-btn');
for(var i=0;i<panes.length;i++)panes[i].style.display='none';
for(var i=0;i<btns.length;i++)btns[i].classList.remove('active');
document.getElementById('pkg-'+type).style.display='block';
for(var i=0;i<btns.length;i++){
var onclick=btns[i].getAttribute('onclick');
if(onclick&&onclick.indexOf('\''+type+'\'')>-1){btns[i].classList.add('active');break;}
}
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
fetch('/admin/support-status').then(function(r){return r.json()}).then(function(d){
var txt=document.getElementById('panelStatus');
var ttl=document.getElementById('panelTitle');
var img=document.getElementById('supportBadge');
var pnl=document.getElementById('panelBadgeImg');
var src='/theme/assets/img/livechat/live-away-2.png';
if(d.status==='online'){src='/theme/assets/img/livechat/live-online-2.png';ttl.textContent='Live Support';txt.textContent='We are online - reply within minutes';}
else if(d.status==='away'){src='/theme/assets/img/livechat/live-away-2.png';ttl.textContent='Away';txt.textContent='Leave a message and someone will get back to you shortly';}
else{src='/theme/assets/img/livechat/live-offline-2.png';ttl.textContent='Offline';txt.textContent='Sorry, Support is offline. Right Leave us a message';}
img.src=src;if(pnl)pnl.src=src;
}).catch(function(){});
</script>
<script src="/theme/assets/js/app.js"></script>
</body>
</html>