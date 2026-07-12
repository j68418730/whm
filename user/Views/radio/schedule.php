<style>
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:2px;margin-bottom:10px}
.cal-header{text-align:center;font-size:10px;color:#64748b;padding:4px;font-weight:600;text-transform:uppercase}
.cal-day{background:rgba(8,16,28,.6);border-radius:4px;padding:6px;min-height:60px;font-size:11px}
.cal-day .num{font-size:12px;font-weight:600;margin-bottom:4px}
.cal-day .event{background:rgba(0,140,255,.15);border-radius:3px;padding:2px 4px;margin-bottom:2px;font-size:9px;color:#0A84FF;cursor:pointer}
.cal-day .event:hover{background:rgba(0,140,255,.25)}
.cal-day.today{border:1px solid rgba(0,140,255,.3)}
</style>
<h2>📅 DJ Schedule</h2>
<p style="color:#64748b;margin-bottom:14px">View and manage your DJ show schedule.</p>
<?php
$streamId = $_GET['stream_id'] ?? ($streams[0]->id ?? 0);
$schedules = [];
if ($streamId) {
    try { $schedules = $this->db->table('radio_schedule')->where('stream_id', $streamId)->orderBy('start_time', 'ASC')->get() ?: []; } catch(\Exception $e) {}
}
$djs = [];
if ($streamId) {
    try { $djs = $this->db->table('radio_djs')->where('stream_id', $streamId)->where('status', 'active')->get() ?: []; } catch(\Exception $e) {}
}
?>
<div class="r-card"><h3>Add Show</h3>
<form method="POST" action="/user/radio/schedule/add" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px">
<input type="hidden" name="stream_id" value="<?php echo $streamId;?>">
<select name="dj_id" required style="padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<option value="">Select DJ</option>
<?php foreach($djs as $d):?><option value="<?php echo $d->id;?>"><?php echo htmlspecialchars($d->name ?: $d->username);?></option><?php endforeach;?>
</select>
<input name="show_name" placeholder="Show name" style="padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<input name="day_of_week" placeholder="Day (0=Sun,1=Mon...)" style="padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<div style="display:flex;gap:4px"><input name="start_time" type="time" style="flex:1;padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<input name="end_time" type="time" style="flex:1;padding:6px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:11px;outline:none">
<button type="submit" class="btn btn-sm btn-primary">➕ Add</button></div>
</form></div>

<div class="r-card"><h3>Schedule</h3>
<?php if (empty($schedules)): ?><p style="color:#64748b;font-size:12px">No shows scheduled.</p>
<?php else: ?>
<table class="table"><thead><tr><th>Day</th><th>Time</th><th>DJ</th><th>Show</th><th></th></tr></thead>
<tbody><?php $days=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
foreach($schedules as $sc):?>
<tr><td><?php echo $days[$sc->day_of_week??0]??'-';?></td>
<td><?php echo htmlspecialchars($sc->start_time??'');?> - <?php echo htmlspecialchars($sc->end_time??'');?></td>
<td><?php echo htmlspecialchars($sc->dj_name??'N/A');?></td>
<td><?php echo htmlspecialchars($sc->show_name??'');?></td>
<td><a href="/user/radio/schedule/delete/<?php echo $sc->id;?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">✕</a></td></tr>
<?php endforeach;?></tbody></table>
<?php endif;?></div>
