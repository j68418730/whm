<style>
.crd{background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:16px;margin-bottom:12px}
.inp{padding:6px 8px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;width:100%;box-sizing:border-box}
.inp:focus{border-color:rgba(0,140,255,.3)}
label{display:block;font-size:11px;color:#94a3b8;margin-bottom:4px}
.btn{padding:8px 20px;border-radius:6px;font-size:12px;border:none;cursor:pointer;text-decoration:none;display:inline-block}
.btn-p{background:rgba(0,140,255,.2);color:#0A84FF}
.btn-p:hover{background:rgba(0,140,255,.3)}
</style>
<div style="max-width:500px;margin:0 auto">
<div class="crd"><h2 style="font-size:16px;font-weight:600;color:#e0e0e0;margin:0 0 12px">Create Global Playlist</h2>
<form method="post" action="/admin/radio/global-playlists/store">
  <div style="margin-bottom:10px"><label>Name</label><input class="inp" name="name" required></div>
  <div style="margin-bottom:10px"><label>Description</label><textarea class="inp" name="description" rows="3"></textarea></div>
  <button class="btn btn-p">Create</button>
  <a href="/admin/radio/global-playlists" class="btn" style="background:rgba(255,255,255,.06);color:#94a3b8;margin-left:6px">Cancel</a>
</form>
</div></div>
