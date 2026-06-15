<?php
$showLogin = isset($_GET['login']);
$loginError = isset($loginError) ? $loginError : null;
$loggedIn = isset($loggedIn) ? $loggedIn : false;
$user = isset($user) ? $user : null;
$packagesByType = isset($packagesByType) ? $packagesByType : [];
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
<div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:20px">
<a href="/admin/support/tickets" style="display:flex;align-items:center;gap:10px;padding:14px 20px;background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.1);border-radius:10px;text-decoration:none;color:#fff;transition:.2s">
<span style="font-size:24px">🎫</span><div><strong>Tickets</strong><br><span style="font-size:12px;color:#94a3b8">Customer support tickets</span></div></a>
<a href="/admin/support/kb" style="display:flex;align-items:center;gap:10px;padding:14px 20px;background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.1);border-radius:10px;text-decoration:none;color:#fff;transition:.2s">
<span style="font-size:24px">📚</span><div><strong>Knowledgebase</strong><br><span style="font-size:12px;color:#94a3b8">Articles and categories</span></div></a>
<a href="/admin/support/announcements" style="display:flex;align-items:center;gap:10px;padding:14px 20px;background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.1);border-radius:10px;text-decoration:none;color:#fff;transition:.2s">
<span style="font-size:24px">📢</span><div><strong>Announcements</strong><br><span style="font-size:12px;color:#94a3b8">System announcements</span></div></a>
</div>
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
<title>Planet Hosts</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#020817;
    color:#fff;
    font-family:'Inter',sans-serif;
    overflow-x:hidden;
    position:relative;
}

.bg-overlay{
    position:fixed;
    inset:0;
    background:
        linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),
        url('/theme/assets/img/background.png');
    background-size:cover;
    background-position:center;
    z-index:-2;
}

.container{
    width:90%;
    max-width:1400px;
    margin:auto;
}

.header{
    padding:25px 0;
    position:sticky;
    top:0;
    z-index:100;
    backdrop-filter:blur(10px);
    background:rgba(2,8,23,.5);
    border-bottom:1px solid rgba(0,191,255,.1);
}

