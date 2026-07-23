<?php
require_once __DIR__ . '/../radio_helper.php';
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
$layout = $_GET['layout'] ?? 'js';
if (!$streamId) exit;

$stream = radio_get_stream($streamId);
if (!$stream) exit;

$stats = radio_fetch_stats($stream);
$name = htmlspecialchars($stream->server_name ?: 'Radio');
$sUrl = radio_ssl_stream_url($streamId);
$online = $stats['status'];
$listeners = $stats['listeners'];
$bitrate = $stats['bitrate'];
$song = htmlspecialchars($stats['song'] ?? '');
$artist = htmlspecialchars($stats['artist'] ?? '');
$nowPlaying = $song ? ($artist ? "$artist — $song" : $song) : '';
?>
<?php if ($layout === 'iframe'): ?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,sans-serif;background:transparent}
.ph-player{background:linear-gradient(135deg,rgba(10,14,26,.97),rgba(15,22,42,.95));border:1px solid rgba(0,191,255,.1);border-radius:16px;padding:18px;max-width:320px;box-shadow:0 8px 32px rgba(0,0,0,.4),inset 0 1px 0 rgba(255,255,255,.04);position:relative;overflow:hidden}
.ph-player::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,#008cff,#3bb8ff,transparent);opacity:.7}
.ph-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.ph-name{font-size:14px;font-weight:700;color:#f1f5f9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px}
.ph-song{margin-bottom:10px;padding:8px 10px;background:rgba(0,140,255,.04);border:1px solid rgba(0,191,255,.06);border-radius:8px}
.ph-song-label{font-size:8px;font-weight:700;color:#3b82f6;letter-spacing:1.5px;margin-bottom:3px;display:block}
.ph-song-title{font-size:12px;font-weight:500;color:#e0e0e0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ph-badge{display:flex;align-items:center;gap:5px;font-size:10px;font-weight:600;padding:3px 10px;border-radius:20px}
.ph-badge.live{background:rgba(74,222,128,.12);color:#4ade80}
.ph-badge.offline{background:rgba(248,113,113,.12);color:#f87171}
.ph-dot{width:7px;height:7px;border-radius:50%;display:inline-block}
.ph-dot.live{background:#4ade80;box-shadow:0 0 6px rgba(74,222,128,.6);animation:ph-pulse 1.5s infinite}
.ph-dot.offline{background:#f87171}
@keyframes ph-pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.85)}}
.ph-controls{display:flex;gap:8px;align-items:center;margin-top:10px}
.ph-play-btn{width:42px;height:42px;border-radius:50%;border:none;background:linear-gradient(135deg,#008cff,#0066cc);color:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;box-shadow:0 4px 15px rgba(0,140,255,.3);flex-shrink:0}
.ph-play-btn:hover{transform:scale(1.05);box-shadow:0 6px 20px rgba(0,140,255,.4)}
.ph-play-btn:active{transform:scale(.95)}
.ph-wave{display:flex;align-items:center;gap:2px;height:28px;flex:1;padding:0 4px}
.ph-bar{width:3px;border-radius:2px;background:#008cff;height:4px;transition:height .3s}
.ph-bar.active{animation:ph-wave 0s ease-in-out infinite alternate}
.ph-bar:nth-child(1){animation-duration:.4s}.ph-bar:nth-child(2){animation-duration:.55s}.ph-bar:nth-child(3){animation-duration:.7s}.ph-bar:nth-child(4){animation-duration:.5s}.ph-bar:nth-child(5){animation-duration:.65s}.ph-bar:nth-child(6){animation-duration:.45s}.ph-bar:nth-child(7){animation-duration:.6s}.ph-bar:nth-child(8){animation-duration:.5s}.ph-bar:nth-child(9){animation-duration:.7s}.ph-bar:nth-child(10){animation-duration:.4s}.ph-bar:nth-child(11){animation-duration:.55s}.ph-bar:nth-child(12){animation-duration:.65s}
@keyframes ph-wave{0%{height:3px}100%{height:22px}}
.ph-volume{display:flex;align-items:center;gap:8px;margin-top:8px;padding:6px 4px}
.ph-vol-btn{background:none;border:none;color:#64748b;cursor:pointer;padding:2px;display:flex;align-items:center;transition:color .2s;flex-shrink:0}
.ph-vol-btn:hover{color:#e0e0e0}
.ph-vol-btn svg{width:16px;height:16px}
.ph-vol-slider{-webkit-appearance:none;appearance:none;height:4px;border-radius:2px;background:rgba(255,255,255,.1);outline:none;flex:1;cursor:pointer;transition:background .2s}
.ph-vol-slider::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:14px;height:14px;border-radius:50%;background:#008cff;cursor:pointer;box-shadow:0 2px 6px rgba(0,140,255,.4);transition:transform .2s}
.ph-vol-slider::-webkit-slider-thumb:hover{transform:scale(1.15)}
.ph-vol-slider::-moz-range-thumb{width:14px;height:14px;border-radius:50%;background:#008cff;cursor:pointer;border:none;box-shadow:0 2px 6px rgba(0,140,255,.4)}
.ph-vol-pct{font-size:10px;color:#64748b;min-width:30px;text-align:right;font-variant-numeric:tabular-nums}
.ph-stats{display:flex;gap:14px;margin-top:8px;padding-top:10px;border-top:1px solid rgba(255,255,255,.04);font-size:11px;color:#64748b}
.ph-stat{display:flex;align-items:center;gap:5px}
.ph-stat svg{width:13px;height:13px;opacity:.6;flex-shrink:0}
.ph-stat-value{color:#94a3b8;font-weight:600}
audio{display:none}
</style>
<div class="ph-player">
<div class="ph-top">
<div class="ph-name"><?php echo $name; ?></div>
<span class="ph-badge <?php echo $online ? 'live' : 'offline'; ?>"><span class="ph-dot <?php echo $online ? 'live' : 'offline'; ?>"></span><?php echo $online ? 'LIVE' : 'OFFAIR'; ?></span>
</div>
<?php if ($nowPlaying): ?>
<div class="ph-song"><span class="ph-song-label">NOW PLAYING</span><div class="ph-song-title"><?php echo $nowPlaying; ?></div></div>
<?php endif; ?>
<audio id="ph-audio-<?php echo $streamId; ?>" src="<?php echo $sUrl; ?>" preload="auto"></audio>
<div class="ph-controls">
<button class="ph-play-btn" id="ph-btn-<?php echo $streamId; ?>" onclick="var a=document.getElementById('ph-audio-<?php echo $streamId; ?>');if(a.paused){a.play();this.innerHTML='&#9646;&#9646;';document.getElementById('ph-wave-<?php echo $streamId; ?>').querySelectorAll('.ph-bar').forEach(function(b){b.classList.add('active')})}else{a.pause();this.innerHTML='&#9654;';document.getElementById('ph-wave-<?php echo $streamId; ?>').querySelectorAll('.ph-bar').forEach(function(b){b.classList.remove('active')})}">&#9654;</button>
<div class="ph-wave" id="ph-wave-<?php echo $streamId; ?>">
<?php for ($i = 0; $i < 12; $i++): ?><div class="ph-bar"></div><?php endfor; ?>
</div>
</div>
<div class="ph-volume">
<button class="ph-vol-btn" id="ph-vol-btn-<?php echo $streamId; ?>" onclick="var a=document.getElementById('ph-audio-<?php echo $streamId; ?>');var s=document.getElementById('ph-vol-<?php echo $streamId; ?>');if(a.muted){a.muted=false;s.value=a.volume*100;this.innerHTML='<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path d=\'M11 5L6 9H2v6h4l5 4V5z\'/><path d=\'M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07\'/></svg>'}else{a.muted=true;this.innerHTML='<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path d=\'M11 5L6 9H2v6h4l5 4V5z\'/><line x1=\'23\' y1=\'9\' x2=\'17\' y2=\'15\'/><line x1=\'17\' y1=\'9\' x2=\'23\' y2=\'15\'/></svg>'};document.getElementById('ph-vol-pct-<?php echo $streamId; ?>').textContent=Math.round((a.muted?0:a.volume)*100)+'%'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5L6 9H2v6h4l5 4V5z"/><path d="M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07"/></svg></button>
<input type="range" min="0" max="100" value="75" class="ph-vol-slider" id="ph-vol-<?php echo $streamId; ?>" oninput="var a=document.getElementById('ph-audio-<?php echo $streamId; ?>');a.volume=this.value/100;a.muted=false;document.getElementById('ph-vol-btn-<?php echo $streamId; ?>').innerHTML='<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path d=\'M11 5L6 9H2v6h4l5 4V5z\'/><path d=\'M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07\'/></svg>';document.getElementById('ph-vol-pct-<?php echo $streamId; ?>').textContent=this.value+'%'">
<span class="ph-vol-pct" id="ph-vol-pct-<?php echo $streamId; ?>">75%</span>
</div>
<div class="ph-stats">
<span class="ph-stat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1v22h22"/><path d="M16 7v8M20 5v10M8 9v6M12 4v12M4 11v4"/></svg><span class="ph-stat-value"><?php echo $listeners; ?></span> listeners</span>
<span class="ph-stat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg><span class="ph-stat-value"><?php echo $bitrate; ?></span> kbps</span>
</div>
</div>
<?php else: ?>
(function(){
var s=<?php echo json_encode(['id'=>$streamId,'name'=>$name,'url'=>$sUrl,'online'=>$online,'listeners'=>$listeners,'bitrate'=>$bitrate,'song'=>$song,'artist'=>$artist,'nowPlaying'=>$nowPlaying]); ?>;
var h='<style>@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap");';
h+='.ph-w{background:linear-gradient(135deg,rgba(10,14,26,.97),rgba(15,22,42,.95));border:1px solid rgba(0,191,255,.1);border-radius:16px;padding:18px;max-width:320px;box-shadow:0 8px 32px rgba(0,0,0,.4),inset 0 1px 0 rgba(255,255,255,.04);position:relative;overflow:hidden;font-family:Inter,sans-serif}';
h+='.ph-w::before{content:"";position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,#008cff,#3bb8ff,transparent);opacity:.7}';
h+='.ph-t{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}';
h+='.ph-n{font-size:14px;font-weight:700;color:#f1f5f9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px}';
h+='.ph-ng{margin-bottom:10px;padding:8px 10px;background:rgba(0,140,255,.04);border:1px solid rgba(0,191,255,.06);border-radius:8px}';
h+='.ph-ng-l{font-size:8px;font-weight:700;color:#3b82f6;letter-spacing:1.5px;margin-bottom:3px;display:block}';
h+='.ph-ng-t{font-size:12px;font-weight:500;color:#e0e0e0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}';
h+='.ph-b{display:flex;align-items:center;gap:5px;font-size:10px;font-weight:600;padding:3px 10px;border-radius:20px}';
h+='.ph-b-l{background:rgba(74,222,128,.12);color:#4ade80}.ph-b-o{background:rgba(248,113,113,.12);color:#f87171}';
h+='.ph-d{width:7px;height:7px;border-radius:50%;display:inline-block}';
h+='.ph-d-l{background:#4ade80;box-shadow:0 0 6px rgba(74,222,128,.6);animation:phq 1.5s infinite}.ph-d-o{background:#f87171}';
h+='@keyframes phq{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.85)}}';
h+='.ph-c{display:flex;gap:8px;align-items:center;margin-top:10px}';
h+='.ph-p{width:42px;height:42px;border-radius:50%;border:none;background:linear-gradient(135deg,#008cff,#0066cc);color:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 15px rgba(0,140,255,.3);flex-shrink:0;transition:all .2s}';
h+='.ph-p:hover{transform:scale(1.05);box-shadow:0 6px 20px rgba(0,140,255,.4)}';
h+='.ph-wv{display:flex;align-items:center;gap:2px;height:28px;flex:1;padding:0 4px}';
h+='.ph-br{width:3px;border-radius:2px;background:#008cff;height:4px;transition:height .3s}';
h+='.ph-br-a{animation:phw 0s ease-in-out infinite alternate}';
h+='.ph-br:nth-child(1){animation-duration:.4s}.ph-br:nth-child(2){animation-duration:.55s}.ph-br:nth-child(3){animation-duration:.7s}.ph-br:nth-child(4){animation-duration:.5s}.ph-br:nth-child(5){animation-duration:.65s}.ph-br:nth-child(6){animation-duration:.45s}.ph-br:nth-child(7){animation-duration:.6s}.ph-br:nth-child(8){animation-duration:.5s}.ph-br:nth-child(9){animation-duration:.7s}.ph-br:nth-child(10){animation-duration:.4s}.ph-br:nth-child(11){animation-duration:.55s}.ph-br:nth-child(12){animation-duration:.65s}';
h+='@keyframes phw{0%{height:3px}100%{height:22px}}';
h+='.ph-v{display:flex;align-items:center;gap:8px;margin-top:8px;padding:6px 4px}';
h+='.ph-vb{background:none;border:none;color:#64748b;cursor:pointer;padding:2px;display:flex;align-items:center;flex-shrink:0}';
h+='.ph-vb:hover{color:#e0e0e0}.ph-vb svg{width:16px;height:16px}';
h+='.ph-vs{-webkit-appearance:none;appearance:none;height:4px;border-radius:2px;background:rgba(255,255,255,.1);outline:none;flex:1;cursor:pointer}';
h+='.ph-vs::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:14px;height:14px;border-radius:50%;background:#008cff;cursor:pointer;box-shadow:0 2px 6px rgba(0,140,255,.4)}';
h+='.ph-vs::-moz-range-thumb{width:14px;height:14px;border-radius:50%;background:#008cff;cursor:pointer;border:none}';
h+='.ph-vp{font-size:10px;color:#64748b;min-width:30px;text-align:right}';
h+='.ph-s{display:flex;gap:14px;margin-top:8px;padding-top:10px;border-top:1px solid rgba(255,255,255,.04);font-size:11px;color:#64748b}';
h+='.ph-st{display:flex;align-items:center;gap:5px}.ph-st svg{width:13px;height:13px;opacity:.6;flex-shrink:0}';
h+='.ph-vv{color:#94a3b8;font-weight:600}';
h+='</style>';
h+='<div class="ph-w"><div class="ph-t"><div class="ph-n">'+s.name+'</div>';
h+='<span class="ph-b '+(s.online?"ph-b-l":"ph-b-o")+'"><span class="ph-d '+(s.online?"ph-d-l":"ph-d-o")+'"></span>'+(s.online?"LIVE":"OFFAIR")+'</span></div>';
if(s.nowPlaying){h+='<div class="ph-ng"><span class="ph-ng-l">NOW PLAYING</span><div class="ph-ng-t">'+s.nowPlaying+'</div></div>';}
h+='<audio id="pha'+s.id+'" src="'+s.url+'" preload="auto"></audio>';
h+='<div class="ph-c"><button class="ph-p" id="phb'+s.id+'" onclick="var a=document.getElementById(\'pha'+s.id+'\');if(a.paused){a.play();this.innerHTML=\'&#9646;&#9646;\';document.getElementById(\'phw'+s.id+'\').querySelectorAll(\'.ph-br\').forEach(function(b){b.classList.add(\'ph-br-a\')})}else{a.pause();this.innerHTML=\'&#9654;\';document.getElementById(\'phw'+s.id+'\').querySelectorAll(\'.ph-br\').forEach(function(b){b.classList.remove(\'ph-br-a\')})}">&#9654;</button>';
h+='<div class="ph-wv" id="phw'+s.id+'">';
for(var i=0;i<12;i++)h+='<div class="ph-br"></div>';
h+='</div></div>';
h+='<div class="ph-v"><button class="ph-vb" id="phvb'+s.id+'" onclick="var a=document.getElementById(\'pha'+s.id+'\');var s=document.getElementById(\'phvs'+s.id+'\');if(a.muted){a.muted=false;s.value=a.volume*100;this.innerHTML=\'<svg viewBox=\\\'0 0 24 24\\\' fill=\\\'none\\\' stroke=\\\'currentColor\\\' stroke-width=\\\'2\\\'><path d=\\\'M11 5L6 9H2v6h4l5 4V5z\\\'/><path d=\\\'M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07\\\'/></svg>\'}else{a.muted=true;this.innerHTML=\'<svg viewBox=\\\'0 0 24 24\\\' fill=\\\'none\\\' stroke=\\\'currentColor\\\' stroke-width=\\\'2\\\'><path d=\\\'M11 5L6 9H2v6h4l5 4V5z\\\'/><line x1=\\\'23\\\' y1=\\\'9\\\' x2=\\\'17\\\' y2=\\\'15\\\'/><line x1=\\\'17\\\' y1=\\\'9\\\' x2=\\\'23\\\' y2=\\\'15\\\'/></svg>\'};document.getElementById(\'phvp'+s.id+'\').textContent=Math.round((a.muted?0:a.volume)*100)+\'%\'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5L6 9H2v6h4l5 4V5z"/><path d="M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07"/></svg></button>';
h+='<input type="range" min="0" max="100" value="75" class="ph-vs" id="phvs'+s.id+'" oninput="var a=document.getElementById(\'pha'+s.id+'\');a.volume=this.value/100;a.muted=false;document.getElementById(\'phvb'+s.id+'\').innerHTML=\'<svg viewBox=\\\'0 0 24 24\\\' fill=\\\'none\\\' stroke=\\\'currentColor\\\' stroke-width=\\\'2\\\'><path d=\\\'M11 5L6 9H2v6h4l5 4V5z\\\'/><path d=\\\'M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07\\\'/></svg>\';document.getElementById(\'phvp'+s.id+'\').textContent=this.value+\'%\'">';
h+='<span class="ph-vp" id="phvp'+s.id+'">75%</span></div>';
h+='<div class="ph-s"><span class="ph-st"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1v22h22"/><path d="M16 7v8M20 5v10M8 9v6M12 4v12M4 11v4"/></svg><span class="ph-vv">'+s.listeners+'</span> listeners</span>';
h+='<span class="ph-st"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg><span class="ph-vv">'+s.bitrate+'</span> kbps</span></div></div>';
document.currentScript.parentNode.insertBefore(document.createRange().createContextualFragment(h),document.currentScript);
})();
<?php endif; ?>