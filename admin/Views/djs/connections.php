<style>
table{width:100%;border-collapse:collapse;font-size:12px}
th{text-align:left;padding:10px 8px;color:#94a3b8;font-weight:600;border-bottom:1px solid rgba(255,255,255,.06)}
td{padding:10px 8px;border-bottom:1px solid rgba(255,255,255,.04)}
.badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600}
.badge-live{background:rgba(74,222,128,.12);color:#4ade80}
.badge-ended{background:rgba(100,116,139,.12);color:#94a3b8}
</style>

<h2 style="margin-bottom:6px">DJ Connection History</h2>
<p style="color:#64748b;font-size:13px;margin-bottom:20px">Recent DJ connections and disconnections.</p>

<div style="background:rgba(8,16,28,.5);border:1px solid rgba(56,189,248,.06);border-radius:12px;padding:20px">
<table>
<tr><th>DJ</th><th>Station</th><th>Connected</th><th>Disconnected</th><th>Duration</th><th>Reason</th><th>Status</th></tr>
<?php if (empty($connections)): ?>
<tr><td colspan="7" style="text-align:center;color:#64748b;padding:30px">No connections recorded yet.</td></tr>
<?php else: ?>
<?php foreach ($connections as $c): ?>
<?php $dur = $c->connected_at && $c->disconnected_at ? strtotime($c->disconnected_at) - strtotime($c->connected_at) : ($c->connected_at ? time() - strtotime($c->connected_at) : 0); ?>
<tr>
  <td><strong><?=htmlspecialchars($c->dj_name ?: $c->dj_username)?></strong></td>
  <td><?=htmlspecialchars($c->station_name)?></td>
  <td style="font-size:11px;color:#64748b"><?=$c->connected_at ? date('M j, g:ia', strtotime($c->connected_at)) : '—'?></td>
  <td style="font-size:11px;color:#64748b"><?=$c->disconnected_at ? date('M j, g:ia', strtotime($c->disconnected_at)) : '—'?></td>
  <td><?php if ($dur > 0) { $h = floor($dur/3600); $m = floor(($dur%3600)/60); $s = $dur%60; echo ($h?$h.'h ':'').($m?$m.'m ':'').$s.'s'; } else echo '—'; ?></td>
  <td style="font-size:11px;color:#64748b"><?=htmlspecialchars($c->disconnect_reason ?: '—')?></td>
  <td><span class="badge badge-<?=$c->disconnected_at ? 'ended' : 'live'?>"><?=$c->disconnected_at ? 'Ended' : 'Live'?></span></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>
</div>
