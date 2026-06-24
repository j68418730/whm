<style>
.r-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:10px;margin-bottom:14px}
.r-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:14px;text-align:center}
.r-stat .num{font-size:22px;font-weight:800}
.r-stat .lbl{font-size:10px;color:#64748b;margin-top:2px}
.r-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:18px;margin-bottom:12px}
.r-card h3{font-size:14px;font-weight:600;margin:0 0 10px}
.nav-pills{display:flex;gap:2px;flex-wrap:wrap;margin-bottom:14px;background:rgba(8,16,28,.6);border-radius:8px;padding:3px}
.nav-pills a{padding:6px 12px;border-radius:6px;font-size:11px;text-decoration:none;color:#94a3b8;transition:.1s;white-space:nowrap}
.nav-pills a:hover{color:#e0e0e0;background:rgba(255,255,255,.04)}
.nav-pills a.active{color:#fff;background:rgba(0,140,255,.2)}
.tab{display:none}
.tab.active{display:block}
.nowplaying{display:flex;align-items:center;gap:14px;padding:14px;background:linear-gradient(135deg,rgba(0,140,255,.06),rgba(168,85,247,.04));border:1px solid rgba(0,191,255,.1);border-radius:12px;margin-bottom:14px}
</style>
<?php $tab = $_GET["tab"] ?? "overview"; ?>
<h2>≡ƒô╗ Radio Dashboard</h2>
<p style="color:#64748b;margin-bottom:14px">Manage your station, DJs, music, and listeners.</p>

<?php if (!$station): ?>
<div class="r-card" style="text-align:center;padding:30px"><h3>No Station</h3><p style="color:#64748b">Create your radio station to get started.</p>
<form method="POST" action="/radio/setup" style="margin-top:10px"><button type="submit" class="btn btn-sm btn-primary">Create Station</button></form></div>
<?php else: ?>

<div class="nowplaying">
<div style="font-size:36px">≡ƒô╗</div>
<div style="flex:1"><strong style="font-size:16px"><?php echo htmlspecialchars($station->name ?? "My Station"); ?></strong>
<div style="font-size:12px;color:#64748b"><?php echo htmlspecialchars($station->current_song ?? "Not Playing"); ?> &bull; <?php echo $station->status === "running" ? "<span style=\"color:#4ade80\">ΓùÅ Live</span>" : "<span style=\"color:#64748b\">ΓùÅ Offline</span>"; ?></div></div>
<div style="text-align:right;font-size:12px;color:#64748b">Listeners: <strong><?php echo (int)($station->listener_count ?? 0); ?></strong><br>Peak: <strong><?php echo (int)($station->listener_peak ?? 0); ?></strong></div></div>

<div class="r-grid">
<div class="r-stat"><div class="num" style="color:#0A84FF"><?php echo count($djs ?? []);?></div><div class="lbl">DJs</div></div>
<div class="r-stat"><div class="num" style="color:#4ade80"><?php echo count($requests ?? []);?></div><div class="lbl">Requests</div></div>
<div class="r-stat"><div class="num" style="color:#a78bfa"><?php echo count($schedule ?? []);?></div><div class="lbl">Shows</div></div>
<div class="r-stat"><div class="num" style="color:#38bdf8"><?php echo $station->listener_peak ?? 0;?></div><div class="lbl">Peak</div></div>
</div>

<div class="nav-pills">
<a href="?tab=overview" class="<?php echo $tab==="overview"?"active":"";?>">≡ƒôè Overview</a>
<a href="?tab=djs" class="<?php echo $tab==="djs"?"active":"";?>">≡ƒÄº DJs</a>
<a href="?tab=mods" class="<?php echo $tab==="mods"?"active":"";?>">≡ƒ¢í∩╕Å Mods</a>
<a href="?tab=schedule" class="<?php echo $tab==="schedule"?"active":"";?>">≡ƒôà Schedule</a>
<a href="?tab=requests" class="<?php echo $tab==="requests"?"active":"";?>">≡ƒÖï Requests</a>
<a href="?tab=media" class="<?php echo $tab==="media"?"active":"";?>">≡ƒÄ╢ Media</a>
<a href="?tab=playlists" class="<?php echo $tab==="playlists"?"active":"";?>">≡ƒôé Playlists</a>
<a href="?tab=mounts" class="<?php echo $tab==="mounts"?"active":"";?>">≡ƒöù Mounts</a>
<a href="?tab=bans" class="<?php echo $tab==="bans"?"active":"";?>">≡ƒÜ½ Bans</a>
<a href="?tab=widgets" class="<?php echo $tab==="widgets"?"active":"";?>">≡ƒº⌐ Widgets</a>
<a href="?tab=pages" class="<?php echo $tab==="pages"?"active":"";?>">≡ƒôä Pages</a>
<a href="?tab=stats" class="<?php echo $tab==="stats"?"active":"";?>">≡ƒôè Stats</a>
<a href="?tab=backups" class="<?php echo $tab==="backups"?"active":"";?>">≡ƒÆ╛ Backups</a>
<a href="?tab=chat" class="<?php echo $tab==="chat"?"active":"";?>">≡ƒÆ¼ Chat</a>
<a href="?tab=logs" class="<?php echo $tab==="logs"?"active":"";?>">≡ƒôï Logs</a>
</div>

<div class="tab <?php echo $tab==="overview"?"active":"";?>">
<div style="margin-bottom:10px"><a href="/radio/setup-wizard" class="btn btn-primary">≡ƒÜÇ Launch Setup Wizard</a></div>
<div class="r-card"><h3>Stream Controls</h3><div style="display:flex;gap:6px;flex-wrap:wrap">
<a href="/radio/start/<?php echo $station->id;?>" class="btn btn-sm btn-success">Γû╢ Start</a>
<a href="/radio/stop/<?php echo $station->id;?>" class="btn btn-sm btn-danger">ΓÅ╣ Stop</a>
<a href="/radio/restart/<?php echo $station->id;?>" class="btn btn-sm btn-warning">≡ƒöä Restart</a>
<a href="/radio/autodj/toggle/<?php echo $station->id;?>" class="btn btn-sm <?php echo $station->autodj_enabled?"btn-warning":"btn-primary";?>"><?php echo $station->autodj_enabled?"ΓÅ╣ AutoDJ On":"Γû╢ AutoDJ Off";?></a>
<button class="btn btn-sm btn-secondary" onclick="kickSource(<?php echo $station->id;?>)">≡ƒöî Kick Source</button>
</div></div>
<div class="r-card"><h3>Stream Info</h3><p style="font-size:12px;color:#94a3b8">
Server: <code><?php echo parse_url("http://".$_SERVER["HTTP_HOST"], PHP_URL_HOST) ?: "localhost";?>:<?php echo $station->port ?? "8000";?></code><br>
Port: <code><?php echo $station->port ?? "N/A";?></code><br>
Mount: <code>/stream<?php echo $station->id ?? "";?></code><br>
Source User: <code>source</code><br>
Source Password: <code><?php echo htmlspecialchars($station->password ?? "N/A");?></code><br>
Admin Password: <code><?php echo htmlspecialchars($station->admin_password ?? "admin");?></code><br>
Status: <?php echo $station->status === "running" ? "<span style=\"color:#4ade80\">ΓùÅ Running</span>" : "<span style=\"color:#64748b\">ΓùÅ ".ucfirst($station->status ?? "stopped")."</span>"; ?>
</p></div></div>

<div class="tab <?php echo $tab==="stats"?"active":"";?>">
<div class="r-card"><h3>Listener Statistics</h3><div class="r-grid">
<div class="r-stat"><div class="num" style="color:#4ade80"><?php echo (int)($station->listener_count ?? 0);?></div><div class="lbl">Current</div></div>
<div class="r-stat"><div class="num" style="color:#38bdf8"><?php echo (int)($station->listener_peak ?? 0);?></div><div class="lbl">Peak</div></div>
<div class="r-stat"><div class="num" style="color:#a78bfa"><?php echo (int)($station->bitrate ?? 0);?></div><div class="lbl">Bitrate</div></div>
</div></div></div>

<div class="tab <?php echo $tab==="logs"?"active":"";?>">
<div class="r-card"><h3>Activity Log</h3>
<?php if (empty($logs)): ?><p style="color:#64748b;text-align:center;padding:10px;font-size:12px">No logs yet.</p>
<?php else: ?><table class="table"><thead><tr><th>Action</th><th>Details</th><th>User</th><th>Time</th></tr></thead><tbody>
<?php foreach ($logs as $l): ?><tr><td><?php echo htmlspecialchars($l->action);?></td><td><?php echo htmlspecialchars($l->details??"");?></td><td><?php echo htmlspecialchars($l->username??"");?></td><td style="font-size:11px"><?php echo htmlspecialchars($l->created_at??"");?></td></tr>
<?php endforeach; ?></tbody></table><?php endif; ?>
</div></div>

<?php $tabFiles = ["djs","mods","schedule","requests","media","playlists","mounts","bans","widgets","pages","backups","chat"]; if (in_array($tab, $tabFiles) && file_exists(__DIR__."/tabs/{$tab}.php")) { echo "<div class=\"tab active\">"; include __DIR__."/tabs/{$tab}.php"; echo "</div>"; } ?>

<script>function kickSource(id){fetch("/radio/kick-source",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:"station_id="+id}).then(r=>r.json()).then(d=>alert(d.success?"Source kicked!":"Error: "+d.error)).catch(e=>alert("Failed"));}</script>
<?php endif; ?>
