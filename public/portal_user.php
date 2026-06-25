<?php
$host = $_SERVER["HTTP_HOST"] ?? "planet-hosts.com";
$pdo = new PDO("mysql:host=localhost;dbname=radiohosting;charset=utf8mb4", "radiouser", "Skylinehosting171");
$packages = $pdo->query("SELECT * FROM hosting_packages WHERE is_active = 1 ORDER BY type, monthly_price LIMIT 50")->fetchAll(PDO::FETCH_OBJ) ?: [];
$categories = [];
foreach ($packages as $p) $categories[$p->type ?? "web_hosting"][] = $p;
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
<a href="http://<?php echo $host; ?>:2082/" class="btn-secondary" style="padding:8px 16px;font-size:13px"><i class="fa-solid fa-user"></i> Client Login</a>
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
<a href="http://<?php echo $host; ?>:2082/" class="btn btn-secondary btn-lg"><i class="fa-solid fa-gauge-high"></i> Client Area</a>
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
<div class="pricing-rotate-col">
<div style="text-align:center;margin-bottom:10px">
<span style="font-size:28px">🌐</span>
<h3 style="font-size:16px;margin:4px 0 2px">Web Hosting</h3>
<div style="font-size:11px;color:#64748b">1 plan</div>
</div>
<div class="pricing-rotate" id="prota-Web Hosting">
<div class="pkg-rotate active" data-type="Web Hosting" data-index="0">
<h3>testinglive</h3>
<div class="subtitle">this is testing live chat</div>
<div class="price">$0.00<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 50 GB Disk</li><li><i class="fa-solid fa-check"></i> 500 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 100 Listeners</li><li><i class="fa-solid fa-check"></i> 320 kbps</li><li><i class="fa-solid fa-check"></i> 10 GB Storage</li><li><i class="fa-solid fa-check"></i> 5 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=8&name=testinglive&price=0" class="btn">Order Now →</a>
<a href="/product/8" class="btn-outline">Read More →</a>
</div>
</div>
<div class="review-dots" id="pdots-Web Hosting">
<span class="dot active" onclick="showPkg(0, 'Web Hosting')"></span>
</div>
<div class="review-nav" style="margin-top:8px">
<button onclick="prevPkg('Web Hosting')">← Prev</button>
<button onclick="nextPkg('Web Hosting')">Next →</button>
</div>
</div>
<div class="pricing-rotate-col">
<div style="text-align:center;margin-bottom:10px">
<span style="font-size:28px">🌐</span>
<h3 style="font-size:16px;margin:4px 0 2px">Web Hosting</h3>
<div style="font-size:11px;color:#64748b">10 plans</div>
</div>
<div class="pricing-rotate" id="prota-web_hosting">
<div class="pkg-rotate active" data-type="web_hosting" data-index="0">
<h3>Starter</h3>
<div class="subtitle">Perfect for personal websites and small blogs.</div>
<div class="price">$2.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 1 GB Disk</li><li><i class="fa-solid fa-check"></i> 10 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 1 Emails</li><li><i class="fa-solid fa-check"></i> 1 Databases</li><li><i class="fa-solid fa-check"></i> 1 Subdomains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=9&name=Starter&price=2.99" class="btn">Order Now →</a>
<a href="/product/9" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_hosting" data-index="1">
<h3>Basic</h3>
<div class="subtitle">Great for small business websites.</div>
<div class="price">$4.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 3 GB Disk</li><li><i class="fa-solid fa-check"></i> 25 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 3 Emails</li><li><i class="fa-solid fa-check"></i> 2 Databases</li><li><i class="fa-solid fa-check"></i> 3 Subdomains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=10&name=Basic&price=4.99" class="btn">Order Now →</a>
<a href="/product/10" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_hosting" data-index="2">
<h3>Standard</h3>
<div class="subtitle">Ideal for growing websites with moderate traffic.</div>
<div class="price">$7.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 5 GB Disk</li><li><i class="fa-solid fa-check"></i> 50 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 5 Emails</li><li><i class="fa-solid fa-check"></i> 3 Databases</li><li><i class="fa-solid fa-check"></i> 5 Subdomains</li><li><i class="fa-solid fa-check"></i> 1 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=11&name=Standard&price=7.99" class="btn">Order Now →</a>
<a href="/product/11" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_hosting" data-index="3">
<h3>Advanced</h3>
<div class="subtitle">For established sites needing more resources.</div>
<div class="price">$12.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 10 GB Disk</li><li><i class="fa-solid fa-check"></i> 100 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 10 Emails</li><li><i class="fa-solid fa-check"></i> 5 Databases</li><li><i class="fa-solid fa-check"></i> 10 Subdomains</li><li><i class="fa-solid fa-check"></i> 2 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=12&name=Advanced&price=12.99" class="btn">Order Now →</a>
<a href="/product/12" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_hosting" data-index="4">
<h3>Professional</h3>
<div class="subtitle">For high-traffic business and e-commerce sites.</div>
<div class="price">$19.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 20 GB Disk</li><li><i class="fa-solid fa-check"></i> 200 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 20 Emails</li><li><i class="fa-solid fa-check"></i> 10 Databases</li><li><i class="fa-solid fa-check"></i> 20 Subdomains</li><li><i class="fa-solid fa-check"></i> 5 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=13&name=Professional&price=19.99" class="btn">Order Now →</a>
<a href="/product/13" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_hosting" data-index="5">
<h3>Starter Plus</h3>
<div class="subtitle">Entry-level with live chat support included.</div>
<div class="price">$4.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 2 GB Disk</li><li><i class="fa-solid fa-check"></i> 20 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 2 Emails</li><li><i class="fa-solid fa-check"></i> 2 Databases</li><li><i class="fa-solid fa-check"></i> 2 Subdomains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=14&name=Starter+Plus&price=4.99" class="btn">Order Now →</a>
<a href="/product/14" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_hosting" data-index="6">
<h3>Basic Plus</h3>
<div class="subtitle">Small business with full support features.</div>
<div class="price">$7.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 5 GB Disk</li><li><i class="fa-solid fa-check"></i> 50 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 5 Emails</li><li><i class="fa-solid fa-check"></i> 3 Databases</li><li><i class="fa-solid fa-check"></i> 5 Subdomains</li><li><i class="fa-solid fa-check"></i> 1 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=15&name=Basic+Plus&price=7.99" class="btn">Order Now →</a>
<a href="/product/15" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_hosting" data-index="7">
<h3>Business</h3>
<div class="subtitle">Everything a growing business needs.</div>
<div class="price">$14.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 15 GB Disk</li><li><i class="fa-solid fa-check"></i> 150 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 15 Emails</li><li><i class="fa-solid fa-check"></i> 8 Databases</li><li><i class="fa-solid fa-check"></i> 15 Subdomains</li><li><i class="fa-solid fa-check"></i> 3 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=16&name=Business&price=14.99" class="btn">Order Now →</a>
<a href="/product/16" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_hosting" data-index="8">
<h3>Business Pro</h3>
<div class="subtitle">Premium hosting for serious businesses.</div>
<div class="price">$24.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 30 GB Disk</li><li><i class="fa-solid fa-check"></i> 300 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 30 Emails</li><li><i class="fa-solid fa-check"></i> 15 Databases</li><li><i class="fa-solid fa-check"></i> 30 Subdomains</li><li><i class="fa-solid fa-check"></i> 8 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=17&name=Business+Pro&price=24.99" class="btn">Order Now →</a>
<a href="/product/17" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_hosting" data-index="9">
<h3>Enterprise</h3>
<div class="subtitle">Maximum performance and dedicated support.</div>
<div class="price">$39.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 50 GB Disk</li><li><i class="fa-solid fa-check"></i> 500 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 50 Emails</li><li><i class="fa-solid fa-check"></i> 25 Databases</li><li><i class="fa-solid fa-check"></i> 50 Subdomains</li><li><i class="fa-solid fa-check"></i> 15 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=18&name=Enterprise&price=39.99" class="btn">Order Now →</a>
<a href="/product/18" class="btn-outline">Read More →</a>
</div>
</div>
<div class="review-dots" id="pdots-web_hosting">
<span class="dot active" onclick="showPkg(0, 'web_hosting')"></span>
<span class="dot" onclick="showPkg(1, 'web_hosting')"></span>
<span class="dot" onclick="showPkg(2, 'web_hosting')"></span>
<span class="dot" onclick="showPkg(3, 'web_hosting')"></span>
<span class="dot" onclick="showPkg(4, 'web_hosting')"></span>
<span class="dot" onclick="showPkg(5, 'web_hosting')"></span>
<span class="dot" onclick="showPkg(6, 'web_hosting')"></span>
<span class="dot" onclick="showPkg(7, 'web_hosting')"></span>
<span class="dot" onclick="showPkg(8, 'web_hosting')"></span>
<span class="dot" onclick="showPkg(9, 'web_hosting')"></span>
</div>
<div class="review-nav" style="margin-top:8px">
<button onclick="prevPkg('web_hosting')">← Prev</button>
<button onclick="nextPkg('web_hosting')">Next →</button>
</div>
</div>
<div class="pricing-rotate-col">
<div style="text-align:center;margin-bottom:10px">
<span style="font-size:28px">🏢</span>
<h3 style="font-size:16px;margin:4px 0 2px">Web Reseller</h3>
<div style="font-size:11px;color:#64748b">10 plans</div>
</div>
<div class="pricing-rotate" id="prota-web_reseller">
<div class="pkg-rotate active" data-type="web_reseller" data-index="0">
<h3>Reseller Mini</h3>
<div class="subtitle">Start your hosting business small.</div>
<div class="price">$9.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 5 GB Disk</li><li><i class="fa-solid fa-check"></i> 50 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 10 Emails</li><li><i class="fa-solid fa-check"></i> 5 Databases</li><li><i class="fa-solid fa-check"></i> 10 Subdomains</li><li><i class="fa-solid fa-check"></i> 2 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=19&name=Reseller+Mini&price=9.99" class="btn">Order Now →</a>
<a href="/product/19" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_reseller" data-index="1">
<h3>Reseller Basic</h3>
<div class="subtitle">Solid foundation for new resellers.</div>
<div class="price">$14.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 10 GB Disk</li><li><i class="fa-solid fa-check"></i> 100 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 20 Emails</li><li><i class="fa-solid fa-check"></i> 10 Databases</li><li><i class="fa-solid fa-check"></i> 20 Subdomains</li><li><i class="fa-solid fa-check"></i> 5 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=20&name=Reseller+Basic&price=14.99" class="btn">Order Now →</a>
<a href="/product/20" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_reseller" data-index="2">
<h3>Reseller Standard</h3>
<div class="subtitle">For growing reseller operations.</div>
<div class="price">$24.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 20 GB Disk</li><li><i class="fa-solid fa-check"></i> 200 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 50 Emails</li><li><i class="fa-solid fa-check"></i> 20 Databases</li><li><i class="fa-solid fa-check"></i> 50 Subdomains</li><li><i class="fa-solid fa-check"></i> 10 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=21&name=Reseller+Standard&price=24.99" class="btn">Order Now →</a>
<a href="/product/21" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_reseller" data-index="3">
<h3>Reseller Advanced</h3>
<div class="subtitle">Manage more clients with better resources.</div>
<div class="price">$39.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 40 GB Disk</li><li><i class="fa-solid fa-check"></i> 400 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 100 Emails</li><li><i class="fa-solid fa-check"></i> 40 Databases</li><li><i class="fa-solid fa-check"></i> 100 Subdomains</li><li><i class="fa-solid fa-check"></i> 20 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=22&name=Reseller+Advanced&price=39.99" class="btn">Order Now →</a>
<a href="/product/22" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_reseller" data-index="4">
<h3>Reseller Pro</h3>
<div class="subtitle">Professional reseller package.</div>
<div class="price">$59.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 75 GB Disk</li><li><i class="fa-solid fa-check"></i> 750 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 200 Emails</li><li><i class="fa-solid fa-check"></i> 75 Databases</li><li><i class="fa-solid fa-check"></i> 200 Subdomains</li><li><i class="fa-solid fa-check"></i> 40 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=23&name=Reseller+Pro&price=59.99" class="btn">Order Now →</a>
<a href="/product/23" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_reseller" data-index="5">
<h3>Reseller Mini Plus</h3>
<div class="subtitle">Entry reseller with live chat.</div>
<div class="price">$12.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 8 GB Disk</li><li><i class="fa-solid fa-check"></i> 75 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 15 Emails</li><li><i class="fa-solid fa-check"></i> 8 Databases</li><li><i class="fa-solid fa-check"></i> 15 Subdomains</li><li><i class="fa-solid fa-check"></i> 3 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=24&name=Reseller+Mini+Plus&price=12.99" class="btn">Order Now →</a>
<a href="/product/24" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_reseller" data-index="6">
<h3>Reseller Business</h3>
<div class="subtitle">Full-featured reseller with support.</div>
<div class="price">$34.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 30 GB Disk</li><li><i class="fa-solid fa-check"></i> 300 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 75 Emails</li><li><i class="fa-solid fa-check"></i> 30 Databases</li><li><i class="fa-solid fa-check"></i> 75 Subdomains</li><li><i class="fa-solid fa-check"></i> 15 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=25&name=Reseller+Business&price=34.99" class="btn">Order Now →</a>
<a href="/product/25" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_reseller" data-index="7">
<h3>Reseller Business Pro</h3>
<div class="subtitle">Premium reseller with all features.</div>
<div class="price">$54.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 60 GB Disk</li><li><i class="fa-solid fa-check"></i> 600 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 150 Emails</li><li><i class="fa-solid fa-check"></i> 60 Databases</li><li><i class="fa-solid fa-check"></i> 150 Subdomains</li><li><i class="fa-solid fa-check"></i> 30 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=26&name=Reseller+Business+Pro&price=54.99" class="btn">Order Now →</a>
<a href="/product/26" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_reseller" data-index="8">
<h3>Reseller Enterprise</h3>
<div class="subtitle">Enterprise-grade reseller hosting.</div>
<div class="price">$89.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 100 GB Disk</li><li><i class="fa-solid fa-check"></i> 1000 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 300 Emails</li><li><i class="fa-solid fa-check"></i> 100 Databases</li><li><i class="fa-solid fa-check"></i> 300 Subdomains</li><li><i class="fa-solid fa-check"></i> 50 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=27&name=Reseller+Enterprise&price=89.99" class="btn">Order Now →</a>
<a href="/product/27" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="web_reseller" data-index="9">
<h3>Reseller Ultimate</h3>
<div class="subtitle">The ultimate reseller package.</div>
<div class="price">$149.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 200 GB Disk</li><li><i class="fa-solid fa-check"></i> 2000 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 500 Emails</li><li><i class="fa-solid fa-check"></i> 200 Databases</li><li><i class="fa-solid fa-check"></i> 500 Subdomains</li><li><i class="fa-solid fa-check"></i> 100 Addon Domains</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=28&name=Reseller+Ultimate&price=149.99" class="btn">Order Now →</a>
<a href="/product/28" class="btn-outline">Read More →</a>
</div>
</div>
<div class="review-dots" id="pdots-web_reseller">
<span class="dot active" onclick="showPkg(0, 'web_reseller')"></span>
<span class="dot" onclick="showPkg(1, 'web_reseller')"></span>
<span class="dot" onclick="showPkg(2, 'web_reseller')"></span>
<span class="dot" onclick="showPkg(3, 'web_reseller')"></span>
<span class="dot" onclick="showPkg(4, 'web_reseller')"></span>
<span class="dot" onclick="showPkg(5, 'web_reseller')"></span>
<span class="dot" onclick="showPkg(6, 'web_reseller')"></span>
<span class="dot" onclick="showPkg(7, 'web_reseller')"></span>
<span class="dot" onclick="showPkg(8, 'web_reseller')"></span>
<span class="dot" onclick="showPkg(9, 'web_reseller')"></span>
</div>
<div class="review-nav" style="margin-top:8px">
<button onclick="prevPkg('web_reseller')">← Prev</button>
<button onclick="nextPkg('web_reseller')">Next →</button>
</div>
</div>
<div class="pricing-rotate-col">
<div style="text-align:center;margin-bottom:10px">
<span style="font-size:28px">🎵</span>
<h3 style="font-size:16px;margin:4px 0 2px">Icecast</h3>
<div style="font-size:11px;color:#64748b">11 plans</div>
</div>
<div class="pricing-rotate" id="prota-icecast">
<div class="pkg-rotate active" data-type="icecast" data-index="0">
<h3>Radio Mini</h3>
<div class="subtitle">Start your radio journey.</div>
<div class="price">$3.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 1 GB Disk</li><li><i class="fa-solid fa-check"></i> 10 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 10 Listeners</li><li><i class="fa-solid fa-check"></i> 64 kbps</li><li><i class="fa-solid fa-check"></i> 500 GB Storage</li><li><i class="fa-solid fa-check"></i> 1 Emails</li><li><i class="fa-solid fa-check"></i> 1 Databases</li><li><i class="fa-solid fa-check"></i> 1 Subdomains</li><li><i class="fa-solid fa-check"></i> 1 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=29&name=Radio+Mini&price=3.99" class="btn">Order Now →</a>
<a href="/product/29" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast" data-index="1">
<h3>Radio Basic</h3>
<div class="subtitle">For hobby broadcasters.</div>
<div class="price">$6.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 3 GB Disk</li><li><i class="fa-solid fa-check"></i> 25 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 25 Listeners</li><li><i class="fa-solid fa-check"></i> 96 kbps</li><li><i class="fa-solid fa-check"></i> 1 GB Storage</li><li><i class="fa-solid fa-check"></i> 2 Emails</li><li><i class="fa-solid fa-check"></i> 1 Databases</li><li><i class="fa-solid fa-check"></i> 2 Subdomains</li><li><i class="fa-solid fa-check"></i> 2 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=30&name=Radio+Basic&price=6.99" class="btn">Order Now →</a>
<a href="/product/30" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast" data-index="2">
<h3>Radio Standard</h3>
<div class="subtitle">Great for community radio stations.</div>
<div class="price">$11.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 5 GB Disk</li><li><i class="fa-solid fa-check"></i> 50 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 50 Listeners</li><li><i class="fa-solid fa-check"></i> 128 kbps</li><li><i class="fa-solid fa-check"></i> 2 GB Storage</li><li><i class="fa-solid fa-check"></i> 5 Emails</li><li><i class="fa-solid fa-check"></i> 2 Databases</li><li><i class="fa-solid fa-check"></i> 5 Subdomains</li><li><i class="fa-solid fa-check"></i> 1 Addon Domains</li><li><i class="fa-solid fa-check"></i> 3 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=31&name=Radio+Standard&price=11.99" class="btn">Order Now →</a>
<a href="/product/31" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast" data-index="3">
<h3>Radio Advanced</h3>
<div class="subtitle">For serious broadcasters.</div>
<div class="price">$19.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 10 GB Disk</li><li><i class="fa-solid fa-check"></i> 100 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 100 Listeners</li><li><i class="fa-solid fa-check"></i> 192 kbps</li><li><i class="fa-solid fa-check"></i> 5 GB Storage</li><li><i class="fa-solid fa-check"></i> 10 Emails</li><li><i class="fa-solid fa-check"></i> 5 Databases</li><li><i class="fa-solid fa-check"></i> 10 Subdomains</li><li><i class="fa-solid fa-check"></i> 2 Addon Domains</li><li><i class="fa-solid fa-check"></i> 5 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=32&name=Radio+Advanced&price=19.99" class="btn">Order Now →</a>
<a href="/product/32" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast" data-index="4">
<h3>Radio Professional</h3>
<div class="subtitle">Professional radio station package.</div>
<div class="price">$29.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 20 GB Disk</li><li><i class="fa-solid fa-check"></i> 200 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 250 Listeners</li><li><i class="fa-solid fa-check"></i> 256 kbps</li><li><i class="fa-solid fa-check"></i> 10 GB Storage</li><li><i class="fa-solid fa-check"></i> 20 Emails</li><li><i class="fa-solid fa-check"></i> 10 Databases</li><li><i class="fa-solid fa-check"></i> 20 Subdomains</li><li><i class="fa-solid fa-check"></i> 5 Addon Domains</li><li><i class="fa-solid fa-check"></i> 10 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=33&name=Radio+Professional&price=29.99" class="btn">Order Now →</a>
<a href="/product/33" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast" data-index="5">
<h3>Radio Mini Plus</h3>
<div class="subtitle">Entry streaming with live chat.</div>
<div class="price">$5.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 2 GB Disk</li><li><i class="fa-solid fa-check"></i> 15 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 15 Listeners</li><li><i class="fa-solid fa-check"></i> 80 kbps</li><li><i class="fa-solid fa-check"></i> 750 GB Storage</li><li><i class="fa-solid fa-check"></i> 1 Emails</li><li><i class="fa-solid fa-check"></i> 1 Databases</li><li><i class="fa-solid fa-check"></i> 1 Subdomains</li><li><i class="fa-solid fa-check"></i> 1 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=34&name=Radio+Mini+Plus&price=5.99" class="btn">Order Now →</a>
<a href="/product/34" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast" data-index="6">
<h3>Radio Standard Plus</h3>
<div class="subtitle">Community radio with chat support.</div>
<div class="price">$14.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 8 GB Disk</li><li><i class="fa-solid fa-check"></i> 75 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 75 Listeners</li><li><i class="fa-solid fa-check"></i> 160 kbps</li><li><i class="fa-solid fa-check"></i> 3 GB Storage</li><li><i class="fa-solid fa-check"></i> 8 Emails</li><li><i class="fa-solid fa-check"></i> 3 Databases</li><li><i class="fa-solid fa-check"></i> 8 Subdomains</li><li><i class="fa-solid fa-check"></i> 1 Addon Domains</li><li><i class="fa-solid fa-check"></i> 5 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=35&name=Radio+Standard+Plus&price=14.99" class="btn">Order Now →</a>
<a href="/product/35" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast" data-index="7">
<h3>Radio Business</h3>
<div class="subtitle">Business-grade radio streaming.</div>
<div class="price">$24.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 15 GB Disk</li><li><i class="fa-solid fa-check"></i> 150 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 150 Listeners</li><li><i class="fa-solid fa-check"></i> 224 kbps</li><li><i class="fa-solid fa-check"></i> 8 GB Storage</li><li><i class="fa-solid fa-check"></i> 15 Emails</li><li><i class="fa-solid fa-check"></i> 8 Databases</li><li><i class="fa-solid fa-check"></i> 15 Subdomains</li><li><i class="fa-solid fa-check"></i> 3 Addon Domains</li><li><i class="fa-solid fa-check"></i> 8 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=36&name=Radio+Business&price=24.99" class="btn">Order Now →</a>
<a href="/product/36" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast" data-index="8">
<h3>Radio Premium</h3>
<div class="subtitle">Premium streaming with full support.</div>
<div class="price">$39.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 30 GB Disk</li><li><i class="fa-solid fa-check"></i> 300 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 500 Listeners</li><li><i class="fa-solid fa-check"></i> 320 kbps</li><li><i class="fa-solid fa-check"></i> 15 GB Storage</li><li><i class="fa-solid fa-check"></i> 30 Emails</li><li><i class="fa-solid fa-check"></i> 15 Databases</li><li><i class="fa-solid fa-check"></i> 30 Subdomains</li><li><i class="fa-solid fa-check"></i> 8 Addon Domains</li><li><i class="fa-solid fa-check"></i> 15 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=37&name=Radio+Premium&price=39.99" class="btn">Order Now →</a>
<a href="/product/37" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast" data-index="9">
<h3>Radio Enterprise</h3>
<div class="subtitle">Maximum broadcast power.</div>
<div class="price">$59.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 50 GB Disk</li><li><i class="fa-solid fa-check"></i> 500 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 1000 Listeners</li><li><i class="fa-solid fa-check"></i> 320 kbps</li><li><i class="fa-solid fa-check"></i> 25 GB Storage</li><li><i class="fa-solid fa-check"></i> 50 Emails</li><li><i class="fa-solid fa-check"></i> 25 Databases</li><li><i class="fa-solid fa-check"></i> 50 Subdomains</li><li><i class="fa-solid fa-check"></i> 15 Addon Domains</li><li><i class="fa-solid fa-check"></i> 25 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=38&name=Radio+Enterprise&price=59.99" class="btn">Order Now →</a>
<a href="/product/38" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast" data-index="10">
<h3>Test All Features</h3>
<div class="subtitle">Full access package for testing</div>
<div class="price">$0.00<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 10 GB Disk</li><li><i class="fa-solid fa-check"></i> 100 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 100 Listeners</li><li><i class="fa-solid fa-check"></i> 192 kbps</li><li><i class="fa-solid fa-check"></i> 5 GB Storage</li><li><i class="fa-solid fa-check"></i> 10 Emails</li><li><i class="fa-solid fa-check"></i> 5 Databases</li><li><i class="fa-solid fa-check"></i> 10 Subdomains</li><li><i class="fa-solid fa-check"></i> 5 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=49&name=Test+All+Features&price=0" class="btn">Order Now →</a>
<a href="/product/49" class="btn-outline">Read More →</a>
</div>
</div>
<div class="review-dots" id="pdots-icecast">
<span class="dot active" onclick="showPkg(0, 'icecast')"></span>
<span class="dot" onclick="showPkg(1, 'icecast')"></span>
<span class="dot" onclick="showPkg(2, 'icecast')"></span>
<span class="dot" onclick="showPkg(3, 'icecast')"></span>
<span class="dot" onclick="showPkg(4, 'icecast')"></span>
<span class="dot" onclick="showPkg(5, 'icecast')"></span>
<span class="dot" onclick="showPkg(6, 'icecast')"></span>
<span class="dot" onclick="showPkg(7, 'icecast')"></span>
<span class="dot" onclick="showPkg(8, 'icecast')"></span>
<span class="dot" onclick="showPkg(9, 'icecast')"></span>
<span class="dot" onclick="showPkg(10, 'icecast')"></span>
</div>
<div class="review-nav" style="margin-top:8px">
<button onclick="prevPkg('icecast')">← Prev</button>
<button onclick="nextPkg('icecast')">Next →</button>
</div>
</div>
<div class="pricing-rotate-col">
<div style="text-align:center;margin-bottom:10px">
<span style="font-size:28px">🎵</span>
<h3 style="font-size:16px;margin:4px 0 2px">Icecast Reseller</h3>
<div style="font-size:11px;color:#64748b">10 plans</div>
</div>
<div class="pricing-rotate" id="prota-icecast_reseller">
<div class="pkg-rotate active" data-type="icecast_reseller" data-index="0">
<h3>Radio Reseller Mini</h3>
<div class="subtitle">Start reselling radio hosting.</div>
<div class="price">$14.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 5 GB Disk</li><li><i class="fa-solid fa-check"></i> 50 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 25 Listeners</li><li><i class="fa-solid fa-check"></i> 64 kbps</li><li><i class="fa-solid fa-check"></i> 1 GB Storage</li><li><i class="fa-solid fa-check"></i> 10 Emails</li><li><i class="fa-solid fa-check"></i> 5 Databases</li><li><i class="fa-solid fa-check"></i> 10 Subdomains</li><li><i class="fa-solid fa-check"></i> 2 Addon Domains</li><li><i class="fa-solid fa-check"></i> 2 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=39&name=Radio+Reseller+Mini&price=14.99" class="btn">Order Now →</a>
<a href="/product/39" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast_reseller" data-index="1">
<h3>Radio Reseller Basic</h3>
<div class="subtitle">Foundational reseller package.</div>
<div class="price">$24.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 10 GB Disk</li><li><i class="fa-solid fa-check"></i> 100 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 50 Listeners</li><li><i class="fa-solid fa-check"></i> 96 kbps</li><li><i class="fa-solid fa-check"></i> 2 GB Storage</li><li><i class="fa-solid fa-check"></i> 20 Emails</li><li><i class="fa-solid fa-check"></i> 10 Databases</li><li><i class="fa-solid fa-check"></i> 20 Subdomains</li><li><i class="fa-solid fa-check"></i> 5 Addon Domains</li><li><i class="fa-solid fa-check"></i> 5 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=40&name=Radio+Reseller+Basic&price=24.99" class="btn">Order Now →</a>
<a href="/product/40" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast_reseller" data-index="2">
<h3>Radio Reseller Standard</h3>
<div class="subtitle">Standard radio reseller.</div>
<div class="price">$39.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 20 GB Disk</li><li><i class="fa-solid fa-check"></i> 200 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 100 Listeners</li><li><i class="fa-solid fa-check"></i> 128 kbps</li><li><i class="fa-solid fa-check"></i> 5 GB Storage</li><li><i class="fa-solid fa-check"></i> 50 Emails</li><li><i class="fa-solid fa-check"></i> 20 Databases</li><li><i class="fa-solid fa-check"></i> 50 Subdomains</li><li><i class="fa-solid fa-check"></i> 10 Addon Domains</li><li><i class="fa-solid fa-check"></i> 10 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=41&name=Radio+Reseller+Standard&price=39.99" class="btn">Order Now →</a>
<a href="/product/41" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast_reseller" data-index="3">
<h3>Radio Reseller Advanced</h3>
<div class="subtitle">Advanced reseller capabilities.</div>
<div class="price">$59.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 40 GB Disk</li><li><i class="fa-solid fa-check"></i> 400 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 200 Listeners</li><li><i class="fa-solid fa-check"></i> 192 kbps</li><li><i class="fa-solid fa-check"></i> 10 GB Storage</li><li><i class="fa-solid fa-check"></i> 100 Emails</li><li><i class="fa-solid fa-check"></i> 40 Databases</li><li><i class="fa-solid fa-check"></i> 100 Subdomains</li><li><i class="fa-solid fa-check"></i> 20 Addon Domains</li><li><i class="fa-solid fa-check"></i> 20 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=42&name=Radio+Reseller+Advanced&price=59.99" class="btn">Order Now →</a>
<a href="/product/42" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast_reseller" data-index="4">
<h3>Radio Reseller Pro</h3>
<div class="subtitle">Professional radio reseller.</div>
<div class="price">$89.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 75 GB Disk</li><li><i class="fa-solid fa-check"></i> 750 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 400 Listeners</li><li><i class="fa-solid fa-check"></i> 256 kbps</li><li><i class="fa-solid fa-check"></i> 20 GB Storage</li><li><i class="fa-solid fa-check"></i> 200 Emails</li><li><i class="fa-solid fa-check"></i> 75 Databases</li><li><i class="fa-solid fa-check"></i> 200 Subdomains</li><li><i class="fa-solid fa-check"></i> 40 Addon Domains</li><li><i class="fa-solid fa-check"></i> 40 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=43&name=Radio+Reseller+Pro&price=89.99" class="btn">Order Now →</a>
<a href="/product/43" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast_reseller" data-index="5">
<h3>Radio Reseller Mini Plus</h3>
<div class="subtitle">Mini reseller with live chat.</div>
<div class="price">$19.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 8 GB Disk</li><li><i class="fa-solid fa-check"></i> 75 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 35 Listeners</li><li><i class="fa-solid fa-check"></i> 80 kbps</li><li><i class="fa-solid fa-check"></i> 2 GB Storage</li><li><i class="fa-solid fa-check"></i> 15 Emails</li><li><i class="fa-solid fa-check"></i> 8 Databases</li><li><i class="fa-solid fa-check"></i> 15 Subdomains</li><li><i class="fa-solid fa-check"></i> 3 Addon Domains</li><li><i class="fa-solid fa-check"></i> 3 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=44&name=Radio+Reseller+Mini+Plus&price=19.99" class="btn">Order Now →</a>
<a href="/product/44" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast_reseller" data-index="6">
<h3>Radio Reseller Standard Plus</h3>
<div class="subtitle">Standard reseller with support.</div>
<div class="price">$49.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 30 GB Disk</li><li><i class="fa-solid fa-check"></i> 300 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 150 Listeners</li><li><i class="fa-solid fa-check"></i> 160 kbps</li><li><i class="fa-solid fa-check"></i> 8 GB Storage</li><li><i class="fa-solid fa-check"></i> 75 Emails</li><li><i class="fa-solid fa-check"></i> 30 Databases</li><li><i class="fa-solid fa-check"></i> 75 Subdomains</li><li><i class="fa-solid fa-check"></i> 15 Addon Domains</li><li><i class="fa-solid fa-check"></i> 15 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=45&name=Radio+Reseller+Standard+Plus&price=49.99" class="btn">Order Now →</a>
<a href="/product/45" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast_reseller" data-index="7">
<h3>Radio Reseller Business</h3>
<div class="subtitle">Business radio reseller.</div>
<div class="price">$79.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 60 GB Disk</li><li><i class="fa-solid fa-check"></i> 600 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 300 Listeners</li><li><i class="fa-solid fa-check"></i> 224 kbps</li><li><i class="fa-solid fa-check"></i> 15 GB Storage</li><li><i class="fa-solid fa-check"></i> 150 Emails</li><li><i class="fa-solid fa-check"></i> 60 Databases</li><li><i class="fa-solid fa-check"></i> 150 Subdomains</li><li><i class="fa-solid fa-check"></i> 30 Addon Domains</li><li><i class="fa-solid fa-check"></i> 30 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=46&name=Radio+Reseller+Business&price=79.99" class="btn">Order Now →</a>
<a href="/product/46" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast_reseller" data-index="8">
<h3>Radio Reseller Premium</h3>
<div class="subtitle">Premium reseller broadcasting.</div>
<div class="price">$129.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 100 GB Disk</li><li><i class="fa-solid fa-check"></i> 1000 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 600 Listeners</li><li><i class="fa-solid fa-check"></i> 320 kbps</li><li><i class="fa-solid fa-check"></i> 25 GB Storage</li><li><i class="fa-solid fa-check"></i> 300 Emails</li><li><i class="fa-solid fa-check"></i> 100 Databases</li><li><i class="fa-solid fa-check"></i> 300 Subdomains</li><li><i class="fa-solid fa-check"></i> 50 Addon Domains</li><li><i class="fa-solid fa-check"></i> 50 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=47&name=Radio+Reseller+Premium&price=129.99" class="btn">Order Now →</a>
<a href="/product/47" class="btn-outline">Read More →</a>
</div>
<div class="pkg-rotate" data-type="icecast_reseller" data-index="9">
<h3>Radio Reseller Ultimate</h3>
<div class="subtitle">Ultimate reseller package.</div>
<div class="price">$199.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 200 GB Disk</li><li><i class="fa-solid fa-check"></i> 2000 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> 1000 Listeners</li><li><i class="fa-solid fa-check"></i> 320 kbps</li><li><i class="fa-solid fa-check"></i> 40 GB Storage</li><li><i class="fa-solid fa-check"></i> 500 Emails</li><li><i class="fa-solid fa-check"></i> 200 Databases</li><li><i class="fa-solid fa-check"></i> 500 Subdomains</li><li><i class="fa-solid fa-check"></i> 100 Addon Domains</li><li><i class="fa-solid fa-check"></i> 100 DJ Accounts</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=48&name=Radio+Reseller+Ultimate&price=199.99" class="btn">Order Now →</a>
<a href="/product/48" class="btn-outline">Read More →</a>
</div>
</div>
<div class="review-dots" id="pdots-icecast_reseller">
<span class="dot active" onclick="showPkg(0, 'icecast_reseller')"></span>
<span class="dot" onclick="showPkg(1, 'icecast_reseller')"></span>
<span class="dot" onclick="showPkg(2, 'icecast_reseller')"></span>
<span class="dot" onclick="showPkg(3, 'icecast_reseller')"></span>
<span class="dot" onclick="showPkg(4, 'icecast_reseller')"></span>
<span class="dot" onclick="showPkg(5, 'icecast_reseller')"></span>
<span class="dot" onclick="showPkg(6, 'icecast_reseller')"></span>
<span class="dot" onclick="showPkg(7, 'icecast_reseller')"></span>
<span class="dot" onclick="showPkg(8, 'icecast_reseller')"></span>
<span class="dot" onclick="showPkg(9, 'icecast_reseller')"></span>
</div>
<div class="review-nav" style="margin-top:8px">
<button onclick="prevPkg('icecast_reseller')">← Prev</button>
<button onclick="nextPkg('icecast_reseller')">Next →</button>
</div>
</div>
<div class="pricing-rotate-col">
<div style="text-align:center;margin-bottom:10px">
<span style="font-size:28px">🎮</span>
<h3 style="font-size:16px;margin:4px 0 2px">Game Server</h3>
<div style="font-size:11px;color:#64748b">1 plan</div>
</div>
<div class="pricing-rotate" id="prota-game_server">
<div class="pkg-rotate active" data-type="game_server" data-index="0">
<h3>Game Server Demo</h3>
<div class="subtitle">Try game hosting with a demo server. Enter your own Steam App ID to install any supported game.</div>
<div class="price">$0.00<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 5 GB Disk</li><li><i class="fa-solid fa-check"></i> 100 GB Bandwidth</li><li><i class="fa-solid fa-check"></i> Free SSL</li>
<li><i class="fa-solid fa-check"></i> 24/7 Support</li>
</ul>
<a href="/cart.php?action=add&id=50&name=Game+Server+Demo&price=0" class="btn">Order Now →</a>
<a href="/product/50" class="btn-outline">Read More →</a>
</div>
</div>
<div class="review-dots" id="pdots-game_server">
<span class="dot active" onclick="showPkg(0, 'game_server')"></span>
</div>
<div class="review-nav" style="margin-top:8px">
<button onclick="prevPkg('game_server')">← Prev</button>
<button onclick="nextPkg('game_server')">Next →</button>
</div>
</div>
</div>
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
<div class="testimonial-grid">
<div class="testimonial-card">
<div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
<blockquote>"If you run an online radio station, stop looking and get this panel. Icecast integration is seamless, DJ management is intuitive, and your listeners get a professional experience. The embeddable player widget is perfect for our website."</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,#ec4899,#f43f5e);color:#fff">T</div><div><div class="name">This is only for Development - Alex H.</div><div class="role">Verified Client</div></div></div>
</div>
<div class="testimonial-card">
<div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i><i class="fa-regular fa-star"></i></div>
<blockquote>"Solid foundation with great potential. The WHM features are comprehensive and radio integration is unique. Some areas like the file manager and backup system need more work. Trust the team will keep improving."</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,#f59e0b,#f97316);color:#fff">T</div><div><div class="name">This is only for Development - Chris B.</div><div class="role">Verified Client</div></div></div>
</div>
<div class="testimonial-card">
<div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
<blockquote>"Finally a control panel that does everything. Domain management, hosting, radio streaming, billing — it is all here. No more juggling between cPanel, WHMCS, and separate streaming platforms. This is the future."</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,#10b981,#34d399);color:#fff">T</div><div><div class="name">This is only for Development - Rachel N.</div><div class="role">Verified Client</div></div></div>
</div>
<div class="testimonial-card">
<div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
<blockquote>"Had a few questions during setup and the support team was incredibly helpful. They even hopped on a remote support session to help configure our Icecast settings. You don&#039;t get this level of service anywhere else."</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,#a855f7,#d946ef);color:#fff">T</div><div><div class="name">This is only for Development - David P.</div><div class="role">Verified Client</div></div></div>
</div>
</div>
<div class="testimonial-scroll-wrap">
<button class="scroll-arrow left" onclick="scrollTestimonials(-1)"><i class="fa-solid fa-chevron-left"></i></button>
<div class="testimonial-scroll" id="testimonialScroll">
<div class="testimonial-scroll-card">
<div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i></div>
<blockquote>"For the features you get, the pricing is very competitive. WHM panel, radio streaming, billing, support tickets — everything included. The AutoDJ feature saves us thousands in DJ costs for overnight slots."</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,#008cff,#00e5ff);color:#fff">T</div><div><div class="name">This is only for Development - Emily C.</div><div class="role">Verified Client</div></div></div>
</div>
<div class="testimonial-scroll-card">
<div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
<blockquote>"The multi-tenant live chat system is amazing. Our support team can handle all customer queries from one place, and the WPF desktop app makes it even easier. Visitors love the quick response times."</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,#6366f1,#818cf8);color:#fff">T</div><div><div class="name">This is only for Development - Tom W.</div><div class="role">Verified Client</div></div></div>
</div>
<div class="testimonial-scroll-card">
<div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
<blockquote>"We migrated from cPanel and the transition was smooth. The billing system is comprehensive, live chat works great for our customers, and the Icecast streaming is rock solid. Support team responded to our queries within minutes."</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,#ec4899,#f43f5e);color:#fff">T</div><div><div class="name">This is only for Development - Lisa K.</div><div class="role">Verified Client</div></div></div>
</div>
<div class="testimonial-scroll-card">
<div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i></div>
<blockquote>"Solid hosting panel with all the features you would expect from a major control panel. The radio streaming integration is what sets it apart. Only gave 4 stars because I would love to see more theme options, but overall very happy."</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,#f59e0b,#f97316);color:#fff">T</div><div><div class="name">This is only for Development - Mike R.</div><div class="role">Verified Client</div></div></div>
</div>
<div class="testimonial-scroll-card">
<div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
<blockquote>"We run a 24/7 radio station and this panel handles everything perfectly. AutoDJ is fantastic, our DJs love the dedicated portal, and listener analytics give us great insights. Highly recommend to any broadcaster."</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,#10b981,#34d399);color:#fff">T</div><div><div class="name">This is only for Development - Sarah M.</div><div class="role">Verified Client</div></div></div>
</div>
<div class="testimonial-scroll-card">
<div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
<blockquote>"I have tried many hosting panels but Planet Hosts is on another level. The combination of WHM and Icecast streaming is exactly what we needed for our radio station. Setup was incredibly easy and the support team helped us get online within hours."</blockquote>
<div class="author"><div class="avatar" style="background:linear-gradient(135deg,#a855f7,#d946ef);color:#fff">T</div><div><div class="name">This is only for Development - John D.</div><div class="role">Verified Client</div></div></div>
</div>
</div>
<button class="scroll-arrow right" onclick="scrollTestimonials(1)"><i class="fa-solid fa-chevron-right"></i></button>
</div>
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
<a href="/hosting/Web+Hosting">Web Hosting</a>
<a href="/hosting/Web+Hosting">Web Hosting</a>
<a href="/hosting/Web+Hosting+Reseller">Web Hosting Reseller</a>
<a href="/hosting/Web+Hosting+Reseller">Web Hosting Reseller</a>
<a href="/hosting/Icecast+Streaming">Icecast Streaming</a>
<a href="/hosting/Icecast+Streaming">Icecast Streaming</a>
<a href="/hosting/Icecast+Reseller">Icecast Reseller</a>
<a href="/hosting/Icecast+Reseller">Icecast Reseller</a>
<a href="/hosting/VPS+Servers">VPS Servers</a>
<a href="/hosting/Dedicated+Servers">Dedicated Servers</a>
<a href="/hosting/Game+Servers">Game Servers</a>
<a href="/hosting/Chat+Room">Chat Room</a>
<a href="/hosting/Chat+Room+Voice">Chat Room Voice</a>
<a href="/hosting/Game+Servers">Game Servers</a>
</div>
<div class="footer-col">
<h4>Support</h4>
<a href="http://<?php echo $host; ?>:2082/">Client Login</a>
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
if(window.location.pathname.indexOf('/admin/')!==0&&window.location.port!=='2087'){(function(){var x=new XMLHttpRequest();x.open('POST','/admin/livechat/track',true);x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');x.send('page='+encodeURIComponent(window.location.pathname)+'&referrer='+encodeURIComponent(document.referrer)+'&url='+encodeURIComponent(window.location.href));})();}
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
