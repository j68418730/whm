<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:16px">
<span style="color:var(--text-secondary);font-size:14px">📁 <?php echo htmlspecialchars($current); ?></span>
<form method="POST" action="/user/files/upload" enctype="multipart/form-data" style="display:flex;gap:6px;align-items:center;flex:1;flex-wrap:wrap">
<input type="hidden" name="dir" value="<?php echo htmlspecialchars($current); ?>">
<input type="file" name="file" required style="flex:1;min-width:150px;padding:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:13px">
<button type="submit" class="btn btn-sm primary">Upload</button>
</form>
<form method="POST" action="/user/files/mkdir" style="display:flex;gap:6px;align-items:center">
<input type="hidden" name="dir" value="<?php echo htmlspecialchars($current); ?>">
<input name="name" placeholder="folder name" required style="padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:13px;width:120px;outline:none">
<button type="submit" class="btn btn-sm secondary">+ Folder</button>
</form>
<form method="POST" action="/user/files/archive" style="display:flex;gap:6px;align-items:center">
<input type="hidden" name="dir" value="<?php echo htmlspecialchars($current); ?>">
<input name="name" value="backup" style="padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:13px;width:80px;outline:none">
<button type="submit" class="btn btn-sm secondary">Zip</button>
</form>
</div>

<table><tr><th>Name</th><th>Size</th><th>Modified</th><th></th></tr>
<tr onclick="window.location='/user/files?dir=<?php echo urlencode(dirname($current)); ?>'" style="cursor:pointer">
<td style="color:var(--accent)">📁 ..</td><td>-</td><td>-</td><td></td></tr>
<?php foreach ($items as $item): ?>
<tr>
<?php if ($item['is_dir']): ?>
<td><a href="/user/files?dir=<?php echo urlencode($item['path']); ?>" style="color:#fff;text-decoration:none">📁 <?php echo htmlspecialchars($item['name']); ?></a></td>
<td>-</td>
<?php else: ?>
<td>📄 <?php echo htmlspecialchars($item['name']); ?></td>
<td><?php echo $item['size'] > 1048576 ? round($item['size']/1048576,1).' MB' : ($item['size'] > 1024 ? round($item['size']/1024,1).' KB' : $item['size'].' B'); ?></td>
<?php endif; ?>
<td style="font-size:12px;color:var(--text-muted)"><?php echo $item['modified']; ?></td>
<td style="display:flex;gap:4px">
<?php if (!$item['is_dir']): ?><a href="/user/files/download?file=<?php echo urlencode($item['path']); ?>" class="btn btn-sm secondary" style="padding:3px 8px;font-size:11px">DL</a><?php endif; ?>
<a href="/user/files/delete?path=<?php echo urlencode($item['path']); ?>" class="btn btn-sm" style="background:rgba(255,50,50,.15);color:#ff6b6b;padding:3px 8px;font-size:11px;border:none" onclick="return confirm('Delete <?php echo $item['name']; ?>?')">✕</a>
</td>
</tr>
<?php endforeach; ?>
</table>
