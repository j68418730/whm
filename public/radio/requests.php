<?php
/**
 * Planet Hosts — Account Request Page
 * Public page: shows ALL of a hosting account's stations grouped by station
 * (name/banner/logo), checks DJ online status, pulls the desktop-app queue +
 * playlist, lets listeners request songs, and highlights requests for songs
 * NOT in the DJ's queue/playlist in RED.
 *
 * URL: /radio/requests.php?u=<hosting_username>
 */
require_once __DIR__ . '/../security_guard.php';
security_guard_run('radio');
require_once __DIR__ . '/radio_helper.php';

$username = trim($_GET['u'] ?? '');
if (!$username) { header('Location: /'); exit; }

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Resolve the hosting account
$acc = $pdo->prepare("SELECT * FROM hosting_users WHERE username=? OR email=? LIMIT 1");
$acc->execute([$username, $username]);
$account = $acc->fetch(PDO::FETCH_OBJ);
if (!$account) { http_response_code(404); echo '<!DOCTYPE html><html><head><title>Not Found</title></head><body style="background:#02050e;color:#fff;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh"><div style="text-align:center"><h1>404</h1><p style="color:#94a3b8">Account not found.</p></div></body></html>'; exit; }

// All stations for this account
$stations = $pdo->prepare("SELECT * FROM streaming_stations WHERE user_id=? ORDER BY id");
$stations->execute([$account->id]);
$stations = $stations->fetchAll(PDO::FETCH_OBJ);

