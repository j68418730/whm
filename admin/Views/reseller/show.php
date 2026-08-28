<?php
$r = $reseller;
$rType = $r->type ?? 'web_reseller';
$typeLabel = $rType === 'icecast_reseller' ? '🎵 Radio Reseller' : '🌐 Web Reseller';
$typeColor = $rType === 'icecast_reseller' ? '#a78bfa' : '#38bdf8';
?>
<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:18px">
<div>
<h2 style="margin:0"><?php echo htmlspecialchars($r->company_name); ?></h2>
<div style="color:#64748b;font-size:13px;margin-top:4px">
<span style="font-weight:700;color:<?php echo $typeColor; ?>"><?php echo $typeLabel; ?></span>
<?php if ($pkg): ?> · <?php echo htmlspecialchars($pkg->name); ?><?php endif; ?>
 · <?php echo htmlspecialchars($r->email); ?>
<?php if ((float)$r->monthly_fee > 0): ?> · <b>$<?php echo number_format((float)$r->monthly_fee,2); ?>/<?php echo htmlspecialchars($r->billing_cycle ?? 'monthly'); ?></b><?php endif; ?>
</div>
</div>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<span class="status-badge status-<?php echo $r->is_active ? 'active' : 'terminated'; ?>" style="align-self:center"><?php echo $r->is_active ? 'Active' : 'Inactive'; ?></span>
<a href="/admin/reseller/edit/<?php echo $r->id; ?>" class="btn btn-sm secondary"><i class="bi bi-pencil"></i> Edit</a>
</div>
</div>

<!-- Sub-nav -->
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px">
<a href="/admin/reseller/show/<?php echo $r->id; ?>" class="btn btn-sm primary" style="font-size:11px">Overview</a>
<a href="/admin/reseller/resources/<?php echo $r->id; ?>" class="btn btn-sm secondary" style="font-size:11px"><i class="bi bi-speedometer2"></i> Resources</a>
<a href="/admin/reseller/pricing/<?php echo $r->id; ?>" class="btn btn-sm secondary" style="font-size:11px"><i class="bi bi-tags"></i> Pricing / Margins</a>
<a href="/admin/reseller/products/<?php echo $r->id; ?>" class="btn btn-sm secondary" style="font-size:11px"><i class="bi bi-box-seam"></i> Products</a>
<a href="/admin/reseller/branding/<?php echo $r->id; ?>" class="btn btn-sm secondary" style="font-size:11px"><i class="bi bi-palette"></i> Branding</a>
<a href="/admin/reseller/audit/<?php echo $r->id; ?>" class="btn btn-sm secondary" style="font-size:11px"><i class="bi bi-clock-history"></i> Audit Log</a>
</div>

<div class="stats-grid" style="margin-bottom:18px">
<div class="stat-card"><h3>Customers</h3><div class="value"><?php echo count($accounts); ?></div></div>
<div class="stat-card"><h3>Active Services</h3><div class="value"><?php echo (int)$services; ?></div></div>
<div class="stat-card"><h3>Staff</h3><div class="value"><?php echo count($staff); ?></div></div>
<div class="stat-card"><h3>API Keys</h3><div class="value"><?php echo count($keys); ?></div></div>
</div>

<?php if (!empty($_SESSION['reseller_api_key_' . $r->id])): ?>
<div class="alert" style="background:rgba(250,204,21,.1);border:1px solid rgba(250,204,21,.25);color:#facc15">
<b>⚠️ API Key (copy now, shown once):</b> <code style="color:#fff"><?php echo htmlspecialchars($_SESSION['reseller_api_key_' . $r->id]); ?></code>
<?php unset($_SESSION['reseller_api_key_' . $r->id]); ?>
</div>
<?php endif; ?>

<!-- Recent customers -->
<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px"><i class="bi bi-people"></i> Recent Customers</h4>
<?php if (!empty($customers)): ?>
<table style="font-size:13px"><tr><th>Username</th><th>Domain</th><th>Created</th><th>Status</th></tr>
<?php foreach ($customers as $c): ?>
<tr><td><?php echo htmlspecialchars($c->username); ?></td><td><?php echo htmlspecialchars($c->domain ?? '-'); ?></td><td><?php echo htmlspecialchars(substr($c->created_at ?? '',0,10)); ?></td><td><?php echo htmlspecialchars($c->status ?? ''); ?></td></tr>
<?php endforeach; ?>
</table>
<?php else: ?><p style="color:#64748b;font-size:13px">No customers assigned yet.</p><?php endif; ?>
</div>

