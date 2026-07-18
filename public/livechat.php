<?php
header('Content-Type: application/javascript');
header('Access-Control-Allow-Origin: *');
$settings = [];
try {
    $config = require __DIR__ . '/../config/database.php';
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password']);
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM automation_settings WHERE setting_key IN ('live_chat_enabled','chat_image_online','chat_image_offline','chat_image_away')");
    while ($r = $stmt->fetch(PDO::FETCH_OBJ)) $settings[$r->setting_key] = $r->setting_value;
} catch (\Exception $e) {}
$enabled = ($settings['live_chat_enabled'] ?? '1') === '1';
?>
(function() {
    <?php if (!$enabled) { echo 'console.log("Live chat disabled."); return;'; } ?>
    var imgs = {
        online: '<?php echo $settings['chat_image_online'] ?? ''; ?>',
        offline: '<?php echo $settings['chat_image_offline'] ?? ''; ?>',
        away: '<?php echo $settings['chat_image_away'] ?? ''; ?>'
    };
    window.openLiveChat = function() {
        window.open('https://planet-hosts.com/livechat_popup.php', 'ph_chat', 'width=400,height=600,scrollbars=yes');
    };
    var links = document.querySelectorAll('a[href*="livechat"], a[href*="Live Chat"]');
    links.forEach(function(l) { l.onclick = function(e) { e.preventDefault(); window.openLiveChat(); }; });
    console.log('Live chat loaded.');
})();
