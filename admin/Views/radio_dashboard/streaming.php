<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px">
    <h2 style="margin:0;color:var(--accent)">Streaming Engine</h2>
    <button type="button" class="btn primary" onclick="document.getElementById('createStationForm').scrollIntoView({behavior:'smooth',block:'center'});document.getElementById('createStationForm').querySelector('input,select')?.focus();">+ Create Station</button>
</div>

<div class="stats-grid" style="margin-bottom:16px;grid-template-columns:repeat(auto-fit,minmax(140px,1fr))">
<div class="stat-card"><h3>Total Stations</h3><div class="value"><?php echo $totalStations; ?></div></div>
<div class="stat-card"><h3>Running</h3><div class="value" style="color:#4ade80"><?php echo $runningStations; ?></div></div>
<div class="stat-card"><h3>Engines</h3><div class="value" style="font-size:16px">
<?php foreach ($engines as $e): ?><span style="color:<?php echo $e['installed'] ? '#4ade80' : '#64748b'; ?>"><?php echo $e['name']; ?></span> <?php endforeach; ?>
</div></div>
</div>

<!-- Engine Status -->
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">Streaming Engines</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:12px">
<?php foreach ($engines as $key => $e): ?>
<div style="background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.08);border-radius:8px;padding:16px">
<div style="font-size:18px;font-weight:600;margin-bottom:4px"><?php echo htmlspecialchars($e['name']); ?></div>
<div style="font-size:12px;color:#64748b;margin-bottom:8px">
Status: <strong style="color:<?php echo $e['installed'] ? '#4ade80' : '#f87171'; ?>"><?php echo $e['installed'] ? 'Installed' : 'Not installed'; ?></strong>
<?php if ($e['installed']): ?> · v<?php echo htmlspecialchars($e['version']); ?><?php endif; ?>
</div>
<?php if (!$e['installed']): ?>
<form method="POST" action="/admin/api/streaming/install" style="display:inline">
<input type="hidden" name="engine" value="<?php echo $key; ?>">
<button type="submit" class="btn btn-sm primary" onclick="return confirm('Install <?php echo $e['name']; ?>?')">Install</button>
</form>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- Create Station -->
<div class="card" style="margin-bottom:16px;max-width:600px">
<form method="POST" action="/admin/api/streaming/stations/create" id="createStationForm">
<h3 style="color:var(--accent);margin-bottom:12px">Create Station</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="form-group"><label>Engine</label>
<select name="engine">
<option value="shoutcast">SHOUTcast v2</option>
<option value="shoutcast1">SHOUTcast v1</option>
<option value="icecast">Icecast</option>
</select></div>
<div class="form-group"><label>Station Name</label>
<input name="name" placeholder="My Radio Station" required></div>
<div class="form-group"><label>User</label>
<select name="user_id" required>
<option value="">Select user...</option>
<?php foreach ($users as $u): ?>
<option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->username); ?> (<?php echo htmlspecialchars($u->email); ?>)</option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Package</label>
<select name="package_id">
<option value="">Select package...</option>
<?php foreach ($packages as $p): ?>
<option value="<?php echo $p->id; ?>"><?php echo htmlspecialchars($p->name); ?> ($<?php echo $p->monthly_price; ?>/mo)</option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Bitrate</label>
<select name="bitrate">
<option value="64">64 kbps</option>
<option value="96">96 kbps</option>
<option value="128" selected>128 kbps</option>
<option value="192">192 kbps</option>
<option value="256">256 kbps</option>
<option value="320">320 kbps</option>
</select></div>
<div class="form-group"><label>Max Listeners</label>
<input name="max_listeners" type="number" value="100"></div>
<div class="form-group"><label>Format</label>
<select name="format">
<option value="mp3" selected>MP3</option>
<option value="aac">AAC</option>
<option value="ogg">OGG</option>
</select></div>
</div>
<div class="form-group" style="margin-top:8px">
<label style="display:flex;align-items:center;gap:8px;cursor:pointer">
<input type="checkbox" name="public_server" value="1">
<span>Public server (list on SHOUTcast directory)</span>
</label>
</div>
<button type="submit" class="btn primary">Create Station</button>
</form>
</div>

<script>
document.getElementById('createStationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var data = new FormData(form);
    fetch('/admin/api/streaming/stations/create', {method:'POST', body:data})
    .then(function(r){return r.json()})
    .then(function(d){
        if (d.success) {
            alert('Station created! Port: ' + d.station.port + ', Password: ' + d.station.password);
            location.reload();
        } else {
            alert('Error: ' + (d.error || 'Unknown'));
        }
    }).catch(function(){alert('Request failed');});
});
</script>

<!-- Stations Table -->
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Stations</h3>
<?php if (!empty($stations)): ?>
<table>
<thead><tr><th>ID</th><th>Name</th><th>Engine</th><th>Port</th><th>User</th><th>Bitrate</th><th>Listeners</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($stations as $s): ?>
<tr>
<td><?php echo $s->id; ?></td>
<td><?php echo htmlspecialchars($s->name); ?></td>
<td><?php echo htmlspecialchars($s->engine); ?></td>
<td><?php echo $s->port; ?></td>
<td><?php echo $s->user_id; ?></td>
<td><?php echo $s->bitrate; ?>k</td>
<td><?php echo $s->listener_count; ?></td>
<td><span class="status-badge status-<?php echo $s->status === 'running' ? 'active' : ($s->status === 'error' ? 'terminated' : 'pending'); ?>"><?php echo $s->status; ?></span></td>
<td>
<button class="btn btn-sm secondary" onclick="stationAction(<?php echo $s->id; ?>, 'start')">Start</button>
<button class="btn btn-sm secondary" onclick="stationAction(<?php echo $s->id; ?>, 'stop')">Stop</button>
<button class="btn btn-sm secondary" onclick="stationAction(<?php echo $s->id; ?>, 'restart')">Restart</button>
<button class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171" onclick="if(confirm('Delete station?'))stationAction(<?php echo $s->id; ?>, 'delete')">Delete</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p style="color:#64748b">No stations yet. Create one above.</p>
<?php endif; ?>
</div>

<script>
function stationAction(id, action) {
    var f = new FormData();
    f.append('id', id);
    f.append('action', action);
    fetch('/admin/api/streaming/stations/action', {method:'POST', body:f})
    .then(function(r){return r.json()})
    .then(function(d){
        if (d.success) location.reload();
        else alert('Error: ' + (d.error || 'Failed'));
    }).catch(function(){alert('Request failed');});
}
</script>
