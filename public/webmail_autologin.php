<?php
session_start();
$user = $_SESSION['user'] ?? null;
$email = '';
$password = '';
if (is_object($user)) { $email = $user->email ?? ''; }
elseif (is_array($user)) { $email = $user['email'] ?? ''; }
if (!empty($_GET['email'])) $email = $_GET['email'];
if (!empty($_SESSION['webmail_email'])) $email = $_SESSION['webmail_email'];
if (!empty($_SESSION['webmail_password'])) $password = $_SESSION['webmail_password'];

// Get domain email accounts instead
if (!$email) {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $uid = is_object($user) ? ($user->id ?? 0) : ($user['id'] ?? 0);
    $uname = is_object($user) ? ($user->name ?? '') : ($user['name'] ?? '');
    // Find hosting user
    $hStmt = $pdo->prepare("SELECT id, domain FROM hosting_users WHERE id = ? OR username = ? LIMIT 1");
    $hStmt->execute([$uid, $uname]);
    $hosting = $hStmt->fetch(PDO::FETCH_OBJ);
    // If no match from session, try first hosting account
    if (!$hosting) {
        $hStmt2 = $pdo->query("SELECT id, domain FROM hosting_users ORDER BY id ASC LIMIT 1");
        $hosting = $hStmt2->fetch(PDO::FETCH_OBJ);
    }
    if ($hosting && $hosting->domain) {
        // Get the first email account for this domain
        $eStmt = $pdo->prepare("SELECT email FROM mail_accounts WHERE domain = ? LIMIT 1");
        $eStmt->execute([$hosting->domain]);
        $mailAcct = $eStmt->fetch(PDO::FETCH_OBJ);
        if ($mailAcct) $email = $mailAcct->email;
    }
}

// Try to get plain password for this email
if ($email) {
    try {
        $pwPdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
        $pwStmt = $pwPdo->prepare("SELECT password_plain FROM mail_accounts WHERE email = ? LIMIT 1");
        $pwStmt->execute([$email]);
        $pwRow = $pwStmt->fetch(PDO::FETCH_OBJ);
        if ($pwRow && $pwRow->password_plain) $password = $pwRow->password_plain;
    } catch (Exception $e) {}
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
<form id="rcForm" method="POST" action="/roundcube/">
<input type="hidden" name="_task" value="login">
<input type="hidden" name="_action" value="login">
<input type="hidden" name="_user" value="<?php echo htmlspecialchars($email); ?>">
<input type="hidden" name="_pass" value="<?php echo htmlspecialchars($password); ?>">
</form>
<script>
<?php if ($email && $password): ?>
setTimeout(function(){document.getElementById('rcForm').submit();}, 1200);
<?php elseif ($email): ?>
setTimeout(function(){window.location.href='/roundcube/?_user=' + encodeURIComponent('<?php echo htmlspecialchars($email); ?>');}, 1200);
<?php else: ?>
setTimeout(function(){window.location.href='/roundcube/';}, 1500);
<?php endif; ?>
</script>
</body></html>
