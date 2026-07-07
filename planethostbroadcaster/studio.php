<?php
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
.topbar{background:linear-gradient(135deg,rgba(0,140,255,.1),rgba(168,85,247,.05));border-bottom:1px solid rgba(0,191,255,.1);padding:10px 20px;display:flex;justify-content:space-between;align-items:center}
.topbar .logo{font-size:16px;font-weight:800}.topbar .logo span{color:#008cff}
.topbar .info{display:flex;align-items:center;gap:12px;font-size:12px;color:#94a3b8}
.topbar .info .dot{width:8px;height:8px;border-radius:50%;display:inline-block}
.topbar .info .dot.on{background:#4ade80;animation:pulse 1.5s infinite}
.topbar .info .dot.off{background:#f87171}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.main{display:flex;flex:1;overflow:hidden}
.sidebar{width:260px;background:rgba(8,16,28,.85);border-right:1px solid rgba(255,255,255,.04);display:flex;flex-direction:column;flex-shrink:0}
.sidebar .lib{flex:1;overflow-y:auto;padding:8px 0}
.sidebar .plh{padding:8px 16px;font-size:10px;text-transform:uppercase;color:#64748b;letter-spacing:1px;font-weight:600}
.sidebar .trk{padding:7px 16px;font-size:12px;color:#94a3b8;cursor:pointer;transition:.1s;display:flex;align-items:center;gap:8px;border-left:2px solid transparent}
.sidebar .trk:hover{background:rgba(0,140,255,.04);color:#e0e0e0}
.sidebar .trk.act{color:#0A84FF;background:rgba(0,140,255,.08);border-left-color:#0A84FF}
.cont{flex:1;display:flex;flex-direction:column;padding:20px;overflow-y:auto;align-items:center}
.player{text-align:center;padding:20px 0;width:100%;max-width:500px}
.cover{width:140px;height:140px;border-radius:20px;background:linear-gradient(135deg,rgba(0,140,255,.1),rgba(168,85,247,.08));margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:48px;border:1px solid rgba(0,191,255,.1)}
.title{font-size:20px;font-weight:700}.artist{font-size:13px;color:#94a3b8;margin-bottom:14px}
.ctrl{display:flex;justify-content:center;gap:10px;margin-bottom:14px}
.ctrl button{width:42px;height:42px;border-radius:50%;border:none;cursor:pointer;transition:.15s;font-size:16px;font-family:inherit;display:flex;align-items:center;justify-content:center}
.ctrl .pp{background:linear-gradient(135deg,#008cff,#0066cc);color:#fff;width:50px;height:50px;font-size:20px}
.ctrl .pp:hover{transform:scale(1.05);box-shadow:0 4px 20px rgba(0,140,255,.4)}
.ctrl .sk{background:rgba(255,255,255,.06);color:#94a3b8}
.ctrl .sk:hover{background:rgba(255,255,255,.1);color:#e0e0e0}
.prog{height:4px;background:rgba(255,255,255,.06);border-radius:2px;margin-bottom:16px;width:100%;position:relative;cursor:pointer}
.prog .bar{height:100%;width:0%;background:linear-gradient(90deg,#008cff,#a855f7);border-radius:2px;transition:width .3s}
.sources{display:flex;gap:8px;justify-content:center;margin-bottom:14px;flex-wrap:wrap}
.src{padding:8px 16px;border-radius:8px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#94a3b8;cursor:pointer;font-size:11px;font-weight:600;transition:.15s;font-family:inherit}
.src:hover{border-color:rgba(0,140,255,.3);color:#e0e0e0}
.src.act{background:rgba(0,140,255,.15);border-color:rgba(0,140,255,.3);color:#0A84FF}
.src.mic{background:rgba(168,85,247,.1);border-color:rgba(168,85,247,.2);color:#a855f7}
.src.mic.act{background:rgba(168,85,247,.2);border-color:rgba(168,85,247,.4);color:#c084fc}
.meter{width:100%;max-width:300px;height:6px;background:rgba(255,255,255,.06);border-radius:3px;margin:8px auto;overflow:hidden}
.meter .lv{height:100%;width:0%;background:linear-gradient(90deg,#4ade80,#facc15,#f87171);border-radius:3px;transition:width .1s}
.queue{width:100%;max-width:500px;background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:14px;margin-top:auto}
.queue h3{font-size:12px;font-weight:600;margin-bottom:8px;color:#94a3b8}
.qi{display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:11px}
.qi:last-child{border:none}
.qi .qt{flex:1;color:#e0e0e0}.qi .qa{color:#64748b;font-size:10px}
.bot{background:rgba(8,16,28,.9);border-top:1px solid rgba(255,255,255,.04);padding:8px 20px;display:flex;justify-content:space-between;align-items:center;font-size:11px;color:#64748b}
.bot .st{display:flex;gap:14px}
.bot .st .s{text-align:center}
.bot .st .s .n{color:#38bdf8;font-weight:600;font-size:12px}
.bot .st .s .l{font-size:8px;text-transform:uppercase;letter-spacing:1px}
.bot .ac{display:flex;gap:6px}
.bot .ac button{padding:6px 14px;border-radius:6px;border:none;font-size:10px;font-weight:600;cursor:pointer;transition:.15s;font-family:inherit}
.bot .ac .gl{background:rgba(74,222,128,.15);color:#4ade80}
.bot .ac .gl:hover{background:rgba(74,222,128,.25)}
.bot .ac .gl.on{background:rgba(248,113,113,.15);color:#f87171;animation:pulse 2s infinite}
</style>
</head>
<body>
<div class="topbar">
<div class="logo">PLANET <span>STUDIO</span></div>
<div class="info"><span class="dot off" id="sDot"></span><span id="sText">Offline</span><span><?php echo htmlspecialchars($dj->username); ?></span></div>
</div>
<div class="main">
<div class="sidebar">
<div class="lib">
<div class="plh">Library</div>
<?php foreach ($playlists as $pl): 
$items = $pdo->prepare("SELECT * FROM radio_playlist_items WHERE playlist_id=? ORDER BY id");
$items->execute([$pl->id]); ?>
<div class="plh" style="font-size:11px;color:#e0e0e0;text-transform:none;letter-spacing:0"><?php echo htmlspecialchars($pl->name); ?></div>
<?php while ($item = $items->fetch(PDO::FETCH_OBJ)): ?>
<div class="trk" onclick="loadTrack('<?php echo htmlspecialchars($item->title ?: basename($item->file_path)); ?>','<?php echo htmlspecialchars($item->artist); ?>')">♪ <?php echo htmlspecialchars($item->title ?: basename($item->file_path)); ?></div>
<?php endwhile; endforeach; ?>
</div>
</div>
<div class="cont">
<div class="player">
<div class="cover" id="cv">🎤</div>
<div class="title" id="sT">Ready</div>
<div class="artist" id="sA">Select a source</div>
<div class="sources">
<button class="src act" id="srcFile" onclick="setSource('file')">📁 File</button>
<button class="src mic" id="srcMic" onclick="setSource('mic')">🎤 Mic</button>
<button class="src" id="srcMix" onclick="setSource('mix')">🔀 Mix</button>
</div>
<div class="ctrl">
<button class="sk" onclick="prev()">⏮</button>
<button class="pp" id="pb" onclick="tp()">▶</button>
<button class="sk" onclick="next()">⏭</button>
</div>
<div class="prog" onclick="seek(event)"><div class="bar" id="pr"></div></div>
<div class="meter"><div class="lv" id="lv"></div></div>
</div>
<div class="queue"><h3>Up Next</h3><div id="qL"></div></div>
</div>
</div>
<div class="bot">
<div class="st">
<div class="s"><div class="n" id="lC">0</div><div class="l">Listeners</div></div>
<div class="s"><div class="n" id="bR">128</div><div class="l">Kbps</div></div>
<div class="s"><div class="n" id="uT">00:00</div><div class="l">Uptime</div></div>
</div>
<div class="ac"><button class="gl" id="glB" onclick="gl()">🔴 Go Live</button></div>
</div>

<audio id="ap" preload="none"></audio>
<script>
var ap=document.getElementById('ap'),pb=document.getElementById('pb'),live=false,src='file';
var mediaRecorder=null,audioChunks=[],audioStream=null,streamXhr=null;
function loadTrack(t,a){document.getElementById('sT').textContent=t||'Unknown';document.getElementById('sA').textContent=a||''}
function tp(){if(ap.paused){ap.play();pb.textContent='⏸'}else{ap.pause();pb.textContent='▶'}}
function prev(){} function next(){}
ap.addEventListener('timeupdate',function(){if(ap.duration)document.getElementById('pr').style.width=(ap.currentTime/ap.duration*100)+'%'});
function seek(e){var r=e.currentTarget.getBoundingClientRect();if(ap.duration)ap.currentTime=((e.clientX-r.left)/r.width)*ap.duration}
function setSource(m){
 src=m;
 document.querySelectorAll('.src').forEach(function(b){b.classList.remove('act')});
 document.getElementById('src'+m.charAt(0).toUpperCase()+m.slice(1)).classList.add('act');
 if(m==='mic')initMic(); else stopMic();
}
function initMic(){
 navigator.mediaDevices.getUserMedia({audio:true}).then(function(s){
  audioStream=s;
  document.getElementById('sT').textContent='Microphone';
  document.getElementById('sA').textContent='Mic active — click Go Live';
  var act=new(window.AudioContext||window.webkitAudioContext)();
  var src=act.createMediaStreamSource(s);
  var ana=act.createAnalyser();ana.fftSize=256;
  src.connect(ana);
  var arr=new Uint8Array(ana.frequencyBinCount);
  setInterval(function(){
   ana.getByteFrequencyData(arr);
   var sum=0;for(var i=0;i<arr.length;i++)sum+=arr[i];
   var pct=Math.min(100,sum/arr.length*2);
   document.getElementById('lv').style.width=pct+'%';
  },200);
 }).catch(function(e){alert('Mic denied: '+e.message)});
}
function stopMic(){if(audioStream){audioStream.getTracks().forEach(function(t){t.stop()});audioStream=null}}
function startBroadcast(){
 if(src==='mic'&&audioStream){
  var mr=new MediaRecorder(audioStream,{mimeType:'audio/webm;codecs=opus'});
  mediaRecorder=mr;
  mr.ondataavailable=function(e){
   if(e.data.size>0&&live){
    var x=new XMLHttpRequest();
    x.open('POST','http://45.61.59.55:9006/',true);
    x.setRequestHeader('Content-Type','application/octet-stream');
    x.send(e.data);
   }
  };
  mr.start(1000); // chunk every second
 } else if(src==='file'&&!ap.paused){
  // File mode — poll audio element and send raw audio via relay
  streamAudioElement();
 }
}
function streamAudioElement(){
 // Read audio element's raw data and send
 // For V1, just keep the connection open to relay
 var x=new XMLHttpRequest();
 x.open('POST','http://45.61.59.55:9006/',true);
 x.setRequestHeader('Content-Type','audio/mpeg');
 x.onerror=function(){};
 // Send keepalive
 streamXhr=x;
 var buf=new Uint8Array(4096);
 x.send(buf);
}
function stopBroadcast(){
 if(mediaRecorder&&mediaRecorder.state!=='inactive'){mediaRecorder.stop();}
 if(streamXhr){streamXhr.abort();streamXhr=null;}
}
function gl(){
 var b=document.getElementById('glB'),x=new XMLHttpRequest();
 if(!live){
  x.open('POST','/studio/relay.php?action=start&stream_id=<?php echo $dj->stream_id; ?>',true);
  x.onload=function(){live=true;b.textContent='⏹ Stop';b.classList.add('on');document.getElementById('sDot').className='dot on';document.getElementById('sText').textContent='On Air';startBroadcast()};
  x.send();
 } else {
  stopBroadcast();
  x.open('POST','/studio/relay.php?action=stop&stream_id=<?php echo $dj->stream_id; ?>',true);
  x.onload=function(){live=false;b.textContent='🔴 Go Live';b.classList.remove('on');document.getElementById('sDot').className='dot off';document.getElementById('sText').textContent='Offline'};
  x.send();
 }
}
setInterval(function(){
 var x=new XMLHttpRequest();
 x.open('GET','/studio/relay.php?action=status&stream_id=<?php echo $dj->stream_id; ?>',true);
 x.onload=function(){try{var d=JSON.parse(x.responseText);document.getElementById('lC').textContent=d.listeners||0;if(d.live&&!live){document.getElementById('sDot').className='dot on';document.getElementById('sText').textContent='On Air'}}catch(e){}};
 x.send();
},5000);
</script>
</body>
</html>