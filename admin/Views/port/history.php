<style>.htable{width:100%;border-collapse:collapse;font-size:13px}.htable th{text-align:left;padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.06);color:#64748b;font-weight:600;font-size:11px;text-transform:uppercase}.htable td{padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.04)}.tag{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600}.tag-allocate{background:rgba(0,191,255,.1);color:#00bfff}.tag-release{background:rgba(74,222,128,.1);color:#4ade80}.tag-reserve{background:rgba(250,204,21,.1);color:#facc15}.tag-fail{background:rgba(239,68,68,.1);color:#ef4444}.tag-validate{background:rgba(168,85,247,.1);color:#a855f7}.tag-firewall_add{background:rgba(59,130,246,.1);color:#3b82f6}.tag-firewall_remove{background:rgba(100,116,139,.1);color:#64748b}</style>
<h2>Port Allocation History</h2>
<p style="color:#64748b;margin-bottom:16px">
  <a href="/admin/port" style="color:var(--accent)">Dashboard</a> &rsaquo; History
</p>
<table class="htable">
<thead><tr><th>Time</th><th>Action</th><th>Type</th><th>Port</th><th>Customer</th><th>Station</th><th>Message</th></tr></thead>
<tbody>
<?php foreach ($log as $l): ?>
<tr>
  <td style="color:#64748b;font-size:11px"><?php echo date('M j H:i:s', strtotime($l->created_at)); ?></td>
  <td><span class="tag tag-<?php echo $l->action; ?>"><?php echo $l->action; ?></span></td>
  <td><?php echo $l->service_type ?: $l->svc_type ?: '-'; ?></td>
  <td><?php echo $l->port_start ?: '-'; ?></td>
  <td style="color:#64748b"><?php echo $l->customer_id ? '#' . $l->customer_id : '-'; ?></td>
  <td style="color:#64748b"><?php echo $l->station_id ? '#' . $l->station_id : '-'; ?></td>
  <td style="color:#64748b"><?php echo htmlspecialchars($l->message ?? ''); ?></td>
</tr>
<?php endforeach; ?>
<?php if (empty($log)): ?>
<tr><td colspan="7" style="text-align:center;color:#64748b;padding:30px">No history records.</td></tr>
<?php endif; ?>
</tbody></table>
