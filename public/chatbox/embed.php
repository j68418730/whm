<?php
require_once __DIR__ . '/../security_guard.php';
security_guard_run('chat');
$tenantId = (int)($_GET['tenant_id'] ?? 0);
if (!$tenantId) { echo 'Invalid tenant'; exit; }
// Suspended owner => chat offline
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$st = $pdo->prepare("SELECT hu.status FROM chatbox_tenants t LEFT JOIN hosting_users hu ON hu.id = t.hosting_user_id WHERE t.id = ?");
$st->execute([$tenantId]);
$ownerStatus = $st->fetchColumn();
if ($ownerStatus === 'suspended') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Chat Offline</title></head><body style="margin:0;background:transparent;display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:Inter,system-ui,sans-serif"><div style="text-align:center;color:#64748b;padding:20px"><div style="font-size:40px;margin-bottom:8px">🚫</div><div style="font-size:14px;font-weight:600">Chat is offline</div><div style="font-size:12px;margin-top:4px">This chat has been suspended.</div></div></body></html>';
    exit;
}
$room = trim($_GET['room'] ?? '');
$token = trim($_GET['token'] ?? '');
$roomParam = $room !== '' ? '&room=' . urlencode($room) : '';
$tokenParam = $token !== '' ? '&token=' . urlencode($token) : '';
?>
<!DOCTYPE html><html><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Chat</title>
<script src="https://planet-hosts.com/chatbox/widget.js.php?tenant_id=<?php echo $tenantId . $roomParam . $tokenParam; ?>"></script>
<style>
body{margin:0;padding:0;background:transparent;overflow:hidden;width:100%;height:100%}
html,body{width:100%;height:100%}
#chatbox-widget{position:fixed!important;top:0!important;left:0!important;right:0!important;bottom:0!important;width:100vw!important;height:100vh!important;margin:0!important;padding:0!important;display:flex!important}
#chatbox-widget #chatbox-toggle{display:none!important}
#chatbox-panel{position:absolute!important;top:0!important;left:0!important;right:0!important;bottom:0!important;width:100%!important;height:100%!important;max-width:none!important;max-height:none!important;border-radius:0!important;margin:0!important}
#chatbox-panel.closed{display:flex!important}
</style>
</head><body>
<script>
// Auto-open in iframe mode — retry until widget loads
(function tryOpen(){
    var panel = document.getElementById('chatbox-panel');
    if (panel) { panel.classList.remove('closed'); panel.classList.add('open'); }
    else { setTimeout(tryOpen, 300); }
})();
</script>
</body></html>

