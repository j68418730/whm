<style>
.r-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-bottom:14px}
.r-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:14px;text-align:center}
.r-stat .num{font-size:22px;font-weight:800}
.r-stat .lbl{font-size:10px;color:#64748b;margin-top:2px}
.r-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:18px;margin-bottom:12px}
.r-card h3{font-size:14px;font-weight:600;margin:0 0 10px}
.r-card h3 span{font-size:12px;color:#64748b;font-weight:400}
.nav-pills{display:flex;gap:2px;flex-wrap:wrap;margin-bottom:14px;background:rgba(8,16,28,.6);border-radius:8px;padding:3px}
.nav-pills a{padding:6px 12px;border-radius:6px;font-size:11px;text-decoration:none;color:#94a3b8;transition:.1s;white-space:nowrap}
.nav-pills a:hover{color:#e0e0e0;background:rgba(255,255,255,.04)}
.nav-pills a.active{color:#fff;background:rgba(0,140,255,.2)}
.tab{display:none}
.tab.active{display:block}
.nowplaying{display:flex;align-items:center;gap:14px;padding:14px;background:linear-gradient(135deg,rgba(0,140,255,.06),rgba(168,85,247,.04));border:1px solid rgba(0,191,255,.1);border-radius:12px;margin-bottom:14px}
.nowplaying .cover{width:56px;height:56px;border-radius:10px;background:linear-gradient(135deg,#0A84FF,#a855f7);display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0}
.nowplaying .info{flex:1;min-width:0}
.nowplaying .info .song{font-size:15px;font-weight:700}
.nowplaying .info .artist{font-size:11px;color:#64748b;margin-top:1px}
</style>

<?php $streams = $streams ?? []; $s = $streams[0] ?? null; ?>
<h2>📻 Radio Dashboard</h2>
<p style="color:#64748b;margin-bottom:14px">Manage your station, DJs, playlists, and listeners.</p>

<?php if (!$s): ?>
<div class="r-card" style="text-align:center;padding:30px"><h3>No Stream</h3><p style="color:#64748b">No radio streams assigned to your account.</p></div>
<?php else: $tab = $_GET['tab'] ?? 'overview'; ?>

<div class="nowplaying">
<div class="cover">📻</div>
<div class="info"><div class="song"><?php echo htmlspecialchars($s->current_song ?? 'Not Playing'); ?></div>
<div class="artist"><?php echo htmlspecialchars($s->server_name ?? 'Station'); ?> • <?php echo $s->status === 'running' ? '<span style="color:#4ade80">● Live</span>' : '<span style="color:#64748b">● Offline</span>'; ?>
 • Listeners: <strong><?php echo (int)($s->listeners_current ?? 0); ?></strong> / Peak: <strong><?php echo (int)($s->listeners_peak ?? 0); ?></strong></div></div>
<div style="display:flex;gap:4px;flex-shrink:0">
<a href="/user/radio/start/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.2);padding:5px 10px;border-radius:5px;text-decoration:none;font-size:10px">▶</a>
<a href="/user/radio/stop/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.2);padding:5px 10px;border-radius:5px;text-decoration:none;font-size:10px">⏹</a>
<a href="/user/radio/restart/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(250,204,21,.1);color:#facc15;border:1px solid rgba(250,204,21,.2);padding:5px 10px;border-radius:5px;text-decoration:none;font-size:10px">🔄</a>
</div></div>

<div class="r-grid">
<div class="r-stat"><div class="num" style="color:#0A84FF"><?php echo (int)($s->listeners_current ?? 0); ?></div><div class="lbl">Listeners</div></div>
<div class="r-stat"><div class="num" style="color:#38bdf8"><?php echo (int)($s->listeners_peak ?? 0); ?></div><div class="lbl">Peak</div></div>
<div class="r-stat"><div class="num" style="color:#a78bfa"><?php echo $s->bitrate ?? 128; ?>k</div><div class="lbl">Bitrate</div></div>
<div class="r-stat"><div class="num" style="color:#4ade80"><?php echo $s->status === 'running' ? 'Live' : 'Offline'; ?></div><div class="lbl">Status</div></div>
</div>

<div class="nav-pills">
<a href="?tab=overview" class="<?php echo $tab==='overview'?'active':'';?>">📊 Overview</a>
<a href="?tab=autodj" class="<?php echo $tab==='autodj'?'active':'';?>">🎵 AutoDJ</a>
<a href="?tab=playlists" class="<?php echo $tab==='playlists'?'active':'';?>">📂 Playlists</a>
<a href="?tab=library" class="<?php echo $tab==='library'?'active':'';?>">🎶 Library</a>
<a href="?tab=djs" class="<?php echo $tab==='djs'?'active':'';?>">🎧 DJs</a>
<a href="?tab=schedule" class="<?php echo $tab==='schedule'?'active':'';?>">📅 Schedule</a>
<a href="?tab=requests" class="<?php echo $tab==='requests'?'active':'';?>">🙋 Requests</a>
<a href="?tab=listeners" class="<?php echo $tab==='listeners'?'active':'';?>">👥 Listeners</a>
<a href="?tab=stats" class="<?php echo $tab==='stats'?'active':'';?>">📊 Stats</a>
<a href="?tab=widgets" class="<?php echo $tab==='widgets'?'active':'';?>">🧩 Widgets</a>
<a href="?tab=player" class="<?php echo $tab==='player'?'active':'';?>">▶ Player</a>
<a href="?tab=settings" class="<?php echo $tab==='settings'?'active':'';?>">⚙️ Settings</a>
</div>

<!-- Overview -->
<div class="tab <?php echo $tab==='overview'?'active':'';?>">
<div class="r-card"><h3>Station Info</h3>
<div style="display:grid;grid-template-columns:140px 1fr;gap:5px;font-size:12px">
<span style="color:#64748b">Name</span><span><?php echo htmlspecialchars($s->server_name ?? '-'); ?></span>
<span style="color:#64748b">Genre</span><span><?php echo htmlspecialchars($s->genre ?? '-'); ?></span>
<span style="color:#64748b">Status</span><span><span style="color:<?php echo $s->status==='running'?'#4ade80':'#64748b';?>">● <?php echo $s->status??'stopped'; ?></span></span>
<span style="color:#64748b">Current Song</span><span><?php echo htmlspecialchars($s->current_song ?? 'N/A'); ?></span>
<span style="color:#64748b">Current DJ</span><span><?php echo htmlspecialchars($s->current_dj ?? 'N/A'); ?></span>
<span style="color:#64748b">Stream URL</span><span><code style="font-size:10px">http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST']??'');?>:<?php echo $s->port??8000;?>/stream</code></span>
<span style="color:#64748b">Mount Point</span><span><code style="font-size:10px">/stream</code></span>
<span style="color:#64748b">Bitrate</span><span><?php echo $s->bitrate??128;?> kbps</span>
</div></div>
<div style="display:flex;gap:6px;flex-wrap:wrap">
<a href="/user/radio/start/<?php echo $s->id;?>" class="btn btn-sm" style="background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.2);padding:6px 14px;border-radius:6px;text-decoration:none">▶ Start</a>
<a href="/user/radio/stop/<?php echo $s->id;?>" class="btn btn-sm" style="background:rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.2);padding:6px 14px;border-radius:6px;text-decoration:none">⏹ Stop</a>
<a href="/user/radio/restart/<?php echo $s->id;?>" class="btn btn-sm" style="background:rgba(250,204,21,.1);color:#facc15;border:1px solid rgba(250,204,21,.2);padding:6px 14px;border-radius:6px;text-decoration:none">🔄 Restart</a>
<a href="http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST']??'');?>:<?php echo $s->port??8000;?>/stream" target="_blank" class="btn btn-sm" style="background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);padding:6px 14px;border-radius:6px;text-decoration:none">🔗 Listen Live</a>
</div></div>

<!-- AutoDJ -->
<div class="tab <?php echo $tab==='autodj'?'active':'';?>">
<div class="r-card"><h3>🎵 AutoDJ <span><?php echo $s->autodj_enabled ? '● Enabled' : '○ Disabled'; ?></span></h3>
<p style="font-size:12px;color:#64748b;margin-bottom:8px">AutoDJ plays music automatically when no live DJ is connected.</p>
<form method="POST" action="/user/radio/autodj/toggle/<?php echo $s->id;?>">
<button type="submit" class="btn btn-sm" style="background:<?php echo $s->autodj_enabled?'rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.2)':'rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.2)';?>;padding:6px 14px;border-radius:6px;cursor:pointer">
<?php echo $s->autodj_enabled ? '⏹ Disable AutoDJ' : '▶ Enable AutoDJ'; ?></button></form></div>
<div class="r-card"><h3>Now Playing</h3><p style="color:#64748b;font-size:12px">Current: <strong><?php echo htmlspecialchars($s->current_song ?? 'N/A');?></strong></p></div>
</div>

<!-- Playlists -->
<div class="tab <?php echo $tab==='playlists'?'active':'';?>">
<div class="r-card"><h3>📂 Playlists</h3>
<p style="color:#64748b;font-size:12px"><a href="/user/dj-manager" style="color:#0A84FF">Manage playlists in DJ Panel →</a></p></div></div>

<!-- Library -->
<div class="tab <?php echo $tab==='library'?'active':'';?>">
<div class="r-card"><h3>🎶 Music Library</h3>
<p style="color:#64748b;font-size:12px">Upload and manage your music files. Supported: MP3, AAC, OGG, FLAC, WAV</p>
<form style="display:flex;gap:6px;margin-top:8px"><input type="file" multiple style="flex:1;padding:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px">
<button class="btn btn-sm btn-primary">📤 Upload</button></form></div></div>

<!-- DJs -->
<div class="tab <?php echo $tab==='djs'?'active':'';?>">
<div class="r-card"><h3>🎧 DJ Accounts</h3>
<p style="font-size:12px;color:#64748b"><a href="/user/dj-manager" style="color:#0A84FF">Go to DJ Manager →</a></p></div></div>

<!-- Schedule -->
<div class="tab <?php echo $tab==='schedule'?'active':'';?>">
<div class="r-card"><h3>📅 Schedule</h3>
<p style="color:#64748b;font-size:12px">Schedule programming coming soon.</p></div></div>

<!-- Requests -->
<div class="tab <?php echo $tab==='requests'?'active':'';?>">
<div class="r-card"><h3>🙋 Song Requests</h3>
<p style="color:#64748b;font-size:12px">No pending requests.</p></div></div>

<!-- Listeners -->
<div class="tab <?php echo $tab==='listeners'?'active':'';?>">
<div class="r-card"><h3>👥 Listeners <span><?php echo (int)($s->listeners_current??0);?> connected</span></h3>
<p style="color:#64748b;font-size:12px">Listener details appear when stream is active.</p></div></div>

<!-- Stats -->
<div class="tab <?php echo $tab==='stats'?'active':'';?>">
<div class="r-card"><h3>📊 Statistics</h3>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:10px">
<div style="text-align:center"><div style="font-size:20px;font-weight:700;color:#0A84FF">--</div><div style="font-size:10px;color:#64748b">Today</div></div>
<div style="text-align:center"><div style="font-size:20px;font-weight:700;color:#38bdf8">--</div><div style="font-size:10px;color:#64748b">Week</div></div>
<div style="text-align:center"><div style="font-size:20px;font-weight:700;color:#a78bfa">--</div><div style="font-size:10px;color:#64748b">Month</div></div>
</div>
<p style="color:#64748b;font-size:12px"><a href="/user/stats" style="color:#0A84FF">Detailed Statistics →</a></p></div></div>

<!-- Widgets -->
<div class="tab <?php echo $tab==='widgets'?'active':'';?>">
<div class="r-card"><h3>🧩 Widgets</h3>
<?php $host = $_SERVER['HTTP_HOST']??'localhost'; $streamId = $s->id; $port = $s->port??8000; ?>
<div style="margin-bottom:8px"><div style="font-size:11px;color:#64748b;margin-bottom:2px">Now Playing</div>
<textarea rows="2" style="width:100%;font-size:10px;font-family:monospace;padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#4ade80;outline:none" readonly>&lt;script src="http://<?php echo $host;?>/radio/widgets/nowplaying.php?stream=<?php echo $streamId;?>"&gt;&lt;/script&gt;</textarea></div>
<div style="margin-bottom:8px"><div style="font-size:11px;color:#64748b;margin-bottom:2px">Listener Count</div>
<textarea rows="2" style="width:100%;font-size:10px;font-family:monospace;padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#4ade80;outline:none" readonly>&lt;script src="http://<?php echo $host;?>/radio/widgets/listeners.php?stream=<?php echo $streamId;?>"&gt;&lt;/script&gt;</textarea></div>
<div style="margin-bottom:8px"><div style="font-size:11px;color:#64748b;margin-bottom:2px">HTML5 Player Embed</div>
<textarea rows="2" style="width:100%;font-size:10px;font-family:monospace;padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#4ade80;outline:none" readonly>&lt;audio controls&gt;&lt;source src="http://<?php echo $host;?>:<?php echo $port;?>/stream" type="audio/mpeg"&gt;&lt;/audio&gt;</textarea></div>
</div></div>

<!-- Player -->
<div class="tab <?php echo $tab==='player'?'active':'';?>">
<div class="r-card" style="text-align:center"><h3>▶ Stream Player</h3>
<audio controls style="width:100%;margin:10px 0"><source src="http://<?php echo $host;?>:<?php echo $port;?>/stream" type="audio/mpeg"></audio>
<p style="font-size:12px;color:#64748b"><code><?php echo $s->bitrate??128;?> kbps</code> stream</p></div></div>

<!-- Settings -->
<div class="tab <?php echo $tab==='settings'?'active':'';?>">
<div class="r-card"><h3>⚙️ Stream Settings</h3>
<div style="display:grid;grid-template-columns:140px 1fr;gap:5px;font-size:12px">
<span style="color:#64748b">Name</span><span><?php echo htmlspecialchars($s->server_name??'-');?></span>
<span style="color:#64748b">Description</span><span><?php echo htmlspecialchars($s->description??'-');?></span>
<span style="color:#64748b">Genre</span><span><?php echo htmlspecialchars($s->genre??'-');?></span>
<span style="color:#64748b">Bitrate</span><span><?php echo $s->bitrate??128;?> kbps</span>
<span style="color:#64748b">Port</span><span><?php echo $s->port??8000;?></span>
</div></div></div>

<?php endif; ?>
