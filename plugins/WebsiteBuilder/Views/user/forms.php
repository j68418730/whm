<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
<div><h3 style="margin:0">Form Builder</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0"><?php echo htmlspecialchars($site->name); ?></p></div>
<a href="/user/websites/<?php echo $site->id; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
</div>

<?php if (isset($viewEntries) && $viewEntries && isset($entries)): ?>
<div class="card">
<h4 style="margin-bottom:12px">Form Entries: <?php echo htmlspecialchars($forms[0]->name ?? ''); ?></h4>
<?php if (count($entries) > 0): ?>
<div style="overflow-x:auto">
<table>
<thead><tr><th>#</th><th>Data</th><th>Date</th></tr></thead>
<tbody>
<?php foreach ($entries as $e):
$data = json_decode($e->data ?? '{}', true);
?>
<tr>
<td><?php echo $e->id; ?></td>
<td><pre style="font-size:11px;color:#94a3b8;margin:0"><?php echo htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)); ?></pre></td>
<td><?php echo $e->created_at; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php else: ?>
<p style="color:var(--text_muted);text-align:center;padding:20px">No entries yet.</p>
<?php endif; ?>
</div>
<?php else: ?>

<div class="card" style="margin-bottom:16px">
<h4 style="margin-bottom:12px">Create New Form</h4>
<form method="POST" action="/user/websites/<?php echo $site->id; ?>/forms/store">
<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
<div style="flex:1">
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Form Name</label>
<input type="text" name="name" class="form-control" required placeholder="Contact Form">
</div>
<button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Create Form</button>
</div>
</form>
</div>

<?php if (count($forms) > 0): ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px">
<?php foreach ($forms as $f): ?>
<div class="card">
<h4 style="margin:0 0 4px"><?php echo htmlspecialchars($f->name); ?></h4>
<p style="color:var(--text_muted);font-size:12px;margin:0 0 8px"><?php echo (int)$f->entries_count; ?> entries</p>
<a href="/user/websites/forms/entries/<?php echo $f->id; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-list-ul"></i> View Entries</a>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<div class="card" style="text-align:center;padding:40px">
<p style="color:var(--text_muted)">No forms created yet.</p>
</div>
<?php endif; ?>
<?php endif; ?>
