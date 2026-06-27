<?php
$showLogin = isset($_GET['login']);
$loggedIn = isset($loggedIn) ? $loggedIn : false;
$user = isset($user) ? $user : null;
$product = isset($product) ? $product : null;
$categories = isset($categories) ? $categories : [];
if (!$product):
header("Location: /hosting");
exit;
endif;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars(($product->name ?? 'Product') . ' - Planet Hosts', ENT_QUOTES, 'UTF-8'); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#020817;color:#fff;font-family:'Inter',sans-serif;overflow-x:hidden}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.container{width:min(1200px,94%);margin:auto}
.header{position:sticky;top:0;z-index:100;backdrop-filter:blur(12px);background:rgba(2,8,23,.7);border-bottom:1px solid rgba(0,191,255,.08)}
.header-inner{display:flex;align-items:center;justify-content:space-between;padding:14px 0}
.logo{display:flex;align-items:center;gap:14px;text-decoration:none}
.logo img{width:50px;height:50px;border-radius:12px}
.logo-text{font-family:'Orbitron',sans-serif;font-size:1.3rem;font-weight:700;color:#fff}
.logo-text span{color:#0A84FF}
.logo-sub{color:#94a3b8;font-size:.7rem;letter-spacing:3px;text-transform:uppercase}
.nav-links{display:flex;align-items:center;gap:6px}
.nav-links a{color:#cbd5e1;text-decoration:none;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:500;transition:.2s}
.nav-links a:hover{color:#fff;background:rgba(0,191,255,.06)}
.nav-links .btn-order{background:linear-gradient(135deg,#0A84FF,#00E5FF);color:#fff;padding:10px 20px}
.nav-toggle{display:none;background:none;border:none;color:#fff;font-size:24px;cursor:pointer;padding:8px}
.btn{display:inline-block;padding:12px 28px;border-radius:12px;text-decoration:none;transition:.3s;font-weight:600;font-size:14px;cursor:pointer;border:none;font-family:'Inter',sans-serif}
.btn-primary{background:linear-gradient(135deg,#0A84FF,#00E5FF);color:#fff}
.btn-primary:hover{transform:translateY(-3px)}
.btn-secondary{border:1px solid rgba(0,191,255,.2);background:rgba(255,255,255,.03);color:#fff}
.btn-secondary:hover{transform:translateY(-3px);border-color:#0A84FF}
.btn-lg{padding:16px 36px;font-size:16px}
.breadcrumb{padding:20px 0;font-size:13px;color:#64748b}
.breadcrumb a{color:#0A84FF;text-decoration:none}
.breadcrumb a:hover{text-decoration:underline}
.breadcrumb span{color:#64748b;margin:0 6px}
.product-layout{display:grid;grid-template-columns:1fr 1fr;gap:50px;padding:20px 0 60px}
.product-image img{width:100%;border-radius:20px;border:1px solid rgba(0,191,255,.12)}
.product-info h1{font-size:2.4rem;margin-bottom:8px}
.product-info h1 span{color:#0A84FF}
.product-info .price{font-size:2.4rem;font-weight:800;color:#0A84FF;margin-bottom:6px}
.product-info .price small{font-size:.9rem;font-weight:400;color:#64748b}
.product-info p.desc{color:#94a3b8;line-height:1.8;margin-bottom:20px;font-size:14px}
.product-info .billing-cycle{margin-bottom:20px}
.product-info .billing-cycle select{padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.4);color:#fff;font-size:14px;outline:none}
.feature-list{list-style:none;padding:0;margin-bottom:24px;display:grid;grid-template-columns:1fr 1fr;gap:8px}
.feature-list li{color:#cbd5e1;font-size:13px;padding:8px 12px;background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.06);border-radius:8px;display:flex;align-items:center;gap:8px}
.feature-list li i{color:#4ade80}
.footer{background:rgba(2,8,23,.9);border-top:1px solid rgba(0,191,255,.08);padding:40px 0 20px;text-align:center}
.footer p{color:#64748b;font-size:13px}
@media(max-width:768px){
.product-layout{grid-template-columns:1fr;gap:30px}
.product-info h1{font-size:1.8rem}
.feature-list{grid-template-columns:1fr}
.nav-links{display:none}
.nav-links.open{display:flex;flex-direction:column}
.nav-toggle{display:block}
}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<header class="header">
<div class="container header-inner">
<a href="/" class="logo"><img src="/theme/assets/img/logo.png" alt="Planet Hosts"><div><div class="logo-text">PLANET-<span>HOSTS</span></div><div class="logo-sub">Hosting Panel</div></div></a>
<button class="nav-toggle" onclick="document.querySelector('.nav-links').classList.toggle('open')"><i class="fa-solid fa-bars"></i></button>
<nav class="nav-links">
<a href="/">Home</a><a href="/hosting">Store</a><a href="?contact">Contact</a>
<a href="http://45.61.59.55:2082/" class="btn-secondary" style="padding:8px 16px;font-size:13px"><i class="fa-solid fa-user"></i> Client Login</a>
<a href="/cart.php" class="btn-primary btn-order" style="padding:8px 20px;font-size:13px"><i class="fa-solid fa-cart-shopping"></i> Cart</a>
</nav>
</div>
</header>

<section class="container">
<div class="breadcrumb">
<a href="/">Home</a><span>&#8250;</span><a href="/hosting">Store</a><span>&#8250;</span><span style="color:#94a3b8"><?php echo htmlspecialchars($product->name ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
</div>
<div class="product-layout">
<div class="product-image"><img src="/theme/assets/img/dashboard.png" alt="<?php echo htmlspecialchars($product->name ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
<div class="product-info">
<h1><?php echo htmlspecialchars($product->name ?? '', ENT_QUOTES, 'UTF-8'); ?> <span>Plan</span></h1>
<div class="price">$<?php echo number_format((float)($product->monthly_price ?? $product->price ?? 0), 2); ?><small>/month</small></div>
<?php if (!empty($product->description)): ?>
<p class="desc"><?php echo nl2br(htmlspecialchars($product->description ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
<?php endif; ?>
<div class="billing-cycle">
<label style="font-size:13px;color:#94a3b8;display:block;margin-bottom:6px">Billing Cycle</label>
<select id="billingCycle">
<option value="1">Monthly - $<?php echo number_format((float)($product->monthly_price ?? $product->price ?? 0), 2); ?>/mo</option>
<option value="3">Quarterly - $<?php echo number_format((float)($product->monthly_price ?? $product->price ?? 0) * 3 * 0.95, 2); ?> (5% off)</option>
<option value="6">Semi-Annual - $<?php echo number_format((float)($product->monthly_price ?? $product->price ?? 0) * 6 * 0.90, 2); ?> (10% off)</option>
<option value="12">Annual - $<?php echo number_format((float)($product->monthly_price ?? $product->price ?? 0) * 12 * 0.80, 2); ?> (20% off)</option>
</select>
</div>
<ul class="feature-list">
<?php $pf = is_string($product->features ?? null) ? json_decode($product->features, true) ?? [] : ($product->features ?? []); $sp = $pf['streaming_package'] ?? []; $gp = $pf['game_package'] ?? []; ?>
<?php if (!empty($product->disk_space) && $product->disk_space > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $product->disk_space; ?> GB Disk</li><?php endif; ?>
<?php if (!empty($product->bandwidth) && $product->bandwidth > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $product->bandwidth; ?> GB Bandwidth</li><?php endif; ?>
<?php if (!empty($sp['max_listeners'])): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $sp['max_listeners']; ?> Listeners</li><?php endif; ?>
<?php if (!empty($sp['max_bitrate'])): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $sp['max_bitrate']; ?> kbps</li><?php endif; ?>
<?php if (!empty($sp['upload_limit'])): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $sp['upload_limit']; ?> MB Upload</li><?php endif; ?>
<?php if (!empty($product->email_accounts) && $product->email_accounts > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $product->email_accounts; ?> Emails</li><?php endif; ?>
<?php if (!empty($product->databases) && $product->databases > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $product->databases; ?> Databases</li><?php endif; ?>
<?php if (!empty($product->addon_domains) && $product->addon_domains > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $product->addon_domains; ?> Addon Domains</li><?php endif; ?>
<?php if (!empty($product->subdomains) && $product->subdomains > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $product->subdomains; ?> Subdomains</li><?php endif; ?>
<?php if (!empty($sp['max_djs'])): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $sp['max_djs']; ?> DJ Accounts</li><?php endif; ?>
<li><i class="fa-solid fa-circle-check"></i> Free SSL</li>
<li><i class="fa-solid fa-circle-check"></i> 24/7 Support</li>
</ul>
<div style="display:flex;gap:12px;flex-wrap:wrap">
<a href="/cart.php?action=add&id=<?php echo (int)$product->id; ?>&name=<?php echo urlencode($product->name ?? ''); ?>&price=<?php echo (float)($product->monthly_price ?? $product->price ?? 0); ?>" class="btn btn-primary btn-lg"><i class="fa-solid fa-cart-plus"></i> Order Now</a>
<a href="/hosting" class="btn btn-secondary btn-lg"><i class="fa-solid fa-arrow-left"></i> Back to Plans</a>
</div>
</div>
</div>
</section>

<footer class="footer"><div class="container"><p>&copy; 2026 Planet-Hosts. All rights reserved.</p></div></footer>
</body>
</html>
