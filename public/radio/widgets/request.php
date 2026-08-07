<?php
require_once __DIR__ . '/../../security_guard.php';
security_guard_run('radio');
require_once __DIR__ . '/../radio_helper.php';
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
if (!$streamId) exit;
// Resolve to the real station id (composite 10000+id supported) and build a readable slug
$realId = $streamId > 10000 ? ($streamId % 10000) : $streamId;
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$st = $pdo->prepare("SELECT name FROM streaming_stations WHERE id = ?");
$st->execute([$realId]);
$stName = $st->fetchColumn() ?: "Station #{$realId}";
$slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($stName)), '-'));
if ($slug === '') $slug = (string)$realId;
?>
<div style="font-family:Inter,sans-serif;max-width:300px">
<form id="ph-req-<?=$streamId?>" onsubmit="var f=this;fetch('https://planet-hosts.com/connector/station/<?=$slug?>/requests',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({artist:f.artist.value,title:f.title.value,guest_name:f.name.value,message:f.message.value})}).then(function(r){f.innerHTML='<div style=\"color:#4ade80;font-size:13px\">Request sent!</div>'});return false">
<div style="margin-bottom:6px"><input name="artist" placeholder="Artist" required style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;box-sizing:border-box"></div>
<div style="margin-bottom:6px"><input name="title" placeholder="Song Title" required style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;box-sizing:border-box"></div>
<div style="margin-bottom:6px"><input name="name" placeholder="Your Name (optional)" style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;box-sizing:border-box"></div>
<div style="margin-bottom:6px"><textarea name="message" placeholder="Message (optional)" style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;min-height:40px;box-sizing:border-box"></textarea></div>
<button type="submit" style="padding:8px 16px;background:#008cff;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600">Request Song</button>
</form>
</div>
