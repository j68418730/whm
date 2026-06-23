<style>
.r-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:16px}
.r-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;text-align:center}
.r-stat .num{font-size:24px;font-weight:800}
.r-stat .lbl{font-size:10px;color:#64748b;margin-top:2px}
.r-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:18px;margin-bottom:14px}
.r-card h3{font-size:14px;font-weight:600;margin:0 0 10px}
.r-card h3 span{font-size:12px;color:#64748b;font-weight:400}
.tab-bar{display:flex;gap:0;border-bottom:1px solid rgba(255,255,255,.06);margin-bottom:14px;flex-wrap:wrap}
.tab{padding:8px 14px;font-size:11px;cursor:pointer;color:#64748b;border-bottom:2px solid transparent;transition:.15s}
.tab:hover{color:#e0e0e0}
.tab.active{color:#0A84FF;border-bottom-color:#0A84FF}
.tab-content{display:none}
.tab-content.active{display:block}
.tbl{width:100%;border-collapse:collapse;font-size:12px}
.tbl th{text-align:left;padding:7px 10px;color:#64748b;font-size:10px;text-transform:uppercase;border-bottom:1px solid rgba(255,255,255,.06)}
.tbl td{padding:7px 10px;border-bottom:1px solid rgba(255,255,255,.04)}
.nowplaying{display:flex;align-items:center;gap:14px;padding:12px;background:rgba(0,0,0,.3);border-radius:8px;margin-bottom:10px}
.nowplaying .cover{width:60px;height:60px;border-radius:8px;background:linear-gradient(135deg,#0A84FF,#a855f7);display:flex;align-items:center;justify-content:center;font-size:24px}
.nowplaying .info{flex:1}
.nowplaying .info .song{font-size:16px;font-weight:700}
.nowplaying .info .artist{font-size:12px;color:#64748b}
</style>

<?php $streams = $streams ?? []; $s = $streams[0] ?? null; ?>

<h2>📻 Radio Dashboard</h2>
<p style="color:#64748b;margin-bottom:16px">Manage your radio station, streams, DJs, and music.</p>

<?php if (!$s): ?>
<div class="r-card" style="text-align:center;padding:40px"><h3>No Radio Streams</h3><p style="color:#64748b">You don't have any radio streams yet.</p></div>
<?php else: ?>

<div class="nowplaying">
<div class="cover">📻</div>
<div class="info"><div class="song"><?php echo htmlspecialchars($s->current_song ?? 'Not Playing'); ?></div>
<div class="artist"><?php echo htmlspecialchars($s->server_name ?? 'Station'); ?> • <?php echo $s->status === 'running' ? '<span style="color:#4ade80">● Live</span>' : '<span style="color:#f87171">● Offline</span>'; ?></div></div>
<div style="text-align:right;font-size:12px;color:#64748b">
<div>Listeners: <strong style="color:#e0e0e0"><?php echo (int)($s->listeners_current ?? 0); ?></strong></div>
<div>Peak: <strong style="color:#e0e0e0"><?php echo (int)($s->listeners_peak ?? 0); ?></strong></div>
</div>
</div>

<div class="r-grid">
<div class="r-stat"><div class="num" style="color:#4ade80"><?php echo $s->status === 'running' ? 'Live' : 'Offline'; ?></div><div class="lbl">Status</div></div>
<div class="r-stat"><div class="num" style="color:#0A84FF"><?php echo (int)($s->listeners_current ?? 0); ?></div><div class="lbl">Listeners</div></div>
<div class="r-stat"><div class="num" style="color:#38bdf8"><?php echo (int)($s->listeners_peak ?? 0); ?></div><div class="lbl">Peak</div></div>
<div class="r-stat"><div class="num" style="color:#a78bfa"><?php echo $s->bitrate ?? 128; ?>k</div><div class="lbl">Bitrate</div></div>
</div>

<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px">
<a href="/user/radio/start/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.2);padding:6px 14px;border-radius:6px;text-decoration:none;font-size:12px">▶ Start</a>
<a href="/user/radio/stop/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.2);padding:6px 14px;border-radius:6px;text-decoration:none;font-size:12px">⏹ Stop</a>
<a href="/user/radio/restart/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(250,204,21,.1);color:#facc15;border:1px solid rgba(250,204,21,.2);padding:6px 14px;border-radius:6px;text-decoration:none;font-size:12px">🔄 Restart</a>
<a href="http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>:<?php echo $s->port ?? 8000; ?>/stream" target="_blank" class="btn btn-sm" style="background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);padding:6px 14px;border-radius:6px;text-decoration:none;font-size:12px">🔗 Listen</a>
</div>

<div class="tab-bar">
<div class="tab active" onclick="showTab('overview',this)">📊 Overview</div>
<div class="tab" onclick="showTab('autodj',this)">🤖 AutoDJ</div>
<div class="tab" onclick="showTab('playlists',this)">📂 Playlists</div>
<div class="tab" onclick="showTab('djs',this)">🎧 DJs</div>
<div class="tab" onclick="showTab('listeners',this)">👥 Listeners</div>
<div class="tab" onclick="showTab('widgets',this)">🧩 Widgets</div>
<div class="tab" onclick="showTab('player',this)">▶ Player</div>
</div>

<div id="tab-overview" class="tab-content active">
<div class="r-card"><h3>Station Info</h3>
<table class="tbl"><tbody>
<tr><td style="color:#64748b">Name</td><td><?php echo htmlspecialchars($s->server_name ?? 'N/A'); ?></td></tr>
<tr><td style="color:#64748b">Genre</td><td><?php echo htmlspecialchars($s->genre ?? 'N/A'); ?></td></tr>
<tr><td style="color:#64748b">Stream URL</td><td><code style="font-size:11px">http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>:<?php echo $s->port ?? 8000; ?>/stream</code></td></tr>
<tr><td style="color:#64748b">Mount Point</td><td><code style="font-size:11px">/stream</code></td></tr>
<tr><td style="color:#64748b">Bitrate</td><td><?php echo $s->bitrate ?? 128; ?> kbps</td></tr>
<tr><td style="color:#64748b">Status</td><td><span style="color:<?php echo $s->status === 'running' ? '#4ade80' : '#f87171'; ?>">● <?php echo $s->status ?? 'stopped'; ?></span></td></tr>
</tbody></table></div>
<div class="r-card"><h3>Quick Links</h3>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<a href="/user/dj-manager" style="color:#0A84FF;font-size:12px">🎤 DJ Panel</a>
<a href="/user/stats" style="color:#0A84FF;font-size:12px">📈 Statistics</a>
<a href="/user/tickets" style="color:#0A84FF;font-size:12px">🎫 Support</a>
</div></div>
</div>

<div id="tab-autodj" class="tab-content">
<div class="r-card"><h3>🤖 AutoDJ <span><?php echo $s->autodj_enabled ? '● Enabled' : '○ Disabled'; ?></span></h3>
<?php if ($s->autodj_enabled): ?>
<p style="font-size:12px;color:#4ade80">AutoDJ is running — playing from playlist: <strong><?php echo htmlspecialchars($s->autodj_playlist ?? 'Default'); ?></strong></p>
<?php else: ?>
<p style="font-size:12px;color:#64748b">AutoDJ is disabled. Enable it to play music automatically when no DJ is connected.</p>
<?php endif; ?>
<form method="POST" action="/user/radio/autodj/toggle/<?php echo $s->id; ?>" style="margin-top:8px">
<button type="submit" class="btn btn-sm" style="background:<?php echo $s->autodj_enabled ? 'rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.2)' : 'rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.2)'; ?>;padding:6px 14px;border-radius:6px;cursor:pointer">
<?php echo $s->autodj_enabled ? '⏹ Disable AutoDJ' : '▶ Enable AutoDJ'; ?>
</button></form>
</div>
<div class="r-card"><h3>Upcoming</h3>
<p style="color:#64748b;font-size:12px">No upcoming songs scheduled.</p></div>
</div>

<div id="tab-playlists" class="tab-content">
<div class="r-card"><h3>📂 Playlists</h3>
<p style="color:#64748b;font-size:12px">Playlist management coming soon.</p></div>
</div>

<div id="tab-djs" class="tab-content">
<div class="r-card"><h3>🎧 DJ Accounts</h3>
<p style="color:#64748b;font-size:12px"><a href="/user/dj-manager" style="color:#0A84FF">Go to DJ Manager →</a></p></div>
</div>

<div id="tab-listeners" class="tab-content">
<div class="r-card"><h3>👥 Current Listeners</h3>
<p style="color:#64748b;font-size:12px"><?php echo (int)($s->listeners_current ?? 0); ?> connected.</p>
<table class="tbl"><thead><tr><th>IP</th><th>Agent</th><th>Connected</th></tr></thead>
<tbody><tr><td colspan="3" style="text-align:center;color:#64748b;padding:20px">Listener details available when stream is active.</td></tr></tbody></table></div>
</div>

<div id="tab-widgets" class="tab-content">
<div class="r-card"><h3>🧩 Stream Widgets</h3>
<div style="margin-bottom:10px"><h4 style="font-size:12px;margin:0 0 4px">Now Playing Widget</h4>
<textarea rows="2" style="width:100%;font-size:11px;font-family:monospace" readonly><script src="http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/radio/widgets/nowplaying.php?stream=<?php echo $s->id; ?>"></script></textarea>
<button class="btn btn-sm btn-primary" style="margin-top:4px" onclick="copy(this)">📋 Copy</button></div>
<div style="margin-bottom:10px"><h4 style="font-size:12px;margin:0 0 4px">Listener Count Widget</h4>
<textarea rows="2" style="width:100%;font-size:11px;font-family:monospace" readonly><script src="http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/radio/widgets/listeners.php?stream=<?php echo $s->id; ?>"></script></textarea>
<button class="btn btn-sm btn-primary" onclick="copy(this)">📋 Copy</button></div>
<div style="margin-bottom:10px"><h4 style="font-size:12px;margin:0 0 4px">HTML5 Player</h4>
<textarea rows="3" style="width:100%;font-size:11px;font-family:monospace" readonly><audio controls><source src="http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>:<?php echo $s->port ?? 8000; ?>/stream" type="audio/mpeg"></audio></textarea>
<button class="btn btn-sm btn-primary" onclick="copy(this)">📋 Copy</button></div>
</div>
</div>

<div id="tab-player" class="tab-content">
<div class="r-card" style="text-align:center">
<h3>▶ Stream Player</h3>
<audio controls style="width:100%;margin:12px 0"><source src="http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>:<?php echo $s->port ?? 8000; ?>/stream" type="audio/mpeg"></audio>
<p style="font-size:12px;color:#64748b">Listening at <code><?php echo $s->bitrate ?? 128; ?> kbps</code></p>
</div>
</div>

<?php endif; ?>

<script>
function showTab(name, el) {
    document.querySelectorAll('.tab').forEach(function(t){t.classList.remove('active')});
    document.querySelectorAll('.tab-content').forEach(function(t){t.classList.remove('active')});
    if (el) el.classList.add('active'); else document.querySelector('.tab[onclick*="'+name+'"]')?.classList.add('active');
    document.getElementById('tab-'+name).classList.add('active');
}
function copy(el) {
    var t = el.previousElementSibling;
    navigator.clipboard.writeText(t.value);
    el.textContent = '✅ Copied!';
    setTimeout(function(){el.textContent = '📋 Copy';},2000);
}
</script>
