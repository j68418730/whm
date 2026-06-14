<h3 style="color:var(--accent);margin-bottom:20px">Tweak Settings</h3>
<form method="POST" action="/admin/tweak">
<?php foreach ($settings as $category => $items): ?>
<div class="card" style="margin-bottom:16px">
<h4 style="color:var(--accent);font-size:15px;margin-bottom:12px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px"><?php echo htmlspecialchars($category); ?></h4>
<?php foreach ($items as $item): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.03)">
<div><span style="font-size:14px"><?php echo htmlspecialchars($item['label']); ?></span>
<?php if ($item['type'] === 'toggle'): ?>
<label style="position:relative;display:inline-block;width:44px;height:24px;cursor:pointer">
<input type="checkbox" name="<?php echo $item['key']; ?>" <?php echo ($item['default'] ?? false) ? 'checked' : ''; ?> style="opacity:0;width:0;height:0">
<span style="position:absolute;inset:0;background:<?php echo ($item['default'] ?? false) ? '#0A84FF' : 'rgba(255,255,255,.15)'; ?>;border-radius:12px;transition:.3s"></span>
<span style="position:absolute;top:2px;left:<?php echo ($item['default'] ?? false) ? '24px' : '2px'; ?>;width:20px;height:20px;background:#fff;border-radius:50%;transition:.3s"></span>
</label>
<?php elseif ($item['type'] === 'select'): ?>
<select name="<?php echo $item['key']; ?>" style="padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none">
<?php foreach ($item['options'] as $val => $label): ?><option value="<?php echo $val; ?>" <?php echo ($item['default'] ?? '') === $val ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option><?php endforeach; ?>
</select>
<?php else: ?>
<input type="<?php echo $item['type'] === 'number' ? 'number' : 'text'; ?>" name="<?php echo $item['key']; ?>" value="<?php echo htmlspecialchars((string)($item['default'] ?? '')); ?>" style="padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none;width:120px">
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
<div style="margin-top:16px"><button type="submit" class="btn primary">Save All Settings</button></div>
</form>
