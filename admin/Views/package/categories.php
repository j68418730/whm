<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div style="display:flex;gap:12px;align-items:start;flex-wrap:wrap;margin-bottom:16px">
<a href="/admin/packages" class="btn secondary">&larr; Back to Packages</a>
</div>

<h3 style="color:var(--accent);margin-bottom:16px"><i class="bi bi-tags"></i> Package Categories</h3>

<div class="card" style="max-width:700px;margin-bottom:20px">
<form method="POST" action="/admin/packages/categories" id="catForm">
<div style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
<div class="form-group" style="flex:2;min-width:150px"><label>Category Name</label><input name="name" required placeholder="e.g. Web Hosting"></div>
<div class="form-group" style="flex:1;min-width:60px"><label>Sort</label><input name="sort_order" type="number" value="0"></div>
</div>

<div class="form-group"><label>Icon (click to select, or type custom)</label>
<div style="display:flex;gap:10px;align-items:center;margin-bottom:8px">
<div id="iconPreview" style="font-size:36px;width:50px;text-align:center">🌐</div>
<input name="icon" id="iconInput" value="🌐" style="flex:1" placeholder="Emoji, AI text, or /theme/assets/img/icon.png">
</div>
<div style="display:flex;flex-wrap:wrap;gap:6px;max-height:120px;overflow-y:auto;padding:8px;background:rgba(255,255,255,.03);border-radius:8px">
<?php $emojis = ['🌐','🏢','🎵','🖥','🔧','📡','☁️','💻','📧','🗄️','🔒','⚡','📊','🎚️','🎤','💾','📶','🛡️','🚀','📦','⭐','🔥','💎','🪐','🌍','🔮','⚙️','📁','🖨️','📟','🕸️','🧩'];
foreach ($emojis as $emoji): ?>
<span onclick="pickIcon('<?php echo $emoji; ?>')" style="font-size:24px;cursor:pointer;padding:4px 6px;border-radius:6px;transition:.15s;border:1px solid transparent" onmouseover="this.style.borderColor='rgba(0,191,255,.3)'" onmouseout="this.style.borderColor='transparent'"><?php echo $emoji; ?></span>
<?php endforeach; ?>
<span onclick="generateIcon()" title="Generate AI icon from text" style="font-size:20px;cursor:pointer;padding:4px 6px;border-radius:6px;border:1px dashed rgba(0,191,255,.3);color:var(--accent)">🤖</span>
</div>
<p style="color:var(--text-muted);font-size:12px;margin-top:6px">Click an emoji, or type your own text and click 🤖 to generate, or paste an image URL</p>

<script>
function generateIcon() {
    var name = document.querySelector('[name=name]').value || 'icon';
    if (confirm('Use AI to generate icon? (DALL-E, costs ~$0.04)')) {
        var url = '/api/icon?text=' + encodeURIComponent(name) + '&ai=1';
        fetch(url).then(function(r) {
            document.getElementById('iconInput').value = r.url;
            document.getElementById('iconPreview').innerHTML = '<img src="' + r.url + '" style="width:36px;height:36px;border-radius:6px" onerror="this.outerHTML=\'?\'">';
        }).catch(function() {
            var bg = prompt('AI failed. Background color?', '#0A84FF');
            var fallback = '/api/icon?text=' + encodeURIComponent(name) + '&bg=' + encodeURIComponent(bg || '0A84FF');
            document.getElementById('iconInput').value = fallback;
            document.getElementById('iconPreview').innerHTML = '<img src="' + fallback + '" style="width:36px;height:36px;border-radius:6px">';
        });
    } else {
        var bg = prompt('Background color (hex):', '#0A84FF');
        var fallback = '/api/icon?text=' + encodeURIComponent(name) + '&bg=' + encodeURIComponent(bg || '0A84FF');
        document.getElementById('iconInput').value = fallback;
        document.getElementById('iconPreview').innerHTML = '<img src="' + fallback + '" style="width:36px;height:36px;border-radius:6px">';
    }
}
</script>
</div>

<button type="submit" class="btn primary">Add Category</button>
</form>
</div>

<script>
function pickIcon(e) {
    document.getElementById('iconInput').value = e;
    document.getElementById('iconPreview').textContent = e;
}
document.getElementById('iconInput').addEventListener('input', function() {
    document.getElementById('iconPreview').textContent = this.value || '?';
});
</script>

<table>
<tr><th>Icon</th><th>Name</th><th>Sort</th><th>Actions</th></tr>
<?php if (!empty($categories)): foreach ($categories as $cat): ?>
<tr>
<form method="POST" action="/admin/packages/categories/update/<?php echo $cat->id; ?>" style="display:contents">
<td style="font-size:24px;text-align:center"><?php
$icon = $cat->icon ?? '📦';
if (str_starts_with($icon, '/') || str_starts_with($icon, 'http')) {
    echo '<img src="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '" style="width:32px;height:32px;border-radius:6px">';
} else {
    echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');
}
?></td>
<td><input name="name" value="<?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?>" style="width:100%;padding:4px 8px;border-radius:4px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></td>
<td><input name="sort_order" type="number" value="<?php echo $cat->sort_order ?? 0; ?>" style="width:60px;padding:4px;border-radius:4px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;text-align:center"></td>
<td style="display:flex;gap:4px">
<button type="submit" class="btn btn-sm" style="background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2)">Save</button>
<a href="/admin/packages/categories/delete/<?php echo $cat->id; ?>" class="btn btn-sm" style="background:rgba(255,50,50,.15);color:#ff6b6b;border:1px solid rgba(255,50,50,.2)" onclick="return confirm('Delete this category?')">Delete</a>
</td>
</form>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No categories yet.</td></tr>
<?php endif; ?>
</table>
