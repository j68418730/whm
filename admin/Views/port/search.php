<style>.tag{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600}.tag-available{background:rgba(74,222,128,.1);color:#4ade80}.tag-assigned{background:rgba(0,191,255,.1);color:#00bfff}.tag-reserved{background:rgba(250,204,21,.1);color:#facc15}.tag-disabled{background:rgba(100,116,139,.1);color:#64748b}.tag-failed{background:rgba(239,68,68,.1);color:#ef4444}.stable{width:100%;border-collapse:collapse;font-size:13px}.stable th{text-align:left;padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.06);color:#64748b;font-weight:600;font-size:11px;text-transform:uppercase}.stable td{padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.04)}</style>
<h2>Port Search</h2>
<p style="color:#64748b;margin-bottom:16px">
  <a href="/admin/port" style="color:var(--accent)">Dashboard</a> &rsaquo; Search
</p>
<form method="get" style="margin-bottom:16px;display:flex;gap:8px">
  <input type="text" name="q" value="<?php echo htmlspecialchars($term); ?>" placeholder="Search by port number, type, or customer..." style="flex:1;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px;padding:8px 12px;color:#e0e0e0;font-size:13px">
  <button type="submit" style="background:var(--accent);color:#fff;border:none;border-radius:6px;padding:8px 20px;font-size:13px;cursor:pointer">Search</button>
</form>
<?php if ($term): ?>
<p style="color:#64748b;font-size:13px;margin-bottom:12px"><?php echo count($results); ?> result(s) for "<?php echo htmlspecialchars($term); ?>"</p>
<?php if (!empty($results)): ?>
<table class="stable">
<thead><tr><th>Port</th><th>Type</th><th>Server</th><th>Customer</th><th>Station</th><th>Status</th><th>Allocated</th></tr></thead>
<tbody>
<?php foreach ($results as $p): ?>
<tr>
  <td><strong><?php echo $p->port_start; ?></strong><?php echo $p->port_end ? ' - ' . $p->port_end : ''; ?></td>
  <td><?php echo $p->service_type; ?></td>
  <td style="color:#64748b"><?php echo $p->server_name ?? 'Main'; ?></td>
  <td style="color:#64748b"><?php echo $p->customer_id ? '#' . $p->customer_id : '-'; ?></td>
  <td style="color:#64748b"><?php echo $p->station_id ? '#' . $p->station_id : '-'; ?></td>
  <td><span class="tag tag-<?php echo $p->status; ?>"><?php echo $p->status; ?></span></td>
  <td style="color:#64748b;font-size:11px"><?php echo $p->allocated_at ? date('Y-m-d', strtotime($p->allocated_at)) : '-'; ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
<?php endif; ?>
