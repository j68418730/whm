<?php
session_start();
$email = $_GET['email'] ?? '';
if (!$email) { header('Location: /webmail_autologin.php'); exit; }

// Get password
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$stmt = $pdo->prepare("SELECT password_plain FROM mail_accounts WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$row = $stmt->fetch(PDO::FETCH_OBJ);
$password = $row ? $row->password_plain : '';
if (!$password) { die('No password stored.'); }

$localPart = explode('@', $email)[0];

// Ensure system user exists
exec("id " . escapeshellarg($localPart) . " 2>/dev/null || (useradd -m -d /home/" . escapeshellarg($localPart) . " -s /sbin/nologin " . escapeshellarg($localPart) . " 2>/dev/null && echo " . escapeshellarg($localPart) . ":" . escapeshellarg($password) . " | chpasswd 2>/dev/null)");

// Use cURL to login to Roundcube and capture session
$ckFile = '/tmp/rc_' . session_id() . '.txt';
@unlink($ckFile);

// Step 1: Get login page + CSRF token
$ch = curl_init('http://localhost/roundcube/');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_COOKIEJAR => $ckFile,
    CURLOPT_COOKIEFILE => $ckFile,
    CURLOPT_TIMEOUT => 10,
]);
$resp = curl_exec($ch);
curl_close($ch);
preg_match('/name="_token" value="([^"]+)"/', $resp, $m);
$token = $m[1] ?? '';

// Step 2: POST login
$ch2 = curl_init('http://localhost/roundcube/?_task=login&_action=login');
curl_setopt_array($ch2, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(['_token'=>$token,'_task'=>'login','_action'=>'login','_timezone'=>'_default_','_url'=>'','_user'=>$localPart,'_pass'=>$password]),
    CURLOPT_COOKIEJAR => $ckFile,
    CURLOPT_COOKIEFILE => $ckFile,
    CURLOPT_TIMEOUT => 10,
]);
curl_exec($ch2);
curl_close($ch2);

// Step 3: Read cookies from jar
$cookies = [];
if (is_file($ckFile)) {
    foreach (file($ckFile, FILE_IGNORE_NEW_LINES) as $line) {
        $line = trim($line);
        if ($line && !str_starts_with($line, '#')) {
            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 7) $cookies[] = ['name' => $parts[5], 'value' => $parts[6]];
        }
    }
}
@unlink($ckFile);

// Get logo
$q = $pdo->query("SELECT setting_value FROM automation_settings WHERE setting_key='company_logo'");
$lr = $q->fetch(PDO::FETCH_OBJ);
$logo = $lr ? $lr->setting_value : '/theme/assets/img/logo.png';
$lf = __DIR__ . '/../' . ltrim($logo, '/');
if (!file_exists($lf)) $logo = '/theme/assets/img/logo.png';

// Build cookie-setting JavaScript
$cookieJS = '';
foreach ($cookies as $c) {
    $cookieJS .= 'document.cookie="' . $c['name'] . '=' . $c['value'] . ';path=/roundcube/;max-age=86400";';
}
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
<img src="<?php echo htmlspecialchars($logo); ?>" alt="" onerror="this.style.display=\'none\'">
<p>Signing in to webmail...</p>
<div class="sp"></div>
</div>
<script>
<?php echo $cookieJS; ?>
setTimeout(function(){window.location.href='/roundcube/';}, 500);
</script>
</body></html>
