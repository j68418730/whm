<?php
$stationId = isset($_GET['id']) ? (int)$_GET['id'] : 4;
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$st = $pdo->prepare("SELECT * FROM streaming_stations WHERE id = ?");
$st->execute([$stationId]);
$station = $st->fetch(PDO::FETCH_OBJ);
$sslStream = "https://planet-hosts.com:2083/radio/stream-proxy.php?port={$station->port}";
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=htmlspecialchars($station->name)?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,-apple-system,sans-serif;background:radial-gradient(ellipse at top,#0d1117,#010409);color:#e6edf3;min-height:100vh;display:flex;align-items:center;justify-content:center}
.card{background:rgba(22,27,34,.85);backdrop-filter:blur(20px);border:1px solid rgba(48,54,61,.4);border-radius:20px;padding:32px;text-align:center;max-width:400px;width:90%}
.badge{display:inline-block;background:linear-gradient(135deg,rgba(0,140,255,.15),rgba(168,85,247,.08));border:1px solid rgba(0,140,255,.12);border-radius:20px;padding:4px 14px;font-size:10px;color:#58a6ff;text-transform:uppercase;letter-spacing:1px;font-weight:600;margin-bottom:12px}
.name{font-size:24px;font-weight:800;margin-bottom:4px;background:linear-gradient(135deg,#e6edf3,#58a6ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.status{font-size:12px;color:#64748b;margin-bottom:16px;display:flex;align-items:center;justify-content:center;gap:6px}
.dot{width:8px;height:8px;border-radius:50%;background:#3fb950;animation:pulse 2s infinite;display:inline-block}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.cover{width:160px;height:160px;border-radius:16px;background:linear-gradient(135deg,rgba(0,140,255,.08),rgba(168,85,247,.06));margin:0 auto 14px;display:flex;align-items:center;justify-content:center;font-size:48px;border:1px solid rgba(48,54,61,.2)}
.song{font-size:16px;font-weight:700;margin-bottom:2px;min-height:22px}
.artist{font-size:13px;color:#8b949e;margin-bottom:16px;min-height:18px}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 32px;border-radius:10px;border:none;background:linear-gradient(135deg,#008cff,#005ec4);color:#fff;font-size:14px;font-weight:700;cursor:pointer;text-decoration:none;transition:.2s}
.btn:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(0,140,255,.25)}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:14px}
.grid div{background:rgba(13,17,23,.4);border-radius:10px;padding:10px}
.grid .n{font-size:20px;font-weight:700;color:#58a6ff}
.grid .l{font-size:9px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-top:2px}
.player{display:none;margin-top:12px}
.player audio{width:100%;height:40px;border-radius:8px}
</style>
</head>
<body>
<div class="card">
<div class="badge">🔴 LIVE</div>
<div class="name"><?=htmlspecialchars($station->name)?></div>
<div class="status"><span class="dot"></span><span id="listeners">0</span> listeners</div>
<div class="cover">📻</div>
<div class="song" id="song"><?=htmlspecialchars($station->current_song ?: 'Loading...')?></div>
<div class="artist" id="artist">Planet Hosts Radio</div>
<button class="btn" onclick="toggle()">▶ Listen Live</button>
<div class="player" id="player"><audio id="audio" controls autoplay><source src="<?=$sslStream?>"></audio></div>
<div class="grid">
<div><div class="n" id="statL">0</div><div class="l">Listeners</div></div>
<div><div class="n"><?=$station->bitrate?:128?></div><div class="l">Kbps</div></div>
</div>
</div>
<script>
function toggle(){
 var p=document.getElementById('player'),a=document.getElementById('audio'),b=document.querySelector('.btn');
 if(p.style.display=='block'){a.pause();p.style.display='none';b.innerHTML='▶ Listen Live'}
 else{p.style.display='block';a.play().catch(function(){});b.innerHTML='⏸ Stop'}
}
setInterval(function(){
 fetch('/radio/nowplaying.php?id=<?=$stationId?>').then(function(r){return r.json()}).then(function(d){
  if(d.success){
   document.getElementById('song').textContent=d.current_song||'Loading...';
   document.getElementById('artist').textContent=d.artist||'Planet Hosts Radio';
   document.getElementById('listeners').textContent=d.listeners||0;
   document.getElementById('statL').textContent=d.listeners||0;
  }
 }).catch(function(){})
},5000);
</script>
</body>
</html>