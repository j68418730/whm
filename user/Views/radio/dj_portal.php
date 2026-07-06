<style>
.portal-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-bottom:16px}
.portal-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:14px;text-align:center}
.portal-stat .num{font-size:22px;font-weight:800;color:var(--accent)}
.portal-stat .lbl{font-size:10px;color:#64748b;margin-top:2px}
.req-item{display:flex;justify-content:space-between;padding:8px;border-bottom:1px solid rgba(255,255,255,.04);font-size:12px}
.req-item:last-child{border:none}
</style>
<h2>🎤 DJ Portal</h2>
<p style="color:#64748b;margin-bottom:14px">Welcome, <?php echo htmlspecialchars($dj->name ?? $dj->username); ?>!</p>
<div class="portal-grid">
<div class="portal-stat"><div class="num"><?php echo $station->status ?? 'N/A';?></div><div class="lbl">Station</div></div>
<div class="portal-stat"><div class="num"><?php echo $station->listener_count ?? 0;?></div><div class="lbl">Listeners</div></div>
<div class="portal-stat"><div class="num"><?php echo count($requests);?></div><div class="lbl">Requests</div></div>
<div class="portal-stat"><div class="num"><?php echo count($schedule);?></div><div class="lbl">Shows</div></div>
</div>
<div class="card"><h3>📋 Pending Requests (<?php echo count($requests);?>)</h3>
<?php if (empty($requests)):?><p style="color:#64748b;text-align:center;padding:10px;font-size:12px">No pending requests.</p>
<?php else: foreach($requests as $r):?>
<div class="req-item"><span><strong><?php echo htmlspecialchars($r->title);?></strong> - <?php echo htmlspecialchars($r->requester_name ?? 'Anonymous');?></span>
<a href="/user/radio/request/approve/<?php echo $r->id;?>" class="btn btn-sm btn-success">Approve</a></div>
<?php endforeach; endif;?></div>
<div class="card"><h3>📅 My Schedule</h3>
<?php if (empty($schedule)):?><p style="color:#64748b;text-align:center;padding:10px;font-size:12px">No shows scheduled.</p>
<?php else: $days=['Sun','Mon','Tue','Wed','Thu','Fri','Sat']; foreach($schedule as $sc):?>
<div style="padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:12px">
<strong><?php echo htmlspecialchars($sc->show_name);?></strong> - <?php echo $days[$sc->day_of_week]??'?';?> <?php echo htmlspecialchars($sc->start_time??'');?>-<?php echo htmlspecialchars($sc->end_time??'');?></div>
<?php endforeach; endif;?></div>
<div class="card" style="text-align:center"><h3>🔌 Stream Info</h3>
<p style="font-size:12px">Server: <code><?php echo $_SERVER['HTTP_HOST']??'localhost';?></code><br>
Port: <code><?php echo $station->port??'N/A';?></code><br>
Mount: <code><?php echo $station->mount??'/stream';?></code><br>
Source User: <code><?php echo htmlspecialchars($dj->username);?></code></p>
<a href="/dj/logout" class="btn btn-sm btn-danger">Logout</a>
</div>
