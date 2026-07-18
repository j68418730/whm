<?php
$pdo = new PDO("mysql:host=localhost;dbname=radiohosting;charset=utf8mb4", "radiouser", "Skylinehosting171");
$packages = $pdo->query("SELECT * FROM hosting_packages WHERE is_active = 1 ORDER BY type, monthly_price LIMIT 20")->fetchAll(PDO::FETCH_OBJ) ?: [];
$categories = [];
foreach ($packages as $p) $categories[$p->type ?? "web_hosting"][] = $p;
$host = $_SERVER["HTTP_HOST"] ?? "planet-hosts.com";
$hosts = ["web_hosting" => "≡ƒîÉ Web Hosting", "icecast" => "≡ƒô╗ Icecast Radio", "game" => "≡ƒÄ« Game Servers", "reseller" => "≡ƒÅó Reseller"];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Planet Hosts - Premium Hosting & Radio Streaming</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,sans-serif;background:#02050e;color:#e0e0e0}
.bg{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.92),rgba(2,8,23,.98)),radial-gradient(ellipse at top,rgba(0,140,255,.06),transparent 70%);z-index:-1}
.container{max-width:1100px;margin:0 auto;padding:20px}
header{text-align:center;padding:30px 0;border-bottom:1px solid rgba(255,255,255,.04)}
header .logo{font-size:28px;font-weight:800;color:#fff}
header .logo span{color:#008cff}
.nav{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:10px}
.nav a{color:#94a3b8;text-decoration:none;font-size:13px;padding:6px 12px;border-radius:6px}
.nav a:hover{color:#fff;background:rgba(255,255,255,.04)}
.hero{text-align:center;padding:60px 0}
.hero h1{font-size:38px;font-weight:800;margin-bottom:10px}
.hero h1 span{background:linear-gradient(135deg,#008cff,#a855f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.hero p{color:#94a3b8;font-size:15px;max-width:650px;margin:0 auto 20px;line-height:1.6}
.btn{display:inline-block;padding:10px 24px;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;transition:.3s;margin:3px}
.btn-primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 0 20px rgba(0,140,255,.3)}
.btn-outline{background:transparent;border:1px solid rgba(255,255,255,.1);color:#94a3b8}
.stats{display:flex;gap:20px;justify-content:center;margin-top:30px;flex-wrap:wrap}
.stat{text-align:center}
.stat .n{font-size:28px;font-weight:800;color:#0A84FF}
.stat .l{font-size:12px;color:#64748b}
.grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin:20px 0}
@media(max-width:900px){.grid{grid-template-columns:1fr}}
.card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:20px;text-align:center;transition:.2s}
.card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.2)}
.card .ico{font-size:32px;margin-bottom:6px}
.card .nm{font-size:16px;font-weight:700}
.card .pr{font-size:24px;font-weight:800;color:#fff;margin:6px 0}
.card .pr small{font-size:13px;color:#64748b}
.card .feat{font-size:12px;color:#94a3b8;line-height:1.8}
.cat-name{font-size:13px;color:#0A84FF;font-weight:600}
.sec-title{text-align:center;margin-bottom:14px}
.sec-sub{text-align:center;color:#64748b;font-size:13px;margin-bottom:20px}
.feat-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin:20px 0}
@media(max-width:700px){.feat-grid{grid-template-columns:1fr}}
.fcard{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:18px;text-align:center}
.fcard .fi{font-size:24px;margin-bottom:6px}
.fcard .ft{font-weight:600;font-size:13px}
.fcard .fd{font-size:11px;color:#64748b;margin-top:4px}
.test-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:20px 0}
@media(max-width:700px){.test-grid{grid-template-columns:1fr}}
.test{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;font-size:12px;color:#94a3b8;line-height:1.6}
.test .au{color:#64748b;font-size:11px;margin-top:6px}
footer{text-align:center;padding:30px 0;border-top:1px solid rgba(255,255,255,.04);margin-top:40px;font-size:12px;color:#64748b}
.flinks{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin:10px 0}
.flinks a{color:#94a3b8;text-decoration:none;font-size:12px}
.flinks a:hover{color:#fff}
</style>
</head>
<body><div class="bg"></div><div class="container">
<header>
<div class="logo">PLANET-<span>HOSTS</span></div>
<div class="nav">
<a href="/">Home</a><a href="#pricing">Packages</a><a href="#features">Features</a>
<a href="http://<?php echo $host; ?>:2082/">Client Login</a>
<a href="http://<?php echo $host; ?>:2087/">Admin Login</a>
<a href="http://<?php echo $host; ?>:2096/">Webmail</a>
</div>
</header>

<div class="hero">
<h1>Premium Hosting &amp; <span>Radio Streaming</span></h1>
<p>Planet Hosts delivers powerful web hosting, SHOUTcast/Icecast radio streaming, reseller solutions, and a complete WHM control panel. Built for performance, backed by 24/7 support.</p>
<a href="#pricing" class="btn btn-primary">View Packages</a>
<a href="/order" class="btn btn-outline">Order Hosting</a>
<a href="http://<?php echo $host; ?>:2082/" class="btn btn-outline">Client Area</a>
<div class="stats">
<div class="stat"><div class="n">99.9%</div><div class="l">Uptime Guarantee</div></div>
<div class="stat"><div class="n">24/7</div><div class="l">Expert Support</div></div>
<div class="stat"><div class="n">15+</div><div class="l">Years Experience</div></div>
</div>
</div>

<div id="features" style="margin:30px 0">
<h2 class="sec-title">Why Planet Hosts</h2>
<p class="sec-sub">Industry-leading features included with every plan</p>
<div class="feat-grid">
<div class="fcard"><div class="fi">ΓÜí</div><div class="ft">NVMe SSD Storage</div><div class="fd">Lightning-fast NVMe SSD drives for maximum performance</div></div>
<div class="fcard"><div class="fi">≡ƒöÆ</div><div class="ft">Free SSL Certificates</div><div class="fd">Auto-installed Let's Encrypt SSL on all domains</div></div>
<div class="fcard"><div class="fi">≡ƒ¢í∩╕Å</div><div class="ft">DDoS Protection</div><div class="fd">Advanced mitigation filtering at network edge</div></div>
<div class="fcard"><div class="fi">≡ƒÆ╛</div><div class="ft">Daily Backups</div><div class="fd">Automated daily backups with 7-day retention</div></div>
<div class="fcard"><div class="fi">≡ƒôï</div><div class="ft">WHM Control Panel</div><div class="fd">Full account, DNS, email, database management</div></div>
<div class="fcard"><div class="fi">≡ƒÄº</div><div class="ft">Icecast Radio</div><div class="fd">Built-in SHOUTcast/Icecast streaming support</div></div>
</div></div>

<div id="pricing" style="margin:30px 0">
<h2 class="sec-title">Simple Pricing</h2>
<p class="sec-sub">Choose the plan that fits your needs. All plans include our full panel.</p>
<div class="grid">
<?php foreach ($packages as $pkg):
$type = $pkg->type ?? "web_hosting";
$catName = $hosts[$type] ?? $type;
?>
<div class="card">
<div class="ico"><?php echo strpos($type,"icecast")!==false ? "≡ƒô╗" : (strpos($type,"game")!==false ? "≡ƒÄ«" : "≡ƒîÉ"); ?></div>
<div class="cat-name"><?php echo htmlspecialchars($catName); ?></div>
<div class="nm"><?php echo htmlspecialchars($pkg->name); ?></div>
<div class="pr">$<?php echo number_format($pkg->monthly_price ?? 0, 2); ?><small>/mo</small></div>
<div class="feat">
<?php
if ($pkg->disk_space > 0) echo number_format($pkg->disk_space) . " MB Disk<br>";
if ($pkg->bandwidth > 0) echo number_format($pkg->bandwidth) . " MB Bandwidth<br>";
if ($pkg->email_accounts > 0) echo ($pkg->email_accounts < 0 ? "Unlimited" : $pkg->email_accounts) . " Emails<br>";
if ($pkg->databases > 0) echo ($pkg->databases < 0 ? "Unlimited" : $pkg->databases) . " Databases<br>";
echo "Free SSL<br>24/7 Support";
?>
</div>
<a href="/order?package=<?php echo $pkg->id; ?>" class="btn btn-primary">Order Now</a>
</div>
<?php endforeach; ?>
</div></div>

<div style="margin:30px 0">
<h2 class="sec-title">What Our Clients Say</h2>
<div class="test-grid">
<div class="test">"If you run an online radio station, stop looking and get this panel. Icecast integration is seamless."<div class="au">- Alex H.</div></div>
<div class="test">"Finally a control panel that does everything. Hosting, radio streaming, billing ΓÇö it is all here."<div class="au">- Rachel N.</div></div>
<div class="test">"We run a 24/7 radio station and this panel handles everything perfectly. AutoDJ is fantastic."<div class="au">- Sarah M.</div></div>
<div class="test">"Solid WHM features with unique radio integration. Support team responded within minutes."<div class="au">- Lisa K.</div></div>
</div></div>

<footer>
<div class="flinks">
<a href="/">Home</a><a href="#pricing">Pricing</a><a href="#features">Features</a>
<a href="http://<?php echo $host; ?>:2082/">Client Login</a>
<a href="http://<?php echo $host; ?>:2087/">Admin Login</a>
<a href="http://<?php echo $host; ?>:2096/">Webmail</a>
</div>
<p>&copy; 2026 Planet-Hosts. All rights reserved. Terms of Service | Privacy Policy</p>
</footer>
</div>
<script>var img=new Image();img.src='https://planet-hosts.com/track.php?id=portal&r='+encodeURIComponent(document.referrer)+'&u='+encodeURIComponent(location.href);img.style.display='none';document.body.appendChild(img);</script>
</body></html>
