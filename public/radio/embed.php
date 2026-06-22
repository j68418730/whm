<?php
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
if (!$streamId) exit;
try {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $pdo->exec("CREATE TABLE IF NOT EXISTS radio_song_history (
        id INT AUTO_INCREMENT PRIMARY KEY, stream_id INT, title VARCHAR(255),
        artist VARCHAR(255), duration INT DEFAULT 0, played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $s = $pdo->prepare("SELECT * FROM radio_streams WHERE id = ?");
    $s->execute([$streamId]);
    $stream = $s->fetch(PDO::FETCH_OBJ);
} catch (Exception $e) { $stream = null; }
if (!$stream) exit;
$name = htmlspecialchars($stream->server_name ?? 'Radio');
$port = (int)($stream->port ?? 8000);
$mount = htmlspecialchars($stream->mount_point ?? '/live');
$sUrl = "http://45.61.59.55:{$port}{$mount}";
$status = $stream->status === 'running';
$listeners = (int)($stream->listener_count ?? 0);
$bitrate = (int)($stream->bitrate ?? 128);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo $name; ?> Player</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Inter,sans-serif;background:#02050e;color:#fff;display:flex;justify-content:center;align-items:center;min-height:100vh}
.player{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:30px;max-width:360px;width:100%;text-align:center}
.logo{font-size:18px;font-weight:800;margin-bottom:4px;color:#e0e0e0}.logo span{color:#008cff}
.status{font-size:12px;margin-bottom:16px;padding:4px 12px;border-radius:12px;display:inline-block}
.status.online{background:rgba(74,222,128,.12);color:#4ade80}
.status.offline{background:rgba(248,113,113,.12);color:#f87171}
.song{font-size:16px;font-weight:600;margin:12px 0 4px}
.artist{font-size:13px;color:#94a3b8;margin-bottom:16px}
.stats{display:flex;justify-content:center;gap:16px;font-size:12px;color:#64748b;margin-bottom:16px}
.stats span{color:#38bdf8;font-weight:600}
.controls{display:flex;gap:8px}.controls button{flex:1;padding:10px;border-radius:8px;border:none;font-weight:600;cursor:pointer;font-size:13px;font-family:inherit;transition:.2s}
.play{background:#008cff;color:#fff}.play:hover{opacity:.9}
.pause{background:rgba(255,255,255,.06);color:#e0e0e0;border:1px solid rgba(255,255,255,.08)}.pause:hover{background:rgba(255,255,255,.1)}
.progress{height:4px;background:rgba(255,255,255,.06);border-radius:2px;margin:14px 0 6px;overflow:hidden}
.progress-bar{height:100%;width:0%;background:linear-gradient(90deg,#008cff,#3bb8ff);border-radius:2px;transition:width 1s}
</style></head><body>
<div class="player">
<div class="logo">PLANET <span>HOSTS</span></div>
<div class="status <?php echo $status ? 'online' : 'offline'; ?>">● <?php echo $status ? 'LIVE' : 'OFFLINE'; ?></div>
<div class="song" id="songDisplay"><?php echo htmlspecialchars($stream->current_song ?? 'Not Playing'); ?></div>
<div class="artist" id="artistDisplay"><?php echo htmlspecialchars($stream->current_artist ?? ''); ?></div>
<div class="stats"><span id="listenerCount"><?php echo $listeners; ?></span> Listeners &middot; <span><?php echo $bitrate; ?>kbps</span></div>
<audio id="audioPlayer" src="<?php echo $sUrl; ?>" preload="none"></audio>
<div class="controls">
<button class="play" onclick="document.getElementById('audioPlayer').play()">&#9654; Play</button>
<button class="pause" onclick="document.getElementById('audioPlayer').pause()">&#9646;&#9646; Pause</button>
</div>
<div class="progress"><div class="progress-bar" id="progressBar"></div></div>
</div>
<script>
var audio = document.getElementById('audioPlayer');
audio.addEventListener('timeupdate', function() {
    if (audio.duration) document.getElementById('progressBar').style.width = (audio.currentTime / audio.duration * 100) + '%';
});
</script>
</body></html>
