<?php
$user = isset($user) ? $user : null;
$title = isset($title) ? $title : 'Dashboard';
$currentUrl = $_SERVER['REQUEST_URI'] ?? '';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?> - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<?php 
$activeTheme = 'planethosts';
$ts = [];
try {
    if (class_exists('\\Core\\Application')) {
        $app = \Core\Application::getInstance();
        $db = $app->get('db');
        if ($db) {
            $rows = $db->table('automation_settings')->get() ?: [];
            if (is_array($rows)) { foreach ($rows as $r) $ts[$r->setting_key] = $r->setting_value; }
            $activeTheme = $ts['theme'] ?? 'cosmic';
        }
    }
} catch (\Exception $e) { error_log("Theme load error: " . $e->getMessage()); }
$themeFile = "/theme/themes/{$activeTheme}/style.css";
$customCss = $ts['custom_css'] ?? '';
$footerText = $ts['footer_text'] ?? 'Building the future of hosting infrastructure.';
$footerLogo = $ts['footer_logo_url'] ?? '/theme/assets/img/logo.png';
$primaryColor = $ts['primary_color'] ?? '';
$bgColor = $ts['bg_color'] ?? '';
$accentColor = $ts['accent_color'] ?? '';
?>
<link rel="stylesheet" href="<?php echo $themeFile; ?>">
<?php if ($primaryColor || $bgColor || $accentColor): ?>
<style>:root{<?php if ($primaryColor): ?>--accent:<?php echo $primaryColor; ?>;--accent-hover:<?php echo $primaryColor; ?>;<?php endif; ?><?php if ($bgColor): ?>--bg-primary:<?php echo $bgColor; ?>;<?php endif; ?><?php if ($accentColor): ?>--accent-hover:<?php echo $accentColor; ?>;<?php endif; ?>}</style>
<?php endif; ?>
<?php if ($customCss): ?><style><?php echo $customCss; ?></style><?php endif; ?>
<style>
.sidebar-toggle{position:fixed;top:10px;left:10px;z-index:999;background:linear-gradient(135deg,#008cff,#3bb8ff);border:none;color:#fff;width:40px;height:40px;border-radius:8px;cursor:pointer;font-size:20px;display:none;align-items:center;justify-content:center;box-shadow:0 0 15px rgba(0,140,255,.3)}
.sidebar-toggle:hover{transform:scale(1.05)}
@media(max-width:900px){
.sidebar-toggle{display:flex}
.admin-shell{grid-template-columns:1fr}
.sidebar{position:fixed;left:0;top:0;height:100vh;z-index:998;transform:translateX(0);transition:transform .3s}
.sidebar.closed{transform:translateX(-105%)}
}
.nav-section a.active{background:rgba(0,191,255,.15);color:#00bfff;border-left:3px solid #008cff}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="grid-overlay"></div>

<?php
$licenseStatus = null; $licenseDaysLeft = 0;
if (class_exists('\\Core\\License')) {
    $lic = new \Core\License(BASE_PATH);
    $lc = $lic->verify();
    $licenseStatus = $lc['valid'] ? 'valid' : (($lc['trial']??false) && ($lc['trial_days_left']??0)>0 ? 'trial' : 'locked');
    $licenseDaysLeft = $lc['trial_days_left'] ?? 0;
}
?>
<?php if ($licenseStatus === 'trial'): ?>
<div style="background:linear-gradient(90deg,#facc15,#f59e0b);color:#000;text-align:center;padding:8px 16px;font-size:13px;font-weight:600;position:sticky;top:0;z-index:9999">
⚠ TRIAL MODE — <?php echo $licenseDaysLeft; ?> day<?php echo $licenseDaysLeft > 1 ? 's' : ''; ?> remaining. <a href="/admin/licensing" style="color:#000;text-decoration:underline;font-weight:700">Activate License</a>
</div>
<?php elseif ($licenseStatus === 'locked'): ?>
<div style="background:linear-gradient(90deg,#ef4444,#dc2626);color:#fff;text-align:center;padding:8px 16px;font-size:13px;font-weight:600;position:sticky;top:0;z-index:9999">
✗ LICENSE REQUIRED — <a href="/admin/licensing" style="color:#fff;text-decoration:underline;font-weight:700">Enter License Key</a>
</div>
<?php endif; ?>
<button class="sidebar-toggle" id="sidebarToggle" onclick="document.getElementById('adminSidebar').classList.toggle('closed')">☰</button>
<div class="admin-shell">
<div class="sidebar" id="adminSidebar">
<div class="logo-text">PLANET <span>HOSTS</span></div>

<input id="sidebarSearch" type="text" placeholder="🔍 Search menu..." oninput="filterSidebar(this.value)" style="width:100%;padding:8px 10px;margin-bottom:12px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;outline:none;font-size:12px;box-sizing:border-box">

<script>
function filterSidebar(val) {
    var items = document.querySelectorAll('.sidebar a');
    var sections = document.querySelectorAll('.nav-section');
    var q = val.toLowerCase().trim();
    sections.forEach(function(s) {
        var count = 0;
        s.querySelectorAll('a').forEach(function(a) {
            var text = a.textContent.toLowerCase();
            var match = !q || text.indexOf(q) !== -1;
            a.style.display = match ? '' : 'none';
            if (match) count++;
        });
        s.style.display = count > 0 ? '' : 'none';
    });
}
</script>

<div class="nav-section" data-section="main">
<div class="nav-label">Main</div>
<a href="/admin/dashboard" class="<?php echo str_contains($currentUrl,'/admin/dashboard')?'active':''; ?>">Dashboard</a>
<a href="/admin/server" class="<?php echo str_contains($currentUrl,'/admin/server')?'active':''; ?>">Server Overview</a>
<a href="/admin/server/health" class="<?php echo str_contains($currentUrl,'/admin/server/health')?'active':''; ?>">Server Health</a>
</div>

<div class="nav-section" data-section="accounts">
<div class="nav-label">Accounts</div>
<a href="/admin/account" class="<?php echo str_contains($currentUrl,'/admin/account')?'active':''; ?>">Account Functions</a>
<a href="/admin/packages" class="<?php echo str_contains($currentUrl,'/admin/packages')?'active':''; ?>">Packages</a>
<a href="/admin/reseller" class="<?php echo str_contains($currentUrl,'/admin/reseller')?'active':''; ?>">Resellers</a>
<a href="/admin/userfeatures" class="<?php echo str_contains($currentUrl,'/admin/userfeatures')?'active':''; ?>">Feature Manager</a>
</div>

<div class="nav-section" data-section="services">
<div class="nav-label">Services</div>
<a href="/admin/dns" class="<?php echo str_contains($currentUrl,'/admin/dns')?'active':''; ?>">DNS Zones</a>
<a href="/admin/email" class="<?php echo str_contains($currentUrl,'/admin/email')?'active':''; ?>">Email</a>
<a href="https://planet-hosts.com:2097/" target="_blank" style="font-size:12px;padding-left:20px;color:#fb923c" class="<?php echo 0?'active':''; ?>">📧 Webmail (2097)</a>
<a href="/admin/mysql" class="<?php echo str_contains($currentUrl,'/admin/mysql')?'active':''; ?>">Databases</a>
<a href="/admin/ftp" class="<?php echo str_contains($currentUrl,'/admin/ftp')?'active':''; ?>">FTP</a>
<a href="/admin/ip" class="<?php echo str_contains($currentUrl,'/admin/ip')?'active':''; ?>">IP Management</a>
<a href="/admin/installers" class="<?php echo str_contains($currentUrl,'/admin/installers')?'active':''; ?>">One-Click Installer</a>
</div>

<div class="nav-section" data-section="security">
<div class="nav-label">Security</div>
<a href="/admin/security" class="<?php echo str_contains($currentUrl,'/admin/security')?'active':''; ?>">Security Center</a>
</div>

<div class="nav-section" data-section="support">
<div class="nav-label">Support</div>
<a href="/admin/support" class="<?php echo str_contains($currentUrl,'/admin/support') && !str_contains($currentUrl,'/admin/support/tickets') && !str_contains($currentUrl,'/admin/support/kb') && !str_contains($currentUrl,'/admin/support/announcements') && !str_contains($currentUrl,'/admin/support/status') && !str_contains($currentUrl,'/admin/livechat') && !str_contains($currentUrl,'/admin/reviews')?'active':''; ?>">Support Center</a>
<a href="/admin/livechat" class="<?php echo str_contains($currentUrl,'/admin/livechat')?'active':''; ?>">Live Chat</a>
<a href="/admin/reviews" class="<?php echo str_contains($currentUrl,'/admin/reviews')?'active':''; ?>" style="font-size:12px;padding-left:20px;color:#facc15">📝 Reviews</a>
</div>

<div class="nav-section" data-section="chat">
<div class="nav-label">💬 Chat</div>
<a href="/admin/livechat" class="<?php echo str_contains($currentUrl,'/admin/livechat')?'active':''; ?>">Live Chat</a>
<a href="/admin/chat-dashboard" class="<?php echo str_contains($currentUrl,'/admin/chat-dashboard')?'active':''; ?>">Chat Dashboard</a>
<a href="/chatbox/admin.php" target="_blank" style="color:#38bdf8">Chat Admin</a>
</div>

<div class="nav-section" data-section="dashboards">
<div class="nav-label">📊 Dashboards</div>
<a href="/admin/radio_dashboard" class="<?php echo str_contains($currentUrl,'/admin/radio_dashboard')?'active':''; ?>">📡 Radio Dashboard</a>
<a href="/admin/streams" class="<?php echo str_contains($currentUrl,'/admin/streams')?'active':''; ?>">🎵 Streams</a>
<a href="/admin/djs" class="<?php echo str_contains($currentUrl,'/admin/djs')?'active':''; ?>">🎤 DJ Accounts</a>
<a href="/admin/dj/ports" class="<?php echo str_contains($currentUrl,'/admin/dj/ports')?'active':''; ?>">🔌 DJ Ports</a>
<a href="/admin/dj/connections" class="<?php echo str_contains($currentUrl,'/admin/dj/connections')?'active':''; ?>">📋 DJ History</a>
<a href="/admin/autodj" class="<?php echo str_contains($currentUrl,'/admin/autodj')?'active':''; ?>">🤖 AutoDJ</a>
<a href="/admin/radiosettings" class="<?php echo str_contains($currentUrl,'/admin/radiosettings')?'active':''; ?>">⚙️ Radio Settings</a>
<a href="/admin/games" class="<?php echo str_contains($currentUrl,'/admin/games')?'active':''; ?>">🎮 Game Servers</a>
<a href="/admin/djs" style="font-size:12px;padding-left:20px;color:#a78bfa">🎤 DJ Dashboard</a>
<?php if (class_exists('\\Plugins\\WebsiteBuilder\\WebsiteBuilderPlugin')): ?>
<a href="/admin/websitebuilder" style="font-size:12px;padding-left:20px;color:#34d399">🌐 Website Builder</a>
<?php endif; ?>
</div>



<div class="nav-section" data-section="system">
<div class="nav-label">System</div>
<a href="/admin/gateways" class="<?php echo str_contains($currentUrl,'/admin/gateways')?'active':''; ?>">💳 Payment Gateways</a>
<a href="/admin/backup" class="<?php echo str_contains($currentUrl,'/admin/backup')?'active':''; ?>">Backups</a>
<a href="/admin/apache" class="<?php echo str_contains($currentUrl,'/admin/apache')?'active':''; ?>">Apache</a>
<a href="/admin/php" class="<?php echo str_contains($currentUrl,'/admin/php')?'active':''; ?>">PHP Manager</a>
<a href="/admin/php-switcher" style="font-size:12px;padding-left:20px;color:#34d399">🔄 PHP Version</a>
<a href="/admin/process-manager" style="font-size:12px;padding-left:20px;color:#f87171">🖥 Process Manager</a>
<a href="/admin/plugins" class="<?php echo str_contains($currentUrl,'/admin/plugins')?'active':''; ?>">Plugins</a>

<a href="/admin/cron" class="<?php echo str_contains($currentUrl,'/admin/cron')?'active':''; ?>">Cron</a>
<a href="/admin/automation" class="<?php echo str_contains($currentUrl,'/admin/automation')?'active':''; ?>">Automation</a>
<a href="/admin/server/terminal" class="<?php echo str_contains($currentUrl,'/admin/server/terminal') || str_contains($currentUrl,'/admin/terminal') ?'active':''; ?>" style="color:#34d399">🖥 Terminal</a>
<a href="/admin/serverconfig" class="<?php echo str_contains($currentUrl,'/admin/serverconfig')?'active':''; ?>">Server Config</a>
<a href="/admin/theme" class="<?php echo str_contains($currentUrl,'/admin/theme')?'active':''; ?>">Theme</a>
<a href="/admin/settings" class="<?php echo str_contains($currentUrl,'/admin/settings')?'active':''; ?>">Settings</a>
<a href="/admin/licensing" class="<?php echo str_contains($currentUrl,'/admin/licensing') && !str_contains($currentUrl,'/generate')?'active':''; ?>">Licensing</a>
<a href="/admin/licensing/generate" class="<?php echo str_contains($currentUrl,'/admin/licensing/generate')?'active':''; ?>">Generate License</a>
<a href="/admin/todo" class="<?php echo str_contains($currentUrl,'/admin/todo')?'active':''; ?>">ToDo List</a>
<a href="/admin/admins" class="<?php echo str_contains($currentUrl,'/admin/admins')?'active':''; ?>" style="color:#facc15">👤 Admins</a>
</div>

<div class="nav-section" data-section="api">
<div class="nav-label">API</div>
<a href="/admin/api" class="<?php echo str_contains($currentUrl,'/admin/api') && !str_contains($currentUrl,'/admin/api/')?'active':''; ?>">API Keys</a>
<a href="/admin/api/permissions" class="<?php echo str_contains($currentUrl,'/admin/api/permissions')?'active':''; ?>">Permissions</a>
<a href="/admin/api/webhooks" class="<?php echo str_contains($currentUrl,'/admin/api/webhooks')?'active':''; ?>">Webhooks</a>
<a href="/admin/api/docs" class="<?php echo str_contains($currentUrl,'/admin/api/docs')?'active':''; ?>">API Docs</a>
<a href="/admin/api/rate-limits" class="<?php echo str_contains($currentUrl,'/admin/api/rate-limits')?'active':''; ?>">Rate Limits</a>
<a href="/admin/paypal/settings" class="<?php echo str_contains($currentUrl,'/admin/paypal')?'active':''; ?>">PayPal</a>
</div>

<div class="nav-section" data-section="logout" style="margin-top:24px;border-top:1px solid rgba(255,255,255,.06);padding-top:16px">
<a href="/admin/logout" style="color:#ff6b6b">Logout</a>
</div>
</div>

<div class="main">
<div class="topbar">
<h1><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
<?php if ($user): ?>
<div style="display:flex;align-items:center;gap:10px;color:var(--text-secondary);font-size:14px">
<img src="/theme/assets/img/logo.png" style="width:32px;height:32px;border-radius:50%">
<?php echo htmlspecialchars($user->name ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
</div>
<div class="content">
<div id="chatAlert" class="alert alert-info" style="display:none;cursor:pointer;background:rgba(56,189,248,.1);color:#38bdf8;border:1px solid rgba(56,189,248,.15);border-radius:8px;font-size:13px;padding:12px 16px;margin-bottom:16px" onclick="window.location.href='/admin/livechat'"><i class="bi bi-chat-dots me-1"></i> <strong>New Chat Messages</strong> &mdash; <span id="chatAlertCount">0</span> waiting. <u>Click to view &rarr;</u></div>
<?php echo $content; ?>
</div>
<script>
(function(){var a=document.getElementById('chatAlert');if(!a)return;var c=document.getElementById('chatAlertCount');function p(){var x=new XMLHttpRequest();x.open('GET','/admin/livechat/waiting-count',true);x.onload=function(){try{var d=JSON.parse(x.responseText);if(d.waiting>0){c.textContent=d.waiting;a.style.display='';}else{a.style.display='none';}}catch(e){}};x.send();}p();setInterval(p,15000);})();
</script>
</div>
</div>

<footer class="footer">
<div class="container">
<div class="footer-logo" style="background:url('<?php echo $footerLogo; ?>') center/contain no-repeat;width:48px;height:48px"></div>
<p><?php echo htmlspecialchars($footerText); ?></p>
<div class="footer-links">
<a href="#">Terms</a><a href="#">Privacy</a><a href="#">Support</a><a href="#">API</a>
</div>
<div class="copyright">&copy; 2026 Planet-Hosts. All rights reserved.</div>
</div>
</footer>

<!-- Live Visitor Panel - appears on all admin pages -->
<style>
.visitor-panel{position:fixed;top:0;right:0;z-index:9999;width:280px;height:100vh;background:rgba(8,16,28,.98);border-left:1px solid rgba(0,191,255,.12);transform:translateX(100%);transition:transform .3s;overflow-y:auto;box-shadow:-5px 0 30px rgba(0,0,0,.5)}
.visitor-panel.open{transform:translateX(0)}
.visitor-panel .head{padding:16px 18px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;justify-content:space-between;align-items:center}
.visitor-panel .head h3{margin:0;font-size:15px;color:#fff}
.visitor-panel .head span{cursor:pointer;color:#64748b;font-size:18px}
.support-status{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:4px;font-size:11px;font-weight:600;cursor:pointer;border:none;margin:0 4px}
.support-status.online{background:rgba(74,222,128,.15);color:#4ade80}
.support-status.away{background:rgba(250,204,21,.12);color:#facc15}
.support-status.offline{background:rgba(239,68,68,.12);color:#ef4444}
.support-status:hover{opacity:.8}
.visitor-item{padding:12px 16px;border-bottom:1px solid rgba(255,255,255,.04);cursor:pointer;transition:.15s}
.visitor-item:hover{background:rgba(0,191,255,.04)}
.visitor-item .vtop{display:flex;align-items:center;gap:10px}
.visitor-item .vdot{width:8px;height:8px;border-radius:50%;background:#4ade80;flex-shrink:0}
.visitor-item .vname{font-weight:600;font-size:13px;color:#fff}
.visitor-item .vpage{font-size:11px;color:#64748b;margin:2px 0;padding-left:18px}
.visitor-item .vinfo{font-size:10px;color:#475569;padding-left:18px;display:flex;gap:8px}
.visitor-item .vactions{margin-top:6px;padding-left:18px;display:flex;gap:6px}
.visitor-item .vactions a{padding:3px 10px;border-radius:4px;font-size:10px;text-decoration:none;font-weight:600}
.visitor-toggle{position:fixed;top:10px;right:16px;z-index:9998;background:rgba(0,140,255,.15);border:1px solid rgba(0,140,255,.2);border-radius:20px;padding:4px 14px;font-size:11px;color:#008cff;cursor:pointer;display:none}
.visitor-toggle:hover{background:rgba(0,140,255,.25)}
</style>
<div class="visitor-toggle" id="visitorToggle" onclick="toggleVisitorPanel()">👥 <span id="vCount">0</span></div>
<button id="supportStatusBtn" class="support-status online" onclick="var s=prompt('Set status: online, away, offline','online');if(s&&['online','away','offline'].includes(s.toLowerCase()))setSupportStatus(s.toLowerCase())">Online</button>
<div class="visitor-panel" id="visitorPanel">
<div class="head"><h3>👤 Live Visitors</h3><span onclick="toggleVisitorPanel()">✕</span></div>
<div id="visitorList"></div>
</div>
<script>
// Version check
fetch('/api/version').then(function(r){return r.json()}).then(function(d){
    if (d.update && d.update.update_available) {
        var banner = document.createElement('div');
        banner.style.cssText = 'background:linear-gradient(90deg,#008cff,#3bb8ff);color:#fff;text-align:center;padding:10px 16px;font-size:14px;font-weight:600;position:sticky;top:0;z-index:99999';
        banner.innerHTML = '🔄 Update available: <strong>' + d.update.new_version + '</strong> (v' + d.update.new_version_code + ') — <a href="' + d.update.download_url + '" target="_blank" style="color:#fff;text-decoration:underline">Download</a> | <a href="#" onclick="this.parentElement.remove()" style="color:rgba(255,255,255,.7);text-decoration:none">✕ Dismiss</a>';
        document.body.insertBefore(banner, document.body.firstChild);
    }
}).catch(function(){});
var csrfToken = <?php echo json_encode($_SESSION['_csrf_token'] ?? ''); ?>;
// Auto-add CSRF token to all forms
document.addEventListener('DOMContentLoaded', function() {
    if (!csrfToken) return;
    document.querySelectorAll('form[method="POST"]').forEach(function(f) {
        if (!f.querySelector('input[name="_csrf_token"]')) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = '_csrf_token'; inp.value = csrfToken;
            f.appendChild(inp);
        }
    });
});
var knownVisitors = {};
var visitorToggle = document.getElementById('visitorToggle');
var visitorList = document.getElementById('visitorList');
var vCount = document.getElementById('vCount');

function toggleVisitorPanel() {
    document.getElementById('visitorPanel').classList.toggle('open');
}

function pollVisitors() {
    var x = new XMLHttpRequest();
    x.open('GET', '/admin/livechat/visitors/online', true);
    x.onload = function() {
        try {
            var visitors = JSON.parse(x.responseText);
            if (visitors && visitors.length > 0) {
                visitorToggle.style.display = 'inline-block';
                vCount.textContent = visitors.length;
                // Check for NEW visitors (by session_id, not id)
                var firstNew = true;
                visitors.forEach(function(v) {
                    var key = v.session_id || v.id;
                    if (!knownVisitors[key]) {
                        knownVisitors[key] = true;
                        if (firstNew) { document.getElementById('visitorPanel').classList.add('open'); firstNew = false; }
                        showVisitorToast(v);
                    }
                });
                // Render visitor list (deduplicate by session_id)
                var seen = {};
                var html = '';
                visitors.forEach(function(v) {
                    var key = v.session_id || v.id;
                    if (seen[key]) return;
                    seen[key] = true;
                    var name = v.name || 'Anonymous';
                    var page = v.current_page || 'Homepage';
                    var browser = v.browser || '';
                    var os = v.os || '';
                    var ip = v.ip_address || '';
                    var tz = v.timezone || '';
                    var time = v.time_on_site || 0;
                    var timeStr = time > 60 ? Math.floor(time/60) + 'm' : time + 's';
                    html += '<div class="visitor-item" onclick="openVisitorChat(\'' + key + '\')">' +
                        '<div class="vtop"><div class="vdot"></div><div class="vname">' + escapeHtml(name) + '</div></div>' +
                        '<div class="vpage">📍 ' + escapeHtml(page) + '</div>' +
                        '<div class="vinfo">🖥 ' + escapeHtml(browser) + ' · ' + escapeHtml(os) + ' · ' + timeStr + '</div>' +
                        '<div class="vinfo">🌐 ' + escapeHtml(ip) + ' · ' + escapeHtml(tz) + '</div>' +
                        '<div class="vactions">' +
                        '<a href="/admin/livechat" style="background:rgba(74,222,128,.15);color:#4ade80">💬 Chat</a>' +
                        '<a href="/remote_support.php" style="background:rgba(0,140,255,.15);color:#008cff">🖥 Remote</a>' +
                        '</div></div>';
                });
                visitorList.innerHTML = html;
            } else {
                visitorToggle.style.display = 'none';
                visitorList.innerHTML = '<div style="text-align:center;padding:40px;color:#64748b;font-size:13px">No visitors online</div>';
            }
        } catch(e) {}
    };
    x.send();
}

function showVisitorToast(v) {
    var name = v.name || 'Anonymous';
    var page = v.current_page || 'Unknown';
    var ip = v.ip_address || '';
    var browser = v.browser || '';
    var os = v.os || '';
    var tz = v.timezone || '';
    var toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;background:rgba(8,16,28,.98);border:1px solid rgba(0,191,255,.15);border-radius:14px;padding:18px 22px;max-width:400px;box-shadow:0 10px 50px rgba(0,0,0,.6);animation:slideUp .3s ease';
    toast.innerHTML = '<div style="display:flex;align-items:center;gap:12px;margin-bottom:8px"><img src="/theme/assets/img/avatars/vistor.png" style="width:38px;height:38px;border-radius:50%"><div><strong style="font-size:15px;color:#fff">' + escapeHtml(name) + '</strong><br><span style="font-size:12px;color:#64748b"><strong style="color:#4ade80">●</strong> Visitor on site</span></div></div>' +
        '<div style="font-size:12px;color:#64748b;margin-bottom:6px">📍 ' + escapeHtml(page) + '</div>' +
        '<div style="font-size:11px;color:#475569;margin-bottom:8px;display:flex;gap:12px;flex-wrap:wrap">' +
        '<span>🖥 ' + escapeHtml(browser) + ' · ' + escapeHtml(os) + '</span>' +
        '<span>🌐 ' + escapeHtml(ip) + '</span>' +
        '<span>🕐 ' + escapeHtml(tz) + '</span></div>' +
        '<div style="display:flex;gap:8px">' +
        '<a href="/admin/livechat" style="padding:6px 14px;border-radius:6px;font-size:12px;background:rgba(74,222,128,.15);color:#4ade80;text-decoration:none;font-weight:600">💬 Message</a>' +
        '<a href="#" onclick="this.parentElement.parentElement.remove()" style="padding:6px 14px;border-radius:6px;font-size:12px;background:rgba(100,116,139,.15);color:#64748b;text-decoration:none">Dismiss</a>' +
        '</div>';
    document.body.appendChild(toast);
    setTimeout(function() { if (toast.parentNode) toast.remove(); }, 12000);
}

function openVisitorChat(sessionId) {
    window.open('/admin/livechat', '_blank');
}

function openVisitorChat(sessionId) {
    window.open('/admin/livechat?action=panel&tab=chats&view=new&visitor=' + encodeURIComponent(sessionId), '_blank');
}

function escapeHtml(t) { return (t||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

setInterval(pollVisitors, 5000);
setTimeout(pollVisitors, 500);

// Support Status
function setSupportStatus(s) {
    var x = new XMLHttpRequest();
    x.open('POST', '/admin/support-status', true);
    x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    x.onload = function() {
        var btn = document.getElementById('supportStatusBtn');
        if (btn) { btn.textContent = s.charAt(0).toUpperCase() + s.slice(1); btn.className = 'support-status ' + s; }
    };
    x.send('status=' + encodeURIComponent(s));
}
// Load current status
(function() {
    var x = new XMLHttpRequest();
    x.open('GET', '/admin/support-status', true);
    x.onload = function() {
        try {
            var d = JSON.parse(x.responseText);
            var btn = document.getElementById('supportStatusBtn');
            if (btn) { btn.textContent = d.status.charAt(0).toUpperCase() + d.status.slice(1); btn.className = 'support-status ' + d.status; }
        } catch(e) {}
    };
    x.send();
})();
</script>
</body>
</body>
</html>

