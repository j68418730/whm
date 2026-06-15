<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="card" style="max-width:700px;margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:16px">Theme Selector</h3>
<form method="POST" action="/admin/theme/update" enctype="multipart/form-data">
<div class="form-group"><label>Active Theme</label>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:8px;margin-top:8px">
<?php foreach ($themes as $t): ?>
<label style="display:block;padding:10px;border-radius:8px;border:2px solid <?php echo $currentTheme === $t ? 'var(--accent)' : 'rgba(255,255,255,.08)'; ?>;text-align:center;cursor:pointer;background:<?php echo $currentTheme === $t ? 'rgba(0,191,255,.08)' : 'transparent'; ?>">
<input type="radio" name="theme" value="<?php echo $t; ?>" <?php echo $currentTheme === $t ? 'checked' : ''; ?> style="display:none">
<div style="width:24px;height:24px;border-radius:50%;margin:0 auto 6px;background:var(--accent)"></div>
<div style="font-size:12px;font-weight:600;text-transform:capitalize"><?php echo $t; ?></div>
</label>
<?php endforeach; ?>
</div>
</div>
</div>

<div class="card" style="max-width:700px;margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">Branding Assets</h3>
<div class="form-group"><label>Logo</label><input name="logo_file" type="file" accept="image/png"><br><small style="color:var(--text-secondary)">Uploads to theme/assets/img/logo.png</small></div>
<div class="form-group"><label>Header Image</label><input name="header_file" type="file" accept="image/png"><br><small style="color:var(--text-secondary)">Uploads to theme/assets/img/header.png</small></div>
<div class="form-group"><label>Footer Image</label><input name="footer_file" type="file" accept="image/png"><br><small style="color:var(--text-secondary)">Uploads to theme/assets/img/footer.png</small></div>
<div class="form-group"><label>Footer Logo URL</label><input name="footer_logo_url" value="<?php echo htmlspecialchars($settings['footer_logo_url'] ?? '/theme/assets/img/logo.png'); ?>" placeholder="/theme/assets/img/logo.png"></div>
</div>

<div class="card" style="max-width:700px;margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">Content</h3>
<div class="form-group"><label>Footer Text</label><input name="footer_text" value="<?php echo htmlspecialchars($settings['footer_text'] ?? 'Building the future of hosting infrastructure.'); ?>"></div>
</div>

<div class="card" style="max-width:700px;margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">Custom Colors</h3>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
<div class="form-group"><label>Primary Color</label><input name="primary_color" type="color" value="<?php echo htmlspecialchars($settings['primary_color'] ?? '#008cff'); ?>"></div>
<div class="form-group"><label>Background</label><input name="bg_color" type="color" value="<?php echo htmlspecialchars($settings['bg_color'] ?? '#0a0a1a'); ?>"></div>
<div class="form-group"><label>Accent</label><input name="accent_color" type="color" value="<?php echo htmlspecialchars($settings['accent_color'] ?? '#3bb8ff'); ?>"></div>
</div>
</div>

<div class="card" style="max-width:700px;margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">Custom CSS</h3>
<div class="form-group"><textarea name="custom_css" rows="4" style="width:100%;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);color:#e0e0e0;border-radius:6px;padding:10px;font-family:monospace;font-size:12px"><?php echo htmlspecialchars($settings['custom_css'] ?? ''); ?></textarea></div>
</div>

<button type="submit" class="btn primary">Save Theme Settings</button>
</form>
