<?php
$showLogin = isset($_GET['login']);
$loginError = isset($loginError) ? $loginError : null;
$loggedIn = isset($loggedIn) ? $loggedIn : false;
$user = isset($user) ? $user : null;
$packagesByType = isset($packagesByType) ? $packagesByType : [];
$at2 = 'planethosts';
try { if (class_exists('\\Core\\Application')) { $a2 = \Core\Application::getInstance(); $d2 = $a2->get('db'); if ($d2) { $r2 = $d2->table('automation_settings')->get() ?: []; $s2 = []; foreach ($r2 as $x) $s2[$x->setting_key] = $x->setting_value; $at2 = $s2['theme'] ?? 'planethosts'; } } } catch(\Exception $e) {}
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<title>Planet Hosts — The World's First WHM + Radio Streaming Panel</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#02050e;color:#fff;font-family:'Inter',sans-serif;overflow-x:hidden}
::-webkit-scrollbar{width:6px}::-webkit-scrollbar-track{background:#02050e}::-webkit-scrollbar-thumb{background:rgba(0,191,255,.2);border-radius:3px}
.hero{min-height:100vh;display:flex;flex-direction:column;position:relative;overflow:hidden}
.hero-bg{position:absolute;inset:0;z-index:0}
.hero-bg .grad{position:absolute;inset:0;background:radial-gradient(ellipse at 20% 50%,rgba(0,100,255,.08),transparent 60%),radial-gradient(ellipse at 80% 20%,rgba(0,200,255,.05),transparent 50%),radial-gradient(ellipse at 50% 80%,rgba(100,0,255,.04),transparent 50%)}
.hero-bg .grid{position:absolute;inset:0;background-image:linear-gradient(rgba(0,191,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(0,191,255,.03) 1px,transparent 1px);background-size:60px 60px;mask-image:radial-gradient(ellipse at center,black 30%,transparent 70%);-webkit-mask-image:radial-gradient(ellipse at center,black 30%,transparent 70%)}
.particles{position:absolute;inset:0;overflow:hidden}
@keyframes float{0%,100%{transform:translateY(0) scale(1);opacity:0}10%{opacity:.3}90%{opacity:.3}100%{opacity:0}}
.particle{position:absolute;width:3px;height:3px;background:var(--accent,#008cff);border-radius:50%;animation:float 8s infinite}
.particle:nth-child(1){left:10%;top:20%;--accent:#008cff;animation-delay:0s;width:4px;height:4px}
.particle:nth-child(2){left:30%;top:60%;--accent:#00e5ff;animation-delay:1.5s}
.particle:nth-child(3){left:50%;top:30%;--accent:#7c3aed;animation-delay:3s;width:5px;height:5px}
.particle:nth-child(4){left:70%;top:70%;--accent:#008cff;animation-delay:4.5s}
.particle:nth-child(5){left:85%;top:40%;--accent:#00e5ff;animation-delay:2s;width:4px;height:4px}
.particle:nth-child(6){left:20%;top:85%;--accent:#7c3aed;animation-delay:5.5s}
.particle:nth-child(7){left:60%;top:10%;--accent:#008cff;animation-delay:.8s;width:3px;height:3px}
.particle:nth-child(8){left:90%;top:80%;--accent:#00e5ff;animation-delay:3.5s}
.container{width:92%;max-width:1320px;margin:auto;position:relative;z-index:1}
nav{display:flex;justify-content:space-between;align-items:center;padding:20px 0}
.logo{display:flex;align-items:center;gap:14px;text-decoration:none}
.logo .icon{width:44px;height:44px;background:linear-gradient(135deg,#008cff,#00e5ff);border-radius:12px;display:flex;align-items:center;justify-content:center;font-family:'Orbitron',sans-serif;font-weight:900;font-size:20px;color:#fff;box-shadow:0 0 30px rgba(0,140,255,.3)}
.logo h1{font-family:'Orbitron',sans-serif;font-size:1.5rem;color:#fff;letter-spacing:1px}
.logo h1 span{color:#008cff}
.nav-links{display:flex;align-items:center;gap:8px}
.nav-links a{padding:8px 18px;border-radius:8px;text-decoration:none;color:#94a3b8;font-size:14px;font-weight:500;transition:.3s}
.nav-links a:hover{color:#fff;background:rgba(255,255,255,.04)}
.nav-links .btn-primary{padding:10px 24px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border-radius:10px;font-weight:600;box-shadow:0 0 25px rgba(0,140,255,.25)}
.nav-links .btn-primary:hover{transform:translateY(-2px);box-shadow:0 0 35px rgba(0,140,255,.35)}
.hero-content{flex:1;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;padding:40px 0 60px}
.hero-content p{text-align:center;margin-left:auto;margin-right:auto}
.hero-content .hero-btns{justify-content:center}
.hero-content .domain-search{margin-left:auto;margin-right:auto}
.hero-content .domain-search h3{text-align:center}
.hero-content .stats-ticker{justify-content:center}
.hero-content .badge{margin-left:auto;margin-right:auto}
.badge{display:inline-flex;align-items:center;gap:8px;padding:8px 18px;background:rgba(0,140,255,.08);border:1px solid rgba(0,191,255,.15);border-radius:50px;font-size:13px;color:#38bdf8;margin-bottom:24px;width:fit-content}
.badge i{font-size:14px}
.hero-content h2{font-size:clamp(2.4rem,6vw,4.8rem);font-weight:800;line-height:1.08;margin-bottom:16px;letter-spacing:-1px}
.hero-content h2 .highlight{background:linear-gradient(135deg,#008cff,#00e5ff,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-content h2 .outline{color:transparent;-webkit-text-stroke:1.5px rgba(255,255,255,.3)}
.hero-content p{font-size:clamp(1rem,1.5vw,1.2rem);color:#64748b;max-width:620px;line-height:1.8;margin-bottom:32px;text-align:center}
.hero-btns{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:40px}
.hero-btns .btn{padding:14px 32px;border-radius:12px;text-decoration:none;font-weight:600;font-size:15px;transition:.3s;display:inline-flex;align-items:center;gap:8px}
.hero-btns .btn-primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;box-shadow:0 0 30px rgba(0,140,255,.3)}
.hero-btns .btn-primary:hover{transform:translateY(-3px);box-shadow:0 0 50px rgba(0,140,255,.4)}
.hero-btns .btn-secondary{border:1px solid rgba(255,255,255,.1);color:#e0e0e0;background:rgba(255,255,255,.03)}
.hero-btns .btn-secondary:hover{background:rgba(255,255,255,.08);transform:translateY(-3px)}
.domain-search{background:rgba(8,16,28,.8);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:28px;backdrop-filter:blur(20px);max-width:700px}
.domain-search h3{font-size:15px;font-weight:600;margin-bottom:14px;color:#e0e0e0}
.domain-search h3 i{color:#facc15;margin-right:8px}
.search-row{display:flex;gap:0;margin-bottom:10px}
.search-row input{flex:1;padding:14px 18px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.08);border-right:none;border-radius:10px 0 0 10px;color:#fff;font-size:16px;outline:none;font-family:'Inter',sans-serif}
.search-row input:focus{border-color:rgba(0,191,255,.3)}
.search-row select{padding:14px 12px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.08);border-left:none;border-right:none;color:#e0e0e0;font-size:14px;outline:none;cursor:pointer;font-family:'Inter',sans-serif;min-width:85px}
.search-row select option{background:#0a0f1a;color:#e0e0e0}
.search-row button{padding:14px 28px;background:linear-gradient(135deg,#008cff,#3bb8ff);border:none;border-radius:0 10px 10px 0;color:#fff;font-weight:600;font-size:15px;cursor:pointer;transition:.3s;white-space:nowrap;font-family:'Inter',sans-serif}
.search-row button:hover{background:linear-gradient(135deg,#0070dd,#2a9fff)}
.domain-result{margin-top:12px;padding:12px 16px;border-radius:8px;font-size:14px;display:none}
.domain-result.available{display:block;background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.2);color:#4ade80}
.domain-result.taken{display:block;background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);color:#f87171}
.domain-result .price{float:right;font-weight:700}
.domain-result i{margin-right:8px}
.suggestions{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}
.suggestions .sug{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);border-radius:6px;padding:6px 12px;font-size:12px;cursor:pointer;transition:.2s;color:#94a3b8}
.suggestions .sug:hover{background:rgba(0,140,255,.1);border-color:rgba(0,140,255,.2);color:#fff}
.suggestions .sug .sug-price{color:#4ade80;font-weight:600;margin-left:4px}
.domain-tlds{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;font-size:11px;color:#64748b}
.domain-tlds span{padding:2px 8px;border-radius:4px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.04)}
.stats-ticker{display:flex;gap:30px;margin-top:28px;flex-wrap:wrap}
.stats-ticker .stat{text-align:center}
.stats-ticker .stat .num{font-size:28px;font-weight:800;background:linear-gradient(135deg,#008cff,#00e5ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.stats-ticker .stat .label{font-size:12px;color:#64748b;margin-top:2px}
section{padding:90px 0}
.section-title{text-align:center;margin-bottom:50px}
.section-title h2{font-size:clamp(1.8rem,3vw,2.6rem);font-weight:800;margin-bottom:12px}
.section-title h2 span{background:linear-gradient(135deg,#008cff,#00e5ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.section-title p{color:#64748b;font-size:1.05rem;max-width:560px;margin:auto;line-height:1.7}
.features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px}
.feature-card{background:rgba(8,16,28,.5);border:1px solid rgba(0,191,255,.08);border-radius:14px;padding:30px;transition:.35s;position:relative;overflow:hidden}
.feature-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--accent,#008cff),transparent);opacity:0;transition:.35s}
.feature-card:hover{transform:translateY(-6px);border-color:rgba(0,191,255,.2);box-shadow:0 20px 60px rgba(0,0,0,.3)}
.feature-card:hover::before{opacity:1}
.feature-card .icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:16px}
.feature-card h3{font-size:16px;font-weight:700;margin-bottom:8px}
.feature-card p{color:#64748b;font-size:13px;line-height:1.7}
.feature-card .tag{display:inline-block;padding:2px 10px;border-radius:4px;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-top:10px}
.tag-new{background:rgba(74,222,128,.12);color:#4ade80}
.tag-coming{background:rgba(251,191,36,.12);color:#fbbf24}
.tag-radio{background:rgba(0,191,255,.12);color:#38bdf8}
.tag-hot{background:rgba(248,113,113,.12);color:#f87171}
.unique-section{background:linear-gradient(135deg,rgba(0,100,255,.04),rgba(100,0,255,.04));border-top:1px solid rgba(0,191,255,.06);border-bottom:1px solid rgba(0,191,255,.06)}
.unique-grid{display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:center}
.unique-grid .text h2{font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;margin-bottom:16px}
.unique-grid .text h2 span{background:linear-gradient(135deg,#008cff,#00e5ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.unique-grid .text p{color:#64748b;line-height:1.8;margin-bottom:20px}
.unique-grid .text ul{list-style:none;padding:0}
.unique-grid .text ul li{padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04);display:flex;align-items:center;gap:12px;font-size:14px;color:#cbd5e1}
.unique-grid .text ul li i{width:20px;color:#4ade80}
.unique-grid .visual{display:flex;justify-content:center;align-items:center}
.unique-grid .visual .stack{width:100%;max-width:380px;display:flex;flex-direction:column;gap:8px}
.unique-grid .visual .stack-item{display:flex;align-items:center;gap:12px;padding:16px 20px;background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.1);border-radius:10px;transition:.3s}
.unique-grid .visual .stack-item:hover{background:rgba(8,16,28,.9);border-color:rgba(0,191,255,.2);transform:translateX(6px)}
.unique-grid .visual .stack-item .si-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.unique-grid .visual .stack-item .si-text{font-size:13px}
.unique-grid .visual .stack-item .si-text strong{display:block;font-size:14px}
.unique-grid .visual .stack-item .si-text span{color:#64748b;font-size:12px}
.radio-showcase{position:relative;overflow:hidden}
.radio-showcase::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 30% 50%,rgba(0,140,255,.04),transparent 60%),radial-gradient(ellipse at 70% 50%,rgba(124,58,237,.04),transparent 60%);pointer-events:none}
.pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:20px}
.pricing-card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.08);border-radius:16px;padding:32px;transition:.35s;position:relative}
.pricing-card:hover{transform:translateY(-6px);border-color:rgba(0,191,255,.2);box-shadow:0 20px 60px rgba(0,0,0,.3)}
.pricing-card.featured{border-color:rgba(0,191,255,.3);background:rgba(8,16,28,.9);box-shadow:0 0 40px rgba(0,140,255,.08)}
.pricing-card.featured .popular{position:absolute;top:-12px;left:50%;transform:translateX(-50%);padding:4px 20px;background:linear-gradient(135deg,#008cff,#3bb8ff);border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap}
.pricing-card .pkg-icon{font-size:32px;margin-bottom:12px}
.pricing-card h3{font-size:18px;font-weight:700;margin-bottom:4px}
.pricing-card .subtitle{color:#64748b;font-size:13px;margin-bottom:16px}
.pricing-card .price{font-size:36px;font-weight:800;margin-bottom:4px}
.pricing-card .price span{font-size:14px;font-weight:400;color:#64748b}
.pricing-card ul{list-style:none;padding:0;margin:16px 0 24px}
.pricing-card ul li{padding:8px 0;display:flex;align-items:center;gap:10px;font-size:13px;color:#cbd5e1;border-bottom:1px solid rgba(255,255,255,.04)}
.pricing-card ul li i{color:#4ade80;font-size:12px;width:16px}
.pricing-card .btn{display:block;text-align:center;padding:12px;background:linear-gradient(135deg,#008cff,#3bb8ff);border-radius:10px;color:#fff;text-decoration:none;font-weight:600;font-size:14px;transition:.3s}
.pricing-card .btn:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(0,140,255,.3)}
.pricing-card .btn-outline{border:1px solid rgba(0,191,255,.15);background:transparent;color:#e0e0e0}
.pricing-card .btn-outline:hover{background:rgba(0,140,255,.08);border-color:rgba(0,140,255,.3)}
.support-section{background:rgba(8,16,28,.5);border-top:1px solid rgba(0,191,255,.06);border-bottom:1px solid rgba(0,191,255,.06)}
.reviews-section{background:linear-gradient(135deg,rgba(0,100,255,.02),rgba(100,0,255,.02))}
.reviews-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;max-width:900px;margin:auto}
.review-card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:24px;transition:.3s;display:none}
.review-card.active{display:block}
.review-card:hover{border-color:rgba(0,191,255,.18)}
.review-card .stars{color:#facc15;font-size:16px;margin-bottom:8px}
.review-card .rtext{color:#cbd5e1;font-size:13px;line-height:1.7;margin-bottom:12px;font-style:italic}
.review-card .rauthor{display:flex;align-items:center;gap:10px}
.review-card .rauthor .rname{font-weight:600;font-size:13px}
.review-card .rauthor .rdate{color:#64748b;font-size:11px}
.review-dots{display:flex;justify-content:center;gap:8px;margin-top:20px}
.review-dots .dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.15);cursor:pointer;transition:.3s}
.review-dots .dot.active{background:#008cff;width:24px;border-radius:4px}
.review-nav{display:flex;justify-content:center;gap:12px;margin-top:16px}
.review-nav button{background:rgba(0,140,255,.08);border:1px solid rgba(0,191,255,.12);border-radius:8px;padding:8px 18px;color:#e0e0e0;cursor:pointer;font-size:13px;transition:.3s;font-family:'Inter',sans-serif}
.review-nav button:hover{background:rgba(0,140,255,.15)}
.review-avg{text-align:center;margin-bottom:30px}
.review-avg .big-star{font-size:42px;color:#facc15}
.review-avg .avg-text{font-size:15px;color:#64748b;margin-top:4px}
.pricing-rotate-col{background:rgba(8,16,28,.4);border:1px solid rgba(0,191,255,.08);border-radius:14px;padding:18px;transition:.3s}
.pricing-rotate-col:hover{border-color:rgba(0,191,255,.18);background:rgba(8,16,28,.6)}
.pricing-rotate{position:relative;min-height:320px}
.pkg-rotate{display:none}
.pkg-rotate.active{display:block}
.pricing-rotate-col .pricing-card{border:1px solid rgba(0,191,255,.1);border-radius:10px;padding:16px;background:rgba(0,0,0,.2)}
.pricing-rotate-col .pricing-card ul{margin:10px 0 14px}
.pricing-rotate-col .pricing-card ul li{padding:4px 0;font-size:12px}
.pricing-rotate-col .pricing-card .price{font-size:24px}
.pricing-rotate-col .review-dots{gap:4px;margin-top:6px}
.pricing-rotate-col .review-dots .dot{width:6px;height:6px}
.pricing-rotate-col .review-dots .dot.active{width:14px}
.pricing-rotate-col .review-nav button{font-size:11px;padding:5px 12px}
.support-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px}
.support-grid a{display:flex;align-items:center;gap:12px;padding:18px 22px;background:rgba(0,140,255,.06);border:1px solid rgba(0,191,255,.1);border-radius:12px;text-decoration:none;color:#fff;transition:.3s;font-size:14px}
.support-grid a:hover{background:rgba(0,140,255,.1);border-color:rgba(0,191,255,.2);transform:translateY(-2px)}
.support-grid a i{font-size:22px;width:28px;text-align:center}
footer{padding:60px 0 30px;border-top:1px solid rgba(255,255,255,.04)}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:30px;margin-bottom:40px}
footer h4{font-family:'Orbitron',sans-serif;font-size:1.2rem;margin-bottom:14px}
footer h4 span{color:#008cff}
footer p{color:#64748b;font-size:13px;line-height:1.7;max-width:320px}
footer a{display:block;color:#94a3b8;text-decoration:none;padding:4px 0;font-size:13px;transition:.2s}
footer a:hover{color:#fff}
footer .copyright{text-align:center;padding-top:20px;border-top:1px solid rgba(255,255,255,.04);color:#64748b;font-size:13px}
@media(max-width:768px){
.unique-grid{grid-template-columns:1fr}
.footer-grid{grid-template-columns:1fr 1fr}
.nav-links .nav-hide{display:none}
.hero-content h2{font-size:2rem}
}
.loader{display:inline-block;width:14px;height:14px;border:2px solid transparent;border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>

<nav class="container">
<a href="/" class="logo">
<div class="icon">P</div>
<h1>PLANET-<span>HOSTS</span></h1>
</a>
<div class="nav-links">
<a href="#" class="nav-hide">Hosting</a>
<a href="#" class="nav-hide">Radio</a>
<a href="#" class="nav-hide">Domains</a>
<a href="?login" class="nav-hide">Login</a>
<a href="http://45.61.59.55:2082/" class="btn-primary">Client Portal →</a>
</div>
</nav>

<section class="hero">
<div class="hero-bg">
<div class="grad"></div>
<div class="grid"></div>
<div class="particles">
<div class="particle"></div><div class="particle"></div><div class="particle"></div>
<div class="particle"></div><div class="particle"></div><div class="particle"></div>
<div class="particle"></div><div class="particle"></div>
</div>
</div>
<div class="hero-content container">
<div class="badge"><i class="fa-solid fa-bolt"></i> The World's First WHM + Radio Streaming Platform</div>
<h2>Hosting, Radio,<br>Domains &amp; Billing —<br><span class="highlight">All in One Panel</span></h2>
<p>Manage web hosting, Icecast radio streams, AutoDJ, live chat support, billing, domains, and reseller accounts from a single unified control panel. No other panel does this.</p>
<div class="hero-btns">
<a href="http://45.61.59.55:2087/" class="btn-primary btn"><i class="fa-solid fa-rocket"></i> Admin Demo</a>
<a href="http://45.61.59.55:2082/" class="btn-secondary btn"><i class="fa-solid fa-user"></i> User Portal</a>
<a href="http://45.61.59.55:2086/" class="btn-secondary btn"><i class="fa-solid fa-store"></i> Reseller Center</a>
</div>

<div class="domain-search">
<h3><i class="fa-solid fa-globe"></i> Find Your Perfect Domain</h3>
<div class="search-row">
<input type="text" id="domainInput" placeholder="Enter your domain name..." value="" autocomplete="off">
<select id="tldSelect">
<option value="com">.com</option>
<option value="net">.net</option>
<option value="org">.org</option>
<option value="io">.io</option>
<option value="co">.co</option>
<option value="us">.us</option>
<option value="app">.app</option>
<option value="dev">.dev</option>
<option value="xyz">.xyz</option>
<option value="top">.top</option>
<option value="live">.live</option>
<option value="radio" selected>.radio</option>
<option value="fm">.fm</option>
<option value="stream">.stream</option>
<option value="music">.music</option>
<option value="audio">.audio</option>
</select>
<button onclick="checkDomain()"><i class="fa-solid fa-search"></i> Search</button>
</div>
<div id="domainResult" class="domain-result"></div>
<div class="domain-tlds">
<span>.com $9.99</span><span>.net $10.99</span><span>.org $11.99</span><span>.io $34.99</span>
<span>.xyz $5.99</span><span>.top $3.99</span><span>.us $8.99</span><span>.radio $24.99</span>
</div>
</div>

<div class="stats-ticker">
<div class="stat"><div class="num">99.99%</div><div class="label">Uptime Guarantee</div></div>
<div class="stat"><div class="num">24/7</div><div class="label">Support Team</div></div>
<div class="stat"><div class="num">Live</div><div class="label">WHM + Radio Panel</div></div>
</div>
</div>
</section>

<!-- Unique Value Prop -->
<section class="unique-section">
<div class="container">
<div class="unique-grid">
<div class="text">
<h2>Why <span>Planet Hosts?</span></h2>
<p>While other control panels are stuck in the past, we built the future. Planet Hosts is the only platform that combines enterprise WHM hosting management with professional Icecast radio streaming — all under one roof.</p>
<ul>
<li><i class="fa-solid fa-check"></i> Full WHM panel — accounts, DNS, email, FTP, security</li>
<li><i class="fa-solid fa-check"></i> Built-in Icecast streaming server management</li>
<li><i class="fa-solid fa-check"></i> Multi-tenant live chat support with SignalR + desktop app</li>
<li><i class="fa-solid fa-check"></i> Complete billing system with PayPal, invoices, dunning</li>
<li><i class="fa-solid fa-check"></i> AutoDJ with library management &amp; playlist scheduling</li>
<li><i class="fa-solid fa-check"></i> DJ panel with banners, bios, and stream authentication</li>
<li><i class="fa-solid fa-check"></i> Application marketplace with one-click installs</li>
<li><i class="fa-solid fa-check"></i> Reseller management with white-label branding</li>
</ul>
</div>
<div class="visual">
<div class="stack">
<div class="stack-item"><div class="si-icon" style="background:rgba(0,140,255,.12);color:#008cff"><i class="fa-solid fa-server"></i></div><div class="si-text"><strong>WHM Control Panel</strong><span>Accounts, DNS, Email, Security, Apache</span></div></div>
<div class="stack-item"><div class="si-icon" style="background:rgba(0,229,255,.12);color:#00e5ff"><i class="fa-solid fa-tower-broadcast"></i></div><div class="si-text"><strong>Icecast Radio Streaming</strong><span>Mounts, AutoDJ, DJ accounts, analytics</span></div></div>
<div class="stack-item"><div class="si-icon" style="background:rgba(124,58,237,.12);color:#a78bfa"><i class="fa-solid fa-comment-dots"></i></div><div class="si-text"><strong>Live Chat + Desktop App</strong><span>Multi-tenant chat with WPF desktop client</span></div></div>
<div class="stack-item"><div class="si-icon" style="background:rgba(74,222,128,.12);color:#4ade80"><i class="fa-solid fa-credit-card"></i></div><div class="si-text"><strong>Billing &amp; Payments</strong><span>Invoicing, PayPal, products, subscriptions</span></div></div>
<div class="stack-item"><div class="si-icon" style="background:rgba(251,191,36,.12);color:#fbbf24"><i class="fa-solid fa-headphones"></i></div><div class="si-text"><strong>DJ Management</strong><span>Banners, bios, scheduling, streaming auth</span></div></div>
</div>
</div>
</div>
</div>
</section>

<!-- Features -->
<section>
<div class="container">
<div class="section-title">
<h2>Everything You Need, <span>Nothing You Don't</span></h2>
<p>A complete hosting ecosystem built for modern web hosts and radio streamers.</p>
</div>
<div class="features-grid">

<div class="feature-card" style="--accent:#008cff">
<div class="icon" style="background:rgba(0,140,255,.1);color:#008cff"><i class="fa-solid fa-server"></i></div>
<h3>WHM Hosting Panel</h3>
<p>Full account management, DNS zones, email, FTP, SSL, security center, Apache config, PHP manager, and MySQL databases.</p>
<span class="tag tag-radio">Live</span>
</div>

<div class="feature-card" style="--accent:#00e5ff">
<div class="icon" style="background:rgba(0,229,255,.1);color:#00e5ff"><i class="fa-solid fa-tower-broadcast"></i></div>
<h3>Icecast Radio Streaming</h3>
<p>Professional Icecast streaming with multi-mount support, AutoDJ, listener analytics, geo-location, and stream archiving.</p>
<span class="tag tag-radio">Live</span>
</div>

<div class="feature-card" style="--accent:#a78bfa">
<div class="icon" style="background:rgba(124,58,237,.1);color:#a78bfa"><i class="fa-solid fa-microphone"></i></div>
<h3>DJ Panel + Authentication</h3>
<p>DJs get their own login portal with banner upload, bio links, and stream authentication — no account = no stream.</p>
<span class="tag tag-new">New</span>
</div>

<div class="feature-card" style="--accent:#4ade80">
<div class="icon" style="background:rgba(74,222,128,.1);color:#4ade80"><i class="fa-solid fa-music"></i></div>
<h3>AutoDJ + Upload Manager</h3>
<p>Upload music via web or FTP (100 files per batch). Smart playlist rotation, crossfade, and dayparting. Only MP3/AAC/OGG/FLAC.</p>
<span class="tag tag-radio">Live</span>
</div>

<div class="feature-card" style="--accent:#fbbf24">
<div class="icon" style="background:rgba(251,191,36,.1);color:#fbbf24"><i class="fa-solid fa-comment-dots"></i></div>
<h3>Multi-Tenant Live Chat</h3>
<p>SignalR-powered real-time chat with WPF desktop client, visitor tracking, file uploads, transcripts, and ratings.</p>
<span class="tag tag-new">New</span>
</div>

<div class="feature-card" style="--accent:#f87171">
<div class="icon" style="background:rgba(248,113,113,.1);color:#f87171"><i class="fa-solid fa-credit-card"></i></div>
<h3>Billing &amp; Invoicing</h3>
<p>Full billing suite: products, orders, invoices, payments, PayPal IPN, pro-rata billing, dunning, taxes, coupons, refunds.</p>
<span class="tag tag-radio">Live</span>
</div>

<div class="feature-card" style="--accent:#34d399">
<div class="icon" style="background:rgba(52,211,153,.1);color:#34d399"><i class="fa-solid fa-cubes"></i></div>
<h3>Application Marketplace</h3>
<p>One-click application installer with pricing, domain picker, and account selection. Install WordPress, Joomla, and more.</p>
<span class="tag tag-hot">Popular</span>
</div>

<div class="feature-card" style="--accent:#38bdf8">
<div class="icon" style="background:rgba(56,189,248,.1);color:#38bdf8"><i class="fa-solid fa-store"></i></div>
<h3>Reseller Management</h3>
<p>White-label reseller system with branded portals, package management, client billing, and support delegation.</p>
<span class="tag tag-radio">Live</span>
</div>

<div class="feature-card" style="--accent:#fb923c">
<div class="icon" style="background:rgba(251,146,60,.1);color:#fb923c"><i class="fa-solid fa-shield-halved"></i></div>
<h3>Security Center</h3>
<p>SSL/TLS, firewall manager, IP blocker, fail2ban integration, ModSecurity, two-factor auth, and license enforcement.</p>
<span class="tag tag-radio">Live</span>
</div>

<div class="feature-card" style="--accent:#e879f9">
<div class="icon" style="background:rgba(232,121,249,.1);color:#e879f9"><i class="fa-solid fa-chart-bar"></i></div>
<h3>Analytics &amp; Monitoring</h3>
<p>Server health, listener analytics, bandwidth graphs, disk usage, service monitoring, and performance metrics.</p>
<span class="tag tag-coming">Coming</span>
</div>

<div class="feature-card" style="--accent:#c084fc">
<div class="icon" style="background:rgba(192,132,252,.1);color:#c084fc"><i class="fa-solid fa-robot"></i></div>
<h3>Automation Engine</h3>
<p>Auto provision/suspend/terminate, email/SMS notifications, SMTP config, cron management, and webhook triggers.</p>
<span class="tag tag-radio">Live</span>
</div>

<div class="feature-card" style="--accent:#f472b6">
<div class="icon" style="background:rgba(244,114,182,.1);color:#f472b6"><i class="fa-solid fa-ticket"></i></div>
<h3>Support System</h3>
<p>Tickets with departments/priority workflow, knowledgebase with search, announcements, email piping, and live chat.</p>
<span class="tag tag-radio">Live</span>
</div>

</div>
</div>
</section>

<!-- Radio Showcase -->
<section class="radio-showcase">
<div class="container">
<div class="section-title">
<h2>Professional <span>Radio Streaming</span></h2>
<p>Built for broadcasters who need reliability, analytics, and total control.</p>
</div>
<div class="features-grid">

<div class="feature-card" style="--accent:#00e5ff">
<div class="icon" style="background:rgba(0,229,255,.1);color:#00e5ff"><i class="fa-solid fa-tower-broadcast"></i></div>
<h3>Icecast Servers</h3>
<p>Per-user Icecast instances with automatic config generation. Support for MP3, AAC, OGG, Opus, and FLAC streaming.</p>
</div>

<div class="feature-card" style="--accent:#4ade80">
<div class="icon" style="background:rgba(74,222,128,.1);color:#4ade80"><i class="fa-solid fa-headphones"></i></div>
<h3>AutoDJ Engine</h3>
<p>24/7 automated broadcasting with music library, playlists, crossfade, scheduling, and dayparting.</p>
</div>

<div class="feature-card" style="--accent:#a78bfa">
<div class="icon" style="background:rgba(124,58,237,.1);color:#a78bfa"><i class="fa-solid fa-users"></i></div>
<h3>Listener Analytics</h3>
<p>Real-time listener count, geolocation mapping, browser/OS stats, listener history, and CSV export.</p>
</div>

<div class="feature-card" style="--accent:#fbbf24">
<div class="icon" style="background:rgba(251,191,36,.1);color:#fbbf24"><i class="fa-solid fa-record-vinyl"></i></div>
<h3>Mount Management</h3>
<p>Multiple mount points per stream with different bitrates and formats. Auto-fallback relay when source disconnects.</p>
</div>

<div class="feature-card" style="--accent:#38bdf8">
<div class="icon" style="background:rgba(56,189,248,.1);color:#38bdf8"><i class="fa-solid fa-code"></i></div>
<h3>Embed Player Widget</h3>
<p>HTML5 web player widget for client websites. Public listener count API and now-playing metadata endpoint.</p>
</div>

<div class="feature-card" style="--accent:#f87171">
<div class="icon" style="background:rgba(248,113,113,.1);color:#f87171"><i class="fa-solid fa-circle-nodes"></i></div>
<h3>Relay Cluster</h3>
<p>Multi-server relay setup for CDN-style streaming. Slave servers, load balancing, and geo-distribution.</p>
</div>

</div>
</div>
</section>

<!-- Pricing -->
<?php
// Fetch packages from DB grouped by type
$allPkgs = [];
try {
    $pdb = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $pq = $pdb->query("SELECT * FROM hosting_packages WHERE is_active=1 ORDER BY sort_order ASC, monthly_price ASC");
    if ($pq) {
        foreach ($pq->fetchAll(PDO::FETCH_OBJ) as $pkg) {
            $allPkgs[$pkg->type][] = $pkg;
        }
    }
} catch (Exception $e) {}

$typeLabels = [
    'web_hosting' => ['icon' => '🌐', 'label' => 'Web Hosting'],
    'web_reseller' => ['icon' => '🏢', 'label' => 'Reseller Hosting'],
    'icecast' => ['icon' => '🎵', 'label' => 'Radio Streaming'],
    'icecast_reseller' => ['icon' => '🎵', 'label' => 'Radio Reseller'],
    'vps' => ['icon' => '🖥', 'label' => 'VPS Servers'],
    'dedicated' => ['icon' => '🔧', 'label' => 'Dedicated Servers'],
];
$pkgTypes = array_keys($allPkgs);
$firstType = $pkgTypes[0] ?? 'web_hosting';
?>
<section>
<div class="container">
<div class="section-title">
<h2>Simple <span>Pricing</span></h2>
<p>Choose the plan that fits your needs. All plans include our full WHM panel.</p>
</div>

<?php if (!empty($allPkgs)): ?>
<div class="pricing-grid" style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr))">
<?php $catOrder = ['web_hosting', 'web_reseller', 'icecast', 'icecast_reseller']; ?>
<?php foreach ($catOrder as $type): if (!isset($allPkgs[$type])) continue; $pkgs = $allPkgs[$type]; $ti = $typeLabels[$type] ?? ['icon' => '📦', 'label' => $type]; ?>
<div class="pricing-rotate-col">
<div style="text-align:center;margin-bottom:10px">
<span style="font-size:28px"><?php echo $ti['icon']; ?></span>
<h3 style="font-size:16px;margin:4px 0 2px"><?php echo $ti['label']; ?></h3>
<div style="font-size:11px;color:#64748b"><?php echo count($pkgs); ?> plans</div>
</div>
<div class="pricing-rotate" id="prota-<?php echo $type; ?>">
<?php foreach ($pkgs as $pi => $pkg):
$isIcecast = $type === 'icecast' || $type === 'icecast_reseller';
$features = [];
if ($pkg->disk_space > 0) $features[] = '📁 ' . $pkg->disk_space . ' GB Disk';
if ($pkg->bandwidth > 0) $features[] = '📶 ' . $pkg->bandwidth . ' GB Bandwidth';
if ($pkg->email_accounts > 0) $features[] = '📧 ' . $pkg->email_accounts . ' Email Accounts';
if ($pkg->databases > 0) $features[] = '🗄 ' . $pkg->databases . ' Databases';
if ($pkg->subdomains > 0) $features[] = '🔗 ' . $pkg->subdomains . ' Subdomains';
if ($pkg->ftp_accounts > 0) $features[] = '📤 ' . $pkg->ftp_accounts . ' FTP Accounts';
if ($isIcecast && $pkg->listener_limit > 0) $features[] = '🎧 ' . $pkg->listener_limit . ' Listeners';
if ($isIcecast && $pkg->bitrate > 0) $features[] = '🎚 ' . $pkg->bitrate . ' kbps Bitrate';
if ($isIcecast && $pkg->storage_limit > 0) $features[] = '💾 ' . $pkg->storage_limit . ' GB Storage';
if ($isIcecast && $pkg->dj_accounts > 0) $features[] = '🎤 ' . $pkg->dj_accounts . ' DJ Accounts';
if ($pkg->live_chat_enabled) $features[] = '💬 Live Chat';
if (empty($features)) $features[] = '✅ Full WHM Panel Access';
?>
<div class="pricing-card pkg-rotate<?php if ($pi === 0) echo ' active'; ?>" data-type="<?php echo $type; ?>" data-index="<?php echo $pi; ?>">
<h3><?php echo htmlspecialchars($pkg->name); ?></h3>
<div class="subtitle"><?php echo htmlspecialchars($pkg->description ?? $ti['label']); ?></div>
<div class="price">$<?php echo number_format($pkg->monthly_price, 2); ?><span>/mo</span></div>
<?php if ($pkg->setup_fee > 0): ?><div style="font-size:10px;color:#64748b;margin-bottom:4px">+ $<?php echo number_format($pkg->setup_fee, 2); ?> setup</div><?php endif; ?>
<ul>
<?php foreach (array_slice($features, 0, 5) as $f): ?><li><i class="fa-solid fa-check"></i> <?php echo $f; ?></li><?php endforeach; ?>
<?php if (count($features) > 5): ?><li style="color:#64748b;font-size:11px">+<?php echo count($features)-5; ?> more features</li><?php endif; ?>
</ul>
<a href="/cart.php?action=add&package=<?php echo $pkg->id; ?>" class="btn" style="font-size:13px;padding:10px">Order Now →</a>
</div>
<?php endforeach; ?>
</div>
<div class="review-dots" id="pdots-<?php echo $type; ?>">
<?php foreach ($pkgs as $pi => $pkg): ?>
<span class="dot<?php if ($pi === 0) echo ' active'; ?>" onclick="showPkg(<?php echo $pi; ?>, '<?php echo $type; ?>')"></span>
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
<div class="pricing-grid">
<div class="pricing-card">
<div class="pkg-icon">🌐</div>
<h3>Web Hosting</h3>
<div class="subtitle">Shared hosting with WHM panel</div>
<div class="price">$5.99<span>/mo</span></div>
<ul>
<li><i class="fa-solid fa-check"></i> 10 GB SSD Storage</li>
<li><i class="fa-solid fa-check"></i> 100 GB Bandwidth</li>
<li><i class="fa-solid fa-check"></i> 5 Email Accounts</li>
<li><i class="fa-solid fa-check"></i> 2 Databases</li>
<li><i class="fa-solid fa-check"></i> Free SSL Certificate</li>
<li><i class="fa-solid fa-check"></i> Full WHM Panel</li>
</ul>
<a href="http://45.61.59.55:2082/" class="btn">Get Started</a>
</div>
</div>
<?php endif; ?>
</div>
</section>

<script>
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
    pkgIntervals[type] = setInterval(function() { nextPkg(type); }, 7000);
}

// Start rotation for all categories
<?php foreach (['web_hosting', 'web_reseller', 'icecast', 'icecast_reseller'] as $t): if (isset($allPkgs[$t])): ?>
startPkgRotation('<?php echo $t; ?>');
<?php endif; endforeach; ?>
</script>

<!-- Reviews -->
<?php
$reviews = [];
try {
    $rdb = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $rq = $rdb->query("SELECT * FROM reviews WHERE approved=1 ORDER BY created_at DESC LIMIT 5");
    if ($rq) $reviews = $rq->fetchAll(PDO::FETCH_OBJ);
} catch (Exception $e) {}
$avgRating = 0;
if (!empty($reviews)) {
    $total = array_sum(array_map(fn($r) => $r->rating, $reviews));
    $avgRating = round($total / count($reviews), 1);
}
?>
<section class="reviews-section">
<div class="container">
<div class="section-title">
<h2>What Our <span>Clients Say</span></h2>
<p>Real feedback from real users. We take every review seriously.</p>
</div>
<?php if (!empty($reviews)): ?>
<div class="review-avg">
<div class="big-star"><?php echo str_repeat('★', 5); ?></div>
<div class="avg-text"><?php echo $avgRating; ?> / 5 average rating from <?php echo count($reviews); ?> reviews</div>
</div>
<div class="reviews-grid" id="reviewsGrid">
<?php foreach ($reviews as $i => $r): ?>
<div class="review-card<?php if ($i === 0) echo ' active'; ?>" data-index="<?php echo $i; ?>">
<div class="stars"><?php echo str_repeat('★', (int)$r->rating) . str_repeat('☆', 5 - (int)$r->rating); ?></div>
<?php if ($r->title): ?><div style="font-weight:700;font-size:15px;margin-bottom:6px"><?php echo htmlspecialchars($r->title); ?></div><?php endif; ?>
<div class="rtext">"<?php echo htmlspecialchars($r->text); ?>"</div>
<div class="rauthor">
<div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#008cff,#00e5ff);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:#fff;flex-shrink:0"><?php echo strtoupper(substr($r->name, 0, 1)); ?></div>
<div><div class="rname"><?php echo htmlspecialchars($r->name); ?></div><div class="rdate"><?php echo date('M j, Y', strtotime($r->created_at)); ?></div></div>
</div>
</div>
<?php endforeach; ?>
</div>
<div class="review-dots" id="reviewDots">
<?php foreach ($reviews as $i => $r): ?>
<span class="dot<?php if ($i === 0) echo ' active'; ?>" onclick="showReview(<?php echo $i; ?>)"></span>
<?php endforeach; ?>
</div>
<div class="review-nav">
<button onclick="prevReview()">← Previous</button>
<button onclick="nextReview()">Next →</button>
</div>
<?php else: ?>
<p style="text-align:center;color:#64748b;font-size:14px">No reviews yet. Be the first!</p>
<?php endif; ?>
</div>
</section>

<script>
var currentReview = 0;
var totalReviews = <?php echo count($reviews); ?>;

function showReview(idx) {
    var cards = document.querySelectorAll('.review-card');
    var dots = document.querySelectorAll('.review-dots .dot');
    cards.forEach(function(c, i) { c.classList.toggle('active', i === idx); });
    dots.forEach(function(d, i) { d.classList.toggle('active', i === idx); });
    currentReview = idx;
}

function nextReview() {
    showReview((currentReview + 1) % totalReviews);
}

function prevReview() {
    showReview((currentReview - 1 + totalReviews) % totalReviews);
}

// Auto-rotate every 6 seconds
setInterval(nextReview, 6000);
</script>

<!-- Support -->
<section class="support-section">
<div class="container">
<div class="section-title">
<h2>We're Here <span>24/7</span></h2>
<p>Get help when you need it — multiple channels, one goal: your success.</p>
</div>
<div class="support-grid">
<a href="/admin/support/tickets"><i class="fa-solid fa-ticket" style="color:#38bdf8"></i> Support Tickets</a>
<a href="/admin/support/kb"><i class="fa-solid fa-book" style="color:#4ade80"></i> Knowledgebase</a>
<a href="/admin/support/announcements"><i class="fa-solid fa-bullhorn" style="color:#fbbf24"></i> Announcements</a>
<a href="/admin/livechat"><i class="fa-solid fa-comment-dots" style="color:#a78bfa"></i> Live Chat</a>
<a href="http://45.61.59.55:5000/swagger"><i class="fa-solid fa-code" style="color:#f87171"></i> API Docs</a>
<a href="/admin/support/status"><i class="fa-solid fa-heart-pulse" style="color:#34d399"></i> Server Status</a>
</div>
</div>
</section>

<!-- Footer -->
<footer>
<div class="container">
<div class="footer-grid">
<div>
<h4>PLANET-<span>HOSTS</span></h4>
<p>The world's first unified WHM hosting + Icecast radio streaming platform. Built for modern hosts and broadcasters.</p>
</div>
<div>
<h5 style="color:#e0e0e0;font-size:14px;margin-bottom:12px">Services</h5>
<a href="#">Web Hosting</a>
<a href="#">Radio Streaming</a>
<a href="#">VPS Servers</a>
<a href="#">Dedicated Servers</a>
<a href="#">Domain Registration</a>
</div>
<div>
<h5 style="color:#e0e0e0;font-size:14px;margin-bottom:12px">Resources</h5>
<a href="#">Knowledgebase</a>
<a href="#">API Documentation</a>
<a href="#">Server Status</a>
<a href="#">Announcements</a>
<a href="#">Support Center</a>
</div>
<div>
<h5 style="color:#e0e0e0;font-size:14px;margin-bottom:12px">Company</h5>
<a href="#">About</a>
<a href="#">Terms of Service</a>
<a href="#">Privacy Policy</a>
<a href="#">Contact</a>
</div>
</div>
<div class="copyright">© 2026 Planet-Hosts. All rights reserved. The first WHM + Radio platform.</div>
</div>
</footer>

<script>
function checkDomain() {
    var domain = document.getElementById('domainInput').value.trim();
    var tld = document.getElementById('tldSelect').value;
    var result = document.getElementById('domainResult');

    if (!domain) { result.className = 'domain-result taken'; result.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Please enter a domain name'; result.style.display = 'block'; return; }

    result.className = 'domain-result';
    result.innerHTML = '<span class="loader"></span> Checking availability...';
    result.style.display = 'block';

    var x = new XMLHttpRequest();
    x.open('GET', '/domain_check.php?domain=' + encodeURIComponent(domain) + '&tld=' + encodeURIComponent(tld), true);
    x.onload = function() {
        try {
            var data = JSON.parse(x.responseText);
            if (data.error) { result.className = 'domain-result taken'; result.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + data.error; return; }

            if (data.available) {
                result.className = 'domain-result available';
                result.innerHTML = '<i class="fa-solid fa-circle-check"></i> <strong>' + data.domain + '</strong> is available! <span class="price">$' + data.price_register.toFixed(2) + '/yr</span>';
            } else {
                result.className = 'domain-result taken';
                result.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> <strong>' + data.domain + '</strong> is taken <span class="price">$' + data.price_transfer.toFixed(2) + ' to transfer</span>';
            }

            // Show cheapest alternatives
            if (data.suggestions && data.suggestions.length > 0) {
                var sugHtml = '<div class="suggestions">';
                data.suggestions.forEach(function(s) {
                    sugHtml += '<span class="sug" onclick="document.getElementById(\'domainInput\').value=\'' + s.domain.split('.')[0] + '\';document.getElementById(\'tldSelect\').value=\'' + s.tld + '\';checkDomain()">' + s.domain + ' <span class="sug-price">$' + s.price.toFixed(2) + '</span></span>';
                });
                sugHtml += '</div>';
                result.innerHTML += sugHtml;
            }
        } catch(e) {
            result.className = 'domain-result taken';
            result.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Error checking domain. Try again.';
        }
    };
    x.onerror = function() {
        result.className = 'domain-result taken';
        result.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Network error. Check your connection.';
    };
    x.send();
}

// Search on Enter
document.getElementById('domainInput').addEventListener('keydown', function(e) { if (e.key === 'Enter') checkDomain(); });
</script>

<!-- Live Chat Widget -->
<div id="chatWidget" style="position:fixed;bottom:0;right:0;z-index:9999;width:240px;cursor:pointer" onclick="toggleChat()">
<img id="chatBanner" src="/theme/assets/img/livechat/live_online.png" style="width:100%;height:auto;display:block" alt="Live Chat">
</div>
<div id="chatBox" style="display:none;position:fixed;bottom:0;right:0;width:380px;height:500px;background:rgba(8,16,28,.98);border:1px solid rgba(0,191,255,.15);border-radius:16px 16px 0 0;overflow:hidden;box-shadow:0 -5px 40px rgba(0,0,0,.5);flex-direction:column;z-index:9998">
<div style="padding:14px 18px;background:linear-gradient(135deg,#008cff,#0055aa);display:flex;justify-content:space-between;align-items:center">
<span style="font-weight:700;color:#fff">💬 Live Support</span>
<span onclick="event.stopPropagation();toggleChat()" style="cursor:pointer;color:rgba(255,255,255,.7);font-size:18px">✕</span>
</div>
<div id="chatStart" style="padding:24px;text-align:center;flex:1;display:flex;flex-direction:column;justify-content:center">
<img src="/theme/assets/img/logo.png" style="width:48px;height:48px;border-radius:12px;margin:0 auto 12px" alt="">
<p style="color:var(--text-secondary);margin-bottom:16px">Need help? Start a chat with our support team.</p>
<input id="chatName" placeholder="Your name" style="width:100%;padding:10px;margin-bottom:8px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;outline:none;box-sizing:border-box">
<input id="chatEmail" placeholder="Your email (optional)" style="width:100%;padding:10px;margin-bottom:12px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;outline:none;box-sizing:border-box">
<button onclick="event.stopPropagation();startChat()" class="btn primary" style="width:100%;box-sizing:border-box">Start Chat</button>

</div>
<div id="chatMsgs" style="display:none;flex:1;overflow-y:auto;padding:16px"></div>
<div id="chatInputArea" style="display:none;padding:12px 16px;border-top:1px solid rgba(255,255,255,.06)">
<div style="display:flex;gap:8px">
<input id="chatMsgInput" placeholder="Type a message..." style="flex:1;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;outline:none;box-sizing:border-box">
<button onclick="event.stopPropagation();sendChatMsg()" style="padding:10px 16px;border-radius:8px;background:var(--accent);color:#fff;border:none;cursor:pointer">Send</button>
</div>
<div style="display:flex;gap:4px;margin-top:6px">
<label style="font-size:11px;color:#64748b;cursor:pointer" onclick="event.stopPropagation();document.getElementById('chatFile').click()">📎 Attach</label>
<input type="file" id="chatFile" style="display:none" onchange="event.stopPropagation();uploadChatFile(event)">
<label style="font-size:11px;color:#64748b;cursor:pointer" onclick="event.stopPropagation();insertEmoji()">😊 Emoji</label>
<span style="font-size:11px;color:#64748b;margin-left:auto">⭐ <a href="/rate_chat.php?id=" target="_blank" style="color:#64748b;text-decoration:none" id="rateLink">Rate</a></span>
</div>
</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/microsoft-signalr/8.0.0/signalr.min.js"></script>
<script>
var chatSessionId = 0, chatLastId = 0, chatConnection = null, useSignalR = false;
function initSignalR() {
    try {
        chatConnection = new signalR.HubConnectionBuilder().withUrl('http://localhost:5000/hub/chat').withAutomaticReconnect().build();
        chatConnection.on('NewMessage', function(msg) {
            if (msg.sessionId === chatSessionId) {
                var out = document.getElementById('chatMsgs');
                var av = msg.senderType==='visitor'?'/theme/assets/img/avatars/vistor.png':'/theme/assets/img/avatars/owner.png';
                out.innerHTML += '<div style="display:flex;gap:8px;margin-bottom:10px;flex-direction:' + (msg.senderType==='visitor'?'row':'row-reverse') + ';align-items:start"><img src="' + av + '" style="width:28px;height:28px;border-radius:50%;flex-shrink:0"><div style="text-align:' + (msg.senderType==='visitor'?'left':'right') + '"><div style="display:inline-block;padding:8px 14px;border-radius:12px;font-size:14px;background:' + (msg.senderType==='visitor'?'rgba(0,140,255,.15)':'rgba(255,255,255,.06)') + ';color:#e0e0e0">' + (msg.message||'').replace(/</g,'&lt;') + '</div><div style="font-size:11px;color:var(--text-muted);margin-top:2px">' + msg.senderName + '</div></div></div>';
                out.scrollTop = out.scrollHeight;
            }
        });
        chatConnection.start().then(function(){useSignalR=true;}).catch(function(){useSignalR=false;});
    } catch(e) { useSignalR = false; }
}
initSignalR();
function toggleChat() {
    var box=document.getElementById('chatBox'),banner=document.getElementById('chatBanner');
    if (box.style.display==='flex'){box.style.display='none';banner.style.display='block';}
    else{box.style.display='flex';banner.style.display='none';}
}
function checkOperators() {
    var banner=document.getElementById('chatBanner');
    if (useSignalR&&chatConnection&&chatConnection.state==='Connected') banner.src='/theme/assets/img/livechat/live_online.png';
    else {
        var x=new XMLHttpRequest();
        x.open('GET','/admin/livechat',true);
        x.onload=function(){banner.src='/theme/assets/img/livechat/live_online.png';};
        x.onerror=function(){banner.src='/theme/assets/img/livechat/live_offline.png';};
        x.send();
    }
}
setInterval(checkOperators,30000);checkOperators();
function trackPage() {
    var tx=new XMLHttpRequest();
    tx.open('POST','/admin/livechat/track',true);
    tx.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    tx.send('page='+encodeURIComponent(window.location.pathname)+'&tz='+encodeURIComponent(Intl.DateTimeFormat().resolvedOptions().timeZone||'')+'&res='+encodeURIComponent(screen.width+'x'+screen.height)+'&lang='+encodeURIComponent(navigator.language||''));
}
trackPage();
var lastTracked=window.location.pathname;
setInterval(function(){if(window.location.pathname!==lastTracked){lastTracked=window.location.pathname;trackPage();}},2000);
function startChat() {
    var name=document.getElementById('chatName').value.trim()||'Visitor';
    var email=document.getElementById('chatEmail').value.trim();
    var x=new XMLHttpRequest();
    x.open('POST','/chat/start',true);
    x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    x.onload=function(){
        var r=JSON.parse(x.responseText);
        chatSessionId=r.id;
        document.getElementById('chatStart').style.display='none';
        document.getElementById('chatMsgs').style.display='block';
        document.getElementById('chatInputArea').style.display='block';
        if (useSignalR&&chatConnection&&chatConnection.state==='Connected') chatConnection.invoke('JoinChat',chatSessionId).catch(function(){});
        setInterval(pollChat,5000);
    };
    x.send('name='+encodeURIComponent(name)+'&email='+encodeURIComponent(email));
}
function pollChat() {
    if (!chatSessionId||useSignalR) return;
    var x=new XMLHttpRequest();
    x.open('GET','/chat/poll/'+chatSessionId+'?since='+chatLastId,true);
    x.onload=function(){
        try{
            var r=JSON.parse(x.responseText);
            if(r.messages) r.messages.forEach(function(m){
                if(m.id>chatLastId) chatLastId=m.id;
                var out=document.getElementById('chatMsgs');
                var av2=m.sender_type==='visitor'?'/theme/assets/img/avatars/vistor.png':'/theme/assets/img/avatars/owner.png';
                out.innerHTML+='<div style="display:flex;gap:8px;margin-bottom:10px;flex-direction:'+(m.sender_type==='visitor'?'row':'row-reverse')+';align-items:start"><img src="'+av2+'" style="width:28px;height:28px;border-radius:50%;flex-shrink:0"><div style="text-align:'+(m.sender_type==='visitor'?'left':'right')+'"><div style="display:inline-block;padding:8px 14px;border-radius:12px;font-size:14px;background:'+(m.sender_type==='visitor'?'rgba(0,140,255,.15)':'rgba(255,255,255,.06)')+';color:#e0e0e0">'+m.message.replace(/</g,'&lt;')+'</div><div style="font-size:11px;color:var(--text-muted);margin-top:2px">'+m.sender_name+'</div></div></div>';
                out.scrollTop=out.scrollHeight;
            });
        }catch(e){}
    };
    x.send();
}
function sendChatMsg() {
    var input=document.getElementById('chatMsgInput');
    var msg=input.value.trim();
    if(!msg||!chatSessionId) return;
    if(useSignalR&&chatConnection&&chatConnection.state==='Connected'){
        chatConnection.invoke('SendMessage',chatSessionId,msg,document.getElementById('chatName').value.trim()||'Visitor','visitor').catch(function(){});
        input.value='';
    } else {
        var x=new XMLHttpRequest();
        x.open('POST','/chat/send',true);
        x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        x.onload=function(){input.value='';pollChat();};
        x.send('session_id='+chatSessionId+'&message='+encodeURIComponent(msg)+'&name='+encodeURIComponent(document.getElementById('chatName').value.trim()||'Visitor'));
    }
}
function uploadChatFile(event){var file=event.target.files[0];if(!file||!chatSessionId)return;var fd=new FormData();fd.append('file',file);fd.append('session_id',chatSessionId);var x=new XMLHttpRequest();x.open('POST','/upload_chat.php',true);x.onload=function(){try{var r=JSON.parse(x.responseText);if(r.url)pollChat();}catch(e){}};x.send(fd);}
function insertEmoji(){var input=document.getElementById('chatMsgInput');var emojis=['😊','😂','❤️','👍','🎉','😍','🤔','👋','🔥','💀','😎','🙏','💯','⭐','🎶','😢','😡','🥳'];var picker=document.getElementById('emojiPicker');if(picker){picker.remove();return;}picker=document.createElement('div');picker.id='emojiPicker';picker.style.cssText='position:absolute;bottom:60px;left:16px;background:#1a2030;border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:10px;display:grid;grid-template-columns:repeat(8,1fr);gap:4px;z-index:999;box-shadow:0 5px 20px rgba(0,0,0,.5)';emojis.forEach(function(e){var s=document.createElement('span');s.textContent=e;s.style.cssText='cursor:pointer;font-size:20px;padding:4px;text-align:center;border-radius:4px';s.onclick=function(){input.value+=e;input.focus();picker.remove();};s.onmouseenter=function(){this.style.background='rgba(255,255,255,.08)';};s.onmouseleave=function(){this.style.background='transparent';};picker.appendChild(s);});document.getElementById('chatInputArea').appendChild(picker);}
</script>
<script src="/theme/assets/js/app.js"></script>
<?php if ($showLogin): ?>
<div id="loginModal" style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.8);backdrop-filter:blur(8px);">
<div style="background:#0b1728;border:1px solid rgba(0,140,255,.2);border-radius:24px;padding:48px;width:400px;max-width:92vw;">
<div style="text-align:center;margin-bottom:32px;">
<h2 style="font-size:28px;font-weight:800;margin-bottom:8px;">Welcome Back</h2>
<p style="color:#94a3b8;">Sign in to your dashboard</p>
</div>
<?php if ($loginError): ?>
<div style="background:rgba(255,50,50,.12);border:1px solid rgba(255,50,50,.3);border-radius:12px;padding:12px 16px;margin-bottom:20px;color:#ff6b6b;font-size:14px;"><?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>
<form method="POST" action="/admin/login/post">
<input type="hidden" name="_csrf_token" value="<?php echo isset($_SESSION['_csrf_token']) ? htmlspecialchars($_SESSION['_csrf_token']) : bin2hex(random_bytes(32)); ?>">
<div style="margin-bottom:20px;">
<label style="display:block;margin-bottom:8px;font-weight:600;font-size:14px;color:#b0c4db;">Email</label>
<input type="text" name="email" value="root" required style="width:100%;padding:14px 18px;border-radius:12px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:16px;outline:none;">
</div>
<div style="margin-bottom:28px;">
<label style="display:block;margin-bottom:8px;font-weight:600;font-size:14px;color:#b0c4db;">Password</label>
<input type="password" name="password" value="admin" required style="width:100%;padding:14px 18px;border-radius:12px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:16px;outline:none;">
</div>
<button type="submit" style="width:100%;padding:16px;border:none;border-radius:14px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;font-size:17px;font-weight:700;cursor:pointer;box-shadow:0 0 20px rgba(0,140,255,.35);">Sign In</button>
</form>
<div style="text-align:center;margin-top:20px;"><a href="/" style="color:#5e8eb0;text-decoration:none;font-size:14px;">Back to home</a></div>
</div></div>
<script>document.getElementById('loginModal')?.addEventListener('click',function(e){if(e.target===this)window.location.href='/';});</script>
<?php endif; ?>
</body>
</html>
