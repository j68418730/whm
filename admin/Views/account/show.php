<style>
.action-card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:10px;padding:16px;margin-bottom:12px}
.action-card h4{font-size:13px;font-weight:600;margin:0 0 8px;display:flex;align-items:center;gap:6px}
.action-card .actions{display:flex;gap:6px;flex-wrap:wrap}
.account-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}@media(max-width:768px){.account-grid{grid-template-columns:1fr}}
.acct-hero{display:flex;align-items:center;gap:16px;margin-bottom:14px}
.acct-avatar{width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#0A84FF,#00C6FF);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;color:#fff;flex-shrink:0}
.chip{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500}
.chip-product{background:rgba(10,132,255,.12);color:#38bdf8;border:1px solid rgba(10,132,255,.25)}
.chip-package{background:rgba(250,204,21,.1);color:#facc15;border:1px solid rgba(250,204,21,.2)}
.chip a{color:inherit;text-decoration:none}
.chip a:hover{text-decoration:underline}
.mini-table{width:100%;font-size:12px;border-collapse:collapse}
.mini-table th{color:#64748b;text-align:left;font-weight:600;font-size:10px;text-transform:uppercase;letter-spacing:.4px;padding:6px 8px;border-bottom:1px solid rgba(255,255,255,.06)}
.mini-table td{padding:8px;border-bottom:1px solid rgba(255,255,255,.04)}
.mini-table tr:last-child td{border-bottom:none}
.bill-tab{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px}
</style>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="card" style="background:linear-gradient(135deg,rgba(10,132,255,.06),rgba(8,16,28,.7));border-color:rgba(0,191,255,.15)">
<div class="acct-hero">
<div class="acct-avatar"><?php echo htmlspecialchars(strtoupper(substr($account->username, 0, 1))); ?></div>
<div style="flex:1">
<h2 style="margin:0 0 4px;display:flex;align-items:center;gap:10px;flex-wrap:wrap"><?php echo htmlspecialchars($account->username); ?>
<span class="badge bg-<?php echo $account->status === 'active' ? 'success' : ($account->status === 'suspended' ? 'warning' : 'danger'); ?>" style="font-size:11px"><?php echo ucfirst($account->status); ?></span>
<?php if (isset($account->no_auto_suspend) && (int)$account->no_auto_suspend === 1): ?><span class="badge bg-danger" style="font-size:10px">No auto-suspend</span><?php endif; ?>
</h2>
<div style="display:flex;gap:8px;flex-wrap:wrap;font-size:13px;color:var(--text_muted)">
<span><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($account->email); ?></span>
<?php if ($account->domain): ?><span><i class="bi bi-globe"></i> <?php echo htmlspecialchars($account->domain); ?></span><?php endif; ?>
<span><i class="bi bi-calendar"></i> <?php echo $account->created_at ?? 'N/A'; ?></span>
</div>
</div>
<div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end">
<?php if ($accountProduct): ?>
<span class="chip chip-product"><i class="bi bi-box-seam"></i> <a href="/admin/billing/products" title="View products"><?php echo htmlspecialchars($accountProduct->name); ?> — $<?php echo number_format($accountProduct->price, 2); ?>/<?php echo htmlspecialchars($accountProduct->billing_cycle ?? 'once'); ?></a></span>
<?php endif; ?>
<?php if ($package): ?>
<span class="chip chip-package"><i class="bi bi-cpu"></i> Package: <a href="/admin/package/edit/<?php echo (int)$package->id; ?>"><?php echo htmlspecialchars($package->name); ?></a></span>
<?php else: ?>
<span class="chip chip-package">No package assigned</span>
<?php endif; ?>
<?php if ($account->reseller_id): ?>
<span class="chip" style="background:rgba(167,139,250,.1);color:#a78bfa;border:1px solid rgba(167,139,250,.2)"><i class="bi bi-person-badge"></i> Reseller #<?php echo (int)$account->reseller_id; ?></span>
<?php endif; ?>
</div>
</div>
</div>

<div class="stats-grid">
<div class="stat-card"><h3>Services</h3><div class="value" style="font-size:20px"><?php echo count($ownedServices ?? []); ?></div></div>
<div class="stat-card"><h3>Orders</h3><div class="value" style="font-size:20px"><?php echo count($orders ?? []); ?></div></div>
<div class="stat-card"><h3>Disk Usage</h3><div class="value" style="font-size:20px"><?php echo $disk_usage; ?></div></div>
<div class="stat-card"><h3>Bandwidth</h3><div class="value" style="font-size:20px"><?php echo $bandwidth_usage; ?></div></div>
</div>

<?php if ($allProducts): ?>
<div class="card" style="border-color:rgba(0,191,255,.18)">
<h3 style="margin-bottom:10px;display:flex;align-items:center;gap:8px"><i class="bi bi-plus-circle" style="color:#0A84FF"></i> Add Service / Create Order</h3>
<form method="POST" action="/admin/account/order/create/<?php echo (int)$account->id; ?>" class="row g-2" style="gap:8px">
<div class="col-md-3 form-group" style="margin-bottom:0"><label>Product *</label>
<select name="product_id" id="newOrdProduct" required class="form-control" onchange="fillOrderProduct()">
<option value="">-- Select product --</option>
<?php foreach ($allProducts as $ap): ?>
<option value="<?php echo $ap->id; ?>" data-price="<?php echo $ap->price; ?>" data-cycle="<?php echo htmlspecialchars($ap->billing_cycle ?? ''); ?>"><?php echo htmlspecialchars($ap->name); ?> — $<?php echo number_format($ap->price, 2); ?>/<?php echo htmlspecialchars($ap->billing_cycle ?? 'once'); ?></option>
<?php endforeach; ?>
</select></div>
<div class="col-md-2 form-group" style="margin-bottom:0"><label>Total ($)</label>
<input type="number" name="total" id="newOrdTotal" step="0.01" min="0" value="0.00" required class="form-control"></div>
<div class="col-md-2 form-group" style="margin-bottom:0"><label>Type</label>
<select name="type" class="form-control"><option value="new">New</option><option value="renewal">Renewal</option><option value="upgrade">Upgrade</option><option value="downgrade">Downgrade</option></select></div>
<div class="col-md-2 form-group" style="margin-bottom:0"><label>Status</label>
<select name="status" class="form-control"><option value="pending">Pending</option><option value="active">Active</option><option value="suspended">Suspended</option><option value="cancelled">Cancelled</option></select></div>
<div class="col-md-3 form-group" style="margin-bottom:0"><label>Hosting Package</label>
<select name="package_id" class="form-control"><option value="">-- None --</option>
<?php foreach ($activePackages as $pkg): ?><option value="<?php echo $pkg->id; ?>"><?php echo htmlspecialchars($pkg->name); ?></option><?php endforeach; ?>
</select></div>
<div class="col-md-3 form-group" style="margin-bottom:0"><label>Next Due</label>
<input type="date" name="next_due_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"></div>
<div class="col-md-3 form-group" style="margin-bottom:0"><label>Description</label>
<input type="text" name="description" class="form-control" placeholder="Order note / description"></div>
<div class="col-md-3 d-flex align-items-end" style="margin-bottom:0;gap:8px">
<label style="display:flex;align-items:center;gap:6px;font-size:12px;color:#94a3b8;cursor:pointer;margin:0"><input type="checkbox" name="create_service" value="1" checked style="accent-color:#0A84FF"> Activate service now</label>
<button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create Order</button>
</div>
</form>
</div>
<?php endif; ?>

<div class="account-grid">
<div class="action-card">
<h4><i class="bi bi-box-seam" style="color:#0A84FF"></i> Products & Services (<?php echo count($ownedServices ?? []); ?>)</h4>
<?php
$kindIcon = ['hosting'=>'bi-house-gear','radio'=>'bi-broadcast','chatbox'=>'bi-chat-dots','game'=>'bi-controller','billing'=>'bi-receipt'];
$kindColor = ['hosting'=>'#38bdf8','radio'=>'#a78bfa','chatbox'=>'#4ade80','game'=>'#fb923c','billing'=>'#94a3b8'];
$kindLabel = ['hosting'=>'Hosting','radio'=>'Radio','chatbox'=>'Chatbox','game'=>'Game','billing'=>'Billing'];
?>
<?php if (!empty($ownedServices)): ?>
<table class="mini-table">
<tr><th>Type</th><th>Product / Service</th><th>Status</th><th>Details</th><th></th></tr>
<?php foreach ($ownedServices as $s):
    $kind = $s->kind ?? 'billing';
    $icon = $kindIcon[$kind] ?? 'bi-box';
    $col = $kindColor[$kind] ?? '#94a3b8';
    $lbl = $kindLabel[$kind] ?? ucfirst($kind);
    $status = $s->status ?? 'pending';
    $statusBadge = in_array($status, ['active','running']) ? 'success' : ($status === 'suspended' ? 'warning' : 'secondary');
?>
<tr>
<td style="white-space:nowrap"><span class="chip" style="background:rgba(<?php echo $kind==='hosting'?'56,189,248':($kind==='radio'?'167,139,250':($kind==='chatbox'?'74,222,128':($kind==='game'?'251,146,60':'148,163,184'))); ?>,.12);color:<?php echo $col; ?>;border:1px solid rgba(<?php echo $kind==='hosting'?'56,189,248':($kind==='radio'?'167,139,250':($kind==='chatbox'?'74,222,128':($kind==='game'?'251,146,60':'148,163,184'))); ?>,.25)"><i class="bi <?php echo $icon; ?>"></i> <?php echo $lbl; ?></span></td>
<td>
<div style="font-weight:600"><?php echo htmlspecialchars($s->name ?? ''); ?></div>
<?php if ($s->product_id): ?><a href="/admin/billing/products" style="color:#38bdf8;font-size:11px" title="View product list"><i class="bi bi-box-seam"></i> Product #<?php echo (int)$s->product_id; ?> →</a>
<?php elseif (!empty($s->manage_url)): ?><a href="<?php echo htmlspecialchars($s->manage_url); ?>" style="color:#38bdf8;font-size:11px" title="Manage this service"><i class="bi bi-arrow-right-circle"></i> Manage →</a><?php endif; ?>
</td>
<td>
<?php if ($kind === 'billing'): ?>
<form method="POST" action="/admin/account/service/status/<?php echo (int)$account->id; ?>/<?php echo (int)$s->ref_id; ?>" style="display:flex;align-items:center;gap:4px">
<select name="status" class="form-select" style="width:auto;padding:3px 6px;font-size:11px" onchange="this.form.submit()">
<option value="active" <?php echo $status==='active'?'selected':''; ?>>Active</option>
<option value="pending" <?php echo $status==='pending'?'selected':''; ?>>Pending</option>
<option value="suspended" <?php echo $status==='suspended'?'selected':''; ?>>Suspended</option>
<option value="terminated" <?php echo $status==='terminated'?'selected':''; ?>>Terminated</option>
</select>
<input type="hidden" name="next_due_date" value="<?php echo htmlspecialchars($s->next_due_date ?? ''); ?>">
</form>
<?php else: ?>
<span class="badge bg-<?php echo $statusBadge; ?>"><?php echo htmlspecialchars(ucfirst((string)$status)); ?></span>
<?php endif; ?>
</td>
<td style="white-space:nowrap;font-size:11px;color:#94a3b8"><?php echo htmlspecialchars($s->detail ?? ''); ?><?php if ($s->next_due_date): ?><br>Due <?php echo htmlspecialchars($s->next_due_date); ?><?php endif; ?></td>
<td>
<?php if ($kind === 'billing'): ?>
<a href="/admin/account/service/delete/<?php echo (int)$account->id; ?>/<?php echo (int)$s->ref_id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.2)" onclick="return confirm('Remove this service?')"><i class="bi bi-trash"></i></a>
<?php elseif (!empty($s->manage_url)): ?>
<a href="<?php echo htmlspecialchars($s->manage_url); ?>" class="btn btn-sm" style="background:rgba(56,189,248,.1);color:#38bdf8;border:1px solid rgba(56,189,248,.2)"><i class="bi bi-arrow-right"></i></a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p style="font-size:12px;color:var(--text_muted)">No services yet. Use <strong>Add Service</strong> above to attach a product to this account.</p>
<?php endif; ?>
</div>

<div class="action-card">
<h4><i class="bi bi-receipt" style="color:#38bdf8"></i> Orders (<?php echo count($orders ?? []); ?>)</h4>
<?php if (!empty($orders)): ?>
<table class="mini-table">
<tr><th>#</th><th>Type</th><th>Status</th><th>Total</th><th>Date</th><th></th></tr>
<?php foreach ($orders as $o): ?>
<tr>
<td><strong>#<?php echo $o->id; ?></strong></td>
<td style="text-transform:capitalize"><?php echo htmlspecialchars($o->type ?? 'new'); ?></td>
<td>
<form method="POST" action="/admin/account/order/status/<?php echo (int)$account->id; ?>/<?php echo (int)$o->id; ?>" style="display:flex;align-items:center;gap:4px">
<select name="status" class="form-select" style="width:auto;padding:3px 6px;font-size:11px" onchange="this.form.submit()">
<option value="pending" <?php echo $o->status==='pending'?'selected':''; ?>>Pending</option>
<option value="active" <?php echo $o->status==='active'?'selected':''; ?>>Active</option>
<option value="suspended" <?php echo $o->status==='suspended'?'selected':''; ?>>Suspended</option>
<option value="cancelled" <?php echo $o->status==='cancelled'?'selected':''; ?>>Cancelled</option>
</select></form>
</td>
<td style="white-space:nowrap">$<?php echo number_format((float)$o->total, 2); ?></td>
<td style="white-space:nowrap;font-size:11px"><?php echo htmlspecialchars($o->created_at ?? '-'); ?></td>
<td><a href="/admin/account/order/delete/<?php echo (int)$account->id; ?>/<?php echo (int)$o->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.2)" onclick="return confirm('Delete this order? Linked services will be removed too.')"><i class="bi bi-trash"></i></a></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p style="font-size:12px;color:var(--text_muted)">No orders yet.</p>
<?php endif; ?>
</div>
</div>

<div class="card">
<h3 style="margin-bottom:12px">Account Details</h3>
<div style="display:grid;grid-template-columns:160px 1fr;gap:6px;font-size:13px">
<span style="color:var(--text_muted)">Email</span><span><?php echo htmlspecialchars($account->email); ?></span>
<span style="color:var(--text_muted)">Name</span><span><?php echo htmlspecialchars(($account->first_name??'') . ' ' . ($account->last_name??'')); ?></span>
<span style="color:var(--text_muted)">PHP Version</span><span><?php echo $account->php_version ?: 'Server default'; ?></span>
<span style="color:var(--text_muted)">Home Dir</span><span><code>/home/<?php echo htmlspecialchars($account->username); ?>/</code></span>
<span style="color:var(--text_muted)">Created</span><span><?php echo $account->created_at ?? 'N/A'; ?></span>
</div>
</div>

<div class="card" style="border-color:rgba(250,204,21,.25);background:linear-gradient(135deg,rgba(250,204,21,.05),rgba(8,16,28,.6))">
<h3 style="margin-bottom:12px;display:flex;align-items:center;gap:8px"><i class="bi bi-crown" style="color:#facc15"></i> Master Owner</h3>
<div style="display:grid;grid-template-columns:160px 1fr;gap:6px;font-size:13px">
<span style="color:var(--text_muted)">Owner</span><span><strong>Root</strong> <span style="color:#64748b">(Master Owner)</span></span>
<span style="color:var(--text_muted)">Reseller</span><span><?php if ($account->reseller_id): $resellerName = ''; foreach (($resellers ?? []) as $rr) { if ((int)$rr->id === (int)$account->reseller_id) { $resellerName = ($rr->company_name ?? $rr->contact_name ?? 'Reseller #' . $rr->id); break; } } echo htmlspecialchars($resellerName) . ' <a href="/admin/reseller/show/' . (int)$account->reseller_id . '" style="color:#38bdf8;font-size:12px">View →</a>'; else: ?><strong>Root</strong> <span style="color:#4ade80">(direct, no reseller)</span><?php endif; ?></span>
<span style="color:var(--text_muted)">Owner Email</span><span>admin@planet-hosts.com</span>
</div>
</div>

<div class="account-grid">
<div class="action-card">
<h4><i class="bi bi-pencil-square" style="color:var(--primary)"></i> Modify Account</h4>
<div class="actions">
<a href="/admin/account/edit/<?php echo $account->id; ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i> Edit Account</a>
</div>
</div>

<div class="action-card">
<h4><i class="bi bi-pause-circle" style="color:#facc15"></i> Suspend / Unsuspend</h4>
<div class="actions">
<a href="/admin/account/suspend/<?php echo $account->id; ?>" class="btn btn-sm" style="background:rgba(250,204,21,.12);color:#facc15;border:1px solid rgba(250,204,21,.2)" onclick="return confirm('Suspend this account?')"><i class="bi bi-pause-circle"></i> Suspend</a>
<a href="/admin/account/unsuspend/<?php echo $account->id; ?>" class="btn btn-sm" style="background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.15)"><i class="bi bi-play-circle"></i> Unsuspend</a>
</div>
<div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.06)">
<form method="POST" action="/admin/account/allow-suspension/<?php echo $account->id; ?>" style="display:flex;align-items:center;gap:10px">
<span style="font-size:12px;color:var(--text_muted)">Allow automatic suspension (quota/billing):</span>
<select name="allow_suspension" style="width:auto;padding:5px 10px;font-size:12px" onchange="this.form.submit()">
<option value="1" <?php echo (($account->allow_suspension ?? 1) == 1) ? 'selected' : ''; ?>>Yes</option>
<option value="0" <?php echo (($account->allow_suspension ?? 1) == 0) ? 'selected' : ''; ?>>No</option>
</select>
<button class="btn btn-sm btn-secondary" style="padding:4px 10px">Save</button>
</form>
<p style="font-size:11px;color:<?php echo (($account->allow_suspension ?? 1) == 1) ? '#4ade80' : '#f87171'; ?>;margin-top:6px">
<?php echo (($account->allow_suspension ?? 1) == 1) ? '🟢 This account CAN be auto-suspended when over quota.' : '🔴 Auto-suspension is DISABLED for this account (manual only).'; ?>
</p>
</div>
<div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.06)">
<form method="POST" action="/admin/account/toggle-no-auto-suspend/<?php echo $account->id; ?>" style="display:flex;align-items:center;gap:10px">
<span style="font-size:12px;color:var(--text_muted)">Prevent ALL auto-suspension (billing + cron):</span>
<select name="no_auto_suspend" style="width:auto;padding:5px 10px;font-size:12px" onchange="this.form.submit()">
<option value="1" <?php echo (($account->no_auto_suspend ?? 0) == 1) ? 'selected' : ''; ?>>Yes — Never auto-suspend</option>
<option value="0" <?php echo (($account->no_auto_suspend ?? 0) == 0) ? 'selected' : ''; ?>>No — Follow normal rules</option>
</select>
<button class="btn btn-sm btn-secondary" style="padding:4px 10px">Save</button>
</form>
<p style="font-size:11px;color:<?php echo (($account->no_auto_suspend ?? 0) == 1) ? '#f87171' : '#4ade80'; ?>;margin-top:6px">
<?php echo (($account->no_auto_suspend ?? 0) == 1) ? '🔴 This account will NEVER be auto-suspended (manual only).' : '🟢 Normal auto-suspension rules apply.'; ?>
</p>
</div>
</div>

<div class="action-card">
<h4><i class="bi bi-x-octagon" style="color:#f87171"></i> Terminate / Delete</h4>
<div class="actions">
<a href="/admin/account/terminate/<?php echo $account->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Terminate this account? This will delete the Linux user and all files.')"><i class="bi bi-x-octagon"></i> Terminate</a>
<a href="/admin/account/delete/<?php echo $account->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171;border:1px solid rgba(248,113,113,.3)" onclick="return confirm('DELETE this account permanently? This cannot be undone.')"><i class="bi bi-trash"></i> Delete Permanently</a>
</div>
</div>

<div class="action-card">
<h4><i class="bi bi-arrow-left-right" style="color:#38bdf8"></i> Change Ownership</h4>
<form method="POST" action="/admin/account/change-owner/<?php echo $account->id; ?>" class="actions" style="flex-wrap:wrap">
<select name="reseller_id" style="width:auto;padding:6px 10px;font-size:12px;flex:1"><option value="">No reseller</option>
<?php foreach ($resellers as $r): ?><option value="<?php echo $r->id; ?>"><?php echo htmlspecialchars($r->name ?? $r->username ?? 'Reseller #' . $r->id); ?></option><?php endforeach; ?></select>
<input name="owner_email" placeholder="New owner email" style="width:auto;padding:6px 10px;font-size:12px;flex:1.5">
<button class="btn btn-sm btn-secondary" style="padding:6px 12px">Transfer</button>
</form>
</div>

<div class="action-card">
<h4><i class="bi bi-key" style="color:#a78bfa"></i> Password Reset</h4>
<form method="POST" action="/admin/account/password/<?php echo $account->id; ?>" class="actions">
<input type="password" name="password" required minlength="8" placeholder="New password" style="flex:1;padding:6px 10px;font-size:12px">
<button class="btn btn-sm btn-primary">Change Password</button>
</form>
</div>

<div class="action-card">
<h4><i class="bi bi-person-check" style="color:#4ade80"></i> Login As User</h4>
<div class="actions">
<a href="/admin/account/login-as/<?php echo $account->id; ?>" class="btn btn-sm btn-primary" onclick="return confirm('Login as <?php echo htmlspecialchars($account->username); ?>?')"><i class="bi bi-box-arrow-in-right"></i> Login as <?php echo htmlspecialchars($account->username); ?></a>
</div>
</div>

<div class="action-card">
<h4><i class="bi bi-box-seam" style="color:#fb923c"></i> Package Assignment</h4>
<form method="POST" action="/admin/package/upgrade/<?php echo $account->id; ?>" class="actions">
<select name="package_id" style="width:auto;padding:6px 10px;font-size:12px;flex:1">
<?php foreach ($packages as $p): ?><option value="<?php echo $p->id; ?>" <?php echo ($package && $package->id === $p->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option><?php endforeach; ?></select>
<button class="btn btn-sm btn-secondary" style="padding:6px 12px">Assign</button>
</form>
</div>

<div class="action-card">
<h4><i class="bi bi-archive" style="color:#34d399"></i> Account Backups</h4>
<div class="actions">
<a href="/admin/backup" class="btn btn-sm btn-secondary"><i class="bi bi-camera"></i> Create Backup</a>
</div>
<?php if (!empty($backup_files)): ?>
<div style="margin-top:8px;max-height:120px;overflow-y:auto">
<?php foreach (array_slice($backup_files, 0, 5) as $bf): $bn = basename($bf); $sz = filesize($bf); ?>
<div style="display:flex;justify-content:space-between;padding:3px 0;font-size:11px;border-bottom:1px solid rgba(255,255,255,.04)">
<span><?php echo htmlspecialchars($bn); ?></span>
<span><?php echo $sz > 1048576 ? round($sz/1048576,1).' MB' : round($sz/1024,1).' KB'; ?></span>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p style="font-size:11px;color:var(--text_muted);margin-top:6px">No backup files found.</p>
<?php endif; ?>
</div>

<div class="action-card">
<h4><i class="bi bi-speedometer2" style="color:#38bdf8"></i> Bandwidth Usage</h4>
<p style="font-size:13px;margin:4px 0"><?php echo $bandwidth_usage; ?></p>
<?php if ($package && $package->bandwidth): ?>
<div class="progress" style="height:6px;margin-top:4px"><div class="progress-bar" style="width:0%;background:#38bdf8"></div></div>
<?php endif; ?>
</div>

<div class="action-card">
<h4><i class="bi bi-hdd-stack" style="color:#facc15"></i> Disk Usage</h4>
<p style="font-size:13px;margin:4px 0"><?php echo $disk_usage; ?></p>
<?php if ($package && $package->disk_space): ?>
<?php
$diskVal = (float)($disk_usage ? str_replace(' MB','',$disk_usage) : 0);
$diskPct = $package->disk_space > 0 ? min(100, round($diskVal / ($package->disk_space * 1024) * 100)) : 0;
?>
<div class="progress" style="height:6px;margin-top:4px"><div class="progress-bar" style="width:<?php echo $diskPct; ?>%;background:#facc15"></div></div>
<?php endif; ?>
</div>

<div class="action-card">
<h4><i class="bi bi-rocket-takeoff" style="color:#a78bfa"></i> Quick Install</h4>
<div class="actions" style="flex-wrap:wrap">
<form method="POST" action="/admin/installers/install" style="display:inline-flex;gap:6px;align-items:center;flex-wrap:wrap">
<input type="hidden" name="app_name" value="WordPress">
<input type="hidden" name="account_id" value="<?php echo $account->id; ?>">
<input type="hidden" name="domain" value="<?php echo htmlspecialchars($account->domain ?? ($account->username . '.planet-hosts.com')); ?>">
<button type="submit" class="btn btn-sm" style="background:rgba(15,117,188,.15);color:#0a84ff;border:1px solid rgba(15,117,188,.2)">📝 Install WordPress</button>
</form>
<form method="POST" action="/admin/installers/install" style="display:inline-flex;gap:6px;align-items:center">
<input type="hidden" name="app_name" value="phpMyAdmin">
<input type="hidden" name="account_id" value="<?php echo $account->id; ?>">
<input type="hidden" name="domain" value="<?php echo htmlspecialchars($account->domain ?? ($account->username . '.planet-hosts.com')); ?>">
<input type="hidden" name="directory" value="phpmyadmin">
<button type="submit" class="btn btn-sm" style="background:rgba(250,204,21,.12);color:#facc15;border:1px solid rgba(250,204,21,.2)">🗄️ Install phpMyAdmin</button>
</form>
<form method="POST" action="/admin/installers/install" style="display:inline-flex;gap:6px;align-items:center">
<input type="hidden" name="app_name" value="Laravel">
<input type="hidden" name="account_id" value="<?php echo $account->id; ?>">
<input type="hidden" name="domain" value="<?php echo htmlspecialchars($account->domain ?? ($account->username . '.planet-hosts.com')); ?>">
<input type="hidden" name="directory" value="laravel">
<button type="submit" class="btn btn-sm" style="background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.15)">⚡ Install Laravel</button>
</form>
<a href="/admin/installers" class="btn btn-sm btn-secondary" style="font-size:11px">More Apps →</a>
</div>
</div>

<div class="action-card">
<h4><i class="bi bi-megaphone" style="color:#facc15"></i> Send Alert</h4>
<form method="POST" action="/admin/account/send-alert/<?php echo $account->id; ?>" class="actions" style="flex-direction:column;gap:6px">
<input name="alert_title" placeholder="Alert title" required style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px;outline:none">
<textarea name="alert_message" placeholder="Alert message" required rows="2" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px;outline:none"></textarea>
<div style="display:flex;gap:6px">
<select name="alert_type" style="padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px;outline:none">
<option value="info">ℹ️ Info</option><option value="warning">⚠️ Warning</option><option value="success">✅ Success</option><option value="danger">⛔ Urgent</option>
</select>
<button type="submit" class="btn btn-sm primary">Send Alert</button>
</div>
</form>
</div>

<div class="action-card" style="grid-column:1/-1">
<h4><i class="bi bi-server" style="color:#0A84FF"></i> Apache Virtual Host</h4>
<?php
$vhostPath = '/etc/apache2/sites-available/' . $account->username . '.conf';
$vhostSslPath = '/etc/apache2/sites-available/' . $account->username . '-ssl.conf';
$vhostContent = @file_get_contents($vhostPath);
$vhostSslContent = @file_get_contents($vhostSslPath);
?>
<?php if ($vhostContent): ?>
<pre style="background:rgba(0,0,0,.5);border:1px solid rgba(0,191,255,.1);border-radius:8px;padding:12px;font-size:12px;overflow-x:auto;color:#e0e0e0;margin:0 0 8px"><?php echo htmlspecialchars($vhostContent); ?></pre>
<?php else: ?>
<p style="font-size:12px;color:var(--text_muted)">No HTTP vhost found at <code><?php echo htmlspecialchars($vhostPath); ?></code></p>
<?php endif; ?>
<?php if ($vhostSslContent): ?>
<pre style="background:rgba(0,0,0,.5);border:1px solid rgba(0,191,255,.1);border-radius:8px;padding:12px;font-size:12px;overflow-x:auto;color:#e0e0e0;margin:0"><?php echo htmlspecialchars($vhostSslContent); ?></pre>
<?php endif; ?>
</div>

<div class="action-card" style="grid-column:1/-1">
<h4><i class="bi bi-clock-history" style="color:#94a3b8"></i> Account History</h4>
<?php if (!empty($history)): ?>
<table style="font-size:12px"><tr><th>Action</th><th>Details</th><th>Date</th></tr>
<?php foreach ($history as $h): ?>
<tr><td><?php echo htmlspecialchars($h->action ?? '-'); ?></td><td><?php echo htmlspecialchars($h->details ?? '-'); ?></td><td style="white-space:nowrap"><?php echo htmlspecialchars($h->created_at ?? '-'); ?></td></tr>
<?php endforeach; ?></table>
<?php else: ?>
<p style="font-size:12px;color:var(--text_muted)">No history recorded yet.</p>
<?php endif; ?>
</div>
</div>

<script>
function fillOrderProduct() {
    var sel = document.getElementById('newOrdProduct');
    var opt = sel.options[sel.selectedIndex];
    if (opt && opt.value && document.getElementById('newOrdTotal').value === '0.00') {
        document.getElementById('newOrdTotal').value = opt.getAttribute('data-price') || '0.00';
    }
}
</script>

<style>
code{background:rgba(255,255,255,.06);padding:2px 6px;border-radius:4px;font-size:12px}
</style>