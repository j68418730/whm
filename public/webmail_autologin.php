<?php
session_start();
$user = $_SESSION['user'] ?? null;
$email = '';
$password = '';
if (is_object($user)) { $email = $user->email ?? ''; }
elseif (is_array($user)) { $email = $user['email'] ?? ''; }
if (!empty($_SESSION['webmail_email'])) $email = $_SESSION['webmail_email'];
if (!empty($_SESSION['webmail_password'])) $password = $_SESSION['webmail_password'];

// Try to get email from hosting account if available
if (!$email) {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $stmt = $pdo->prepare("SELECT email FROM hosting_users WHERE id = ? OR username = ? LIMIT 1");
    $uid = is_object($user) ? ($user->id ?? 0) : ($user['id'] ?? 0);
    $uname = is_object($user) ? ($user->name ?? '') : ($user['name'] ?? '');
    $stmt->execute([$uid, $uname]);
    $hosting = $stmt->fetch(PDO::FETCH_OBJ);
    if ($hosting && $hosting->email) $email = $hosting->email;
}

// Get logo
$pdo2 = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$q = $pdo2->query("SELECT setting_value FROM automation_settings WHERE setting_key='company_logo'");
$logoRow = $q->fetch(PDO::FETCH_OBJ);
$logo = $logoRow ? $logoRow->setting_value : '/theme/assets/img/logo.png';
$logoFile = __DIR__ . '/../' . ltrim($logo, '/');
if (!file_exists($logoFile)) $logo = '/theme/assets/img/logo.png';

?><!DOCTYPE html>
<html><head><title>Redirecting to Webmail...</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#02050e;color:#fff;font-family:'Inter',sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh}
.card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;text-align:center;max-width:360px;width:92%}
.card img{max-height:48px;margin-bottom:14px;border-radius:8px}
.card p{color:#64748b;font-size:13px;margin-bottom:16px}
.spinner{width:32px;height:32px;border:3px solid rgba(0,191,255,.1);border-top-color:#0A84FF;border-radius:50%;animation:spin .8s linear infinite;margin:0 auto}
@keyframes spin{to{transform:rotate(360deg)}}
</style></head>
<body>
<div class="card">
<img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo" onerror="this.style.display='none'">
<p>Connecting to webmail...</p>
<div class="spinner"></div>
</div>
<form id="rcForm" method="GET" action="/roundcube/">
<input type="hidden" name="_user" value="<?php echo htmlspecialchars($email); ?>">
</form>
<script>
<?php if ($email): ?>
setTimeout(function(){window.location.href='/roundcube/?_user=' + encodeURIComponent('<?php echo htmlspecialchars($email); ?>');}, 1200);
<?php else: ?>
setTimeout(function(){window.location.href='/roundcube/';}, 1500);
<?php endif; ?>
</script>
</body></html>
