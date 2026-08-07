<?php
$totalTodos = count($todos);
$doneCount = count(array_filter($todos, fn($t) => (int)$t->progress >= 100));
$activeCount = $totalTodos - $doneCount;
$csrf = htmlspecialchars($_SESSION['_csrf_token'] ?? '');
?>
<style>
.todo-summary{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px}
.todo-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:14px 20px;min-width:130px;text-align:center}
.todo-stat .num{font-size:26px;font-weight:800;color:var(--accent,#008cff)}
.todo-stat.green .num{color:#4ade80}
.todo-stat.red .num{color:#f87171}
.todo-stat .lbl{font-size:11px;color:#64748b;margin-top:2px}
.todo-add{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.12);border-radius:14px;padding:20px;margin-bottom:20px}
.todo-add h3{margin:0 0 14px;font-size:15px;color:var(--text,#e0e0e0)}
.todo-add .row{display:flex;gap:10px;flex-wrap:wrap}
.todo-add input[type=text],.todo-add select,.todo-add textarea{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);color:#e0e0e0;border-radius:8px;padding:9px 12px;font-size:13px;outline:none}
.todo-add input[type=text]{flex:2;min-width:200px}
.todo-add textarea{flex:1;min-width:200px;resize:vertical}
.todo-cat{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px}
.todo-cat .chip{padding:5px 12px;border-radius:100px;border:1px solid rgba(255,255,255,.1);font-size:12px;color:#94a3b8;cursor:pointer;background:rgba(255,255,255,.02)}
.todo-cat .chip.active{background:rgba(0,140,255,.15);border-color:rgba(0,140,255,.4);color:#3bb8ff}
.todo-group{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.1);border-radius:14px;margin-bottom:16px;overflow:hidden}
.todo-group-hdr{display:flex;align-items:center;gap:10px;padding:13px 18px;cursor:pointer;user-select:none;border-bottom:1px solid rgba(255,255,255,.04)}
.todo-group-hdr:hover{background:rgba(255,255,255,.02)}
.todo-group-hdr .arrow{transition:transform .2s;font-size:11px;color:#64748b}
.todo-group-hdr.closed .arrow{transform:rotate(-90deg)}
.todo-group-hdr h4{flex:1;margin:0;font-size:14px;color:var(--accent,#008cff)}
.todo-group-hdr .meta{font-size:12px;color:#64748b}
.todo-group-body{padding:0 18px}
.todo-group-body.closed{display:none}
.todo-row{display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.todo-row:last-child{border-bottom:none}
.todo-row.done .ttl{text-decoration:line-through;opacity:.55}
.todo-row .chk{width:20px;height:20px;flex-shrink:0;margin-top:2px;accent-color:#4ade80;cursor:pointer}
.todo-row .main{flex:1;min-width:0}
.todo-row .ttl{font-size:14px;font-weight:600;color:#e0e0e0}
.todo-row .desc{font-size:12px;color:#94a3b8;margin-top:2px;white-space:pre-wrap;word-break:break-word}
.todo-row .prg{display:flex;align-items:center;gap:10px;margin-top:8px}
.todo-row .prg .bar{flex:1;height:7px;background:rgba(255,255,255,.07);border-radius:4px;overflow:hidden}
.todo-row .prg .fill{height:100%;background:linear-gradient(90deg,#008cff,#3bb8ff);border-radius:4px;transition:width .3s}
.todo-row .prg .fill.done{background:linear-gradient(90deg,#22c55e,#4ade80)}
.todo-row .prg .pct{font-size:12px;color:#64748b;min-width:34px;text-align:right}
.todo-row .prg input[type=range]{flex:0 0 90px;accent-color:#008cff;cursor:pointer}
.todo-row .acts{display:flex;gap:5px;flex-shrink:0;align-items:center}
.todo-row .acts button{border:none;cursor:pointer;border-radius:6px;padding:5px 9px;font-size:12px;background:rgba(255,255,255,.05);color:#94a3b8;transition:.15s}
.todo-row .acts button:hover{background:rgba(255,255,255,.1);color:#e0e0e0}
.todo-row .acts button.del:hover{background:rgba(255,60,60,.15);color:#ff6b6b}
.todo-row .acts button.save{background:rgba(34,197,94,.15);color:#4ade80}
.todo-edit{display:none;margin-top:8px;padding:10px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:10px}
.todo-edit input,.todo-edit select,.todo-edit textarea{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);color:#e0e0e0;border-radius:6px;padding:7px 9px;font-size:12px;outline:none;margin-bottom:6px;width:100%;box-sizing:border-box}
.todo-empty{padding:30px;text-align:center;color:#64748b;font-size:13px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:1px solid rgba(0,191,255,.2);color:#e0e0e0;background:rgba(0,140,255,.08);text-decoration:none}
.btn.primary{background:#008cff;border-color:#008cff;color:#fff}
.btn:hover{opacity:.9}
</style>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="todo-summary">
  <div class="todo-stat"><div class="num"><?php echo $activeCount; ?></div><div class="lbl">Active</div></div>
  <div class="todo-stat green"><div class="num"><?php echo $doneCount; ?></div><div class="lbl">Completed</div></div>
  <div class="todo-stat red"><div class="num"><?php echo $totalTodos; ?></div><div class="lbl">Total</div></div>
</div>

<div class="todo-add">
  <h3>➕ Add Task</h3>
  <form method="POST" action="/admin/todo">
    <input type="hidden" name="_csrf_token" value="<?php echo $csrf; ?>">
    <div class="row">
      <input type="text" name="title" placeholder="Task title..." required>
      <input type="text" name="description" placeholder="Short description (optional)">
      <select name="category">
        <option>General</option>
        <option>Core Platform</option><option>User Portal</option><option>Admin Portal</option>
        <option>Account Management</option><option>Packages</option><option>Resellers</option>
        <option>Server</option><option>DNS</option><option>Email Server</option><option>Security Admin</option>
        <option>Backups</option><option>Monitoring</option><option>System</option><option>API</option>
        <option>Radio Streaming</option><option>Billing</option><option>Support System</option><option>Nice To Have</option>
      </select>
      <button type="submit" class="btn primary">Add</button>
    </div>
  </form>
</div>

<?php
$categories = [];
foreach ($todos as $t) { $categories[$t->category ?? 'General'] = true; }
$categories = array_keys($categories);
sort($categories);
?>
<div class="todo-cat">
  <span class="chip active" data-cat="">All</span>
  <?php foreach ($categories as $cat): ?>
  <span class="chip" data-cat="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></span>
  <?php endforeach; ?>
</div>

<?php if (empty($todos)): ?>
<div class="todo-empty">No tasks yet. Add one above. ✅</div>
<?php endif; ?>

<?php foreach ($categories as $cat): ?>
<?php
$activeTodos = []; $doneTodos = [];
foreach ($todos as $t) {
    if (($t->category ?? 'General') === $cat) {
        if ((int)$t->progress >= 100) $doneTodos[] = $t; else $activeTodos[] = $t;
    }
}
$catTodos = array_merge($activeTodos, $doneTodos);
if (empty($catTodos)) continue;
?>
<div class="todo-group" data-group="<?php echo htmlspecialchars($cat); ?>">
  <div class="todo-group-hdr" onclick="toggleCat(this)">
    <span class="arrow">▼</span>
    <h4><?php echo htmlspecialchars($cat); ?></h4>
    <span class="meta"><?php echo count($doneTodos); ?>/<?php echo count($catTodos); ?> done</span>
    <?php if (count($activeTodos) === 0 && count($doneTodos) > 0): ?>
    <a href="/admin/todo/delete-category/<?php echo urlencode($cat); ?>" style="font-size:11px;color:#f87171;text-decoration:none;padding:3px 10px;background:rgba(255,60,60,.1);border-radius:6px" onclick="return confirm('Delete all completed in \'<?php echo htmlspecialchars($cat); ?>\'?')">🗑 Clear done</a>
    <?php endif; ?>
  </div>
  <div class="todo-group-body">
    <?php foreach ($catTodos as $t): $done = (int)$t->progress >= 100; ?>
    <div class="todo-row<?php echo $done ? ' done' : ''; ?>" data-id="<?php echo (int)$t->id; ?>">
      <input type="checkbox" class="chk" <?php echo $done ? 'checked' : ''; ?> onchange="toggleDone(<?php echo (int)$t->id; ?>, this)">
      <div class="main">
        <div class="ttl"><?php echo htmlspecialchars($t->title); ?></div>
        <?php if (!empty($t->description)): ?><div class="desc"><?php echo nl2br(htmlspecialchars($t->description)); ?></div><?php endif; ?>
        <div class="prg">
          <div class="bar"><div class="fill<?php echo $done ? ' done' : ''; ?>" style="width:<?php echo min(100, max(0, (int)$t->progress)); ?>%"></div></div>
          <span class="pct"><?php echo (int)$t->progress; ?>%</span>
          <input type="range" min="0" max="100" value="<?php echo (int)$t->progress; ?>" onchange="setProgress(<?php echo (int)$t->id; ?>, this.value)">
        </div>
        <div class="todo-edit" data-edit="<?php echo (int)$t->id; ?>">
          <form method="POST" action="/admin/todo/<?php echo (int)$t->id; ?>">
            <input type="hidden" name="_csrf_token" value="<?php echo $csrf; ?>">
            <input type="text" name="title" value="<?php echo htmlspecialchars($t->title); ?>">
            <textarea name="description" rows="2"><?php echo htmlspecialchars($t->description ?? ''); ?></textarea>
            <select name="category">
              <?php foreach ($categories as $c): ?>
              <option value="<?php echo htmlspecialchars($c); ?>" <?php echo ($t->category ?? 'General') === $c ? 'selected' : ''; ?>><?php echo htmlspecialchars($c); ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn primary" style="padding:6px 12px;font-size:12px">Save</button>
          </form>
        </div>
      </div>
      <div class="acts">
        <button title="Edit" onclick="openEdit(<?php echo (int)$t->id; ?>)" style="font-size:13px">✏️</button>
        <button class="del" title="Delete" onclick="if(confirm('Delete this task?'))location.href='/admin/todo/delete/<?php echo (int)$t->id; ?>'" style="font-size:13px">🗑</button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>

<script>
var CSRF = '<?php echo $csrf; ?>';
function post(url, data, cb) {
    var fd = new FormData();
    fd.append('_csrf_token', CSRF);
    for (var k in data) fd.append(k, data[k]);
    fetch(url, { method: 'POST', body: fd }).then(function(r){ if (r.ok && cb) cb(); }).catch(function(){});
}
function setProgress(id, val) {
    post('/admin/todo/' + id, { progress: val }, function(){ location.reload(); });
}
function toggleDone(id, chk) {
    post('/admin/todo/' + id, { done: chk.checked ? '1' : '0' }, function(){ location.reload(); });
}
function openEdit(id) {
    var el = document.querySelector('[data-edit="' + id + '"]');
    if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function toggleCat(hdr) {
    var body = hdr.nextElementSibling;
    body.classList.toggle('closed');
    hdr.classList.toggle('closed');
    var name = hdr.querySelector('h4').textContent;
    try { localStorage.setItem('todo_cat_' + name, body.classList.contains('closed') ? '1' : '0'); } catch(e) {}
}
// Restore collapsed categories
(function(){
    document.querySelectorAll('.todo-group-hdr').forEach(function(h){
        var name = h.querySelector('h4').textContent;
        try { if (localStorage.getItem('todo_cat_' + name) === '1') { h.classList.add('closed'); h.nextElementSibling.classList.add('closed'); } } catch(e) {}
    });
})();
// Category filter chips
document.querySelectorAll('.todo-cat .chip').forEach(function(chip){
    chip.addEventListener('click', function(){
        document.querySelectorAll('.todo-cat .chip').forEach(function(c){ c.classList.remove('active'); });
        chip.classList.add('active');
        var cat = chip.dataset.cat;
        document.querySelectorAll('.todo-group').forEach(function(g){
            g.style.display = !cat || g.dataset.group === cat ? '' : 'none';
        });
    });
});
</script>
