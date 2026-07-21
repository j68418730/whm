<?php
require_once __DIR__ . '/../radio_helper.php';
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
if (!$streamId) exit;
$stream = radio_get_stream($streamId);
if (!$stream) exit;
$liveDj = $stream->current_dj ?? '';
$song = htmlspecialchars($stream->current_song ?? '');
$artist = htmlspecialchars($stream->current_artist ?? '');
if ($liveDj): ?>
<div style="background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.2);border-radius:10px;padding:12px;font-family:Inter,sans-serif">
<div style="color:#4ade80;font-size:11px;font-weight:700;margin-bottom:4px">🔴 LIVE</div>
<div style="color:#e0e0e0;font-size:15px;font-weight:700"><?=htmlspecialchars($liveDj)?></div>
<?php if ($song || $artist): ?><div style="color:#94a3b8;font-size:12px;margin-top:4px"><?=$artist?> — <?=$song?></div><?php endif; ?>
</div>
<?php else: ?>
<div style="background:rgba(100,116,139,.08);border:1px solid rgba(100,116,139,.15);border-radius:10px;padding:12px;font-family:Inter,sans-serif;text-align:center">
<div style="color:#94a3b8;font-size:12px">No DJ currently live</div>
</div>
<?php endif; ?>
