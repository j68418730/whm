<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:start">
<div>
<h3 style="color:var(--accent);margin:0"><?php echo htmlspecialchars($reseller->company_name); ?></h3>
<p style="color:#64748b;margin:4px 0"><?php echo htmlspecialchars($reseller->email); ?> &middot; <?php echo htmlspecialchars($reseller->phone ?: 'No phone'); ?></p>
</div>
<span class="status-badge status-<?php echo $reseller->is_active ? 'active' : 'terminated'; ?>"><?php echo $reseller->is_active ? 'Active' : 'Inactive'; ?></span>
</div>
<div style="font-size:13px;color:#94a3b8;margin-top:8px">Website: <?php echo htmlspecialchars($reseller->website ?: '-'); ?></div>
<a href="/admin/reseller/edit/<?php echo $reseller->id; ?>" class="btn btn-sm secondary" style="margin-top:8px"><i class="bi bi-pencil"></i> Edit</a>
</div>

<h3 style="color:var(--accent);margin-bottom:12px">Hosting Accounts (<?php echo count($accounts); ?>)</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px">
<?php if (!empty($accounts)): foreach ($accounts as $a): $pkgName = $pkgNames[$a->id] ?? '-'; ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="font-weight:600;font-size:14px"><?php echo htmlspecialchars($a->username); ?></div>
<div style="font-size:12px;color:#64748b"><?php echo htmlspecialchars($a->domain ?: '-'); ?></div>
<div style="font-size:11px;color:#94a3b8;margin:4px 0"><?php echo htmlspecialchars($a->email); ?> &middot; Package: <?php echo htmlspecialchars($pkgName); ?></div>
<div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px">
<span class="badge bg-<?php echo $a->status === 'active' ? 'success' : ($a->status === 'suspended' ? 'warning' : 'danger'); ?>"><?php echo ucfirst($a->status); ?></span>
<a href="/admin/account/show/<?php echo $a->id; ?>" class="btn btn-sm primary" style="font-size:10px;padding:3px 8px">View</a>
</div>
</div>
<?php endforeach; else: ?>
<p style="color:#64748b;font-size:13px;grid-column:1/-1">No accounts owned by this reseller.</p>
<?php endif; ?>
</div>
<a href="/admin/reseller" class="btn secondary" style="margin-top:16px">&larr; Back</a>
