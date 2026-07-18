<?php
header('Content-Type: text/html; charset=utf-8');
$settings = [];
try {
    $config = require __DIR__ . '/../config/database.php';
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password']);
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM automation_settings WHERE setting_key LIKE 'chat_image_%'");
    while ($r = $stmt->fetch(PDO::FETCH_OBJ)) $settings[$r->setting_key] = $r->setting_value;
} catch (\Exception $e) {}
$online = $settings['chat_image_online'] ?? '';
$offline = $settings['chat_image_offline'] ?? '';
$away = $settings['chat_image_away'] ?? '';
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Live Chat</title>
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Inter,sans-serif;background:#0a0e1a;color:#e0e0e0;display:flex;flex-direction:column;height:100vh}
.header{background:linear-gradient(135deg,#008cff,#3bb8ff);padding:14px;text-align:center;color:#fff;font-weight:600;font-size:14px}
.chat{flex:1;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:8px}
.msg{max-width:80%;padding:8px 12px;border-radius:8px;font-size:12px;line-height:1.5}
.msg.support{background:rgba(0,140,255,.15);align-self:flex-start;color:#e0e0e0}
.msg.user{background:rgba(0,200,83,.15);align-self:flex-end;color:#e0e0e0}
.input-bar{display:flex;gap:6px;padding:10px;border-top:1px solid rgba(255,255,255,.06)}
.input-bar input{flex:1;padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;outline:none;font-size:12px}
.input-bar button{padding:8px 14px;border-radius:6px;border:none;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;cursor:pointer;font-size:12px}
.status-img{width:16px;height:16px;vertical-align:middle;margin-right:4px}
</style></head>
<body>
<div class="header"><?php if ($online): ?><img src="/<?php echo htmlspecialchars($online); ?>" class="status-img"><?php endif; ?> Planet Hosts Support</div>
<div class="chat" id="chatBox">
<div class="msg support">Hello! How can we help you today?</div>
</div>
<form class="input-bar" onsubmit="sendMsg(this);return false">
<input type="text" id="chatInput" placeholder="Type your message..." autofocus>
<button type="submit">Send</button>
</form>
<script>
function sendMsg(f) {
    var input = f.querySelector('input');
    var msg = input.value.trim();
    if (!msg) return;
    var box = document.getElementById('chatBox');
    var d = document.createElement('div');
    d.className = 'msg user';
    d.textContent = msg;
    box.appendChild(d);
    input.value = '';
    box.scrollTop = box.scrollHeight;
    setTimeout(function() {
        var r = document.createElement('div');
        r.className = 'msg support';
        r.textContent = 'Thank you for your message. A support agent will respond shortly.';
        box.appendChild(r);
        box.scrollTop = box.scrollHeight;
    }, 1000);
}
</script>
</body>
</html>
