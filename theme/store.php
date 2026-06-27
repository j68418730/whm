<?php
$showLogin = isset($_GET['login']);
$loggedIn = isset($loggedIn) ? $loggedIn : false;
$user = isset($user) ? $user : null;
$categories = isset($categories) ? $categories : [];
$packagesByType = isset($packagesByType) ? $packagesByType : [];
$currentCategory = isset($currentCategory) ? $currentCategory : '';

// Load reviews from DB
$reviews = [];
try {
    $app = \Core\Application::getInstance();
    $pdo = $app->get('db')->pdo();
    $stmt = $pdo->query("SELECT id,name,rating,text,created_at FROM reviews WHERE approved=1 ORDER BY created_at DESC LIMIT 20");
    $reviews = $stmt->fetchAll(\PDO::FETCH_OBJ);
} catch (\Exception $e) {}

// Load all categories for footer
$allCategories = [];
try {
    $app = \Core\Application::getInstance();
    $allCategories = $app->get('db')->table('package_categories')->orderBy('sort_order', 'ASC')->get() ?: [];
} catch (\Exception $e) {}

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($title ?? 'Store - Planet Hosts', ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="description" content="Browse our hosting plans and services.">
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
/* STORE LAYOUT */
.store-layout{display:grid;grid-template-columns:280px 1fr;gap:30px;padding:40px 0;min-height:80vh}
.store-sidebar{background:rgba(8,16,28,.8);border:1px solid rgba(0,191,255,.1);border-radius:16px;padding:24px;height:fit-content;position:sticky;top:100px}
.store-sidebar h3{font-size:1rem;margin-bottom:16px;color:#0A84FF}
.store-sidebar .cat-link{display:block;padding:10px 14px;border-radius:10px;color:#94a3b8;text-decoration:none;font-size:13px;font-weight:500;margin-bottom:4px;transition:.2s}
.store-sidebar .cat-link:hover{color:#fff;background:rgba(0,191,255,.06)}
.store-sidebar .cat-link.active{background:rgba(0,140,255,.12);color:#0A84FF;font-weight:600}
.store-main h1{font-size:2rem;margin-bottom:8px}
.store-main p.sub{color:#94a3b8;margin-bottom:24px}
.store-search{margin-bottom:24px}
.store-search input{width:100%;padding:12px 16px;border-radius:10px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:14px;outline:none;font-family:'Inter',sans-serif}
.store-search input:focus{border-color:#0A84FF}
.store-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px}
.store-card{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:28px;transition:.35s;display:flex;flex-direction:column}
.store-card:hover{transform:translateY(-4px);border-color:#0A84FF;box-shadow:0 0 30px rgba(0,191,255,.08)}
.store-card.featured{border-color:#0A84FF;box-shadow:0 0 30px rgba(0,191,255,.12)}
.store-card.featured::before{content:"Featured";position:absolute;top:14px;right:14px;background:linear-gradient(135deg,#0A84FF,#00E5FF);color:#fff;font-size:11px;font-weight:700;padding:4px 12px;border-radius:6px}
.store-card h4{font-size:1.15rem;margin-bottom:4px}
.store-card .price{font-size:1.8rem;font-weight:800;color:#0A84FF;margin-bottom:10px}
.store-card .price small{font-size:.8rem;font-weight:400;color:#64748b}
.store-card p{color:#94a3b8;font-size:.82rem;line-height:1.6;margin-bottom:10px;flex-grow:1}
.store-card .features-list{list-style:none;padding:0;margin-bottom:14px}
.store-card .features-list li{color:#cbd5e1;font-size:.78rem;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.04);display:flex;align-items:center;gap:8px}
.store-card .features-list li:last-child{border-bottom:none}
.store-card .features-list li i{width:16px;color:#4ade80;font-size:10px}
.store-card .btn-row{display:flex;gap:8px;margin-top:auto}
.store-card .btn-row .btn{flex:1;text-align:center;padding:10px 8px;font-size:13px}
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
.footer-bottom{border-top:1px solid rgba(255,255,255,.04);padding-top:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
.footer-bottom p{color:#64748b;font-size:12px;margin:0}
.footer-bottom a{color:#64748b;font-size:12px;text-decoration:none;margin-left:16px}
.footer-bottom a:hover{color:#0A84FF}
@media(max-width:992px){
.store-layout{grid-template-columns:1fr}
.store-sidebar{position:static}
.nav-links{display:none}
.nav-links.open{display:flex;flex-direction:column;position:absolute;top:100%;left:0;right:0;background:rgba(2,8,23,.98);padding:16px;border-bottom:1px solid rgba(0,191,255,.08);gap:4px}
.nav-toggle{display:block}
.header-inner{position:relative}
.footer-grid{grid-template-columns:1fr 1fr}
}
@media(max-width:768px){
.store-grid{grid-template-columns:1fr}
.footer-grid{grid-template-columns:1fr}
}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="grid-overlay"></div>

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
<a href="/hosting">Store</a>
<a href="?login" class="btn-primary btn-order" style="padding:8px 20px;font-size:13px"><i class="fa-solid fa-cart-plus"></i> Order Now</a>
</nav>
</div>
</header>

<section class="section" style="padding:40px 0">
<div class="container">
<div class="store-layout">
<div class="store-sidebar">
<h3>Categories</h3>
<?php
$shownTypes = [];
?>
<?php foreach ($packagesByType as $type => $pkgs): ?>
<?php if (in_array($type, $shownTypes)) continue; $shownTypes[] = $type; ?>
<?php $displayName = ucwords(str_replace(['_', '-'], ' ', $type)); ?>
<a href="/hosting/<?php echo urlencode($type); ?>" class="cat-link<?php if ($currentCategory === $type): ?> active<?php endif; ?>"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></a>
<?php endforeach; ?>
<a href="/hosting/Game+Servers" class="cat-link<?php if ($currentCategory === 'Game Servers'): ?> active<?php endif; ?>">Game Servers</a>
</div>
<div class="store-main">
<h1><?php echo $currentCategory ? htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', $currentCategory)), ENT_QUOTES, 'UTF-8') : 'All Products'; ?></h1>
<p class="sub">Browse our plans and find the perfect solution for your needs.</p>
<div class="store-search">
<input type="text" id="storeSearch" placeholder="Search products..." onkeyup="filterStore()">
</div>
<div class="store-grid" id="storeGrid">
<?php if ($isGameServers && !empty($gameTypes)): ?>
<?php foreach ($gameTypes as $gt):
$minPrice = ($gt->min_slots ?? 10) * ($gt->price_per_slot ?? 0.50) + ($gt->setup_fee ?? 0);
?>
<div class="store-card" data-name="<?php echo htmlspecialchars(strtolower($gt->name ?? ""), ENT_QUOTES, "UTF-8"); ?>">
<h4><?php echo htmlspecialchars($gt->name ?? "", ENT_QUOTES, "UTF-8"); ?></h4>
<div class="price">$<?php echo number_format($minPrice, 2); ?><small>/mo starting</small></div>
<p><?php echo htmlspecialchars($gt->description ?? "", ENT_QUOTES, "UTF-8"); ?></p>
<ul class="features-list">
<li><i class="fa-solid fa-users"></i> Slots: <?php echo (int)($gt->min_slots ?? 10); ?>-<?php echo (int)($gt->max_slots ?? 100); ?></li>
<li><i class="fa-solid fa-circle-check"></i> $<?php echo number_format($gt->price_per_slot ?? 0.50, 2); ?>/slot</li>
<?php if ($gt->setup_fee > 0): ?><li><i class="fa-solid fa-circle-check"></i> $<?php echo number_format($gt->setup_fee, 2); ?> setup</li><?php endif; ?>
</ul>
<div class="btn-row">
<a href="/game-servers.php?game=<?php echo urlencode($gt->name); ?>" class="btn btn-primary"><i class="fa-solid fa-cart-plus"></i> Order Now</a>
</div>
</div>
<?php endforeach; ?>
<?php elseif (!empty($currentCategory) && isset($packagesByType[$currentCategory])): ?>
<?php foreach ($packagesByType[$currentCategory] as $i => $pkg): ?>
<div class="store-card<?php if ($i === 1): ?> featured<?php endif; ?>" data-name="<?php echo htmlspecialchars(strtolower($pkg->name ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
<h4><?php echo htmlspecialchars($pkg->name, ENT_QUOTES, 'UTF-8'); ?></h4>
<div class="price">$<?php echo number_format((float)($pkg->monthly_price ?? $pkg->price ?? 0), 2); ?><small>/mo</small></div>
<p><?php echo htmlspecialchars($pkg->description ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
<ul class="features-list">
<?php $pf = is_string($pkg->features ?? null) ? json_decode($pkg->features, true) ?? [] : ($pkg->features ?? []); $sp = $pf['streaming_package'] ?? []; $gp = $pf['game_package'] ?? []; ?>
<?php if (!empty($pkg->disk_space) && $pkg->disk_space > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $pkg->disk_space; ?> GB Disk</li><?php endif; ?>
<?php if (!empty($pkg->bandwidth) && $pkg->bandwidth > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $pkg->bandwidth; ?> GB Bandwidth</li><?php endif; ?>
<?php if (!empty($sp['max_listeners'])): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $sp['max_listeners']; ?> Listeners</li><?php endif; ?>
<?php if (!empty($sp['max_bitrate'])): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $sp['max_bitrate']; ?> kbps Bitrate</li><?php endif; ?>
<?php if (!empty($sp['upload_limit'])): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $sp['upload_limit']; ?> MB Upload</li><?php endif; ?>
<?php if (!empty($pkg->email_accounts) && $pkg->email_accounts > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $pkg->email_accounts; ?> Emails</li><?php endif; ?>
<?php if (!empty($pkg->databases) && $pkg->databases > 0): ?><li><i class="fa-solid fa-circle-check"></i> <?php echo $pkg->databases; ?> Databases</li><?php endif; ?>
<li><i class="fa-solid fa-circle-check"></i> Free SSL</li>
<li><i class="fa-solid fa-circle-check"></i> 24/7 Support</li>
</ul>
<div class="btn-row">
<a href="?login" class="btn btn-primary"><i class="fa-solid fa-cart-plus"></i> Order Now</a>
<a href="?login" class="btn btn-secondary">Read More</a>
</div>
</div>
<?php endforeach; ?>
<?php else: ?>
<p style="color:#94a3b8">No products available in this category.</p>
<?php endif; ?>
</div>
</div>
</div>
</div>
</section>

<footer class="footer">
<div class="container">
<div class="footer-grid">
<div class="footer-brand">
<h3>PLANET-<span>HOSTS</span></h3>
<p>Premium web hosting, radio streaming, and server solutions.</p>
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
<a href="/hosting">Store</a>
<a href="?contact">Contact Us</a>
</div>
<div class="footer-col">
<h4>Hosting Services</h4>
<?php
$footerTypes = [];
?>
<?php foreach ($packagesByType as $type => $pkgs): ?>
<?php if (in_array($type, $footerTypes)) continue; $footerTypes[] = $type; ?>
<?php $displayName = ucwords(str_replace(['_', '-'], ' ', $type)); ?>
<a href="/hosting/<?php echo urlencode($type); ?>"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></a>
<?php endforeach; ?>
<a href="/hosting/Game+Servers" class="cat-link<?php if ($currentCategory === 'Game Servers'): ?> active<?php endif; ?>">Game Servers</a>
</div>
<div class="footer-col">
<h4>Support</h4>
<a href="/game-servers.php">Game Servers</a>
<a href="?login">Client Login</a>
<a href="/admin/support/tickets">Submit Ticket</a>
<a href="/admin/support/kb">Knowledgebase</a>
</div>
</div>
<div class="footer-bottom">
<p>&copy; 2026 Planet-Hosts. All rights reserved.</p>
<div>
<a href="#">Terms of Service</a>
<a href="#">Privacy Policy</a>
</div>
</div>
</div>
</footer>

<script>
function filterStore() {
    var input = document.getElementById('storeSearch').value.toLowerCase().trim();
    var cards = document.querySelectorAll('.store-card');
    var visible = 0;
    for (var i = 0; i < cards.length; i++) {
        var name = cards[i].getAttribute('data-name') || '';
        var desc = (cards[i].querySelector('p') || {}).textContent || '';
        var match = !input || name.indexOf(input) > -1 || desc.toLowerCase().indexOf(input) > -1;
        cards[i].style.display = match ? '' : 'none';
        if (match) visible++;
    }
    var empty = document.getElementById('storeEmpty');
    if (visible === 0) {
        if (!empty) {
            empty = document.createElement('p');
            empty.id = 'storeEmpty';
            empty.style.cssText = 'grid-column:1/-1;text-align:center;padding:40px;color:#64748b;font-size:14px';
            empty.textContent = 'No products match your search.';
            document.getElementById('storeGrid').appendChild(empty);
        }
        empty.style.display = '';
    } else if (empty) {
        empty.style.display = 'none';
    }
}
</script>
</body>
</html>
