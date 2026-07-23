<?php
require_once __DIR__ . '/../radio_helper.php';
$streamId = (int)($_GET['stream'] ?? 0);
$layout = $_GET['layout'] ?? 'js';
if (!$streamId) { header('Content-Type: application/javascript'); echo 'console.log("no stream");'; exit; }
$stream = radio_get_stream($streamId);
if (!$stream) { header('Content-Type: application/javascript'); echo 'console.log("stream not found");'; exit; }
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','Skylinehosting171');
$liveDj = $stream->current_dj ?? '';
$song = htmlspecialchars($stream->current_song ?? '');
$artist = htmlspecialchars($stream->current_artist ?? '');
$queue = [];

// Try desktop app queue first (dj_queue table)
$dq = $pdo->prepare("SELECT title, artist FROM dj_queue WHERE stream_id=? ORDER BY position LIMIT 10");
$dq->execute([$streamId]);
$dqItems = $dq->fetchAll(PDO::FETCH_OBJ);
if (!empty($dqItems) && $dqItems[0]->title) {
    foreach ($dqItems as $i) $queue[] = ['t' => addslashes($i->title ?? ''), 'a' => addslashes($i->artist ?? '')];
} else {
    // Fallback to radio playlist
    $compositeId = $streamId > 10000 ? $streamId : $streamId + 10000;
    $ac = $pdo->prepare("SELECT playlist_ids FROM radio_autodj_config WHERE station_id=?");
    $ac->execute([$compositeId]);
    $cfg = $ac->fetch(PDO::FETCH_OBJ);
    $plIds = $cfg ? json_decode($cfg->playlist_ids ?? '[]', true) : [];
    if (!empty($plIds)) {
        $ids = implode(',', array_map('intval', $plIds));
        $items = $pdo->query("SELECT title, artist FROM radio_playlist_items WHERE playlist_id IN ($ids) ORDER BY RAND() LIMIT 8")->fetchAll(PDO::FETCH_OBJ);
        foreach ($items as $i) $queue[] = ['t' => addslashes($i->title ?? ''), 'a' => addslashes($i->artist ?? '')];
    }
    if (empty($queue)) {
        $pl = $pdo->prepare("SELECT id FROM radio_playlists WHERE stream_id=? LIMIT 1");
        $pl->execute([$streamId]);
        $plId = $pl->fetchColumn();
        if ($plId) {
            $items = $pdo->prepare("SELECT title, artist FROM radio_playlist_items WHERE playlist_id=? ORDER BY RAND() LIMIT 8");
            $items->execute([$plId]);
            foreach ($items as $i) $queue[] = ['t' => addslashes($i->title ?? ''), 'a' => addslashes($i->artist ?? '')];
        }
    }
}
$name = addslashes($stream->server_name ?: $stream->name ?: 'Radio');
$sUrl = radio_ssl_stream_url($streamId);
$djHtml = $liveDj ? '🔴 LIVE — '.addslashes($liveDj) : '🎧 AutoDJ is online';
$djColor = $liveDj ? '#4ade80' : '#38bdf8';

// Build playlist items HTML
$plHtml = '';
foreach ($queue as $q) {
    $label = ($q['a'] ? $q['a'].' — ' : '').$q['t'];
    $plHtml .= '<div style="display:flex;align-items:center;gap:6px;padding:4px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:12px;color:#94a3b8">';
    $plHtml .= '<div style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">'.($q['a'] ? '<span style="color:#e0e0e0">'.$q['a'].'</span> — ' : '').$q['t'].'</div>';
    $plHtml .= '<button onclick="srqf(\''.$q['t'].'\',\''.$q['a'].'\')" style="padding:3px 8px;border-radius:5px;background:rgba(56,189,248,.12);color:#38bdf8;border:none;cursor:pointer;font-size:10px;font-weight:600;white-space:nowrap;flex-shrink:0">Request</button></div>';
}

