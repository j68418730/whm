<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div><?php endif; ?>

<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center">
<div><h3 style="margin:0">All Websites</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0"><?php echo count($sites); ?> total sites</p></div>
<a href="/admin/websitebuilder" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
</div>

<div class="card" style="padding:0;overflow:hidden">
<table>
<thead><tr><th>Name</th><th>Domain</th><th>Status</th><th>Pages</th><th>Created</th><th>Actions</th></tr></thead>
<tbody>
<?php if (count($sites) > 0): foreach ($sites as $s):
$app = \Core\Application::getInstance(); $db = $app->get('db');
$pageCount = $db->table('wb_pages')->where('site_id', $s->id)->get() ?: [];
?>
<tr>
<td><strong><?php echo htmlspecialchars($s->name); ?></strong></td>
<td><?php echo htmlspecialchars($s->domain ?: 'N/A'); ?></td>
<td><span class="badge bg-<?php echo $s->status === 'published' ? 'success' : ($s->status === 'draft' ? 'warning' : 'secondary'); ?>"><?php echo $s->status ?: 'draft'; ?></span></td>
<td><?php echo count($pageCount); ?></td>
<td><?php echo $s->created_at; ?></td>
<td>
<a href="/admin/websitebuilder/sites/<?php echo $s->id; ?>" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
<a href="/admin/websitebuilder/sites/delete/<?php echo $s->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this site and all its content?')"><i class="bi bi-trash"></i></a>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text_muted)">No websites found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
