<?php
require_once __DIR__ . '/radio_helper.php';
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
$streams = [];
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$rows = $pdo->query("SELECT id, name AS station_name, server_type, port, mount_point, bitrate, status, current_song, current_artist FROM streaming_stations ORDER BY id")->fetchAll(PDO::FETCH_OBJ);
$defaultIdx = 0;
foreach ($rows as $i => $r) {
    $sUrl = radio_stream_url($r);
    $streams[] = ['id' => $r->id, 'name' => $r->station_name ?: "Stream #{$r->id}", 'stream' => $sUrl, 'port' => $r->port, 'type' => $r->server_type, 'mount' => $r->mount_point ?: '/live', 'bitrate' => $r->bitrate ?: 128, 'status' => $r->status, 'song' => $r->current_song ?? '', 'artist' => $r->current_artist ?? ''];
    if ($streamId && $r->id == $streamId) $defaultIdx = $i;
    elseif (!$streamId && $r->status === 'running' && !isset($firstRunning)) { $defaultIdx = $i; $firstRunning = true; }
}
$theme = $_GET['theme'] ?? 'dark';
$isDark = $theme !== 'light';
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Planet Hosts Radio</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,system-ui,sans-serif;background:<?=$isDark?'#0a0e1a':'#f0f2f5'?>;color:<?=$isDark?'#e0e0e0':'#1a1a2e'?>;min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:20px}
.bg{position:fixed;top:0;left:0;right:0;bottom:0;background:url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="10" cy="10" r="1" fill="rgba(0,140,255,.05)"/><circle cx="90" cy="80" r="2" fill="rgba(168,85,247,.04)"/><circle cx="50" cy="50" r="3" fill="rgba(0,191,255,.03)"/></svg>');background-size:100px 100px;z-index:0;pointer-events:none}
.player-wrap{position:relative;z-index:1;width:100%;max-width:440px;margin:auto}
.player{background:<?=$isDark?'rgba(8,16,28,.92)':'#ffffff'?>;border:1px solid <?=$isDark?'rgba(0,191,255,.12)':'rgba(0,0,0,.06)'?>;border-radius:24px;padding:36px 28px;text-align:center;box-shadow:<?=$isDark?'0 0 40px rgba(0,0,0,.5)':'0 8px 32px rgba(0,0,0,.08)'?>;position:relative;overflow:hidden}
.player::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#008cff,#a855f7,#008cff);background-size:200% 100%;animation:gradient 4s ease infinite}
@keyframes gradient{0%,100%{background-position:0 50%}50%{background-position:100% 50%}}
.sel-wrap select{width:100%;padding:10px 14px;border-radius:10px;border:1px solid <?=$isDark?'rgba(255,255,255,.08)':'rgba(0,0,0,.08)'?>;background:<?=$isDark?'rgba(0,0,0,.3)':'rgba(0,0,0,.02)'?>;color:<?=$isDark?'#e0e0e0':'#1a1a2e'?>;font-size:13px;font-weight:600;outline:none;cursor:pointer;margin-bottom:16px;font-family:inherit}
.sel-wrap select option{background:<?=$isDark?'#0a0e1a':'#fff'?>;color:<?=$isDark?'#e0e0e0':'#1a1a2e'?>}
.cover{width:140px;height:140px;border-radius:20px;background:linear-gradient(135deg,rgba(0,140,255,.1),rgba(168,85,247,.08));margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:56px;border:1px solid <?=$isDark?'rgba(0,191,255,.1)':'rgba(0,0,0,.06)'?>;transition:.3s}
.cover img{max-width:100%;max-height:100%;border-radius:16px}
.station-name{font-size:20px;font-weight:800;margin-bottom:2px;letter-spacing:-.5px;color:<?=$isDark?'#e0e0e0':'#1a1a2e'?>}
.station-name span{color:#008cff}
.status-row{display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:12px}
.dot{width:8px;height:8px;border-radius:50%;display:inline-block}
.dot.online{background:#4ade80;animation:pulse 1.5s infinite;box-shadow:0 0 8px rgba(74,222,128,.4)}
.dot.offline{background:#f87171}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.3)}}
.meta{font-size:11px;color:<?=$isDark?'#64748b':'#94a3b8'?>;display:flex;gap:12px;align-items:center}
.song{font-size:18px;font-weight:700;margin:10px 0 2px;line-height:1.3;color:<?=$isDark?'#e0e0e0':'#1a1a2e'?>}
.artist{font-size:13px;color:<?=$isDark?'#94a3b8':'#64748b'?>;margin-bottom:16px;font-weight:500}
.stats{display:flex;justify-content:center;gap:24px;margin-bottom:18px}
.stat{text-align:center}
.stat-num{font-size:18px;font-weight:700;color:#38bdf8;display:block}
.stat-lbl{font-size:9px;text-transform:uppercase;letter-spacing:1px;color:<?=$isDark?'#64748b':'#94a3b8'?>;margin-top:2px}
.controls{display:flex;gap:8px;margin-bottom:14px}
.controls button{flex:1;padding:14px;border-radius:12px;border:none;font-weight:700;cursor:pointer;font-size:15px;font-family:inherit;transition:.2s;display:flex;align-items:center;justify-content:center;gap:8px}
.pp-btn{background:linear-gradient(135deg,#008cff,#0066cc);color:#fff}
.pp-btn:hover{transform:translateY(-1px);box-shadow:0 4px 20px rgba(0,140,255,.35)}
.progress{height:4px;background:<?=$isDark?'rgba(255,255,255,.06)':'rgba(0,0,0,.06)'?>;border-radius:4px;margin-bottom:12px;cursor:pointer;position:relative;overflow:hidden}
.progress-bar{height:100%;width:0%;background:linear-gradient(90deg,#008cff,#a855f7);border-radius:4px;transition:width .3s ease}
.volume-row{display:flex;align-items:center;gap:8px;margin-bottom:8px;justify-content:center}
.volume-row .v-icon{font-size:14px;cursor:pointer;opacity:.6;transition:.15s}
.volume-row .v-icon:hover{opacity:1}
.volume-row input[type=range]{width:120px;height:4px;-webkit-appearance:none;appearance:none;background:<?=$isDark?'rgba(255,255,255,.1)':'rgba(0,0,0,.1)'?>;border-radius:2px;outline:none}
.volume-row input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:14px;height:14px;border-radius:50%;background:#008cff;cursor:pointer;box-shadow:0 0 6px rgba(0,140,255,.3)}
.footer{display:flex;justify-content:center;gap:16px;font-size:14px;margin-top:8px}
.footer a{color:<?=$isDark?'#64748b':'#94a3b8'?>;text-decoration:none;opacity:.6;transition:.15s;cursor:pointer}
.footer a:hover{opacity:1;color:#008cff}
.powered{font-size:10px;color:<?=$isDark?'#64748b':'#94a3b8'?>;margin-top:16px;text-align:center;opacity:.5}
.powered a{color:#008cff;text-decoration:none}
@media(max-width:480px){.player-wrap{padding:0}.player{border-radius:16px;padding:24px 16px}.cover{width:100px;height:100px;font-size:40px}}
</style></head><body>
<div class="bg"></div>
<div class="player-wrap">
<div class="player">
<div class="sel-wrap"><select id="stationSel" onchange="switchStation(this.value)">
<?php foreach ($streams as $i => $s): ?>
<option value="<?=$i?>"<?=$i===$defaultIdx?' selected':''?>><?=htmlspecialchars($s['name'])?> (<?=strtoupper($s['type'])?>)</option>
<?php endforeach; ?>
</select></div>
<div class="cover" id="cover">🎵</div>
<div class="station-name">PLANET <span>HOSTS</span></div>
<div class="status-row"><span class="dot <?=$streams[$defaultIdx]['status']==='running'?'online':'offline'?>" id="statusDot"></span><span class="meta" id="meta"><span id="listenerDisplay">0</span> listeners · <span id="bitrateDisplay"><?=$streams[$defaultIdx]['bitrate']?></span> kbps</span></div>
<div class="song" id="songDisplay"><?=htmlspecialchars($streams[$defaultIdx]['song']?:'Not Playing')?></div>
<div class="artist" id="artistDisplay"><?=htmlspecialchars($streams[$defaultIdx]['artist']??'')?></div>
<div class="stats"><div class="stat"><span class="stat-num" id="listenerCount">0</span><span class="stat-lbl">Listeners</span></div><div class="stat"><span class="stat-num" id="peakCount">0</span><span class="stat-lbl">Peak</span></div></div>
<audio id="audio" src="https://planet-hosts.com:2083/radio/stream-proxy.php?stream=<?=$streams[$defaultIdx]['id']?>" preload="auto"></audio>
<div class="controls">
<button class="pp-btn" id="playBtn" onclick="togglePlay()">▶ Play</button>
<button style="background:<?=$isDark?'rgba(255,255,255,.06)':'rgba(0,0,0,.04)'?>;color:<?=$isDark?'#e0e0e0':'#1a1a2e'?>;border:1px solid <?=$isDark?'rgba(255,255,255,.08)':'rgba(0,0,0,.08)'?>" onclick="audio.pause();playBtn.innerHTML='▶ Play'">⏸ Pause</button>
</div>
<div class="progress" onclick="seek(event)"><div class="progress-bar" id="progressFill"></div></div>
<div class="volume-row"><span class="v-icon" id="muteBtn" onclick="toggleMute()">🔊</span><input id="volSlider" type="range" min="0" max="1" step="0.01" value="0.8"></div>
<div class="footer">
<a onclick="toggleFav()" id="favBtn">♡</a>
<a onclick="shareStation()">↗</a>
<a href="<?=$streams[$defaultIdx]['stream']?>" target="_blank">🔗</a>
</div>
</div>
<div class="powered">Powered by <a href="https://planet-hosts.com">Planet Hosts</a></div>
</div>
<script>
var stations=<?=json_encode($streams)?>,curIdx=<?=$defaultIdx?>;
var audio=document.getElementById('audio'),playBtn=document.getElementById('playBtn');
function switchStation(i){curIdx=parseInt(i);var s=stations[curIdx];audio.src='https://planet-hosts.com:2083/radio/stream-proxy.php?stream='+s.id;audio.load();document.getElementById('songDisplay').textContent=s.song||'Not Playing';document.getElementById('artistDisplay').textContent=s.artist||'';document.getElementById('bitrateDisplay').textContent=s.bitrate;var on=s.status==='running';document.getElementById('statusDot').className='dot '+(on?'online':'offline');document.getElementById('cover').textContent=on?'🎵':'🔇';playBtn.innerHTML='▶ Play';updateStats(curIdx)}
function togglePlay(){if(audio.paused){audio.play();playBtn.innerHTML='⏸ Pause'}else{audio.pause();playBtn.innerHTML='▶ Play'}}
audio.addEventListener('timeupdate',function(){if(audio.duration)document.getElementById('progressFill').style.width=(audio.currentTime/audio.duration*100)+'%'});
audio.addEventListener('play',function(){playBtn.innerHTML='⏸ Pause'});
audio.addEventListener('pause',function(){playBtn.innerHTML='▶ Play'});
document.getElementById('volSlider').addEventListener('input',function(){audio.volume=this.value;document.getElementById('muteBtn').textContent=this.value==0?'🔇':this.value<.5?'🔉':'🔊'});
function toggleMute(){audio.muted=!audio.muted;document.getElementById('muteBtn').textContent=audio.muted?'🔇':audio.volume==0?'🔇':audio.volume<.5?'🔉':'🔊'}
function seek(e){var r=e.currentTarget,pct=(e.clientX-r.getBoundingClientRect().left)/r.offsetWidth;audio.currentTime=pct*audio.duration}
function toggleFav(){var f=document.getElementById('favBtn');f.textContent=f.textContent==='♡'?'♥':'♡';f.style.color=f.textContent==='♥'?'#f87171':''}
function shareStation(){var u=window.location.href;if(navigator.share)navigator.share({title:stations[curIdx].name,url:u});else navigator.clipboard.writeText(u)}
function updateStats(idx){var x=new XMLHttpRequest();x.open('GET','/radio/widgets/stats.php?stream='+stations[idx].id+'&json=1',true);x.onload=function(){try{var d=JSON.parse(x.responseText);if(d.listeners!=undefined){document.getElementById('listenerCount').textContent=d.listeners;document.getElementById('listenerDisplay').textContent=d.listeners}if(d.peak)document.getElementById('peakCount').textContent=d.peak}catch(e){}};x.send()}
setInterval(function(){updateStats(curIdx)},15000);
</script>
</body></html>