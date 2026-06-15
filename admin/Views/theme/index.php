<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="card" style="max-width:900px;margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:16px">Theme Selector</h3>
<form method="POST" action="/admin/theme/update" enctype="multipart/form-data" id="themeForm">
<input type="hidden" name="theme" id="themeInput" value="<?php echo $currentTheme; ?>">

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px" id="themeGrid">
<?php
$colors = [
  'planethosts'=>['bg'=>'#000000','ac'=>'#0A84FF','txt'=>'#ffffff','name'=>'Planet Hosts'],
  'cosmic'=>['bg'=>'#0a0a1a','ac'=>'#008cff','txt'=>'#e0e0ff','name'=>'Cosmic'],
  'nebula'=>['bg'=>'#0d0a1a','ac'=>'#8b5cf6','txt'=>'#e8e0f0','name'=>'Nebula'],
  'cyber'=>['bg'=>'#0a0f0a','ac'=>'#00ff88','txt'=>'#ccffdd','name'=>'Cyber'],
  'ember'=>['bg'=>'#1a0f0a','ac'=>'#f97316','txt'=>'#f0ddd0','name'=>'Ember'],
  'frost'=>['bg'=>'#0a1218','ac'=>'#06b6d4','txt'=>'#d0e8f0','name'=>'Frost'],
  'midnight'=>['bg'=>'#0a0e1a','ac'=>'#2563eb','txt'=>'#d0d8f0','name'=>'Midnight'],
  'oxide'=>['bg'=>'#081010','ac'=>'#14b8a6','txt'=>'#cce8e0','name'=>'Oxide'],
  'sunset'=>['bg'=>'#1a0e0a','ac'=>'#f59e0b','txt'=>'#f0e0c0','name'=>'Sunset'],
  'ocean'=>['bg'=>'#080e14','ac'=>'#0ea5e9','txt'=>'#c0dce8','name'=>'Ocean'],
  'crimson'=>['bg'=>'#140808','ac'=>'#ef4444','txt'=>'#f0c8c8','name'=>'Crimson'],
];
foreach ($colors as $key => $c):
  $sel = $currentTheme === $key;
?>
<div onclick="pickTheme('<?php echo $key; ?>',this)" style="border:2px solid <?php echo $sel ? $c['ac'] : 'transparent'; ?>;border-radius:12px;overflow:hidden;cursor:pointer;background:<?php echo $c['bg']; ?>;transition:.15s">
<div style="height:10px;background:linear-gradient(90deg,<?php echo $c['ac']; ?>,<?php echo $c['ac']; ?>66);margin:0"></div>
<div style="padding:12px">
<div style="display:flex;align-items:center;gap:6px;margin-bottom:10px">
<div style="width:10px;height:10px;border-radius:3px;background:<?php echo $c['ac']; ?>"></div>
<div style="font-size:12px;font-weight:700;color:<?php echo $c['txt']; ?>"><?php echo $c['name']; ?></div>
<div style="margin-left:auto;font-size:9px;color:#666">&#9679;&#9679;&#9679;</div>
</div>
<div style="height:6px;border-radius:3px;background:<?php echo $c['bg']; ?>;opacity:.5;margin-bottom:4px"></div>
<div style="height:6px;border-radius:3px;background:<?php echo $c['bg']; ?>;opacity:.5;width:65%;margin-bottom:10px"></div>
<div style="display:flex;gap:4px">
<div style="flex:1;height:20px;border-radius:4px;background:<?php echo $c['ac']; ?>"></div>
<div style="flex:1;height:20px;border-radius:4px;border:1px solid <?php echo $c['ac']; ?>;opacity:.4"></div>
</div>
</div>
<div style="text-align:center;padding:8px;font-size:12px;font-weight:600;color:<?php echo $c['txt']; ?>;background:<?php echo $c['bg']; ?>;border-top:1px solid rgba(255,255,255,.05)"><?php echo $c['name']; ?></div>
</div>
<?php endforeach; ?>
</div>

<script>
function pickTheme(key, el) {
  document.getElementById('themeInput').value = key;
  document.querySelectorAll('#themeGrid > div').forEach(function(d) { d.style.borderColor = 'transparent'; });
  el.style.borderColor = getComputedStyle(el.querySelector('[style*="background:linear-gradient"]')).getPropertyValue('--accent') || '#008cff';
  var bar = document.getElementById('themeApplyBar');
  bar.style.display = 'flex';
  bar.querySelector('span').textContent = key.charAt(0).toUpperCase() + key.slice(1) + ' selected. Save to apply.';
}
</script>

<div id="themeApplyBar" style="display:none;position:sticky;bottom:0;left:0;right:0;z-index:999;background:var(--bg-card);border-top:1px solid var(--border-color);padding:14px 24px;justify-content:space-between;align-items:center;margin-top:16px">
<span style="color:var(--text-secondary);font-size:13px">Click a theme above to select it, then save.</span>
<button type="submit" class="btn primary">Save Theme</button>
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

<button type="submit" class="btn primary">Save All Theme Settings</button>
</form>
</div>
