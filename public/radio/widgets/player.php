<?php
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
if (!$streamId) exit;
try {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $s = $pdo->prepare("SELECT * FROM radio_streams WHERE id = ?");
    $s->execute([$streamId]);
    $stream = $s->fetch(PDO::FETCH_OBJ);
} catch (Exception $e) { $stream = null; }
if (!$stream) exit;
$name = htmlspecialchars($stream->server_name ?? 'Radio');
$port = (int)($stream->port ?? 8000);
$mount = htmlspecialchars($stream->mount_point ?? '/live');
$status = $stream->status === 'running';
$sUrl = "http://planet-hosts.com:{$port}{$mount}";
?>
(function() {
var p='<div style="background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:12px;padding:14px;font-family:Inter,sans-serif;color:#e0e0e0;max-width:320px">';
p+='<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">';
p+='<span style="font-size:13px;font-weight:700;color:#008cff"><?php echo $name; ?></span>';
p+='<span style="font-size:10px;color:<?php echo $status ? "#4ade80" : "#f87171"; ?>"><?php echo $status ? "● LIVE" : "● OFFLINE"; ?></span></div>';
p+='<audio id="ph-player-<?php echo $streamId; ?>" src="<?php echo $sUrl; ?>" preload="none" style="width:100%"></audio>';
p+='<div style="display:flex;gap:6px;margin-top:8px">';
p+='<button onclick="document.getElementById(\'ph-player-<?php echo $streamId; ?>\').play()" style="flex:1;padding:6px;border-radius:6px;border:none;background:var(--primary,#008cff);color:#fff;cursor:pointer;font-size:12px">&#9654; Play</button>';
p+='<button onclick="document.getElementById(\'ph-player-<?php echo $streamId; ?>\').pause()" style="flex:1;padding:6px;border-radius:6px;border:none;background:rgba(255,255,255,.1);color:#fff;cursor:pointer;font-size:12px">&#9646;&#9646; Pause</button>';
p+='</div><div style="margin-top:6px;display:flex;gap:8px;font-size:10px;color:#64748b">';
p+='<span>Listeners: <?php echo (int)($stream->listener_count??0); ?></span>';
p+='<span><?php echo (int)($stream->bitrate??128); ?>kbps</span>';
p+='</div></div>';
document.currentScript.parentNode.insertBefore(document.createRange().createContextualFragment(p), document.currentScript);
})();

