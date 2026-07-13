<style>.ptable{width:100%;border-collapse:collapse;font-size:13px}.ptable th{text-align:left;padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.06);color:#64748b;font-weight:600;font-size:11px;text-transform:uppercase}.ptable td{padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.04)}.tag{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600}.tag-available{background:rgba(74,222,128,.1);color:#4ade80}.tag-assigned{background:rgba(0,191,255,.1);color:#00bfff}.tag-reserved{background:rgba(250,204,21,.1);color:#facc15}.tag-disabled{background:rgba(100,116,139,.1);color:#64748b}.tag-failed{background:rgba(239,68,68,.1);color:#ef4444}</style>
<h2>Port Usage</h2>
<p style="color:#64748b;margin-bottom:16px">
  <a href="/admin/port" style="color:var(--accent)">Dashboard</a> &rsaquo; Usage
</p>
<form method="get" style="margin-bottom:16px;display:flex;gap:8px;align-items:center">
  <label style="color:#64748b;font-size:13px">Filter by type:</label>
  <select name="type" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px;padding:6px 12px;color:#e0e0e0;font-size:13px">
    <option value="">All types</option>
    <?php foreach ($ranges as $r): ?>
    <option value="<?php echo $r->service_type; ?>" <?php echo $currentType === $r->service_type ? 'selected' : ''; ?>><?php echo htmlspecialchars($r->name); ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" style="background:var(--accent);color:#fff;border:none;border-radius:6px;padding:6px 16px;font-size:13px;cursor:pointer">Filter</button>
</form>
<table class="ptable">
<thead><tr><th>Port</th><th>Type</th><th>Customer</th><th>Station</th><th>Status</th><th>Allocated</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($ports as $p): ?>
<tr>
  <td><strong><?php echo $p->port_start; ?></strong><?php echo $p->port_end ? ' - ' . $p->port_end : ''; ?></td>
  <td><?php echo $p->service_type; ?></td>
  <td style="color:#64748b"><?php echo $p->customer_id ? '#' . $p->customer_id : '-'; ?></td>
  <td style="color:#64748b"><?php echo $p->station_id ? '#' . $p->station_id : '-'; ?></td>
  <td><span class="tag tag-<?php echo $p->status; ?>"><?php echo $p->status; ?></span></td>
  <td style="color:#64748b;font-size:11px"><?php echo $p->allocated_at ? date('Y-m-d', strtotime($p->allocated_at)) : '-'; ?></td>
  <td>
    <?php if ($p->status === 'assigned'): ?>
    <a href="/admin/port/release/<?php echo $p->id; ?>" onclick="return confirm('Release this port?')" style="color:#ef4444;font-size:12px">Release</a>
    <?php endif; ?>
    <a href="/admin/port/validate/<?php echo $p->id; ?>" style="color:var(--accent);font-size:12px;margin-left:8px">Validate</a>
  </td>
</tr>
<?php endforeach; ?>
<?php if (empty($ports)): ?>
<tr><td colspan="7" style="text-align:center;color:#64748b;padding:30px">No ports found.</td></tr>
<?php endif; ?>
</tbody></table>
