<?php
$email = $_GET['email'] ?? '';
if (!$email) { header('Location: /webmail_autologin.php'); exit; }

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$stmt = $pdo->prepare("SELECT password_plain FROM mail_accounts WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$row = $stmt->fetch(PDO::FETCH_OBJ);
$password = $row ? $row->password_plain : '';
if (!$password) { header('Location: /webmail_autologin.php?email=' . urlencode($email)); exit; }

$localPart = explode('@', $email)[0];
exec("id " . escapeshellarg($localPart) . " 2>/dev/null || (useradd -m -d /home/" . escapeshellarg($localPart) . " -s /sbin/nologin " . escapeshellarg($localPart) . " 2>/dev/null && echo " . escapeshellarg($localPart) . ":" . escapeshellarg($password) . " | chpasswd 2>/dev/null)");

while (ob_get_level()) ob_end_clean();

require_once '/var/www/radiohosting/public/snappymail/snappymail/v/2.38.2/include.php';

$_SERVER['SERVER_NAME'] = 'server.planet-hosts.com';
$_SERVER['HTTP_HOST'] = 'server.planet-hosts.com';

$oActions = RainLoop\Api::Actions();
$oActions->SetIsJson(false);

try {
    $oAccount = $oActions->LoginProcess(
        $email,
        new SnappyMail\SensitiveString($password)
    );
    while (ob_get_level()) ob_end_clean();
    header('Location: /snappymail/');
    exit;
} catch (\Throwable $e) {
    while (ob_get_level()) ob_end_clean();
}

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
<p style="color:#ef4444">Login failed: <?php echo htmlspecialchars($e->getMessage() ?? 'Unknown'); ?></p>
</div>
</body></html>
