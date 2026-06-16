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
<a href="/admin/mysql" class="<?php echo str_contains($currentUrl,'/admin/mysql')?'active':''; ?>">Databases</a>
<a href="/admin/ftp" class="<?php echo str_contains($currentUrl,'/admin/ftp')?'active':''; ?>">FTP</a>
</div>

<div class="nav-section" data-section="security">
<div class="nav-label">Security</div>
<a href="/admin/security" class="<?php echo str_contains($currentUrl,'/admin/security')?'active':''; ?>">Security Center</a>
</div>

<div class="nav-section" data-section="support">
<div class="nav-label">Support</div>
<a href="/admin/support" class="<?php echo str_contains($currentUrl,'/admin/support') && !str_contains($currentUrl,'/admin/support/tickets') && !str_contains($currentUrl,'/admin/support/kb') && !str_contains($currentUrl,'/admin/support/announcements') && !str_contains($currentUrl,'/admin/support/status') && !str_contains($currentUrl,'/admin/livechat')?'active':''; ?>">Support Center</a>
<a href="/admin/livechat" class="<?php echo str_contains($currentUrl,'/admin/livechat')?'active':''; ?>">Live Chat</a>
</div>

<div class="nav-section" data-section="billing">
<div class="nav-label">Billing</div>
<a href="/admin/billing" class="<?php echo str_contains($currentUrl,'/admin/billing') && !str_contains($currentUrl,'/admin/billing/')?'active':''; ?>">Billing Dashboard</a>
<a href="/admin/billing/products" class="<?php echo str_contains($currentUrl,'/admin/billing/products')?'active':''; ?>">Products</a>
<a href="/admin/billing/orders" class="<?php echo str_contains($currentUrl,'/admin/billing/orders')?'active':''; ?>">Orders</a>
<a href="/admin/billing/services" class="<?php echo str_contains($currentUrl,'/admin/billing/services')?'active':''; ?>">Services</a>
<a href="/admin/billing/invoices" class="<?php echo str_contains($currentUrl,'/admin/billing/invoices')?'active':''; ?>">Invoices</a>
</div>

<div class="nav-section" data-section="system">
<div class="nav-label">System</div>
<a href="/admin/backup" class="<?php echo str_contains($currentUrl,'/admin/backup')?'active':''; ?>">Backups</a>
<a href="/admin/apache" class="<?php echo str_contains($currentUrl,'/admin/apache')?'active':''; ?>">Apache</a>
<a href="/admin/php" class="<?php echo str_contains($currentUrl,'/admin/php')?'active':''; ?>">PHP Manager</a>
<a href="/admin/plugins" class="<?php echo str_contains($currentUrl,'/admin/plugins')?'active':''; ?>">Plugins</a>
<a href="/admin/cron" class="<?php echo str_contains($currentUrl,'/admin/cron')?'active':''; ?>">Cron</a>
<a href="/admin/automation" class="<?php echo str_contains($currentUrl,'/admin/automation')?'active':''; ?>">Automation</a>
<a href="/admin/serverconfig" class="<?php echo str_contains($currentUrl,'/admin/serverconfig')?'active':''; ?>">Server Config</a>
<a href="/admin/theme" class="<?php echo str_contains($currentUrl,'/admin/theme')?'active':''; ?>">Theme</a>
<a href="/admin/settings" class="<?php echo str_contains($currentUrl,'/admin/settings')?'active':''; ?>">Settings</a>
<a href="/admin/licensing" class="<?php echo str_contains($currentUrl,'/admin/licensing') && !str_contains($currentUrl,'/generate')?'active':''; ?>">Licensing</a>
<a href="/admin/licensing/generate" class="<?php echo str_contains($currentUrl,'/admin/licensing/generate')?'active':''; ?>">Generate License</a>
<a href="/admin/todo" class="<?php echo str_contains($currentUrl,'/admin/todo')?'active':''; ?>">ToDo List</a>
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
<?php echo $content; ?>
</div>
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

<script>
// Active menu tracking - highlight current page
(function() {
    var current = '<?php echo addslashes($currentUrl); ?>';
    document.querySelectorAll('.sidebar a').forEach(function(a) {
        var href = a.getAttribute('href');
        if (href && current.indexOf(href) === 0) {
            a.classList.add('active');
        }
    });
})();
</script>
</body>
</html>
