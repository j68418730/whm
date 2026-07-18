<?php
header('Content-Type: application/javascript');
header('Access-Control-Allow-Origin: *');
$settings = [];
$config = [];
$dbFile = __DIR__ . '/../config/database.php';
if (is_file($dbFile)) {
    try {
        $config = require $dbFile;
        $pdo = new PDO("mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password']);
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM automation_settings WHERE setting_key IN ('visitor_tracking_enabled','company_name')");
        while ($r = $stmt->fetch(PDO::FETCH_OBJ)) $settings[$r->setting_key] = $r->setting_value;
    } catch (\Exception $e) {}
}
$enabled = ($settings['visitor_tracking_enabled'] ?? '1') === '1';
$siteId = $_GET['id'] ?? 'unknown';
$ref = $_SERVER['HTTP_REFERER'] ?? '';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
if ($enabled && !empty($ref) && !empty($config)) {
    try {
        $pdo2 = new PDO("mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password']);
        $ins = $pdo2->prepare("INSERT INTO visitor_logs (site_id, url, user_agent, ip, visited_at) VALUES (?, ?, ?, ?, NOW())");
        $ins->execute([$siteId, substr($ref, 0, 500), substr($ua, 0, 500), $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (\Exception $e) {}
}
?>
(function() {
    <?php if ($enabled): ?>
    var img = new Image();
    img.src = 'https://planet-hosts.com/track.php?id=<?php echo urlencode($siteId); ?>&r=' + encodeURIComponent(document.referrer) + '&u=' + encodeURIComponent(window.location.href);
    img.style.display = 'none';
    document.body.appendChild(img);
    <?php endif; ?>
    console.log('<?php echo $enabled ? "Tracking active" : "Tracking disabled"; ?>');
})();
