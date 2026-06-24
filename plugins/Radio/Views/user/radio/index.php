<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
<div><h3 style="margin:0">My Radio Streams</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0">Manage your radio streaming accounts</p></div>
<a href="/radio/create" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle"></i> New Stream</a>
</div>
</div>

<?php if (count($streams) > 0): foreach ($streams as $s): ?>
<div class="card" style="margin-bottom:10px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px">
<div>
<h4 style="margin:0;font-size:14px"><?php echo htmlspecialchars($s->server_name ?? 'Stream #'.$s->id); ?></h4>
<span class="badge bg-<?php echo $s->status === 'running' ? 'success' : 'secondary'; ?>"><?php echo $s->status ?? 'stopped'; ?></span>
<small style="color:var(--text_muted);margin-left:6px">Port: <?php echo $s->port; ?> &middot; Mount: <?php echo htmlspecialchars($s->mount_point ?? '/live'); ?></small>
</div>
<div class="d-flex gap-1">
<a href="/radio/stream/<?php echo $s->id; ?>" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i> Manage</a>
<a href="/radio/start/<?php echo $s->id; ?>" class="btn btn-sm btn-success"><i class="bi bi-play-circle"></i></a>
<a href="/radio/stop/<?php echo $s->id; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-stop-circle"></i></a>
</div>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:6px;margin-top:8px">
<div><small style="color:var(--text_muted)">Listeners</small><div style="font-weight:700"><?php echo $s->listener_count ?? 0; ?></div></div>
<div><small style="color:var(--text_muted)">Bitrate</small><div style="font-weight:700"><?php echo $s->bitrate ?? 128; ?>kbps</div></div>
<div><small style="color:var(--text_muted)">AutoDJ</small><div style="font-weight:700;color:<?php echo $s->autodj_enabled ? '#4ade80' : '#64748b'; ?>"><?php echo $s->autodj_enabled ? 'ON' : 'OFF'; ?></div></div>
</div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:40px">
<div style="font-size:48px;margin-bottom:8px">≡ƒô╗</div>
<h4 style="margin:0 0 4px">No Radio Streams</h4>
<p style="color:var(--text_muted);font-size:13px;margin:0 0 14px">You haven't created any radio streams yet.</p>
<a href="/radio/create" class="btn btn-primary">Create Your First Stream</a>
</div>
<?php endif; ?>

