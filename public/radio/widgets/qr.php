<?php
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
$url = 'https://planet-hosts.com/radio/embed.php?stream=' . $streamId;
?>
<div style="text-align:center;font-family:Inter,sans-serif">
<img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?=urlencode($url)?>" alt="QR Code" style="border-radius:8px">
<div style="font-size:10px;color:#64748b;margin-top:4px">Scan to listen</div>
</div>
