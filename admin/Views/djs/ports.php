<style>
.stat-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px}
.stat-card{background:rgba(8,16,28,.5);border:1px solid rgba(56,189,248,.08);border-radius:12px;padding:18px;text-align:center}
.stat-card .num{font-size:26px;font-weight:800;color:var(--c,#008cff)}
.stat-card .label{font-size:11px;color:#64748b;margin-top:4px}
table{width:100%;border-collapse:collapse;font-size:12px}
th{text-align:left;padding:10px 8px;color:#94a3b8;font-weight:600;border-bottom:1px solid rgba(255,255,255,.06);font-size:11px}
td{padding:10px 8px;border-bottom:1px solid rgba(255,255,255,.04)}
.badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600}
.badge-available{background:rgba(74,222,128,.12);color:#4ade80}
.badge-assigned{background:rgba(56,189,248,.12);color:#38bdf8}
.badge-reserved{background:rgba(250,204,21,.1);color:#facc15}
.badge-failed{background:rgba(248,113,113,.12);color:#f87171}
.btn{padding:6px 12px;border-radius:6px;font-size:11px;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-block;transition:.2s}
.btn-primary{background:rgba(56,189,248,.15);color:#38bdf8}
.btn-primary:hover{background:rgba(56,189,248,.25)}
.btn-danger{background:rgba(248,113,113,.12);color:#f87171}
.btn-danger:hover{background:rgba(248,113,113,.2)}
</style>

<h2 style="margin-bottom:6px">DJ Port Manager</h2>
<p style="color:#64748b;font-size:13px;margin-bottom:12px">Manage dedicated DJ source ports — one port per DJ account.</p>

<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
  <a href="/admin/dj/ports/listener/start" class="btn btn-primary">Start Listener</a>
  <a href="/admin/dj/ports/listener/stop" class="btn" style="background:rgba(248,113,113,.12);color:#f87171">Stop Listener</a>
  <a href="/admin/dj/ports/listener/restart" class="btn" style="background:rgba(250,204,21,.1);color:#facc15">Restart Listener</a>
  <a href="/admin/dj/ports/listener/status" class="btn" style="background:rgba(56,189,248,.1);color:#38bdf8">Status</a>
  <a href="/admin/dj/ports/allocate-missing" class="btn" style="background:rgba(168,85,247,.1);color:#a855f7">Allocate Missing Ports</a>
</div>

<?php if (isset($_SESSION['info'])): ?>
<div style="background:rgba(56,189,248,.08);border:1px solid rgba(56,189,248,.15);border-radius:8px;padding:12px;margin-bottom:16px;font-size:12px;color:#38bdf8;font-family:monospace;white-space:pre-wrap"><?=$_SESSION['info']; unset($_SESSION['info']);?></div>
<?php endif; ?>

<div class="stat-cards">
  <div class="stat-card" style="--c:#38bdf8"><div class="num"><?=count($ports)?></div><div class="label">Total DJ Ports</div></div>
  <div class="stat-card" style="--c:#4ade80"><div class="num"><?php $used=0; foreach($ports as $p) if($p->status==='assigned') $used++; echo $used; ?></div><div class="label">Assigned</div></div>
  <div class="stat-card" style="--c:#94a3b8"><div class="num"><?php echo count($ports)-$used; ?></div><div class="label">Available</div></div>
  <?php if ($djRange): ?>
  <div class="stat-card" style="--c:#facc15"><div class="num"><?=$djRange->start?>-<?=$djRange->end?></div><div class="label">DJ Port Range</div></div>
  <?php endif; ?>
</div>

<div style="background:rgba(8,16,28,.5);border:1px solid rgba(56,189,248,.06);border-radius:12px;padding:20px">
<table>
<tr>
  <th>Port</th><th>Status</th><th>Station</th><th>Active DJs</th><th>Allocated</th><th>Actions</th>
</tr>
<?php if (empty($ports)): ?>
<tr><td colspan="6" style="text-align:center;color:#64748b;padding:30px">No DJ ports configured. Add a DJ port range in Port Ranges settings.</td></tr>
<?php else: ?>
<?php foreach ($ports as $p): ?>
<tr>
  <td><code style="color:#38bdf8;font-size:13px"><?=$p->port_start?></code></td>
  <td><span class="badge badge-<?=$p->status?>"><?=$p->status?></span></td>
  <td><?=htmlspecialchars($p->station_name ?: '—')?></td>
  <td><?=(int)$p->dj_count?></td>
  <td style="font-size:11px;color:#64748b"><?=$p->allocated_at ? date('M j, g:ia', strtotime($p->allocated_at)) : '—'?></td>
  <td>
    <?php if ($p->status === 'assigned'): ?>
    <a href="/admin/dj/ports/release/<?=$p->id?>" class="btn btn-danger" onclick="return confirm('Release port <?=$p->port_start?>? This will disconnect all DJs for this station.')">Release</a>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>
</div>
