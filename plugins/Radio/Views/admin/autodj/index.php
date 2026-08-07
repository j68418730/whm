<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<style>
.ad-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:20px}
.ad-stat{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:10px;padding:16px;text-align:center}
.ad-stat .v{font-size:26px;font-weight:800}.ad-stat .l{font-size:11px;color:var(--text_muted,#64748b);margin-top:2px}
.ad-stat .v.blue{color:#38bdf8}.ad-stat .v.green{color:#4ade80}.ad-stat .v.yellow{color:#facc15}.ad-stat .v.purple{color:#a855f7}
.ad-group{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:12px;padding:16px;margin-bottom:18px}
.ad-group .gh{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px}
.ad-group .gh h3{margin:0;font-size:15px;color:#e0e0e0}
.ad-group .gh h3 span{color:var(--primary,#008cff)}
.ad-group .gh .gmeta{font-size:11px;color:var(--text_muted,#64748b)}
.ad-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px}
.ad-card{background:rgba(0,0,0,.3);border:1px solid var(--border,rgba(0,191,255,.08));border-radius:10px;padding:14px;display:flex;flex-direction:column;gap:8px}
.ad-card .row1{display:flex;align-items:center;justify-content:space-between;gap:8px}
.ad-card .name{font-size:14px;font-weight:600;color:#e0e0e0}
.ad-card .desc{font-size:11px;color:var(--text_muted,#64748b)}
.ad-card .meta{display:flex;gap:12px;font-size:11px;color:var(--text_muted,#64748b);flex-wrap:wrap}
.ad-card .meta b{color:#cbd5e1}
.ad-badge{padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700}
.ad-badge.running{background:rgba(74,222,128,.15);color:#4ade80}
.ad-badge.stopped{background:rgba(248,113,113,.15);color:#f87171}
.ad-badge.engine{background:rgba(0,140,255,.12);color:#38bdf8}
.ad-card .acts{display:flex;gap:6px;flex-wrap:wrap;margin-top:auto}
.ad-card .acts a{padding:5px 12px;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;border:1px solid var(--border,rgba(0,191,255,.1));color:#e0e0e0;transition:.1s}
.ad-card .acts a.start{background:rgba(74,222,128,.12);color:#4ade80}
.ad-card .acts a.stop{background:rgba(248,113,113,.12);color:#f87171}
.ad-card .acts a:hover{opacity:.85}
.empty{padding:40px;text-align:center;color:var(--text_muted,#64748b)}
.ad-top{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.ad-top a{padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;background:rgba(0,140,255,.12);color:#0A84FF;border:1px solid rgba(0,140,255,.25)}
.ad-top a:hover{background:rgba(0,140,255,.2)}
</style>

<h2 style="margin-bottom:6px">🤖 AutoDJ Management</h2>
<p style="color:var(--text_muted,#64748b);margin-bottom:16px">AutoDJ players grouped by the hosting account that owns each station.</p>

<div class="ad-top">
  <a href="/admin/autodj/upload">📁 Upload Music</a>
  <a href="/admin/autodj/library">🎵 Music Library</a>
  <a href="/admin/autodj/playlists">📋 Playlists</a>
  <a href="/admin/radio_dashboard">📻 Radio Dashboard</a>
  <a href="/admin/streams">🔊 Streams</a>
</div>

<?php
$autodjs = $autodjs ?? [];
$totalStations = count($autodjs);
$runningCount = count(array_filter($autodjs, fn($s) => !empty($s->autodj_running)));
$totalSongs = array_sum(array_map(fn($s) => (int)($s->song_count ?? 0), $autodjs));
$totalPlaylists = array_sum(array_map(fn($s) => (int)($s->playlist_count ?? 0), $autodjs));
$grouped = [];
foreach ($autodjs as $s) {
    $key = $s->user_username ?: 'Unassigned';
    $grouped[$key][] = $s;
}
?>

<div class="ad-stats">
  <div class="ad-stat"><div class="v blue"><?php echo $totalStations; ?></div><div class="l">Stations</div></div>
  <div class="ad-stat"><div class="v green"><?php echo $runningCount; ?></div><div class="l">AutoDJ Running</div></div>
  <div class="ad-stat"><div class="v yellow"><?php echo $totalSongs; ?></div><div class="l">Total Songs</div></div>
  <div class="ad-stat"><div class="v purple"><?php echo $totalPlaylists; ?></div><div class="l">Playlists</div></div>
</div>

<?php if (empty($grouped)): ?>
<div class="empty">No streaming stations found. Create a stream first under <a href="/admin/streams" style="color:#0A84FF">Streams</a>.</div>
<?php else: ?>
<?php foreach ($grouped as $username => $userStations): ?>
<div class="ad-group">
  <div class="gh">
    <h3>👤 <span><?php echo htmlspecialchars($username); ?></span></h3>
    <div class="gmeta">
      <?php echo count($userStations); ?> station(s) ·
      <?php echo array_sum(array_map(fn($s) => (int)($s->song_count ?? 0), $userStations)); ?> songs ·
      <?php echo count(array_filter($userStations, fn($s) => !empty($s->autodj_running))); ?> running
    </div>
  </div>
  <div class="ad-cards">
    <?php foreach ($userStations as $s): ?>
    <div class="ad-card">
      <div class="row1">
        <span class="name"><?php echo htmlspecialchars($s->name ?: 'Station #' . $s->id); ?></span>
        <span class="ad-badge <?php echo !empty($s->autodj_running) ? 'running' : 'stopped'; ?>"><?php echo !empty($s->autodj_running) ? '● Running' : '○ Stopped'; ?></span>
      </div>
      <span class="ad-badge engine" style="align-self:flex-start"><?php echo htmlspecialchars($s->engine ?? $s->server_type ?? 'icecast'); ?></span>
      <div class="desc"><?php echo htmlspecialchars($s->description ?: ''); ?></div>
      <div class="meta">
        <span>🎵 <b><?php echo (int)($s->song_count ?? 0); ?></b></span>
        <span>📋 <b><?php echo (int)($s->playlist_count ?? 0); ?></b></span>
        <span>📻 <b><?php echo (int)($s->listener_count ?? 0); ?></b></span>
        <span>🎧 <b><?php echo (int)($s->bitrate ?? 128); ?>kbps</b></span>
      </div>
      <div class="meta">Now: <b><?php echo htmlspecialchars($s->current_song ?: '—'); ?></b></div>
      <div class="acts">
        <a class="start" href="/user/radio/autodj/start/<?php echo 10000 + (int)$s->id; ?>" <?php if (!empty($s->autodj_running)): ?>style="opacity:.4;pointer-events:none"<?php endif; ?>>▶ Start</a>
        <a class="stop" href="/user/radio/autodj/stop/<?php echo 10000 + (int)$s->id; ?>" <?php if (empty($s->autodj_running)): ?>style="opacity:.4;pointer-events:none"<?php endif; ?>>⏹ Stop</a>
        <a href="/admin/streams/edit/<?php echo (int)$s->id; ?>">✏️ Edit</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
