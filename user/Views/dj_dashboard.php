<style>
.dj-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:16px}
.dj-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:14px;text-align:center}
.dj-stat .num{font-size:22px;font-weight:800}
.dj-stat .lbl{font-size:10px;color:#64748b}
.dj-connect{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:20px;margin-bottom:14px}
.dj-connect h3{margin:0 0 8px;font-size:15px}
.dj-info{display:grid;grid-template-columns:140px 1fr;gap:4px;font-size:12px;margin:10px 0}
.dj-info .lbl{color:#64748b}
code{background:rgba(0,0,0,.4);padding:2px 6px;border-radius:4px;font-size:11px}
</style>
<h2>🎤 DJ Panel</h2>
<p style="color:#64748b;margin-bottom:14px">Connect as a DJ to your radio stream and manage your broadcast.</p>
<?php
$streams = $streams ?? [];
$djStream = $streams[0] ?? null;
if (!$djStream): ?>
<div class="card" style="text-align:center;padding:30px"><h3>No Stream</h3><p style="color:#64748b">No radio streams available. Contact support.</p></div>
<?php else:
$djs = [];
try { $djs = $this->db->table('radio_djs')->where('stream_id', $djStream->id)->get() ?: []; } catch(\Exception $e) {}
$djUser = $djs[0] ?? null;
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$port = $djStream->port ?? 8000;
?>
<div class="r-grid">
<div class="dj-stat"><div class="num" style="color:<?php echo $djStream->status==='running'?'#4ade80':'#64748b';?>"><?php echo $djStream->status==='running'?'Live':'Offline';?></div><div class="lbl">Stream Status</div></div>
<div class="dj-stat"><div class="num" style="color:#0A84FF"><?php echo (int)($djStream->listener_count??0);?></div><div class="lbl">Listeners</div></div>
<div class="dj-stat"><div class="num" style="color:#a78bfa"><?php echo count($djs);?></div><div class="lbl">DJ Accounts</div></div>
<div class="dj-stat"><div class="num" style="color:#38bdf8"><?php echo $port;?></div><div class="lbl">Port</div></div>
</div>

<div class="dj-connect">
<h3>🔌 Connect as DJ</h3>
<p style="font-size:12px;color:#64748b;margin-bottom:10px">Use these settings in your broadcasting software (Mixxx, OBS, BUTT, etc.)</p>
<div class="dj-info">
<span class="lbl">Server</span><span><code><?php echo $host;?></code></span>
<span class="lbl">Port</span><span><code><?php echo $port;?></code></span>
<span class="lbl">Mount</span><span><code>/stream</code></span>
<span class="lbl">Username</span><span><code>source</code></span>
<span class="lbl">Password</span><span><code><?php echo htmlspecialchars($djStream->password ?? 'changeme');?></code></span>
</div>
<button class="btn btn-sm btn-primary" onclick="navigator.clipboard.writeText('Server: <?php echo $host;?>\nPort: <?php echo $port;?>\nMount: /stream\nUser: source\nPass: <?php echo htmlspecialchars($djStream->password ?? 'changeme');?>')">📋 Copy Connection Details</button>
</div>

<div class="r-card"><h3>🔌 Source Control</h3>
<button class="btn btn-sm" style="background:rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.2);padding:6px 14px;border-radius:6px;cursor:pointer" onclick="kickSource(<?php echo $djStream->id;?>)">⛔ Kick Current Source</button>
<span id="kickResult" style="font-size:11px;margin-left:8px;color:#64748b"></span>
</div>

<div class="r-card"><h3>🎧 DJ Accounts <span>(<?php echo count($djs);?>)</span></h3>
<?php if (empty($djs)): ?>
<p style="color:#64748b;font-size:12px">No DJ accounts. <a href="/user/dj-manager" style="color:#0A84FF">Create one →</a></p>
<?php else: ?>
<table class="table"><thead><tr><th>DJ Name</th><th>Username</th><th>Last Login</th><th>Status</th></tr></thead>
<tbody><?php foreach($djs as $d): ?>
<tr><td><?php echo htmlspecialchars($d->name ?? $d->username);?></td>
<td><code><?php echo htmlspecialchars($d->username);?></code></td>
<td><?php echo htmlspecialchars($d->last_login ?? 'Never');?></td>
<td><span style="color:<?php echo $d->status==='active'?'#4ade80':'#64748b';?>">● <?php echo $d->status ?? 'active';?></span></td></tr>
<?php endforeach;?></tbody></table>
<?php endif;?>
</div>

<div class="r-card"><h3>▶ Stream Player</h3>
<audio controls style="width:100%;margin:8px 0"><source src="http://<?php echo $host;?>:<?php echo $port;?>/stream" type="audio/mpeg"></audio>
</div>

<div style="display:flex;gap:8px;flex-wrap:wrap">
<a href="/user/radio" class="btn btn-sm" style="background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);padding:6px 14px;border-radius:6px;text-decoration:none;font-size:12px">📻 Radio Dashboard</a>
<a href="/user/dj-manager" class="btn btn-sm" style="background:rgba(168,85,247,.1);color:#a855f7;border:1px solid rgba(168,85,247,.2);padding:6px 14px;border-radius:6px;text-decoration:none;font-size:12px">🎤 DJ Manager</a>
</div>

<script>
function kickSource(streamId) {
    if (!confirm('Kick the current source/DJ from the stream?')) return;
    var el = document.getElementById('kickResult');
    if (!el) return;
    el.textContent = '⏳ Kicking...';
    el.style.color = '#facc15';
    var x = new XMLHttpRequest();
    x.open('POST', '/user/radio/kick-source', true);
    x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    x.onload = function() {
        try {
            var d = JSON.parse(x.responseText);
            el.textContent = d.success ? '✅ Source kicked!' : '❌ ' + (d.error || 'Failed');
            el.style.color = d.success ? '#4ade80' : '#f87171';
        } catch(e) { el.textContent = '❌ Error'; el.style.color = '#f87171'; }
    };
    x.onerror = function() { el.textContent = '❌ Connection error'; el.style.color = '#f87171'; };
    x.send('stream_id=' + streamId);
}
</script>

<?php endif; ?>
