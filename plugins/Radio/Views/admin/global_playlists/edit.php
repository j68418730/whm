<style>
.crd{background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:16px;margin-bottom:12px}
.crd h2{font-size:14px;font-weight:600;color:#e0e0e0;margin:0 0 8px}
.inp{padding:6px 8px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;width:100%;box-sizing:border-box}
.inp:focus{border-color:rgba(0,140,255,.3)}
label{display:block;font-size:11px;color:#94a3b8;margin-bottom:4px}
.btn{padding:6px 14px;border-radius:6px;font-size:11px;border:none;cursor:pointer;text-decoration:none;display:inline-block;margin:2px}
.btn-p{background:rgba(0,140,255,.2);color:#0A84FF}
.btn-d{background:rgba(255,68,68,.15);color:#ff4444}
.btn-s{background:rgba(255,255,255,.06);color:#94a3b8}
.upload-zone{border:2px dashed rgba(0,140,255,.2);border-radius:10px;padding:30px;text-align:center;color:#64748b;cursor:pointer;font-size:12px;transition:.15s}
.upload-zone:hover{border-color:rgba(0,140,255,.4);color:#94a3b8}
table{width:100%;border-collapse:collapse;font-size:11px}
th{padding:8px 6px;text-align:left;font-weight:600;color:#64748b;border-bottom:1px solid rgba(255,255,255,.06)}
td{padding:8px 6px;border-bottom:1px solid rgba(255,255,255,.04);color:#c0c0c0}
.empty-state{padding:30px;text-align:center;color:#64748b;font-size:12px}
</style>
<div style="max-width:800px;margin:0 auto">
<div class="crd">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
  <h2>Edit: <?=htmlspecialchars($playlist->name)?></h2>
  <a href="/admin/radio/global-playlists" class="btn btn-s">&laquo; Back</a>
</div>
<form method="post" action="/admin/radio/global-playlists/update/<?=$playlist->id?>" style="display:flex;gap:8px;align-items:end">
  <div style="flex:1"><label>Name</label><input class="inp" name="name" value="<?=htmlspecialchars($playlist->name)?>"></div>
  <div style="flex:2"><label>Description</label><input class="inp" name="description" value="<?=htmlspecialchars($playlist->description ?? '')?>"></div>
  <button class="btn btn-p">Save</button>
</form>
</div>

<div class="crd"><h2>Upload Songs</h2>
<form method="post" action="/admin/radio/global-playlists/upload/<?=$playlist->id?>" enctype="multipart/form-data">
  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <label style="flex:1;padding:20px;border:2px dashed rgba(0,140,255,.2);border-radius:10px;text-align:center;color:#64748b;font-size:12px;cursor:pointer;transition:.15s;display:block" onmouseover="this.style.borderColor='rgba(0,140,255,.4)';this.style.color='#94a3b8'" onmouseout="this.style.borderColor='rgba(0,140,255,.2)';this.style.color='#64748b'">
    <input type="file" name="files[]" multiple accept=".mp3,.aac,.ogg,.flac,.wav,.m4a" style="display:block;margin:0 auto;font-size:11px;color:#e0e0e0;background:rgba(0,0,0,.3);padding:6px;border-radius:4px;border:1px solid rgba(255,255,255,.08)" onchange="document.getElementById('file-count').textContent=this.files.length+' file(s) selected'">
    <span id="file-count" style="display:block;margin-top:6px">Click to browse (mp3, aac, ogg, flac, wav, m4a)</span>
    </label>
  </div>
  <button class="btn btn-p" style="margin-top:8px">Upload</button>
</form>
</div>

<div class="crd"><h2>Songs (<?=count($items)?>)</h2>
<?php if (empty($items)): ?>
<div class="empty-state">No songs in this playlist.</div>
<?php else: ?>
<table><tr><th>Title</th><th>Artist</th><th>File</th><th>Size</th><th>Actions</th></tr>
<?php foreach ($items as $item): ?>
<tr>
  <td><?=htmlspecialchars($item->title ?? '')?></td><td><?=htmlspecialchars($item->artist ?? '')?></td>
  <td style="font-size:10px;color:#64748b"><?=htmlspecialchars(basename($item->file_path ?? ''))?></td>
  <td><?=$item->file_size ? round($item->file_size / 1048576, 1) . ' MB' : '-'?></td>
  <td><a href="/admin/radio/global-playlists/remove-song/<?=$item->id?>" class="btn btn-d" style="padding:4px 10px;font-size:10px" onclick="return confirm('Remove this song?')">Remove</a></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>
</div>
