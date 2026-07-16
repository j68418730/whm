<style>
.crd{background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:16px;margin-bottom:12px}
.crd h2{font-size:14px;font-weight:600;color:#e0e0e0;margin:0 0 8px}
.crd p{font-size:11px;color:#64748b;margin:0 0 10px}
table{width:100%;border-collapse:collapse;font-size:11px}
th{padding:8px 6px;text-align:left;font-weight:600;color:#64748b;border-bottom:1px solid rgba(255,255,255,.06)}
td{padding:8px 6px;border-bottom:1px solid rgba(255,255,255,.04);color:#c0c0c0}
.empty-state{padding:30px;text-align:center;color:#64748b;font-size:12px}
.btn{padding:6px 14px;border-radius:6px;font-size:11px;border:none;cursor:pointer;text-decoration:none;display:inline-block}
.btn-p{background:rgba(0,140,255,.2);color:#0A84FF}
.btn-p:hover{background:rgba(0,140,255,.3)}
.btn-s{background:rgba(255,255,255,.06);color:#94a3b8}
select.inp{padding:4px 8px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none;margin:0 4px}
</style>

<div class="crd"><h2>Global Music Library</h2><p>Browse shared music and download songs to your station's playlists.</p></div>

<?php if (empty($playlists)): ?>
<div class="crd"><div class="empty-state">No global playlists available yet.</div></div>
<?php else: ?>
<?php foreach ($playlists as $pl): $items = $globalItems[$pl->id] ?? []; ?>
<div class="crd">
  <h2><?=htmlspecialchars($pl->name)?> <span style="font-size:10px;color:#64748b;font-weight:400">(<?=count($items)?> songs)</span></h2>
  <p><?=htmlspecialchars($pl->description ?: '')?></p>
  <?php if (empty($items)): ?>
  <div class="empty-state">No songs in this playlist.</div>
  <?php else: ?>
  <table><tr><th>Title</th><th>Artist</th><th>Size</th><th>Actions</th></tr>
  <?php foreach ($items as $item): ?>
  <tr>
    <td><?=htmlspecialchars($item->title ?? '')?></td><td><?=htmlspecialchars($item->artist ?? '')?></td>
    <td><?=$item->file_size ? round($item->file_size / 1048576, 1) . ' MB' : '-'?></td>
    <td>
      <form method="get" action="/user/radio/global-music/download/<?=$item->id?>" style="display:flex;gap:4px;align-items:center">
        <select name="playlist_id" class="inp" required>
          <option value="">-- Select playlist --</option>
          <?php foreach ($userPlaylists as $up): ?>
          <option value="<?=$up->id?>"><?=htmlspecialchars($up->name)?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-p" style="padding:4px 10px;font-size:10px">Download</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>
