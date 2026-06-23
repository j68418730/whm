<?php
session_start();
$email = $_GET['email'] ?? '';
if (!$email) { header('Location: /webmail_autologin.php'); exit; }
$user = $_SESSION['user'] ?? null;
if (!$user) { header('Location: /?login'); exit; }

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Get the password from mail_accounts
$stmt = $pdo->prepare("SELECT password_plain FROM mail_accounts WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$row = $stmt->fetch(PDO::FETCH_OBJ);
$password = $row ? $row->password_plain : '';

// Get logo
$q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='company_logo'");
$lr = $q->fetch(PDO::FETCH_OBJ);
$logo = $lr ? $lr->setting_value : '/theme/assets/img/logo.png';
$lf = __DIR__ . '/../' . ltrim($logo, '/');
if (!file_exists($lf)) $logo = '/theme/assets/img/logo.png';
?><!DOCTYPE html>
<html><head><title>Opening Webmail...</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#02050e;color:#fff;font-family:'Inter',sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh}
.card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;text-align:center;max-width:360px;width:92%}
.card img{max-height:48px;margin-bottom:14px;border-radius:8px}
.card p{color:#64748b;font-size:13px;margin-bottom:16px}
.sp{width:32px;height:32px;border:3px solid rgba(0,191,255,.1);border-top-color:#0A84FF;border-radius:50%;animation:spin .8s linear infinite;margin:0 auto}
@keyframes spin{to{transform:rotate(360deg)}}
</style></head>
<body>
<div class="card">
<img src="<?php echo htmlspecialchars($logo); ?>" alt="" onerror="this.style.display='none'">
<p>Signing in to webmail...</p>
<div class="sp"></div>
</div>
<form id="f" method="POST" action="/roundcube/?_task=login&_action=login">
<input type="hidden" name="_user" value="<?php echo htmlspecialchars($email); ?>">
<input type="hidden" name="_pass" value="<?php echo htmlspecialchars($password); ?>">
<input type="hidden" name="_timezone" value="default">
</form>
<script>setTimeout(function(){document.getElementById('f').submit();},800);</script>
</body></html>
