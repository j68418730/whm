<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div style="display:flex;gap:12px;align-items:start;flex-wrap:wrap;margin-bottom:16px">
<a class="btn primary" onclick="document.getElementById('addForm').style.display='block'">+ Add Task</a>
</div>

<div id="addForm" style="display:none" class="card" style="margin-bottom:20px">
<form method="POST" action="/admin/todo">
<div class="form-group"><label>Title</label><input name="title" required></div>
<div class="form-group"><label>Description</label><textarea name="description"></textarea></div>
<div class="form-group"><label>Category</label><select name="category">
<option>Core Platform</option><option>User Portal</option><option>Admin Portal</option><option>Account Management</option>
<option>Packages</option><option>Resellers</option><option>Server</option><option>DNS</option><option>Email Server</option>
<option>Security Admin</option><option>Backups</option><option>Monitoring</option><option>System</option>
<option>Radio Streaming</option><option>Billing</option><option>Support System</option><option>Nice To Have</option>
</select></div>
<button type="submit" class="btn primary">Add Task</button>
<a class="btn secondary" onclick="document.getElementById('addForm').style.display='none'">Cancel</a>
</form>
</div>

<style>
.toggle-icon{transition:transform .2s;display:inline-block;margin-right:8px;cursor:pointer;user-select:none}
.toggle-icon.closed{transform:rotate(-90deg)}
.toggle-body{overflow:hidden;transition:max-height .3s}
.toggle-body.closed{max-height:0!important;padding:0!important;margin:0!important}
</style>

<script>
function updateProgress(id, input) {
    var val = input.value;
    var row = input.closest('.task-row');
    var bar = row.querySelector('.pbar');
    var pct = row.querySelector('.pct');
    var formData = new FormData();
    formData.append('progress', val);
    formData.append('title', row.dataset.title || '');
    formData.append('description', row.dataset.desc || '');
    fetch('/admin/todo/' + id, { method: 'POST', body: formData }).then(function(r) {
        if (r.ok) {
            bar.style.width = Math.min(100, Math.max(0, val)) + '%';
            pct.textContent = val + '%';
            if (parseInt(val) >= 100) {
                var parent = row.parentNode;
                parent.appendChild(row);
            }
        }
    });
    return false;
}
function toggleCat(el) {
    var name = el.dataset.cat;
    var body = document.getElementById('cat-' + name);
    var icon = el.querySelector('.toggle-icon');
    body.classList.toggle('closed');
    icon.classList.toggle('closed');
    var closed = body.classList.contains('closed');
    try { localStorage.setItem('todo_cat_' + name, closed ? '1' : '0'); } catch(e) {}
}
</script>

<?php $categories = ['Core Platform','User Portal','Files','Domains','Email','Databases','Security','Applications','Advanced','Statistics','Support','Account','Admin Portal','Account Management','Packages','Resellers','Server','DNS','Email Server','Security Admin','Backups','Monitoring','System','API','Reseller Portal','Billing','Automation','Radio Streaming','Support System','Admin Settings','Logging','Nice To Have']; ?>
<?php foreach ($categories as $cat): ?>
<?php
$activeTodos = []; $doneTodos = [];
foreach ($todos as $t) {
    if (($t->category ?? 'General') === $cat) {
        if ((int)$t->progress >= 100) $doneTodos[] = $t;
        else $activeTodos[] = $t;
    }
}
$catTodos = array_merge($activeTodos, $doneTodos);
if (empty($catTodos)) continue;
?>
<div class="card" style="margin-bottom:16px">
<div style="display:flex;align-items:center;cursor:pointer;margin-bottom:<?php echo empty($catTodos) ? '0' : '8px'; ?>;user-select:none" onclick="toggleCat(this)" data-cat="<?php echo $cat; ?>">
<span class="toggle-icon">▼</span>
<h4 style="color:var(--accent);font-size:15px;margin:0;flex:1"><?php echo htmlspecialchars($cat); ?></h4>
<span style="font-size:12px;color:var(--text-muted);margin-right:8px"><?php echo count($doneTodos); ?>/<?php echo count($catTodos); ?> done</span>
<?php if (count($activeTodos) === 0 && count($doneTodos) > 0): ?>
<a href="/admin/todo/delete-category/<?php echo urlencode($cat); ?>" class="btn btn-sm" style="background:rgba(255,50,50,.1);color:#f87171;padding:2px 10px;font-size:11px;text-decoration:none;border-radius:4px" onclick="return confirm('Delete completed \'<?php echo htmlspecialchars($cat); ?>\' group?')">🗑 Delete All Done</a>
<?php endif; ?>
</div>
<div id="cat-<?php echo $cat; ?>" class="toggle-body">
<?php foreach ($catTodos as $t): ?>
<div class="task-row" data-title="<?php echo htmlspecialchars($t->title); ?>" data-desc="<?php echo htmlspecialchars($t->description ?? ''); ?>">
<div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04)">
<div style="flex:1">
<strong style="font-size:14px"><?php echo htmlspecialchars($t->title); ?></strong>
<?php if ($t->description): ?><p style="color:var(--text-muted);font-size:12px;margin-top:2px"><?php echo htmlspecialchars($t->description); ?></p><?php endif; ?>
<div style="display:flex;align-items:center;gap:8px;margin-top:6px">
<div style="flex:1;height:6px;background:rgba(255,255,255,.08);border-radius:3px;overflow:hidden">
<div class="pbar" style="height:100%;width:<?php echo min(100, max(0, (int)$t->progress)); ?>%;background:linear-gradient(90deg,<?php echo (int)$t->progress >= 100 ? '#4ade80' : '#008cff,#3bb8ff'; ?>);border-radius:3px;transition:width .3s"></div>
</div>
<span class="pct" style="font-size:12px;color:var(--text-muted);min-width:35px"><?php echo (int)$t->progress; ?>%</span>
</div>
</div>
<div style="display:flex;gap:4px;flex-shrink:0">
<input type="number" value="<?php echo (int)$t->progress; ?>" min="0" max="100" style="width:50px;padding:4px 6px;border-radius:4px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none;font-size:12px" onchange="updateProgress(<?php echo $t->id; ?>, this)">
<a href="/admin/todo/delete/<?php echo $t->id; ?>" class="btn btn-sm" style="background:rgba(255,50,50,.15);color:#ff6b6b;padding:4px 8px;font-size:13px;border:none;font-weight:700;line-height:1" onclick="return confirm('Delete?')" title="Delete task">✕</a>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; if (empty(array_filter($todos))): ?>
<div class="card"><p style="text-align:center;padding:20px;color:#64748b">No tasks yet.</p></div>
<?php endif; ?>

<script>
// Restore toggle state from localStorage
(function() {
    var cats = document.querySelectorAll('[data-cat]');
    for (var i = 0; i < cats.length; i++) {
        var name = cats[i].dataset.cat;
        try {
            if (localStorage.getItem('todo_cat_' + name) === '1') {
                var body = document.getElementById('cat-' + name);
                if (body) { body.classList.add('closed'); cats[i].querySelector('.toggle-icon').classList.add('closed'); }
            }
        } catch(e) {}
    }
})();
</script>
