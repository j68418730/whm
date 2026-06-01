<?php
$showLogin = isset($_GET['login']);
$loginError = isset($loginError) ? $loginError : null;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Planet Hosts</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">


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
        url('../img/background.png');
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
            <a href="#">Contact</a>
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
        <img src="assets/img/dashboard.png" alt="dashboard">
    </div>

</section>

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
        <img src="assets/img/dashboard.png" alt="preview">
    </div>
</section>

<footer class="footer">
    <div class="container">
        <img src="assets/img/logo.png" alt="logo">

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
            <div style="background:rgba(255,50,50,.12);border:1px solid rgba(255,50,50,.3);border-radius:12px;padding:12px 16px;margin-bottom:20px;color:#ff6b6b;font-size:14px;">
                <?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="/admin/login/post">
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;font-size:14px;color:#b0c4db;">Email</label>
                <input type="email" name="email" value="admin@example.com" required style="width:100%;padding:14px 18px;border-radius:12px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:16px;outline:none;">
            </div>
            <div style="margin-bottom:28px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;font-size:14px;color:#b0c4db;">Password</label>
                <input type="password" name="password" value="admin" required style="width:100%;padding:14px 18px;border-radius:12px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:16px;outline:none;">
            </div>
            <button type="submit" style="width:100%;padding:16px;border:none;border-radius:14px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;font-size:17px;font-weight:700;cursor:pointer;box-shadow:0 0 20px rgba(0,140,255,.35);">
                Sign In
            </button>
        </form>
        <div style="text-align:center;margin-top:20px;"><a href="/" style="color:#5e8eb0;text-decoration:none;font-size:14px;">Back</a></div>
    </div>
</div>
<script>document.getElementById('loginModal')?.addEventListener('click',function(e){if(e.target===this)window.location.href='/';});</script>
<?php endif; ?>

</body>
</html>
