<?php
require_once __DIR__ . '/../radio_helper.php';
$streamId = (int)($_GET['stream'] ?? 0);
$layout = $_GET['layout'] ?? 'js';
if (!$streamId) exit;
$stream = radio_get_stream($streamId);
if (!$stream) exit;

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','Skylinehosting171');
$liveDj = $stream->current_dj ?? '';
$song = $stream->current_song ?? '';
$artist = $stream->current_artist ?? '';

// Get upcoming songs from queue/playlist
$queue = [];
$ac = $pdo->prepare("SELECT playlist_ids FROM radio_autodj_config WHERE station_id=?");
$ac->execute([$streamId + 10000]);
$cfg = $ac->fetch(PDO::FETCH_OBJ);
$plIds = $cfg ? json_decode($cfg->playlist_ids ?? '[]', true) : [];
if (!empty($plIds)) {
    $ids = implode(',', array_map('intval', $plIds));
    $items = $pdo->query("SELECT title, artist FROM radio_playlist_items WHERE playlist_id IN ($ids) ORDER BY RAND() LIMIT 8")->fetchAll(PDO::FETCH_OBJ);
    foreach ($items as $i) $queue[] = ['title' => $i->title ?? '', 'artist' => $i->artist ?? ''];
}
if (empty($queue)) {
    $pl = $pdo->prepare("SELECT id FROM radio_playlists WHERE stream_id=? LIMIT 1");
    $pl->execute([$streamId]);
    $plId = $pl->fetchColumn();
    if ($plId) {
        $items = $pdo->prepare("SELECT title, artist FROM radio_playlist_items WHERE playlist_id=? ORDER BY RAND() LIMIT 8");
        $items->execute([$plId]);
        foreach ($items as $i) $queue[] = ['title' => $i->title ?? '', 'artist' => $i->artist ?? ''];
    }
}
$name = htmlspecialchars($stream->server_name ?: $stream->name ?: 'Radio');
$sUrl = radio_ssl_stream_url($streamId);
?>
<div style="font-family:Inter,sans-serif;max-width:400px;margin:0 auto">
<div style="background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.06);border-radius:14px;padding:16px;margin-bottom:10px">
<div style="font-size:13px;font-weight:700;color:#e0e0e0;margin-bottom:6px"><?=$name?></div>
<?php if ($liveDj): ?>
<div style="font-size:11px;color:#4ade80;font-weight:700;margin-bottom:4px">🔴 LIVE — <?=htmlspecialchars($liveDj)?></div>
<?php else: ?>
<div style="font-size:11px;color:#38bdf8;font-weight:700;margin-bottom:4px">🎧 AutoDJ is online</div>
<?php endif; ?>
<div style="font-size:12px;color:#94a3b8"><?=$artist?htmlspecialchars($artist).' — ':''?><?=htmlspecialchars($song?:'No song playing')?></div>
<audio src="<?=$sUrl?>" preload="auto" controls style="width:100%;height:32px;margin-top:8px;border-radius:6px"></audio>
</div>

<?php if (!empty($queue)): ?>
<div style="background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.06);border-radius:14px;padding:16px;margin-bottom:10px">
<div style="font-size:12px;font-weight:700;color:#e0e0e0;margin-bottom:8px">🎧 Up Next</div>
<?php foreach ($queue as $q): ?>
<div style="padding:4px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:12px;color:#94a3b8">
<?php if ($q['artist']): ?><span style="color:#e0e0e0"><?=htmlspecialchars($q['artist'])?></span> — <?php endif; ?>
<?=htmlspecialchars($q['title'])?>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<div style="background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.06);border-radius:14px;padding:16px">
<div style="font-size:12px;font-weight:700;color:#e0e0e0;margin-bottom:8px">🎤 Request a Song</div>
<form id="sr-form-<?=$streamId?>" onsubmit="return submitReq(event,<?=$streamId?>)">
<div style="margin-bottom:6px"><input name="artist" placeholder="Artist" required style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;box-sizing:border-box;outline:none"></div>
<div style="margin-bottom:6px"><input name="title" placeholder="Song Title" required style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;box-sizing:border-box;outline:none"></div>
<div style="margin-bottom:6px"><input name="guest_name" placeholder="Your Name (optional)" style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;box-sizing:border-box;outline:none"></div>
<button type="submit" style="width:100%;padding:8px;background:linear-gradient(135deg,#008cff,#38bdf8);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Send Request</button>
</form>
<div id="sr-msg-<?=$streamId?>" style="display:none;margin-top:6px;font-size:12px;text-align:center;padding:6px;border-radius:6px"></div>
</div>
</div>
<script>
function submitReq(e,sid){e.preventDefault();var f=e.target,m=document.getElementById('sr-msg-'+sid);
fetch('https://planet-hosts.com/connector/station/'+sid+'/requests',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({artist:f.artist.value,title:f.title.value,guest_name:f.guest_name.value})})
.then(function(r){return r.json()}).then(function(d){
if(d.success){f.style.display='none';m.style.display='block';m.style.background='rgba(74,222,128,.1)';m.style.color='#4ade80';m.textContent='✅ Request sent!';}
else{m.style.display='block';m.style.background='rgba(248,113,113,.1)';m.style.color='#f87171';m.textContent='Error: '+d.error;}
}).catch(function(){m.style.display='block';m.style.background='rgba(248,113,113,.1)';m.style.color='#f87171';m.textContent='Connection error';});
return false;}
</script>
