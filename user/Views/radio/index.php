<style>
.r-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:10px;margin-bottom:14px}
.r-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:14px;text-align:center}
.r-stat .num{font-size:22px;font-weight:800}
.r-stat .lbl{font-size:10px;color:#64748b;margin-top:2px}
.r-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:18px;margin-bottom:12px}
.r-card h3{font-size:14px;font-weight:600;margin:0 0 10px}
.r-card h3 span{color:#64748b;font-size:12px;font-weight:400}
.nav-pills{display:flex;gap:2px;flex-wrap:wrap;margin-bottom:14px;background:rgba(8,16,28,.6);border-radius:8px;padding:3px}
.nav-pills a{padding:6px 12px;border-radius:6px;font-size:11px;text-decoration:none;color:#94a3b8;transition:.1s;white-space:nowrap}
.nav-pills a:hover{color:#e0e0e0;background:rgba(255,255,255,.04)}
.nav-pills a.active{color:#fff;background:rgba(0,140,255,.2)}
.tab{display:none}
.tab.active{display:block}
.nowplaying{display:flex;align-items:center;gap:14px;padding:14px;background:linear-gradient(135deg,rgba(0,140,255,.06),rgba(168,85,247,.04));border:1px solid rgba(0,191,255,.1);border-radius:12px;margin-bottom:14px}
</style>
<?php $tab = $_GET['tab'] ?? 'overview'; ?>
<h2>📻 Radio Dashboard</h2>
<p style="color:#64748b;margin-bottom:14px">Manage your station, DJs, music, and listeners.</p>

<?php if (!$station): ?>
<div class="r-card" style="text-align:center;padding:30px"><h3>No Station</h3><p style="color:#64748b">Your package does not include radio hosting.</p></div>
<?php else: ?>

<div class="nowplaying">
<div style="font-size:36px">📻</div>
<div style="flex:1"><strong style="font-size:16px"><?php echo htmlspecialchars($station->name ?? 'My Station'); ?></strong>
<div style="font-size:12px;color:#64748b"><?php echo htmlspecialchars($station->current_song ?? 'Not Playing'); ?> • <?php echo $station->status === 'running' ? '<span style="color:#4ade80">● Live</span>' : '<span style="color:#64748b">● Offline</span>'; ?></div></div>
<div style="text-align:right;font-size:12px;color:#64748b">
Listeners: <strong><?php echo (int)($station->listener_count ?? 0); ?></strong><br>
Peak: <strong><?php echo (int)($station->listener_peak ?? 0); ?></strong>
</div></div>

<div class="r-grid">
<div class="r-stat"><div class="num" style="color:#0A84FF"><?php echo count($djs ?? []);?></div><div class="lbl">DJs</div></div>
<div class="r-stat"><div class="num" style="color:#4ade80"><?php echo count($requests ?? []);?></div><div class="lbl">Requests</div></div>
<div class="r-stat"><div class="num" style="color:#a78bfa"><?php echo count($schedule ?? []);?></div><div class="lbl">Shows</div></div>
<div class="r-stat"><div class="num" style="color:#38bdf8"><?php echo $station->listener_peak ?? 0;?></div><div class="lbl">Peak</div></div>
</div>

<div class="nav-pills">
<a href="?tab=overview" class="<?php echo $tab==='overview'?'active':'';?>">📊 Overview</a>
<a href="?tab=djs" class="<?php echo $tab==='djs'?'active':'';?>">🎧 DJs</a>
<a href="?tab=schedule" class="<?php echo $tab==='schedule'?'active':'';?>">📅 Schedule</a>
<a href="?tab=requests" class="<?php echo $tab==='requests'?'active':'';?>">🙋 Requests</a>
<a href="?tab=media" class="<?php echo $tab==='media'?'active':'';?>">🎶 Media</a>
<a href="?tab=playlists" class="<?php echo $tab==='playlists'?'active':'';?>">📂 Playlists</a>
<a href="?tab=mounts" class="<?php echo $tab==='mounts'?'active':'';?>">🔗 Mounts</a>
<a href="?tab=widgets" class="<?php echo $tab==='widgets'?'active':'';?>">🧩 Widgets</a>
<a href="?tab=stats" class="<?php echo $tab==='stats'?'active':'';?>">📊 Stats</a>
<a href="?tab=backups" class="<?php echo $tab==='backups'?'active':'';?>">💾 Backups</a>
</div>

<!-- Overview -->
<div class="tab <?php echo $tab==='overview'?'active':'';?>">
<div class="r-card"><h3>Stream Controls</h3>
<div style="display:flex;gap:6px;flex-wrap:wrap">
<a href="/user/radio/start/<?php echo $station->id;?>" class="btn btn-sm" style="background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.2);padding:6px 14px;border-radius:6px;text-decoration:none">▶ Start</a>
<a href="/user/radio/stop/<?php echo $station->id;?>" class="btn btn-sm" style="background:rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.2);padding:6px 14px;border-radius:6px;text-decoration:none">⏹ Stop</a>
<a href="/user/radio/restart/<?php echo $station->id;?>" class="btn btn-sm" style="background:rgba(250,204,21,.1);color:#facc15;border:1px solid rgba(250,204,21,.2);padding:6px 14px;border-radius:6px;text-decoration:none">🔄 Restart</a>
<form method="POST" action="/user/radio/kick-source" style="display:inline">
<input type="hidden" name="station_id" value="<?php echo $station->id;?>">
<button type="submit" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171;border:1px solid rgba(248,113,113,.25);padding:6px 14px;border-radius:6px;cursor:pointer">⛔ Kick Source</button>
</form>
<form method="POST" action="/user/radio/autodj/toggle/<?php echo $station->id;?>" style="display:inline">
<button type="submit" class="btn btn-sm" style="background:<?php echo $station->autodj_enabled?'rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.2)':'rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.2)';?>;padding:6px 14px;border-radius:6px;cursor:pointer">
<?php echo $station->autodj_enabled ? '⏹ Disable AutoDJ' : '▶ Enable AutoDJ';?></button>
</form>
</div></div>
<div class="r-card"><h3>Station Info</h3>
<div style="display:grid;grid-template-columns:120px 1fr;gap:4px;font-size:12px">
<span style="color:#64748b">Name</span><span><?php echo htmlspecialchars($station->name);?></span>
<span style="color:#64748b">Genre</span><span><?php echo htmlspecialchars($station->genre);?></span>
<span style="color:#64748b">Status</span><span><span style="color:<?php echo $station->status==='running'?'#4ade80':'#64748b';?>">● <?php echo $station->status;?></span></span>
<span style="color:#64748b">Port</span><span><code><?php echo $station->port;?></code></span>
<span style="color:#64748b">Mount</span><span><code><?php echo htmlspecialchars($station->mount);?></code></span>
<span style="color:#64748b">Bitrate</span><span><?php echo $station->bitrate;?> kbps</span>
<span style="color:#64748b">Listen URL</span><span><code style="font-size:10px">http://<?php echo $_SERVER['HTTP_HOST']??'localhost';?>:<?php echo $station->port;?><?php echo htmlspecialchars($station->mount);?></code></span>
</div></div>
</div>

<!-- DJs -->
<div class="tab <?php echo $tab==='djs'?'active':'';?>">
<div class="r-card"><h3>➕ Create DJ</h3>
<form method="POST" action="/user/radio/dj/create" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:6px">
<input name="username" placeholder="Username" required style="padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<input name="password" placeholder="Password" required style="padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<input name="name" placeholder="Display Name" style="padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<input name="email" placeholder="Email" style="padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<div style="grid-column:span 4;display:flex;gap:6px">
<input name="bio" placeholder="Bio / Genres" style="flex:1;padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<button type="submit" class="btn btn-sm btn-primary">➕ Add DJ</button>
</div>
</form></div>
<div class="r-card"><h3>🎧 DJ Accounts <span>(<?php echo count($djs);?>)</span></h3>
<?php if (empty($djs)):?><p style="color:#64748b;font-size:12px;text-align:center;padding:10px">No DJs yet.</p>
<?php else:?>
<table class="table"><thead><tr><th>Name</th><th>Username</th><th>Status</th><th>Last Login</th><th></th></tr></thead>
<tbody><?php foreach($djs as $d):?>
<tr><td><?php echo htmlspecialchars($d->display_name ?? $d->username);?></td>
<td><code><?php echo htmlspecialchars($d->username);?></code></td>
<td><span style="color:<?php echo $d->status==='active'?'#4ade80':'#f87171';?>">● <?php echo $d->status;?></span></td>
<td><?php echo $d->last_login ? date('M j',strtotime($d->last_login)) : 'Never';?></td>
<td style="display:flex;gap:3px">
<a href="/user/radio/dj/toggle/<?php echo $d->id;?>" class="btn btn-sm <?php echo $d->status==='active'?'btn-warning':'btn-success';?>"><?php echo $d->status==='active'?'⏸':'▶';?></a>
<a href="/user/radio/dj/delete/<?php echo $d->id;?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete <?php echo htmlspecialchars($d->username);?>?')">🗑</a>
</td></tr>
<?php endforeach;?></tbody></table>
<?php endif;?>
</div></div>

<!-- Schedule -->
<div class="tab <?php echo $tab==='schedule'?'active':'';?>">
<div class="r-card"><h3>➕ Add Show</h3>
<form method="POST" action="/user/radio/schedule/add" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:6px">
<select name="dj_id" style="padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<option value="">DJ</option><?php foreach($djs as $d):?><option value="<?php echo $d->id;?>"><?php echo htmlspecialchars($d->display_name??$d->username);?></option><?php endforeach;?></select>
<input name="show_name" placeholder="Show name" style="padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<select name="day_of_week" style="padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<option value="0">Sun</option><option value="1">Mon</option><option value="2">Tue</option><option value="3">Wed</option><option value="4">Thu</option><option value="5">Fri</option><option value="6">Sat</option></select>
<div style="display:flex;gap:4px"><input name="start_time" type="time" style="flex:1;padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<input name="end_time" type="time" style="flex:1;padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<button type="submit" class="btn btn-sm btn-primary">➕</button></div>
</form></div>
<div class="r-card"><h3>📅 Schedule</h3>
<?php if (empty($schedule)):?><p style="color:#64748b;font-size:12px;text-align:center;padding:10px">No shows.</p>
<?php else:$days=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];?>
<table class="table"><thead><tr><th>Day</th><th>Time</th><th>Show</th><th>DJ</th><th></th></tr></thead>
<tbody><?php foreach($schedule as $sc):?><tr><td><?php echo $days[$sc->day_of_week]??'?';?></td>
<td><?php echo htmlspecialchars($sc->start_time??'');?>-<?php echo htmlspecialchars($sc->end_time??'');?></td>
<td><?php echo htmlspecialchars($sc->show_name);?></td>
<td><?php
$djName=''; foreach($djs as $d){if($d->id==$sc->dj_id){$djName=$d->display_name??$d->username;break;}}
echo htmlspecialchars($djName);?></td>
<td><a href="/user/radio/schedule/delete/<?php echo $sc->id;?>" class="btn btn-sm btn-danger">✕</a></td></tr>
<?php endforeach;?></tbody></table>
<?php endif;?></div></div>

<!-- Requests -->
<div class="tab <?php echo $tab==='requests'?'active':'';?>">
<div class="r-card"><h3>🙋 Pending Requests <span>(<?php echo count($requests);?>)</span></h3>
<?php if(empty($requests)):?><p style="color:#64748b;font-size:12px;text-align:center;padding:15px">No pending requests.</p>
<?php else: foreach($requests as $r):?>
<div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:12px">
<div><strong><?php echo htmlspecialchars($r->title);?></strong> <?php if($r->artist):?>by <em><?php echo htmlspecialchars($r->artist);?></em><?php endif;?>
<br><span style="color:#64748b;font-size:10px">From <?php echo htmlspecialchars($r->requester_name??'Anonymous');?></span></div>
<div style="display:flex;gap:4px"><a href="/user/radio/request/approve/<?php echo $r->id;?>" class="btn btn-sm btn-success">✓</a>
<a href="/user/radio/request/reject/<?php echo $r->id;?>" class="btn btn-sm btn-danger">✕</a></div></div>
<?php endforeach; endif;?></div></div>

<!-- Media Manager -->
<div class="tab <?php echo $tab==='media'?'active':'';?>">
<div class="r-card"><h3>🎶 Media Library</h3>
<?php $musicDir = '/home/radio/' . $station->id . '/music'; $files = []; if (is_dir($musicDir)) $files = array_diff(scandir($musicDir), ['.','..']); ?>
<div style="margin-bottom:10px">
<form method="POST" action="/user/radio/media/upload" enctype="multipart/form-data" style="display:flex;gap:6px">
<input type="file" name="file[]" multiple required style="flex:1;padding:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px">
<button type="submit" class="btn btn-sm btn-primary">📤 Upload</button>
</form>
<small style="color:#64748b">Supported: MP3, AAC, OGG, FLAC, WAV</small>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px">
<?php if (empty($files)):?><p style="color:#64748b;font-size:12px;grid-column:1/-1;text-align:center;padding:15px">No music files uploaded.</p>
<?php else: foreach($files as $f): $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION)); if (is_file("$musicDir/$f")): $sz = filesize("$musicDir/$f"); ?>
<div style="background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.06);border-radius:8px;padding:10px;text-align:center;font-size:11px">
<div style="font-size:24px;margin-bottom:4px">🎵</div>
<div style="font-weight:600;word-break:break-all"><?php echo htmlspecialchars($f);?></div>
<div style="color:#64748b;font-size:10px"><?php echo $sz > 1048576 ? round($sz/1048576,1).' MB' : round($sz/1024,1).' KB'; ?></div>
<a href="/user/radio/media/delete?file=<?php echo urlencode($f);?>" class="btn btn-sm btn-danger" style="margin-top:4px" onclick="return confirm('Delete?')">✕</a>
</div><?php endif; endforeach; endif; ?>
</div></div></div>

<!-- Playlists -->
<div class="tab <?php echo $tab==='playlists'?'active':'';?>">
<div class="r-card"><h3>📂 Playlists</h3>
<p style="color:#64748b;font-size:12px">Playlist management coming soon.</p></div></div>

<!-- Mount Points -->
<div class="tab <?php echo $tab==='mounts'?'active':'';?>">
<?php $mounts = []; try { $mounts = $this->db->table('radio_mounts')->where('station_id', $station->id)->get() ?: []; } catch(\Exception $e) {} ?>
<div class="r-card"><h3>🔗 Mount Points</h3>
<form method="POST" action="/user/radio/mount/add" style="display:grid;grid-template-columns:1fr 1fr 80px;gap:6px;margin-bottom:10px">
<input name="mount" placeholder="/stream2" value="/" style="padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<select name="bitrate" style="padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none"><option value="64">64 kbps</option><option value="128" selected>128 kbps</option><option value="192">192 kbps</option><option value="320">320 kbps</option></select>
<button type="submit" class="btn btn-sm btn-primary">➕</button>
</form>
<?php if (empty($mounts)):?><p style="color:#64748b;font-size:12px;text-align:center;padding:10px">No additional mounts.</p>
<?php else:?>
<table class="table"><thead><tr><th>Mount</th><th>Bitrate</th><th>Listeners</th><th></th></tr></thead>
<tbody><?php foreach($mounts as $m):?><tr><td><code><?php echo htmlspecialchars($m->mount);?></code></td><td><?php echo $m->bitrate;?> kbps</td><td><?php echo (int)$m->listener_count;?></td>
<td><a href="/user/radio/mount/delete/<?php echo $m->id;?>" class="btn btn-sm btn-danger">✕</a></td></tr><?php endforeach;?></tbody></table>
<?php endif;?></div></div>

<!-- Statistics -->
<div class="tab <?php echo $tab==='stats'?'active':'';?>">
<div class="r-card"><h3>📊 Listener Statistics</h3>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:12px">
<div style="text-align:center;padding:14px;background:rgba(8,16,28,.6);border-radius:8px"><div style="font-size:22px;font-weight:700;color:#0A84FF"><?php echo (int)($station->listener_count??0);?></div><div style="font-size:10px;color:#64748b">Current</div></div>
<div style="text-align:center;padding:14px;background:rgba(8,16,28,.6);border-radius:8px"><div style="font-size:22px;font-weight:700;color:#38bdf8"><?php echo (int)($station->listener_peak??0);?></div><div style="font-size:10px;color:#64748b">Peak</div></div>
<div style="text-align:center;padding:14px;background:rgba(8,16,28,.6);border-radius:8px"><div style="font-size:22px;font-weight:700;color:#a78bfa"><?php echo $station->bitrate??128;?>k</div><div style="font-size:10px;color:#64748b">Bitrate</div></div>
</div>
<p style="color:#64748b;font-size:12px">Detailed analytics available when stream is active.</p></div></div>

<!-- Backups -->
<div class="tab <?php echo $tab==='backups'?'active':'';?>">
<?php $backupDir = '/home/radio/' . $station->id; $backups = is_dir($backupDir) ? glob($backupDir . '/backup_*.tar.gz') : []; rsort($backups); ?>
<div class="r-card"><h3>💾 Backups</h3>
<a href="/user/radio/backup/create" class="btn btn-sm btn-primary" style="margin-bottom:10px;display:inline-block">📦 Create Backup</a>
<?php if (empty($backups)):?><p style="color:#64748b;font-size:12px;text-align:center;padding:10px">No backups yet.</p>
<?php else:?>
<table class="table"><thead><tr><th>Filename</th><th>Size</th><th>Date</th><th></th></tr></thead>
<tbody><?php foreach(array_slice($backups,0,10) as $bf): $bn=basename($bf); $sz=filesize($bf); $dt=date('M j Y',filemtime($bf)); ?>
<tr><td style="font-size:11px"><?php echo htmlspecialchars($bn);?></td>
<td><?php echo $sz>1048576?round($sz/1048576,1).'MB':round($sz/1024,1).'KB';?></td>
<td style="font-size:11px;color:#64748b"><?php echo $dt;?></td>
<td style="display:flex;gap:4px"><a href="/user/radio/backup/download?file=<?php echo urlencode($bn);?>" class="btn btn-sm btn-primary">⬇</a>
<a href="/user/radio/backup/delete?file=<?php echo urlencode($bn);?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">✕</a></td></tr>
<?php endforeach;?></tbody></table>
<?php endif;?></div></div>

<!-- Widgets -->
<div class="tab <?php echo $tab==='widgets'?'active':'';?>">
<div class="r-card"><h3>🧩 Widgets</h3>
<?php $h=$_SERVER['HTTP_HOST']??'localhost';$sp=$station->port??8000;$sm=$station->mount??'/stream';?>
<div style="margin-bottom:8px"><div style="font-size:11px;color:#64748b">Now Playing</div>
<textarea rows="2" style="width:100%;font-size:10px;font-family:monospace;padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#4ade80;outline:none" readonly>&lt;script src="http://<?php echo $h;?>/radio/widgets/nowplaying.php?stream=<?php echo $station->id;?>"&gt;&lt;/script&gt;</textarea></div>
<div style="margin-bottom:8px"><div style="font-size:11px;color:#64748b">Listener Count</div>
<textarea rows="2" style="width:100%;font-size:10px;font-family:monospace;padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#4ade80;outline:none" readonly>&lt;script src="http://<?php echo $h;?>/radio/widgets/listeners.php?stream=<?php echo $station->id;?>"&gt;&lt;/script&gt;</textarea></div>
<div style="margin-bottom:8px"><div style="font-size:11px;color:#64748b">HTML5 Player</div>
<textarea rows="2" style="width:100%;font-size:10px;font-family:monospace;padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#4ade80;outline:none" readonly>&lt;audio controls&gt;&lt;source src="http://<?php echo $h;?>:<?php echo $sp;?><?php echo $sm;?>" type="audio/mpeg"&gt;&lt;/audio&gt;</textarea></div>
</div></div>

<?php endif; ?>
