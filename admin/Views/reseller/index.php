<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Total Resellers</h3><div class="value"><?php echo $resellerStats['total_resellers']; ?></div></div>
<div class="stat-card"><h3>Active</h3><div class="value" style="color:#4ade80"><?php echo $resellerStats['active_resellers']; ?></div></div>
<div class="stat-card"><h3>Accounts Owned</h3><div class="value"><?php echo $resellerStats['accounts_owned_by_resellers']; ?></div></div>
</div>

<div style="display:flex;gap:8px;margin-bottom:16px">
<a href="/admin/reseller/create" class="btn primary"><i class="bi bi-plus-circle"></i> Create Reseller</a>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px">
<?php if (!empty($resellers)): foreach ($resellers as $r):
$acctCount = $acctCounts[$r->id] ?? 0;
?>
<div class="card" style="padding:18px;margin-bottom:0">
<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px">
<div>
<div style="font-weight:700;font-size:15px"><?php echo htmlspecialchars($r->company_name); ?></div>
<div style="font-size:12px;color:#64748b"><?php echo htmlspecialchars($r->email); ?></div>
</div>
<span class="status-badge status-<?php echo $r->is_active ? 'active' : 'terminated'; ?>"><?php echo $r->is_active ? 'Active' : 'Inactive'; ?></span>
</div>
<div style="font-size:12px;color:#94a3b8;margin-bottom:10px">
<div>Contact: <?php echo htmlspecialchars($r->contact_name ?: '-'); ?></div>
<div>Phone: <?php echo htmlspecialchars($r->phone ?: '-'); ?></div>
<div>Website: <?php echo htmlspecialchars($r->website ?: '-'); ?></div>
</div>
<div style="font-size:13px;font-weight:600;color:var(--accent);margin-bottom:10px"><?php echo $acctCount; ?> account<?php echo $acctCount !== 1 ? 's' : ''; ?></div>
<div style="display:flex;gap:6px">
<a href="/admin/account?reseller_id=<?php echo $r->id; ?>" class="btn btn-sm primary" style="font-size:11px;padding:5px 12px"><i class="bi bi-eye"></i> View Accounts</a>
<a href="/admin/reseller/edit/<?php echo $r->id; ?>" class="btn btn-sm secondary" style="font-size:11px;padding:5px 12px"><i class="bi bi-pencil"></i> Edit</a>
</div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:40px;grid-column:1/-1">
<p style="color:#64748b">No resellers yet. <a href="/admin/reseller/create">Create one</a>.</p>
</div>
<?php endif; ?>
</div>
