<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<h3 style="color:var(--accent)">Announcements</h3>
<a class="btn primary" onclick="document.getElementById('annForm').classList.toggle('hidden')">Add Announcement</a>
</div>
<div id="annForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/support/announcements/store">
<div class="form-group"><label>Title</label><input name="title" required></div>
<div class="form-group"><label>Type</label><select name="type"><option value="info">Info</option><option value="warning">Warning</option><option value="success">Success</option><option value="danger">Critical</option></select></div>
<div class="form-group"><label>Content</label><textarea name="content" rows="4" required></textarea></div>
<div class="form-group"><label><input name="is_active" type="checkbox" value="1" checked> Active</label></div>
<button type="submit" class="btn primary">Create</button>
</form></div>
<table><tr><th>Title</th><th>Type</th><th>Status</th><th>Date</th><th></th></tr>
<?php if (!empty($announcements)): foreach ($announcements as $a): ?>
<tr><td><?php echo htmlspecialchars($a->title); ?></td>
<td><span class="status-badge" style="background:<?php echo $a->type === 'danger' ? 'rgba(248,113,113,.15)' : ($a->type === 'warning' ? 'rgba(250,204,21,.15)' : 'rgba(74,222,128,.15)'); ?>;color:<?php echo $a->type === 'danger' ? '#f87171' : ($a->type === 'warning' ? '#facc15' : '#4ade80'); ?>"><?php echo $a->type; ?></span></td>
<td><span class="status-badge status-<?php echo $a->is_active ? 'active' : 'terminated'; ?>"><?php echo $a->is_active ? 'Active' : 'Inactive'; ?></span></td>
<td><?php echo $a->created_at; ?></td>
<td><a href="/admin/support/announcements/delete/<?php echo $a->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">Delete</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No announcements.</td></tr>
<?php endif; ?></table>
