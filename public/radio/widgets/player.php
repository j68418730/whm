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
$sUrl = radio_stream_url($stream);
$online = $stats['status'];
$listeners = $stats['listeners'];
$bitrate = $stats['bitrate'];
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
.ph-stats{display:flex;gap:14px;margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.04);font-size:11px;color:#64748b}
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
<audio id="ph-audio-<?php echo $streamId; ?>" src="<?php echo $sUrl; ?>" preload="none"></audio>
<div class="ph-controls">
<button class="ph-play-btn" id="ph-btn-<?php echo $streamId; ?>" onclick="var a=document.getElementById('ph-audio-<?php echo $streamId; ?>');if(a.paused){a.play();this.innerHTML='&#9646;&#9646;';document.getElementById('ph-wave-<?php echo $streamId; ?>').querySelectorAll('.ph-bar').forEach(function(b){b.classList.add('active')})}else{a.pause();this.innerHTML='&#9654;';document.getElementById('ph-wave-<?php echo $streamId; ?>').querySelectorAll('.ph-bar').forEach(function(b){b.classList.remove('active')})}">&#9654;</button>
<div class="ph-wave" id="ph-wave-<?php echo $streamId; ?>">
<?php for ($i = 0; $i < 12; $i++): ?><div class="ph-bar"></div><?php endfor; ?>
</div>
</div>
<div class="ph-stats">
<span class="ph-stat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1v22h22"/><path d="M16 7v8M20 5v10M8 9v6M12 4v12M4 11v4"/></svg><span class="ph-stat-value"><?php echo $listeners; ?></span> listeners</span>
<span class="ph-stat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg><span class="ph-stat-value"><?php echo $bitrate; ?></span> kbps</span>
</div>
</div>
<?php else: ?>
(function(){
var s=<?php echo json_encode(['id'=>$streamId,'name'=>$name,'url'=>$sUrl,'online'=>$online,'listeners'=>$listeners,'bitrate'=>$bitrate]); ?>;
var h='<style>@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap");';
h+='.ph-w{background:linear-gradient(135deg,rgba(10,14,26,.97),rgba(15,22,42,.95));border:1px solid rgba(0,191,255,.1);border-radius:16px;padding:18px;max-width:320px;box-shadow:0 8px 32px rgba(0,0,0,.4),inset 0 1px 0 rgba(255,255,255,.04);position:relative;overflow:hidden;font-family:Inter,sans-serif}';
h+='.ph-w::before{content:"";position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,#008cff,#3bb8ff,transparent);opacity:.7}';
h+='.ph-t{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}';
h+='.ph-n{font-size:14px;font-weight:700;color:#f1f5f9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px}';
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
h+='.ph-s{display:flex;gap:14px;margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.04);font-size:11px;color:#64748b}';
h+='.ph-st{display:flex;align-items:center;gap:5px}.ph-st svg{width:13px;height:13px;opacity:.6;flex-shrink:0}';
h+='.ph-v{color:#94a3b8;font-weight:600}';
h+='</style>';
h+='<div class="ph-w"><div class="ph-t"><div class="ph-n">'+s.name+'</div>';
h+='<span class="ph-b '+(s.online?"ph-b-l":"ph-b-o")+'"><span class="ph-d '+(s.online?"ph-d-l":"ph-d-o")+'"></span>'+(s.online?"LIVE":"OFFAIR")+'</span></div>';
h+='<audio id="pha'+s.id+'" src="'+s.url+'" preload="none"></audio>';
h+='<div class="ph-c"><button class="ph-p" id="phb'+s.id+'" onclick="var a=document.getElementById(\'pha'+s.id+'\');if(a.paused){a.play();this.innerHTML=\'&#9646;&#9646;\';document.getElementById(\'phw'+s.id+'\').querySelectorAll(\'.ph-br\').forEach(function(b){b.classList.add(\'ph-br-a\')})}else{a.pause();this.innerHTML=\'&#9654;\';document.getElementById(\'phw'+s.id+'\').querySelectorAll(\'.ph-br\').forEach(function(b){b.classList.remove(\'ph-br-a\')})}">&#9654;</button>';
h+='<div class="ph-wv" id="phw'+s.id+'">';
for(var i=0;i<12;i++)h+='<div class="ph-br"></div>';
h+='</div></div>';
h+='<div class="ph-s"><span class="ph-st"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1v22h22"/><path d="M16 7v8M20 5v10M8 9v6M12 4v12M4 11v4"/></svg><span class="ph-v">'+s.listeners+'</span> listeners</span>';
h+='<span class="ph-st"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg><span class="ph-v">'+s.bitrate+'</span> kbps</span></div></div>';
document.currentScript.parentNode.insertBefore(document.createRange().createContextualFragment(h),document.currentScript);
})();
<?php endif; ?>