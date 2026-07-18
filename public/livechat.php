<?php
// Live Chat Widget - Embeddable JavaScript widget for client sites
header('Content-Type: application/javascript');
header('Access-Control-Allow-Origin: *');
$settings = [];
$dbFile = __DIR__ . '/../config/database.php';
if (is_file($dbFile)) {
    try {
        $config = require $dbFile;
        $pdo = new PDO("mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password']);
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM automation_settings WHERE setting_key IN ('live_chat_enabled','company_name')");
        while ($r = $stmt->fetch(PDO::FETCH_OBJ)) $settings[$r->setting_key] = $r->setting_value;
    } catch (\Exception $e) {}
}
$enabled = ($settings['live_chat_enabled'] ?? '1') === '1';
$company = htmlspecialchars($settings['company_name'] ?? 'Planet Hosts', ENT_QUOTES);
?>
(function() {
    <?php if ($enabled): ?>
    var css = document.createElement('link');
    css.rel = 'stylesheet';
    css.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
    document.head.appendChild(css);
    var btn = document.createElement('div');
    btn.id = 'ph-chat-btn';
    btn.innerHTML = '<i class="fas fa-comment"></i>';
    btn.style.cssText = 'position:fixed;bottom:20px;right:20px;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;cursor:pointer;box-shadow:0 4px 20px rgba(0,140,255,.3);z-index:999999;display:flex;align-items:center;justify-content:center;font-size:24px;transition:transform .2s';
    btn.onmouseover = function(){this.style.transform='scale(1.1)'};
    btn.onmouseout = function(){this.style.transform='scale(1)'};
    btn.onclick = function(){
        var w = window.open('https://planet-hosts.com/livechat.php?popup=1', 'ph_chat', 'width=400,height=600,scrollbars=yes');
    };
    document.body.appendChild(btn);
    <?php else: ?>
    console.log('Live chat disabled.');
    <?php endif; ?>
})();
