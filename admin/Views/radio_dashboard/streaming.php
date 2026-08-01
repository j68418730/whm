<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px">
    <h2 style="margin:0;color:var(--accent)">Streaming Engine</h2>
    <button type="button" class="btn" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;font-weight:600" onclick="document.getElementById('createStationForm').scrollIntoView({behavior:'smooth',block:'center'});document.getElementById('createStationForm').querySelector('input,select')?.focus();">+ Create Station</button>
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

<!-- Package Versions -->
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">🛠️ Streaming Package Versions</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px">
<?php foreach ($serverVersions as $key => $sv): ?>
<div style="background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;border-top:3px solid <?php echo $sv['up_to_date'] ? '#4ade80' : '#facc15'; ?>">
<div style="font-size:16px;font-weight:600;margin-bottom:6px">
<?php echo htmlspecialchars($sv['name']); ?>
<span style="font-size:11px;padding:2px 8px;border-radius:10px;font-weight:700;margin-left:6px;background:<?php echo $sv['up_to_date'] ? 'rgba(74,222,128,.15)' : 'rgba(250,204,21,.15)'; ?>;color:<?php echo $sv['up_to_date'] ? '#4ade80' : '#eab308'; ?>">
<?php echo $sv['up_to_date'] ? '✓ Up to date' : '⚠ Update available'; ?>
</span>
</div>
<div style="font-size:12px;color:#64748b;line-height:1.8">
<div>Installed: <strong style="color:#e0e0e0"><?php echo htmlspecialchars($sv['version']); ?></strong></div>
<div>Latest: <strong style="color:#e0e0e0"><?php echo htmlspecialchars($sv['latest']); ?></strong></div>
<?php if (!$sv['up_to_date']): ?>
<div style="margin-top:8px;padding:8px;background:rgba(250,204,21,.06);border:1px solid rgba(250,204,21,.2);border-radius:6px;font-size:11px;color:#eab308">
🔔 New version <?php echo htmlspecialchars($sv['latest']); ?> is available.
<?php if ($sv['update_file']): ?><br>Update downloaded: <code style="color:#94a3b8"><?php echo htmlspecialchars($sv['update_file']); ?></code>
<form method="POST" action="/admin/api/streaming/update-package" style="display:inline;margin-top:6px">
<input type="hidden" name="package" value="<?php echo $key; ?>">
<button type="submit" class="btn btn-sm primary" onclick="return confirm('Install <?php echo htmlspecialchars($sv['latest']); ?> update? Server will restart.')" style="margin-top:6px">🚀 Install Update</button>
</form><?php endif; ?>
</div>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- Create Station -->
<div class="card" style="margin-bottom:16px">
<form method="POST" action="/admin/api/streaming/stations/create" id="createStationForm">
<div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,.06)">
<div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,rgba(0,140,255,.2),rgba(168,85,247,.15));display:flex;align-items:center;justify-content:center;font-size:18px">📡</div>
<div><h3 style="color:var(--accent);margin:0;font-size:15px">Create New Station</h3><div style="font-size:11px;color:#64748b">Add a new streaming station for a customer</div></div>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px">
<div class="form-group"><label>Streaming Engine</label>
<select name="engine" id="selEngine" onchange="engineHint()">
<option value="shoutcast">SHOUTcast v2</option>
<option value="shoutcast1">SHOUTcast v1</option>
<option value="icecast">Icecast</option>
</select>
<div id="engineHint" style="font-size:10px;color:#64748b;margin-top:3px"></div></div>
<div class="form-group"><label>Station Name</label>
<input name="name" placeholder="e.g. Cool Radio" required></div>
<div class="form-group"><label>Customer Account</label>
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
<input name="max_listeners" type="number" value="100" min="1"></div>
<div class="form-group"><label>Format</label>
<select name="format">
<option value="mp3" selected>MP3</option>
<option value="aac">AAC</option>
<option value="ogg">OGG</option>
</select></div>
</div>
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:14px;flex-wrap:wrap">
<label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:12px;color:#c0c0c0">
<input type="checkbox" name="public_server" value="1">
<span>🌍 Public server (list on SHOUTcast directory)</span>
</label>
<button type="submit" class="btn primary" style="padding:10px 24px;font-weight:600"><span id="createBtnIcon">➕</span> Create Station</button>
</div>
</form>
</div>

<script>
function engineHint() {
    var e = document.getElementById('selEngine').value;
    var hints = {
        'shoutcast': 'SHOUTcast DNAS v2 — modern streaming, good for MP3/AAC',
        'shoutcast1': 'SHOUTcast DNAS v1 — classic lightweight streaming',
        'icecast': 'Icecast 2 — open source, multi-format, mounts'
    };
    document.getElementById('engineHint').textContent = hints[e] || '';
}
engineHint();
</script>

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
