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
<button onclick="openEditModal(<?php echo $a->id; ?>,'<?php echo htmlspecialchars(addslashes($a->username)); ?>','<?php echo htmlspecialchars(addslashes($a->email ?? '')); ?>','<?php echo $a->role; ?>')" class="btn btn-sm" style="font-size:10px;padding:3px 8px;background:rgba(99,102,241,.12);color:#818cf8;border:none;border-radius:4px;cursor:pointer">Edit</button>
<button onclick="openPassModal(<?php echo $a->id; ?>,'<?php echo htmlspecialchars(addslashes($a->username)); ?>')" class="btn btn-sm" style="font-size:10px;padding:3px 8px;background:rgba(168,85,247,.12);color:#c084fc;border:none;border-radius:4px;cursor:pointer">Password</button>
<a href="/admin/admins/toggle-status/<?php echo $a->id; ?>" class="btn btn-sm" style="font-size:10px;padding:3px 8px;background:<?php echo $a->is_active ? 'rgba(250,204,21,.1)' : 'rgba(74,222,128,.1)'; ?>;color:<?php echo $a->is_active ? '#facc15' : '#4ade80'; ?>;text-decoration:none;border-radius:4px">
<?php echo $a->is_active ? 'Suspend' : 'Unsuspend'; ?>
</a>
<a href="/admin/admins/delete/<?php echo $a->id; ?>" class="btn btn-sm" style="font-size:10px;padding:3px 8px;background:rgba(248,113,113,.12);color:#f87171;text-decoration:none;border-radius:4px" onclick="return confirm('Delete admin?')">Delete</a>
<?php else: ?>
<span style="font-size:10px;color:#64748b">Protected</span>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>

<!-- Edit Admin Modal -->
<div id="editModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center">
<div style="background:#1e293b;border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:24px;width:420px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,.5)">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
<h3 style="margin:0;color:#e2e8f0;font-size:16px">Edit Admin</h3>
<span onclick="closeEditModal()" style="cursor:pointer;color:#64748b;font-size:20px">&times;</span>
</div>
<form method="POST" id="editForm">
<input type="hidden" name="username" id="edit_username_val">
<div style="margin-bottom:10px">
<label style="font-size:11px;color:#94a3b8;display:block;margin-bottom:4px">Username</label>
<input name="display_username" id="edit_username" required style="width:100%;padding:8px 10px;font-size:12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);color:#e2e8f0;border-radius:6px">
</div>
<div style="margin-bottom:10px">
<label style="font-size:11px;color:#94a3b8;display:block;margin-bottom:4px">Email</label>
<input name="email" id="edit_email" type="email" style="width:100%;padding:8px 10px;font-size:12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);color:#e2e8f0;border-radius:6px">
</div>
<div style="margin-bottom:16px">
<label style="font-size:11px;color:#94a3b8;display:block;margin-bottom:4px">Role</label>
<select name="role" id="edit_role" style="width:100%;padding:8px 10px;font-size:12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);color:#e2e8f0;border-radius:6px">
<option value="admin">Admin</option>
<option value="super">Super Admin</option>
<option value="support">Support Staff</option>
<option value="sales">Sales</option>
<option value="billing">Billing</option>
<option value="technical">Technical Support</option>
<option value="server">Server Support</option>
<option value="streaming">Streaming Support</option>
<option value="game">Game Server Support</option>
<option value="domain">Domain Support</option>
<option value="cpanel">Control Panel Support</option>
<option value="abuse">Abuse Department</option>
<option value="dmca">DMCA / Copyright</option>
<option value="linux">Linux Support</option>
<option value="windows">Windows Server Support</option>
</select>
</div>
<div style="display:flex;gap:8px;justify-content:flex-end">
<button type="button" onclick="closeEditModal()" style="padding:8px 16px;font-size:12px;background:rgba(255,255,255,.05);color:#94a3b8;border:1px solid rgba(255,255,255,.08);border-radius:6px;cursor:pointer">Cancel</button>
<button type="submit" style="padding:8px 16px;font-size:12px;background:rgba(99,102,241,.2);color:#818cf8;border:1px solid rgba(99,102,241,.3);border-radius:6px;cursor:pointer">Save</button>
</div>
</form>
</div>
</div>

<!-- Change Password Modal -->
<div id="passModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);align-items:center;justify-content:center">
<div style="background:#1e293b;border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:24px;width:400px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,.5)">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
<h3 style="margin:0;color:#e2e8f0;font-size:16px">Change Password — <span id="pass_admin_name"></span></h3>
<span onclick="closePassModal()" style="cursor:pointer;color:#64748b;font-size:20px">&times;</span>
</div>
<form method="POST" id="passForm">
<div style="margin-bottom:14px">
<label style="font-size:11px;color:#94a3b8;display:block;margin-bottom:4px">New Password (min 6 characters)</label>
<input name="password" id="pass_input" type="password" required minlength="6" style="width:100%;padding:8px 10px;font-size:12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);color:#e2e8f0;border-radius:6px">
</div>
<div style="display:flex;gap:8px;justify-content:flex-end">
<button type="button" onclick="closePassModal()" style="padding:8px 16px;font-size:12px;background:rgba(255,255,255,.05);color:#94a3b8;border:1px solid rgba(255,255,255,.08);border-radius:6px;cursor:pointer">Cancel</button>
<button type="submit" style="padding:8px 16px;font-size:12px;background:rgba(168,85,247,.2);color:#c084fc;border:1px solid rgba(168,85,247,.3);border-radius:6px;cursor:pointer">Update Password</button>
</div>
</form>
</div>
</div>

<script>
function openEditModal(id, username, email, role) {
  document.getElementById('editForm').action = '/admin/admins/edit/' + id;
  document.getElementById('edit_username').value = username;
  document.getElementById('edit_email').value = email;
  document.getElementById('edit_role').value = role;
  document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }

function openPassModal(id, username) {
  document.getElementById('passForm').action = '/admin/admins/change-password/' + id;
  document.getElementById('pass_admin_name').textContent = username;
  document.getElementById('pass_input').value = '';
  document.getElementById('passModal').style.display = 'flex';
}
function closePassModal() { document.getElementById('passModal').style.display = 'none'; }
</script>
