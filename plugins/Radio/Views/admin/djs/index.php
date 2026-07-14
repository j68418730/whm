<h2 style="margin-bottom:16px">🎤 DJ Accounts</h2>
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">➕ Create DJ</h3>
<form method="POST" action="/admin/djs/create" style="display:flex;flex-direction:column;gap:16px">
<div style="display:flex;gap:8px;flex-wrap:wrap;">
<input name="username" placeholder="Username" required style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none;flex:1;min-width:150px">
<input name="password" type="password" placeholder="Password" required style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none;flex:1;min-width:150px">
<input name="name" placeholder="Display Name" style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none;flex:1;min-width:150px">
</div>

<!-- Stream Selection as Checkboxes -->
<div class="form-group" style="margin-bottom:12px;">
<label style="display:block;margin-bottom:8px;font-size:14px;color:#94a3b8;">Assigned Streams (Select multiple):</label>
<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(150px, 1fr));gap:8px;background:rgba(0,0,0,.2);padding:12px;border-radius:6px;border:1px solid rgba(255,255,255,.08);">
<?php foreach ($streams as $stream): ?>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#e0e0e0;cursor:pointer;">
<input type="checkbox" name="stream_ids[]" value="<?php echo $stream->id; ?>" style="margin:0;">
<span>Stream #<?php echo $stream->id; ?> (Port <?php echo $stream->port; ?>)</span>
</label>
<?php endforeach; ?>
</div>
</div>

<button type="submit" class="btn btn-sm primary" style="padding:10px 20px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;border-radius:6px;font-weight:600;cursor:pointer;margin-top:8px;">Create DJ</button>
</form>
</div>

<?php if (isset($showCreateForm)): ?>
<div class="card" style="margin-top:16px;">
<h3 style="color:var(--accent);margin-bottom:12px">✏️ Edit DJ</h3>
<form method="POST" action="/admin/djs/update/<?php echo $editId ?? ''; ?>" style="display:flex;flex-direction:column;gap:16px">
<div style="display:flex;gap:8px;flex-wrap:wrap;">
<input type="hidden" name="stream_ids" id="stream_ids_input" value="">
<input name="name" placeholder="Display Name" value="<?php echo htmlspecialchars($dj->name ?? ''); ?>" style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none;flex:1;min-width:150px">
<select name="status" style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#e0e0e0;flex:1;min-width:150px">
<option value="active" <?php echo ($dj->status ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
<option value="inactive" <?php echo ($dj->status ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
<option value="banned" <?php echo ($dj->status ?? '') === 'banned' ? 'selected' : ''; ?>>Banned</option>
</select>
</div>

<!-- Stream Selection as Checkboxes for Edit -->
<div class="form-group" style="margin-bottom:12px;">
<label style="display:block;margin-bottom:8px;font-size:14px;color:#94a3b8;">Assigned Streams (Select multiple):</label>
<div class="stream-checkboxes" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(150px, 1fr));gap:8px;background:rgba(0,0,0,.2);padding:12px;border-radius:6px;border:1px solid rgba(255,255,255,.08);">
<?php if (!empty($assignedStreams)): ?>
<?php $assignedStreamIds = array_column($assignedStreams, 'stream_id'); ?>
<?php foreach ($streams as $stream): ?>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#e0e0e0;cursor:pointer;">
<input type="checkbox" name="stream_ids[]" value="<?php echo $stream->id; ?>" class="stream-checkbox" <?php echo in_array($stream->id, $assignedStreamIds) ? 'checked' : ''; ?> style="margin:0;">
<span>Stream #<?php echo $stream->id; ?> (Port <?php echo $stream->port; ?>)</span>
</label>
<?php endforeach; ?>
<?php else: ?>
<?php foreach ($streams as $stream): ?>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#e0e0e0;cursor:pointer;">
<input type="checkbox" name="stream_ids[]" value="<?php echo $stream->id; ?>" class="stream-checkbox" style="margin:0;">
<span>Stream #<?php echo $stream->id; ?> (Port <?php echo $stream->port; ?>)</span>
</label>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>

<button type="submit" class="btn btn-sm primary" style="padding:10px 20px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;border-radius:6px;font-weight:600;cursor:pointer;margin-top:8px;">Update DJ</button>
</form>
</div>
<?php endif; ?>

<table style="margin-top:16px;">
<tr><th>Username</th><th>Name</th><th>Assigned Streams</th><th>Status</th><th>Last Active</th><th>Actions</th></tr>
<?php foreach ($djs as $d): ?>
<tr>
<td><strong><?php echo htmlspecialchars($d->username); ?></strong></td>
<td><?php echo htmlspecialchars($d->name ?? ''); ?></td>
<td>
<?php
$assignedStreamsForDJ = array_filter($djs[$d->id] ?? [], fn($s) => $s['stream_id'] ?? false);
if (!empty($assignedStreamsForDJ)) {
    foreach ($assignedStreamsForDJ as $stream) {
        echo '<span style="display:inline-block;background:rgba(0,191,255,.1);padding:2px 8px;border-radius:4px;margin:2px;font-size:12px;">' . htmlspecialchars($stream['stream_id']) . '</span>';
    }
} else {
    echo '<span style="color:#64748b;font-size:12px;">No streams assigned</span>';
}
?>
</td>
<td><?php echo $d->status === 'active' ? '<span style="color:#4ade80">Active</span>' : ($d->status === 'banned' ? '<span style="color:#f87171">Banned</span>' : '<span style="color:#64748b">Inactive</span>'); ?></td>
<td><?php echo $d->last_login ?: 'Never'; ?></td>
<td><a href="/admin/djs/edit/<?php echo $d->id; ?>" class="btn btn-sm secondary">Edit</a>
<a href="/admin/djs/remove/<?php echo $d->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171" onclick="return confirm('Remove DJ?')">🗑</a></td>
</tr>
<?php endforeach; ?>
</table>

<script>
// Set initial selected stream IDs for edit form
<?php if (isset($editId) && !empty($assignedStreams)): ?>
const selectedStreams = <?php echo json_encode(array_column($assignedStreams, 'stream_id')); ?>;
const checkboxes = document.querySelectorAll('.stream-checkbox');
checkboxes.forEach(cb => {
    if (selectedStreams.includes(parseInt(cb.value))) {
        cb.checked = true;
    }
});
<?php endif; ?>
</script>
