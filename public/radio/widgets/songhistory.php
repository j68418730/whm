<?php
require_once __DIR__ . '/../../security_guard.php';
security_guard_run('radio');
require_once __DIR__ . '/../radio_helper.php';
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
$layout = $_GET['layout'] ?? 'js';
$limit = (int)($_GET['limit'] ?? 10);
if ($limit < 1) $limit = 10;
if ($limit > 100) $limit = 100;
if (!$streamId) exit;

try {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $q = $pdo->prepare("SELECT * FROM radio_song_history WHERE stream_id = ? ORDER BY played_at DESC LIMIT {$limit}");
    $q->execute([$streamId]);
    $history = $q->fetchAll(PDO::FETCH_OBJ);
} catch (Exception $e) { $history = []; }

if ($layout === 'iframe'): ?><!DOCTYPE html><html><head><style>body{margin:0;background:transparent;font-family:Inter,sans-serif;color:#fff;font-size:12px}</style></head><body><div style="font-family:Inter;color:#fff;font-size:12px"><?php foreach ($history as $h): ?><div style="padding:4px 0;border-bottom:1px solid rgba(255,255,255,.04)"><strong><?php echo htmlspecialchars($h->title??''); ?></strong><?php if ($h->artist): ?> - <?php echo htmlspecialchars($h->artist); ?><?php endif; ?> <span style="color:#64748b;font-size:10px"><?php echo $h->played_at??''; ?></span></div><?php endforeach; ?></div></body></html><?php exit; endif; ?>
document.write('<div style="font-family:Inter;color:#e0e0e0;font-size:12px"><?php foreach ($history as $h): ?>'+
'<div style="padding:4px 0;border-bottom:1px solid rgba(255,255,255,.04)"><strong><?php echo addslashes(htmlspecialchars($h->title??'')); ?></strong><?php if ($h->artist): ?> - <?php echo addslashes(htmlspecialchars($h->artist)); ?><?php endif; ?> <span style="color:#64748b;font-size:10px"><?php echo $h->played_at??''; ?></span></div>'+
'<?php endforeach; ?></div>');
