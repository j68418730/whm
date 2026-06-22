<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
<div><h3 style="margin:0">Radio Dashboard</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0">All radio streams on the server</p></div>
<div class="d-flex gap-2">
<a href="/admin/streams/create" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle"></i> New Stream</a>
<a href="/admin/streams" class="btn btn-sm btn-secondary"><i class="bi bi-list-ul"></i> All Streams</a>
</div>
</div>
</div>

<div class="stats-grid" style="margin-bottom:16px">
<div class="stat-card"><h3>Total Streams</h3><div class="value"><?php echo $total; ?></div></div>
<div class="stat-card"><h3>Active</h3><div class="value" style="color:#4ade80"><?php echo $active; ?></div></div>
<div class="stat-card"><h3>Total Listeners</h3><div class="value" style="color:#38bdf8"><?php echo $totalListeners; ?></div></div>
<div class="stat-card"><h3>DJ Accounts</h3><div class="value"><?php echo count($djs); ?></div></div>
</div>

<div class="card" style="padding:0;overflow:hidden">
<table class="table table-hover" style="margin:0">
<thead><tr><th>Name</th><th>Client</th><th>Port</th><th>Mount</th><th>Status</th><th>Listeners</th><th>Bitrate</th><th>AutoDJ</th><th>Actions</th></tr></thead>
<tbody>
<?php if (count($streams) > 0): foreach ($streams as $s): ?>
<tr>
<td><strong><?php echo htmlspecialchars($s->server_name ?? 'Stream #'.$s->id); ?></strong></td>
<td><?php echo htmlspecialchars($s->user_name ?? 'N/A'); ?></td>
<td><?php echo $s->port; ?></td>
<td><?php echo htmlspecialchars($s->mount_point ?? '/live'); ?></td>
<td><span class="badge bg-<?php echo $s->status === 'running' ? 'success' : ($s->status === 'error' ? 'danger' : 'secondary'); ?>"><?php echo $s->status ?? 'stopped'; ?></span></td>
<td><?php echo (int)($s->listener_count ?? 0); ?></td>
<td><?php echo (int)($s->bitrate ?? 128); ?>k</td>
<td><?php echo $s->autodj_enabled ? '<span class="badge bg-success">ON</span>' : '<span class="badge bg-secondary">OFF</span>'; ?></td>
<td style="white-space:nowrap">
<a href="/admin/streams/edit/<?php echo $s->id; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-pencil"></i></a>
<?php if ($s->status === 'running'): ?>
<a href="/admin/streams/suspend/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(250,204,21,.1);color:#facc15;border:1px solid rgba(250,204,21,.2)"><i class="bi bi-pause-circle"></i></a>
<?php else: ?>
<a href="/admin/streams/unsuspend/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.2)"><i class="bi bi-play-circle"></i></a>
<?php endif; ?>
<a href="/admin/streams/delete/<?php echo $s->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete stream #<?php echo $s->id; ?>?')"><i class="bi bi-trash"></i></a>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text_muted)">No streams yet. <a href="/admin/streams/create">Create one</a>.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
