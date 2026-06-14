<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<h3 style="color:var(--accent);margin-bottom:12px">Super Admin Accounts</h3>
<div class="card" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/admin/roles/create">
<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:end">
<div class="form-group" style="flex:1;min-width:120px"><label>Username</label><input name="username" required></div>
<div class="form-group" style="flex:1;min-width:120px"><label>Email</label><input type="email" name="email" required></div>
<div class="form-group" style="flex:1;min-width:120px"><label>Password</label><input type="password" name="password" required minlength="8"></div>
<div class="form-group"><button type="submit" class="btn primary">+ Add Super Admin</button></div>
</div>
</form>
</div>
<table><tr><th>Username</th><th>Email</th><th>Role</th></tr>
<?php if (!empty($admins)): foreach ($admins as $a):
$role = $roleMap[$a->id]->role ?? 'superadmin';
?>
<tr><td><strong><?php echo htmlspecialchars($a->username ?: $a->name); ?></strong></td>
<td><?php echo htmlspecialchars($a->email); ?></td>
<td><span class="status-badge status-active">Super Admin</span></td></tr>
<?php endforeach; endif; ?>
</table>

<h3 style="color:var(--accent);margin:20px 0 12px">Hosting Users</h3>
<table><tr><th>Username</th><th>Email</th><th>Current Role</th><th>Set Role</th></tr>
<?php if (!empty($hostingUsers)): foreach ($hostingUsers as $u):
$role = $roleMap[$u->id]->role ?? 'user';
?>
<tr>
<td><?php echo htmlspecialchars($u->username); ?></td>
<td><?php echo htmlspecialchars($u->email); ?></td>
<td><span class="status-badge status-<?php echo $role === 'superadmin' ? 'active' : ($role === 'reseller' ? 'suspended' : 'terminated'); ?>"><?php echo ucfirst($role); ?></span></td>
<td><form method="POST" action="/admin/roles/<?php echo $u->id; ?>" style="display:flex;gap:4px">
<select name="role" style="padding:4px 8px;border-radius:4px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none;font-size:12px">
<option value="superadmin" <?php echo $role === 'superadmin' ? 'selected' : ''; ?>>Super Admin</option>
<option value="reseller" <?php echo $role === 'reseller' ? 'selected' : ''; ?>>Reseller</option>
<option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>User</option>
</select>
<button type="submit" class="btn btn-sm secondary" style="padding:4px 8px;font-size:11px">Set</button>
</form></td>
</tr>
<?php endforeach; else: ?><tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No users yet.</td></tr>
<?php endif; ?></table>
