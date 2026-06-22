<style>
.color-swatch{width:100%;height:36px;border-radius:6px;border:2px solid transparent;cursor:pointer;transition:.2s;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600}
.color-swatch:hover{border-color:var(--primary,#008cff)}
.color-swatch input[type=color]{opacity:0;position:absolute;width:0;height:0}
</style>

<div class="card" style="margin-bottom:20px">
<div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap">
<h3 style="margin:0">🎨 Theme Builder</h3>
<a href="/admin/themes" class="btn btn-secondary btn-sm" style="margin-left:auto">← Theme Manager</a>
</div>
<p style="color:var(--text_muted,#64748b);font-size:13px;margin-top:8px">Customize the visual appearance of your admin panel. Changes apply immediately after saving.</p>
</div>

<form method="POST" action="/admin/theme/update" enctype="multipart/form-data">
<div style="display:grid;grid-template-columns:280px 1fr;gap:20px">
<div class="card" style="padding:16px"><h4 style="font-size:13px;margin-bottom:10px">Active Theme</h4>
<select name="active_theme" style="margin-bottom:12px">
<?php foreach ($adminThemes as $key => $t): ?>
<option value="<?php echo $key; ?>" <?php echo $key === $activeAdmin ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['name'] ?? $key); ?> (<?php echo $key; ?>)</option>
<?php endforeach; ?>
</select>
<p style="font-size:11px;color:var(--text_muted);margin-bottom:8px">Version: <?php echo htmlspecialchars($currentTheme['version'] ?? '1.0'); ?> · by <?php echo htmlspecialchars($currentTheme['author'] ?? 'Planet-Hosts'); ?></p>
<div class="preview-box" style="background:var(--sidebar_bg,#0b1728);border-radius:8px;padding:12px;border:1px solid var(--border,rgba(0,191,255,.08))">
<div style="font-size:10px;color:var(--text_muted,#64748b);margin-bottom:6px">PREVIEW</div>
<div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
<div style="width:24px;height:24px;border-radius:4px;background:var(--primary,#008cff)"></div>
<div style="font-size:11px;font-weight:600;color:var(--text,#e0e0e0)">Theme Preview</div>
</div>
<div style="height:4px;border-radius:2px;background:var(--border,rgba(0,191,255,.08));margin-bottom:6px"></div>
<div style="display:flex;gap:4px">
<div style="flex:1;height:16px;border-radius:3px;background:var(--card_bg,rgba(8,16,28,.6))"></div>
<div style="flex:1;height:16px;border-radius:3px;background:var(--card_bg,rgba(8,16,28,.6))"></div>
</div>
</div>
</div>

<div style="display:flex;flex-direction:column;gap:14px">
<div class="card" style="padding:16px"><h4 style="font-size:13px;margin-bottom:10px">🎯 Branding Assets</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
<div><label style="font-size:11px;color:var(--text_muted);">Logo (PNG)</label><input type="file" name="logo_file" accept="image/png" style="padding:6px"></div>
<div><label style="font-size:11px;color:var(--text_muted);">Header Image</label><input type="file" name="header_file" accept="image/png" style="padding:6px"></div>
<div><label style="font-size:11px;color:var(--text_muted);">Footer Image</label><input type="file" name="footer_file" accept="image/png" style="padding:6px"></div>
</div>
<div class="form-group" style="margin-top:8px"><label style="font-size:11px;color:var(--text_muted);">Footer Logo URL</label><input name="footer_logo_url" value="<?php echo htmlspecialchars($settings['footer_logo_url'] ?? '/theme/assets/img/logo.png'); ?>"></div>
<div class="form-group"><label style="font-size:11px;color:var(--text_muted);">Footer Text</label><input name="footer_text" value="<?php echo htmlspecialchars($settings['footer_text'] ?? 'Building the future of hosting infrastructure.'); ?>"></div>
</div>

<div class="card" style="padding:16px"><h4 style="font-size:13px;margin-bottom:10px">🎨 Custom Colors</h4>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px">
<?php $colorFields = [
'primary_color'=>'Primary','bg_color'=>'Background','sidebar_bg'=>'Sidebar','card_bg'=>'Card BG',
'text_color'=>'Text','text_muted'=>'Muted Text','border_color'=>'Border',
'success_color'=>'Success','warning_color'=>'Warning','danger_color'=>'Danger'
];
$defaults = ['primary_color'=>'#008cff','bg_color'=>'#02050e','sidebar_bg'=>'#0b1728','card_bg'=>'rgba(8,16,28,.8)',
'text_color'=>'#e0e0e0','text_muted'=>'#64748b','border_color'=>'rgba(0,191,255,.08)',
'success_color'=>'#4ade80','warning_color'=>'#facc15','danger_color'=>'#f87171'];
foreach ($colorFields as $k => $label):
$val = $settings[$k] ?? $defaults[$k] ?? '#666';
$isRgba = str_starts_with($val, 'rgba');
?>
<div class="form-group" style="margin:0">
<label style="font-size:10px;color:var(--text_muted);margin-bottom:2px"><?php echo $label; ?></label>
<div class="color-swatch" style="background:<?php echo $isRgba ? '#0b1728' : $val; ?>;position:relative">
<input type="color" name="<?php echo $k; ?>" value="<?php echo $isRgba ? '#0b1728' : $val; ?>" onchange="this.parentElement.style.background=this.value">
</div>
</div>
<?php endforeach; ?>
</div>
</div>

<div class="card" style="padding:16px"><h4 style="font-size:13px;margin-bottom:10px">📝 Custom CSS</h4>
<textarea name="custom_css" rows="5" style="font-family:monospace;font-size:12px;background:rgba(0,0,0,.3);border:1px solid var(--border,rgba(0,191,255,.08));color:var(--text,#e0e0e0);border-radius:8px;padding:12px;width:100%"><?php echo htmlspecialchars($settings['custom_css'] ?? ''); ?></textarea>
</div>
</div>
</div>

<div style="margin-top:16px;display:flex;gap:10px">
<button type="submit" class="btn btn-primary">💾 Save Theme Settings</button>
</div>
</form>
