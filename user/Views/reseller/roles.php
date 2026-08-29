<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px">
<div>
<h2 style="margin:0"><i class="bi bi-shield-lock"></i> Roles &amp; Staff</h2>
<p style="color:var(--text_muted,#94a3b8);margin:4px 0 0">Add team members to your reseller account by permission roles. Staff can only reach areas you grant them — never other resellers or the admin panel.</p>
</div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px">

<!-- Permission Roles -->
<div class="card">
<h4 style="color:var(--accent,#008cff);margin-bottom:12px"><i class="bi bi-person-lines-fill"></i> Permission Roles</h4>
<form method="POST" action="/reseller/roles/store" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
<input name="name" placeholder="Role name" required style="flex:1;min-width:140px">
<input name="description" placeholder="Description" style="flex:1;min-width:160px">
<button class="btn btn-primary btn-sm">+ Add Role</button>
</form>
<?php if (!empty($roles)): foreach ($roles as $r): $perms = is_string($r->permissions ?? null) ? json_decode((string)$r->permissions, true) : ($r->permissions ?? []); $perms = is_array($perms) ? $perms : []; ?>
<div style="border:1px solid var(--border,rgba(0,191,255,.1));border-radius:8px;padding:10px;margin-bottom:8px">
<form method="POST" action="/reseller/roles/update/<?php echo (int)$r->id; ?>">
<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
<input style="flex:1" name="name" value="<?php echo htmlspecialchars($r->name); ?>">
<input style="flex:1" name="description" value="<?php echo htmlspecialchars($r->description ?? ''); ?>" placeholder="Description">
<button class="btn btn-sm secondary" title="Save"><i class="bi bi-check-lg"></i></button>
<a href="/reseller/roles/delete/<?php echo (int)$r->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete role <?php echo htmlspecialchars($r->name); ?>?')"><i class="bi bi-trash"></i></a>
</div>
<div style="display:flex;flex-wrap:wrap;gap:4px;font-size:12px">
<?php foreach ($permMap as $k => $label): ?>
<label style="cursor:pointer;padding:2px 8px;border-radius:99px;border:1px solid rgba(0,191,255,.15);background:rgba(0,0,0,.2)"><input type="checkbox" name="permissions[]" value="<?php echo $k; ?>" <?php echo in_array($k, $perms, true) ? 'checked' : ''; ?>> <?php echo $label; ?></label>
<?php endforeach; ?>
</div>
</form>
</div>
<?php endforeach; else: ?>
<p style="color:#64748b;font-size:13px">No custom roles yet — create one above, then assign it to staff below.</p>
<?php endif; ?>
</div>

<!-- Staff Members -->
<div class="card">
<h4 style="color:var(--accent,#008cff);margin-bottom:12px"><i class="bi bi-people"></i> Staff Members</h4>
<form method="POST" action="/reseller/staff/store" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
<input name="name" placeholder="Name" style="flex:1;min-width:120px">
<input name="email" type="email" placeholder="Email (login)" required style="flex:1;min-width:150px">
<input name="password" type="text" placeholder="Password" required style="flex:0 0 120px">
<select name="role" style="flex:0 0 120px"><option value="manager">Manager</option><option value="support">Support</option><option value="billing">Billing</option><option value="technician">Technician</option></select>
<button class="btn btn-sm primary">Add Staff</button>
</form>
<?php if (!empty($roles)): ?>
<div style="font-size:11px;color:#64748b;margin:-4px 0 12px"><b>Assign custom role templates below, then “Add Staff”.</b></div>
<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px">
<?php foreach ($roles as $r): ?>
<label style="cursor:pointer;font-size:12px;padding:4px 10px;border-radius:99px;border:1px solid rgba(0,191,255,.15);background:rgba(0,0,0,.2)">
<input type="checkbox" name="role_ids[]" value="<?php echo (int)$r->id; ?>" style="display:none"> <?php echo htmlspecialchars($r->name); ?>
</label>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php if (!empty($staff)): ?>
<table style="font-size:13px;width:100%"><tr><th>Name</th><th>Email</th><th>Role</th><th>Roles</th><th>Status</th><th></th></tr>
<?php foreach ($staff as $s): ?>
<tr>
<td><?php echo htmlspecialchars($s->name); ?></td>
<td><?php echo htmlspecialchars($s->email); ?></td>
<td><span class="badge bg-<?php echo $s->role === 'owner' ? 'warning' : ($s->role === 'support' ? 'info' : 'secondary'); ?>"><?php echo htmlspecialchars($s->role); ?></span></td>
<td><?php $sr = $staffRoles[$s->id] ?? []; $names = []; foreach ($roles as $r) { if (in_array($r->id, $sr)) $names[] = $r->name; } echo $names ? htmlspecialchars(implode(', ', $names)) : '<span style="color:#64748b">—</span>'; ?></td>
<td><?php echo $s->is_active ? '<span style="color:#4ade80">Active</span>' : '<span style="color:#f87171">Disabled</span>'; ?></td>
<td style="white-space:nowrap">
<?php if ($s->role !== 'owner'): ?>
<a href="/reseller/staff/toggle/<?php echo (int)$s->id; ?>" class="btn btn-xs secondary"><?php echo $s->is_active ? 'Disable' : 'Enable'; ?></a>
<a href="/reseller/staff/delete/<?php echo (int)$s->id; ?>" class="btn btn-xs danger" onclick="return confirm('Remove <?php echo htmlspecialchars($s->email); ?>?')">Delete</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</table>
<p style="color:#64748b;font-size:12px;margin-top:8px">Staff log in with their email + password at the reseller portal. They only see sections their role grants.</p>
<?php else: ?><p style="color:#64748b;font-size:13px">No staff yet. Add your first team member above.</p><?php endif; ?>
</div>

</div>
<a href="/reseller" class="btn secondary">&larr; Back</a>