<?php
$chatId = (int)($_GET['chat_id'] ?? 0);
$json = (int)($_GET['json'] ?? 0);
$verify = $_GET['verify'] ?? '';
$code = $_GET['code'] ?? '';
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// ─── OTP Verification ───
if ($verify && $code) {
    $otp = trim($_POST['otp'] ?? '');
    $stmt = $pdo->prepare("SELECT * FROM remote_sessions WHERE session_code = ? AND otp = ? AND status = 'pending' AND expires_at > NOW()");
    $stmt->execute([$code, $otp]);
    $session = $stmt->fetch(PDO::FETCH_OBJ);
    if ($session) {
        $pdo->prepare("UPDATE remote_sessions SET status = 'verified', verified_at = NOW() WHERE id = ?")->execute([$session->id]);
        $verified = true;
    } else {
        $error = 'Invalid or expired OTP.';
    }
    ?>
    <!DOCTYPE html><html><head><title>Remote Support - Planet Hosts</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"><link rel="stylesheet" href="/theme/assets/css/style.css">
    <style>body{display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#000;font-family:Inter,sans-serif}
    .bg{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.9),rgba(2,8,23,.97)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
    .card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;max-width:440px;width:90%;text-align:center;position:relative}
    h1{color:#fff;font-size:22px;margin:0 0 8px}h1 span{color:#008cff}
    .success{color:#4ade80;font-size:18px;margin:20px 0}
    .error{color:#f87171;font-size:14px;margin:12px 0}
    </style></head><body>
    <div class="bg"></div>
    <div class="card">
    <h1>PLANET-<span>HOSTS</span></h1>
    <?php if (isset($verified)): ?>
    <div class="success">✅ Remote session verified!</div>
    <p>A support agent will connect shortly.</p>
    <?php else: ?>
    <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
    <a href="/remote_support.php?verify=1&code=<?php echo urlencode($code); ?>" class="btn primary">Try Again</a>
    <?php endif; ?>
    <br><br><a href="/" class="btn secondary">Back to Home</a>
    </div></body></html>
    <?php
    exit;
}

// ─── OTP Entry Form ───
if ($verify) {
    ?>
    <!DOCTYPE html><html><head><title>Verify Remote Support - Planet Hosts</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"><link rel="stylesheet" href="/theme/assets/css/style.css">
    <style>body{display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#000;font-family:Inter,sans-serif}
    .bg{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.9),rgba(2,8,23,.97)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
    .card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;max-width:400px;width:90%;text-align:center}
    h1{color:#fff;font-size:22px;margin:0 0 8px}h1 span{color:#008cff}
    p{color:#64748b;font-size:13px;margin:0 0 16px}
    input{width:100%;max-width:200px;padding:12px 16px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff;font-size:24px;text-align:center;outline:none;letter-spacing:6px;font-weight:700}
    input:focus{border-color:#008cff;}
    </style></head><body>
    <div class="bg"></div>
    <div class="card">
    <h1>PLANET-<span>HOSTS</span></h1>
    <p>Enter the one-time password sent to your chat</p>
    <form method="POST">
    <input type="text" name="otp" maxlength="8" autofocus placeholder="000000" style="margin-bottom:16px">
    <button type="submit" class="btn primary" style="width:100%">Verify & Connect</button>
    </form>
    </div></body></html>
    <?php
    exit;
}

// ─── Generate Remote Session (called by admin) ───
$supportUrl = '';
$otp = '';
$sessionCode = bin2hex(random_bytes(8));
$otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Store in DB
$stmt = $pdo->prepare("INSERT INTO remote_sessions (session_code, otp, chat_session_id, status, created_at, expires_at) VALUES (?, ?, ?, 'pending', NOW(), DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
$stmt->execute([$sessionCode, $otp, $chatId ?: null]);

$supportUrl = "https://remote.planet-hosts.com/connect/{$sessionCode}";

// If chat_id provided, send link + OTP as chat message
if ($chatId) {
    $msg = "🖥 Remote Support Link: {$supportUrl}\n🔑 Your one-time password: {$otp}\nThis code expires in 15 minutes.";
    $pdo->prepare("INSERT INTO chat_messages (session_id, sender_type, sender_name, message, created_at) VALUES (?, 'operator', 'Support', ?, NOW())")
        ->execute([$chatId, $msg]);
    $sent = true;
}

if ($json) {
    header('Content-Type: application/json');
    echo json_encode(['url' => $supportUrl, 'otp' => $otp, 'session_code' => $sessionCode, 'sent' => !empty($sent)]);
    exit;
}
?>
<!DOCTYPE html><html><head><title>Remote Support - Planet Hosts</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>body{display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#000;font-family:Inter,sans-serif}
.bg{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.9),rgba(2,8,23,.97)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;max-width:500px;width:90%;text-align:center;position:relative}
h1{color:#fff;font-size:22px;margin:0 0 8px}h1 span{color:#008cff}
p{color:#64748b;font-size:14px;margin:0 0 20px}
.code-box{background:rgba(0,0,0,.4);border:1px solid rgba(0,191,255,.2);border-radius:8px;padding:16px;font-family:monospace;font-size:16px;color:#4ade80;margin:16px 0;word-break:break-all}
.otp-box{background:rgba(0,0,0,.4);border:1px solid rgba(250,204,21,.2);border-radius:8px;padding:14px;font-family:monospace;font-size:32px;color:#facc15;margin:8px 0;letter-spacing:8px;font-weight:700}
.btn{display:inline-block;padding:12px 24px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;text-decoration:none;margin:4px}
.btn-sec{background:#333;color:#ccc}
.success{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;padding:12px;color:#4ade80;margin-bottom:16px}
</style></head><body>
<div class="bg"></div>
<div class="card">
<h1>PLANET-<span>HOSTS</span></h1>
<p>Remote support session generated.</p>
<?php if (!empty($sent)): ?>
<div class="success">✅ Link + OTP sent directly to the chat!</div>
<?php endif; ?>
<div style="font-size:12px;color:#64748b;margin-bottom:4px">Session Link</div>
<div class="code-box"><?php echo htmlspecialchars($supportUrl); ?></div>
<div style="font-size:12px;color:#64748b;margin-bottom:4px">One-Time Password (expires in 15 min)</div>
<div class="otp-box"><?php echo htmlspecialchars($otp); ?></div>
<p style="font-size:12px;color:#94a3b8">Share this link and OTP with the visitor. The link without the OTP is useless.</p>
<button class="btn" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($supportUrl); ?>')">📋 Copy Link</button>
<button class="btn" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($otp); ?>')">📋 Copy OTP</button>
<br><br>
<a href="/admin/livechat" class="btn btn-sec">← Back to Live Chat</a>
<a href="/voice/admin.php" class="btn btn-sec">📞 Voice Call</a>
</div></body></html>
