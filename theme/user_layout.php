<?php
$title = $title ?? 'Dashboard';
$hosting = $hosting ?? null;
$package = $package ?? null;
$username = $hosting->username ?? ($user->name ?? 'User');
$userEmail = $hosting->email ?? ($user->email ?? '');
$pkgType = $package->type ?? ($hosting->plan_type ?? '');

$features = [];
if ($package && $package->feature_list_id) {
    try { $fl = (new \Core\Application::getInstance())->get('db')->table('feature_lists')->where('id', $package->feature_list_id)->first(); if($fl) $features = (array)$fl; } catch(\Exception $e) {}
}
$hasWeb = stripos($pkgType, 'web') !== false || stripos($pkgType, 'hosting') !== false || !$pkgType;
$hasRadio = stripos($pkgType, 'icecast') !== false || stripos($pkgType, 'radio') !== false;
$hasGame = stripos($pkgType, 'game') !== false;
$hasBuilder = stripos($pkgType, 'builder') !== false || stripos($pkgType, 'website') !== false;
// Merge package type flags into features array
if (($features['radio'] ?? 0) || $hasRadio || ($package->icecast_enabled ?? 0)) $features['radio'] = 1;
if (($features['game'] ?? 0) || $hasGame || ($package->game_enabled ?? 0)) $features['game'] = 1;
if (($features['builder'] ?? 0) || $hasBuilder || class_exists('\\Plugins\\WebsiteBuilder\\WebsiteBuilderPlugin')) $features['builder'] = 1;
if (($features['dj_panel'] ?? 0) || ($package->dj_panel_enabled ?? 0)) $features['dj_panel'] = 1;
if (($features['livechat'] ?? ($features['chatbox'] ?? 0)) || ($package->live_chat_enabled ?? 0)) $features['livechat'] = 1;
$features['web'] = $hasWeb;
// Load feature-aware menu
require_once BASE_PATH . '/core/UserMenu.php';
$currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/user';
$hasEmail = $features['email_accounts'] ?? -1;
$hasDB = $features['databases'] ?? -1;
$hasFTP = $features['ftp_accounts'] ?? -1;
$hasSSL = $features['ssl_allowed'] ?? 1;
$hasSSH = $features['ssh_access'] ?? 0;
$hasCron = $features['cron_jobs'] ?? 1;
$hasGit = $features['git_access'] ?? 1;
$hasNode = $features['nodejs'] ?? 0;
$hasPython = $features['python'] ?? 0;
$hasTerminal = $features['terminal'] ?? 0;
$hasBackups = $features['backups'] ?? 1;

$serverHost = $_SERVER['HTTP_HOST'] ?? 'planet-hosts.com';
$domain = $hosting->domain ?? $serverHost;
?><!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo htmlspecialchars($title); ?> - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,sans-serif;background:#070b14;color:#e0e0e0;display:flex;min-height:100vh}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.92),rgba(2,8,23,.98)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}

