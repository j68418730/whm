<div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:20px">
<a href="/admin/account/create" class="btn btn-primary"><i class="bi bi-person-plus"></i> Create Account</a>
<span style="color:var(--text_muted);font-size:13px"><span data-stat="total"><?php echo $accountsStats['total_accounts']; ?></span> accounts · <span data-stat="active"><?php echo $accountsStats['active_accounts']; ?></span> active · <span data-stat="suspended"><?php echo $accountsStats['suspended_accounts']; ?></span> suspended</span>
<span style="flex:1"></span>
<div style="display:flex;gap:6px;align-items:center">
<input type="text" id="accountSearch" placeholder="🔍 Search accounts (username, domain, email)..." style="padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;outline:none;font-size:12px;min-width:260px">
<select id="statusFilter" style="padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;outline:none;font-size:12px">
<option value="">All statuses</option>
<option value="active">Active</option>
<option value="suspended">Suspended</option>
<option value="terminated">Terminated</option>
</select>
</div>
</div>

<table class="table table-hover" style="color:#fff" id="accountTable">
<thead><tr>
<th>Username</th><th>Domain</th><th>Email</th><th>Package</th><th>Vhost</th><th>Actions</th><th>Status</th>
</tr></thead>
<tbody>
<?php if (!empty($accounts)): foreach ($accounts as $a): 
$pkgName = 'N/A';
if (isset($packages)) {
    foreach ($packages as $p) { if ($p->id == $a->package_id) { $pkgName = $p->name; break; } }
}
$vhostFile = "/etc/apache2/sites-available/{$a->username}.conf";
$vhostExists = file_exists($vhostFile);
$vhostContent = $vhostExists ? @file_get_contents($vhostFile) : '';
$vhostLines = $vhostContent ? explode("\n", trim($vhostContent)) : [];
$vhostSummary = '';
foreach ($vhostLines as $line) {
    $t = trim($line);
    if (str_starts_with($t, 'ServerName ')) { $vhostSummary .= $t . ' '; }
    if (str_starts_with($t, 'DocumentRoot ')) { $vhostSummary .= $t; }
}
?>
<tr data-username="<?php echo htmlspecialchars(strtolower($a->username)); ?>" data-domain="<?php echo htmlspecialchars(strtolower($a->domain ?? '')); ?>" data-email="<?php echo htmlspecialchars(strtolower($a->email ?? '')); ?>" data-status="<?php echo $a->status; ?>">
<td><strong><?php echo htmlspecialchars($a->username); ?></strong></td>
<td><?php echo htmlspecialchars($a->domain ?? '-'); ?></td>
<td style="font-size:12px"><?php echo htmlspecialchars($a->email ?? '-'); ?></td>
<td><?php echo htmlspecialchars($pkgName); ?></td>
<td style="font-size:12px">
<?php if ($vhostExists): ?>
<span style="color:#4ade80">&#9679; Active</span>
<?php else: ?>
<span style="color:#f87171">&#9679; Missing</span>
<?php endif; ?>
</td>
<td style="white-space:nowrap">
<a href="/admin/account/show/<?php echo $a->id; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-eye"></i> View</a>
<?php if ($a->status === 'active'): ?>
<a href="/admin/account/suspend/<?php echo $a->id; ?>" class="btn btn-sm btn-secondary" style="background:rgba(250,204,21,.1);color:#facc15;border-color:rgba(250,204,21,.2)" onclick="return confirm('Suspend <?php echo htmlspecialchars($a->username); ?>?')"><i class="bi bi-pause-circle"></i></a>
<?php elseif ($a->status === 'suspended'): ?>
<a href="/admin/account/unsuspend/<?php echo $a->id; ?>" class="btn btn-sm btn-secondary" style="background:rgba(74,222,128,.1);color:#4ade80;border-color:rgba(74,222,128,.2)"><i class="bi bi-play-circle"></i></a>
<?php endif; ?>
<a href="#" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.2)" onclick="return deleteAccount(<?php echo $a->id; ?>, '<?php echo htmlspecialchars($a->username, ENT_QUOTES, 'UTF-8'); ?>', this)"><i class="bi bi-trash"></i></a>
</td>
<td><span class="badge bg-<?php echo $a->status === 'active' ? 'success' : ($a->status === 'suspended' ? 'warning' : 'danger'); ?>"><?php echo ucfirst($a->status); ?></span></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text_muted)">No accounts created yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
<div id="accountNoResults" style="display:none;text-align:center;padding:2rem;color:var(--text_muted)">No accounts match your search.</div>

<style>
tr[data-vhost="missing"] td:first-child { border-left: 3px solid #f87171; }
tr[data-vhost="ok"] td:first-child { border-left: 3px solid #4ade80; }
</style>

<script>
document.querySelectorAll('td[title]').forEach(function(td) {
    td.style.cursor = 'help';
});
</script>

<script>
function applyAccountFilter() {
    var q = (document.getElementById('accountSearch').value || '').toLowerCase().trim();
    var st = document.getElementById('statusFilter').value;
    var rows = document.querySelectorAll('#accountTable tbody tr[data-username]');
    var any = false;
    rows.forEach(function(r) {
        var match = (!q || (r.getAttribute('data-username').indexOf(q) > -1 || r.getAttribute('data-domain').indexOf(q) > -1 || r.getAttribute('data-email').indexOf(q) > -1)) && (!st || r.getAttribute('data-status') === st);
        r.style.display = match ? '' : 'none';
        if (match) any = true;
    });
    var empty = document.querySelectorAll('#accountTable tbody tr[data-username]');
    var noRes = document.getElementById('accountNoResults');
    if (noRes) noRes.style.display = (rows.length > 0 && !any) ? '' : 'none';
    if (empty.length === 0 && noRes) noRes.style.display = 'none';
}
document.getElementById('accountSearch').addEventListener('input', applyAccountFilter);
document.getElementById('statusFilter').addEventListener('change', applyAccountFilter);
</script>

<script>
function deleteAccount(id, username, btn) {
    if (!confirm('Permanently delete ' + username + ' and all data? This cannot be undone.')) return false;
    var x = new XMLHttpRequest();
    x.open('GET', '/admin/account/delete/' + id, true);
    x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    x.onload = function() {
        if (x.status === 200) {
            var row = btn.closest('tr');
            if (row) row.remove();
            // Update stats
            var totalEl = document.querySelector('[data-stat="total"]');
            var activeEl = document.querySelector('[data-stat="active"]');
            if (totalEl) totalEl.textContent = parseInt(totalEl.textContent) - 1;
        } else {
            alert('Delete failed. Check console for details.');
        }
    };
    x.onerror = function() { alert('Network error.'); };
    x.send();
    return false;
}
</script>
