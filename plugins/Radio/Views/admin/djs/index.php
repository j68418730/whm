<h2 style="margin-bottom:16px">🎤 DJ Accounts</h2>
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">➕ Create DJ</h3>
<form method="POST" action="/admin/djs/create" style="display:flex;gap:8px;flex-wrap:wrap">
<input name="username" placeholder="Username" required style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none">
<input name="password" type="password" placeholder="Password" required style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none">
<input name="name" placeholder="Display Name" style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none">
<select name="stream_id" style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#e0e0e0">
<?php foreach ($streams as $st): ?><option value="<?php echo $st->id; ?>">Stream #<?php echo $st->id; ?> (Port <?php echo $st->port; ?>)</option><?php endforeach; ?>
</select>
<button type="submit" class="btn btn-sm primary">Create DJ</button>
</form>
</div>
<table>
<tr><th>Username</th><th>Name</th><th>Stream</th><th>Status</th><th>Last Active</th><th>Actions</th></tr>
<?php foreach ($djs as $d): ?>
<tr>
<td><strong><?php echo htmlspecialchars($d->username); ?></strong></td>
<td><?php echo htmlspecialchars($d->name ?? ''); ?></td>
<td>#<?php echo $d->stream_id; ?></td>
<td><?php echo $d->status === 'active' ? '<span style="color:#4ade80">Active</span>' : ($d->status === 'banned' ? '<span style="color:#f87171">Banned</span>' : '<span style="color:#64748b">Inactive</span>'); ?></td>
<td><?php echo $d->last_login ?: 'Never'; ?></td>
<td><a href="/admin/djs/edit/<?php echo $d->id; ?>" class="btn btn-sm secondary">Edit</a>
<a href="/admin/djs/remove/<?php echo $d->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171" onclick="return confirm('Remove DJ?')">🗑</a></td>
</tr>
<?php endforeach; ?>
</table>