// DJs for this account (all stations, active)
$djs = $pdo->prepare("SELECT d.*, ss.name AS station_name, ss.engine AS station_engine
    FROM radio_djs d
    LEFT JOIN streaming_stations ss ON ss.id = d.stream_id
    WHERE d.status='active'
      AND (d.stream_id IN (SELECT id FROM streaming_stations WHERE user_id=?)
           OR d.id IN (SELECT dj_id FROM radio_dj_streams WHERE stream_id IN (SELECT id FROM streaming_stations WHERE user_id=?)))
    ORDER BY d.username");
$djs->execute([$account->id, $account->id]);
$djs = $djs->fetchAll(PDO::FETCH_OBJ);

// Group DJs by station for quick lookup
$djByStation = [];
foreach ($djs as $dj) {
    foreach ([$dj->stream_id] as $sid) $djByStation[$sid][] = $dj;
    // junction stations
    $js = $pdo->prepare("SELECT stream_id FROM radio_dj_streams WHERE dj_id=? AND is_active='yes'");
    $js->execute([$dj->id]);
    foreach ($js->fetchAll(PDO::FETCH_COLUMN) as $jrid) $djByStation[$jrid][] = $dj;
}
// Unique each
foreach ($djByStation as &$arr) {
    $seen = []; $out = [];
    foreach ($arr as $d) { if (!isset($seen[$d->id])) { $seen[$d->id] = 1; $out[] = $d; } }
    $arr = $out;
}
unset($arr);

$base = "https://planet-hosts.com";

// Helper: is a DJ "online"? (last_active within 5 min, or current_dj on a running station)
function dj_online($dj, $pdo) {
    if (($dj->last_active ?? null)) {
        $t = strtotime($dj->last_active);
        if ($t && (time() - $t) < 300) return true;
    }
    // Fallback: check dj_connections for a live row
    try {
        $c = $pdo->prepare("SELECT COUNT(*) FROM dj_connections WHERE dj_id=? AND disconnected_at IS NULL");
        $c->execute([$dj->id]);
        if ((int)$c->fetchColumn() > 0) return true;
    } catch (Exception $e) {}
    return false;
}

function station_slug($name, $id) {
    $s = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-'));
    return $s === '' ? (string)$id : $s;
}

// Queue + playlist lookup for a station (for red-highlight matching)
function station_song_list($pdo, $streamId) {
    $titles = [];
    // Desktop queue (dj_queue)
    $q = $pdo->prepare("SELECT title, artist FROM dj_queue WHERE stream_id=? ORDER BY position");
    $q->execute([$streamId]);
    foreach ($q->fetchAll(PDO::FETCH_OBJ) as $r) $titles[] = strtolower(trim($r->title ?? '')) . '|' . strtolower(trim($r->artist ?? ''));
    // Playlists (from autodj config playlist_ids)
    $compositeId = $streamId > 10000 ? $streamId : $streamId + 10000;
    $ac = $pdo->prepare("SELECT playlist_ids FROM radio_autodj_config WHERE station_id=?");
    $ac->execute([$compositeId]);
    $cfg = $ac->fetch(PDO::FETCH_OBJ);
    $plIds = $cfg ? json_decode($cfg->playlist_ids ?? '[]', true) : [];
    if (!empty($plIds)) {
        $ids = implode(',', array_map('intval', $plIds));
        $items = $pdo->query("SELECT title, artist FROM radio_playlist_items WHERE playlist_id IN ($ids)")->fetchAll(PDO::FETCH_OBJ);
        foreach ($items as $r) $titles[] = strtolower(trim($r->title ?? '')) . '|' . strtolower(trim($r->artist ?? ''));
    }
    return $titles;
}

// Current playing lookup per station
function station_current_song($pdo, $streamId) {
    $st = $pdo->prepare("SELECT current_song, current_artist FROM streaming_stations WHERE id=?");
    $st->execute([$streamId]);
    $s = $st->fetch(PDO::FETCH_OBJ);
    if (!$s) return '';
    $txt = trim(($s->current_artist ?? '') . ' ' . ($s->current_song ?? ''));
    return strtolower($txt);
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Song Requests — <?php echo htmlspecialchars($account->username); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,sans-serif;background:#02050e;color:#e0e0e0;min-height:100vh}
.header{background:rgba(8,16,28,.95);border-bottom:1px solid rgba(0,191,255,.08);padding:16px 24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
.header .logo{font-size:18px;font-weight:800}.header .logo span{color:#008cff}
.header .sub{font-size:12px;color:#64748b}
.container{max-width:1100px;margin:0 auto;padding:24px}
h1{font-size:22px;margin-bottom:4px}
.desc{color:#64748b;font-size:13px;margin-bottom:20px}
.station-block{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:16px;overflow:hidden;margin-bottom:22px}
.station-banner{height:90px;background:linear-gradient(135deg,rgba(0,140,255,.15),rgba(168,85,247,.08));display:flex;align-items:center;gap:14px;padding:0 20px;background-size:cover;background-position:center;position:relative}
.station-banner::before{content:'';position:absolute;inset:0;background:rgba(2,8,23,.35)}
.station-banner > *{position:relative}
.station-logo{width:52px;height:52px;border-radius:10px;object-fit:cover;border:2px solid rgba(0,191,255,.2);background:rgba(0,0,0,.3);flex-shrink:0}
.station-name{font-size:17px;font-weight:700}
.station-status{font-size:11px;font-weight:700;padding:4px 12px;border-radius:999px;margin-left:auto}
.status-live{background:rgba(74,222,128,.15);color:#4ade80}
.status-no-dj{background:rgba(248,113,113,.15);color:#f87171}
.status-autodj{background:rgba(250,204,21,.15);color:#facc15}
.station-body{padding:16px 20px}
.dj-row{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04);margin-bottom:10px;flex-wrap:wrap}
.dj-avatar{width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0}
.dj-info{flex:1;min-width:0}
.dj-name{font-weight:600;font-size:13px}
.dj-online{color:#4ade80;font-size:11px}
.dj-offline{color:#64748b;font-size:11px}
.dj-bio-link{font-size:11px;color:#38bdf8;text-decoration:none}
.queue-box{background:rgba(0,0,0,.3);border-radius:10px;padding:12px;margin-bottom:10px}
.queue-title{font-size:12px;font-weight:700;color:#e0e0e0;margin-bottom:8px}
.queue-item{display:flex;align-items:center;gap:6px;padding:4px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:12px;color:#94a3b8}
.queue-item:last-child{border-bottom:none}
.queue-item .ttl{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#cbd5e1}
.queue-item button{padding:3px 8px;border-radius:5px;background:rgba(56,189,248,.12);color:#38bdf8;border:none;cursor:pointer;font-size:10px;font-weight:600;flex-shrink:0}
.no-list{color:#64748b;font-size:12px;padding:6px 0}
.nowplaying{color:#94a3b8;font-size:12px;margin-bottom:8px}
.nowplaying b{color:#facc15}
.req-form{background:rgba(0,0,0,.3);border-radius:10px;padding:14px}
.req-form h3{font-size:13px;font-weight:700;margin-bottom:10px}
.req-form input{width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.4);color:#e0e0e0;font-size:12px;outline:none;margin-bottom:8px}
.req-form .row{display:flex;gap:8px}
.req-form .row input{flex:1}
.req-form button{width:100%;padding:10px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer}
.req-form .msg{display:none;margin-top:8px;font-size:12px;text-align:center;padding:6px;border-radius:6px}
.req-red{color:#f87171 !important;font-weight:700}
.pending-list{margin-top:10px}
.pending-item{display:flex;align-items:center;gap:8px;padding:6px;background:rgba(248,113,113,.06);border:1px solid rgba(248,113,113,.15);border-radius:8px;font-size:12px;color:#f87171;margin-bottom:4px}
.empty-note{color:#64748b;font-size:12px;text-align:center;padding:24px}
.audio{width:100%;height:36px;border-radius:8px;margin-top:8px}
</style></head><body>
<div class="header">
<div class="logo">🎧 Song <span>Requests</span></div>
<div class="sub"><?php echo htmlspecialchars($account->username); ?> · Planet Hosts Radio</div>
</div>
<div class="container">
<h1>Request a Song</h1>
<div class="desc">Pick a station below, see what the DJ is playing, and request a song. Songs already in the DJ's queue/playlist are normal — anything <span style="color:#f87171;font-weight:700">highlighted red</span> is NOT in their list, so they'll add or play it for you.</div>

<?php if (empty($stations)): ?>
<div class="empty-note">No stations on this account yet.</div>
<?php else: ?>

<?php foreach ($stations as $st):
    $sid = (int)$st->id;
    $slug = station_slug($st->name ?? "Station #{$sid}", $sid);
    // Pending requests for this station (only shown in the DJ's desktop app, not here)
    $pendingReqs = [];

    // Only show ONLINE DJs
    $onlineDjs = array_filter($djByStation[$sid] ?? [], fn($d) => dj_online($d, $pdo));
    $isOnline = !empty($onlineDjs);
    $songList = station_song_list($pdo, $sid);
    $currentTxt = station_current_song($pdo, $sid);
    $listenUrl = radio_ssl_stream_url($sid);

    // Station branding
    $brand = $pdo->prepare("SELECT brand_logo, brand_banner, brand_primary_color, brand_slogan FROM radio_branding WHERE station_id=?");
    $brand->execute([$sid]);
    $brandRow = $brand->fetch(PDO::FETCH_OBJ);
    $stLogo = !empty($brandRow->brand_logo) ? $base . $brandRow->brand_logo : '';
    $stBanner = !empty($brandRow->brand_banner) ? $base . $brandRow->brand_banner : '';
    $stColor = $brandRow->brand_primary_color ?? '#008cff';
    $stSlogan = $brandRow->brand_slogan ?? '';
?>
<div class="station-block" id="st-<?php echo $sid; ?>">
  <div class="station-banner" <?php if ($stBanner): ?>style="background-image:url('<?php echo htmlspecialchars($stBanner); ?>')"<?php endif; ?>>
    <?php if ($stLogo): ?><img class="station-logo" src="<?php echo htmlspecialchars($stLogo); ?>"><?php endif; ?>
    <div>
      <div class="station-name" style="color:<?php echo htmlspecialchars($stColor); ?>"><?php echo htmlspecialchars($st->name ?? "Station #$sid"); ?></div>
      <div style="font-size:11px;color:#94a3b8"><?php echo htmlspecialchars($stSlogan); ?> · <?php echo strtoupper(htmlspecialchars($st->engine ?? 'icecast')); ?></div>
    </div>
    <span class="station-status <?php echo $isOnline ? 'status-live' : (($st->status ?? '') === 'running' ? 'status-autodj' : 'status-no-dj'); ?>">
      <?php echo $isOnline ? '🔴 LIVE' : (($st->status ?? '') === 'running' ? '🎧 AutoDJ' : '○ Offline'); ?>
    </span>
  </div>
  <div class="station-body">
    <?php if (!empty($onlineDjs)): foreach ($onlineDjs as $dj): ?>
    <div class="dj-row">
      <?php if ($dj->avatar): ?><img class="dj-avatar" src="<?php echo $base . '/' . ltrim(htmlspecialchars($dj->avatar), '/'); ?>"><?php else: ?><div class="dj-avatar" style="display:flex;align-items:center;justify-content:center;background:rgba(74,222,128,.2);font-size:14px;color:#4ade80">🔴</div><?php endif; ?>
      <div class="dj-info">
        <div class="dj-name"><?php echo htmlspecialchars($dj->name ?: $dj->username); ?></div>
        <span class="dj-online">🔴 LIVE</span>
      </div>
    </div>
    <?php endforeach; endif; ?>

    <?php if (!$isOnline): ?>
    <div class="empty-note">😔 Sorry, No DJ is online right now. Enjoy the AutoDJ stream below.</div>
    <audio class="audio" src="<?php echo htmlspecialchars($listenUrl); ?>" controls preload="none"></audio>
    <?php endif; ?>

    <div class="nowplaying">🎵 Now Playing: <b><?php echo htmlspecialchars(($st->current_artist ?? '') ? ($st->current_artist . ' - ' . $st->current_song) : ($st->current_song ?? 'Waiting for source...')); ?></b></div>

    <?php if ($isOnline): ?>
    <div class="queue-box">
      <div class="queue-title">🎧 DJ Queue / Playlist</div>
      <?php if (empty($songList)): ?><div class="no-list">No queue loaded yet — the DJ's desktop app will sync it here.</div>
      <?php else: ?>
      <?php foreach (array_slice($songList, 0, 12) as $i => $key): $parts = explode('|', $key); $t = $parts[0]; $a = $parts[1] ?? ''; ?>
      <div class="queue-item"><span class="ttl"><?php echo htmlspecialchars(($a ? $a . ' — ' : '') . $t); ?></span><button onclick="prefill(<?php echo $sid; ?>,'<?php echo addslashes($a); ?>','<?php echo addslashes($t); ?>')">Request</button></div>
      <?php endforeach; endif; ?>
    </div>
    <?php endif; ?>

    <div class="req-form" data-station="<?php echo $sid; ?>" data-slug="<?php echo htmlspecialchars($slug); ?>">
      <h3>🎤 Request a Song</h3>
      <div class="row"><input id="rq-a-<?php echo $sid; ?>" placeholder="Artist" <?php if (!$isOnline) echo 'disabled'; ?>><input id="rq-t-<?php echo $sid; ?>" placeholder="Song Title" <?php if (!$isOnline) echo 'disabled'; ?>></div>
      <input id="rq-n-<?php echo $sid; ?>" placeholder="Your Name (optional)" <?php if (!$isOnline) echo 'disabled'; ?>>
      <button <?php if (!$isOnline) echo 'disabled'; ?> onclick="submitReq(<?php echo $sid; ?>)">Send Request</button>
      <div class="msg" id="rq-msg-<?php echo $sid; ?>"></div>
    </div>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<script>
var SONG_LISTS = <?php echo json_encode(array_map(function($st) use ($pdo) { $sid=(int)$st->id; return ['sid'=>$sid, 'list'=>station_song_list($pdo,$sid), 'current'=>station_current_song($pdo,$sid)]; }, $stations)); ?>;
function prefill(sid,a,t){document.getElementById('rq-a-'+sid).value=a;document.getElementById('rq-t-'+sid).value=t;document.getElementById('rq-t-'+sid).focus();}
function submitReq(sid){
  var a=document.getElementById('rq-a-'+sid),t=document.getElementById('rq-t-'+sid),n=document.getElementById('rq-n-'+sid),msg=document.getElementById('rq-msg-'+sid),f=document.querySelector('.req-form[data-station="'+sid+'"]');
  if(!t.value){msg.style.display='block';msg.style.background='rgba(248,113,113,.1)';msg.style.color='#f87171';msg.textContent='Song title required.';return;}
  // Red-highlight check: is it in the DJ's library/list? (partial match, like the desktop app)
  var qTitle = t.value.trim().toLowerCase(), qArtist = a.value.trim().toLowerCase();
  var inList = false;
  (SONG_LISTS||[]).forEach(function(s){
    if(s.sid!==sid) return;
    (s.list||[]).forEach(function(k){
      var parts=k.split('|'); var lt=parts[0]||'', la=parts[1]||'';
      if((qTitle && lt.indexOf(qTitle)>-1) || (qTitle && qTitle.indexOf(lt)>-1) || (qArtist && la.indexOf(qArtist)>-1)) { inList=true; }
    });
  });
  fetch('/connector/station/'+f.dataset.slug+'/requests',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({artist:a.value,title:t.value,guest_name:n.value})})
  .then(function(r){return r.json()}).then(function(d){
    msg.style.display='block';
    if(d.success){
      if(!inList){ msg.style.background='rgba(248,113,113,.15)';msg.style.color='#f87171';msg.textContent='🔴 Requested — NOT in the DJ list. They\'ll add or play it.'; }
      else { msg.style.background='rgba(74,222,128,.1)';msg.style.color='#4ade80';msg.textContent='✅ Request sent!'; }
      a.value='';t.value='';n.value='';
    } else { msg.style.background='rgba(248,113,113,.1)';msg.style.color='#f87171';msg.textContent=d.error||'Error.'; }
  }).catch(function(){msg.style.display='block';msg.style.background='rgba(248,113,113,.1)';msg.style.color='#f87171';msg.textContent='Connection error.';});
}
</script>
</body></html>