.nav{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.logo{
    display:flex;
    align-items:center;
    gap:18px;
}

.logo img{
    width:70px;
}

.logo h1{
    font-family:'Orbitron',sans-serif;
    font-size:2rem;
}

.logo span{
    color:#0A84FF;
}

.logo p{
    color:#94a3b8;
    letter-spacing:4px;
    text-transform:uppercase;
}

nav a{
    color:#fff;
    text-decoration:none;
    margin-left:24px;
    transition:.3s;
}

nav a:hover{
    color:#00BFFF;
}

.hero{
    min-height:90vh;
    display:grid;
    grid-template-columns:1fr 1fr;
    align-items:center;
    gap:50px;
    padding:80px 0;
}

.hero-text h2{
    font-size:4.4rem;
    line-height:1.1;
    margin-bottom:20px;
}

.hero-text h2 span{
    color:#0A84FF;
}

.hero-text p{
    color:#cbd5e1;
    line-height:1.9;
    margin-bottom:35px;
    max-width:650px;
}

.hero-buttons{
    display:flex;
    gap:20px;
    margin-bottom:50px;
}

.btn{
    padding:15px 28px;
    border-radius:14px;
    text-decoration:none;
    transition:.3s;
    font-weight:600;
}

.primary{
    background:linear-gradient(135deg,#0A84FF,#00E5FF);
    box-shadow:0 0 35px rgba(0,191,255,.35);
    color:#fff;
}

.secondary{
    border:1px solid rgba(0,191,255,.2);
    background:rgba(255,255,255,.03);
    color:#fff;
}

.btn:hover{
    transform:translateY(-3px);
}

.hero-image img{
    width:100%;
    border-radius:22px;
    border:1px solid rgba(0,191,255,.2);
    box-shadow:0 0 80px rgba(0,191,255,.18);
}

.stats{
    display:flex;
    gap:20px;
}

.stat-card,
.feature-card,
.panel{
    background:rgba(255,255,255,.03);
    border:1px solid rgba(0,191,255,.15);
    backdrop-filter:blur(12px);
    border-radius:20px;
}

.stat-card{
    padding:25px;
    width:170px;
}

.stat-card h3{
    color:#00BFFF;
    font-size:2rem;
    margin-bottom:10px;
}

.stat-card p{
    margin:0;
}

.features{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:30px;
    padding:60px 0 100px;
}

.feature-card{
    padding:35px;
    transition:.35s;
}

.feature-card:hover{
    transform:translateY(-8px);
    box-shadow:0 0 40px rgba(0,191,255,.15);
}

.icon{
    font-size:2rem;
    margin-bottom:18px;
}

.feature-card h3{
    margin-bottom:15px;
    color:#0A84FF;
}

.feature-card p{
    color:#94a3b8;
    line-height:1.8;
}

/* SERVICES TAB MENU */
.services{padding:60px 0 100px}
.services .section-title{text-align:center;margin-bottom:40px}
.services .section-title h2{font-size:2.6rem;margin-bottom:10px}
.services .section-title p{color:#94a3b8;font-size:1.1rem}
.tab-menu{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-bottom:50px}
.tab-btn{padding:12px 24px;border-radius:12px;border:1px solid rgba(0,191,255,.15);background:rgba(8,16,28,.8);color:#94a3b8;cursor:pointer;font-size:14px;font-weight:600;transition:.3s;white-space:nowrap}
.tab-btn:hover{color:#fff;border-color:#0A84FF;background:rgba(0,191,255,.08)}
.tab-btn.active{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border-color:#008cff;box-shadow:0 0 20px rgba(0,140,255,.3)}
.tab-content{display:none}
.tab-content.active{display:block}
.pkg-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px}
.pkg-card{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:28px;transition:.35s}
.pkg-card:hover{transform:translateY(-4px);border-color:#0A84FF;box-shadow:0 0 30px rgba(0,191,255,.08)}
.pkg-card h4{font-size:1.3rem;margin-bottom:6px}
.pkg-card .price{font-size:1.8rem;font-weight:800;color:#00BFFF;margin-bottom:12px}
.pkg-card .price small{font-size:.9rem;font-weight:400;color:#94a3b8}
.pkg-card p{color:#94a3b8;font-size:.9rem;line-height:1.7;margin-bottom:15px}
.pkg-card .features-list{list-style:none;padding:0;margin-bottom:18px}
.pkg-card .features-list li{color:#cbd5e1;font-size:.85rem;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.pkg-card .features-list li:last-child{border-bottom:none}
.pkg-card .btn{display:block;text-align:center}

.dashboard-preview{
    padding:50px 0 100px;
}

.panel{
    padding:20px;
}

.panel img{
    width:100%;
    border-radius:18px;
}

.footer{
    padding:80px 0;
    text-align:center;
    border-top:1px solid rgba(0,191,255,.1);
}

.footer img{
    width:110px;
    margin-bottom:20px;
}

.footer h2{
    font-family:'Orbitron',sans-serif;
    font-size:2.2rem;
}

.footer span{
    color:#0A84FF;
}

.footer p{
    color:#94a3b8;
    margin:18px 0 30px;
}

.footer-links{
    margin-bottom:30px;
}

.footer-links a{
    color:#fff;
    text-decoration:none;
    margin:0 14px;
}

.copyright{
    color:#64748b;
}

@media(max-width:992px){

    .hero{
        grid-template-columns:1fr;
        text-align:center;
    }

    .hero-buttons,
    .stats{
        justify-content:center;
        flex-wrap:wrap;
    }

    .nav{
        flex-direction:column;
        gap:20px;
    }

    nav{
        display:flex;
        flex-wrap:wrap;
        justify-content:center;
    }

    .hero-text h2{
        font-size:3rem;
    }
}
</style>
</head>
<body>

<div class="bg-overlay"></div>

<header class="header">
    <div class="container nav">
        <div class="logo">
            <img src="assets/img/logo.png" alt="logo">
            <div>
                <h1>PLANET-<span>HOSTS</span></h1>
                <p>Hosting Panel</p>
            </div>
        </div>

        <nav>
            <a href="#">Home</a>
            <a href="#">Hosting</a>
            <a href="#">Servers</a>
            <a href="#">Domains</a>
            <a href="#">Billing</a>
            <a href="?login">Login</a>
        </nav>
    </div>
</header>

<section class="hero container">

    <div class="hero-text">
        <h2>Modern Hosting<br><span>Built For The Future</span></h2>

        <p>
            Futuristic cloud hosting platform with powerful infrastructure,
            domain management, VPS solutions and enterprise-grade security.
        </p>

        <div class="hero-buttons">
            <a href="?login" class="btn primary">Get Started</a>
            <a href="?login" class="btn secondary">Admin Login</a>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>99.99%</h3>
                <p>Uptime</p>
            </div>

            <div class="stat-card">
                <h3>24/7</h3>
                <p>Support</p>
            </div>

            <div class="stat-card">
                <h3>12K+</h3>
                <p>Clients</p>
            </div>
        </div>
    </div>

    <div class="hero-image">
        <img src="/theme/assets/img/dashboard.png" alt="dashboard">
    </div>

</section>

<?php if (!empty($packagesByType)): ?>
<section class="services container">
<div class="section-title">
<h2>Our Plans &amp; Services</h2>
<p>Choose the perfect plan for your needs. All plans include 24/7 support.</p>
</div>
<?php
$typeLabels = [
    'web_hosting' => '🌐 Web Hosting',
    'web_reseller' => '🏢 Web Hosting Reseller',

    'icecast' => '🎵 Icecast Streaming',
    'icecast_reseller' => '🎵 Icecast Reseller',
    'vps' => '🖥 VPS Servers',
    'dedicated' => '🔧 Dedicated Servers',
];
$firstTab = true;
?>
<div class="tab-menu" id="tabMenu">
<?php foreach ($packagesByType as $type => $pkgs): ?>
<button class="tab-btn<?php if ($firstTab): ?> active<?php $firstTab = false; endif; ?>" onclick="showTab('<?php echo $type; ?>')"><?php echo $typeLabels[$type] ?? $type; ?></button>
<?php endforeach; ?>
</div>
<?php $firstTab = true; ?>
<?php foreach ($packagesByType as $type => $pkgs): ?>
<div class="tab-content<?php if ($firstTab): ?> active<?php $firstTab = false; endif; ?>" id="tab-<?php echo $type; ?>">
<div class="pkg-grid">
<?php foreach ($pkgs as $pkg): ?>
<div class="pkg-card">
<h4><?php echo htmlspecialchars($pkg->name, ENT_QUOTES, 'UTF-8'); ?></h4>
<div class="price">$<?php echo number_format($pkg->monthly_price, 2); ?><small>/mo</small></div>
<p><?php echo htmlspecialchars($pkg->description ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
<ul class="features-list">
<?php if ($pkg->disk_space > 0): ?><li>📁 <?php echo $pkg->disk_space; ?> GB Disk Space</li><?php endif; ?>
<?php if ($pkg->bandwidth > 0): ?><li>📶 <?php echo $pkg->bandwidth; ?> GB Bandwidth</li><?php endif; ?>
<?php if ($pkg->listener_limit > 0): ?><li>🎧 <?php echo $pkg->listener_limit; ?> Listeners</li><?php endif; ?>
<?php if ($pkg->bitrate > 0): ?><li>🎚 <?php echo $pkg->bitrate; ?> kbps Bitrate</li><?php endif; ?>
<?php if ($pkg->storage_limit > 0): ?><li>💾 <?php echo $pkg->storage_limit; ?> GB Storage</li><?php endif; ?>
<?php if ($pkg->dj_accounts > 0): ?><li>🎤 <?php echo $pkg->dj_accounts; ?> DJ Accounts</li><?php endif; ?>
<?php if ($pkg->email_accounts > 0): ?><li>📧 <?php echo $pkg->email_accounts; ?> Email Accounts</li><?php endif; ?>
<?php if ($pkg->databases > 0): ?><li>🗄 <?php echo $pkg->databases; ?> Databases</li><?php endif; ?>
</ul>
<a href="?login" class="btn primary">Get Started</a>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>
</section>
<script>
function showTab(type) {
    document.querySelectorAll('.tab-content').forEach(function(el) { el.classList.remove('active'); });
    document.querySelectorAll('.tab-btn').forEach(function(el) { el.classList.remove('active'); });
    document.getElementById('tab-' + type).classList.add('active');
    document.querySelector('.tab-btn[onclick*="' + type + '"]').classList.add('active');
}
</script>
<?php endif; ?>

<section class="features container">

    <div class="feature-card">
        <div class="icon">☁</div>
        <h3>Cloud Hosting</h3>
        <p>Deploy scalable cloud hosting infrastructure optimized for performance.</p>
    </div>

    <div class="feature-card">
        <div class="icon">🖥</div>
        <h3>Dedicated Servers</h3>
        <p>Enterprise dedicated servers with lightning-fast network speeds.</p>
    </div>

    <div class="feature-card">
        <div class="icon">🌐</div>
        <h3>Domain Management</h3>
        <p>Manage domains, DNS records and SSL certificates easily.</p>
    </div>

    <div class="feature-card">
        <div class="icon">🔒</div>
        <h3>Advanced Security</h3>
        <p>Firewall protection, backups and enterprise monitoring included.</p>
    </div>

</section>

<section class="dashboard-preview container">
    <div class="panel">
        <img src="/theme/assets/img/dashboard.png" alt="preview">
    </div>
</section>

<footer class="footer">
    <div class="container">
        <img src="/theme/assets/img/logo.png" alt="logo">

        <h2>PLANET-<span>HOSTS</span></h2>

        <p>Building the future of hosting infrastructure.</p>

        <div class="footer-links">
            <a href="#">Terms</a>
            <a href="#">Privacy</a>
            <a href="#">Support</a>
            <a href="#">API</a>
        </div>

        <div class="copyright">
            © 2024 Planet-Hosts. All rights reserved.
        </div>
    </div>
</footer>

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
