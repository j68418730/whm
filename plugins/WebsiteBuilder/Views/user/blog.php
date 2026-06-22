<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
<div><h3 style="margin:0">Blog Posts</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0"><?php echo htmlspecialchars($site->name); ?></p></div>
<a href="/user/websites/<?php echo $site->id; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
</div>

<div class="card" style="margin-bottom:16px">
<h4 style="margin-bottom:12px">New / Edit Post</h4>
<form method="POST" action="/user/websites/<?php echo $site->id; ?>/blog/store">
<input type="hidden" name="post_id" id="edit_post_id" value="0">
<div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:12px">
<div>
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Title</label>
<input type="text" name="title" id="post_title" class="form-control" required placeholder="Post Title">
</div>
<div>
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Category</label>
<input type="text" name="category" id="post_category" class="form-control" placeholder="News, Tutorial, etc.">
</div>
</div>
<div style="margin-bottom:12px">
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Content</label>
<textarea name="content" id="post_content" class="form-control" rows="8" placeholder="Write your post content here..."></textarea>
</div>
<div style="margin-bottom:12px">
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Excerpt</label>
<textarea name="excerpt" id="post_excerpt" class="form-control" rows="2" placeholder="Brief summary..."></textarea>
</div>
<div style="display:flex;gap:8px;align-items:center;margin-bottom:12px">
<label style="font-size:12px;color:var(--text_muted)">Status:</label>
<select name="status" id="post_status" style="padding:6px 10px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff">
<option value="draft">Draft</option>
<option value="published">Published</option>
</select>
</div>
<button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-save"></i> Save Post</button>
<button type="button" class="btn btn-sm btn-secondary" onclick="resetForm()" style="display:none" id="cancelEdit">Cancel Edit</button>
</form>
</div>

<div class="card">
<h4 style="margin-bottom:12px">All Posts</h4>
<?php if (count($posts) > 0): ?>
<table>
<thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($posts as $p): ?>
<tr>
<td><strong><?php echo htmlspecialchars($p->title); ?></strong></td>
<td><?php echo htmlspecialchars($p->category ?: 'Uncategorized'); ?></td>
<td><span class="badge bg-<?php echo ($p->status ?? 'draft') === 'published' ? 'success' : 'warning'; ?>"><?php echo $p->status ?? 'draft'; ?></span></td>
<td><?php echo $p->created_at; ?></td>
<td>
<button class="btn btn-sm btn-secondary" onclick="editPost(<?php echo $p->id; ?>,'<?php echo htmlspecialchars(addslashes($p->title)); ?>','<?php echo htmlspecialchars(addslashes($p->category ?? '')); ?>','<?php echo htmlspecialchars(addslashes($p->status ?? 'draft')); ?>','<?php echo htmlspecialchars(addslashes($p->content ?? '')); ?>','<?php echo htmlspecialchars(addslashes($p->excerpt ?? '')); ?>')"><i class="bi bi-pencil"></i></button>
<a href="/user/websites/blog/delete/<?php echo $p->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this post?')"><i class="bi bi-trash"></i></a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p style="color:var(--text_muted);text-align:center;padding:20px">No blog posts yet.</p>
<?php endif; ?>
</div>

<script>
function editPost(id, title, category, status, content, excerpt) {
document.getElementById('edit_post_id').value = id;
document.getElementById('post_title').value = title;
document.getElementById('post_category').value = category;
document.getElementById('post_status').value = status;
document.getElementById('post_content').value = content;
document.getElementById('post_excerpt').value = excerpt;
document.getElementById('cancelEdit').style.display = 'inline-block';
}
function resetForm() {
document.getElementById('edit_post_id').value = 0;
document.getElementById('post_title').value = '';
document.getElementById('post_category').value = '';
document.getElementById('post_status').value = 'draft';
document.getElementById('post_content').value = '';
document.getElementById('post_excerpt').value = '';
document.getElementById('cancelEdit').style.display = 'none';
}
</script>
