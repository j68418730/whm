<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
<h2 style="margin:0">Admin Management</h2>
</div>

<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div><?php endif; ?>

<!-- Create Admin -->
<div class="card" style="margin-bottom:16px;max-width:700px">
<h4 style="color:var(--accent);margin:0 0 12px">Create Admin</h4>
<form method="POST" action="/admin/admins/create">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div class="form-group" style="margin:0"><label style="font-size:11px">Username</label><input name="username" required style="width:100%;padding:6px 10px;font-size:12px"></div>
<div class="form-group" style="margin:0"><label style="font-size:11px">Password</label><input name="password" type="password" required style="width:100%;padding:6px 10px;font-size:12px"></div>
<div class="form-group" style="margin:0"><label style="font-size:11px">Email</label><input name="email" type="email" placeholder="optional" style="width:100%;padding:6px 10px;font-size:12px"></div>
<div class="form-group" style="margin:0"><label style="font-size:11px">Role</label>
<select name="role" style="width:100%;padding:6px 10px;font-size:12px">
<option value="admin">Admin</option>
<option value="super">Super Admin</option>
<option value="support">Support Staff</option>
</select>
</div>
</div>

<div style="margin-top:10px">
<label style="font-size:12px;color:#94a3b8;display:block;margin-bottom:6px">Permissions (what this admin can do)</label>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:4px;max-height:200px;overflow-y:auto;padding:8px;background:rgba(0,0,0,.15);border-radius:6px">
<?php
$allPerms = ['billing','accounts','packages','resellers','streaming','domains','dns','ssl','ssh','ftp','email','databases','backups','support','tickets','livechat','kb','announcements','reports','servers','plugins','templates','security','api','settings','theme'];
foreach ($allPerms as $perm):
?>
<label style="display:flex;align-items:center;gap:5px;font-size:11px;cursor:pointer;padding:2px 4px;border-radius:3px;background:rgba(255,255,255,.02)">
<input type="checkbox" name="permissions[]" value="<?php echo $perm; ?>"> <?php echo ucfirst($perm); ?>
</label>
<?php endforeach; ?>
</div>
</div>

<button type="submit" class="btn btn-sm primary" style="margin-top:10px">Create Admin</button>
</form>
</div>

<!-- Admin Cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px">
<?php foreach ($admins as $a):
$perms = json_decode($a->permissions ?? '[]', true) ?: [];
$isProtected = in_array($a->username, ['root', 'kane']);
?>
<div class="card" style="margin-bottom:0;padding:16px;background:<?php echo !$a->is_active ? 'rgba(248,113,113,.04)' : ''; ?>">
<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px">
<div>
<div style="font-weight:700;font-size:15px"><?php echo htmlspecialchars($a->username); ?>
<?php if ($isProtected): ?><span style="color:#facc15;font-size:10px;margin-left:6px">★ SUPER</span><?php endif; ?>
</div>
<div style="font-size:12px;color:#64748b"><?php echo htmlspecialchars($a->email ?? '-'); ?></div>
</div>
<div>
<span class="badge bg-<?php echo $a->role === 'super' ? 'warning' : ($a->role === 'support' ? 'info' : 'secondary'); ?>" style="font-size:10px"><?php echo $a->role ?? 'admin'; ?></span>
<?php if (!$a->is_active): ?><span class="badge bg-danger" style="font-size:10px">Suspended</span><?php endif; ?>
</div>
</div>
<div style="font-size:11px;color:#94a3b8;margin-bottom:8px">Created: <?php echo $a->created_at ?? '-'; ?></div>

<?php if (!empty($perms)): ?>
<div style="margin-bottom:8px">
<div style="font-size:10px;color:#64748b;margin-bottom:4px">Permissions:</div>
<div style="display:flex;flex-wrap:wrap;gap:3px">
<?php foreach ($perms as $p): ?>
<span style="padding:1px 6px;border-radius:3px;font-size:10px;background:rgba(0,140,255,.08);color:#38bdf8"><?php echo htmlspecialchars($p); ?></span>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;padding-top:8px;border-top:1px solid rgba(255,255,255,.04)">
<?php if (!$isProtected && $a->role !== 'super'): ?>
<form method="POST" action="/admin/admins/permissions/<?php echo $a->id; ?>" style="display:inline">
<div style="display:flex;gap:4px;flex-wrap:wrap;align-items:center">
<select name="permissions[]" multiple size="1" style="width:130px;padding:3px 6px;font-size:10px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.06);color:#e0e0e0;border-radius:4px">
<?php foreach ($allPerms as $perm): ?>
<option value="<?php echo $perm; ?>" <?php echo in_array($perm, $perms) ? 'selected' : ''; ?>><?php echo ucfirst($perm); ?></option>
<?php endforeach; ?>
</select>
<button class="btn btn-sm" style="font-size:10px;padding:3px 8px;background:rgba(0,140,255,.1);color:#38bdf8;border:none;border-radius:4px;cursor:pointer">Set</button>
</div>
</form>
<?php endif; ?>
<div style="display:flex;gap:4px;margin-left:auto">
<?php if (!$isProtected): ?>
<a href="/admin/admins/toggle-status/<?php echo $a->id; ?>" class="btn btn-sm" style="font-size:10px;padding:3px 8px;background:<?php echo $a->is_active ? 'rgba(250,204,21,.1)' : 'rgba(74,222,128,.1)'; ?>;color:<?php echo $a->is_active ? '#facc15' : '#4ade80'; ?>;text-decoration:none;border-radius:4px">
<?php echo $a->is_active ? 'Suspend' : 'Unsuspend'; ?>
</a>
<a href="/admin/admins/delete/<?php echo $a->id; ?>" class="btn btn-sm" style="font-size:10px;padding:3px 8px;background:rgba(248,113,113,.12);color:#f87171;text-decoration:none;border-radius:4px" onclick="return confirm('Delete admin?')">Delete</a>
<?php else: ?>
<span style="font-size:10px;color:#64748b">Protected</span>
<?php endif; ?>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
