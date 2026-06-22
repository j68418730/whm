<h2 style="margin-bottom:16px">👤 Admin Management</h2>

<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div><?php endif; ?>

<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">➕ Create Admin</h3>
<form method="POST" action="/admin/admins/create" style="display:flex;gap:8px;flex-wrap:wrap">
<input name="username" placeholder="Username" required style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none">
<input name="password" type="password" placeholder="Password" required style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none">
<input name="email" placeholder="Email (optional)" style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none">
<select name="role" style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#e0e0e0;outline:none">
<option value="admin">Admin</option>
<option value="super">Super Admin</option>
<option value="support">Support Staff</option>
</select>
<button type="submit" class="btn btn-sm primary">Create</button>
</form>
</div>

<table>
<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Created</th><th>Action</th></tr>
<?php foreach ($admins as $a): ?>
<tr>
<td><?php echo $a->id; ?></td>
<td><strong><?php echo htmlspecialchars($a->username); ?></strong> <?php if (in_array($a->username, ['root','kane','spectre'])): ?><span style="color:#facc15;font-size:10px">★ SUPER</span><?php endif; ?></td>
<td><?php echo htmlspecialchars($a->email ?? '-'); ?></td>
<td><span style="color:<?php echo $a->role === 'super' ? '#facc15' : ($a->role === 'support' ? '#38bdf8' : '#94a3b8'); ?>"><?php echo $a->role ?? 'admin'; ?></span></td>
<td><?php echo $a->created_at ?? '-'; ?></td>
<td>
<?php if ($a->username !== 'root' && $a->username !== 'kane'): ?>
<a href="/admin/admins/delete/<?php echo $a->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171" onclick="return confirm('Delete admin?')">🗑</a>
<?php else: ?>
<span style="color:#64748b;font-size:11px">Protected</span>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</table>
