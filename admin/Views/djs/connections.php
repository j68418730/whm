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
setInterval(function(){
var el=document.getElementById('admin-debug-panel');
if(!el)return;
var x=new XMLHttpRequest();
x.open('GET','/api/stream-debug.php?station=12',true);
x.onload=function(){
if(x.status!==200){el.innerHTML='<div style="color:#f87171">HTTP '+x.status+'</div>';return;}
try{
var d=JSON.parse(x.responseText);
var chk=d.autodj_running&&d.source_connected&&d.proxy_reachable;
var uc=d.active_upstreams>0?'#4ade80':'#f87171';
var cc=d.active_clients>0?'#4ade80':'#94a3b8';
var h='<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;margin-bottom:4px">';
h+='<div style="background:rgba(0,0,0,.3);border-radius:3px;padding:3px;text-align:center"><div style="font-size:10px;font-weight:700;color:'+(d.status==='running'?'#4ade80':'#f87171')+'">'+(d.status||'?')+'</div><div style="font-size:6px;color:#64748b">Stn</div></div>';
h+='<div style="background:rgba(0,0,0,.3);border-radius:3px;padding:3px;text-align:center"><div style="font-size:10px;font-weight:700;color:'+(d.autodj_running?'#4ade80':'#f87171')+'">'+(d.autodj_running?'Run':'Stop')+'</div><div style="font-size:6px;color:#64748b">AutoDJ</div></div>';
h+='<div style="background:rgba(0,0,0,.3);border-radius:3px;padding:3px;text-align:center"><div style="font-size:10px;font-weight:700;color:'+(d.source_connected?'#4ade80':'#f87171')+'">'+(d.source_connected?'OK':'OFF')+'</div><div style="font-size:6px;color:#64748b">Src</div></div>';
h+='<div style="background:rgba(0,0,0,.3);border-radius:3px;padding:3px;text-align:center"><div style="font-size:10px;font-weight:700;color:'+uc+'">'+(d.active_upstreams||0)+'</div><div style="font-size:6px;color:#64748b">Upstr</div></div>';
h+='<div style="background:rgba(0,0,0,.3);border-radius:3px;padding:3px;text-align:center"><div style="font-size:10px;font-weight:700;color:'+cc+'">'+(d.active_clients||0)+'</div><div style="font-size:6px;color:#64748b">Client</div></div>';
h+='<div style="background:rgba(0,0,0,.3);border-radius:3px;padding:3px;text-align:center"><div style="font-size:10px;font-weight:700;color:'+(d.proxy_reachable?'#4ade80':'#f87171')+'">'+(d.proxy_reachable?d.proxy_response_ms+'ms':'DOWN')+'</div><div style="font-size:6px;color:#64748b">Proxy</div></div>';
h+='<div style="background:rgba(0,0,0,.3);border-radius:3px;padding:3px;text-align:center"><div style="font-size:10px;font-weight:700;color:'+(d.listener_running?'#4ade80':'#f87171')+'">'+(d.listener_running?'ON':'OFF')+'</div><div style="font-size:6px;color:#64748b">Listnr</div></div>';
h+='</div><div style="display:grid;grid-template-columns:1fr 1fr;gap:3px">';
h+='<div style="background:rgba(0,0,0,.2);border-radius:4px;padding:5px;font-size:9px">';
h+='<div><span style="color:#64748b">Song:</span> <span style="color:#facc15">'+(d.current_song||'—')+'</span></div>';
h+='<div><span style="color:#64748b">Listeners:</span> <span style="color:#4ade80">'+(d.listeners||0)+'</span></div>';
h+='<div><span style="color:#64748b">Ports:</span> <span style="color:#94a3b8">DJ:'+(d.dj_port||'—')+' Src:'+(d.src_port||'—')+'</span></div>';
h+='<div><span style="color:#64748b">PID:</span> <span style="color:#94a3b8">'+(d.autodj_pid||'—')+'</span></div></div>';
h+='<div style="background:rgba(0,0,0,.2);border-radius:4px;padding:5px;font-size:9px"><div style="color:#64748b;margin-bottom:2px">Events:</div>';
if(d.connections&&d.connections.length){var n=0;d.connections.forEach(function(c){if(n>=6)return;
var ts='';if(c.connected){var d2=new Date(c.connected);var h2=d2.getHours()%12||12;var a2=d2.getHours()<12?'AM':'PM';ts=(d2.getMonth()+1)+'/'+d2.getDate()+' '+h2+':'+(d2.getMinutes()<10?'0':'')+d2.getMinutes()+' '+a2;}
var ag='';if(c.connected){var s=Math.floor((Date.now()-new Date(c.connected))/1000);ag=s<120?s+'s':(s<7200?Math.floor(s/60)+'m':Math.floor(s/3600)+'h');}
h+='<div><span style="color:#64748b">'+ts+'</span> '+(c.disconnected?'[DC]':'[LIVE]')+' '+(c.dj||'?')+(c.duration?' '+Math.floor(c.duration/60)+'m':'')+(ag?' <span style="color:#64748b">('+ag+' ago)</span>':'')+(c.reason?' <span style="color:#f87171">'+c.reason+'</span>':'')+'</div>';n++;});
}else{h+='<div style="color:#64748b">None</div>';}
h+='</div></div><div style="font-size:8px;color:#64748b;margin-top:3px">'+(d.timestamp||'')+' <span style="color:'+(chk?'#4ade80':'#f87171')+'">'+(chk?'● OK':'● Issues')+'</span></div>';
el.innerHTML=h;
}catch(e){el.innerHTML='<div style="color:#f87171">Parse</div>';}
};
x.onerror=function(){el.innerHTML='<div style="color:#f87171">Net err</div>';};
x.send();
},5000);
})();
</script>
