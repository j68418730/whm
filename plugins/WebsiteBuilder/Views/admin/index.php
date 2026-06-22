<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div><?php endif; ?>

<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
<div><h3 style="margin:0">Website Builder Dashboard</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0">Manage all user websites, templates, and themes</p></div>
<div class="d-flex gap-2">
<a href="/admin/websitebuilder/sites" class="btn btn-sm btn-secondary"><i class="bi bi-globe"></i> All Sites</a>
<a href="/admin/websitebuilder/templates" class="btn btn-sm btn-secondary"><i class="bi bi-file-earmark-zip"></i> Templates</a>
<a href="/admin/websitebuilder/themes" class="btn btn-sm btn-secondary"><i class="bi bi-palette"></i> Themes</a>
</div>
</div>
</div>

<div class="stats-grid" style="margin-bottom:16px">
<div class="stat-card"><h3>Total Sites</h3><div class="value"><?php echo $totalSites; ?></div></div>
<div class="stat-card"><h3>Total Pages</h3><div class="value"><?php echo $totalPages; ?></div></div>
<div class="stat-card"><h3>Templates</h3><div class="value"><?php echo $totalTemplates; ?></div></div>
<div class="stat-card"><h3>Themes</h3><div class="value"><?php echo $totalThemes; ?></div></div>
</div>

<div class="card">
<h3 style="margin-bottom:12px">Recent Sites</h3>
<?php if (count($recentSites) > 0): ?>
<table>
<thead><tr><th>Name</th><th>Domain</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($recentSites as $s): ?>
<tr>
<td><strong><?php echo htmlspecialchars($s->name); ?></strong></td>
<td><?php echo htmlspecialchars($s->domain ?: 'N/A'); ?></td>
<td><span class="badge bg-<?php echo $s->status === 'published' ? 'success' : ($s->status === 'draft' ? 'warning' : 'secondary'); ?>"><?php echo $s->status ?: 'draft'; ?></span></td>
<td><?php echo $s->created_at; ?></td>
<td><a href="/admin/websitebuilder/sites/<?php echo $s->id; ?>" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p style="color:var(--text_muted);text-align:center;padding:20px">No sites created yet.</p>
<?php endif; ?>
</div>
