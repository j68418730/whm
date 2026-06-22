<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
<div><h3 style="margin:0">My Websites</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0">Manage your websites</p></div>
<a href="/user/websites/create" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle"></i> Create Website</a>
</div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<?php if (count($sites) > 0): foreach ($sites as $s): ?>
<div class="card" style="margin-bottom:10px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
<div>
<h4 style="margin:0 0 2px"><?php echo htmlspecialchars($s->name); ?></h4>
<small style="color:var(--text_muted)">
Domain: <?php echo htmlspecialchars($s->domain ?: 'Not set'); ?> &middot;
<span class="badge bg-<?php echo $s->status === 'published' ? 'success' : ($s->status === 'draft' ? 'warning' : 'secondary'); ?>"><?php echo $s->status ?: 'draft'; ?></span>
</small>
</div>
<div class="d-flex gap-2">
<a href="/user/websites/<?php echo $s->id; ?>" class="btn btn-sm btn-primary"><i class="bi bi-speedometer2"></i> Dashboard</a>
</div>
</div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:40px">
<div style="font-size:48px;margin-bottom:8px">🌐</div>
<h4 style="margin:0 0 4px">No Websites Yet</h4>
<p style="color:var(--text_muted);font-size:13px;margin:0 0 14px">Create your first website using one of our templates.</p>
<a href="/user/websites/create" class="btn btn-primary">Create Your First Website</a>
</div>
<?php endif; ?>
