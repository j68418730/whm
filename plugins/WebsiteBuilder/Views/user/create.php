<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center">
<div><h3 style="margin:0">Create Website</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0">Choose a template and name your site</p></div>
<a href="/user/websites" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
</div>

<?php if (count($templates) > 0): ?>
<form method="POST" action="/user/websites/store" id="createSiteForm">
<div class="card" style="margin-bottom:16px">
<h4 style="margin-bottom:12px">Site Details</h4>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:600px">
<div>
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Website Name</label>
<input type="text" name="name" class="form-control" required placeholder="My Awesome Site" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
</div>
<div>
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Domain (optional)</label>
<input type="text" name="domain" class="form-control" placeholder="mywebsite.com" value="<?php echo htmlspecialchars($_POST['domain'] ?? ''); ?>">
</div>
</div>
</div>

<div class="card">
<h4 style="margin-bottom:16px">Choose a Template</h4>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px">
<?php foreach ($templates as $t):
$cfg = json_decode($t->config, true);
$pageCount = isset($cfg['pages']) ? count($cfg['pages']) : 0;
?>
<label class="template-card" style="cursor:pointer;background:rgba(8,16,28,.85);border:2px solid rgba(0,191,255,.1);border-radius:12px;padding:20px;text-align:center;transition:.3s;display:block" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor=this.querySelector('input').checked?'var(--accent)':'rgba(0,191,255,.1)'">
<input type="radio" name="template_id" value="<?php echo $t->id; ?>" <?php echo $loop->first ?? !isset($_POST['template_id']) ? 'checked' : ''; ?> style="display:none">
<div style="font-size:36px;margin-bottom:8px">📄</div>
<h4 style="margin:0 0 4px;font-size:14px"><?php echo htmlspecialchars($t->name); ?></h4>
<p style="color:var(--text_muted);font-size:11px;margin:0 0 4px"><?php echo htmlspecialchars($t->description ?: ''); ?></p>
<small style="color:var(--text_muted)"><?php echo $pageCount; ?> pages</small>
</label>
<?php endforeach; ?>
</div>
</div>

<div style="margin-top:16px;text-align:center">
<button type="submit" class="btn btn-primary" style="padding:12px 40px;font-size:16px"><i class="bi bi-magic"></i> Create Website</button>
</div>
</form>
<?php else: ?>
<div class="card" style="text-align:center;padding:40px">
<h4>No templates available</h4>
<p style="color:var(--text_muted)">Please contact support to enable website templates.</p>
</div>
<?php endif; ?>
