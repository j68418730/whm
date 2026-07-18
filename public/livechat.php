<?php
header('Content-Type: application/javascript');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/../core/helpers.php';
$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}
$settings = [];
$config = [];
try {
    $config = require __DIR__ . '/../config/database.php';
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password']);
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM automation_settings WHERE setting_key LIKE 'chat_image_%' OR setting_key = 'live_chat_enabled'");
    while ($r = $stmt->fetch(PDO::FETCH_OBJ)) $settings[$r->setting_key] = $r->setting_value;
} catch (\Exception $e) {}
$enabled = ($settings['live_chat_enabled'] ?? '1') === '1';
$onlineImg = $settings['chat_image_online'] ?? '';
$offlineImg = $settings['chat_image_offline'] ?? '';
$awayImg = $settings['chat_image_away'] ?? '';
?>
(function() {
    <?php if (!$enabled) { echo 'console.log("Live chat disabled."); return;'; } ?>
    var imgs = {
        online: '<?php echo $onlineImg ? "/" . $onlineImg : ""; ?>',
        offline: '<?php echo $offlineImg ? "/" . $offlineImg : ""; ?>',
        away: '<?php echo $awayImg ? "/" . $awayImg : ""; ?>'
    };
    window.openLiveChat = function() {
        window.open('https://planet-hosts.com/livechat_popup.php', 'ph_chat', 'width=400,height=600,scrollbars=yes');
    };
    var statusHtml = '<div style="display:flex;gap:12px;align-items:center;justify-content:center;padding:6px 0;font-size:11px;color:#94a3b8">';
    if (imgs.online) statusHtml += '<span><img src="'+imgs.online+'" style="width:16px;height:16px;vertical-align:middle"> Online</span>';
    if (imgs.offline) statusHtml += '<span><img src="'+imgs.offline+'" style="width:16px;height:16px;vertical-align:middle"> Offline</span>';
    if (imgs.away) statusHtml += '<span><img src="'+imgs.away+'" style="width:16px;height:16px;vertical-align:middle"> Away</span>';
    statusHtml += '</div>';
    var links = document.querySelectorAll('a[href*="livechat"], a[href*="Live Chat"]');
    links.forEach(function(l) {
        l.innerHTML = '≡ƒÆ¼ Live Chat ' + statusHtml;
        l.onclick = function(e) { e.preventDefault(); window.openLiveChat(); };
    });
    console.log('Live chat loaded.');
})();
