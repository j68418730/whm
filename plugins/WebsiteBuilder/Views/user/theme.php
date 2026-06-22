<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center">
<div><h3 style="margin:0">Theme Selection</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0"><?php echo htmlspecialchars($site->name); ?></p></div>
<a href="/user/websites/<?php echo $site->id; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
</div>

<?php if ($currentTheme): ?>
<div class="card" style="margin-bottom:16px;border-color:var(--accent)">
<h4 style="margin:0 0 8px">Current Theme: <?php echo htmlspecialchars($currentTheme->name); ?></h4>
<p style="color:var(--text_muted);font-size:12px;margin:0"><?php echo htmlspecialchars($currentTheme->description ?: ''); ?></p>
</div>
<?php endif; ?>

<form method="POST" action="/user/websites/<?php echo $site->id; ?>/theme/save">
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px">
<?php foreach ($themes as $t):
$cfg = json_decode($t->config, true);
$primary = $cfg['primary'] ?? '#008cff';
$selected = $currentTheme && $currentTheme->id === $t->id;
?>
<label class="theme-card" style="cursor:pointer;background:rgba(8,16,28,.85);border:2px solid <?php echo $selected ? 'var(--accent)' : 'rgba(0,191,255,.1)'; ?>;border-radius:12px;overflow:hidden;transition:.3s">
<input type="radio" name="theme_id" value="<?php echo $t->id; ?>" <?php echo $selected ? 'checked' : ''; ?> style="display:none">
<div style="height:60px;background:linear-gradient(135deg,<?php echo $primary; ?>,<?php echo $cfg['secondary'] ?? $primary; ?>);display:flex;align-items:center;justify-content:center;font-size:24px">🎨</div>
<div style="padding:16px">
<h4 style="margin:0 0 4px;font-size:14px"><?php echo htmlspecialchars($t->name); ?></h4>
<p style="color:var(--text_muted);font-size:11px;margin:0 0 8px"><?php echo htmlspecialchars($t->description ?: ''); ?></p>
<div style="display:flex;gap:6px">
<?php foreach (['primary','secondary','accent'] as $k):
$c = $cfg[$k] ?? '#333';
?>
<div style="width:20px;height:20px;border-radius:50%;background:<?php echo $c; ?>;border:2px solid rgba(255,255,255,.1)"></div>
<?php endforeach; ?>
</div>
</div>
</label>
<?php endforeach; ?>
</div>
<div style="margin-top:16px;text-align:center">
<button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Apply Theme</button>
</div>
</form>
