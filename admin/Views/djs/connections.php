<style>
table{width:100%;border-collapse:collapse;font-size:12px}
th{text-align:left;padding:10px 8px;color:#94a3b8;font-weight:600;border-bottom:1px solid rgba(255,255,255,.06)}
td{padding:10px 8px;border-bottom:1px solid rgba(255,255,255,.04)}
.badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600}
.badge-live{background:rgba(74,222,128,.12);color:#4ade80}
.badge-ended{background:rgba(100,116,139,.12);color:#94a3b8}
</style>

<h2 style="margin-bottom:6px">DJ Connection History</h2>
<p style="color:#64748b;font-size:13px;margin-bottom:20px">Recent DJ connections and disconnections.</p>

<div style="background:rgba(8,16,28,.5);border:1px solid rgba(56,189,248,.06);border-radius:12px;padding:20px">
<table>
<tr><th>DJ</th><th>Station</th><th>Connected</th><th>Disconnected</th><th>Duration</th><th>Reason</th><th>Status</th></tr>
<?php if (empty($connections)): ?>
<tr><td colspan="7" style="text-align:center;color:#64748b;padding:30px">No connections recorded yet.</td></tr>
<?php else: ?>
<?php foreach ($connections as $c): ?>
<?php $dur = $c->connected_at && $c->disconnected_at ? strtotime($c->disconnected_at) - strtotime($c->connected_at) : ($c->connected_at ? time() - strtotime($c->connected_at) : 0); ?>
<tr>
  <td><strong><?=htmlspecialchars($c->dj_name ?: $c->dj_username)?></strong></td>
  <td><?=htmlspecialchars($c->station_name)?></td>
  <td style="font-size:11px;color:#64748b"><?=$c->connected_at ? date('M j, g:ia', strtotime($c->connected_at)) : '—'?></td>
  <td style="font-size:11px;color:#64748b"><?=$c->disconnected_at ? date('M j, g:ia', strtotime($c->disconnected_at)) : '—'?></td>
  <td><?php if ($dur > 0) { $h = floor($dur/3600); $m = floor(($dur%3600)/60); $s = $dur%60; echo ($h?$h.'h ':'').($m?$m.'m ':'').$s.'s'; } else echo '—'; ?></td>
  <td style="font-size:11px;color:#64748b"><?=htmlspecialchars($c->disconnect_reason ?: '—')?></td>
  <td><span class="badge badge-<?=$c->disconnected_at ? 'ended' : 'live'?>"><?=$c->disconnected_at ? 'Ended' : 'Live'?></span></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>
</div>

<!-- Testacct Stream Debug -->
<div style="background:rgba(255,159,10,.06);border:1px solid rgba(255,159,10,.12);border-radius:12px;padding:16px;margin-top:16px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
<h3 style="margin:0;font-size:14px;color:#f97316">🔧 testacct Stream Debug</h3>
<div><span id="admin-debug-timer" style="font-size:10px;color:#64748b">Auto-refresh in 5s</span></div>
</div>
<div id="admin-debug-panel" style="font-size:12px;line-height:1.8;font-family:monospace">
<div style="color:#64748b">Loading testacct stream data...</div>
</div>
</div>
<script>
(function(){
var timer = 5;
function loadAdminDebug(){
var x = new XMLHttpRequest();
var sid = 12; // testacct station
x.open('GET', '/api/stream-debug.php?station='+sid, true);
x.onload = function(){
if(x.status === 200){
try{
var d = JSON.parse(x.responseText);
var h = '';
h += '<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:10px">';
h += '<div style="background:rgba(0,0,0,.3);border-radius:6px;padding:8px;text-align:center"><div style="font-size:14px;font-weight:700;color:'+(d.status==="running"?"#4ade80":"#f87171")+'">'+d.status+'</div><div style="font-size:8px;color:#64748b">Station</div></div>';
h += '<div style="background:rgba(0,0,0,.3);border-radius:6px;padding:8px;text-align:center"><div style="font-size:14px;font-weight:700;color:'+(d.autodj_running?"#4ade80":"#f87171")+'">'+(d.autodj_running?"Running":"Stopped")+'</div><div style="font-size:8px;color:#64748b">AutoDJ</div></div>';
h += '<div style="background:rgba(0,0,0,.3);border-radius:6px;padding:8px;text-align:center"><div style="font-size:14px;font-weight:700;color:'+(d.source_connected?"#4ade80":"#f87171")+'">'+(d.source_connected?"OK":"OFF")+'</div><div style="font-size:8px;color:#64748b">Source</div></div>';
h += '<div style="background:rgba(0,0,0,.3);border-radius:6px;padding:8px;text-align:center"><div style="font-size:14px;font-weight:700;color:'+(d.proxy_reachable?"#4ade80":"#f87171")+'">'+(d.proxy_reachable?d.proxy_response_ms+"ms":"DOWN")+'</div><div style="font-size:8px;color:#64748b">Proxy</div></div>';
h += '</div>';
h += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:8px">';
h += '<div style="background:rgba(0,0,0,.2);border-radius:6px;padding:8px;font-size:10px"><div><span style="color:#64748b">Song:</span> <span style="color:#facc15">'+escapeHtml(d.current_song||"—")+'</span></div><div><span style="color:#64748b">Listeners:</span> <span style="color:#4ade80">'+d.listeners+'</span></div><div><span style="color:#64748b">PID:</span> <span style="color:#94a3b8">'+(d.autodj_pid||"—")+'</span></div></div>';
h += '<div style="background:rgba(0,0,0,.2);border-radius:6px;padding:8px;font-size:10px">';
if(d.connections && d.connections.length){
d.connections.forEach(function(c){
h += '<div>'+escapeHtml(c.dj)+" — "+(c.duration?Math.floor(c.duration/60)+"m":"active")+(c.reason?" ("+c.reason+")":"")+'</div>';
});
}else{ h += '<div style="color:#64748b">No connections</div>'; }
h += '</div></div>';
h += '<div style="font-size:9px;color:#64748b">'+d.timestamp+'</div>';
document.getElementById("admin-debug-panel").innerHTML = h;
}catch(e){ document.getElementById("admin-debug-panel").innerHTML = '<div style="color:#f87171">Parse error</div>'; }
}else{
document.getElementById("admin-debug-panel").innerHTML = '<div style="color:#f87171">HTTP '+x.status+'</div>';
}
};
x.onerror = function(){ document.getElementById("admin-debug-panel").innerHTML = '<div style="color:#f87171">Error</div>'; };
x.send();
timer = 5;
var tel = document.getElementById("admin-debug-timer");
if(tel) tel.textContent = "Auto-refresh in "+timer+"s";
}
function escapeHtml(t){var d=document.createElement("div");d.textContent=t;return d.innerHTML;}
loadAdminDebug();
setInterval(function(){
timer--;
var tel = document.getElementById("admin-debug-timer");
if(tel) tel.textContent = "Auto-refresh in "+timer+"s";
if(timer <= 0){ loadAdminDebug(); }
}, 1000);
})();
</script>
