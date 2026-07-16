<style>
.crd{background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:16px;margin-bottom:12px}
.crd h2{font-size:14px;font-weight:600;color:#e0e0e0;margin:0 0 4px}
.crd p{font-size:11px;color:#64748b;margin:0 0 10px}
.btn{padding:6px 14px;border-radius:6px;font-size:11px;border:none;cursor:pointer;text-decoration:none;display:inline-block;margin:2px}
.btn-p{background:rgba(0,140,255,.2);color:#0A84FF}
.btn-p:hover{background:rgba(0,140,255,.3)}
.btn-d{background:rgba(255,68,68,.15);color:#ff4444}
.btn-d:hover{background:rgba(255,68,68,.25)}
.btn-s{background:rgba(255,255,255,.06);color:#94a3b8}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px}
table{width:100%;border-collapse:collapse;font-size:11px}
th{padding:8px 6px;text-align:left;font-weight:600;color:#64748b;border-bottom:1px solid rgba(255,255,255,.06)}
td{padding:8px 6px;border-bottom:1px solid rgba(255,255,255,.04);color:#c0c0c0}
.empty-state{padding:30px;text-align:center;color:#64748b;font-size:12px}
</style>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
  <h1 style="font-size:20px;font-weight:700;color:#e0e0e0;margin:0">Global Playlists</h1>
  <a href="/admin/radio/global-playlists/create" class="btn btn-p">+ New Playlist</a>
</div>
<?php if (empty($playlists)): ?>
<div class="crd"><div class="empty-state">No global playlists yet.</div></div>
<?php else: ?>
<div class="grid">
<?php foreach ($playlists as $pl): $its = $items[$pl->id] ?? []; ?>
<div class="crd">
  <div style="display:flex;justify-content:space-between;align-items:start">
    <div><h2><?=htmlspecialchars($pl->name)?></h2><p><?=htmlspecialchars($pl->description ?: 'No description')?></p></div>
    <div style="font-size:10px;color:#64748b"><?=count($its)?> songs</div>
  </div>
  <div style="display:flex;gap:4px;margin-top:8px">
    <a href="/admin/radio/global-playlists/edit/<?=$pl->id?>" class="btn btn-p btn-s" style="padding:4px 10px;font-size:10px">Manage</a>
    <a href="/admin/radio/global-playlists/delete/<?=$pl->id?>" class="btn btn-d" style="padding:4px 10px;font-size:10px" onclick="return confirm('Delete this playlist and all its songs?')">Delete</a>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
