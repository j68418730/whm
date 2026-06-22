<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div><?php endif; ?>

<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
<div>
<h3 style="margin:0"><?php echo htmlspecialchars($site->name); ?></h3>
<p style="color:var(--text_muted);font-size:12px;margin:2px 0 0">
Domain: <?php echo htmlspecialchars($site->domain ?: 'Not set'); ?> &middot;
Status: <span class="badge bg-<?php echo $site->status === 'published' ? 'success' : ($site->status === 'draft' ? 'warning' : 'secondary'); ?>"><?php echo $site->status ?: 'draft'; ?></span>
</p>
</div>
<div class="d-flex gap-2">
<a href="/admin/websitebuilder/sites" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
<a href="/admin/websitebuilder/sites/delete/<?php echo $site->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this site?')"><i class="bi bi-trash"></i> Delete</a>
</div>
</div>
</div>

<div class="card">
<h3 style="margin-bottom:12px">Pages (<?php echo count($pages); ?>)</h3>
<?php if (count($pages) > 0): ?>
<table>
<thead><tr><th>Title</th><th>Slug</th><th>Status</th><th>Blocks</th><th>Created</th></tr></thead>
<tbody>
<?php foreach ($pages as $p):
$app = \Core\Application::getInstance(); $db = $app->get('db');
$blockCount = $db->table('wb_blocks')->where('page_id', $p->id)->get() ?: [];
?>
<tr>
<td><strong><?php echo htmlspecialchars($p->title); ?></strong></td>
<td>/<?php echo htmlspecialchars($p->slug); ?></td>
<td><span class="badge bg-<?php echo ($p->status ?? 'draft') === 'published' ? 'success' : 'warning'; ?>"><?php echo $p->status ?? 'draft'; ?></span></td>
<td><?php echo count($blockCount); ?></td>
<td><?php echo $p->created_at; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p style="color:var(--text_muted);text-align:center;padding:20px">No pages in this site.</p>
<?php endif; ?>
</div>
