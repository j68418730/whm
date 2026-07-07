<?php
$tab = $_GET['tab'] ?? 'overview';
$stationId = $station->id ?? 0;
$realId = $station->streaming_id ?? $stationId;
$isIces = ($station->server_type ?? 'icecast') === 'icecast';
$streamHost = 'planet-hosts.com';
$streamProto = 'https';
$mount = $station->mount ?? '/live';
if (!str_starts_with($mount, '/')) $mount = "/{$mount}";
$listenUrl = $streamProto . '://' . $streamHost . ':2083/radio/stream-proxy.php?stream=' . $station->streaming_id;
$directUrl = $isIces ? "http://{$streamHost}:{$station->port}{$mount}" : "http://{$streamHost}:{$station->port}/;stream.nsv";
?>
<style>
.nav-pills{display:flex;gap:2px;flex-wrap:wrap;margin-bottom:14px;background:rgba(8,16,28,.6);border-radius:8px;padding:3px;max-height:200px;overflow-y:auto}
.nav-pills a{padding:6px 12px;border-radius:6px;font-size:11px;text-decoration:none;color:#94a3b8;transition:.1s;white-space:nowrap}
.nav-pills a:hover{color:#e0e0e0;background:rgba(255,255,255,.04)}
.nav-pills a.active{color:#fff;background:rgba(0,140,255,.2)}
.tab{display:none}
.tab.active{display:block}
.top-bar{display:flex;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap}
.top-bar .page-title{font-size:20px;font-weight:700;color:#e0e0e0}
.top-bar .breadcrumb{font-size:11px;color:#64748b;display:flex;align-items:center;gap:4px;margin-bottom:2px}
.top-bar .breadcrumb a{color:#64748b;text-decoration:none}
.top-bar .breadcrumb a:hover{color:#0A84FF}
.nowplaying-bar{display:flex;align-items:center;gap:14px;padding:14px;background:linear-gradient(135deg,rgba(0,140,255,.06),rgba(168,85,247,.04));border:1px solid rgba(0,191,255,.1);border-radius:12px;margin-bottom:16px}
.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:12px}
.stat-box{text-align:center;padding:14px;background:rgba(8,16,28,.6);border-radius:8px}
.stat-box .num{font-size:22px;font-weight:700}
.stat-box .lbl{font-size:10px;color:#64748b;margin-top:2px}
.upload-zone{border:2px dashed rgba(0,140,255,.2);border-radius:10px;padding:30px;text-align:center;color:#64748b;font-size:12px;cursor:pointer;transition:.15s;margin-bottom:10px}
.upload-zone:hover{border-color:rgba(0,140,255,.4);background:rgba(0,140,255,.03);color:#94a3b8}
.file-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px}
.color-picker-wrap{display:flex;align-items:center;gap:8px;margin-bottom:6px}
.color-picker-wrap input[type=color]{width:36px;height:36px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);cursor:pointer;padding:2px}
.color-picker-wrap .hex{font-size:11px;color:#94a3b8;font-family:monospace}
.inp{padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;width:100%;box-sizing:border-box}
.inp:focus{border-color:rgba(0,140,255,.3)}
.inp-sm{padding:4px 6px;font-size:10px}
select.inp{color:#e0e0e0;cursor:pointer}
select.inp option{background:#0a0e1a;color:#e0e0e0}
.station-selector{display:flex;align-items:center;gap:8px;margin-bottom:14px;padding:10px 14px;background:rgba(8,16,28,.6);border-radius:8px;font-size:12px}
.station-selector select{padding:4px 8px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;min-width:200px}
.card{background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:16px;margin-bottom:12px}
.card h3{font-size:13px;font-weight:600;color:#e0e0e0;margin:0 0 12px 0}
.card .hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;margin-bottom:12px}
.stat-card{padding:14px;background:rgba(0,0,0,.3);border-radius:8px;text-align:center}
.stat-card .sv{font-size:20px;font-weight:700;color:#e0e0e0}
.stat-card .sl{font-size:10px;color:#64748b;margin-top:2px}
.btn{padding:6px 14px;border-radius:6px;font-size:11px;font-weight:500;border:none;cursor:pointer;transition:.1s;text-decoration:none;display:inline-block}
.btn-sm{padding:4px 10px;font-size:10px}
.btn-primary{background:rgba(0,140,255,.2);color:#0A84FF}
.btn-primary:hover{background:rgba(0,140,255,.3)}
.btn-success{background:rgba(0,200,83,.15);color:#00C853}
.btn-success:hover{background:rgba(0,200,83,.25)}
.btn-danger{background:rgba(255,68,68,.15);color:#ff4444}
.btn-danger:hover{background:rgba(255,68,68,.25)}
.btn-warning{background:rgba(255,193,7,.15);color:#ffc107}
.btn-warning:hover{background:rgba(255,193,7,.25)}
.btn-secondary{background:rgba(255,255,255,.06);color:#94a3b8}
.btn-secondary:hover{background:rgba(255,255,255,.1)}
.status-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:9px;font-weight:600}
.status-running{background:rgba(0,200,83,.15);color:#00C853}
.status-stopped{background:rgba(255,68,68,.12);color:#ff4444}
.status-starting{background:rgba(255,193,7,.15);color:#ffc107}
.msg{padding:8px 12px;border-radius:6px;font-size:11px;margin-bottom:10px;display:none}
.msg-success{display:block;background:rgba(0,200,83,.1);color:#00C853;border:1px solid rgba(0,200,83,.15)}
.msg-error{display:block;background:rgba(255,68,68,.1);color:#ff4444;border:1px solid rgba(255,68,68,.15)}
.empty-state{padding:30px;text-align:center;color:#64748b;font-size:12px}
.progress-bar{height:4px;border-radius:2px;background:rgba(255,255,255,.06);overflow:hidden;margin-top:6px}
.progress-bar .fill{height:100%;border-radius:2px;background:linear-gradient(90deg,#0A84FF,#5856D6);transition:width .3s}
table{width:100%;border-collapse:collapse;font-size:11px}
th{padding:8px 6px;text-align:left;font-weight:600;color:#64748b;border-bottom:1px solid rgba(255,255,255,.06)}
td{padding:8px 6px;border-bottom:1px solid rgba(255,255,255,.04);color:#c0c0c0}
tr:hover td{background:rgba(255,255,255,.02)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px}
.form-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:8px}
.form-group{margin-bottom:10px}
.form-group label{display:block;font-size:10px;color:#64748b;margin-bottom:3px;font-weight:500}
.actions{display:flex;gap:4px;flex-wrap:wrap}
</style>
<div class="top-bar"><div><div class="breadcrumb"><a href="/user">Dashboard</a> &raquo; <span>Radio</span></div><div class="page-title">Radio Dashboard</div></div><?php if (empty($stations)): ?><a href="/user/radio/setup" class="btn btn-primary">Create Station</a><?php endif; ?></div>
<?php if (isset($_SESSION['success'])): ?><div class="msg msg-success"><?=$_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error'])): ?><div class="msg msg-error"><?=$_SESSION['error']; unset($_SESSION['error']); ?></div><?php endif; ?>
<?php if (!empty($stations) && $station): ?>
<div class="station-selector">
  <span>Station:</span>
  <select onchange="window.location.href='/user/radio?station_id='+this.value+'&tab='+getTab()">
    <?php foreach ($stations as $st): ?>
    <option value="<?=$st->id?>" <?=$st->id==$stationId?'selected':''?>><?=htmlspecialchars($st->name)?> (<?=$st->server_type?> :<?=$st->port?>)</option>
    <?php endforeach; ?>
  </select>
  <?php $sc = ($station->status??'stopped')==='running'?'status-running':(($station->status??'stopped')==='starting'?'status-starting':'status-stopped'); $sl = ucfirst($station->status??'Stopped'); ?>
  <span class="status-badge <?=$sc?>"><?=$sl?></span>
  <div style="margin-left:auto;display:flex;gap:4px">
    <a href="/user/radio/start/<?=$stationId?>" class="btn btn-sm btn-success">Start</a>
    <a href="/user/radio/stop/<?=$stationId?>" class="btn btn-sm btn-danger">Stop</a>
    <a href="/user/radio/restart/<?=$stationId?>" class="btn btn-sm btn-warning">Restart</a>
    <a href="<?=$listenUrl?>" target="_blank" class="btn btn-sm btn-secondary">Listen</a>
  </div>
</div>
<div class="nowplaying-bar">
  <div style="flex:1"><div style="font-size:10px;color:#64748b;margin-bottom:2px">Now Playing</div><div style="font-size:14px;font-weight:600;color:#e0e0e0"><?=htmlspecialchars($station->current_song ?: 'No song playing')?></div></div>
  <div style="text-align:center"><div style="font-size:10px;color:#64748b">Listeners</div><div style="font-size:18px;font-weight:700;color:#0A84FF"><?=(int)($station->listener_count??0)?></div></div>
  <div style="text-align:center"><div style="font-size:10px;color:#64748b">Peak</div><div style="font-size:18px;font-weight:700;color:#5856D6"><?=(int)($station->listener_peak??0)?></div></div>
  <div style="text-align:center"><div style="font-size:10px;color:#64748b">Bitrate</div><div style="font-size:18px;font-weight:700;color:#e0e0e0"><?=$station->bitrate??128?> kbps</div></div>
  <div style="text-align:center"><div style="font-size:10px;color:#64748b">Format</div><div style="font-size:18px;font-weight:700;color:#e0e0e0"><?=strtoupper($station->format??'mp3')?></div></div>
  <div style="text-align:center">
    <div style="font-size:10px;color:#64748b">Disk</div>
    <div style="font-size:14px;font-weight:700;color:#e0e0e0"><?=$diskUsedFormatted?> / <?=$diskTotalFormatted?></div>
    <?php $pct = $diskTotal > 0 ? min(100, round($diskUsed/$diskTotal*100)) : 0; ?>
    <div class="progress-bar" style="width:100px"><div class="fill" style="width:<?=$pct?>%"></div></div>
  </div>
</div>
<div class="nav-pills">
  <a href="?station_id=<?=$stationId?>&tab=overview" class="<?=$tab==='overview'?'active':''?>">Overview</a>
  <a href="?station_id=<?=$stationId?>&tab=djs" class="<?=$tab==='djs'?'active':''?>">DJs</a>
  <a href="?station_id=<?=$stationId?>&tab=requests" class="<?=$tab==='requests'?'active':''?>">Requests</a>
  <a href="?station_id=<?=$stationId?>&tab=schedule" class="<?=$tab==='schedule'?'active':''?>">Schedule</a>
  <a href="?station_id=<?=$stationId?>&tab=playlists" class="<?=$tab==='playlists'?'active':''?>">Playlists</a>
  <a href="?station_id=<?=$stationId?>&tab=media" class="<?=$tab==='media'?'active':''?>">Media</a>
  <a href="?station_id=<?=$stationId?>&tab=autodj" class="<?=$tab==='autodj'?'active':''?>">AutoDJ</a>
  <a href="?station_id=<?=$stationId?>&tab=settings" class="<?=$tab==='settings'?'active':''?>">Settings</a>
  <a href="?station_id=<?=$stationId?>&tab=branding" class="<?=$tab==='branding'?'active':''?>">Branding</a>
  <a href="?station_id=<?=$stationId?>&tab=mounts" class="<?=$tab==='mounts'?'active':''?>">Mounts</a>
  <a href="?station_id=<?=$stationId?>&tab=song_history" class="<?=$tab==='song_history'?'active':''?>">Song History</a>
  <a href="?station_id=<?=$stationId?>&tab=applications" class="<?=$tab==='applications'?'active':''?>" style="color:#facc15">📋 Applications</a>
  <a href="?station_id=<?=$stationId?>&tab=backups" class="<?=$tab==='backups'?'active':''?>">Backups</a>
  <a href="/user/radio/widgets?station_id=<?=$stationId?>" style="color:#a855f7;font-weight:700">🎨 Widgets</a>
  <a href="/dj_panel.php" target="_blank" style="color:#facc15;font-weight:600">🎧 DJ Panel</a>
</div>
<div class="tab <?=$tab==='overview'?'active':''?>">
  <div style="background:linear-gradient(135deg,rgba(0,140,255,.06),rgba(168,85,247,.03));border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:16px;margin-bottom:16px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <div style="flex-shrink:0;width:60px;height:60px;border-radius:12px;background:linear-gradient(135deg,rgba(0,140,255,.15),rgba(168,85,247,.1));display:flex;align-items:center;justify-content:center;font-size:28px;border:1px solid rgba(0,191,255,.1)">🎵</div>
    <div style="flex:1;min-width:150px">
      <div style="font-size:14px;font-weight:600;color:#e0e0e0;margin-bottom:2px">Now Playing</div>
      <div style="font-size:12px;color:#94a3b8" id="ov-song"><?=$autodjCfg->autodj_enabled?'Loading...':'AutoDJ Stopped'?></div>
    </div>
    <div style="flex-shrink:0;min-width:200px">
      <audio src="<?=$listenUrl?>" preload="none" controls style="width:100%;height:36px;border-radius:8px"></audio>
    </div>
    <div style="display:flex;gap:6px;flex-shrink:0">
      <a href="https://planet-hosts.com:2083/radio/embed.php?stream=<?=$station->streaming_id?>" target="_blank" class="btn btn-sm btn-primary" style="font-size:10px;padding:6px 10px">Player</a>
      <a href="<?=$isIces ? 'http://planet-hosts.com:'.$station->port.$station->mount : 'http://planet-hosts.com:'.$station->port.'/;stream.nsv'?>" target="_blank" class="btn btn-sm btn-secondary" style="font-size:10px;padding:6px 10px">Direct</a>
  </div>
</div>
<div class="tab <?=$tab==='station_info'?'active':''?>">
  <div class="card"><div class="hdr"><h3>Station Information</h3></div>
  <table>
    <tr><td style="color:#64748b;padding:6px 0">Station Name</td><td style="padding:6px 0"><?=htmlspecialchars($station->name??'')?></td></tr>
    <tr><td style="color:#64748b;padding:6px 0">Server Type</td><td style="padding:6px 0"><?=strtoupper($station->server_type??'ICECAST')?></td></tr>
    <tr><td style="color:#64748b;padding:6px 0">Port</td><td style="padding:6px 0"><?=$station->port?></td></tr>
    <tr><td style="color:#64748b;padding:6px 0">Mount Point</td><td style="padding:6px 0"><?=htmlspecialchars($station->mount??'/stream')?></td></tr>
    <tr><td style="color:#64748b;padding:6px 0">Bitrate</td><td style="padding:6px 0"><?=$station->bitrate??128?> kbps</td></tr>
    <tr><td style="color:#64748b;padding:6px 0">Status</td><td style="padding:6px 0;color:<?=$station->status==='running'?'#4ade80':'#f87171'?>"><?=$station->status??'stopped'?></td></tr>
  </table>
  </div>
  <div class="card"><h3>Source Password</h3>
  <div style="display:flex;gap:8px;align-items:center">
    <input class="inp inp-sm" id="src-pass" value="<?=htmlspecialchars($station->password??'')?>" readonly style="flex:1;font-family:monospace;font-size:13px;color:#4ade80">
    <button class="btn btn-sm btn-sec" onclick="var p=document.getElementById('src-pass');p.select();navigator.clipboard.writeText(p.value);this.textContent='Copied!';setTimeout(function(){this.textContent='Copy'}.bind(this),2000)">Copy</button>
  </div>
  </div>
  <div class="card"><h3>Admin Password</h3>
  <div style="display:flex;gap:8px;align-items:center">
    <input class="inp inp-sm" id="adm-pass" value="<?=htmlspecialchars($station->admin_password??'')?>" readonly style="flex:1;font-family:monospace;font-size:13px;color:#facc15">
    <button class="btn btn-sm btn-sec" onclick="var p=document.getElementById('adm-pass');p.select();navigator.clipboard.writeText(p.value);this.textContent='Copied!';setTimeout(function(){this.textContent='Copy'}.bind(this),2000)">Copy</button>
  </div>
  </div>
  <div class="card"><h3>Change Passwords</h3>
  <form method="post" action="/user/radio/update-passwords">
    <input type="hidden" name="station_id" value="<?=$stationId?>">
    <div class="form-row">
      <div class="form-group"><label>New Source Password</label><input class="inp inp-sm" name="password" placeholder="Leave blank to keep current"></div>
      <div class="form-group"><label>New Admin Password</label><input class="inp inp-sm" name="admin_password" placeholder="Leave blank to keep current"></div>
    </div>
    <button class="btn btn-sm btn-primary">Update Passwords</button>
  </form>
  </div>
</div>
  <script>
  setInterval(function(){
    var x=new XMLHttpRequest();
    x.open('GET','/user/radio/autodj/dashboard?station_id=<?=$stationId?>',true);
    x.onload=function(){try{var d=JSON.parse(x.responseText);if(d.config){document.getElementById('ov-song').textContent=d.config.autodj_enabled?'AutoDJ Active':'AutoDJ Stopped'}}catch(e){}};
    x.send();
  },10000);
  </script>
  <div class="stat-grid">
    <div class="stat-card"><div class="sv"><?=(int)($station->listener_count??0)?></div><div class="sl">Current Listeners</div></div>
    <div class="stat-card"><div class="sv"><?=(int)($station->listener_peak??0)?></div><div class="sl">Peak Listeners</div></div>
    <div class="stat-card"><div class="sv"><?=$station->bitrate??128?></div><div class="sl">Bitrate (kbps)</div></div>
    <div class="stat-card"><div class="sv"><?=$diskUsedFormatted?></div><div class="sl">Disk Used</div></div>
    <div class="stat-card"><div class="sv"><?=$diskTotalFormatted?></div><div class="sl">Disk Limit</div></div>
    <div class="stat-card"><div class="sv"><?=count($djs)?></div><div class="sl">DJs</div></div>
    <div class="stat-card"><div class="sv"><?=count($playlists)?></div><div class="sl">Playlists</div></div>
    <div class="stat-card"><div class="sv"><?=count($songs)?></div><div class="sl">Recent Songs</div></div>
    <div class="stat-card"><div class="sv"><?=count($schedule)?></div><div class="sl">Shows</div></div>
  </div>
  <div class="card"><h3>Quick Actions</h3><div style="display:flex;gap:6px;flex-wrap:wrap">
    <a href="?station_id=<?=$stationId?>&tab=playlists" class="btn btn-sm btn-primary">Manage Playlists</a>
    <a href="?station_id=<?=$stationId?>&tab=branding" class="btn btn-sm btn-primary">Branding</a>
    <a href="?station_id=<?=$stationId?>&tab=autodj" class="btn btn-sm btn-primary">AutoDJ</a>
    <a href="?station_id=<?=$stationId?>&tab=backups" class="btn btn-sm btn-secondary">Backups</a>
    <a href="https://planet-hosts.com:2083/radio/embed.php?stream=<?=$station->streaming_id?>" target="_blank" class="btn btn-sm btn-secondary">Listen</a>
  </div></div>
  <?php if (!empty($songs)): ?>
  <div class="card"><div class="hdr"><h3>Recently Played</h3></div><table><tr><th>Title</th><th>Artist</th><th>Played At</th></tr>
    <?php foreach (array_slice($songs,0,10) as $sh): ?>
    <tr><td><?=htmlspecialchars($sh->title??'Unknown')?></td><td><?=htmlspecialchars($sh->artist??'Unknown')?></td><td><?=htmlspecialchars($sh->played_at??'')?></td></tr>
    <?php endforeach; ?>
  </table></div>
  <?php endif; ?>
</div>
<div class="tab <?=$tab==='djs'?'active':''?>">
  <div class="card"><div class="hdr"><h3>DJs</h3></div>
  <table><tr><th>Username</th><th>Name</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr>
    <?php if (empty($djs)): ?><tr><td colspan="5" class="empty-state">No DJs yet</td></tr>
    <?php else: ?>
    <?php foreach ($djs as $dj): ?>
    <tr>
      <td><?=htmlspecialchars($dj->username??'')?></td>
      <td><?=htmlspecialchars($dj->name??$dj->username??'')?></td>
      <td><?=htmlspecialchars($dj->role??'dj')?></td>
      <td><span class="status-badge <?=$dj->status==='active'?'status-running':'status-stopped'?>"><?=$dj->status??'unknown'?></span></td>
      <td><?=htmlspecialchars($dj->last_login??'Never')?></td>
      <td class="actions">
        <a href="?station_id=<?=$stationId?>&tab=djs&edit_dj=<?=$dj->id?>" class="btn btn-sm btn-primary">Edit</a>
        <a href="/user/radio/dj/toggle/<?=$dj->id?>" class="btn btn-sm btn-warning"><?=$dj->status==='active'?'Suspend':'Activate'?></a>
        <a href="/user/radio/dj/delete/<?=$dj->id?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </table></div>
  <div class="card"><h3>Add DJ</h3>
  <form method="post" action="/user/radio/dj/create">
    <input type="hidden" name="station_id" value="<?=$stationId?>">
    <div class="form-row"><div class="form-group"><label>Username</label><input class="inp inp-sm" name="username" required></div><div class="form-group"><label>Password</label><input class="inp inp-sm" type="password" name="password" required></div></div>
    <div class="form-row"><div class="form-group"><label>Display Name</label><input class="inp inp-sm" name="name"></div><div class="form-group"><label>Email</label><input class="inp inp-sm" type="email" name="email"></div></div>
    <div class="form-group"><label>Bio</label><textarea class="inp inp-sm" name="bio" rows="2"></textarea></div>
    <div class="form-group"><label>Role</label><div style="display:flex;gap:12px"><label style="display:flex;align-items:center;gap:4px;font-size:11px;color:#c0c0c0"><input type="radio" name="role" value="dj" checked> DJ</label><label style="display:flex;align-items:center;gap:4px;font-size:11px;color:#c0c0c0"><input type="radio" name="role" value="mod"> Mod</label></div></div>
    <button class="btn btn-sm btn-primary">Add DJ</button>
  </form></div>
  <div class="card"><h3>DJ Takeover</h3>
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <span style="font-size:11px;color:#64748b">When a DJ connects via SAM Broadcaster or other software, click below to stop AutoDJ and allow the DJ to take over:</span>
    <form method="post" action="/user/radio/kick-source" style="display:inline">
      <input type="hidden" name="station_id" value="<?=$stationId?>">
      <button class="btn btn-sm btn-warning">🎤 Stop AutoDJ for DJ</button>
    </form>
  </div>
  </div>
  <div class="card"><h3>DJ Login Link</h3>
  <p style="font-size:11px;color:#64748b;margin-bottom:8px">Share this link with your DJs so they can access the DJ Panel with their credentials:</p>
  <div style="display:flex;gap:8px;align-items:center">
    <input class="inp inp-sm" value="https://planet-hosts.com:2083/dj_panel.php" readonly style="flex:1;font-family:monospace;font-size:12px;color:#4ade80">
    <button class="btn btn-sm btn-primary" onclick="var i=this.previousElementSibling;i.select();navigator.clipboard.writeText(i.value);this.textContent='Copied!';setTimeout(function(){this.textContent='Copy'}.bind(this),2000)">Copy</button>
  </div>
  </div>
  <?php $editDjId = (int)($_GET['edit_dj']??0); $editDj = null; foreach($djs as $d){if($d->id==$editDjId){$editDj=$d;break;}} ?>
  <?php if ($editDj): ?>
  <div class="card"><h3>Edit DJ: <?=htmlspecialchars($editDj->name?:$editDj->username)?></h3>
  <form method="post" action="/user/radio/dj/update/<?=$editDj->id?>">
    <input type="hidden" name="station_id" value="<?=$stationId?>">
    <div class="form-row"><div class="form-group"><label>Username</label><input class="inp inp-sm" name="username" value="<?=htmlspecialchars($editDj->username??'')?>"></div><div class="form-group"><label>New Password</label><input class="inp inp-sm" type="password" name="password" placeholder="Leave blank to keep current"></div></div>
    <div class="form-row"><div class="form-group"><label>Display Name</label><input class="inp inp-sm" name="name" value="<?=htmlspecialchars($editDj->name??'')?>"></div><div class="form-group"><label>Email</label><input class="inp inp-sm" type="email" name="email" value="<?=htmlspecialchars($editDj->email??'')?>"></div></div>
    <div class="form-group"><label>Bio</label><textarea class="inp inp-sm" name="bio" rows="2"><?=htmlspecialchars($editDj->bio??'')?></textarea></div>
    <div class="form-group"><label>Role</label><div style="display:flex;gap:12px"><label style="display:flex;align-items:center;gap:4px;font-size:11px;color:#c0c0c0"><input type="radio" name="role" value="dj" <?=($editDj->role??'dj')==='dj'?'checked':''?>> DJ</label><label style="display:flex;align-items:center;gap:4px;font-size:11px;color:#c0c0c0"><input type="radio" name="role" value="mod" <?=($editDj->role??'')==='mod'?'checked':''?>> Mod</label></div></div>
    <button class="btn btn-sm btn-primary">Save Changes</button>
    <a href="?station_id=<?=$stationId?>&tab=djs" class="btn btn-sm btn-secondary">Cancel</a>
  </form></div>
  <?php endif; ?>
</div>
<div class="tab <?=$tab==='requests'?'active':''?>">
  <div class="card"><div class="hdr"><h3>Song Requests</h3><a href="/user/radio/requests/toggle/<?=$stationId?>" class="btn btn-sm btn-secondary">Requests: <?=$station->requests_enabled?'ON':'OFF'?></a></div>
  <table><tr><th>Song</th><th>Artist</th><th>Requester</th><th>Date</th><th>Status</th><th>Actions</th></tr>
    <?php if (empty($requests)): ?><tr><td colspan="6" class="empty-state">No requests</td></tr>
    <?php else: ?>
    <?php foreach ($requests as $r): ?>
    <tr>
      <td><?=htmlspecialchars($r->song??$r->title??'')?></td><td><?=htmlspecialchars($r->artist??'')?></td>
      <td><?=htmlspecialchars($r->requester_name??$r->name??'Anonymous')?></td><td><?=htmlspecialchars($r->created_at??'')?></td>
      <td><span class="status-badge <?=$r->status==='approved'?'status-running':($r->status==='rejected'?'status-stopped':'status-starting')?>"><?=$r->status??'pending'?></span></td>
      <td class="actions"><?php if (($r->status??'pending')==='pending'): ?><a href="/user/radio/request/approve/<?=$r->id?>" class="btn btn-sm btn-success">Approve</a><a href="/user/radio/request/reject/<?=$r->id?>" class="btn btn-sm btn-danger">Reject</a><?php endif; ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </table></div>
</div>
<div class="tab <?=$tab==='schedule'?'active':''?>">
  <div class="card"><h3>Schedule</h3>
  <table><tr><th>Show</th><th>Day</th><th>Start</th><th>End</th><th>DJ</th><th>Actions</th></tr>
    <?php if (empty($schedule)): ?><tr><td colspan="6" class="empty-state">No shows scheduled</td></tr>
    <?php else: ?>
    <?php $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']; ?>
    <?php foreach ($schedule as $sh): ?>
    <tr>
      <td><?=htmlspecialchars($sh->show_name??'Untitled')?></td>
      <td><?=$days[$sh->day_of_week]??$sh->day_of_week?></td>
      <td><?=htmlspecialchars($sh->start_time??'')?></td><td><?=htmlspecialchars($sh->end_time??'')?></td>
      <td><?php $dn=''; foreach($djs as $d){if(($d->id??0)==($sh->dj_id??0)){$dn=$d->name??$d->username??'';break;}} echo htmlspecialchars($dn?:'Auto'); ?></td>
      <td class="actions"><a href="/user/radio/schedule/delete/<?=$sh->id?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </table></div>
  <div class="card"><h3>Add Show</h3>
  <form method="post" action="/user/radio/schedule/add">
    <input type="hidden" name="station_id" value="<?=$stationId?>">
    <div class="form-row"><div class="form-group"><label>Show Name</label><input class="inp inp-sm" name="show_name" required></div>
    <div class="form-group"><label>Day</label><select class="inp inp-sm" name="day_of_week"><option value="0">Sunday</option><option value="1">Monday</option><option value="2">Tuesday</option><option value="3">Wednesday</option><option value="4">Thursday</option><option value="5">Friday</option><option value="6">Saturday</option></select></div></div>
    <div class="form-row"><div class="form-group"><label>Start</label><input class="inp inp-sm" type="time" name="start_time" required></div><div class="form-group"><label>End</label><input class="inp inp-sm" type="time" name="end_time" required></div></div>
    <div class="form-row">
      <div class="form-group"><label>DJ</label><select class="inp inp-sm" name="dj_id"><option value="">Auto</option><?php foreach($djs as $d): ?><option value="<?=$d->id?>"><?=htmlspecialchars($d->name ?: $d->username)?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label>DJ Name</label><input class="inp inp-sm" name="dj_name" placeholder="DJ display name"></div>
    </div>
    <button class="btn btn-sm btn-primary">Add Show</button>
  </form></div>
</div>
<div class="tab <?=$tab==='playlists'?'active':''?>">
  <div class="card"><div class="hdr"><h3>Playlists</h3></div>
  <?php if (empty($playlists)): ?><div class="empty-state">No playlists yet.</div>
  <?php else: ?>
  <table><tr><th>Name</th><th>Type</th><th>Actions</th></tr>
    <?php foreach ($playlists as $p): ?>
    <tr>
      <td><a href="?station_id=<?=$stationId?>&tab=playlists&playlist_id=<?=$p->id?>" style="color:#0A84FF;text-decoration:none"><?=htmlspecialchars($p->name)?></a></td>
      <td><?=htmlspecialchars($p->type??'default')?></td>
      <td class="actions"><a href="?station_id=<?=$stationId?>&tab=playlists&playlist_id=<?=$p->id?>" class="btn btn-sm btn-primary">Manage</a><a href="/user/radio/playlist/delete/<?=$p->id?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
  </div>
  <?php $selPlId = (int)($_GET['playlist_id']??0); $selPl = null; foreach($playlists as $p){if(($p->id??0)==$selPlId){$selPl=$p;break;}} ?>
  <?php if ($selPl): ?>
  <div class="card"><div class="hdr"><h3>Playlist: <?=htmlspecialchars($selPl->name)?></h3><span style="font-size:10px;color:#64748b"><?=count($playlistItems)?> songs</span></div>
  <table><tr><th>Title</th><th>Artist</th><th>File</th><th>Duration</th><th>Order</th><th>Actions</th></tr>
    <?php if (empty($playlistItems)): ?><tr><td colspan="6" class="empty-state">No songs</td></tr>
    <?php else: ?>
    <?php foreach ($playlistItems as $item): ?>
    <tr>
      <td><?=htmlspecialchars($item->title??'')?></td><td><?=htmlspecialchars($item->artist??'')?></td>
      <td style="font-size:10px;color:#64748b"><?=htmlspecialchars(basename($item->file_path??''))?></td>
      <td><?=$item->duration?gmdate('i:s',$item->duration):'-'?></td><td><?=$item->position??0?></td>
      <td class="actions"><a href="/user/radio/playlist/remove-song/<?=$item->id?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove?')">Remove</a></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </table>
  <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap">
    <a href="?station_id=<?=$stationId?>&tab=media&playlist_id=<?=$selPlId?>" class="btn btn-sm btn-primary">Upload Media</a>
    <a href="/user/radio/autodj/restart/<?=$stationId?>" class="btn btn-sm btn-warning">⟳ Reload in AutoDJ</a>
  </div>
  </div>
  <?php endif; ?>
  <div class="card"><h3>Create Playlist</h3>
  <form method="post" action="/user/radio/playlist/create">
    <input type="hidden" name="station_id" value="<?=$stationId?>">
    <div class="form-row"><div class="form-group"><label>Name</label><input class="inp inp-sm" name="name" required></div><div class="form-group"><label>Type</label><select class="inp inp-sm" name="type"><option value="default">Default</option><option value="rotation">Rotation</option><option value="request">Request</option></select></div></div>
    <div class="form-group"><label>Description</label><input class="inp inp-sm" name="description"></div>
    <button class="btn btn-sm btn-primary">Create</button>
  </form></div>
</div>
<div class="tab <?=$tab==='media'?'active':''?>">
  <div class="card"><div class="hdr"><h3>Media Library</h3></div>
  <?php $mPlId = isset($_GET['playlist_id'])?(int)$_GET['playlist_id']:null; $selPlName = ''; foreach($playlists as $p){if(($p->id??0)==$mPlId){$selPlName=$p->name;break;}} ?>
  <div class="form-row" style="margin-bottom:12px">
    <div class="form-group" style="flex:1">
      <select class="inp inp-sm" onchange="window.location.href='?station_id=<?=$stationId?>&tab=media&playlist_id='+this.value">
        <option value="">-- All Music --</option>
        <?php foreach($playlists as $p): ?>
        <option value="<?=$p->id?>" <?=$mPlId==$p->id?'selected':''?>><?=htmlspecialchars($p->name)?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if($mPlId && $selPlName): ?>
    <div style="font-size:12px;color:#0A84FF;padding:6px 0 0 8px">Playlist: <?=htmlspecialchars($selPlName)?></div>
    <?php endif; ?>
  </div>
  <div id="uploadZone" class="upload-zone" style="cursor:pointer;border:2px dashed rgba(0,191,255,.2);border-radius:10px;padding:30px;text-align:center;transition:.3s;background:rgba(0,0,0,.2)" ontouchend="document.getElementById('media-input').click()">Drop files here or click to browse (mp3, aac, ogg, flac, wav, m4a)</div>
  <input id="media-input" type="file" name="files[]" multiple accept=".mp3,.aac,.ogg,.flac,.wav,.m4a" style="display:none">
  <div id="uploadQueue" style="margin-top:8px;font-size:11px;color:#94a3b8"></div>
  <div id="uploadProgress" style="display:none;margin-top:8px;background:rgba(0,0,0,.3);border-radius:6px;overflow:hidden;height:6px"><div id="uploadProgressBar" style="width:0;height:100%;background:linear-gradient(90deg,#008cff,#3bb8ff);transition:width .3s"></div></div>
  <div id="uploadStatus" style="margin-top:6px;font-size:10px;color:#64748b;text-align:center"></div>
  <button id="uploadBtn" class="btn btn-sm btn-primary" style="margin-top:8px;display:none" onclick="startUpload()">Upload <span id="uploadCount"></span></button>
  <?php if (empty($mediaFiles)): ?><div class="empty-state" style="margin-top:10px">No media files<?=$mPlId?' in this playlist':''?></div>
  <?php else: ?>
  <?php $plDirMap = []; foreach($playlists as $p) $plDirMap['playlist_'.$p->id] = $p->name; ?>
  <div class="file-grid" style="margin-top:10px">
    <?php foreach ($mediaFiles as $f):
      $isDir = is_dir('/home/'.$station->username.'/radio/musicdatabase'.($mPlId?'/playlist_'.$mPlId:'').'/'.$f);
      $plName = $plDirMap[$f] ?? null;
      $displayName = $plName ? htmlspecialchars($plName) : htmlspecialchars($f);
      $plIdFromDir = $plName ? (int)str_replace('playlist_','',$f) : 0;
    ?>
    <?php if ($isDir && $plIdFromDir): ?>
    <a href="?station_id=<?=$stationId?>&tab=media&playlist_id=<?=$plIdFromDir?>" style="text-decoration:none">
    <?php endif; ?>
    <div style="padding:10px;background:rgba(0,0,0,.3);border-radius:8px;text-align:center;<?=$isDir?'cursor:pointer;border:1px solid rgba(0,140,255,.15)':''?>">
      <div style="font-size:28px;margin-bottom:4px;opacity:.5"><?=$isDir?'&#128193;':'&#9835;'?></div>
      <div style="font-size:10px;color:<?=$plName?'#0A84FF':'#c0c0c0'?>;word-break:break-all;font-weight:<?=$plName?'600':'400'?>"><?=$displayName?></div>
      <div style="margin-top:6px;font-size:10px;color:#64748b"><?=$isDir?'Playlist folder':round(filesize('/home/'.$station->username.'/radio/musicdatabase'.($mPlId?'/playlist_'.$mPlId:'').'/'.$f)/1024,1).' KB'?></div>
      <?php if (!$isDir): ?>
      <div style="margin-top:6px"><a href="/user/radio/media/delete?file=<?=urlencode($f)?>&playlist_id=<?=$mPlId?>&station_id=<?=$stationId?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a></div>
      <?php endif; ?>
    </div>
    <?php if ($isDir && $plIdFromDir): ?>
    </a>
    <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  </div>
</div>
<div class="tab <?=$tab==='autodj'?'active':''?>">
<?php if (!$autodjCfg || !$autodjCfg->wizard_completed): ?>
<div class="card" style="text-align:center;padding:40px">
<div style="font-size:50px;margin-bottom:10px">&#9881;</div>
<div style="font-size:15px;color:#c0c0c0;margin-bottom:4px">AutoDJ Not Configured</div>
<div style="font-size:11px;color:#64748b;margin-bottom:14px">Complete the setup wizard to configure AutoDJ settings, playlists, and rotation rules</div>
<a href="/user/radio/autodj/setup?station_id=<?=$stationId?>" class="btn btn-primary">Start Setup Wizard</a>
</div>
<?php else: ?>
<?php $adTab = $_GET['adtab'] ?? 'overview'; $ac = $autodjCfg; ?>
<div class="nowplaying-bar">
<div style="flex:1"><div style="font-size:10px;color:#64748b;margin-bottom:2px">AutoDJ</div><div style="font-size:14px;font-weight:600;color:#e0e0e0"><?=$ac->autodj_enabled?'Running':'Stopped'?></div></div>
<div style="text-align:center"><div style="font-size:10px;color:#64748b">Mode</div><div style="font-size:14px;font-weight:700;color:#0A84FF"><?=ucfirst($ac->playlist_mode)?></div></div>
<div style="text-align:center"><div style="font-size:10px;color:#64748b">Crossfade</div><div style="font-size:14px;font-weight:700;color:#5856D6"><?=$ac->crossfade_time?>s</div></div>
<div style="text-align:center"><div style="font-size:10px;color:#64748b">Bitrate</div><div style="font-size:14px;font-weight:700;color:#e0e0e0"><?=$ac->bitrate?> kbps</div></div>
<div style="text-align:center;display:flex;gap:4px">
<a href="/user/radio/autodj/start/<?=$stationId?>" class="btn btn-sm <?=$ac->autodj_enabled?'btn-secondary':'btn-success'?>"><?=$ac->autodj_enabled?'Restart':'Start'?></a>
<a href="/user/radio/autodj/stop/<?=$stationId?>" class="btn btn-sm btn-danger">Stop</a>
</div>
</div>
<div class="nav-pills" style="margin-bottom:12px">
<a href="?station_id=<?=$stationId?>&tab=autodj&adtab=overview" class="<?=$adTab==='overview'?'active':''?>">Overview</a>
<a href="?station_id=<?=$stationId?>&tab=autodj&adtab=playback" class="<?=$adTab==='playback'?'active':''?>">Playback</a>
<a href="?station_id=<?=$stationId?>&tab=autodj&adtab=rotation" class="<?=$adTab==='rotation'?'active':''?>">Rotation</a>
<a href="?station_id=<?=$stationId?>&tab=autodj&adtab=categories" class="<?=$adTab==='categories'?'active':''?>">Categories</a>
<a href="?station_id=<?=$stationId?>&tab=autodj&adtab=logs" class="<?=$adTab==='logs'?'active':''?>">Logs</a>
<a href="?station_id=<?=$stationId?>&tab=autodj&adtab=ai" class="<?=$adTab==='ai'?'active':''?>">AI Assistant</a>
</div>
<div class="tab <?=$adTab==='overview'?'active':''?>">
<div class="stat-grid">
<div class="stat-card"><div class="sv" id="ad-status"><?=$ac->autodj_enabled?'Running':'Stopped'?></div><div class="sl">Status</div></div>
<div class="stat-card"><div class="sv"><?=ucfirst($ac->playlist_mode)?></div><div class="sl">Mode</div></div>
<div class="stat-card"><div class="sv"><?=$ac->crossfade_time?>s</div><div class="sl">Crossfade</div></div>
<div class="stat-card"><div class="sv"><?=$ac->bitrate?>k</div><div class="sl">Bitrate</div></div>
<div class="stat-card"><div class="sv"><?=$ac->normalize_audio?'On':'Off'?></div><div class="sl">Normalize</div></div>
<div class="stat-card"><div class="sv"><?=$ac->shuffle_enabled?'On':'Off'?></div><div class="sl">Shuffle</div></div>
</div>
<?php if (!empty($playlists)): ?>
<div class="card"><h3>Active Playlists</h3>
<?php $savedPlIds = !empty($ac->playlist_ids) ? json_decode($ac->playlist_ids, true) : []; ?>
<form method="post" action="/user/radio/autodj/update" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px">
<input type="hidden" name="station_id" value="<?=$stationId?>">
<?php foreach ($playlists as $p): $checked = empty($savedPlIds) || in_array($p->id, $savedPlIds) ? 'checked' : ''; ?>
<label style="display:flex;align-items:center;gap:6px;padding:8px 12px;background:rgba(0,0,0,.3);border-radius:6px;font-size:11px;color:#c0c0c0;cursor:pointer">
<input type="checkbox" name="playlist_ids[]" value="<?=$p->id?>" <?=$checked?>> <?=htmlspecialchars($p->name)?>
</label>
<?php endforeach; ?>
<button type="submit" class="btn btn-sm btn-primary" style="margin-left:auto">Save Playlists</button>
</form>
</div>
<?php endif; ?>
<div class="card"><h3>Quick Actions</h3><div style="display:flex;gap:6px;flex-wrap:wrap">
<a href="/user/radio/autodj/setup?step=1&station_id=<?=$stationId?>" class="btn btn-sm btn-secondary">Re-run Wizard</a>
<a href="?station_id=<?=$stationId?>&tab=autodj&adtab=playback" class="btn btn-sm btn-primary">Playback Settings</a>
<a href="?station_id=<?=$stationId?>&tab=autodj&adtab=rotation" class="btn btn-sm btn-primary">Rotation Rules</a>
<a href="?station_id=<?=$stationId?>&tab=autodj&adtab=categories" class="btn btn-sm btn-primary">Categories</a>
<a href="?station_id=<?=$stationId?>&tab=playlists" class="btn btn-sm btn-primary">Playlists</a>
<a href="?station_id=<?=$stationId?>&tab=autodj&adtab=ai" class="btn btn-sm btn-primary" style="background:rgba(168,85,247,.15);color:#a855f7">AI Assistant</a>
</div></div>
</div>
<div class="tab <?=$adTab==='playback'?'active':''?>">
<div class="card"><h3>Playback Controls</h3>
<div style="display:flex;gap:6px;margin-bottom:12px">
<a href="/user/radio/autodj/start/<?=$stationId?>" class="btn btn-sm btn-success">Start</a>
<a href="/user/radio/autodj/stop/<?=$stationId?>" class="btn btn-sm btn-danger">Stop</a>
<a href="/user/radio/autodj/restart/<?=$stationId?>" class="btn btn-sm btn-warning">Restart</a>
</div>
<form method="post" action="/user/radio/autodj/update">
<input type="hidden" name="station_id" value="<?=$stationId?>">
<div class="form-row-3">
<div class="form-group"><label>Playlist Mode</label><select class="inp inp-sm" name="playlist_mode"><option value="sequential" <?=$ac->playlist_mode==='sequential'?'selected':''?>>Sequential</option><option value="random" <?=$ac->playlist_mode==='random'?'selected':''?>>Random</option><option value="weighted" <?=$ac->playlist_mode==='weighted'?'selected':''?>>Weighted</option></select></div>
<div class="form-group"><label>Crossfade (s)</label><input class="inp inp-sm" name="crossfade_time" value="<?=$ac->crossfade_time?:5?>" type="number" step="0.5"></div>
<div class="form-group"><label>&nbsp;</label><div class="check-group"><label><input type="checkbox" name="crossfade_enabled" value="1" <?=$ac->crossfade_enabled?'checked':''?>> Enable Crossfade</label></div></div>
</div>
<div class="form-row-3">
<div class="check-group"><label><input type="checkbox" name="normalize_audio" value="1" <?=$ac->normalize_audio?'checked':''?>> Normalize Audio</label></div>
<div class="check-group"><label><input type="checkbox" name="replaygain" value="1" <?=$ac->replaygain?'checked':''?>> ReplayGain</label></div>
<div class="check-group"><label><input type="checkbox" name="silence_detection" value="1" <?=$ac->silence_detection?'checked':''?>> Silence Detection</label></div>
</div>
<div class="check-group"><label><input type="checkbox" name="remove_duplicates" value="1" <?=$ac->remove_duplicates?'checked':''?>> Remove Duplicates</label></div>
<button class="btn btn-sm btn-primary" style="margin-top:8px">Save Playback Settings</button>
</form></div>
</div>
<div class="tab <?=$adTab==='rotation'?'active':''?>">
<div class="card"><h3>Rotation Rules</h3>
<form method="post" action="/user/radio/autodj/update">
<input type="hidden" name="station_id" value="<?=$stationId?>">
<div class="form-row-3">
<div class="form-group"><label>Max Artist Repeat</label><select class="inp inp-sm" name="max_artist_repeat"><?php foreach([15,30,60,120,240] as $v): ?><option value="<?=$v?>" <?=($ac->max_artist_repeat?:60)==$v?'selected':''?>><?=$v>=60?($v/60).'h':$v.'m'?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>Max Song Repeat</label><select class="inp inp-sm" name="max_song_repeat"><?php foreach([60,120,240,480] as $v): ?><option value="<?=$v?>" <?=($ac->max_song_repeat?:240)==$v?'selected':''?>><?=$v>=60?($v/60).'h':$v.'m'?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>Max Album Repeat</label><select class="inp inp-sm" name="max_album_repeat"><?php foreach([30,60,120,240] as $v): ?><option value="<?=$v?>" <?=($ac->max_album_repeat?:120)==$v?'selected':''?>><?=$v>=60?($v/60).'h':$v.'m'?></option><?php endforeach; ?></select></div>
</div>
<div class="check-group">
<label><input type="checkbox" name="shuffle_enabled" value="1" <?=$ac->shuffle_enabled?'checked':''?>> Shuffle</label>
<label><input type="checkbox" name="weight_new_songs" value="1" <?=$ac->weight_new_songs?'checked':''?>> Weight New Songs</label>
<label><input type="checkbox" name="weight_favorites" value="1" <?=$ac->weight_favorites?'checked':''?>> Weight Favorites</label>
</div>
<button class="btn btn-sm btn-primary" style="margin-top:8px">Save Rotation Rules</button>
</form></div>
</div>
<div class="tab <?=$adTab==='categories'?'active':''?>">
<div class="card"><div class="hdr"><h3>Categories</h3></div>
<table><tr><th>Name</th><th>Type</th><th>Playlist</th><th>Actions</th></tr>
<?php if (empty($autodjCats)): ?><tr><td colspan="4" class="empty-state">No categories</td></tr>
<?php else: ?>
<?php foreach ($autodjCats as $c): ?>
<tr><td><?=htmlspecialchars($c->name)?></td><td><?=htmlspecialchars($c->type)?></td><td><?php $pl=null; foreach($playlists as $p){if(($p->id??0)==($c->playlist_id??0)){$pl=$p;break;}} echo htmlspecialchars($pl->name??'-'); ?></td><td class="actions"><a href="/user/radio/autodj/category/delete/<?=$c->id?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a></td></tr>
<?php endforeach; ?>
<?php endif; ?>
</table></div>
<div class="card"><h3>Add Category</h3>
<form method="post" action="/user/radio/autodj/category/add">
<input type="hidden" name="station_id" value="<?=$stationId?>">
<div class="form-row"><div class="form-group"><label>Name</label><input class="inp inp-sm" name="name" required placeholder="e.g. Morning Music"></div>
<div class="form-group"><label>Type</label><select class="inp inp-sm" name="type"><option value="music">Music</option><option value="jingle">Jingle</option><option value="promo">Promo</option><option value="ad">Advertisement</option><option value="sweeper">Sweeper</option><option value="station_id">Station ID</option><option value="news">News</option><option value="weather">Weather</option><option value="talk">Talk Show</option></select></div></div>
<div class="form-group"><label>Link to Playlist (optional)</label><select class="inp inp-sm" name="playlist_id"><option value="">None</option><?php foreach($playlists as $p): ?><option value="<?=$p->id?>"><?=htmlspecialchars($p->name)?></option><?php endforeach; ?></select></div>
<button class="btn btn-sm btn-primary">Add Category</button>
</form></div>
</div>
<div class="tab <?=$adTab==='logs'?'active':''?>">
<div class="card"><div class="hdr"><h3>AutoDJ Logs</h3><a href="/user/radio/autodj/clear-logs?station_id=<?=$stationId?>" class="btn btn-sm btn-danger" onclick="return confirm('Clear all logs?')">Clear Logs</a></div>
<table><tr><th>Time</th><th>Type</th><th>Message</th></tr>
<?php if (empty($autodjLogs)): ?><tr><td colspan="3" class="empty-state">No log entries</td></tr>
<?php else: ?>
<?php foreach ($autodjLogs as $l): ?>
<tr><td style="white-space:nowrap"><?=htmlspecialchars($l->created_at??'')?></td><td><span class="status-badge <?=$l->type==='error'?'status-stopped':($l->type==='warning'?'status-starting':'status-running')?>"><?=htmlspecialchars($l->type)?></span></td><td><?=htmlspecialchars($l->message)?></td></tr>
<?php endforeach; ?>
<?php endif; ?>
</table></div>
</div>
<div class="tab <?=$adTab==='ai'?'active':''?>">
<div class="card"><div class="hdr"><h3>AI AutoDJ Assistant</h3><span style="font-size:10px;color:#64748b">Powered by OpenAI</span></div>
<div style="background:rgba(0,0,0,.3);border-radius:8px;padding:12px;margin-bottom:12px;max-height:300px;overflow-y:auto" id="aiChat">
<div style="padding:8px 12px;margin-bottom:6px;background:rgba(168,85,247,.08);border-radius:8px;font-size:11px;color:#94a3b8">Hello! I'm your AI AutoDJ assistant. Ask me to create playlists, configure rotation rules, schedule music, or optimize your station.<br><br>Try: "Create a classic rock playlist with no artist repeating within 2 hours" or "Schedule Christmas music from December 1st"</div>
</div>
<div style="display:flex;gap:6px">
<input class="inp inp-sm" id="aiQuestion" placeholder="Ask the AI AutoDJ assistant..." style="flex:1" onkeydown="if(event.key==='Enter')askAI()">
<button class="btn btn-sm btn-primary" onclick="askAI()">Ask</button>
</div>
</div>
<div class="card" id="aiSuggestions" style="display:none"><h3>AI Suggestion</h3><div id="aiAnswer" style="font-size:12px;color:#c0c0c0;white-space:pre-wrap"></div></div>
</div>
<?php endif; ?>
</div>
<div class="tab <?=$tab==='settings'?'active':''?>">
  <div class="card"><h3>Station Settings</h3>
  <form method="post" action="/user/radio/settings/update">
    <input type="hidden" name="station_id" value="<?=$stationId?>">
    <div class="form-row"><div class="form-group"><label>Station Name</label><input class="inp inp-sm" name="name" value="<?=htmlspecialchars($station->name??'')?>"></div><div class="form-group"><label>Genre</label><input class="inp inp-sm" name="genre" value="<?=htmlspecialchars($station->genre??'')?>"></div></div>
    <div class="form-group"><label>Description</label><textarea class="inp inp-sm" name="description" rows="2"><?=htmlspecialchars($station->description??'')?></textarea></div>
    <div class="form-row"><div class="form-group"><label>Mount Point</label><input class="inp inp-sm" name="mount" value="<?=htmlspecialchars($station->mount??'/stream')?>"></div><div class="form-group"><label>Bitrate</label><select class="inp inp-sm" name="bitrate"><option value="128" <?=($station->bitrate??128)==128?'selected':''?>>128 kbps</option><option value="192" <?=($station->bitrate??'')==192?'selected':''?>>192 kbps</option><option value="256" <?=($station->bitrate??'')==256?'selected':''?>>256 kbps</option><option value="320" <?=($station->bitrate??'')==320?'selected':''?>>320 kbps</option></select></div></div>
    <div class="form-row"><div class="form-group"><label>Source Password</label><input class="inp inp-sm" name="password" value="<?=htmlspecialchars($station->plain_password??$station->password??'')?>" style="font-family:monospace;color:#4ade80"></div><div class="form-group"><label>Admin Password</label><input class="inp inp-sm" name="admin_password" value="<?=htmlspecialchars($station->admin_password??'')?>" style="font-family:monospace;color:#facc15"></div></div>
    <div class="form-row"><div class="form-group"><label>Max Listeners</label><input class="inp inp-sm" type="number" name="max_listeners" value="<?=$station->max_listeners??100?>"></div><div class="form-group"><label>Public</label><select class="inp inp-sm" name="public_server"><option value="1" <?=($station->public_server??1)==1?'selected':''?>>Yes</option><option value="0" <?=($station->public_server??'')==='0'?'selected':''?>>No</option></select></div></div>
    <button class="btn btn-sm btn-primary">Save Settings</button>
  </form></div>
</div>
<div class="tab <?=$tab==='branding'?'active':''?>">
  <div class="card"><div class="hdr"><h3>Station Branding</h3><span style="font-size:10px;color:#64748b">Your station's unique identity</span></div>
  <form method="post" action="/user/radio/branding/save" enctype="multipart/form-data">
    <input type="hidden" name="station_id" value="<?=$stationId?>">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
      <div>
        <h4 style="font-size:11px;color:#94a3b8;margin:0 0 8px 0">Colors</h4>
        <div class="color-picker-wrap"><label style="font-size:10px;color:#64748b;min-width:80px">Primary</label><input type="color" name="brand_primary_color" value="<?=htmlspecialchars($branding->brand_primary_color??'#0A84FF')?>"><span class="hex"><?=htmlspecialchars($branding->brand_primary_color??'#0A84FF')?></span></div>
        <div class="color-picker-wrap"><label style="font-size:10px;color:#64748b;min-width:80px">Secondary</label><input type="color" name="brand_secondary_color" value="<?=htmlspecialchars($branding->brand_secondary_color??'#5856D6')?>"><span class="hex"><?=htmlspecialchars($branding->brand_secondary_color??'#5856D6')?></span></div>
        <div class="color-picker-wrap"><label style="font-size:10px;color:#64748b;min-width:80px">Accent</label><input type="color" name="brand_accent_color" value="<?=htmlspecialchars($branding->brand_accent_color??'#00C853')?>"><span class="hex"><?=htmlspecialchars($branding->brand_accent_color??'#00C853')?></span></div>
        <h4 style="font-size:11px;color:#94a3b8;margin:12px 0 8px 0">Fonts</h4>
        <div class="form-group"><label>Header Font</label><input class="inp inp-sm" name="brand_header_font" value="<?=htmlspecialchars($branding->brand_header_font??'Inter')?>"></div>
        <div class="form-group"><label>Body Font</label><input class="inp inp-sm" name="brand_body_font" value="<?=htmlspecialchars($branding->brand_body_font??'Inter')?>"></div>
      </div>
      <div>
        <h4 style="font-size:11px;color:#94a3b8;margin:0 0 8px 0">Player Theme</h4>
        <div class="form-row"><div class="form-group"><label>Theme</label><select class="inp inp-sm" name="brand_player_theme"><option value="default" <?=($branding->brand_player_theme??'default')==='default'?'selected':''?>>Default</option><option value="minimal" <?=($branding->brand_player_theme??'')==='minimal'?'selected':''?>>Minimal</option><option value="full" <?=($branding->brand_player_theme??'')==='full'?'selected':''?>>Full</option><option value="custom" <?=($branding->brand_player_theme??'')==='custom'?'selected':''?>>Custom</option></select></div>
        <div class="form-group"><label>BG</label><select class="inp inp-sm" name="brand_player_bg"><option value="dark" <?=($branding->brand_player_bg??'dark')==='dark'?'selected':''?>>Dark</option><option value="light" <?=($branding->brand_player_bg??'')==='light'?'selected':''?>>Light</option><option value="gradient" <?=($branding->brand_player_bg??'')==='gradient'?'selected':''?>>Gradient</option><option value="transparent" <?=($branding->brand_player_bg??'')==='transparent'?'selected':''?>>Transparent</option></select></div></div>
        <div class="form-group"><label>Slogan</label><input class="inp inp-sm" name="brand_slogan" value="<?=htmlspecialchars($branding->brand_slogan??'')?>"></div>
        <h4 style="font-size:11px;color:#94a3b8;margin:12px 0 8px 0">Social Links</h4>
        <div class="form-group"><label>Twitter</label><input class="inp inp-sm" name="brand_social_twitter" value="<?=htmlspecialchars($branding->brand_social_twitter??'')?>"></div>
        <div class="form-group"><label>Facebook</label><input class="inp inp-sm" name="brand_social_facebook" value="<?=htmlspecialchars($branding->brand_social_facebook??'')?>"></div>
        <div class="form-group"><label>Instagram</label><input class="inp inp-sm" name="brand_social_instagram" value="<?=htmlspecialchars($branding->brand_social_instagram??'')?>"></div>
        <div class="form-group"><label>Discord</label><input class="inp inp-sm" name="brand_social_discord" value="<?=htmlspecialchars($branding->brand_social_discord??'')?>"></div>
      </div>
    </div>
    <h4 style="font-size:11px;color:#94a3b8;margin:12px 0 8px 0">Images</h4>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px">
      <div class="form-group"><label>Logo</label><input type="file" name="brand_logo" accept="image/*" class="inp inp-sm" style="padding:3px"></div>
      <div class="form-group"><label>Banner</label><input type="file" name="brand_banner" accept="image/*" class="inp inp-sm" style="padding:3px"></div>
      <div class="form-group"><label>Player BG</label><input type="file" name="brand_player_bg_img" accept="image/*" class="inp inp-sm" style="padding:3px"></div>
      <div class="form-group"><label>Default Art</label><input type="file" name="brand_default_art" accept="image/*" class="inp inp-sm" style="padding:3px"></div>
    </div>
    <button class="btn btn-sm btn-primary" style="margin-top:10px">Save Branding</button>
  </form></div>
</div>
<div class="tab <?=$tab==='mounts'?'active':''?>">
  <div class="card"><div class="hdr"><h3>Mount Points</h3></div>
  <table><tr><th>Mount</th><th>Bitrate</th><th>Description</th><th>Actions</th></tr>
    <?php if (empty($mounts)): ?><tr><td colspan="4" class="empty-state">Main mount: <?=htmlspecialchars($station->mount??'/stream')?></td></tr>
    <?php else: ?>
    <?php foreach ($mounts as $m): ?>
    <tr><td><?=htmlspecialchars($m->mount??'')?></td><td><?=$m->bitrate??128?> kbps</td><td><?=htmlspecialchars($m->description??'')?></td><td class="actions"><a href="/user/radio/mount/delete/<?=$m->id?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a></td></tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </table></div>
  <div class="card"><h3>Add Mount</h3>
  <form method="post" action="/user/radio/mount/add">
    <input type="hidden" name="station_id" value="<?=$stationId?>">
    <div class="form-row"><div class="form-group"><label>Mount Path</label><input class="inp inp-sm" name="mount" value="/stream2"></div><div class="form-group"><label>Bitrate</label><select class="inp inp-sm" name="bitrate"><option value="128">128</option><option value="64">64</option><option value="192">192</option><option value="320">320</option></select></div></div>
    <div class="form-group"><label>Description</label><input class="inp inp-sm" name="description"></div>
    <button class="btn btn-sm btn-primary">Add Mount</button>
  </form></div>
</div>
<div class="tab <?=$tab==='song_history'?'active':''?>">
  <div class="card"><div class="hdr"><h3>Song History</h3><input class="inp inp-sm" id="song-search" placeholder="Search..." style="width:200px" onkeyup="searchSongs(this.value)"></div>
  <table id="song-table"><tr><th>Title</th><th>Artist</th><th>Album</th><th>Duration</th><th>Played At</th></tr>
    <?php if (empty($songs)): ?><tr><td colspan="5" class="empty-state">No songs played yet</td></tr>
    <?php else: ?>
    <?php foreach ($songs as $sh): ?>
    <tr class="song-row">
      <td><?=htmlspecialchars($sh->title??'Unknown')?></td><td><?=htmlspecialchars($sh->artist??'Unknown')?></td>
      <td><?=htmlspecialchars($sh->album??'')?></td><td><?=$sh->duration?gmdate('i:s',$sh->duration):'-'?></td>
      <td><?=htmlspecialchars($sh->played_at??'')?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </table></div>
</div>
<div class="tab <?=$tab==='backups'?'active':''?>">
  <div class="card"><div class="hdr"><h3>Backups</h3><a href="/user/radio/backup/create" class="btn btn-sm btn-primary">Create Backup</a></div>
  <?php if (empty($backups)): ?><div class="empty-state">No backups yet</div>
  <?php else: ?>
  <table><tr><th>File</th><th>Size</th><th>Date</th><th>Actions</th></tr>
    <?php foreach ($backups as $bk): $bn = basename($bk); ?>
    <tr>
      <td><?=htmlspecialchars($bn)?></td><td><?=round(filesize($bk)/1048576,1)?> MB</td><td><?=date('Y-m-d H:i',filemtime($bk))?></td>
      <td class="actions"><a href="/user/radio/backup/download?file=<?=urlencode($bn)?>" class="btn btn-sm btn-success">Download</a><a href="/user/radio/backup/delete?file=<?=urlencode($bn)?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
  </div>
</div>
<div class="tab <?=$tab==='applications'?'active':''?>">
  <div class="card"><div class="hdr"><h3>DJ Applications</h3><a href="https://planet-hosts.com:2083/radio/apply.php?stream=<?=$station->streaming_id?>" target="_blank" class="btn btn-sm btn-primary">Public Form</a></div>
  <?php $apps = $applications ?? []; $as = $_GET['app_filter'] ?? 'pending';
  $fa = array_filter($apps, function($a) use ($as) { return $as === 'all' || ($a->status ?? 'pending') === $as; }); ?>
  <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px">
    <a href="?station_id=<?=$stationId?>&tab=applications&app_filter=pending" class="btn btn-sm <?=$as==='pending'?'btn-primary':'btn-secondary'?>">Pending</a>
    <a href="?station_id=<?=$stationId?>&tab=applications&app_filter=approved" class="btn btn-sm <?=$as==='approved'?'btn-primary':'btn-secondary'?>">Approved</a>
    <a href="?station_id=<?=$stationId?>&tab=applications&app_filter=rejected" class="btn btn-sm <?=$as==='rejected'?'btn-primary':'btn-secondary'?>">Rejected</a>
    <a href="?station_id=<?=$stationId?>&tab=applications&app_filter=all" class="btn btn-sm <?=$as==='all'?'btn-primary':'btn-secondary'?>">All</a>
  </div>
  <?php if (empty($fa)): ?><div class="empty-state">No <?=$as!=='all'?$as:''?> applications</div>
  <?php else: ?>
  <table><tr><th>Name</th><th>Email</th><th>DJ Name</th><th>Date</th><th>Status</th><th>Actions</th></tr>
    <?php foreach ($fa as $a): ?>
    <tr>
      <td><?=htmlspecialchars($a->name??'')?></td><td><?=htmlspecialchars($a->email??'')?></td>
      <td><?=htmlspecialchars($a->dj_name??'-')?></td><td style="font-size:11px"><?=htmlspecialchars($a->created_at??'')?></td>
      <td><span class="status-badge <?=$a->status==='approved'?'status-running':($a->status==='rejected'?'status-stopped':'status-starting')?>"><?=$a->status??'pending'?></span></td>
      <td class="actions">
        <?php if ($a->status === 'pending'): ?>
          <a href="/user/radio/dj/approve/<?=$a->id?>" class="btn btn-sm btn-success" onclick="return confirm('Approve? DJ account will be created.')">Approve</a>
          <a href="/user/radio/dj/reject/<?=$a->id?>" class="btn btn-sm btn-danger" onclick="return confirm('Reject?')">Reject</a>
        <?php else: ?><span style="font-size:10px;color:#64748b"><?=ucfirst($a->status)?></span><?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
  </div>
</div>
<?php elseif (empty($stations)): ?>
<div class="card"><div class="empty-state"><div style="font-size:40px;margin-bottom:10px">&#127926;</div><div style="font-size:14px;color:#c0c0c0;margin-bottom:6px">No radio stations found</div><div style="font-size:11px;color:#64748b;margin-bottom:14px">Create your first station</div><a href="/user/radio/setup" class="btn btn-primary">Create Station</a></div></div>
<?php else: ?>
<div class="card"><div class="empty-state"><div style="font-size:40px;margin-bottom:10px">&#9888;</div><div style="font-size:14px;color:#c0c0c0">No station selected</div></div></div>
<?php endif; ?>
<script>
function getTab(){return new URLSearchParams(window.location.search).get('tab')||'overview';}
function searchSongs(q){document.querySelectorAll('.song-row').forEach(function(r){r.style.display=r.textContent.toLowerCase().indexOf(q.toLowerCase())>=0?'':'none';});}
function askAI(){var q=document.getElementById('aiQuestion');if(!q.value.trim())return;var chat=document.getElementById('aiChat');var msg=document.createElement('div');msg.style.cssText='padding:8px 12px;margin-bottom:6px;background:rgba(0,140,255,.08);border-radius:8px;font-size:11px;color:#e0e0e0';msg.textContent=q.value;chat.appendChild(msg);chat.scrollTop=chat.scrollHeight;var sug=document.getElementById('aiSuggestions');sug.style.display='block';document.getElementById('aiAnswer').textContent='Thinking...';var x=new XMLHttpRequest();x.open('POST','/user/radio/autodj/ai-ask',true);x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');x.onload=function(){try{var r=JSON.parse(x.responseText);document.getElementById('aiAnswer').textContent=r.answer||'Error: '+(r.error||'Unknown');var resp=document.createElement('div');resp.style.cssText='padding:8px 12px;margin-bottom:6px;background:rgba(168,85,247,.08);border-radius:8px;font-size:11px;color:#94a3b8;white-space:pre-wrap';resp.textContent=r.answer||r.error;chat.appendChild(resp);chat.scrollTop=chat.scrollHeight;}catch(e){document.getElementById('aiAnswer').textContent='Error processing response'}};x.send('question='+encodeURIComponent(q.value)+'&station_id=<?=$stationId?>');q.value='';}
var _queue=[],_playlistId=<?=$mPlId?:'null'?>;_playlistId=_playlistId||'',_csrf='<?=$_csrf_token??''?>';
var _z=document.getElementById('uploadZone'),_inp=document.getElementById('media-input'),_q=document.getElementById('uploadQueue'),_p=document.getElementById('uploadProgress'),_pb=document.getElementById('uploadProgressBar'),_ps=document.getElementById('uploadStatus'),_btn=document.getElementById('uploadBtn'),_cnt=document.getElementById('uploadCount');
['dragenter','dragover'].forEach(function(e){_z.addEventListener(e,function(ev){ev.preventDefault();_z.style.borderColor='#008cff';_z.style.background='rgba(0,140,255,.08)';});});
['dragleave','drop'].forEach(function(e){_z.addEventListener(e,function(ev){ev.preventDefault();_z.style.borderColor='rgba(0,191,255,.2)';_z.style.background='rgba(0,0,0,.2)';});});
_z.addEventListener('drop',function(ev){ev.preventDefault();handleFiles(ev.dataTransfer.files);});
_inp.addEventListener('change',function(){handleFiles(this.files);});
function handleFiles(files){for(var i=0;i<files.length;i++){var f=files[i];var ext=f.name.split('.').pop().toLowerCase();if(['mp3','aac','ogg','flac','wav','m4a'].indexOf(ext)<0)continue;_queue.push(f);}renderQueue();}
function renderQueue(){_q.innerHTML='';if(!_queue.length){_btn.style.display='none';return;}_btn.style.display='inline-block';_cnt.textContent=_queue.length+' file(s)';for(var i=0;i<_queue.length;i++){var d=document.createElement('div');d.style.cssText='padding:4px 8px;margin:2px 0;background:rgba(0,0,0,.2);border-radius:4px';d.textContent=_queue[i].name+' ('+Math.round(_queue[i].size/1024)+' KB)';_q.appendChild(d);}}
function startUpload(){if(!_queue.length)return;_btn.disabled=true;_btn.textContent='Uploading...';_p.style.display='block';_pb.style.width='0';_ps.textContent='';var i=0;var total=_queue.length;function uploadNext(){if(i>=total){_ps.textContent='All files uploaded!';_btn.textContent='Done';setTimeout(function(){location.reload();},1000);return;}var fd=new FormData();fd.append('playlist_id',_playlistId);fd.append('_csrf_token',_csrf);fd.append('files[]',_queue[i]);var x=new XMLHttpRequest();x.open('POST','/user/radio/media/upload',true);x.setRequestHeader('X-CSRF-Token',_csrf);x.upload.onprogress=function(ev){if(ev.lengthComputable){var pct=Math.round(((i+ev.loaded/ev.total)/total)*100);_pb.style.width=pct+'%';_ps.textContent='Uploading '+_queue[i].name+' ('+Math.round(ev.loaded/ev.total*100)+'%)';}};x.onload=function(){if(x.status===200){i++;uploadNext();}else{_ps.textContent='Failed: '+_queue[i].name;_btn.disabled=false;}};x.send(fd);}uploadNext();}
</script>

