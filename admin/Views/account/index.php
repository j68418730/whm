<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<style>
.acct-group{margin-bottom:22px;border:1px solid var(--border,rgba(0,191,255,.08));border-radius:12px;overflow:hidden;background:var(--card_bg,rgba(8,16,28,.6))}
.acct-group-head{display:flex;align-items:center;justify-content:center;gap:10px;padding:9px 18px;background:linear-gradient(135deg,rgba(0,191,255,.10),rgba(0,140,255,.03));border-bottom:1px solid var(--border,rgba(0,191,255,.08));cursor:pointer;user-select:none;transition:.15s}
.acct-group-head:hover{background:linear-gradient(135deg,rgba(0,191,255,.16),rgba(0,140,255,.06))}
.acct-group-head .g-title{font-weight:700;font-size:14px;color:var(--accent,#008cff);letter-spacing:.3px}
.acct-group-head .g-count{background:rgba(0,140,255,.15);color:#38bdf8;border-radius:20px;padding:2px 10px;font-size:11px;font-weight:700}
.acct-group-head .g-toggle{color:#64748b;font-size:11px;transition:transform .2s;margin-left:4px}
.acct-group.collapsed .g-toggle{transform:rotate(-90deg)}
.acct-group.collapsed .acct-table-wrap{display:none}
.acct-table-wrap{overflow-x:auto;margin-top:0}
.acct-table-wrap table{width:100%;margin:0}
.acct-table-wrap thead th{padding-top:6px;padding-bottom:6px}
.acct-table-wrap tbody tr:first-child td{padding-top:8px}
.acct-group-sep{height:14px;display:flex;align-items:center;justify-content:center}
.acct-group-sep::after{content:'•••';color:#334155;font-size:10px;letter-spacing:6px}
.acct-owner-cell{white-space:nowrap;font-size:11px;color:#94a3b8}
</style>

<div class="page-header">
<div class="d-flex justify-content-between align-items-center">
<h2 style="margin:0; color:var(--accent, #008cff)">👥 Account Management</h2>
<a href="/admin/account/create" class="btn btn-primary"><i class="bi bi-plus me-2"></i> Create Account</a>
</div>
<div style="margin-top:8px;font-size:13px;color:var(--text_muted)">Accounts grouped by <strong style="color:var(--accent)">Owner</strong>. Click a group header to expand/collapse.</div>
</div>

<div class="card" style="margin-bottom:14px;padding:12px">
<form method="GET" action="/admin/account" class="d-flex" style="gap:8px;flex-wrap:wrap;align-items:center">
<div style="position:relative;flex:1;min-width:200px">
<input type="text" name="search" value="<?php echo htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search username, domain, email, name…" style="width:100%;padding:9px 34px 9px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none">
<i class="bi bi-search" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:#64748b"></i>
</div>
<select name="reseller_id" style="padding:9px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none;width:auto">
<option value="">All owners</option>
<?php foreach (($resellers ?? []) as $rr): ?>
<option value="<?php echo $rr->id; ?>" <?php echo ((int)$reseller_id === (int)$rr->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($rr->company_name ?: $rr->name ?: ('Reseller #' . $rr->id)); ?></option>
<?php endforeach; ?>
</select>
<button type="submit" class="btn btn-secondary" style="padding:8px 16px">Filter</button>
<?php if ($search || $reseller_id): ?>
<a href="/admin/account" class="btn btn-outline-secondary" style="padding:8px 16px"><i class="bi bi-x-lg"></i> Clear</a>
<?php endif; ?>
</form>
</div>

<?php
$allAccounts = $accountGroups ?? [];
$totalAccounts = count($accounts ?? []);
$activeCount = 0; $suspendedCount = 0; $terminatedCount = 0;
foreach ($allAccounts as $group) {
    foreach ($group as $a) {
        $st = $a->status ?? 'active';
        if ($st === 'active') $activeCount++;
        elseif ($st === 'suspended') $suspendedCount++;
        elseif ($st === 'terminated') $terminatedCount++;
    }
}
?>
<div class="stats-grid">
<div class="stat-card"><h3>Total Accounts</h3><div class="value"><?php echo $totalAccounts; ?></div></div>
<div class="stat-card"><h3>Active</h3><div class="value" style="color:var(--success, #4ade80)"><?php echo $activeCount; ?></div></div>
<div class="stat-card"><h3>Suspended</h3><div class="value" style="color:var(--warning, #facc15)"><?php echo $suspendedCount; ?></div></div>
<div class="stat-card"><h3>Terminated</h3><div class="value" style="color:var(--danger, #f87171)"><?php echo $terminatedCount; ?></div></div>
</div>

<?php
$groupCount = count($allAccounts);
$gi = 0;
foreach ($allAccounts as $ownerName => $group):
    $gi++;
    $groupAccounts = count($group);
?>
<div class="acct-group" data-owner="<?php echo htmlspecialchars($ownerName, ENT_QUOTES, 'UTF-8'); ?>">
<div class="acct-group-head" onclick="toggleGroup(this)">
<span class="g-title"><?php echo htmlspecialchars($ownerName); ?></span>
<span class="g-count"><?php echo $groupAccounts; ?> account<?php echo $groupAccounts === 1 ? '' : 's'; ?></span>
<span class="g-toggle"><i class="bi bi-chevron-down"></i></span>
</div>
<div class="acct-table-wrap">
<table class="table table-bordered align-middle mb-0">
<thead><tr><th>Owner</th><th>Username</th><th>Domain</th><th>Package</th><th>Products / Services</th><th>Disk</th><th>Last Order</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($group as $a): ?>
<?php
$pkg = $packageMap[$a->package_id] ?? null;
$svcs = $servicesByUser[(int)$a->id] ?? [];
$svcCount = count($svcs);
$svcActive = count(array_filter($svcs, function($s) { return $s->status === 'active'; }));
$lastOrder = $latestOrderByUser[(int)$a->id] ?? null;
$disk = $diskUsageByUser[$a->username] ?? '-';
$ownerCell = $a->reseller_id ? ('Reseller #' . $a->reseller_id) : 'Root';
?>
<tr>
<td class="acct-owner-cell"><?php echo htmlspecialchars($ownerCell); ?></td>
<td><strong><?php echo htmlspecialchars($a->username); ?></strong>
<?php if (isset($a->no_auto_suspend) && (int)$a->no_auto_suspend === 1): ?><br><span class="badge bg-danger" style="font-size:10px">No auto-suspend</span><?php endif; ?>
</td>
<td><?php echo htmlspecialchars($a->domain ?: '—'); ?></td>
<td><?php echo $pkg ? htmlspecialchars($pkg->name) : ($a->package_id ? 'Package ' . $a->package_id : 'Free'); ?></td>
<td>
<?php if ($svcCount): ?>
<span class="badge bg-primary" title="<?php echo $svcActive . ' active / ' . ($svcCount - $svcActive) . ' inactive'; ?>"><?php echo $svcCount; ?> service<?php echo $svcCount === 1 ? '' : 's'; ?></span>
<?php foreach (array_slice($svcs, 0, 2) as $s): $pn = $productsById[$s->product_id]->name ?? 'Service #' . $s->id; ?>
<span class="badge bg-<?php echo $s->status === 'active' ? 'success' : ($s->status === 'suspended' ? 'warning' : 'secondary'); ?>" style="font-size:10px"><?php echo htmlspecialchars($pn); ?> · <?php echo $s->status; ?></span>
<?php endforeach; ?>
<?php else: ?><span style="color:#64748b;font-size:12px">—</span><?php endif; ?>
</td>
<td><?php echo htmlspecialchars($disk); ?></td>
<td><?php if ($lastOrder): ?><span class="badge bg-<?php echo $lastOrder->status === 'active' ? 'success' : ($lastOrder->status === 'pending' ? 'warning' : 'secondary'); ?>" title="Order #<?php echo $lastOrder->id; ?>">#<?php echo $lastOrder->id; ?> · <?php echo $lastOrder->status; ?></span><?php else: ?><span style="color:#64748b;font-size:12px">—</span><?php endif; ?></td>
<td><span class="status-badge status-<?php echo $a->status ?: 'active'; ?>"><?php echo ucfirst($a->status ?: 'Active'); ?></span></td>
<td style="white-space:nowrap">
<a href="/admin/account/show/<?php echo (int)$a->id; ?>" class="btn btn-sm btn-primary"><i class="bi bi-eye me-1"></i> Show</a>
<a href="/admin/account/edit/<?php echo (int)$a->id; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Edit</a>
<form method="POST" style="display:inline" action="/admin/account/suspend/<?php echo (int)$a->id; ?>" onsubmit="return confirm('Suspend this account?')">
<button class="btn btn-sm btn-danger">Suspend</button>
</form>
<a href="/admin/account/delete/<?php echo (int)$a->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.3)" onclick="return confirm('DELETE account <?php echo htmlspecialchars($a->username); ?> permanently? This cannot be undone.')"><i class="bi bi-trash"></i> Delete</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<?php if ($gi < $groupCount): ?>
<div class="acct-group-sep"></div>
<?php endif; ?>
<?php endforeach; ?>

<?php if (empty($allAccounts)): ?>
<div class="card" style="text-align:center;padding:30px;color:#64748b">No accounts found.</div>
<?php endif; ?>

<script>
function toggleGroup(head) {
    head.closest('.acct-group').classList.toggle('collapsed');
}
// Collapse all groups with 0 accounts? No — keep all expanded by default.
</script>