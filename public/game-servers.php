<?php
session_start();

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Fetch all active game types
$games = $pdo->query("SELECT * FROM game_types WHERE is_active = 1 ORDER BY sort_order ASC, name ASC")->fetchAll(PDO::FETCH_OBJ);

// Fetch tiered pricing
$tiers = $pdo->query("SELECT * FROM game_slot_pricing ORDER BY game_type_id, min_slots ASC")->fetchAll(PDO::FETCH_OBJ);
$tiersByGame = [];
foreach ($tiers as $t) {
    $tiersByGame[$t->game_type_id][] = $t;
}

// Fetch packages
$pkgs = $pdo->query("SELECT * FROM game_packages WHERE is_active = 1 ORDER BY price ASC")->fetchAll(PDO::FETCH_OBJ);
$pkgsByGame = [];
foreach ($pkgs as $p) {
    $pkgsByGame[$p->game_type_id][] = $p;
}

$selectedGameId = 0;
if (isset($_GET['game'])) {
    $gameInput = $_GET['game'];
    // Try as numeric ID first
    if (is_numeric($gameInput)) {
        $selectedGameId = (int)$gameInput;
    } else {
        // Match by name (case-insensitive)
        $decoded = urldecode($gameInput);
        $stmt = $pdo->prepare("SELECT id FROM game_types WHERE LOWER(name) = LOWER(?) OR LOWER(name) LIKE LOWER(?) LIMIT 1");
        $stmt->execute([$decoded, '%' . $decoded . '%']);
        $matched = $stmt->fetchColumn();
        if ($matched) $selectedGameId = (int)$matched;
    }
}
$selectedSlots = isset($_GET['slots']) ? max(1, (int)$_GET['slots']) : 10;

