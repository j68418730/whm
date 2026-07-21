<?php
require_once __DIR__ . '/../radio_helper.php';
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
if (!$streamId) exit;
?>
<div style="font-family:Inter,sans-serif;max-width:300px">
<form id="ph-req-<?=$streamId?>" onsubmit="var f=this;fetch('https://planet-hosts.com/connector/station/<?=$streamId?>/requests',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({artist:f.artist.value,title:f.title.value,guest_name:f.name.value,message:f.message.value})}).then(function(r){f.innerHTML='<div style=\"color:#4ade80;font-size:13px\">Request sent!</div>'});return false">
<div style="margin-bottom:6px"><input name="artist" placeholder="Artist" required style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;box-sizing:border-box"></div>
<div style="margin-bottom:6px"><input name="title" placeholder="Song Title" required style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;box-sizing:border-box"></div>
<div style="margin-bottom:6px"><input name="name" placeholder="Your Name (optional)" style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;box-sizing:border-box"></div>
<div style="margin-bottom:6px"><textarea name="message" placeholder="Message (optional)" style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;min-height:40px;box-sizing:border-box"></textarea></div>
<button type="submit" style="padding:8px 16px;background:#008cff;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600">Request Song</button>
</form>
</div>
