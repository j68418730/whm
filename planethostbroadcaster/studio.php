<?php
/**
 * Planet Hosts Studio — Web Broadcaster Remote Control
 * 
 * Controls existing systems:
 * - AutoDJ (start/stop via RadioAutoDJPlayer)
 * - Playlist Manager (queue tracks via radio_playlist_items)
 * - DJ Manager (auth via radio_djs)
 * - Stream status (via SHOUTcast statistics API)
 * 
 * No audio capture — uses existing streaming infrastructure.
 */

session_start();
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$djUser = $_SESSION['dj_user'] ?? null;
if (!$djUser) { header('Location: /dj_panel.php'); exit; }

$stmt = $pdo->prepare("SELECT d.*, ss.name as station_name, ss.port, ss.plain_password, ss.status, ss.autodj_enabled 
    FROM radio_djs d JOIN streaming_stations ss ON d.stream_id = ss.id WHERE d.id = ?");
$stmt->execute([$djUser['id']]);
$dj = $stmt->fetch(PDO::FETCH_OBJ);
if (!$dj) die('DJ account not found.');

$playlists = $pdo->prepare("SELECT * FROM radio_playlists WHERE stream_id=?");
$playlists->execute([$dj->stream_id]);
$playlists = $playlists->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Planet Hosts Studio</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,sans-serif;background:#070b14;color:#e0e0e0;height:100vh;display:flex;flex-direction:column}
.topbar{background:linear-gradient(135deg,rgba(0,140,255,.1),rgba(168,85,247,.05));border-bottom:1px solid rgba(0,191,255,.1);padding:12px 20px;display:flex;justify-content:space-between;align-items:center}
.topbar .logo{font-size:16px;font-weight:800}.topbar .logo span{color:#008cff}
.topbar .status{display:flex;align-items:center;gap:8px;font-size:12px}
.topbar .status .dot{width:8px;height:8px;border-radius:50%;display:inline-block}
.topbar .status .dot.on{background:#4ade80;animation:pulse 1.5s infinite}
.topbar .status .dot.off{background:#f87171}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.main{display:flex;flex:1;overflow:hidden}
.sidebar{width:260px;background:rgba(8,16,28,.85);border-right:1px solid rgba(255,255,255,.04);display:flex;flex-direction:column;flex-shrink:0}
.sidebar .lib{flex:1;overflow-y:auto;padding:8px 0}
.sidebar .pl-hdr{padding:8px 16px;font-size:10px;text-transform:uppercase;color:#64748b;letter-spacing:1px;font-weight:600}
.sidebar .trk{padding:8px 16px;font-size:12px;color:#94a3b8;cursor:pointer;transition:.1s;display:flex;align-items:center;gap:8px;border-left:2px solid transparent}
.sidebar .trk:hover{background:rgba(0,140,255,.04);color:#e0e0e0}
.sidebar .trk.act{color:#0A84FF;background:rgba(0,140,255,.08);border-left-color:#0A84FF}
.content{flex:1;display:flex;flex-direction:column;padding:20px;overflow-y:auto;align-items:center}
.nowplaying{text-align:center;padding:30px 0}
.cover{width:160px;height:160px;border-radius:20px;background:linear-gradient(135deg,rgba(0,140,255,.1),rgba(168,85,247,.08));margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:56px;border:1px solid rgba(0,191,255,.1)}
.title{font-size:22px;font-weight:700;margin-bottom:2px}
.artist{font-size:14px;color:#94a3b8;margin-bottom:20px}
.ctrl{display:flex;justify-content:center;gap:10px;margin-bottom:16px}
.ctrl button{width:44px;height:44px;border-radius:50%;border:none;cursor:pointer;transition:.15s;display:flex;align-items:center;justify-content:center;font-size:18px;font-family:inherit}
.ctrl .pp{background:linear-gradient(135deg,#008cff,#0066cc);color:#fff;width:52px;height:52px;font-size:22px}
.ctrl .pp:hover{transform:scale(1.05);box-shadow:0 4px 20px rgba(0,140,255,.4)}
.ctrl .sk{background:rgba(255,255,255,.06);color:#94a3b8}
.ctrl .sk:hover{background:rgba(255,255,255,.1);color:#e0e0e0}
.prog{height:4px;background:rgba(255,255,255,.06);border-radius:2px;margin-bottom:24px;max-width:360px;width:100%;position:relative}
.prog .bar{height:100%;width:0%;background:linear-gradient(90deg,#008cff,#a855f7);border-radius:2px;transition:width .3s}
.queue{width:100%;max-width:500px;background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:16px;margin-top:12px}
.queue h3{font-size:13px;font-weight:600;margin-bottom:10px}
.qitem{display:flex;align-items:center;gap:10px;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:12px}
.qitem:last-child{border-bottom:none}
.qitem .qt{flex:1;color:#e0e0e0}
.qitem .qa{color:#64748b;font-size:11px}
.botbar{background:rgba(8,16,28,.9);border-top:1px solid rgba(255,255,255,.04);padding:10px 20px;display:flex;justify-content:space-between;align-items:center;font-size:11px;color:#64748b}
.botbar .inf{display:flex;gap:16px}
.botbar .inf .st{text-align:center}
.botbar .inf .st .n{color:#38bdf8;font-weight:600;font-size:13px}
.botbar .inf .st .l{font-size:9px;text-transform:uppercase;letter-spacing:1px}
.botbar .act{display:flex;gap:8px}
.botbar .act button{padding:6px 16px;border-radius:6px;border:none;font-size:11px;font-weight:600;cursor:pointer;transition:.15s;font-family:inherit}
.botbar .act .gl{background:rgba(74,222,128,.15);color:#4ade80}
.botbar .act .gl:hover{background:rgba(74,222,128,.25)}
.botbar .act .gl.on{background:rgba(248,113,113,.15);color:#f87171;animation:pulse 2s infinite}
</style>
</head>
<body>
<div class="topbar">
<div class="logo">PLANET <span>STUDIO</span></div>
<div><span class="dot off" id="sDot"></span> <span id="sText">Offline</span></div>
<div style="font-size:12px;color:#94a3b8"><?php echo htmlspecialchars($dj->station_name ?? 'Studio'); ?> · <?php echo htmlspecialchars($dj->username); ?></div>
</div>
<div class="main">
<div class="sidebar">
<div class="lib">
<div class="pl-hdr">My Library</div>
<?php foreach ($playlists as $pl): 
$items = $pdo->prepare("SELECT * FROM radio_playlist_items WHERE playlist_id=? ORDER BY id");
$items->execute([$pl->id]);
?>
<div class="pl-hdr" style="font-size:11px;color:#e0e0e0;text-transform:none;letter-spacing:0"><?php echo htmlspecialchars($pl->name); ?></div>
<?php while ($item = $items->fetch(PDO::FETCH_OBJ)): ?>
<div class="trk" onclick="load('<?php echo htmlspecialchars($item->title ?: basename($item->file_path)); ?>','<?php echo htmlspecialchars($item->artist); ?>')">♪ <?php echo htmlspecialchars($item->title ?: basename($item->file_path)); ?></div>
<?php endwhile; endforeach; ?>
</div>
</div>
<div class="content">
<div class="nowplaying">
<div class="cover">🎤</div>
<div class="title" id="sTitle">Ready</div>
<div class="artist" id="sArtist">Select a track</div>
<div class="ctrl">
<button class="sk" onclick="prev()">⏮</button>
<button class="pp" id="pb" onclick="tp()">▶</button>
<button class="sk" onclick="next()">⏭</button>
</div>
<div class="prog"><div class="bar" id="pr"></div></div>
</div>
<div class="queue">
<h3>Up Next</h3>
<div id="qList"></div>
</div>
</div>
</div>
<div class="botbar">
<div class="inf">
<div class="st"><div class="n" id="lCnt">0</div><div class="l">Listeners</div></div>
<div class="st"><div class="n">128</div><div class="l">Kbps</div></div>
<div class="st"><div class="n" id="uTime">00:00</div><div class="l">Uptime</div></div>
</div>
<div class="act"><button class="gl" id="glBtn" onclick="gl()">🔴 Go Live</button></div>
</div>

<audio id="ap" preload="none"></audio>
<script>
var ap=document.getElementById('ap'),pb=document.getElementById('pb'),live=false;
function load(t,a){document.getElementById('sTitle').textContent=t||'Unknown';document.getElementById('sArtist').textContent=a||'';}
function tp(){if(ap.paused){ap.play();pb.textContent='⏸'}else{ap.pause();pb.textContent='▶'}}
function next(){/* queue from playlist */} function prev(){}
ap.addEventListener('timeupdate',function(){if(ap.duration)document.getElementById('pr').style.width=(ap.currentTime/ap.duration*100)+'%'});
function gl(){
 var b=document.getElementById('glBtn'),x=new XMLHttpRequest();
 if(!live){
  x.open('POST','/studio/relay.php?action=start&stream_id=<?php echo $dj->stream_id; ?>',true);
  x.onload=function(){live=true;b.textContent='⏹ Stop';b.classList.add('on');document.getElementById('sDot').className='dot on';document.getElementById('sText').textContent='On Air'};
  x.send();
 } else {
  x.open('POST','/studio/relay.php?action=stop&stream_id=<?php echo $dj->stream_id; ?>',true);
  x.onload=function(){live=false;b.textContent='🔴 Go Live';b.classList.remove('on');document.getElementById('sDot').className='dot off';document.getElementById('sText').textContent='Offline'};
  x.send();
 }
}
setInterval(function(){
 var x=new XMLHttpRequest();
 x.open('GET','/studio/relay.php?action=status&stream_id=<?php echo $dj->stream_id; ?>',true);
 x.onload=function(){try{var d=JSON.parse(x.responseText);document.getElementById('lCnt').textContent=d.listeners||0;if(d.live&&!live){document.getElementById('sDot').className='dot on';document.getElementById('sText').textContent='On Air'}}catch(e){}};
 x.send();
},5000);
</script>
</body>
</html>