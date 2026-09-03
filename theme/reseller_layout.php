<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo htmlspecialchars($title ?? 'Reseller Panel'); ?> — Planet Hosts Reseller</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
<?php
$resellerTheme = require __DIR__ . '/reseller_theme.php';
if (!empty($resellerTheme['colors'])) {
    echo ':root{';
    foreach ($resellerTheme['colors'] as $k => $v) echo "--{$k}:{$v};";
    foreach (($resellerTheme['fonts'] ?? []) as $k => $v) echo "--font-{$k}:{$v};";
    echo '}';
}
?>
:root{--bs-body-font-family:var(--font-body,'Inter',sans-serif);--bs-body-bg:var(--bg,#02050e);--bs-body-color:var(--text,#e8edf5)}
.sidebar{width:240px;background:var(--sidebar_bg,#0b1728);border-right:1px solid var(--border,rgba(0,191,255,.08));display:flex;flex-direction:column;height:100vh;position:sticky;top:0;overflow-y:auto;flex-shrink:0}
.sidebar .logo{padding:16px 16px 12px;font-size:16px;font-weight:800;border-bottom:1px solid var(--border,rgba(0,191,255,.08));letter-spacing:1px}
.sidebar .logo small{display:block;font-size:9px;color:#a78bfa;letter-spacing:2px;text-transform:uppercase;margin-top:3px}
.sidebar .logo span{color:var(--primary,#008cff)}
.sidebar .rsell{font-size:12px;color:#a78bfa;padding:10px 16px;border-bottom:1px solid var(--border,rgba(0,191,255,.06));display:flex;align-items:center;gap:6px}
.sidebar .nav{padding:2px 0;flex:1;overflow-y:auto;display:flex;flex-direction:column}
.sidebar .nav-label{font-size:9px;text-transform:uppercase;color:var(--text_muted,#64748b);padding:8px 16px 2px;letter-spacing:1px;font-weight:700}
.sidebar .nav-link{display:flex;align-items:center;gap:10px;padding:8px 16px;color:var(--text_muted,#94a3b8);font-size:14px;text-decoration:none;transition:.1s;border-left:2px solid transparent;width:100%;text-align:left;line-height:1.2}
.sidebar .nav-link:hover{background:rgba(0,191,255,.04);color:var(--text,#e0e0e0)}
.sidebar .nav-link.active{color:var(--primary,#008cff);background:rgba(0,140,255,.08);border-left-color:var(--primary,#008cff)}
.sidebar .nav-link i.bi{font-size:15px;width:18px;text-align:center;flex-shrink:0}
.main{flex:1;display:flex;flex-direction:column;min-width:0}
.topbar{display:flex;justify-content:space-between;align-items:center;padding:12px 24px;border-bottom:1px solid var(--border,rgba(0,191,255,.08));background:rgba(8,16,28,.4)}
.topbar h1{font-size:17px;font-weight:700;margin:0}
.topbar .user-info{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text_muted,#64748b)}
.topbar .hamburger{display:none;background:none;border:none;color:var(--text,#e0e0e0);font-size:20px;cursor:pointer;padding:4px}
.content{padding:20px 24px;flex:1}
.card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:12px;margin-bottom:16px}
.card h3,.card h4{color:var(--accent,#008cff)}
.btn{font-weight:600;font-size:13px;border-radius:8px;padding:8px 20px;transition:.15s;font-family:var(--font-body,'Inter',sans-serif)}
.btn-primary{background:var(--primary,#008cff);border-color:var(--primary,#008cff)}
.btn-primary:hover{opacity:.9;transform:translateY(-1px)}
.btn-secondary{background:rgba(255,255,255,.06);border:1px solid var(--border,rgba(0,191,255,.1));color:var(--text,#e0e0e0)}
.btn-secondary:hover{background:rgba(255,255,255,.1);color:var(--text)}
.btn-danger{background:rgba(248,113,113,.15);border:1px solid rgba(248,113,113,.2);color:var(--danger,#f87171)}
.btn-danger:hover{background:rgba(248,113,113,.25);color:var(--danger)}
.btn-sm{padding:5px 14px;font-size:12px}
.form-control,.form-select{background:rgba(0,0,0,.35);border:1px solid var(--border,rgba(0,191,255,.1));color:var(--text,#e0e0e0);border-radius:8px;padding:10px 14px;font-size:13px}
.form-control:focus,.form-select:focus{border-color:var(--primary,#008cff);box-shadow:0 0 0 2px rgba(0,140,255,.15);background:rgba(0,0,0,.4);color:var(--text)}
.form-control::placeholder{color:var(--text_muted,#64748b)}
.form-label{font-size:12px;color:var(--text_muted,#64748b);font-weight:600;margin-bottom:4px}
.table{font-size:13px;color:var(--text,#e8edf5);margin:0;--bs-table-color:var(--text,#e8edf5);--bs-table-bg:transparent}
.table>:not(caption)>*>th{color:var(--text_muted,#94a3b8);font-size:11px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border,rgba(0,191,255,.04));background:transparent}
.table>:not(caption)>*>td{border-bottom:1px solid var(--border,rgba(0,191,255,.04));padding:10px 8px;vertical-align:middle;color:var(--text,#e8edf5)}
.table-hover>tbody>tr:hover>*{color:var(--text,#e8edf5);background-color:rgba(0,140,255,.05)}
.alert{border-radius:8px;font-size:13px;padding:12px 16px;border:none}
.alert-success{background:rgba(74,222,128,.1);color:var(--success,#4ade80);border:1px solid rgba(74,222,128,.15)}
.alert-danger{background:rgba(248,113,113,.1);color:var(--danger,#f87171);border:1px solid rgba(248,113,113,.15)}
.alert-info{background:rgba(56,189,248,.1);color:#38bdf8;border:1px solid rgba(56,189,248,.15)}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:16px}
.stat-card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:10px;padding:16px;text-align:center}
.stat-card h3{font-size:11px;text-transform:uppercase;color:var(--text_muted,#64748b);letter-spacing:.5px;margin-bottom:6px;font-weight:600}
.stat-card .value{font-size:26px;font-weight:800;line-height:1.2}
.stat-card .label{font-size:11px;color:var(--text_muted,#64748b);margin-top:4px}
.status-badge{padding:3px 10px;border-radius:99px;font-size:11px;font-weight:700}
.status-active{background:rgba(74,222,128,.12);color:#4ade80}
.status-terminated{background:rgba(248,113,113,.12);color:#f87171}
.page-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.action-card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:12px;padding:22px;text-align:center;text-decoration:none;color:var(--text,#e0e0e0);transition:.2s}
.action-card:hover{border-color:var(--primary,#008cff);transform:translateY(-2px)}
.action-card .icon{font-size:30px;margin-bottom:8px;display:block}
.action-card .name{font-size:14px;font-weight:600}
@media(max-width:768px){.main{flex-direction:column}.sidebar{width:100%;height:auto;position:relative;max-height:60px;overflow:hidden}.sidebar.open{max-height:100vh;overflow-y:auto}.topbar .hamburger{display:block}.content{padding:12px 14px}}
</style>
</head>
<body>
<div class="d-flex" style="min-height:100vh">
  <div class="sidebar" id="sidebar">
    <div class="logo">PLANET <span>HOSTS</span><small>Reseller Portal</small></div>
    <div class="rsell">💼 <?php echo htmlspecialchars($reseller->company_name ?? $user->name ?? 'Reseller'); ?></div>
    <div class="nav">
      <div class="nav-label">Management</div>
      <a href="/reseller" class="nav-link <?php echo str_contains($title ?? '', 'Dashboard') ? 'active' : ''; ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="/reseller/alerts" class="nav-link <?php echo str_contains($title ?? '', 'Alerts') ? 'active' : ''; ?>"><i class="bi bi-bell"></i> Alerts<?php if (!empty($alert_unread)): ?> <span style="margin-left:auto;background:#f87171;color:#fff;font-size:10px;font-weight:700;min-width:16px;height:16px;border-radius:99px;display:flex;align-items:center;justify-content:center;padding:0 4px"><?php echo (int)$alert_unread; ?></span><?php endif; ?></a>
      <?php if ($feat('clients') && ($staff === null || $can('clients'))): ?>
      <a href="/reseller/clients" class="nav-link <?php echo str_contains($title ?? '', 'Clients') ? 'active' : ''; ?>"><i class="bi bi-people"></i> Clients</a>
      <?php endif; ?>
      <?php if ($feat('packages') && ($staff === null || $can('packages'))): ?>
      <a href="/reseller/packages" class="nav-link <?php echo str_contains($title ?? '', 'System Packages') ? 'active' : ''; ?>"><i class="bi bi-box-seam"></i> System Packages</a>
      <?php endif; ?>
      <?php if ($feat('provisioning') && ($staff === null || $can('provisioning'))): ?>
      <a href="/reseller/provisioning" class="nav-link <?php echo str_contains($title ?? '', 'Provisioning') ? 'active' : ''; ?>"><i class="bi bi-hdd-network"></i> Provisioning</a>
      <?php endif; ?>
      <?php if ($feat('billing') && ($staff === null || $can('billing'))): ?>
      <a href="/reseller/billing-system" class="nav-link <?php echo str_contains($title ?? '', 'Billing System') ? 'active' : ''; ?>"><i class="bi bi-cash-stack"></i> Billing System</a>
      <?php endif; ?>
      <?php if ($feat('chat') && ($staff === null || $can('chat'))): ?>
      <a href="/reseller/chat-system" class="nav-link <?php echo str_contains($title ?? '', 'Chat System') ? 'active' : ''; ?>"><i class="bi bi-chat-dots"></i> Chat System</a>
      <?php endif; ?>
      <?php if ($feat('support') && ($staff === null || $can('support'))): ?>
      <a href="/reseller/support-system" class="nav-link <?php echo str_contains($title ?? '', 'Support System') ? 'active' : ''; ?>"><i class="bi bi-headset"></i> Support System</a>
      <?php endif; ?>
      <?php if ($feat('streaming') && ($staff === null || $can('radio'))): ?>
      <a href="/reseller/radio-system" class="nav-link <?php echo str_contains($title ?? '', 'Radio System') ? 'active' : ''; ?>"><i class="bi bi-radio"></i> Radio System</a>
      <?php endif; ?>
      <?php if ($feat('game_servers') && ($staff === null || $can('games'))): ?>
      <a href="/reseller/game-system" class="nav-link <?php echo str_contains($title ?? '', 'Game System') ? 'active' : ''; ?>"><i class="bi bi-controller"></i> Game System</a>
      <?php endif; ?>
      <div class="nav-label">Account</div>
      <?php if ($feat('staff') && ($staff === null || $can('staff'))): ?>
      <a href="/reseller/roles" class="nav-link <?php echo str_contains($title ?? '', 'Roles & Staff') ? 'active' : ''; ?>"><i class="bi bi-shield-lock"></i> Roles &amp; Staff</a>
      <?php endif; ?>
      <?php if ($feat('branding') && ($staff === null || $can('branding'))): ?>
      <a href="/reseller/branding" class="nav-link <?php echo str_contains($title ?? '', 'Branding') ? 'active' : ''; ?>"><i class="bi bi-palette"></i> Branding</a>
      <?php endif; ?>
      <a href="/reseller/billing" class="nav-link <?php echo str_contains($title ?? '', 'Billing Overview') ? 'active' : ''; ?>"><i class="bi bi-credit-card"></i> Billing</a>
      <a href="/reseller/support" class="nav-link <?php echo str_contains($title ?? '', 'Support Tickets') ? 'active' : ''; ?>"><i class="bi bi-life-preserver"></i> Support</a>
    </div>
  </div>
  <div class="main">
    <div class="topbar">
      <div class="d-flex align-items-center">
        <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="bi bi-list"></i></button>
      </div>
      <div class="d-flex align-items-center justify-content-center" style="flex:1;text-align:center">
        <h1><?php echo htmlspecialchars($title ?? 'Reseller Panel'); ?></h1>
      </div>
      <div class="user-info">
        <a href="/reseller/alerts" title="Alerts" style="position:relative;color:var(--text_muted,#94a3b8);text-decoration:none;font-size:18px;margin-right:4px">
          <i class="bi bi-bell"></i>
          <?php if (!empty($alert_unread)): ?><span style="position:absolute;top:-6px;right:-8px;background:#f87171;color:#fff;font-size:10px;font-weight:700;min-width:16px;height:16px;border-radius:99px;display:flex;align-items:center;justify-content:center;padding:0 4px"><?php echo (int)$alert_unread; ?></span><?php endif; ?>
        </a>
        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user->name ?? $user->email ?? ''); ?>
        <a href="/user/logout" class="btn btn-sm" style="border:1px solid rgba(248,113,113,.2);background:rgba(248,113,113,.08);color:#f87171;padding:4px 12px;font-size:12px;text-decoration:none"><i class="bi bi-box-arrow-right"></i> Logout</a>
      </div>
    </div>
    <div class="content">
<?php
$typeIcon = ['info' => 'ℹ️', 'warning' => '⚠️', 'success' => '✅', 'danger' => '⛔'];
$typeColor = ['info' => '#38bdf8', 'warning' => '#facc15', 'success' => '#4ade80', 'danger' => '#f87171'];
$srcLabel = ['quota' => 'Quota', 'invoice' => 'Invoice', 'client' => 'Client', 'order' => 'Order', 'admin' => 'Support', 'admin_client' => 'Support'];
// Show top strips for every alert (quota included, but only when actually low).
if (!empty($alerts)):
foreach ($alerts as $al):
$at = $al['type'] ?? 'info';
$asrc = $al['source'] ?? '';
$isQ = $asrc === 'quota';
$isMsg = ($asrc === 'admin' || $asrc === 'admin_client') && empty($al['is_read']);
$color = $typeColor[$at] ?? '#94a3b8';
$dismissible = !empty($al['dismissible']);
$akey = $al['key'] ?? $al['id'];
?>
<div class="card" style="margin-bottom:10px;padding:12px 16px;border:1px solid <?php echo $isQ ? '#f87171' : 'rgba(56,189,248,.15)'; ?>;background:<?php echo $isQ ? 'rgba(248,113,113,.08)' : 'rgba(8,16,28,.6)'; ?>;<?php echo $isMsg ? 'border-left:3px solid ' . $color . ';' : ''; ?>">
<div style="display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap">
<div style="font-size:20px;line-height:1"><?php echo $typeIcon[$at] ?? 'ℹ️'; ?></div>
<div style="flex:1;min-width:220px">
<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
<span style="color:<?php echo $isQ ? '#f87171' : $color; ?>;font-weight:700;font-size:13px"><?php echo htmlspecialchars($al['title'] ?? ''); ?></span>
<?php if ($isMsg): ?><span style="font-size:9px;background:rgba(56,189,248,.15);color:#38bdf8;padding:1px 7px;border-radius:99px">NEW</span><?php endif; ?>
<?php if ($asrc): ?><span style="font-size:10px;color:#64748b;background:rgba(255,255,255,.05);padding:2px 8px;border-radius:99px"><?php echo $srcLabel[$asrc] ?? ucfirst($asrc); ?></span><?php endif; ?>
</div>
<?php if (!empty($al['message'])): ?>
<div style="color:<?php echo $isQ ? '#e2e8f0' : '#94a3b8'; ?>;font-size:13px;margin-top:3px"><?php echo $al['message']; ?></div>
<?php endif; ?>
<?php if (!empty($al['link'])): ?>
<div style="margin-top:6px"><a href="<?php echo $al['link']; ?>" style="font-size:12px;color:var(--primary,#008cff);text-decoration:none">View →</a></div>
<?php endif; ?>
</div>
<?php if ($isMsg || $dismissible): ?>
<div style="align-self:center;display:flex;align-items:center;gap:10px">
<?php if ($isMsg): ?>
<?php if ($asrc === 'admin'): ?>
<a href="/reseller/alerts/read/<?php echo preg_replace('/[^0-9]/', '', $al['id']); ?>" style="font-size:12px;color:#94a3b8;text-decoration:none">Mark read</a>
<?php else: ?>
<a href="/reseller/alerts/read-user/<?php echo (int)$al['user_alert_id']; ?>" style="font-size:12px;color:#94a3b8;text-decoration:none">Mark read</a>
<?php endif; ?>
<?php endif; ?>
<?php if ($dismissible): ?>
<a href="/reseller/alerts/dismiss/<?php echo rawurlencode($akey); ?>" title="Dismiss" onclick="return confirm('Dismiss this alert?')" style="color:#94a3b8;text-decoration:none;font-size:16px;line-height:1;padding:2px 6px;border-radius:6px;border:1px solid rgba(255,255,255,.12)">&times;</a>
<?php endif; ?>
</div>
<?php endif; ?>
</div>
</div>
<?php endforeach; endif; ?>
<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div><?php endif; ?>
<?php echo $content ?? ''; ?>
    </div>
  </div>
</div>
</body>
</html>