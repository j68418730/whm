<?php
require_once __DIR__ . '/../security_guard.php';
security_guard_run('radio');
require_once __DIR__ . '/radio_helper.php';
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? $_GET['id'] ?? 0);
$scroll = $_GET['scroll'] ?? 'yes';
if (!$streamId) { echo '<!-- No stream ID -->'; exit; }

$stream = radio_get_stream($streamId);
if (!$stream) { echo '<!-- Stream not found -->'; exit; }

$stats = radio_fetch_stats($stream);
$song = $stats['song'] ?: 'Waiting for source...';
$artist = $stats['artist'];
$online = $stats['status'];
$liveDj = $stats['live_dj'];
$listeners = $stats['listeners'];
$bitrate = $stats['bitrate'];

// Get last 10 songs from streaming history if the table exists
$songsList = [];
try {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $h = $pdo->prepare("SELECT * FROM radio_song_history WHERE stream_id = ? ORDER BY played_at DESC LIMIT 10");
    $h->execute([(int)$stream->id]);
    $songsList = $h->fetchAll(PDO::FETCH_OBJ) ?: [];
} catch (\Exception $e) {}

// Pending requests count (only if table exists)
$requestCount = 0;
try {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $reqs = $pdo->prepare("SELECT COUNT(*) FROM radio_requests WHERE stream_id = ? AND status = 'pending'");
    $reqs->execute([(int)$stream->id]);
    $requestCount = (int)$reqs->fetchColumn();
} catch (\Exception $e) {}

$scrollStyle = $scroll === 'no' ? 'overflow-y:visible;max-height:none' : 'overflow-y:auto;max-height:300px';
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif}
body{background:#0a0e1a;color:#e0e0e0;padding:12px;font-size:13px}
.widget{background:rgba(8,16,28,.8);border:1px solid rgba(0,191,255,.12);border-radius:10px;padding:14px}
.section{margin-bottom:12px}
.section:last-child{margin-bottom:0}
.label{font-size:10px;text-transform:uppercase;color:#64748b;letter-spacing:1px;margin-bottom:4px;font-weight:600}
.value{font-size:14px;font-weight:600;color:#fff}
.status-dot{display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:6px}
.live{background:#4ade80;animation:pulse 1.5s infinite}
.autodj{background:#facc15}
.offline{background:#f87171}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.song-list{<?php echo $scrollStyle; ?>}
.song-item{padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:12px;display:flex;justify-content:space-between;gap:8px}
.song-item:last-child{border-bottom:none}
.song-title{color:#e0e0e0;font-weight:500}
.song-time{color:#64748b;font-size:10px;white-space:nowrap}
.requests{display:flex;gap:6px;align-items:center;margin-top:6px;font-size:12px;color:#94a3b8}
</style>
<div class="widget">
<div class="section">
<div class="label">Now Playing</div>
<div class="value"><?php echo htmlspecialchars($artist ? $artist . ' - ' . $song : $song); ?></div>
</div>

<div class="section">
<div class="label">DJ / Source</div>
<div class="value">
<?php if ($liveDj): ?>
<span class="status-dot live"></span> <?php echo htmlspecialchars($liveDj); ?>
<?php elseif (!empty($stream->autodj_enabled)): ?>
<span class="status-dot autodj"></span> AutoDJ
<?php elseif ($online): ?>
<span class="status-dot live"></span> Live
<?php else: ?>
<span class="status-dot offline"></span> Offline
<?php endif; ?>
</div>
</div>

<?php if (!empty($songsList)): ?>
<div class="section">
<div class="label">Last 10 Songs</div>
<div class="song-list">
<?php foreach ($songsList as $s): ?>
<div class="song-item">
<span class="song-title"><?php echo htmlspecialchars(($s->artist ?? '') ? ($s->artist . ' - ' . $s->title) : ($s->title ?? '')); ?></span>
<span class="song-time"><?php echo date('H:i', strtotime($s->played_at ?? 'now')); ?></span>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<?php if ($requestCount > 0): ?>
<div class="section">
<div class="requests">🎵 <?php echo $requestCount; ?> song request<?php echo $requestCount > 1 ? 's' : ''; ?> pending</div>
</div>
<?php endif; ?>
</div>
