<?php
// Group streams by user
$grouped = [];
foreach ($streams as $s) {
    $uname = $s->user_name ?? 'N/A';
    if (!isset($grouped[$uname])) $grouped[$uname] = [];
    $grouped[$uname][] = $s;
}
ksort($grouped);
?>
<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
<div><h3 style="margin:0">Stream Management</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0">All radio streams grouped by client</p></div>
<a href="/admin/streams/create" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create Stream</a>
</div>
</div>

<div class="stats-grid" style="margin-bottom:16px">
<div class="stat-card"><h3>Total</h3><div class="value"><?php echo $streamsStats['total_streams'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Active</h3><div class="value" style="color:#4ade80"><?php echo $streamsStats['active_streams'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Clients</h3><div class="value"><?php echo count($grouped); ?></div></div>
</div>

<?php if (!empty($streams)): foreach ($grouped as $client => $clientStreams): ?>
<div class="card" style="margin-bottom:14px;padding:14px 18px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
<h4 style="margin:0;font-size:14px"><i class="bi bi-person-circle" style="color:var(--primary)"></i> <?php echo htmlspecialchars($client); ?> <span class="badge bg-secondary" style="font-size:10px"><?php echo count($clientStreams); ?> stream(s)</span></h4>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px">
<?php foreach ($clientStreams as $s): ?>
<div style="background:rgba(0,0,0,.2);border:1px solid var(--border,rgba(0,191,255,.06));border-radius:10px;padding:12px">
<div style="display:flex;justify-content:space-between;align-items:start">
<div><strong style="font-size:13px"><?php echo htmlspecialchars($s->server_name ?? 'Stream #'.$s->id); ?></strong></div>
<span class="badge bg-<?php echo $s->status === 'running' ? 'success' : ($s->status === 'error' ? 'danger' : 'secondary'); ?>"><?php echo $s->status ?? 'stopped'; ?></span>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;margin:8px 0;font-size:12px">
<div><span style="color:var(--text_muted)">Port:</span> <?php echo $s->port; ?></div>
<div><span style="color:var(--text_muted)">Mount:</span> <?php echo htmlspecialchars($s->mount_point ?? '/live'); ?></div>
<div><span style="color:var(--text_muted)">Listeners:</span> <?php echo (int)($s->listener_count ?? 0); ?></div>
<div><span style="color:var(--text_muted)">Bitrate:</span> <?php echo (int)($s->bitrate ?? 128); ?>kbps</div>
</div>
<div style="display:flex;gap:4px;flex-wrap:wrap">
<a href="/admin/streams/edit/<?php echo $s->id; ?>" class="btn btn-sm btn-secondary" style="padding:3px 8px;font-size:10px"><i class="bi bi-pencil"></i></a>
<?php if ($s->status === 'running'): ?>
<a href="/admin/streams/suspend/<?php echo $s->id; ?>" class="btn btn-sm" style="padding:3px 8px;font-size:10px;background:rgba(250,204,21,.1);color:#facc15;border:1px solid rgba(250,204,21,.2)"><i class="bi bi-pause-circle"></i></a>
<?php else: ?>
<a href="/admin/streams/unsuspend/<?php echo $s->id; ?>" class="btn btn-sm" style="padding:3px 8px;font-size:10px;background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.2)"><i class="bi bi-play-circle"></i></a>
<?php endif; ?>
<a href="/admin/streams/delete/<?php echo $s->id; ?>" class="btn btn-sm btn-danger" style="padding:3px 8px;font-size:10px" onclick="return confirm('Delete stream?')"><i class="bi bi-trash"></i></a>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:40px">
<div style="font-size:48px;margin-bottom:8px">📻</div>
<h4 style="margin:0 0 4px">No Streams</h4>
<p style="color:var(--text_muted);font-size:13px;margin:0 0 14px">No radio streams created yet.</p>
<a href="/admin/streams/create" class="btn btn-primary">Create First Stream</a>
</div>
<?php endif; ?>
