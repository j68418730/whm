<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo htmlspecialchars($title ?? 'Dashboard'); ?> — Planet Hosts</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
<?php $te = \Core\ThemeEngine::getInstance(); echo $te->getThemeCss('admin'); ?>
:root{--bs-body-font-family:var(--font-body,'Inter',sans-serif);--bs-body-bg:var(--bg,#02050e);--bs-body-color:var(--text,#e0e0e0)}
.sidebar{width:240px;background:var(--sidebar_bg,#0b1728);border-right:1px solid var(--border,rgba(0,191,255,.08));display:flex;flex-direction:column;height:100vh;position:sticky;top:0;overflow-y:auto;flex-shrink:0}
.sidebar .logo{padding:14px 14px;font-size:16px;font-weight:800;border-bottom:1px solid var(--border,rgba(0,191,255,.08));letter-spacing:1px}
.sidebar .logo span{color:var(--primary,#008cff)}
.sidebar .search{padding:6px 10px;border-bottom:1px solid var(--border,rgba(0,191,255,.08))}
.sidebar .search input{width:100%;padding:5px 8px;border-radius:5px;border:1px solid var(--border,rgba(0,191,255,.08));background:rgba(0,0,0,.3);color:var(--text,#e0e0e0);font-size:11px}
.sidebar .search input:focus{border-color:var(--primary,#008cff);outline:none}
.sidebar .nav{padding:2px 0;flex:1;overflow-y:auto}
.sidebar .nav-section{margin-bottom:0}
.sidebar .nav-label{font-size:9px;text-transform:uppercase;color:var(--text_muted,#64748b);padding:5px 14px 2px;letter-spacing:1px;font-weight:700;cursor:pointer;user-select:none;display:flex;align-items:center;justify-content:space-between}
.sidebar .nav-label .collapse-icon{font-size:8px;transition:transform .2s;margin-left:auto}
.sidebar .nav-label .collapse-icon.collapsed{transform:rotate(-90deg)}
.sidebar .nav-link{display:flex;align-items:center;gap:8px;padding:5px 14px;color:var(--text_muted,#94a3b8);font-size:12.5px;text-decoration:none;transition:.1s;border-left:2px solid transparent}
.sidebar .nav-link:hover{background:rgba(0,191,255,.04);color:var(--text,#e0e0e0)}
.sidebar .nav-link.active{color:var(--primary,#008cff);background:rgba(0,140,255,.08);border-left-color:var(--primary,#008cff)}
.sidebar .nav-link i.bi{font-size:13px;width:16px;text-align:center;flex-shrink:0}
.main{flex:1;display:flex;flex-direction:column;min-width:0}
.topbar{display:flex;justify-content:space-between;align-items:center;padding:12px 24px;border-bottom:1px solid var(--border,rgba(0,191,255,.08));background:rgba(8,16,28,.4)}
.topbar h1{font-size:17px;font-weight:700;margin:0}
.topbar .user-info{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text_muted,#64748b)}
.topbar .hamburger{display:none;background:none;border:none;color:var(--text,#e0e0e0);font-size:20px;cursor:pointer;padding:4px}
.content{padding:20px 24px;flex:1}
.card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:12px;margin-bottom:16px}
.card-body{padding:20px}
.btn{font-weight:600;font-size:13px;border-radius:8px;padding:8px 20px;transition:.15s;font-family:var(--font-body,'Inter',sans-serif)}
.btn-primary{background:var(--primary,#008cff);border-color:var(--primary,#008cff)}
.btn-primary:hover{opacity:.9;transform:translateY(-1px)}
.btn-secondary{background:rgba(255,255,255,.06);border:1px solid var(--border,rgba(0,191,255,.1));color:var(--text,#e0e0e0)}
.btn-secondary:hover{background:rgba(255,255,255,.1);color:var(--text)}
.btn-sm{padding:5px 14px;font-size:12px}
.form-control,.form-select{background:rgba(0,0,0,.35);border:1px solid var(--border,rgba(0,191,255,.1));color:var(--text,#e0e0e0);border-radius:8px;padding:10px 14px;font-size:13px}
.form-control:focus,.form-select:focus{border-color:var(--primary,#008cff);box-shadow:0 0 0 2px rgba(0,140,255,.15)}
.form-label{font-size:12px;color:var(--text_muted,#64748b);font-weight:600;margin-bottom:4px}
.table{font-size:13px;color:var(--text,#e0e0e0);margin:0}
.table>:not(caption)>*>td,.table>:not(caption)>*>th{color:var(--text,#e0e0e0);border-bottom:1px solid var(--border,rgba(0,191,255,.04));padding:8px;vertical-align:middle}
.alert{border-radius:8px;font-size:13px;padding:12px 16px;border:none}
.alert-success{background:rgba(74,222,128,.1);color:var(--success,#4ade80)}
.alert-danger{background:rgba(248,113,113,.1);color:var(--danger,#f87171)}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:16px}
.stat-card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:10px;padding:16px;text-align:center}
.stat-card h3{font-size:11px;text-transform:uppercase;color:var(--text_muted,#64748b);letter-spacing:.5px;margin-bottom:6px;font-weight:600}
.stat-card .value{font-size:26px;font-weight:800;line-height:1.2}
.stat-card .label{font-size:11px;color:var(--text_muted,#64748b);margin-top:4px}
@media(max-width:768px){
.main{flex-direction:column}
.sidebar{width:100%;height:auto;position:relative;max-height:60px;overflow:hidden}
.sidebar.open{max-height:100vh;overflow-y:auto}
.topbar .hamburger{display:block}
.content{padding:12px 14px}
.stats-grid{grid-template-columns:1fr 1fr}
}
.nav-label{cursor:pointer;user-select:none;display:flex;align-items:center;justify-content:space-between}
.nav-label:hover{color:var(--text,#e0e0e0)}
.nav-label .collapse-icon{font-size:9px;transition:transform .2s;margin-left:auto}
.nav-section.collapsed .nav-link{display:none}
.nav-section.collapsed .nav-label .collapse-icon{transform:rotate(-90deg)}
</style>
</head>
<body>
<div class="d-flex" style="min-height:100vh">
  <div class="sidebar" id="sidebar">
    <div class="logo">PLANET <span>HOSTS</span></div>
    <div class="search"><input type="text" class="form-control" placeholder="Search menu..." oninput="var q=this.value.toLowerCase();document.querySelectorAll('.sidebar .nav-link').forEach(function(a){a.style.display=a.textContent.toLowerCase().indexOf(q)>-1?'':'none'})"></div>
    <div class="nav">
<?php
$currentUrl = $_SERVER['REQUEST_URI'] ?? '';
$pkgType = isset($package) && isset($package->type) ? $package->type : (isset($hosting) && isset($hosting->plan_type) ? $hosting->plan_type : '');
$isWeb = $pkgType === '' || $pkgType === 'web_hosting' || $pkgType === 'hosting' || str_contains($pkgType, 'web');
$features = [];
if ($package && !empty($package->feature_list_id)) {
    try { $db = \Core\Application::getInstance()->get('db'); $fl = $db->table('feature_lists')->where('id', $package->feature_list_id)->first(); if($fl) $features = (array)$fl; } catch(\Exception $e) {}
}
if ($isRadio) $features['radio'] = 1;
$features['web'] = $isWeb;
require_once BASE_PATH . '/core/UserMenu.php';
$items = user_menu_items($features);
echo render_user_sidebar($items, $currentUrl); ?>
    </div>
  </div>
    <div class="main">
    <div class="topbar">
      <div class="d-flex align-items-center" style="flex:0 0 auto">
        <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="bi bi-list"></i></button>
      </div>
      <div class="d-flex align-items-center justify-content-center gap-3" style="flex:1;text-align:center">
        <h1 style="font-size:16px;font-weight:600;margin:0"><?php echo htmlspecialchars($title ?? 'Dashboard'); ?></h1>
        <?php if (!empty($_SESSION['sudo_login'])): ?>
        <span style="font-size:11px;color:#38bdf8;background:rgba(56,189,248,.1);padding:3px 10px;border-radius:12px;font-weight:600">ADMIN VIEWING</span>
        <?php endif; ?>
      </div>
      <div class="d-flex align-items-center justify-content-end gap-2" style="flex:0 0 auto">
        <span class="user-info"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($hosting->username ?? $user->name ?? 'User'); ?></span>
        <?php if (!empty($_SESSION['sudo_login'])): ?>
        <a href="/admin/exit-sudo" class="btn btn-sm btn-primary" style="padding:4px 12px;font-size:12px"><i class="bi bi-arrow-left"></i> Admin Panel</a>
        <?php endif; ?>
        <a href="/user/logout" class="btn btn-sm" style="border:1px solid rgba(248,113,113,.2);background:rgba(248,113,113,.08);color:#f87171;padding:4px 12px;font-size:12px"><i class="bi bi-box-arrow-right"></i> Logout</a>
      </div>
    </div>
    <div class="content">
<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success"><i class="bi bi-check-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div><?php endif; ?>
<?php echo $content ?? ''; ?>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.nav-section').forEach(function(section) {
    var label = section.querySelector('.nav-label');
    if (!label) return;
    var icon = document.createElement('span');
    icon.className = 'collapse-icon bi bi-chevron-down';
    label.appendChild(icon);
    var stored = localStorage.getItem('user_nav_' + label.textContent.trim().toLowerCase().replace(/\s+/g, '-'));
    if (stored === 'collapsed') { section.classList.add('collapsed'); icon.classList.add('collapsed'); }
    label.addEventListener('click', function(e) {
        if (e.target.closest('a')) return;
        section.classList.toggle('collapsed');
        icon.classList.toggle('collapsed');
        localStorage.setItem('user_nav_' + label.textContent.trim().toLowerCase().replace(/\s+/g, '-'), section.classList.contains('collapsed') ? 'collapsed' : 'open');
    });
});
</script>
</body>
</html>
