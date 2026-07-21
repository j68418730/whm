<?php
/**
 * Public DJ Page — DJ Name, Banner, Bio, Song Request Form
 */
$username = $_GET['u'] ?? $_SERVER['DJ_USERNAME'] ?? '';
if (!$username) { http_response_code(404); exit; }

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','Skylinehosting171');
$dj = $pdo->prepare("SELECT rd.*, ss.name AS station_name, ss.id AS station_id FROM radio_djs rd JOIN streaming_stations ss ON ss.id=rd.stream_id WHERE rd.username=? AND rd.status='active' LIMIT 1");
$dj->execute([$username]);
$dj = $dj->fetch(PDO::FETCH_OBJ);
if (!$dj) { http_response_code(404); exit; }

$profileData = $dj->profile_data ? json_decode($dj->profile_data, true) : [];
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?=htmlspecialchars($dj->name ?: $dj->username)?> — Request a Song</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,sans-serif;background:#02050e;color:#e2e8f0;min-height:100vh}
.bg{position:fixed;inset:0;background:linear-gradient(145deg,rgba(2,8,23,.92),rgba(15,23,42,.98));z-index:-2}
<?php if ($dj->banner): ?>.banner{width:100%;height:280px;background:url('/<?=htmlspecialchars($dj->banner)?>') center/cover;position:relative}
.banner::after{content:'';position:absolute;inset:0;background:linear-gradient(transparent 40%,#02050e)}<?php endif; ?>
.container{max-width:600px;margin:0 auto;padding:24px}
.avatar{width:100px;height:100px;border-radius:50%;border:3px solid rgba(56,189,248,.2);object-fit:cover;margin:-60px auto 16px;display:block;background:rgba(15,23,42,.8)}
.avatar-placeholder{width:100px;height:100px;border-radius:50%;margin:-60px auto 16px;display:flex;align-items:center;justify-content:center;font-size:36px;background:linear-gradient(135deg,#008cff,#a855f7);color:#fff;font-weight:700;border:3px solid rgba(56,189,248,.2)}
h1{font-size:24px;font-weight:800;text-align:center;margin-bottom:2px}
.station{text-align:center;font-size:13px;color:#64748b;margin-bottom:20px}
.bio{background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.06);border-radius:12px;padding:16px;margin-bottom:16px;font-size:13px;color:#94a3b8;line-height:1.6;white-space:pre-wrap}
.social{display:flex;justify-content:center;gap:10px;margin-bottom:20px;flex-wrap:wrap}
.social a{text-decoration:none;font-size:13px;color:#64748b;padding:6px 14px;border-radius:8px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);transition:.2s}
.social a:hover{color:#38bdf8;border-color:rgba(56,189,248,.2)}
.card{background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.06);border-radius:16px;padding:24px;margin-bottom:16px}
.card h2{font-size:16px;font-weight:700;margin-bottom:12px;color:#38bdf8}
.form-group{margin-bottom:12px}
.form-group label{display:block;font-size:11px;color:#64748b;margin-bottom:4px;font-weight:600;text-transform:uppercase;letter-spacing:.3px}
.form-group input,.form-group textarea{width:100%;padding:10px 14px;background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.06);border-radius:10px;color:#e2e8f0;font-size:13px;outline:none;box-sizing:border-box;font-family:inherit;transition:border-color .2s}
.form-group input:focus,.form-group textarea:focus{border-color:rgba(56,189,248,.35)}
.form-group textarea{min-height:60px;resize:vertical}
.btn{width:100%;padding:12px;background:linear-gradient(135deg,#008cff,#38bdf8);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;transition:.2s}
.btn:hover{transform:translateY(-2px);box-shadow:0 4px 14px rgba(0,140,255,.2)}
.btn:disabled{opacity:.5;cursor:default;transform:none}
.success{display:none;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:10px;padding:16px;text-align:center;color:#4ade80;font-size:14px;font-weight:600}
footer{text-align:center;padding:20px;font-size:11px;color:#475569}
</style>
</head><body>
<div class="bg"></div>
<?php if ($dj->banner): ?><div class="banner"></div><?php endif; ?>
<div class="container">
<?php if ($dj->avatar): ?><img src="/<?=htmlspecialchars($dj->avatar)?>" class="avatar"><?php else: ?><div class="avatar-placeholder"><?=strtoupper(substr($dj->name?:$dj->username,0,1))?></div><?php endif; ?>
<h1><?=htmlspecialchars($dj->name ?: $dj->username)?></h1>
<div class="station">🎵 <?=htmlspecialchars($dj->station_name)?></div>

<?php if ($dj->bio): ?><div class="bio"><?=nl2br(htmlspecialchars($dj->bio))?></div><?php endif; ?>

<?php
$socialLinks = [];
foreach (['website_url'=>'🌐 Website','facebook'=>'📘 Facebook','instagram'=>'📷 Instagram','twitter'=>'🐦 X','youtube'=>'▶️ YouTube','soundcloud'=>'🎵 SoundCloud','mixcloud'=>'☁️ Mixcloud'] as $k=>$l) {
    $v = $profileData[$k] ?? ($dj->$k ?? '');
    if ($v) $socialLinks[] = '<a href="'.htmlspecialchars($v).'" target="_blank">'.$l.'</a>';
}
if ($socialLinks): ?><div class="social"><?=implode("\n",$socialLinks)?></div><?php endif; ?>

<div class="card">
<h2>🎧 Up Next</h2>
<div id="queue-list"><div style="text-align:center;color:#64748b;font-size:12px;padding:10px">Loading...</div></div>
</div>

<div class="card">
<h2>🎤 Request a Song</h2>
<form id="req-form" onsubmit="return submitRequest(event)">
<div class="form-group"><label>Artist</label><input name="artist" required placeholder="Artist name"></div>
<div class="form-group"><label>Song Title</label><input name="title" required placeholder="Song title"></div>
<div class="form-group"><label>Your Name (optional)</label><input name="guest_name" placeholder="Your name"></div>
<div class="form-group"><label>Message (optional)</label><textarea name="message" placeholder="A message for the DJ..."></textarea></div>
<button type="submit" class="btn" id="req-btn">Send Request</button>
</form>
<div class="success" id="req-success">✅ Request sent! Thanks for the request.</div>
</div>

<footer>Powered by Planet Hosts Radio</footer>
</div>
<script>
// Load queue
fetch('/api/radio/queue/<?=$dj->station_id?>').then(function(r){return r.json()}).then(function(d){
  var html='';if(d.queue&&d.queue.length){d.queue.forEach(function(s,i){var a=s.artist?s.artist+' - ':'',t=s.title||'Unknown';html+='<div style="display:flex;align-items:center;gap:6px;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04)"><div style="flex:1;min-width:0"><div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">'+a+t+'</div><div style="font-size:10px;color:#64748b">'+(s.duration?Math.floor(s.duration/60)+':'+(s.duration%60<10?'0':'')+s.duration%60:'')+'</div></div><button class="req-btn" data-artist="'+(s.artist||'')+'" data-title="'+(s.title||'')+'" onclick="openRequest(this)" style="padding:4px 10px;border-radius:6px;background:rgba(56,189,248,.12);color:#38bdf8;border:none;cursor:pointer;font-size:11px;font-weight:600;white-space:nowrap;flex-shrink:0">Request</button></div>';})}else{html='<div style="text-align:center;color:#64748b;font-size:12px;padding:10px">No upcoming songs</div>';}
  document.getElementById('queue-list').innerHTML=html;
}).catch(function(){document.getElementById('queue-list').innerHTML='<div style="text-align:center;color:#64748b;font-size:12px;padding:10px">Could not load queue</div>';});

function openRequest(btn){
  document.querySelector('[name="artist"]').value=btn.dataset.artist;
  document.querySelector('[name="title"]').value=btn.dataset.title;
  document.querySelector('[name="title"]').focus();
  document.getElementById('req-form').scrollIntoView({behavior:'smooth'});
}
function submitRequest(e){
  e.preventDefault(); var f=e.target,btn=document.getElementById('req-btn'),s=document.getElementById('req-success');
  btn.disabled=true; btn.textContent='Sending...';
  fetch('https://planet-hosts.com/connector/station/<?=$dj->station_id?>/requests',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({artist:f.artist.value,title:f.title.value,guest_name:f.guest_name.value,message:f.message.value})
  }).then(function(r){return r.json()}).then(function(d){
    if(d.success){f.style.display='none';s.style.display='block';}
    else{alert('Failed to send request');btn.disabled=false;btn.textContent='Send Request';}
  }).catch(function(){alert('Connection error');btn.disabled=false;btn.textContent='Send Request';});
  return false;
}
</script>
</body></html>
