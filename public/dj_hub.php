<?php
// DJ V2 Hub — Scheduling, Public List, Listen Live, Messages, Apps
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

$action = $_GET['action'] ?? 'list';

// ─── PUBLIC DJ LISTING ───
if ($action === 'public') {
    $streamId = (int)($_GET['stream'] ?? 0);
    if (!$streamId) { $streamId = $pdo->query("SELECT MIN(id) FROM radio_streams")->fetchColumn(); }
    $djs = $pdo->prepare("SELECT * FROM radio_djs WHERE stream_id = ? AND status='active' ORDER BY name");
    $djs->execute([$streamId]);
    $djList = $djs->fetchAll(PDO::FETCH_OBJ);
    ?>
    <!DOCTYPE html><html><head><title>Our DJs</title><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body{background:#02050e;color:#fff;font-family:Inter;padding:20px}.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:16px}.card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:20px;text-align:center}.avatar{width:80px;height:80px;border-radius:50%;margin:0 auto 10px;border:3px solid rgba(0,191,255,.2);object-fit:cover;background:rgba(0,0,0,.3)}.name{font-size:18px;font-weight:700}.bio{color:#64748b;font-size:13px;margin:6px 0}.links a{color:#38bdf8;text-decoration:none;font-size:12px}.btn{padding:8px 20px;background:linear-gradient(135deg,#008cff,#3bb8ff);border:none;border-radius:8px;color:#fff;text-decoration:none;display:inline-block;font-size:13px;margin-top:8px}h1{font-size:24px;margin-bottom:16px}h1 span{color:#008cff}</style></head><body>
    <h1>🎤 Our <span>DJs</span></h1>
    <div class="grid"><?php foreach ($djList as $d): ?>
    <div class="card"><img src="<?php echo $d->avatar && file_exists($d->avatar) ? '/'.$d->avatar : '/theme/assets/img/avatars/vistor.png'; ?>" class="avatar">
    <div class="name"><?php echo htmlspecialchars($d->name ?: $d->username); ?></div>
    <div class="bio"><?php echo htmlspecialchars(substr($d->bio ?? '', 0, 100)); ?></div>
    <div class="links"><?php if ($d->website_url): ?><a href="<?php echo htmlspecialchars($d->website_url); ?>" target="_blank">🌐 Website</a><?php endif; ?></div>
    <a href="/dj_panel.php?action=schedule&username=<?php echo urlencode($d->username); ?>" class="btn">📅 Schedule</a>
    <?php if ($d->banner && file_exists($d->banner)): ?><div style="margin-top:8px"><img src="/<?php echo $d->banner; ?>" style="width:100%;border-radius:6px;max-height:60px;object-fit:cover"></div><?php endif; ?>
    </div><?php endforeach; ?></div></body></html>
    <?php exit;
}

// ─── PUBLIC SCHEDULE ───
if ($action === 'public_schedule') {
    $streamId = (int)($_GET['stream'] ?? 0);
    $week = $_GET['week'] ?? date('Y-m-d', strtotime('monday this week'));
    $scheds = $pdo->prepare("SELECT s.*, d.name as dj_name, d.username as dj_user, d.avatar
        FROM radio_dj_schedule s JOIN radio_djs d ON s.dj_id = d.id
        WHERE s.stream_id = ? AND s.status='booked' AND s.scheduled_date >= ? AND s.scheduled_date < DATE_ADD(?, INTERVAL 7 DAY)
        ORDER BY s.scheduled_date, s.time_slot");
    $scheds->execute([$streamId, $week, $week]);
    $list = $scheds->fetchAll(PDO::FETCH_OBJ);
    $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    ?>
    <!DOCTYPE html><html><head><title>DJ Schedule</title><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body{background:#02050e;color:#fff;font-family:Inter;padding:20px}.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px}.day-card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.1);border-radius:8px;padding:12px}.day-card h3{font-size:13px;color:var(--accent,#008cff);margin-bottom:6px}.slot{font-size:11px;padding:4px 6px;background:rgba(0,140,255,.08);border-radius:4px;margin-bottom:2px;color:#cbd5e1}h1{font-size:22px;margin-bottom:16px}h1 span{color:#008cff}.nav a{color:#38bdf8;text-decoration:none;font-size:13px}</style></head><body>
    <h1>📅 DJ <span>Schedule</span> <span style="font-size:14px;color:#64748b;font-weight:400"><?php echo date('M j', strtotime($week)); ?> - <?php echo date('M j, Y', strtotime($week . ' +6 days')); ?></span></h1>
    <div class="nav"><?php
    $prev = date('Y-m-d', strtotime($week . ' -7 days'));
    $next = date('Y-m-d', strtotime($week . ' +7 days'));
    ?><a href="?action=public_schedule&week=<?php echo $prev; ?>">← Previous Week</a> · <a href="?action=public_schedule&week=<?php echo $next; ?>">Next Week →</a></div>
    <div class="grid"><?php foreach ($days as $i => $day):
    $date = date('Y-m-d', strtotime($week . " +{$i} days"));
    $daySlots = array_filter($list, fn($s) => $s->scheduled_date == $date);
    ?><div class="day-card"><h3><?php echo $day; ?><br><span style="font-weight:400;font-size:10px;color:#64748b"><?php echo date('M j', strtotime($date)); ?></span></h3>
    <?php if (empty($daySlots)): ?><div style="font-size:10px;color:#475569">No shows</div><?php endif; ?>
    <?php foreach ($daySlots as $s): ?><div class="slot"><?php echo htmlspecialchars($s->time_slot); ?> — <?php echo htmlspecialchars($s->dj_name ?: $s->dj_user); ?></div><?php endforeach; ?>
    </div><?php endforeach; ?></div></body></html>
    <?php exit;
}

// ─── LISTEN LIVE ───
if ($action === 'listen') {
    $streamId = (int)($_GET['stream'] ?? 0);
    $stream = $pdo->prepare("SELECT * FROM radio_streams WHERE id = ?");
    $stream->execute([$streamId]);
    $s = $stream->fetch(PDO::FETCH_OBJ);
    $dj = $s ? $pdo->prepare("SELECT name, username FROM radio_djs WHERE stream_id = ? AND status='active' AND username = ? LIMIT 1") : null;
    if ($s) { $dj->execute([$s->id, $s->current_dj]); $currentDj = $dj->fetch(PDO::FETCH_OBJ); }
    ?>
    <!DOCTYPE html><html><head><title>Listen Live</title><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body{background:#02050e;color:#fff;font-family:Inter;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0}.card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;text-align:center;max-width:400px;width:92%}.icon{font-size:64px;margin-bottom:12px}h1{font-size:22px;margin-bottom:4px}h1 span{color:#008cff}.status{font-size:13px;color:#64748b;margin-bottom:16px}.btn{padding:12px 28px;background:linear-gradient(135deg,#008cff,#3bb8ff);border:none;border-radius:8px;color:#fff;font-weight:700;cursor:pointer;text-decoration:none;display:inline-block;font-size:15px}audio{width:100%;margin-top:16px;border-radius:8px}</style></head><body>
    <div class="card"><div class="icon">📻</div>
    <h1>Listen <span>Live</span></h1>
    <div class="status"><?php if (isset($currentDj)): ?>🎤 Live with <?php echo htmlspecialchars($currentDj->name ?: $currentDj->username); ?><?php else: ?>🤖 AutoDJ Playing<?php endif; ?></div>
    <?php if ($s && $s->status === 'running'): ?>
    <audio controls autoplay><source src="http://<?php echo $_SERVER['SERVER_NAME'] ?? 'planet-hosts.com'; ?>:<?php echo $s->port; ?>/stream.ogg" type="audio/ogg">
    Your browser does not support audio. Use port <?php echo $s->port; ?> in your media player.</audio>
    <?php else: ?><p style="color:#f87171">Stream is offline</p><?php endif; ?>
    <br><br><a href="/radio/nowplaying.php?stream=<?php echo $streamId; ?>" target="_blank" style="color:#38bdf8;font-size:12px">🎵 Now Playing</a>
    </div></body></html>
    <?php exit;
}

echo 'DJ Hub loaded. Use ?action=public, ?action=public_schedule, or ?action=listen';

