<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
<div><h3 style="margin:0">Media Library</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0"><?php echo count($files); ?> files</p></div>
<div class="d-flex gap-2">
<a href="/user/websites/<?php echo $site->id; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
</div>
</div>

<div class="card" style="margin-bottom:16px">
<h4 style="margin-bottom:12px">Upload File</h4>
<form method="POST" action="/user/websites/<?php echo $site->id; ?>/media/upload" enctype="multipart/form-data">
<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
<input type="file" name="file" required style="background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:6px;padding:8px;color:#fff;flex:1">
<button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-upload"></i> Upload</button>
</div>
</form>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px">
<?php if (count($files) > 0): foreach ($files as $f):
$isImage = str_starts_with($f->type ?? '', 'image/');
?>
<div class="card" style="padding:12px;text-align:center">
<?php if ($isImage): ?>
<img src="<?php echo htmlspecialchars($f->path); ?>" style="width:100%;height:120px;object-fit:cover;border-radius:6px;margin-bottom:8px" alt="">
<?php else: ?>
<div style="font-size:36px;padding:20px;color:var(--text_muted)">📄</div>
<?php endif; ?>
<p style="font-size:11px;margin:0 0 4px;word-break:break-all"><?php echo htmlspecialchars($f->original_name ?: $f->filename); ?></p>
<small style="color:var(--text_muted)"><?php echo number_format($f->size / 1024, 1); ?> KB</small>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:40px;grid-column:1/-1">
<p style="color:var(--text_muted)">No media files uploaded yet.</p>
</div>
<?php endif; ?>
</div>
