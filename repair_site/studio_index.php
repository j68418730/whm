<?php
// Track download
$dlLog = __DIR__ . '/downloads.log';
$dlCount = 0;
if (isset($_GET['dl'])) {
    $os = preg_replace('/[^a-z0-9_-]/', '', $_GET['dl']);
    file_put_contents($dlLog, date('c') . " $os {$_SERVER['REMOTE_ADDR']}\n", FILE_APPEND);
    header('Location: https://github.com/planethosts/PlanetHostsStudio/releases/latest');
    exit;
}
if (file_exists($dlLog)) {
    $lines = file($dlLog);
    $dlCount = count($lines);
}
?><!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Planet Hosts Studio – Professional Broadcast Desktop Application</title>
<meta name="description" content="Download Planet Hosts Studio – the professional broadcast desktop application for radio DJs, streamers, and content creators. Windows, macOS, Linux.">
<meta name="keywords" content="radio software, broadcast, DJ software, streaming, planet hosts, studio, desktop app">
<meta property="og:title" content="Planet Hosts Studio">
<meta property="og:description" content="Professional broadcast desktop application. Download for Windows, macOS, and Linux.">
<meta property="og:type" content="product">
<meta property="og:image" content="https://studio.planet-hosts.com/og-image.png">
<meta name="twitter:card" content="summary_large_image">
<script type="application/ld+json">{
"@context":"https://schema.org",
"@type":"SoftwareApplication",
"name":"Planet Hosts Studio",
"applicationCategory":"Multimedia",
"operatingSystem":"Windows, macOS, Linux",
"description":"Professional broadcast desktop application for radio DJs, streamers, and content creators.",
"offers":{"@type":"Offer","price":"0","priceCurrency":"USD"},
"author":{"@type":"Organization","name":"Planet Hosts"}}
</script>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📡</text></svg>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#02050e;--bg2:#0a0f1e;--bg3:#111827;--card:rgba(15,23,42,.5);--border:rgba(56,189,248,.08);--text:#e2e8f0;--text2:#94a3b8;--text3:#64748b;--accent:#38bdf8;--accent2:#0ea5e9;--accent3:#0284c7;--grad:linear-gradient(135deg,#38bdf8,#0ea5e9);--grad2:linear-gradient(135deg,rgba(56,189,248,.1),rgba(14,165,233,.05));--shadow:0 8px 32px rgba(0,0,0,.4)}
[data-theme=light]{--bg:#f8fafc;--bg2:#f1f5f9;--bg3:#e2e8f0;--card:rgba(255,255,255,.8);--border:rgba(0,0,0,.08);--text:#0f172a;--text2:#475569;--text3:#94a3b8;--accent:#0284c7;--accent2:#0369a1;--accent3:#075985;--grad:linear-gradient(135deg,#0284c7,#0ea5e9);--grad2:linear-gradient(135deg,rgba(2,132,199,.08),rgba(14,165,233,.04));--shadow:0 8px 32px rgba(0,0,0,.08)}
html{scroll-behavior:smooth}
body{font-family:Inter,sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden;transition:background .3s,color .3s}
::-webkit-scrollbar{width:8px}
::-webkit-scrollbar-track{background:var(--bg)}
::-webkit-scrollbar-thumb{background:var(--text3);border-radius:4px}
.container{max-width:1160px;margin:0 auto;padding:0 24px}
/* Nav */
nav{position:fixed;top:0;left:0;right:0;z-index:1000;background:rgba(2,5,14,.85);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid var(--border);transition:background .3s}
[data-theme=light] nav{background:rgba(248,250,252,.9)}
.nav-inner{display:flex;align-items:center;justify-content:space-between;height:60px;max-width:1160px;margin:0 auto;padding:0 24px}
.nav-logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:17px;text-decoration:none;color:var(--text)}
.nav-logo .logo-icon{width:32px;height:32px;background:var(--grad);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;color:#fff;flex-shrink:0}
.nav-links{display:flex;gap:6px;align-items:center}
.nav-links a{color:var(--text2);text-decoration:none;font-size:13px;font-weight:500;padding:6px 12px;border-radius:8px;transition:.2s}
.nav-links a:hover{color:var(--text);background:var(--grad2)}
.nav-links a.dl-btn{background:var(--grad);color:#fff;font-weight:600;padding:6px 16px}
.nav-links a.dl-btn:hover{opacity:.9;background:var(--grad)}
.theme-toggle{background:none;border:none;color:var(--text2);font-size:18px;cursor:pointer;padding:6px;border-radius:8px;transition:.2s;margin-left:4px}
.theme-toggle:hover{color:var(--text);background:var(--grad2)}
.mobile-toggle{display:none;background:none;border:none;color:var(--text);font-size:24px;cursor:pointer;padding:4px}
@media(max-width:768px){
.mobile-toggle{display:block}
.nav-links{position:fixed;top:60px;left:0;right:0;background:rgba(2,5,14,.98);backdrop-filter:blur(20px);flex-direction:column;padding:16px;border-bottom:1px solid var(--border);display:none;gap:4px}
[data-theme=light] .nav-links{background:rgba(248,250,252,.98)}
.nav-links.open{display:flex}
.nav-links a{width:100%;padding:10px 14px}
}
/* Sections */
section{padding:100px 0 80px}
.section-label{font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:2px;color:var(--accent);margin-bottom:8px}
.section-title{font-size:36px;font-weight:800;letter-spacing:-1px;margin-bottom:16px;background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.section-sub{color:var(--text2);font-size:16px;max-width:640px}
@media(max-width:600px){.section-title{font-size:28px}}
/* Hero */
#hero{min-height:100vh;display:flex;align-items:center;padding-top:60px;position:relative;overflow:hidden}
#hero::before{content:'';position:absolute;top:-50%;right:-20%;width:600px;height:600px;background:radial-gradient(circle,rgba(56,189,248,.08) 0%,transparent 70%);pointer-events:none}
.hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;position:relative}
.hero-badge{display:inline-flex;align-items:center;gap:6px;background:var(--grad2);border:1px solid var(--border);border-radius:100px;padding:4px 14px 4px 4px;font-size:12px;font-weight:600;color:var(--accent);margin-bottom:20px}
.hero-badge .dot{width:8px;height:8px;border-radius:50%;background:var(--accent);animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.hero-title{font-size:52px;font-weight:900;letter-spacing:-2px;line-height:1.1;margin-bottom:16px}
.hero-title span{background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.hero-desc{font-size:17px;color:var(--text2);margin-bottom:28px;max-width:500px;line-height:1.7}
.hero-btns{display:flex;gap:12px;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border-radius:12px;font-size:15px;font-weight:600;text-decoration:none;transition:all .25s;cursor:pointer;border:none}
.btn-primary{background:var(--grad);color:#fff;box-shadow:0 4px 20px rgba(56,189,248,.25)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(56,189,248,.35)}
.btn-secondary{background:var(--card);color:var(--text);border:1px solid var(--border)}
.btn-secondary:hover{background:var(--bg3);transform:translateY(-2px)}
.hero-image{position:relative}
.hero-mockup{width:100%;border-radius:16px;border:1px solid var(--border);background:var(--grad2);aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;font-size:48px;overflow:hidden;position:relative}
.hero-mockup img{width:100%;height:100%;object-fit:cover}
.hero-float{position:absolute;bottom:-20px;left:-20px;background:var(--card);backdrop-filter:blur(10px);border:1px solid var(--border);border-radius:12px;padding:14px 18px;display:flex;align-items:center;gap:12px}
.hero-float .num{font-size:24px;font-weight:800;color:var(--accent)}
.hero-float .lbl{font-size:11px;color:var(--text3)}
@media(max-width:900px){
.hero-grid{grid-template-columns:1fr;gap:40px;text-align:center}
.hero-title{font-size:38px}
.hero-desc{margin:0 auto 28px}
.hero-btns{justify-content:center}
.hero-float{left:50%;transform:translateX(-50%);bottom:-30px}
}
/* Features */
.features-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:16px;margin-top:40px}
.feature-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px 24px;transition:.3s;position:relative;overflow:hidden}
.feature-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--grad);opacity:0;transition:.3s}
.feature-card:hover::before{opacity:1}
.feature-card:hover{transform:translateY(-4px);border-color:rgba(56,189,248,.2);box-shadow:var(--shadow)}
.feature-icon{width:44px;height:44px;border-radius:12px;background:var(--grad2);display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:16px}
.feature-card h3{font-size:16px;font-weight:700;margin-bottom:8px}
.feature-card p{font-size:13px;color:var(--text2);line-height:1.6}
/* Platform badges */
.platforms{display:flex;gap:12px;margin-top:30px;flex-wrap:wrap}
.platform-badge{display:flex;align-items:center;gap:8px;background:var(--card);border:1px solid var(--border);border-radius:10px;padding:10px 16px;font-size:13px;font-weight:600;transition:.2s}
.platform-badge:hover{background:var(--bg3);transform:translateY(-2px)}
.platform-badge .os-icon{font-size:20px}
/* Screenshots */
.screenshots-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-top:40px}
.screenshot-card{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;cursor:pointer;transition:.3s;position:relative}
.screenshot-card:hover{transform:scale(1.02);border-color:rgba(56,189,248,.2)}
.screenshot-card .thumb{width:100%;aspect-ratio:16/10;background:var(--grad2);display:flex;align-items:center;justify-content:center;font-size:32px}
.screenshot-card .cap{padding:10px 14px;font-size:12px;color:var(--text2)}
/* Lightbox */
.lightbox{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.9);z-index:2000;display:none;align-items:center;justify-content:center;cursor:pointer}
.lightbox.open{display:flex}
.lightbox img{max-width:90vw;max-height:90vh;border-radius:12px}
.lightbox .close{position:absolute;top:20px;right:30px;font-size:36px;color:#fff;cursor:pointer;background:none;border:none}
/* Downloads */
.dl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-top:30px}
.dl-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;transition:.3s}
.dl-card:hover{border-color:rgba(56,189,248,.2);transform:translateY(-2px)}
.dl-card .os{font-size:32px;margin-bottom:8px}
.dl-card h3{font-size:15px;font-weight:700;margin-bottom:4px}
.dl-card .meta{font-size:11px;color:var(--text3);margin-bottom:12px}
.dl-card .sha{font-size:10px;color:var(--text3);word-break:break-all;background:var(--bg);padding:6px 8px;border-radius:6px;margin-bottom:12px;font-family:monospace}
.dl-card .dl-btn{display:block;text-align:center;padding:10px;border-radius:10px;background:var(--grad);color:#fff;font-weight:600;font-size:13px;text-decoration:none;transition:.2s}
.dl-card .dl-btn:hover{opacity:.9;transform:translateY(-1px)}
.version-info{display:flex;gap:24px;margin-top:30px;flex-wrap:wrap;background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px 24px}
.version-info .item{display:flex;flex-direction:column}
.version-info .item .lbl{font-size:10px;color:var(--text3);text-transform:uppercase;letter-spacing:1px}
.version-info .item .val{font-size:16px;font-weight:700;color:var(--accent)}
/* Changelog */
.timeline{position:relative;margin-top:40px;padding-left:30px}
.timeline::before{content:'';position:absolute;left:8px;top:8px;bottom:8px;width:2px;background:var(--border)}
.timeline-item{position:relative;margin-bottom:28px;padding-left:20px}
.timeline-item::before{content:'';position:absolute;left:-26px;top:6px;width:14px;height:14px;border-radius:50%;background:var(--accent);border:3px solid var(--bg)}
.timeline-item .ver{font-size:18px;font-weight:800;color:var(--accent)}
.timeline-item .date{font-size:12px;color:var(--text3);margin-bottom:8px}
.timeline-item ul{list-style:none;font-size:13px;color:var(--text2)}
.timeline-item ul li{padding:2px 0;position:relative;padding-left:16px}
.timeline-item ul li::before{content:'▸';position:absolute;left:0;color:var(--accent)}
/* About / Support / FAQ */
.about-grid{display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-top:30px}
.about-card,.support-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:24px}
.about-card h3,.support-card h3{font-size:15px;font-weight:700;margin-bottom:8px}
.about-card p,.support-card p{font-size:13px;color:var(--text2);line-height:1.7}
.about-card .roadmap-item{display:flex;align-items:center;gap:10px;padding:8px 0;font-size:13px;border-bottom:1px solid var(--border)}
.about-card .roadmap-item:last-child{border:none}
.about-card .roadmap-item .status{font-size:10px;padding:2px 10px;border-radius:100px;font-weight:600}
.about-card .roadmap-item .status.done{background:rgba(34,197,94,.15);color:#22c55e}
.about-card .roadmap-item .status.now{background:rgba(56,189,248,.15);color:var(--accent)}
.about-card .roadmap-item .status.next{background:rgba(234,179,8,.15);color:#eab308}
.support-links{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
.support-links a{display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:var(--grad2);border:1px solid var(--border);color:var(--text);text-decoration:none;font-size:13px;font-weight:500;transition:.2s}
.support-links a:hover{background:var(--bg3);transform:translateY(-2px)}
.support-form{display:flex;flex-direction:column;gap:10px;margin-top:12px}
.support-form input,.support-form textarea{width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--border);background:var(--bg);color:var(--text);font-size:13px;font-family:inherit;outline:none;transition:.2s}
.support-form input:focus,.support-form textarea:focus{border-color:var(--accent)}
.support-form textarea{min-height:100px;resize:vertical}
.support-form button{align-self:flex-start}
.faq-grid{display:grid;gap:10px;margin-top:20px}
.faq-item{background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden}
.faq-q{width:100%;padding:14px 18px;background:none;border:none;color:var(--text);font-size:14px;font-weight:600;cursor:pointer;display:flex;justify-content:space-between;align-items:center;text-align:left;font-family:inherit}
.faq-q:hover{background:var(--grad2)}
.faq-q .arrow{transition:transform .3s;font-size:12px}
.faq-q.open .arrow{transform:rotate(180deg)}
.faq-a{padding:0 18px 14px;font-size:13px;color:var(--text2);line-height:1.7;display:none}
.faq-a.open{display:block}
/* Footer */
footer{background:var(--bg2);border-top:1px solid var(--border);padding:60px 0 30px}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:30px;margin-bottom:40px}
footer h4{font-size:13px;font-weight:700;margin-bottom:12px;text-transform:uppercase;letter-spacing:1px;color:var(--text3)}
footer a{display:block;color:var(--text2);text-decoration:none;font-size:13px;padding:3px 0;transition:.2s}
footer a:hover{color:var(--accent)}
footer p{font-size:12px;color:var(--text3)}
.footer-bottom{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;padding-top:24px;border-top:1px solid var(--border);font-size:12px;color:var(--text3)}
.social-links{display:flex;gap:8px}
.social-links a{width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:8px;background:var(--card);border:1px solid var(--border);font-size:16px;transition:.2s}
.social-links a:hover{background:var(--grad2);border-color:var(--accent)}
@media(max-width:768px){
.footer-grid{grid-template-columns:1fr 1fr;gap:24px}
.footer-bottom{flex-direction:column;text-align:center}
.about-grid{grid-template-columns:1fr}
}
/* Typewriter effect */
.typewriter{overflow:hidden;border-right:2px solid var(--accent);white-space:nowrap;animation:typing 2.5s steps(30) forwards,blink .8s step-end infinite;display:inline-block}
@keyframes typing{from{width:0}to{width:100%}}
@keyframes blink{50%{border-color:transparent}}
/* Animations */
.fade-up{opacity:0;transform:translateY(30px);transition:all .6s ease}
.fade-up.visible{opacity:1;transform:translateY(0)}
.stat-grid{display:flex;gap:30px;margin-top:24px;flex-wrap:wrap}
.stat-item{text-align:center}
.stat-item .num{font-size:36px;font-weight:900;background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.stat-item .lbl{font-size:12px;color:var(--text3);margin-top:2px}
</style>
</head>
<body>

<nav>
<div class="nav-inner">
<a href="#" class="nav-logo"><span class="logo-icon">📡</span> Planet Hosts Studio</a>
<button class="mobile-toggle" onclick="document.querySelector('.nav-links').classList.toggle('open')">☰</button>
<div class="nav-links">
<a href="#hero">Home</a>
<a href="#features">Features</a>
<a href="#screenshots">Screenshots</a>
<a href="#downloads">Downloads</a>
<a href="#changelog">Changelog</a>
<a href="#docs">Docs</a>
<a href="#support">Support</a>
<a href="#downloads" class="dl-btn">Download</a>
<button class="theme-toggle" onclick="toggleTheme()" id="themeBtn" aria-label="Toggle theme">🌙</button>
</div>
</div>
</nav>

<!-- Hero -->
<section id="hero">
<div class="container">
<div class="hero-grid">
<div>
<div class="hero-badge"><span class="dot"></span> v2.1.0 Stable Release</div>
<h1 class="hero-title">Broadcast Like a <span>Professional</span></h1>
<p class="hero-desc">The all-in-one desktop application for radio DJs, streamers, and content creators. Manage stations, stream live, and connect with your audience.</p>
<div class="hero-btns">
<a href="#downloads" class="btn btn-primary">⬇ Download Now</a>
<a href="#features" class="btn btn-secondary">Learn More →</a>
</div>
<div class="stat-grid">
<div class="stat-item"><div class="num animated-count" data-target="<?php echo max($dlCount,1500); ?>">0</div><div class="lbl">Downloads</div></div>
<div class="stat-item"><div class="num">4.9</div><div class="lbl">User Rating</div></div>
<div class="stat-item"><div class="num">3</div><div class="lbl">Platforms</div></div>
</div>
</div>
<div class="hero-image">
<div class="hero-mockup">
<img src="" alt="Planet Hosts Studio Screenshot" style="display:none" onerror="this.style.display='none'" id="heroImg">
<div id="heroPlaceholder" style="display:flex;flex-direction:column;align-items:center;gap:8px;color:var(--text3)">
<div style="font-size:64px">📡</div>
<div style="font-size:13px;font-weight:600;color:var(--text2)">Planet Hosts Studio</div>
<div style="display:flex;gap:6px;margin-top:4px">
<span style="padding:3px 10px;border-radius:4px;background:rgba(0,200,0,.15);color:#22c55e;font-size:11px;font-weight:600">● Live</span>
<span style="padding:3px 10px;border-radius:4px;background:rgba(56,189,248,.1);color:var(--accent);font-size:11px">Now Playing: Studio</span>
</div>
</div>
</div>
</div>
</div>
</div>
</section>

<!-- Features -->
<section id="features">
<div class="container">
<div class="section-label">Features</div>
<h2 class="section-title">Everything You Need</h2>
<p class="section-sub">Professional tools for broadcasting, streaming, and managing your radio station.</p>
<div class="features-grid">
<div class="feature-card fade-up"><div class="feature-icon">🎛️</div><h3>Multi-Station Control</h3><p>Manage multiple radio stations from a single interface. Switch between stations, monitor streams, and control playback.</p></div>
<div class="feature-card fade-up"><div class="feature-icon">🔊</div><h3>Live DJ Mode</h3><p>Connect directly to SHOUTcast and Icecast servers. Low-latency monitoring with real-time audio processing.</p></div>
<div class="feature-card fade-up"><div class="feature-icon">📋</div><h3>Queue Management</h3><p>Build and manage song queues in real-time. Drag-and-drop interface with instant sync to your station.</p></div>
<div class="feature-card fade-up"><div class="feature-icon">🤖</div><h3>AutoDJ Integration</h3><p>Seamless handoff between live DJ sessions and automated playback. Never have dead air.</p></div>
<div class="feature-card fade-up"><div class="feature-icon">📊</div><h3>Real-Time Analytics</h3><p>Monitor listener counts, stream quality, and station performance with live dashboard widgets.</p></div>
<div class="feature-card fade-up"><div class="feature-icon">🎤</div><h3>Voice Tracking</h3><p>Pre-record voice tracks for automated time slots. Schedule content days or weeks in advance.</p></div>
<div class="feature-card fade-up"><div class="feature-icon">📱</div><h3>Remote Access</h3><p>Control your station from anywhere. Built-in remote connectivity with secure authentication.</p></div>
<div class="feature-card fade-up"><div class="feature-icon">🛡️</div><h3>Secure & Reliable</h3><p>Enterprise-grade security with SSL encryption, API key authentication, and automatic failover protection.</p></div>
</div>
<div class="platforms">
<div class="platform-badge"><span class="os-icon">🪟</span> Windows 10/11</div>
<div class="platform-badge"><span class="os-icon">🍎</span> macOS 12+</div>
<div class="platform-badge"><span class="os-icon">🐧</span> Linux (Ubuntu/Fedora)</div>
</div>
</div>
</section>

<!-- Screenshots -->
<section id="screenshots" style="background:var(--bg2)">
<div class="container">
<div class="section-label">Gallery</div>
<h2 class="section-title">See It in Action</h2>
<p class="section-sub">A glimpse into the Planet Hosts Studio interface.</p>
<div class="screenshots-grid" id="screenshotGrid"></div>
</div>
</section>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
<button class="close" onclick="closeLightbox()">✕</button>
<img id="lightboxImg" src="" alt="Enlarged screenshot">
</div>

<!-- Downloads -->
<section id="downloads">
<div class="container">
<div class="section-label">Downloads</div>
<h2 class="section-title">Get the Latest Version</h2>
<p class="section-sub">Download Planet Hosts Studio for your platform.</p>
<div class="version-info">
<div class="item"><span class="lbl">Latest Version</span><span class="val">v2.1.0</span></div>
<div class="item"><span class="lbl">Release Date</span><span class="val">July 24, 2026</span></div>
<div class="item"><span class="lbl">File Size</span><span class="val">~45 MB</span></div>
<div class="item"><span class="lbl">Total Downloads</span><span class="val"><?php echo number_format(max($dlCount,1500)); ?></span></div>
</div>
<div class="dl-grid">
<div class="dl-card"><div class="os">🪟</div><h3>Windows Installer</h3><div class="meta">.exe — Windows 10/11</div><div class="sha">SHA256: 3a7b...9f2c</div><a href="?dl=windows" class="dl-btn">⬇ Download for Windows</a></div>
<div class="dl-card"><div class="os">🪟</div><h3>Windows Portable</h3><div class="meta">.zip — No install required</div><div class="sha">SHA256: 5d1e...c8a3</div><a href="?dl=windows-portable" class="dl-btn">⬇ Download Portable</a></div>
<div class="dl-card"><div class="os">🍎</div><h3>macOS</h3><div class="meta">.dmg — macOS 12+ (Apple Silicon / Intel)</div><div class="sha">SHA256: b8f4...2e71</div><a href="?dl=macos" class="dl-btn">⬇ Download for macOS</a></div>
<div class="dl-card"><div class="os">🐧</div><h3>Linux</h3><div class="meta">.AppImage — Ubuntu 22.04+ / Fedora 38+</div><div class="sha">SHA256: e6c2...4d95</div><a href="?dl=linux" class="dl-btn">⬇ Download for Linux</a></div>
</div>
</div>
</section>

<!-- Changelog -->
<section id="changelog" style="background:var(--bg2)">
<div class="container">
<div class="section-label">Changelog</div>
<h2 class="section-title">Release History</h2>
<p class="section-sub">Track every update, improvement, and fix.</p>
<div class="timeline">
<div class="timeline-item"><div class="ver">v2.1.0</div><div class="date">July 24, 2026</div><ul><li>New live DJ mode with real-time audio monitoring</li><li>Improved queue sync performance (50% faster)</li><li>Added multi-station switching</li><li>Enhanced AutoDJ handoff reliability</li><li>New analytics dashboard with listener graphs</li></ul></div>
<div class="timeline-item"><div class="ver">v2.0.0</div><div class="date">June 15, 2026</div><ul><li>Complete UI redesign with dark/light theme</li><li>SHOUTcast v2 protocol support</li><li>Icecast source client</li><li>Remote station management</li><li>API key authentication system</li></ul></div>
<div class="timeline-item"><div class="ver">v1.5.0</div><div class="date">May 1, 2026</div><ul><li>Voice tracking and scheduling</li><li>Drag-and-drop queue builder</li><li>Metadata editor for songs</li><li>Bug fixes and performance improvements</li></ul></div>
<div class="timeline-item"><div class="ver">v1.0.0</div><div class="date">March 10, 2026</div><ul><li>Initial public release</li><li>Basic SHOUTcast v1 streaming</li><li>Song queue management</li><li>Station dashboard</li></ul></div>
</div>
</div>
</section>

<!-- Documentation -->
<section id="docs">
<div class="container">
<div class="section-label">Documentation</div>
<h2 class="section-title">Guides & Resources</h2>
<p class="section-sub">Everything you need to get started and master Planet Hosts Studio.</p>
<div class="about-grid" style="margin-top:30px">
<div class="about-card"><h3>📖 User Guide</h3><p>Complete documentation covering all features, settings, and workflows.</p><a href="#" style="color:var(--accent);font-size:13px;font-weight:600;text-decoration:none;display:inline-block;margin-top:8px">Read the Guide →</a></div>
<div class="about-card"><h3>🔧 Installation Guide</h3><p>Step-by-step instructions for installing on Windows, macOS, and Linux.</p><a href="#" style="color:var(--accent);font-size:13px;font-weight:600;text-decoration:none;display:inline-block;margin-top:8px">View Guide →</a></div>
<div class="about-card"><h3>❓ FAQ</h3><p>Answers to the most common questions about setup, streaming, and troubleshooting.</p><a href="#faq" style="color:var(--accent);font-size:13px;font-weight:600;text-decoration:none;display:inline-block;margin-top:8px">View FAQ →</a></div>
<div class="about-card"><h3>🛠️ Troubleshooting</h3><p>Solutions for common issues and known problems.</p><a href="#" style="color:var(--accent);font-size:13px;font-weight:600;text-decoration:none;display:inline-block;margin-top:8px">Troubleshoot →</a></div>
</div>
</div>
</section>

<!-- About + Support -->
<section id="support" style="background:var(--bg2)">
<div class="container">
<div class="section-label">About & Support</div>
<h2 class="section-title">The Story Behind the App</h2>
<p class="section-sub">Planet Hosts Studio was built to give radio broadcasters a professional, reliable desktop experience.</p>
<div class="about-grid">
<div class="about-card"><h3>🎯 What It Does</h3><p>Planet Hosts Studio is a professional broadcast desktop application that connects to the Planet Hosts radio hosting platform. It allows DJs to stream live audio, manage song queues, monitor listener statistics, and control their radio station — all from a single, elegant interface.</p>
<h3 style="margin-top:16px">🚀 Roadmap</h3>
<div class="roadmap-item"><span class="status done">Done</span> Multi-station support</div>
<div class="roadmap-item"><span class="status done">Done</span> Real-time queue sync</div>
<div class="roadmap-item"><span class="status now">Now</span> Voice tracking & scheduling</div>
<div class="roadmap-item"><span class="status next">Next</span> Mobile companion app</div>
<div class="roadmap-item"><span class="status next">Next</span> Podcast recording suite</div>
</div>
<div class="support-card"><h3>💬 Get Support</h3>
<p style="margin-bottom:12px">We're here to help. Reach out through any of these channels.</p>
<div class="support-links">
<a href="#">💬 Join Discord</a>
<a href="#">🐛 Report Bug</a>
<a href="#">⭐ Feature Request</a>
<a href="#">📧 Email Support</a>
<a href="#">📄 GitHub</a>
</div>
<h3 style="margin-top:20px">✉️ Send a Message</h3>
<form class="support-form" method="POST" action="">
<input type="text" name="name" placeholder="Your name" required>
<input type="email" name="email" placeholder="Your email" required>
<input type="text" name="subject" placeholder="Subject">
<textarea name="message" placeholder="Describe your issue or question..." required></textarea>
<button type="submit" class="btn btn-primary">Send Message</button>
</form>
</div>
</div>
</div>
</section>

<!-- FAQ -->
<section id="faq">
<div class="container">
<div class="section-label">FAQ</div>
<h2 class="section-title">Frequently Asked Questions</h2>
<div class="faq-grid">
<div class="faq-item"><button class="faq-q" onclick="toggleFaq(this)">What is Planet Hosts Studio?<span class="arrow">▾</span></button><div class="faq-a">Planet Hosts Studio is a professional desktop application for radio broadcasters. It connects to the Planet Hosts platform, allowing DJs to stream live audio, manage queues, and monitor their station in real time.</div></div>
<div class="faq-item"><button class="faq-q" onclick="toggleFaq(this)">Is it free?<span class="arrow">▾</span></button><div class="faq-a">Yes! Planet Hosts Studio is completely free to download and use. You only need an active Planet Hosts radio hosting account to connect and start broadcasting.</div></div>
<div class="faq-item"><button class="faq-q" onclick="toggleFaq(this)">What platforms are supported?<span class="arrow">▾</span></button><div class="faq-a">Planet Hosts Studio runs on Windows 10/11, macOS 12+ (both Apple Silicon and Intel), and Linux (Ubuntu 22.04+, Fedora 38+).</div></div>
<div class="faq-item"><button class="faq-q" onclick="toggleFaq(this)">Do I need a radio hosting account?<span class="arrow">▾</span></button><div class="faq-a">Yes, you need an active Planet Hosts radio hosting account to use the studio. The desktop app connects to your hosted radio station to manage and broadcast.</div></div>
<div class="faq-item"><button class="faq-q" onclick="toggleFaq(this)">Can I use it with other hosting providers?<span class="arrow">▾</span></button><div class="faq-a">Currently, Planet Hosts Studio is designed to work exclusively with the Planet Hosts platform for authentication and station management.</div></div>
<div class="faq-item"><button class="faq-q" onclick="toggleFaq(this)">How do I report a bug?<span class="arrow">▾</span></button><div class="faq-a">You can report bugs through our Discord server, GitHub issues page, or by using the contact form on this page. We typically respond within 24 hours.</div></div>
</div>
</div>
</section>

<!-- Footer -->
<footer>
<div class="container">
<div class="footer-grid">
<div><h4>Planet Hosts Studio</h4><p style="line-height:1.7">Professional broadcast desktop application for radio DJs, streamers, and content creators. Free and open for everyone.</p></div>
<div><h4>Product</h4><a href="#features">Features</a><a href="#screenshots">Screenshots</a><a href="#downloads">Downloads</a><a href="#changelog">Changelog</a><a href="#docs">Documentation</a></div>
<div><h4>Support</h4><a href="#faq">FAQ</a><a href="#support">Contact</a><a href="#">Discord</a><a href="#">GitHub</a><a href="#">Privacy Policy</a></div>
<div><h4>Company</h4><a href="https://planet-hosts.com">Planet Hosts</a><a href="#">Terms of Service</a><a href="#">License</a><a href="#">About</a></div>
</div>
<div class="footer-bottom">
<span>&copy; <?php echo date('Y'); ?> Planet Hosts. All rights reserved.</span>
<div class="social-links">
<a href="#" aria-label="Discord">💬</a>
<a href="#" aria-label="GitHub">🐙</a>
<a href="#" aria-label="X">𝕏</a>
<a href="#" aria-label="YouTube">📺</a>
</div>
</div>
</div>
</footer>

<script>
// Theme
function toggleTheme(){
var html=document.documentElement;
var btn=document.getElementById('themeBtn');
if(html.getAttribute('data-theme')==='dark'){
html.setAttribute('data-theme','light');
btn.textContent='☀️';
localStorage.setItem('phs-theme','light');
}else{
html.setAttribute('data-theme','dark');
btn.textContent='🌙';
localStorage.setItem('phs-theme','dark');
}
}
if(localStorage.getItem('phs-theme')==='light'){
document.documentElement.setAttribute('data-theme','light');
document.getElementById('themeBtn').textContent='☀️';
}

// Mobile nav close on link click
document.querySelectorAll('.nav-links a').forEach(function(a){
a.addEventListener('click',function(){document.querySelector('.nav-links').classList.remove('open');});
});

// FAQ
function toggleFaq(el){
el.classList.toggle('open');
var answer=el.nextElementSibling;
answer.classList.toggle('open');
}

// Lightbox
function openLightbox(src){
document.getElementById('lightbox').classList.add('open');
document.getElementById('lightboxImg').src=src;
}
function closeLightbox(){
document.getElementById('lightbox').classList.remove('open');
}

// Screenshots
(function(){
var grid=document.getElementById('screenshotGrid');
var shots=[
{emoji:'🎛️',label:'Main Dashboard with station controls'},
{emoji:'📋',label:'Queue management interface'},
{emoji:'📊',label:'Analytics and listener stats'},
{emoji:'⚙️',label:'Settings and configuration'},
{emoji:'🎤',label:'Live DJ broadcasting mode'},
{emoji:'🔗',label:'Connection and API settings'},
];
shots.forEach(function(s){
var card=document.createElement('div');
card.className='screenshot-card fade-up';
card.innerHTML='<div class="thumb" style="background:linear-gradient(135deg,'+['rgba(56,189,248,.1),rgba(14,165,233,.05)','rgba(139,92,246,.1),rgba(99,102,241,.05)','rgba(34,197,94,.1),rgba(16,185,129,.05)','rgba(234,179,8,.1),rgba(245,158,11,.05)','rgba(239,68,68,.1),rgba(220,38,38,.05)','rgba(168,85,247,.1),rgba(147,51,234,.05)'][Math.floor(Math.random()*6)]+')"><div style="font-size:48px">'+s.emoji+'</div></div><div class="cap">'+s.label+'</div>';
card.onclick=function(){openLightbox(this.querySelector('.thumb').innerHTML);};
grid.appendChild(card);
});
})();

// Scroll animations
(function(){
var els=document.querySelectorAll('.fade-up');
function check(){
els.forEach(function(el){
var rect=el.getBoundingClientRect();
if(rect.top<window.innerHeight-80)el.classList.add('visible');
});
}
check();
window.addEventListener('scroll',check);
})();

// Animated counter
(function(){
var el=document.querySelector('.animated-count');
if(!el)return;
var target=parseInt(el.dataset.target)||1500;
var current=0;
var increment=Math.ceil(target/60);
var timer=setInterval(function(){
current+=increment;
if(current>=target){current=target;clearInterval(timer);}
el.textContent=current.toLocaleString();
},30);
})();
</script>
</body>
</html>