$currency = '$';
$enableSlotPricing = true;
$enablePackages = true;

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Game Servers - Planet Hosts</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{background:#020817;color:#fff;font-family:'Inter',sans-serif;overflow-x:hidden;position:relative}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;background-position:center;z-index:-2}
.grid-overlay{position:fixed;inset:0;background-image:linear-gradient(rgba(0,140,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(0,140,255,.04) 1px,transparent 1px);background-size:80px 80px;z-index:-1;opacity:.35}
.container{width:min(1200px,94%);margin:auto}
.section{padding:60px 0}

.header{position:sticky;top:0;z-index:100;backdrop-filter:blur(12px);background:rgba(2,8,23,.7);border-bottom:1px solid rgba(0,191,255,.08)}
.header-inner{display:flex;align-items:center;justify-content:space-between;padding:14px 0}
.nav-links{display:flex;align-items:center;gap:6px}
.nav-links a{color:#cbd5e1;text-decoration:none;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:500;transition:.2s}
.nav-links a:hover{color:#fff;background:rgba(0,191,255,.06)}
.nav-links .btn-order{background:linear-gradient(135deg,#0A84FF,#00E5FF);color:#fff;padding:10px 20px}
.nav-links .btn-order:hover{transform:translateY(-2px);box-shadow:0 0 20px rgba(0,191,255,.3)}
.nav-toggle{display:none;background:none;border:none;color:#fff;font-size:24px;cursor:pointer;padding:8px}
@media(max-width:768px){
.nav-links{display:none}
.nav-links.open{display:flex;flex-direction:column;position:absolute;top:100%;left:0;right:0;background:rgba(2,8,23,.98);padding:16px;border-bottom:1px solid rgba(0,191,255,.08);gap:4px}
.nav-toggle{display:block}
.header-inner{position:relative}
}

.logo{display:flex;align-items:center;gap:14px;text-decoration:none}
.logo img{width:55px;height:55px;border-radius:12px}
.logo-text{font-family:'Orbitron',sans-serif;font-size:1.4rem;font-weight:700;color:#fff}
.logo-text span{color:#0A84FF}
.logo-sub{color:#94a3b8;font-size:.7rem;letter-spacing:3px;text-transform:uppercase;margin-top:-2px}

.page-title{text-align:center;padding:40px 0 20px}
.page-title h1{font-size:2.4rem;margin-bottom:8px}
.page-title h1 span{color:#0A84FF}
.page-title p{color:#94a3b8;max-width:600px;margin:auto;font-size:1.05rem}

.game-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;margin-bottom:40px}
.game-card{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:24px;text-align:center;cursor:pointer;transition:.35s;position:relative}
.game-card:hover{transform:translateY(-3px);border-color:#0A84FF;box-shadow:0 0 25px rgba(0,191,255,.08)}
.game-card.active{border-color:#0A84FF;background:rgba(0,140,255,.08);box-shadow:0 0 25px rgba(0,191,255,.12)}
.game-card h4{font-size:1rem;margin-bottom:4px}
.game-card p{color:#64748b;font-size:.78rem;line-height:1.5}
.game-card .price-tag{position:absolute;top:12px;right:12px;background:rgba(0,140,255,.15);color:#0A84FF;font-size:11px;font-weight:700;padding:4px 10px;border-radius:6px}

.order-panel{display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-bottom:40px}
@media(max-width:768px){.order-panel{grid-template-columns:1fr}}

.panel-card{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:32px}
.panel-card h3{font-size:1.2rem;margin-bottom:20px;color:#0A84FF}

.slot-display{text-align:center;margin:24px 0}
.slot-display .slot-value{font-size:4rem;font-weight:800;color:#0A84FF;line-height:1}
.slot-display .slot-label{color:#64748b;font-size:.85rem;text-transform:uppercase;letter-spacing:2px;margin-top:4px}

.slider-container{position:relative;padding:10px 0}
input[type=range]{-webkit-appearance:none;width:100%;height:6px;background:rgba(0,191,255,.15);border-radius:4px;outline:none;transition:.2s}
input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,#0A84FF,#00E5FF);cursor:pointer;box-shadow:0 0 20px rgba(0,140,255,.4);transition:.2s}
input[type=range]::-webkit-slider-thumb:hover{transform:scale(1.1)}
input[type=range]::-moz-range-thumb{width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,#0A84FF,#00E5FF);cursor:pointer;border:none;box-shadow:0 0 20px rgba(0,140,255,.4)}

.slot-controls{display:flex;align-items:center;justify-content:center;gap:12px;margin-top:16px}
.slot-btn{width:44px;height:44px;border-radius:12px;border:1px solid rgba(0,191,255,.2);background:rgba(0,140,255,.08);color:#0A84FF;font-size:20px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s;font-weight:700}
.slot-btn:hover{background:rgba(0,140,255,.2);border-color:#0A84FF}
.slot-input{width:80px;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.4);color:#fff;font-size:18px;font-weight:700;text-align:center;outline:none;font-family:'Inter',sans-serif}
.slot-input:focus{border-color:#0A84FF}

.price-breakdown{background:rgba(0,0,0,.3);border-radius:12px;padding:20px;margin-top:20px}
.price-row{display:flex;justify-content:space-between;padding:8px 0;font-size:14px;border-bottom:1px solid rgba(255,255,255,.04)}
.price-row:last-child{border-bottom:none}
.price-row .label{color:#94a3b8}
.price-row .value{font-weight:600;color:#fff}
.price-row.total{font-size:18px;font-weight:800;padding-top:12px;margin-top:8px;border-top:2px solid rgba(0,191,255,.15)}
.price-row.total .value{color:#4ade80}
.price-row.setup .value{color:#fbbf24}

.btn-order{display:block;width:100%;padding:16px;border-radius:12px;border:none;background:linear-gradient(135deg,#0A84FF,#00E5FF);color:#fff;font-size:16px;font-weight:700;cursor:pointer;transition:.3s;font-family:'Inter',sans-serif;margin-top:20px;text-decoration:none;text-align:center}
.btn-order:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(0,191,255,.35)}

.packages-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-top:20px}
.pkg-card{background:rgba(0,0,0,.3);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:20px;text-align:center;cursor:pointer;transition:.3s}
.pkg-card:hover{border-color:#0A84FF;transform:translateY(-2px)}
.pkg-card.selected{border-color:#0A84FF;background:rgba(0,140,255,.08)}
.pkg-card h5{font-size:1rem;margin-bottom:4px}
.pkg-card p{color:#64748b;font-size:.75rem;margin-bottom:8px}
.pkg-card .pkg-price{font-size:1.6rem;font-weight:800;color:#0A84FF}
.pkg-card .pkg-price small{font-size:.7rem;font-weight:400;color:#64748b}
.pkg-card .pkg-slots{color:#94a3b8;font-size:.8rem}
.pkg-card .btn-select{display:inline-block;margin-top:10px;padding:8px 20px;border-radius:8px;background:rgba(0,140,255,.1);border:1px solid rgba(0,191,255,.2);color:#0A84FF;font-size:12px;font-weight:600;cursor:pointer;transition:.2s;text-decoration:none}
.pkg-card .btn-select:hover{background:rgba(0,140,255,.2)}

.info-note{text-align:center;color:#64748b;font-size:13px;margin-top:12px;padding:12px;background:rgba(0,0,0,.2);border-radius:8px}

.tab-bar{display:flex;gap:4px;margin-bottom:24px;background:rgba(0,0,0,.3);border-radius:12px;padding:4px}
.tab-btn{flex:1;padding:10px;border:none;background:transparent;color:#94a3b8;font-size:13px;font-weight:600;border-radius:8px;cursor:pointer;transition:.2s;font-family:'Inter',sans-serif}
.tab-btn.active{background:rgba(0,140,255,.15);color:#0A84FF}
.tab-btn:hover{color:#fff}

.panel-section{display:none}
.panel-section.active{display:block}

@media(max-width:768px){
.game-grid{grid-template-columns:repeat(auto-fill,minmax(160px,1fr))}
.slot-display .slot-value{font-size:3rem}
.page-title h1{font-size:1.6rem}
}

/* Toast */
.toast{position:fixed;bottom:30px;right:30px;background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.25);border-radius:12px;padding:16px 24px;color:#4ade80;font-size:14px;font-weight:600;z-index:9999;transform:translateY(120px);opacity:0;transition:.4s;backdrop-filter:blur(12px)}
.toast.show{transform:translateY(0);opacity:1}
.toast.error{background:rgba(248,113,113,.12);border-color:rgba(248,113,113,.25);color:#f87171}

/* Footer */
.footer{background:rgba(2,8,23,.9);border-top:1px solid rgba(0,191,255,.08);padding:40px 0 20px;margin-top:60px;text-align:center}
.footer p{color:#64748b;font-size:13px}
.footer a{color:#0A84FF;text-decoration:none}
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
<div class="logo-sub">Game Server Hosting</div>
</div>
</a>
<button class="nav-toggle" onclick="document.querySelector('.nav-links').classList.toggle('open')"><i class="fa-solid fa-bars"></i></button>
<nav class="nav-links">
<a href="/">Home</a>
<a href="/hosting">Store</a>
<a href="/hosting/Game+Servers">Game Servers</a>
<a href="http://planet-hosts.com:2082/" class="btn-secondary" style="padding:8px 16px;font-size:13px"><i class="fa-solid fa-user"></i> Client Login</a>
<a href="/cart.php" class="btn-primary btn-order" style="padding:8px 20px;font-size:13px"><i class="fa-solid fa-cart-shopping"></i> Cart</a>
</nav>
</div>
</header>

<div class="container">
<div class="page-title">
<h1>Game <span>Server</span> Hosting</h1>
<p>Deploy high-performance game servers with instant setup. Choose your game, pick your slots, and launch in minutes.</p>
</div>

<?php if (!$selectedGameId): ?>
<div class="game-grid">
<?php foreach ($games as $g): ?>
<a href="?game=<?php echo urlencode($g->name); ?>" class="game-card<?php echo $selectedGameId === (int)$g->id ? ' active' : ''; ?>">
<div class="icon"><?php echo htmlspecialchars($g->icon ?? '🎮'); ?></div>
<h4><?php echo htmlspecialchars($g->name); ?></h4>
<p><?php echo htmlspecialchars($g->description ?? ''); ?></p>
<div class="price-tag">From <?php echo $currency; ?><?php echo number_format($g->price_per_slot, 2); ?>/slot</div>
</a>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php
if ($selectedGameId > 0):
$selectedGame = null;
foreach ($games as $g) { if ((int)$g->id === $selectedGameId) { $selectedGame = $g; break; } }
if ($selectedGame):
$selectedSlots = max((int)$selectedGame->min_slots, min((int)$selectedGame->max_slots, $selectedSlots));

// Calculate price
$pricePerSlot = (float)$selectedGame->price_per_slot;
$basePrice = (float)$selectedGame->price_per_slot;
$gameTiers = $tiersByGame[$selectedGameId] ?? [];
if (!empty($gameTiers)) {
    foreach ($gameTiers as $tier) {
        if ($selectedSlots >= (int)$tier->min_slots && $selectedSlots <= (int)$tier->max_slots) {
            $pricePerSlot = (float)$tier->price_per_slot;
            break;
        }
    }
}
$monthlyCost = $pricePerSlot * $selectedSlots;
$setupFee = (float)$selectedGame->setup_fee;
$total = $monthlyCost + $setupFee;

$gameTiersJson = htmlspecialchars(json_encode($gameTiers), ENT_QUOTES, 'UTF-8');
$gameMinSlots = (int)$selectedGame->min_slots;
$gameMaxSlots = (int)$selectedGame->max_slots;
$gamePkgs = $pkgsByGame[$selectedGameId] ?? [];
?>
<script>
var gameTiers = <?php echo json_encode($gameTiers); ?>;
var basePrice = <?php echo $basePrice; ?>;
var setupFee = <?php echo $setupFee; ?>;
var minSlots = <?php echo $gameMinSlots; ?>;
var maxSlots = <?php echo $gameMaxSlots; ?>;

function getPricePerSlot(slots) {
    for (var i = 0; i < gameTiers.length; i++) {
        if (slots >= parseInt(gameTiers[i].min_slots) && slots <= parseInt(gameTiers[i].max_slots)) {
            return parseFloat(gameTiers[i].price_per_slot);
        }
    }
    return basePrice;
}

function updatePricing(slots) {
    slots = Math.max(minSlots, Math.min(maxSlots, slots));
    var pps = getPricePerSlot(slots);
    var monthly = pps * slots;
    var total = monthly + setupFee;

    document.getElementById('slotValue').textContent = slots;
    document.getElementById('slotRange').value = slots;
    document.getElementById('slotInput').value = slots;
    document.getElementById('pricePerSlot').textContent = '<?php echo $currency; ?>' + pps.toFixed(2);
    document.getElementById('monthlyCost').textContent = '<?php echo $currency; ?>' + monthly.toFixed(2);
    document.getElementById('setupFeeDisplay').textContent = '<?php echo $currency; ?>' + setupFee.toFixed(2);
    document.getElementById('totalDisplay').textContent = '<?php echo $currency; ?>' + total.toFixed(2);
    document.getElementById('addToCartBtn').href = '/cart.php?action=add_game&game_id=<?php echo $selectedGameId; ?>&slots=' + slots + '&price=' + monthly.toFixed(2) + '&setup=' + setupFee.toFixed(2) + '&pps=' + pps.toFixed(2);

    // Update URL without reload
    var url = new URL(window.location);
    url.searchParams.set('slots', slots);
    window.history.replaceState({}, '', url);
}
</script>

<div class="order-panel">
<div class="panel-card">
<h3>Configure Your Server</h3>
<p style="color:#94a3b8;font-size:14px;margin-bottom:16px">
<?php echo htmlspecialchars($selectedGame->icon ?? '🎮'); ?>
<strong><?php echo htmlspecialchars($selectedGame->name); ?></strong>
— <?php echo htmlspecialchars($selectedGame->description ?? ''); ?>
</p>

<?php if (!empty($gamePkgs) && $enablePackages): ?>
<div class="tab-bar">
<button class="tab-btn active" onclick="setTab('slider',this)">Per-Slot Pricing</button>
<button class="tab-btn" onclick="setTab('packages',this)">Fixed Packages</button>
</div>
<?php endif; ?>

<div id="tabSlider" class="panel-section active">
<div class="slot-display">
<div class="slot-value" id="slotValue"><?php echo $selectedSlots; ?></div>
<div class="slot-label">Player Slots</div>
</div>

<div class="slider-container">
<input type="range" id="slotRange" min="<?php echo $gameMinSlots; ?>" max="<?php echo $gameMaxSlots; ?>" value="<?php echo $selectedSlots; ?>" oninput="updatePricing(parseInt(this.value))">
</div>

<div class="slot-controls">
<button class="slot-btn" onclick="updatePricing(Math.max(minSlots, parseInt(document.getElementById('slotInput').value) - 1))">−</button>
<input type="number" class="slot-input" id="slotInput" value="<?php echo $selectedSlots; ?>" min="<?php echo $gameMinSlots; ?>" max="<?php echo $gameMaxSlots; ?>" onchange="updatePricing(parseInt(this.value) || minSlots)">
<button class="slot-btn" onclick="updatePricing(Math.min(maxSlots, parseInt(document.getElementById('slotInput').value) + 1))">+</button>
</div>

<div class="price-breakdown">
<div class="price-row"><span class="label">Price Per Slot</span><span class="value" id="pricePerSlot"><?php echo $currency; ?><?php echo number_format($pricePerSlot, 2); ?></span></div>
<div class="price-row"><span class="label">Slots Selected</span><span class="value" id="slotsSelected"><?php echo $selectedSlots; ?></span></div>
<div class="price-row"><span class="label">Monthly Cost</span><span class="value" id="monthlyCost"><?php echo $currency; ?><?php echo number_format($monthlyCost, 2); ?></span></div>
<?php if ($setupFee > 0): ?>
<div class="price-row setup"><span class="label">Setup Fee</span><span class="value" id="setupFeeDisplay"><?php echo $currency; ?><?php echo number_format($setupFee, 2); ?></span></div>
<?php endif; ?>
<div class="price-row total"><span class="label">Total Today</span><span class="value" id="totalDisplay"><?php echo $currency; ?><?php echo number_format($total, 2); ?></span></div>
</div>

<a id="addToCartBtn" href="/cart.php?action=add_game&game_id=<?php echo $selectedGameId; ?>&slots=<?php echo $selectedSlots; ?>&price=<?php echo number_format($monthlyCost, 2); ?>&setup=<?php echo number_format($setupFee, 2); ?>&pps=<?php echo number_format($pricePerSlot, 2); ?>" class="btn-order">
<i class="fa-solid fa-cart-plus"></i> Add to Cart — <?php echo $currency; ?><?php echo number_format($total, 2); ?>
</a>
</div>

<?php if (!empty($gamePkgs) && $enablePackages): ?>
<div id="tabPackages" class="panel-section">
<p style="color:#94a3b8;font-size:13px;margin-bottom:16px">Pre-configured packages with fixed pricing:</p>
<div class="packages-grid">
<?php foreach ($gamePkgs as $pkg): ?>
<div class="pkg-card" onclick="selectPackage(this, <?php echo $pkg->id; ?>, <?php echo $pkg->price; ?>, <?php echo $pkg->setup_fee ?? 0; ?>, <?php echo $pkg->slots; ?>)">
<h5><?php echo htmlspecialchars($pkg->name); ?></h5>
<p><?php echo htmlspecialchars($pkg->description ?? ''); ?></p>
<div class="pkg-price"><?php echo $currency; ?><?php echo number_format($pkg->price, 2); ?><small>/mo</small></div>
<div class="pkg-slots"><?php echo $pkg->slots; ?> slots</div>
<?php if ($pkg->setup_fee > 0): ?>
<div class="pkg-slots" style="color:#fbbf24;font-size:.7rem">+<?php echo $currency; ?><?php echo number_format($pkg->setup_fee, 2); ?> setup</div>
<?php endif; ?>
<a class="btn-select" href="/cart.php?action=add_game&game_id=<?php echo $selectedGameId; ?>&package_id=<?php echo $pkg->id; ?>&slots=<?php echo $pkg->slots; ?>&price=<?php echo $pkg->price; ?>&setup=<?php echo $pkg->setup_fee ?? 0; ?>&pkg_name=<?php echo urlencode($pkg->name); ?>">Select</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
<?php endif; ?>
</div>

<div class="panel-card">
<h3>About <?php echo htmlspecialchars($selectedGame->name); ?> Hosting</h3>
<ul style="list-style:none;padding:0">
<li style="padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04);display:flex;align-items:center;gap:10px;font-size:14px">
<i class="fa-solid fa-microchip" style="color:#0A84FF;width:20px"></i>
<strong>Dedicated Resources</strong> — CPU, RAM, and SSD allocated per slot
</li>
<li style="padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04);display:flex;align-items:center;gap:10px;font-size:14px">
<i class="fa-solid fa-shield" style="color:#0A84FF;width:20px"></i>
<strong>DDoS Protection</strong> — Enterprise-grade mitigation included
</li>
<li style="padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04);display:flex;align-items:center;gap:10px;font-size:14px">
<i class="fa-solid fa-bolt" style="color:#0A84FF;width:20px"></i>
<strong>Instant Setup</strong> — Server provisioned in under 60 seconds
</li>
<li style="padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04);display:flex;align-items:center;gap:10px;font-size:14px">
<i class="fa-solid fa-headset" style="color:#0A84FF;width:20px"></i>
<strong>24/7 Support</strong> — Expert game server support team
</li>
<li style="padding:10px 0;display:flex;align-items:center;gap:10px;font-size:14px">
<i class="fa-solid fa-rotate" style="color:#0A84FF;width:20px"></i>
<strong>Automated Backups</strong> — Daily backups with one-click restore
</li>
</ul>

<div style="margin-top:20px;padding:16px;background:rgba(0,140,255,.05);border:1px solid rgba(0,191,255,.1);border-radius:12px">
<p style="color:#94a3b8;font-size:12px;line-height:1.6">
<i class="fa-solid fa-circle-info" style="color:#0A84FF;margin-right:6px"></i>
All game servers include a dedicated IP, full FTP access, web-based file manager, and one-click mod/plugin installer. Upgrade or downgrade your slot count at any time.
</p>
</div>
</div>
</div>

<?php if (!empty($gameTiers)): ?>
<div class="info-note">
<i class="fa-solid fa-chart-line" style="margin-right:6px"></i>
Volume discounts available — larger slot counts get lower per-slot pricing.
</div>
<?php endif; ?>

<script>
function setTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    document.querySelectorAll('.panel-section').forEach(function(s) { s.classList.remove('active'); });
    document.getElementById('tab' + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.add('active');
}

function selectPackage(el, id, price, setup, slots) {
    document.querySelectorAll('.pkg-card').forEach(function(c) { c.classList.remove('selected'); });
    el.classList.add('selected');
}
</script>

<?php
$gameTiers = null; // cleanup
?>
<?php endif; ?>
</div>

<footer class="footer">
<div class="container">
<p>&copy; 2026 Planet-Hosts. All rights reserved. | <a href="/">Home</a> | <a href="/cart.php">Cart</a></p>
</div>
</footer>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</body>
</html>

