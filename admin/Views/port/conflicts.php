<style>.ctable{width:100%;border-collapse:collapse;font-size:13px}.ctable th{text-align:left;padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.06);color:#64748b;font-weight:600;font-size:11px;text-transform:uppercase}.ctable td{padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.04)}.tag{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600}.tag-warn{background:rgba(239,68,68,.1);color:#ef4444}</style>
<h2>Port Conflicts</h2>
<p style="color:#64748b;margin-bottom:16px">
  <a href="/admin/port" style="color:var(--accent)">Dashboard</a> &rsaquo; Conflicts
</p>
<?php if (empty($conflicts)): ?>
<div style="background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.2);border-radius:8px;padding:20px;text-align:center">
  <div style="font-size:36px;margin-bottom:8px">✓</div>
  <p style="color:#4ade80">No port conflicts detected.</p>
</div>
<?php else: ?>
<p style="color:#ef4444;margin-bottom:12px"><?php echo count($conflicts); ?> conflict(s) found.</p>
<table class="ctable">
<thead><tr><th>Port</th><th>Record 1</th><th>Type 1</th><th>Record 2</th><th>Type 2</th><th>Action</th></tr></thead>
<tbody>
<?php foreach ($conflicts as $c): ?>
<tr>
  <td><strong><?php echo $c->port_start; ?></strong></td>
  <td>#<?php echo $c->id1; ?> (cust:<?php echo $c->cust1 ?? '-'; ?>, sta:<?php echo $c->station1 ?? '-'; ?>)</td>
  <td><span class="tag tag-warn"><?php echo $c->type1; ?></span></td>
  <td>#<?php echo $c->id2; ?> (cust:<?php echo $c->cust2 ?? '-'; ?>, sta:<?php echo $c->station2 ?? '-'; ?>)</td>
  <td><span class="tag tag-warn"><?php echo $c->type2; ?></span></td>
  <td><a href="/admin/port/release/<?php echo $c->id2; ?>" onclick="return confirm('Release duplicate?')" style="color:#ef4444;font-size:12px">Release #<?php echo $c->id2; ?></a></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