<!-- Staff -->
<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px"><i class="bi bi-person-badge"></i> Staff &amp; Permissions</h4>
<form method="POST" action="/admin/reseller/staff/create/<?php echo $r->id; ?>" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
<input name="name" placeholder="Staff name" style="flex:1;min-width:140px">
<input name="email" type="email" placeholder="Email" required style="flex:1;min-width:160px">
<input name="password" type="text" placeholder="Password" required style="flex:0 0 130px">
<select name="role" style="flex:0 0 120px"><option value="manager">Manager</option><option value="support">Support</option><option value="billing">Billing</option><option value="technician">Technician</option></select>
<button class="btn btn-sm primary">Add Staff</button>
</form>
<?php if (!empty($staff)): ?>
<table style="font-size:13px"><tr><th>Name</th><th>Email</th><th>Role</th><th>2FA</th><th></th></tr>
<?php foreach ($staff as $s): ?>
<tr>
<td><?php echo htmlspecialchars($s->name); ?></td>
<td><?php echo htmlspecialchars($s->email); ?></td>
<td><?php echo htmlspecialchars($s->role); ?></td>
<td><?php echo $s->twofa_enabled ? '✅' : '—'; ?></td>
<td style="white-space:nowrap">
<a href="/admin/reseller/staff/toggle/<?php echo $r->id; ?>/<?php echo $s->id; ?>" class="btn btn-xs secondary"><?php echo $s->is_active ? 'Disable' : 'Enable'; ?></a>
<a href="/admin/reseller/staff/delete/<?php echo $r->id; ?>/<?php echo $s->id; ?>" class="btn btn-xs danger" onclick="return confirm('Remove this staff member?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?><p style="color:#64748b;font-size:13px">No staff yet.</p><?php endif; ?>
</div>

<!-- API keys -->
<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px"><i class="bi bi-key"></i> API Keys</h4>
<form method="POST" action="/admin/reseller/api/create/<?php echo $r->id; ?>" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
<input name="name" placeholder="Key name" style="flex:1;min-width:180px">
<button class="btn btn-sm primary"><i class="bi bi-plus-lg"></i> Generate Key</button>
</form>
<?php if (!empty($keys)): ?>
<table style="font-size:13px"><tr><th>Name</th><th>Created</th><th>Status</th><th></th></tr>
<?php foreach ($keys as $k): ?>
<tr>
<td><?php echo htmlspecialchars($k->name); ?></td>
<td><?php echo htmlspecialchars(substr($k->created_at ?? '',0,10)); ?></td>
<td><?php echo $k->is_active ? '✅' : '🔴'; ?></td>
<td style="white-space:nowrap">
<a href="/admin/reseller/api/toggle/<?php echo $r->id; ?>/<?php echo $k->id; ?>" class="btn btn-xs secondary">Toggle</a>
<a href="/admin/reseller/api/delete/<?php echo $r->id; ?>/<?php echo $k->id; ?>" class="btn btn-xs danger" onclick="return confirm('Delete API key?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?><p style="color:#64748b;font-size:13px">No API keys.</p><?php endif; ?>
</div>

<!-- Recent audit -->
<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px"><i class="bi bi-clock-history"></i> Recent Audit</h4>
<?php if (!empty($audit)): ?>
<table style="font-size:13px"><tr><th>Action</th><th>By</th><th>IP</th><th>When</th></tr>
<?php foreach ($audit as $a): ?>
<tr><td><?php echo htmlspecialchars($a->action); ?></td><td><?php echo htmlspecialchars($a->staff_email ?? '-'); ?></td><td><?php echo htmlspecialchars($a->ip_address ?? '-'); ?></td><td><?php echo htmlspecialchars($a->created_at ?? ''); ?></td></tr>
<?php endforeach; ?>
</table>
<?php else: ?><p style="color:#64748b;font-size:13px">No activity yet.</p><?php endif; ?>
</div>