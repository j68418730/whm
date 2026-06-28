<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<h3 style="color:var(--accent);margin:0">Announcements</h3>
<a class="btn primary" onclick="document.getElementById('annForm').classList.toggle('hidden')">+ Add</a>
</div>
<div id="annForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/support/announcements/store">
<div class="form-group"><label>Title</label><input name="title" required></div>
<div class="form-group"><label>Type</label><select name="type"><option value="info">Info</option><option value="warning">Warning</option><option value="success">Success</option><option value="danger">Critical</option></select></div>
<div class="form-group"><label>Content</label><textarea name="content" rows="4" required></textarea></div>
<div class="form-group"><label><input name="is_active" type="checkbox" value="1" checked> Active</label></div>
<button type="submit" class="btn primary">Create</button>
</form></div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:12px">
<?php if (!empty($announcements)): foreach ($announcements as $a): ?>
<div class="card" style="margin-bottom:0;padding:16px;border-left:3px solid <?php echo $a->type === 'danger' ? '#f87171' : ($a->type === 'warning' ? '#facc15' : ($a->type === 'success' ? '#4ade80' : '#38bdf8')); ?>">
<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:6px">
<strong style="font-size:14px"><?php echo htmlspecialchars($a->title); ?></strong>
<span style="font-size:10px;padding:2px 8px;border-radius:4px;background:<?php echo $a->type === 'danger' ? 'rgba(248,113,113,.15)' : ($a->type === 'warning' ? 'rgba(250,204,21,.15)' : 'rgba(74,222,128,.15)'); ?>;color:<?php echo $a->type === 'danger' ? '#f87171' : ($a->type === 'warning' ? '#facc15' : '#4ade80'); ?>"><?php echo $a->type; ?></span>
</div>
<div style="font-size:12px;color:var(--text-secondary);margin-bottom:8px"><?php echo nl2br(htmlspecialchars(substr($a->content ?? '', 0, 200))); ?><?php echo strlen($a->content ?? '') > 200 ? '...' : ''; ?></div>
<div style="display:flex;justify-content:space-between;align-items:center;font-size:11px;color:#64748b">
<span><?php echo $a->created_at; ?> · <span class="status-badge status-<?php echo $a->is_active ? 'active' : 'terminated'; ?>" style="font-size:10px"><?php echo $a->is_active ? 'Active' : 'Inactive'; ?></span></span>
<div style="display:flex;gap:4px">
<a class="btn btn-sm secondary" onclick="editAnn(<?php echo $a->id; ?>, '<?php echo htmlspecialchars(addslashes($a->title)); ?>', '<?php echo htmlspecialchars(addslashes($a->content)); ?>', '<?php echo $a->type; ?>', <?php echo $a->is_active; ?>)">✏</a>
<a href="/admin/support/announcements/delete/<?php echo $a->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">🗑</a>
</div>
</div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:30px;grid-column:1/-1;color:#64748b">No announcements yet.</div>
<?php endif; ?>
</div>
<div id="editAnnModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.7);align-items:center;justify-content:center" onclick="if(event.target===this)this.style.display='none'">
<div class="card" style="max-width:500px;margin:auto;position:relative;top:10%">
<h4 style="color:var(--accent);margin-bottom:8px">Edit Announcement</h4>
<form method="POST" action="" id="editAnnForm">
<div class="form-group"><label>Title</label><input name="title" id="ea_title" required></div>
<div class="form-group"><label>Type</label><select name="type" id="ea_type"><option value="info">Info</option><option value="warning">Warning</option><option value="success">Success</option><option value="danger">Critical</option></select></div>
<div class="form-group"><label>Content</label><textarea name="content" id="ea_content" rows="4" required></textarea></div>
<div class="form-group"><label><input name="is_active" type="checkbox" value="1" id="ea_active"> Active</label></div>
<button type="submit" class="btn primary">Save</button>
<button type="button" class="btn secondary" onclick="document.getElementById('editAnnModal').style.display='none'">Cancel</button>
</form></div></div>
<script>
function editAnn(id, title, content, type, active) {
    document.getElementById('editAnnForm').action = '/admin/support/announcements/update/' + id;
    document.getElementById('ea_title').value = title;
    document.getElementById('ea_content').value = content;
    document.getElementById('ea_type').value = type;
    document.getElementById('ea_active').checked = active == 1;
    document.getElementById('editAnnModal').style.display = 'flex';
}
</script>