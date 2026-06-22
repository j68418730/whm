<h2 style="margin-bottom:16px">🤖 AutoDJ Management</h2>
<table>
<tr><th>Stream</th><th>Status</th><th>Songs</th><th>Last Song</th><th>Actions</th></tr>
<?php foreach ($autodjs as $a): ?>
<tr>
<td>#<?php echo $a->stream_id; ?></td>
<td><?php echo $a->status === 'running' ? '<span style="color:#4ade80">● Running</span>' : '<span style="color:#64748b">● Stopped</span>'; ?></td>
<td><?php echo $a->song_count ?? 0; ?></td>
<td><?php echo htmlspecialchars($a->last_song ?? 'N/A'); ?></td>
<td>
<a href="/admin/autodj/start/<?php echo $a->id; ?>" class="btn btn-sm secondary">▶ Start</a>
<a href="/admin/autodj/stop/<?php echo $a->id; ?>" class="btn btn-sm secondary">⏹ Stop</a>
<a href="/admin/autodj/upload/<?php echo $a->id; ?>" class="btn btn-sm secondary">📤 Upload</a>
</td>
</tr>
<?php endforeach; ?>
</table>
