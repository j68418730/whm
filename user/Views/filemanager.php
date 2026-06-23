<style>
.fm-toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;padding:14px 16px;background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;margin-bottom:16px}
.fm-toolbar .path{font-size:13px;color:#94a3b8;display:flex;align-items:center;gap:4px;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.fm-toolbar .path span{color:#e0e0e0}
.fm-toolbar .path .sep{color:#475569;margin:0 2px}
.fm-toolbar form{display:flex;gap:6px;align-items:center}
.fm-toolbar input{padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none}
.fm-toolbar input:focus{border-color:#0A84FF}
.fm-toolbar input[type=file]{padding:4px;width:160px}
.fm-toolbar .btn{padding:6px 12px;border-radius:6px;font-size:12px;font-weight:500;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px}
.fm-toolbar .btn-primary{background:rgba(0,140,255,.12);color:#0A84FF;border:1px solid rgba(0,140,255,.2)}
.fm-toolbar .btn-primary:hover{background:rgba(0,140,255,.2)}
.fm-toolbar .btn-secondary{background:rgba(255,255,255,.06);color:#ccc;border:1px solid rgba(255,255,255,.1)}
.fm-toolbar .btn-secondary:hover{background:rgba(255,255,255,.1)}

.fm-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px}
.fm-item{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.06);border-radius:10px;padding:14px;text-align:center;transition:.15s;position:relative}
.fm-item:hover{border-color:rgba(0,140,255,.2);transform:translateY(-2px)}
.fm-item .icon{font-size:36px;margin-bottom:6px}
.fm-item .name{font-size:12px;font-weight:500;color:#e0e0e0;word-break:break-all;max-height:36px;overflow:hidden}
.fm-item .size{font-size:10px;color:#64748b;margin-top:2px}
.fm-item .actions{position:absolute;top:6px;right:6px;display:flex;gap:3px;opacity:0;transition:.15s}
.fm-item:hover .actions{opacity:1}
.fm-item .actions a{width:24px;height:24px;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:11px;text-decoration:none;background:rgba(0,0,0,.5)}

.fm-list{width:100%;border-collapse:collapse;font-size:13px}
.fm-list th{text-align:left;padding:10px 14px;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid rgba(255,255,255,.06);font-weight:600}
.fm-list td{padding:10px 14px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle}
.fm-list tr:hover td{background:rgba(0,191,255,.02)}
.fm-list .file-icon{font-size:16px;margin-right:8px}
.fm-list .file-name{color:#e0e0e0;text-decoration:none;display:flex;align-items:center;gap:4px}
.fm-list .file-name:hover{color:#0A84FF}
.fm-list .file-size{color:#64748b;font-size:12px}
.fm-list .file-date{color:#64748b;font-size:12px}
.fm-list .file-actions{display:flex;gap:4px;justify-content:flex-end}
.fm-list .file-actions a{padding:4px 8px;border-radius:4px;font-size:11px;text-decoration:none}
.fm-list .file-actions .dl{background:rgba(0,140,255,.08);color:#0A84FF}
.fm-list .file-actions .dl:hover{background:rgba(0,140,255,.15)}
.fm-list .file-actions .del{background:rgba(248,113,113,.08);color:#f87171}
.fm-list .file-actions .del:hover{background:rgba(248,113,113,.15)}
.fm-empty{padding:60px 20px;text-align:center;color:#64748b}
.fm-empty .icon{font-size:48px;margin-bottom:12px}
</style>

<script>
var viewMode = localStorage.getItem('fm_view') || 'grid';
function toggleView() {
    viewMode = viewMode === 'grid' ? 'list' : 'grid';
    localStorage.setItem('fm_view', viewMode);
    document.getElementById('fmGrid').style.display = viewMode === 'grid' ? 'grid' : 'none';
    document.getElementById('fmList').style.display = viewMode === 'list' ? 'table' : 'none';
}
</script>

<div class="fm-toolbar">
<div class="path">📁 Path: <?php
$parts = explode('/', trim($current, '/'));
$cumulative = '';
echo '<a href="/user/files" style="color:#0A84FF;text-decoration:none">Home</a>';
foreach ($parts as $p) {
    if (!$p) continue;
    $cumulative .= '/' . $p;
    echo '<span class="sep">/</span><a href="/user/files?dir=' . urlencode($cumulative) . '" style="color:#e0e0e0;text-decoration:none">' . htmlspecialchars($p) . '</a>';
}
?></div>
<div style="display:flex;gap:6px">
<button class="btn btn-secondary" onclick="toggleView()">☰ ▦</button>
</div>
</div>

<div class="fm-toolbar">
<form method="POST" action="/user/files/upload" enctype="multipart/form-data" style="flex:1">
<input type="hidden" name="dir" value="<?php echo htmlspecialchars($current); ?>">
<input type="file" name="file" required>
<button type="submit" class="btn btn-primary">📤 Upload</button>
</form>
<form method="POST" action="/user/files/mkdir">
<input type="hidden" name="dir" value="<?php echo htmlspecialchars($current); ?>">
<input name="name" placeholder="folder name" required style="width:110px">
<button type="submit" class="btn btn-secondary">📁 +Folder</button>
</form>
<form method="POST" action="/user/files/archive">
<input type="hidden" name="dir" value="<?php echo htmlspecialchars($current); ?>">
<input name="name" value="backup" style="width:80px">
<button type="submit" class="btn btn-secondary">📦 Zip</button>
</form>
</div>

<!-- Grid View -->
<div id="fmGrid" style="display:<?php echo $viewMode === 'grid' ? 'grid' : 'none'; ?>" class="fm-grid">
<?php if ($current !== '/'): ?>
<a href="/user/files?dir=<?php echo urlencode(dirname($current)); ?>" class="fm-item" style="text-decoration:none;display:flex;flex-direction:column;align-items:center;justify-content:center">
<div class="icon">📂</div><div class="name" style="color:#0A84FF">..</div>
</a>
<?php endif; ?>
<?php if (empty($items)): ?>
<div class="fm-empty" style="grid-column:1/-1"><div class="icon">📂</div><p>This folder is empty</p></div>
<?php else: foreach ($items as $item): ?>
<div class="fm-item">
<div class="actions">
<?php if (!$item['is_dir']): ?><a href="/user/files/download?file=<?php echo urlencode($item['path']); ?>" class="dl" style="color:#0A84FF;background:rgba(0,140,255,.1)" title="Download">⬇</a><?php endif; ?>
<a href="/user/files/delete?path=<?php echo urlencode($item['path']); ?>" class="del" style="color:#f87171;background:rgba(248,113,113,.1)" title="Delete" onclick="return confirm('Delete <?php echo htmlspecialchars($item['name']); ?>?')">✕</a>
</div>
<?php if ($item['is_dir']): ?>
<a href="/user/files?dir=<?php echo urlencode($item['path']); ?>" style="text-decoration:none;color:inherit">
<div class="icon">📁</div><div class="name"><?php echo htmlspecialchars($item['name']); ?></div>
</a>
<?php else: ?>
<div class="icon">📄</div>
<div class="name"><?php echo htmlspecialchars($item['name']); ?></div>
<div class="size"><?php echo $item['size'] > 1048576 ? round($item['size']/1048576,1).' MB' : ($item['size'] > 1024 ? round($item['size']/1024,1).' KB' : $item['size'].' B'); ?></div>
<?php endif; ?>
</div>
<?php endforeach; endif; ?>
</div>

<!-- List View -->
<table id="fmList" style="display:<?php echo $viewMode === 'list' ? 'table' : 'none'; ?>" class="fm-list">
<thead><tr><th>Name</th><th>Size</th><th>Modified</th><th></th></tr></thead>
<tbody>
<?php if ($current !== '/'): ?>
<tr onclick="window.location='/user/files?dir=<?php echo urlencode(dirname($current)); ?>'" style="cursor:pointer">
<td><span class="file-icon">📂</span> ..</td><td class="file-size">-</td><td class="file-date">-</td><td></td></tr>
<?php endif; ?>
<?php if (empty($items)): ?>
<tr><td colspan="4" style="text-align:center;padding:40px;color:#64748b">This folder is empty</td></tr>
<?php else: foreach ($items as $item): ?>
<tr>
<?php if ($item['is_dir']): ?>
<td><a href="/user/files?dir=<?php echo urlencode($item['path']); ?>" class="file-name"><span class="file-icon">📁</span> <?php echo htmlspecialchars($item['name']); ?></a></td>
<td class="file-size">-</td>
<?php else: ?>
<td><span class="file-name" style="cursor:default"><span class="file-icon">📄</span> <?php echo htmlspecialchars($item['name']); ?></span></td>
<td class="file-size"><?php echo $item['size'] > 1048576 ? round($item['size']/1048576,1).' MB' : ($item['size'] > 1024 ? round($item['size']/1024,1).' KB' : $item['size'].' B'); ?></td>
<?php endif; ?>
<td class="file-date"><?php echo $item['modified']; ?></td>
<td class="file-actions">
<?php if (!$item['is_dir']): ?><a href="/user/files/download?file=<?php echo urlencode($item['path']); ?>" class="dl">⬇ Download</a><?php endif; ?>
<a href="/user/files/delete?path=<?php echo urlencode($item['path']); ?>" class="del" onclick="return confirm('Delete <?php echo htmlspecialchars($item['name'])?>?')">✕ Delete</a>
</td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
