<?php
require_once __DIR__ . '/radio_helper.php';
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
$theme = $_GET['theme'] ?? 'dark';
if (!$streamId) exit;
$stream = radio_get_stream($streamId);
if (!$stream) exit;
$stats = radio_fetch_stats($stream);
$name = htmlspecialchars($stream->server_name ?: 'Radio');
$sUrl = radio_ssl_stream_url($streamId);
$online = $stats['status'];
$listeners = $stats['listeners'];
$bitrate = $stats['bitrate'];
$song = htmlspecialchars($stats['song'] ?: 'Not Playing');
$artist = htmlspecialchars($stats['artist']);
$isDark = $theme !== 'light';
$bg = $isDark ? '#0a0e1a' : '#f8fafc';
$card = $isDark ? 'rgba(8,16,28,.95)' : '#ffffff';
$border = $isDark ? 'rgba(0,191,255,.12)' : 'rgba(0,0,0,.08)';
$text = $isDark ? '#e0e0e0' : '#1a1a2e';
$muted = $isDark ? '#64748b' : '#94a3b8';
$shadow = $isDark ? 'none' : '0 4px 24px rgba(0,0,0,.08)';
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo $name; ?> Player</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,system-ui,sans-serif;background:<?php echo $bg; ?>;color:<?php echo $text; ?>;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0}
.player{background:<?php echo $card; ?>;border:1px solid <?php echo $border; ?>;border-radius:20px;padding:28px;max-width:380px;width:100%;text-align:center;box-shadow:<?php echo $shadow; ?>;position:relative;overflow:hidden}
.player::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#008cff,#a855f7,#008cff);background-size:200% 100%;animation:gradient 3s ease infinite}
@keyframes gradient{0%,100%{background-position:0 50%}50%{background-position:100% 50%}}
.logo{font-size:16px;font-weight:800;margin-bottom:2px;letter-spacing:-.5px}.logo span{color:#008cff}
.status-row{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:14px}
.status-dot{width:10px;height:10px;border-radius:50%;display:inline-block;animation:<?php echo $online ? 'pulse 1.5s infinite' : 'none'; ?>}
.status-dot.online{background:#4ade80;box-shadow:0 0 8px rgba(74,222,128,.4)}
.status-dot.offline{background:#f87171}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.6;transform:scale(1.2)}}
.status-text{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1.5px;color:<?php echo $online ? '#4ade80' : '#f87171'; ?>}
.cover-art{width:120px;height:120px;border-radius:16px;background:linear-gradient(135deg,rgba(0,140,255,.12),rgba(168,85,247,.08));margin:0 auto 14px;display:flex;align-items:center;justify-content:center;font-size:48px;border:1px solid <?php echo $border; ?>}
.song{font-size:17px;font-weight:700;margin:0 0 2px;line-height:1.3}
.artist{font-size:13px;color:<?php echo $muted; ?>;margin-bottom:14px;font-weight:500}
.stats{display:flex;justify-content:center;gap:20px;font-size:11px;color:<?php echo $muted; ?>;margin-bottom:16px}
.stats .stat{text-align:center}
.stats .num{font-size:15px;font-weight:700;color:#38bdf8;display:block}
.stats .lbl{font-size:9px;text-transform:uppercase;letter-spacing:1px;margin-top:2px;display:block}
.controls{display:flex;gap:8px;margin-bottom:12px}
.controls button{flex:1;padding:12px;border-radius:12px;border:none;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:6px}
.play{background:linear-gradient(135deg,#008cff,#0066cc);color:#fff}
.play:hover{transform:translateY(-1px);box-shadow:0 4px 15px rgba(0,140,255,.3)}
.pause{background:<?php echo $isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.04)'; ?>;color:<?php echo $text; ?>;border:1px solid <?php echo $border; ?>}
.pause:hover{background:<?php echo $isDark ? 'rgba(255,255,255,.1)' : 'rgba(0,0,0,.08)'; ?>}
.progress{height:4px;background:<?php echo $isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.06)'; ?>;border-radius:4px;margin-bottom:14px;overflow:hidden;cursor:pointer;position:relative}
.progress-bar{height:100%;width:0%;background:linear-gradient(90deg,#008cff,#a855f7);border-radius:4px;transition:width .5s ease}
.volume-wrap{display:flex;align-items:center;gap:8px;margin-bottom:6px}
.volume-wrap .icon{font-size:14px;cursor:pointer;opacity:.6;transition:.15s;user-select:none}
.volume-wrap .icon:hover{opacity:1}
.volume-wrap input[type=range]{flex:1;height:4px;-webkit-appearance:none;appearance:none;background:<?php echo $isDark ? 'rgba(255,255,255,.1)' : 'rgba(0,0,0,.1)'; ?>;border-radius:2px;outline:none}
.volume-wrap input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:14px;height:14px;border-radius:50%;background:#008cff;cursor:pointer;box-shadow:0 0 6px rgba(0,140,255,.3)}
.footer{display:flex;justify-content:center;gap:12px;font-size:16px;margin-top:4px}
.footer a{color:<?php echo $muted; ?>;text-decoration:none;opacity:.6;transition:.15s;cursor:pointer}
.footer a:hover{opacity:1;color:#008cff}
@media(max-width:420px){.player{border-radius:0;border:none;min-height:100vh;display:flex;flex-direction:column;justify-content:center}}
</style></head><body>
<div class="player">
<div class="logo">PLANET <span>HOSTS</span></div>
<div class="status-row">
<span class="status-dot <?php echo $online ? 'online' : 'offline'; ?>" id="statusDot"></span>
<span class="status-text" id="statusText"><?php echo $online ? 'LIVE' : 'OFFLINE'; ?></span>
<span style="color:<?php echo $muted; ?>;font-size:11px" id="listenerDisplay"><?php echo $listeners; ?> listeners</span>
</div>
<div class="cover-art" id="coverArt"><?php echo $online ? '🎵' : '🔇'; ?></div>
<div class="song" id="songDisplay"><?php echo $song; ?></div>
<?php if ($artist): ?><div class="artist" id="artistDisplay"><?php echo $artist; ?></div>
<?php else: ?><div class="artist" id="artistDisplay" style="visibility:hidden">&#8203;</div><?php endif; ?>
<div class="stats">
<div class="stat"><span class="num" id="listenerCount"><?php echo $listeners; ?></span><span class="lbl">Listeners</span></div>
<div class="stat"><span class="num"><?php echo $bitrate; ?></span><span class="lbl">Kbps</span></div>
<div class="stat"><span class="num" id="peakCount"><?php echo $stats['peak']; ?></span><span class="lbl">Peak</span></div>
</div>
<audio id="audioPlayer" src="<?php echo $sUrl; ?>" preload="none"></audio>
<div class="controls">
<button class="play" id="playBtn" onclick="togglePlay()">▶ Play</button>
<button class="pause" onclick="document.getElementById('audioPlayer').pause();document.getElementById('playBtn').innerHTML='▶ Play'">⏸ Pause</button>
</div>
<div class="progress" id="progressBar" onclick="seek(event)">
<div class="progress-bar" id="progressFill"></div>
</div>
<div class="volume-wrap">
<span class="icon" id="muteBtn" onclick="toggleMute()">🔊</span>
<input id="volumeSlider" type="range" min="0" max="1" step="0.01" value="0.8">
</div>
<div class="footer">
<a onclick="toggleFavorite()" id="favBtn" title="Favorite">♡</a>
<a onclick="shareStation()" title="Share">↗</a>
<a href="<?php echo $sUrl; ?>" target="_blank" title="Direct Link">🔗</a>
</div>
</div>
<script>
var audio=document.getElementById('audioPlayer'),playBtn=document.getElementById('playBtn');
var progressFill=document.getElementById('progressFill'),volSlider=document.getElementById('volumeSlider');
var muteBtn=document.getElementById('muteBtn'),songDisp=document.getElementById('songDisplay');
var artistDisp=document.getElementById('artistDisplay'),listenerDisp=document.getElementById('listenerCount');
var peakDisp=document.getElementById('peakCount'),statusDot=document.getElementById('statusDot');
var statusText=document.getElementById('statusText'),coverArt=document.getElementById('coverArt');
var listenerRow=document.getElementById('listenerDisplay');
function togglePlay(){if(audio.paused){audio.play();playBtn.innerHTML='⏸ Pause'}else{audio.pause();playBtn.innerHTML='▶ Play'}}
audio.addEventListener('timeupdate',function(){if(audio.duration)progressFill.style.width=(audio.currentTime/audio.duration*100)+'%'});
audio.addEventListener('play',function(){playBtn.innerHTML='⏸ Pause'});
audio.addEventListener('pause',function(){playBtn.innerHTML='▶ Play'});
volSlider.addEventListener('input',function(){audio.volume=this.value;muteBtn.textContent=this.value==0?'🔇':this.value<.5?'🔉':'🔊'});
function toggleMute(){audio.muted=!audio.muted;muteBtn.textContent=audio.muted?'🔇':audio.volume==0?'🔇':audio.volume<.5?'🔉':'🔊'}
function seek(e){var r=e.currentTarget,rect=r.getBoundingClientRect(),pct=(e.clientX-rect.left)/rect.width;audio.currentTime=pct*audio.duration}
function toggleFavorite(){var f=document.getElementById('favBtn');f.textContent=f.textContent==='♡'?'♥':f.textContent==='♥'?'♡':'♡';f.style.color=f.textContent==='♥'?'#f87171':''}
function shareStation(){var u=window.location.href;if(navigator.share)navigator.share({title:'<?php echo addslashes($name); ?>',url:u});else navigator.clipboard.writeText(u)}
// Auto-refresh now-playing
setInterval(function(){
 var x=new XMLHttpRequest();
 x.open('GET','<?php echo addslashes(radio_stream_url($stream)); ?>?stats=1&_='+Date.now(),true);
 x.onload=function(){
  try{
   var d=JSON.parse(x.responseText);
   if(d.song){songDisp.textContent=d.song;if(d.artist){artistDisp.textContent=d.artist;artistDisp.style.visibility='visible'}else{artistDisp.style.visibility='hidden'}}
   if(d.listeners!=undefined){listenerDisp.textContent=d.listeners;listenerRow.textContent=d.listeners+' listeners'}
   if(d.peak)peakDisp.textContent=d.peak
   var on=d.status||d.listeners>0;statusDot.className='status-dot '+(on?'online':'offline');statusText.textContent=on?'LIVE':'OFFLINE';statusText.style.color=on?'#4ade80':'#f87171';coverArt.textContent=on?'🎵':'🔇'
  }catch(e){}
 };
 x.send();
},10000);
</script>
</body></html>