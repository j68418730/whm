<h2 style="margin-bottom:16px">🤖 AutoDJ Management</h2>
<table>
<tr><th>Stream</th><th>Status</th><th>Songs</th><th>Last Song</th><th>Actions</th></tr>
<?php $autodjs = $autodjs ?? []; if (empty($autodjs)): ?>
<tr><td colspan="5" style="text-align:center;color:#64748b;padding:30px;font-size:13px">No streams found.</td></tr>
<?php else: ?>
<?php foreach ($autodjs as $s): ?>
<tr>
<td><strong><?=htmlspecialchars($s->name)?></strong></td>
<td><?=$s->autodj_running ? '<span style="color:#4ade80">● Running</span>' : '<span style="color:#64748b">● Stopped</span>'; ?></td>
<td><?=(int)$s->song_count?></td>
<td><?=htmlspecialchars($s->current_song ?? 'N/A')?></td>
<td>
<a href="/user/radio/autodj/start/<?=10000+(int)$s->id?>" class="btn btn-sm" style="background:rgba(0,140,255,.15);color:#008cff;padding:4px 10px;border-radius:4px;text-decoration:none;font-size:11px">▶ Start</a>
<a href="/user/radio/autodj/stop/<?=10000+(int)$s->id?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171;padding:4px 10px;border-radius:4px;text-decoration:none;font-size:11px">⏹ Stop</a>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>
