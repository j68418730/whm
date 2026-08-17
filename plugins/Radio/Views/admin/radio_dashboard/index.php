<style>
.rd-wrap{max-width:1400px;margin:0 auto}
.rd-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:18px}
.rd-stat{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;text-align:center}
.rd-stat .num{font-size:24px;font-weight:800}
.rd-stat .lbl{font-size:11px;color:#64748b;margin-top:2px}
.rd-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px;margin-bottom:18px}
.rd-card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:16px;transition:.15s}
.rd-card:hover{border-color:rgba(0,140,255,.25)}
.rd-card .head{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:8px}
.rd-card .name{font-size:15px;font-weight:700}
.rd-card .meta{display:flex;gap:10px;font-size:11px;color:#64748b;flex-wrap:wrap;margin-bottom:8px}
.rd-card .meta b{color:#cbd5e1}
.rd-card .acts{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}
.rd-card .acts a{padding:6px 12px;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;border:1px solid rgba(0,191,255,.12);color:#e0e0e0;background:rgba(0,140,255,.08)}
.rd-card .acts a:hover{background:rgba(0,140,255,.15)}
.rd-badge{padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700}
.rd-badge.running{background:rgba(74,222,128,.15);color:#4ade80}
.rd-badge.stopped{background:rgba(248,113,113,.15);color:#f87171}
.rd-table{width:100%;border-collapse:collapse;font-size:12px}
.rd-table th{padding:8px 6px;text-align:left;font-weight:600;color:#64748b;border-bottom:1px solid rgba(255,255,255,.06)}
.rd-table td{padding:8px 6px;border-bottom:1px solid rgba(255,255,255,.04);color:#c0c0c0}
.rd-stream-chip{display:inline-block;background:rgba(0,191,255,.1);padding:2px 8px;border-radius:4px;margin:2px;font-size:11px;text-decoration:none;color:#38bdf8}
</style>

<div class="rd-wrap">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px">
<div><h2 style="margin:0">📻 Radio Dashboard</h2><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0">All radio streams and DJs on the server</p></div>
<div class="d-flex gap-2" style="display:flex;gap:8px">
<a href="/admin/streams/create" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle"></i> New Stream</a>
<a href="/admin/autodj" class="btn btn-sm btn-secondary"><i class="bi bi-robot"></i> AutoDJ</a>
<a href="/admin/djs" class="btn btn-sm btn-secondary"><i class="bi bi-person-badge"></i> DJs</a>
</div>
</div>

<div class="rd-stats">
<div class="rd-stat"><div class="num" style="color:#38bdf8"><?php echo $total; ?></div><div class="lbl">Streams</div></div>
<div class="rd-stat"><div class="num" style="color:#4ade80"><?php echo $active; ?></div><div class="lbl">Running</div></div>
<div class="rd-stat"><div class="num"><?php echo $totalListeners; ?></div><div class="lbl">Listeners</div></div>
<div class="rd-stat"><div class="num" style="color:#a855f7"><?php echo count($djs); ?></div><div class="lbl">DJ Accounts</div></div>
</div>

<h3 style="margin:0 0 10px;font-size:14px">🎵 Stations — click <strong>Go to Dashboard</strong> to manage a stream</h3>
<div class="rd-cards">
<?php if (count($streams) > 0): foreach ($streams as $s): ?>
<div class="rd-card">
<div class="head">
<span class="name"><?php echo htmlspecialchars($s->server_name ?? 'Stream #'.$s->id); ?></span>
<span class="rd-badge <?php echo $s->status === 'running' ? 'running' : 'stopped'; ?>"><?php echo $s->status ?? 'stopped'; ?></span>
</div>
<div class="meta">
<span>👤 <?php echo htmlspecialchars($s->user_name ?? 'N/A'); ?></span>
<span>🔌 <?php echo $s->port; ?></span>
<span>📎 <?php echo htmlspecialchars($s->mount_point ?? '/live'); ?></span>
<span>👥 <b><?php echo (int)($s->listener_count ?? 0); ?></b></span>
<span>🎧 <b><?php echo (int)($s->bitrate ?? 128); ?>k</b></span>
<span>🤖 <b><?php echo $s->autodj_enabled ? 'ON' : 'OFF'; ?></b></span>
</div>
<div class="acts">
<a href="/admin/streams/edit/<?php echo $s->id; ?>"><i class="bi bi-speedometer2"></i> Go to Dashboard</a>
<a href="/admin/streams/edit/<?php echo $s->id; ?>"><i class="bi bi-pencil"></i> Edit</a>
<?php if ($s->status === 'running'): ?>
<a href="/admin/streams/suspend/<?php echo $s->id; ?>" style="color:#facc15;border-color:rgba(250,204,21,.2)"><i class="bi bi-pause-circle"></i></a>
<?php else: ?>
<a href="/admin/streams/unsuspend/<?php echo $s->id; ?>" style="color:#4ade80;border-color:rgba(74,222,128,.2)"><i class="bi bi-play-circle"></i></a>
<?php endif; ?>
<a href="/admin/streams/delete/<?php echo $s->id; ?>" style="color:#f87171;border-color:rgba(248,113,113,.2)" onclick="return confirm('Delete stream #<?php echo $s->id; ?>?')"><i class="bi bi-trash"></i></a>
</div>
</div>
<?php endforeach; else: ?>
<div class="rd-card" style="grid-column:1/-1;text-align:center;color:var(--text_muted)">No streams yet. <a href="/admin/streams/create">Create one</a>.</div>
<?php endif; ?>
</div>

<div class="rd-card" style="padding:16px">
<h3 style="margin:0 0 12px;font-size:14px">🎧 DJ Accounts — click a stream to open its dashboard</h3>
<table class="rd-table">
<thead><tr><th>Username</th><th>Name</th><th>Assigned Streams</th><th>Status</th><th>Last Active</th><th>Actions</th></tr></thead>
<tbody>
<?php
$djStreams = $djStreams ?? [];
if (count($djs) > 0): foreach ($djs as $d):
$assigned = $djStreams[$d->id] ?? [];
?>
<tr>
<td><strong><?php echo htmlspecialchars($d->username); ?></strong></td>
<td><?php echo htmlspecialchars($d->name ?? ''); ?></td>
<td>
<?php if (!empty($assigned)): foreach ($assigned as $sid => $st): ?>
<a class="rd-stream-chip" href="/admin/streams/edit/<?php echo (int)$sid; ?>" title="Open station #<?php echo (int)$sid; ?> dashboard"><?php echo htmlspecialchars($st->name); ?><?php echo !empty($st->is_primary) ? ' ★' : ''; ?></a>
<?php endforeach; else: ?>
<span style="color:#64748b;font-size:11px">No streams assigned</span>
<?php endif; ?>
</td>
<td><span style="color:<?php echo $d->status === 'active' ? '#4ade80' : '#64748b'; ?>"><?php echo $d->status; ?></span></td>
<td><?php echo htmlspecialchars($d->last_active ?: ($d->last_login ?: 'Never')); ?></td>
<td><a href="/admin/djs/edit/<?php echo $d->id; ?>" class="btn btn-sm secondary" style="text-decoration:none">Edit</a></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="6" style="text-align:center;padding:20px;color:#64748b">No DJ accounts yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
