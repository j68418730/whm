<style>
.port-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:24px}
.port-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:20px}
.port-card .title{font-size:13px;color:#64748b;margin-bottom:6px}
.port-card .big{font-size:28px;font-weight:800;color:var(--accent)}
.port-card .sub{font-size:11px;color:#64748b;margin-top:4px}
.port-table{width:100%;border-collapse:collapse;font-size:13px}
.port-table th{text-align:left;padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.06);color:#64748b;font-weight:600;font-size:11px;text-transform:uppercase}
.port-table td{padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.04)}
.tag{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600}
.tag-available{background:rgba(74,222,128,.1);color:#4ade80}
.tag-assigned{background:rgba(0,191,255,.1);color:#00bfff}
.tag-reserved{background:rgba(250,204,21,.1);color:#facc15}
.tag-disabled{background:rgba(100,116,139,.1);color:#64748b}
.tag-failed{background:rgba(239,68,68,.1);color:#ef4444}
.conflict-badge{background:rgba(239,68,68,.15);color:#ef4444;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600}
</style>

<h2>Port Manager</h2>
<p style="color:#64748b;margin-bottom:20px">Centralized port allocation for all streaming services.</p>

<?php if (!empty($conflicts)): ?>
<div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:8px;padding:12px 16px;margin-bottom:20px">
  <strong style="color:#ef4444">⚠ <?php echo count($conflicts); ?> port conflict(s) detected</strong>
  <a href="/admin/port/conflicts" style="float:right;color:var(--accent)">Resolve</a>
</div>
<?php endif; ?>

<div class="port-grid">
  <div class="port-card">
    <div class="title">Total Port Ranges</div>
    <div class="big"><?php echo count($stats); ?></div>
    <div class="sub">Service types configured</div>
  </div>
  <div class="port-card">
    <div class="title">Assigned Ports</div>
    <div class="big"><?php echo array_sum(array_column(array_filter($stats, fn($s)=>!$s->internal_only), 'used')); ?></div>
    <div class="sub">Customer-facing services</div>
  </div>
  <div class="port-card">
    <div class="title">Free Ports</div>
    <div class="big"><?php echo array_sum(array_column($stats, 'free')); ?></div>
    <div class="sub">Available for allocation</div>
  </div>
  <div class="port-card">
    <div class="title">Servers</div>
    <div class="big"><?php echo count($servers); ?></div>
    <div class="sub">Streaming server nodes</div>
  </div>
</div>

<h3 style="margin-bottom:12px">Port Ranges</h3>
<table class="port-table">
<thead><tr>
  <th>Service Type</th><th>Range</th><th>Total</th><th>Used</th><th>Free</th><th>Reserved</th><th>Failed</th><th>Usage</th>
</tr></thead>
<tbody>
<?php foreach ($stats as $s): ?>
<tr>
  <td><strong><?php echo htmlspecialchars($s->name); ?></strong>
    <?php if ($s->internal_only): ?><span class="tag tag-reserved" style="margin-left:6px">internal</span><?php endif; ?>
  </td>
  <td><?php echo $s->start; ?> - <?php echo $s->end; ?></td>
  <td><?php echo $s->total; ?></td>
  <td><?php echo $s->used; ?></td>
  <td><?php echo $s->free; ?></td>
  <td><?php echo $s->reserved; ?></td>
  <td><?php echo $s->failed > 0 ? '<span style="color:#ef4444">' . $s->failed . '</span>' : '0'; ?></td>
  <td><div style="background:rgba(255,255,255,.06);border-radius:4px;height:8px;width:100px;overflow:hidden">
    <div style="background:<?php echo ($s->used/$s->total) > 0.8 ? '#ef4444' : ($s->used/$s->total) > 0.5 ? '#facc15' : '#4ade80'; ?>;width:<?php echo min(100, ($s->used/$s->total)*100); ?>%;height:8px;border-radius:4px"></div>
  </div></td>
</tr>
<?php endforeach; ?>
</tbody></table>

<div style="display:flex;gap:16px;margin-top:24px">
  <a href="/admin/port/usage" class="btn btn-primary" style="background:var(--accent);color:#fff;padding:8px 20px;border-radius:8px;text-decoration:none;font-size:13px">View All Ports</a>
  <a href="/admin/port/search" class="btn" style="background:rgba(255,255,255,.06);color:#e0e0e0;padding:8px 20px;border-radius:8px;text-decoration:none;font-size:13px">Search Ports</a>
  <a href="/admin/port/history" class="btn" style="background:rgba(255,255,255,.06);color:#e0e0e0;padding:8px 20px;border-radius:8px;text-decoration:none;font-size:13px">Allocation History</a>
</div>

<h3 style="margin:24px 0 12px">Servers</h3>
<table class="port-table">
<thead><tr><th>Name</th><th>IP</th><th>Total Ports</th><th>Assigned</th><th>Free</th><th>Status</th></tr></thead>
<tbody>
<?php foreach ($servers as $sv): ?>
<tr>
  <td><strong><?php echo htmlspecialchars($sv->name); ?></strong></td>
  <td><?php echo $sv->ip; ?></td>
  <td><?php echo $sv->total_ports; ?></td>
  <td><?php echo $sv->assigned_ports; ?></td>
  <td><?php echo $sv->free_ports; ?></td>
  <td><span class="tag tag-<?php echo $sv->status; ?>"><?php echo $sv->status; ?></span></td>
</tr>
<?php endforeach; ?>
</tbody></table>

<?php if (!empty($recent)): ?>
<h3 style="margin:24px 0 12px">Recent Activity</h3>
<table class="port-table">
<thead><tr><th>Time</th><th>Action</th><th>Type</th><th>Message</th></tr></thead>
<tbody>
<?php foreach ($recent as $r): ?>
<tr>
  <td style="color:#64748b;font-size:11px"><?php echo date('M j H:i', strtotime($r->created_at)); ?></td>
  <td><span class="tag tag-<?php echo $r->action === 'allocate' ? 'assigned' : ($r->action === 'release' ? 'available' : 'reserved'); ?>"><?php echo $r->action; ?></span></td>
  <td><?php echo $r->service_type ?: $r->svc_type; ?></td>
  <td style="color:#64748b"><?php echo htmlspecialchars($r->message ?? ''); ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