$js = "document.getElementById('ph-sr-{$streamId}').innerHTML='<div style=\"font-family:Inter,sans-serif;max-width:400px;margin:0 auto\">"
    ."<div style=\"background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.06);border-radius:14px;padding:16px;margin-bottom:10px\">"
    ."<div style=\"font-size:13px;font-weight:700;color:#e0e0e0;margin-bottom:6px\">{$name}</div>"
    ."<div style=\"font-size:11px;color:{$djColor};font-weight:700;margin-bottom:4px\">{$djHtml}</div>"
    ."<div style=\"font-size:12px;color:#94a3b8\">".($artist?"{$artist} — ":"").($song?:'No song playing')."</div>"
    ."<audio src=\"{$sUrl}\" preload=\"auto\" controls style=\"width:100%;height:32px;margin-top:8px;border-radius:6px\"></audio></div>"
    .($plHtml ? "<div style=\"background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.06);border-radius:14px;padding:16px;margin-bottom:10px\"><div style=\"font-size:12px;font-weight:700;color:#e0e0e0;margin-bottom:8px\">🎧 Up Next</div>{$plHtml}</div>" : "")
    ."<div style=\"background:rgba(15,23,42,.5);border:1px solid rgba(56,189,248,.06);border-radius:14px;padding:16px\">"
    ."<div style=\"font-size:12px;font-weight:700;color:#e0e0e0;margin-bottom:8px\">🎤 Request a Song</div>"
    ."<div style=\"margin-bottom:6px\"><input id=\"sra\" placeholder=\"Artist\" required style=\"width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none\"></div>"
    ."<div style=\"margin-bottom:6px\"><input id=\"srt\" placeholder=\"Song Title\" required style=\"width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none\"></div>"
    ."<div style=\"margin-bottom:6px\"><input id=\"srg\" placeholder=\"Your Name\" style=\"width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none\"></div>"
    ."<button onclick=\"srq()\" style=\"width:100%;padding:8px;background:linear-gradient(135deg,#008cff,#38bdf8);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer\">Send Request</button>"
    ."<div id=\"srp\" style=\"display:none;margin-top:6px;font-size:12px;text-align:center;padding:6px;border-radius:6px\"></div></div></div>';"
    ."function srq(){var a=document.getElementById('sra'),t=document.getElementById('srt'),g=document.getElementById('srg'),p=document.getElementById('srp');"
    ."if(!a.value||!t.value){p.style.display='block';p.style.background='rgba(248,113,113,.1)';p.style.color='#f87171';p.textContent='Artist and title required';return;}"
    ."fetch('https://planet-hosts.com/connector/station/{$streamId}/requests',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({artist:a.value,title:t.value,guest_name:g.value})})"
    .".then(function(r){return r.json()}).then(function(d){p.style.display='block';"
    ."if(d.success){a.value='';t.value='';g.value='';p.style.background='rgba(74,222,128,.1)';p.style.color='#4ade80';p.textContent='Request sent!';}"
    ."else{p.style.background='rgba(248,113,113,.1)';p.style.color='#f87171';p.textContent='Error.';}})"
    .".catch(function(){p.style.display='block';p.style.background='rgba(248,113,113,.1)';p.style.color='#f87171';p.textContent='Connection error.';});}"
    ."function srqf(t,a){document.getElementById('sra').value=a;document.getElementById('srt').value=t;document.getElementById('srt').focus();"
    ."document.getElementById('srt').scrollIntoView({behavior:'smooth'});}";

if ($layout === 'iframe') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">'
        .'<title>Station Request</title><style>*{margin:0;padding:0;box-sizing:border-box}body{background:transparent;font-family:Inter,sans-serif}</style></head><body>'
        .'<div id="ph-sr-'.$streamId.'"></div><script>'.$js.'</script></body></html>';
} else {
    header('Content-Type: application/javascript; charset=utf-8');
    echo '(function(){var s=document.getElementById("ph-sr-'.$streamId.'");if(!s){s=document.createElement("div");s.id="ph-sr-'.$streamId.'";'
        .'document.currentScript.parentNode.insertBefore(s,document.currentScript);}'.$js.'})();';
}
