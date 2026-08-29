<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:20px">
<span style="color:var(--text_muted);font-size:13px"><span data-stat="total"><?php echo $stats['total'] ?? 0; ?></span> clients · <span data-stat="active"><?php echo $stats['active'] ?? 0; ?></span> active · <span data-stat="suspended"><?php echo $stats['suspended'] ?? 0; ?></span> suspended</span>
<span style="flex:1"></span>
<div style="display:flex;gap:6px;align-items:center">
<input type="text" id="clientSearch" placeholder="🔍 Search clients (username, domain, email)..." style="padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;outline:none;font-size:12px;min-width:260px">
<select id="statusFilter" style="padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;outline:none;font-size:12px">
<option value="">All statuses</option>
<option value="active">Active</option>
<option value="pending">Pending</option>
<option value="suspended">Suspended</option>
<option value="terminated">Terminated</option>
</select>
</div>
</div>

<table class="table table-hover" style="color:#fff" id="clientTable">
<thead><tr>
<th>Username</th><th>Domain</th><th>Email</th><th>Package</th><th>Actions</th><th>Status</th>
</tr></thead>
<tbody>
<?php if (!empty($accounts)): foreach ($accounts as $a): $pn = $pkgNames[$a->id] ?? '-'; ?>
<tr data-username="<?php echo htmlspecialchars(strtolower($a->username)); ?>" data-domain="<?php echo htmlspecialchars(strtolower($a->domain ?? '')); ?>" data-email="<?php echo htmlspecialchars(strtolower($a->email ?? '')); ?>" data-status="<?php echo $a->status; ?>">
<td><strong><?php echo htmlspecialchars($a->username); ?></strong></td>
<td><?php echo htmlspecialchars($a->domain ?? '-'); ?></td>
<td style="font-size:12px"><?php echo htmlspecialchars($a->email ?? '-'); ?></td>
<td><?php echo htmlspecialchars($pn); ?></td>
<td style="white-space:nowrap">
<?php if ($a->status === 'active'): ?>
<a href="/reseller/clients/suspend/<?php echo (int)$a->id; ?>" class="btn btn-sm btn-secondary" style="background:rgba(250,204,21,.1);color:#facc15;border-color:rgba(250,204,21,.2)" onclick="return confirm('Suspend <?php echo htmlspecialchars($a->username); ?>?')"><i class="bi bi-pause-circle"></i> Suspend</a>
<?php elseif ($a->status === 'suspended'): ?>
<a href="/reseller/clients/unsuspend/<?php echo (int)$a->id; ?>" class="btn btn-sm btn-secondary" style="background:rgba(74,222,128,.1);color:#4ade80;border-color:rgba(74,222,128,.2)"><i class="bi bi-play-circle"></i> Reactivate</a>
<?php endif; ?>
</td>
<td><span class="badge bg-<?php echo $a->status === 'active' ? 'success' : ($a->status === 'suspended' ? 'warning' : ($a->status === 'pending' ? 'info' : 'danger')); ?>"><?php echo ucfirst($a->status); ?></span></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text_muted)">No clients yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
<div id="clientNoResults" style="display:none;text-align:center;padding:2rem;color:var(--text_muted)">No clients match your search.</div>

<a href="/reseller" class="btn secondary" style="margin-top:12px">&larr; Back</a>

<script>
function applyClientFilter() {
    var q = (document.getElementById('clientSearch').value || '').toLowerCase().trim();
    var st = document.getElementById('statusFilter').value;
    var rows = document.querySelectorAll('#clientTable tbody tr[data-username]');
    var any = false;
    rows.forEach(function(r) {
        var match = (!q || (r.getAttribute('data-username').indexOf(q) > -1 || r.getAttribute('data-domain').indexOf(q) > -1 || r.getAttribute('data-email').indexOf(q) > -1)) && (!st || r.getAttribute('data-status') === st);
        r.style.display = match ? '' : 'none';
        if (match) any = true;
    });
    var noRes = document.getElementById('clientNoResults');
    if (noRes) noRes.style.display = (rows.length > 0 && !any) ? '' : 'none';
}
document.getElementById('clientSearch').addEventListener('input', applyClientFilter);
document.getElementById('statusFilter').addEventListener('change', applyClientFilter);
</script>