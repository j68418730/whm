<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center">
<div><h3 style="margin:0">Site Settings</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0"><?php echo htmlspecialchars($site->name); ?></p></div>
<a href="/user/websites/<?php echo $site->id; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
</div>

<div class="card">
<form method="POST" action="/user/websites/<?php echo $site->id; ?>/settings/save">
<div style="margin-bottom:14px">
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Site Name</label>
<input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($site->name); ?>" style="max-width:400px">
</div>
<div style="margin-bottom:14px">
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Domain</label>
<input type="text" name="domain" class="form-control" value="<?php echo htmlspecialchars($site->domain ?? ''); ?>" placeholder="mywebsite.com" style="max-width:400px">
</div>
<div style="margin-bottom:14px">
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Default Meta Title</label>
<input type="text" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($site->name); ?>" style="max-width:400px">
</div>
<div style="margin-bottom:14px">
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Default Meta Description</label>
<textarea name="meta_description" class="form-control" rows="3" style="max-width:400px"><?php echo htmlspecialchars($site->name); ?> - Website built with Website Builder</textarea>
</div>
<div style="margin-bottom:14px">
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Default Meta Keywords</label>
<input type="text" name="meta_keywords" class="form-control" placeholder="website, builder, <?php echo htmlspecialchars(strtolower($site->name)); ?>" style="max-width:400px">
</div>
<button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i> Save Settings</button>
</form>
</div>