/* Sidebar */
.sidebar{width:250px;background:#0a1628;border-right:1px solid rgba(0,191,255,.08);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100;overflow-y:auto}
.sidebar-logo{padding:18px 16px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;align-items:center;gap:10px}
.sidebar-logo img{width:32px;height:32px;border-radius:8px}
.sidebar-logo h1{font-size:16px;font-weight:800}
.sidebar-logo h1 span{color:#0A84FF}
.sidebar-user{padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;align-items:center;gap:10px}
.sidebar-user .avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#0A84FF,#3bb8ff);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:#fff;flex-shrink:0}
.sidebar-user .info{min-width:0}
.sidebar-user .name{font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sidebar-user .email{font-size:10px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

.sidebar-nav{padding:4px 0;flex:1;overflow-y:auto}
.nav-section{margin-bottom:2px}
.nav-section .nav-label{font-size:9px;text-transform:uppercase;color:#475569;letter-spacing:1.5px;padding:6px 14px 2px;font-weight:700}
.nav-section .nav-link{display:flex;align-items:center;gap:8px;padding:5px 14px;color:#94a3b8;text-decoration:none;font-size:12.5px;transition:.1s;border-left:2px solid transparent}
.nav-section .nav-link:hover{background:rgba(0,191,255,.04);color:#e0e0e0}
.nav-section .nav-link.active{color:#0A84FF;background:rgba(0,140,255,.08);border-left-color:#0A84FF}
.nav-section .nav-link .icon{width:18px;text-align:center;font-size:13px}
.search-box{padding:6px 10px;border-bottom:1px solid rgba(255,255,255,.06)}
.search-box input{width:100%;padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none}
.search-box input:focus{border-color:#0A84FF}

/* Main */
.main{flex:1;margin-left:250px;padding:24px 32px;max-width:1400px}
.main h2{font-size:22px;font-weight:700;margin-bottom:4px}
.main .subtitle{color:#64748b;font-size:13px;margin-bottom:24px}

/* Cards */
.card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:20px;margin-bottom:16px}
.card h3{font-size:14px;font-weight:700;margin-bottom:12px;display:flex;align-items:center;gap:8px}
.card h3 span{color:#64748b;font-size:12px;font-weight:400}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:20px}
.stat-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;text-align:center;transition:.15s}
.stat-card:hover{border-color:rgba(0,140,255,.3);transform:translateY(-2px)}
.stat-card .num{font-size:24px;font-weight:800;margin-bottom:2px}
.stat-card .lbl{font-size:11px;color:#64748b}
.quick-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:10px}
.quick-link{display:flex;align-items:center;gap:10px;padding:12px 14px;background:rgba(255,255,255,.02);border:1px solid rgba(0,191,255,.06);border-radius:10px;text-decoration:none;color:#e0e0e0;font-size:13px;transition:.15s}
.quick-link:hover{border-color:#0A84FF;background:rgba(0,140,255,.04)}
.quick-link .qicon{font-size:18px;width:26px;text-align:center}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:900px){.two-col{grid-template-columns:1fr}}
@media(max-width:768px){.sidebar{width:60px}.sidebar-logo h1,.sidebar-user .info,.sidebar-section .label,.sidebar-section a span{display:none}.sidebar-section a{justify-content:center;padding:10px}.main{margin-left:60px;padding:16px}}
</style>
</head>
<body>
<div class="bg-overlay"></div>

<!-- Sidebar -->
<div class="sidebar">
<div class="sidebar-logo"><img src="/theme/assets/img/logo.png" alt=""><h1>PLANET-<span>HOSTS</span></h1></div>
<div class="sidebar-user"><div class="avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div><div class="info"><div class="name"><?php echo htmlspecialchars($username); ?></div><div class="email"><?php echo htmlspecialchars($userEmail); ?></div></div></div>
<div class="search-box"><input type="text" id="menuSearch" placeholder="Search..." oninput="var q=this.value.toLowerCase();document.querySelectorAll('.sidebar-nav .nav-link').forEach(function(a){a.style.display=q?a.textContent.toLowerCase().indexOf(q)>-1?'':'none':''})"></div>
<div id="sidebarMenu"><?php echo render_user_sidebar($currentUrl, $features); ?></div>
</div>
<?php endif; ?>

<?php if ($hasGame): ?>
<div class="sidebar-section">
<div class="label">Game Servers</div>
<a href="/user/games"><span class="icon">🎮</span><span>My Servers</span></a>
</div>
<?php endif; ?>

<?php if ($hasBuilder): ?>
<div class="sidebar-section">
<div class="label">Website Builder</div>
<a href="/user/websitebuilder"><span class="icon">🏗️</span><span>My Websites</span></a>
</div>
</nav>
</div>

<!-- Main Content -->
<div class="main">
<?php if (isset($_SESSION['success'])): ?><div class="alert alert-success" style="margin-bottom:16px"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
<?php echo $content ?? ''; ?>
</div>
</body>
</html>
