<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center">
<div><h3 style="margin:0">Menu Manager</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0"><?php echo htmlspecialchars($site->name); ?></p></div>
<a href="/user/websites/<?php echo $site->id; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
</div>

<div class="card">
<form method="POST" action="/user/websites/<?php echo $site->id; ?>/menus/save">
<div id="menusContainer">
<?php if (count($menus) > 0): foreach ($menus as $mi => $m):
$items = json_decode($m->items ?? '[]', true);
?>
<div class="menu-group" style="margin-bottom:16px;padding:16px;background:rgba(8,16,28,.5);border-radius:8px">
<h4 style="margin-bottom:8px;font-size:14px">Menu: <?php echo htmlspecialchars($m->name); ?></h4>
<div style="display:flex;gap:8px;margin-bottom:8px">
<input name="menu_name_<?php echo $mi; ?>" value="<?php echo htmlspecialchars($m->name); ?>" placeholder="Menu Name" style="flex:1;padding:6px 10px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff">
<select name="menu_location_<?php echo $mi; ?>" style="padding:6px 10px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff">
<option value="main" <?php echo $m->location === 'main' ? 'selected' : ''; ?>>Main Menu</option>
<option value="footer" <?php echo $m->location === 'footer' ? 'selected' : ''; ?>>Footer</option>
<option value="sidebar" <?php echo $m->location === 'sidebar' ? 'selected' : ''; ?>>Sidebar</option>
</select>
</div>
<div class="menu-items">
<?php foreach ($items as $item): ?>
<div class="menu-item" style="display:flex;gap:6px;margin-bottom:4px">
<input name="menu_<?php echo $mi; ?>_label[]" value="<?php echo htmlspecialchars($item['label'] ?? ''); ?>" placeholder="Label" style="flex:1;padding:6px 10px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:12px">
<input name="menu_<?php echo $mi; ?>_url[]" value="<?php echo htmlspecialchars($item['url'] ?? ''); ?>" placeholder="URL" style="flex:1;padding:6px 10px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:12px">
<button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
</div>
<?php endforeach; ?>
</div>
<button type="button" class="btn btn-sm btn-secondary" onclick="addMenuItem(this, <?php echo $mi; ?>)"><i class="bi bi-plus"></i> Add Item</button>
</div>
<?php endforeach; else: ?>
<p style="color:var(--text_muted);text-align:center;padding:20px">No menus configured. Default menus will be created from template.</p>
<?php endif; ?>
</div>
<button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i> Save Menus</button>
</form>
</div>

<script>
function addMenuItem(btn, idx) {
const div = document.createElement('div');
div.className = 'menu-item';
div.style.cssText = 'display:flex;gap:6px;margin-bottom:4px';
div.innerHTML = '<input name="menu_' + idx + '_label[]" placeholder="Label" style="flex:1;padding:6px 10px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:12px"><input name="menu_' + idx + '_url[]" placeholder="URL" style="flex:1;padding:6px 10px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:12px"><button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>';
btn.parentElement.querySelector('.menu-items').appendChild(div);
}
</script>
