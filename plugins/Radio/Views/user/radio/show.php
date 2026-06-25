<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
<div><h3 style="margin:0"><?php echo htmlspecialchars($stream->server_name ?? 'Stream'); ?></h3>
<div class="d-flex gap-2 mt-1">
<span class="badge bg-<?php echo $stream->status === 'running' ? 'success' : 'secondary'; ?>"><?php echo $stream->status ?? 'stopped'; ?></span>
<small style="color:var(--text_muted)">Port: <?php echo $stream->port; ?> &middot; Mount: <?php echo htmlspecialchars($stream->mount_point ?? '/live'); ?></small>
</div></div>
<div class="d-flex gap-1">
<a href="/radio/start/<?php echo $stream->id; ?>" class="btn btn-sm btn-success"><i class="bi bi-play-circle"></i> Start</a>
<a href="/radio/stop/<?php echo $stream->id; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-stop-circle"></i> Stop</a>
</div>
</div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:12px">
<div class="stat-card"><h3>Listeners</h3><div class="value" style="font-size:22px;color:#38bdf8"><?php echo $stream->listener_count ?? 0; ?></div></div>
<div class="stat-card"><h3>Peak</h3><div class="value" style="font-size:22px;color:#a78bfa"><?php echo $stream->peak_listeners ?? 0; ?></div></div>
<div class="stat-card"><h3>Bitrate</h3><div class="value" style="font-size:22px"><?php echo $stream->bitrate ?? 128; ?>kbps</div></div>
</div>

<div class="card"><h4>AutoDJ</h4>
<div class="d-flex gap-2 flex-wrap">
<?php if ($stream->autodj_enabled): ?>
<a href="/radio/autodj/disable/<?php echo $stream->id; ?>" class="btn btn-sm btn-secondary">Disable AutoDJ</a>
<a href="/radio/autodj/start/<?php echo $stream->id; ?>" class="btn btn-sm btn-success"><i class="bi bi-play-circle"></i> Start</a>
<a href="/radio/autodj/stop/<?php echo $stream->id; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-stop-circle"></i> Stop</a>
<?php else: ?>
<a href="/radio/autodj/enable/<?php echo $stream->id; ?>" class="btn btn-sm btn-primary">Enable AutoDJ</a>
<?php endif; ?>
<a href="/radio/stream/<?php echo $stream->id; ?>/manage-djs" class="btn btn-sm btn-secondary">Manage DJs</a>
<a href="/radio/stream/<?php echo $stream->id; ?>/manage-playlists" class="btn btn-sm btn-secondary">Playlists</a>
</div>
</div>

<div class="card"><h4>Embed Player</h4>
<p style="font-size:12px;color:var(--text_muted);margin-bottom:8px">Add this player to your website:</p>
<code style="display:block;background:rgba(0,0,0,.4);padding:10px;border-radius:6px;font-size:11px;color:#4ade80;word-break:break-all">&lt;iframe src="http://planet-hosts.com/radio/embed.php?stream=<?php echo $stream->id; ?>" width="360" height="340" frameborder="0"&gt;&lt;/iframe&gt;</code>
<button class="btn btn-sm btn-secondary mt-2" onclick="navigator.clipboard.writeText('&lt;iframe src=&quot;http://planet-hosts.com/radio/embed.php?stream=<?php echo $stream->id; ?>&quot; width=&quot;360&quot; height=&quot;340&quot; frameborder=&quot;0&quot;&gt;&lt;/iframe&gt;')">Copy Embed</button>
</div>

