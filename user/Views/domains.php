<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
<?php endif; ?>
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Your Domains</h3>
<table><tr><th>Domain</th><th>Actions</th></tr>
<?php if (!empty($domains)): foreach ($domains as $d): ?>
<tr><td><strong><?php echo htmlspecialchars($d->domain); ?></strong></td>
<td><a href="#" class="btn btn-sm btn-primary" onclick="showDnsZone(<?=$d->id?>,'<?=htmlspecialchars($d->domain)?>');return false">DNS</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="2" style="text-align:center;padding:20px;color:#64748b">No domains yet.</td></tr>
<?php endif; ?></table></div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Your Subdomains</h3>
<table><tr><th>Subdomain</th><th>Actions</th></tr>
<?php $hasSd = false; foreach ($domains as $d): $sdList = array_filter($subdomains, function($s) use ($d) { return $s->domain === $d->domain; }); if (empty($sdList)) continue; $hasSd = true; foreach ($sdList as $s): ?>
<tr><td><?php echo htmlspecialchars($s->name . '.' . $d->domain); ?></td>
<td><a href="#" class="btn btn-sm btn-primary" onclick="editSubDns(<?=$s->record_id?>,'<?=htmlspecialchars($s->name)?>','<?=htmlspecialchars($s->value)?>',300);return false">DNS</a></td></tr>
<?php endforeach; endforeach; if (!$hasSd): ?><tr><td colspan="2" style="text-align:center;padding:20px;color:#64748b">No subdomains yet.</td></tr>
<?php endif; ?></table></div>

<!-- DNS Zone Modal -->
<div id="dns-zone-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.8);align-items:center;justify-content:center" onclick="if(event.target===this)closeDnsZone()">
<div style="background:rgba(8,16,28,.95);border:1px solid rgba(56,189,248,.15);border-radius:16px;padding:28px;max-width:600px;width:92%;margin:auto;max-height:80vh;overflow-y:auto">
<div style="font-size:16px;font-weight:700;margin-bottom:16px;color:#38bdf8">🌍 DNS Zone: <span id="dns-zone-domain"></span></div>
<div id="dns-zone-records" style="margin-bottom:12px;font-size:12px;color:#64748b">Loading...</div>
</div>
</div>

<!-- Subdomain DNS Edit Modal -->
<div id="dns-sd-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.8);align-items:center;justify-content:center" onclick="if(event.target===this)closeDnsSd()">
<div style="background:rgba(8,16,28,.95);border:1px solid rgba(56,189,248,.15);border-radius:16px;padding:28px;max-width:400px;width:92%;margin:auto">
<div style="font-size:16px;font-weight:700;margin-bottom:16px;color:#38bdf8">🌍 Edit DNS Record</div>
<div class="form-group" style="margin-bottom:10px"><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Name</label><input id="dns-sd-name" style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none"></div>
<div class="form-group" style="margin-bottom:10px"><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Type</label><select id="dns-sd-type" style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none"><option value="A">A (IPv4)</option><option value="AAAA">AAAA (IPv6)</option><option value="CNAME">CNAME</option><option value="TXT">TXT</option></select></div>
<div class="form-group" style="margin-bottom:10px"><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Value</label><input id="dns-sd-value" style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none"></div>
<div class="form-group" style="margin-bottom:10px"><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">TTL</label><input id="dns-sd-ttl" type="number" value="300" style="width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none"></div>
<div style="display:flex;gap:8px;margin-top:12px"><button class="btn btn-sm btn-primary" onclick="saveSdDns()" style="flex:1;padding:8px">Save</button><button class="btn btn-sm btn-secondary" onclick="closeDnsSd()" style="flex:1;padding:8px">Cancel</button></div>
<div id="dns-sd-msg" style="margin-top:6px;font-size:12px;text-align:center"></div>
</div>
</div>

<style>
.btn{padding:6px 12px;border-radius:6px;border:none;font-size:11px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block}
.btn-primary{background:rgba(56,189,248,.15);color:#38bdf8}
.btn-secondary{background:rgba(255,255,255,.06);color:#94a3b8}
.btn-sm{padding:4px 10px;font-size:10px}
table{width:100%;border-collapse:collapse;font-size:12px}
th{text-align:left;padding:8px 6px;color:#64748b;font-weight:600;border-bottom:1px solid rgba(255,255,255,.06)}
td{padding:8px 6px;border-bottom:1px solid rgba(255,255,255,.04)}
.form-group label{font-size:11px;color:#64748b;display:block;margin-bottom:3px}
.form-group input,.form-group select{width:100%;padding:7px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;box-sizing:border-box}
</style>
<script>
var _sdId=0;
function showDnsZone(id,domain){
  document.getElementById('dns-zone-domain').textContent=domain;
  document.getElementById('dns-zone-records').innerHTML='Loading...';
  document.getElementById('dns-zone-modal').style.display='flex';
  var x=new XMLHttpRequest();
  x.open('GET','/user/domains/zone-records/'+id,true);
  x.onload=function(){
    try{var r=JSON.parse(x.responseText);
      if(r.success&&r.records){
        var h='<table><tr><th>Name</th><th>Type</th><th>Value</th><th>TTL</th></tr>';
        r.records.forEach(function(rec){h+='<tr><td>'+rec.name+'</td><td>'+rec.type+'</td><td style="font-size:11px;word-break:break-all">'+rec.value+'</td><td>'+rec.ttl+'</td></tr>';});
        h+='</table>';document.getElementById('dns-zone-records').innerHTML=h;
      }else{document.getElementById('dns-zone-records').innerHTML='No records found.';}
    }catch(e){document.getElementById('dns-zone-records').innerHTML='Error loading zone.';}
  };
  x.onerror=function(){document.getElementById('dns-zone-records').innerHTML='Connection error.';};
  x.send();
}
function closeDnsZone(){document.getElementById('dns-zone-modal').style.display='none';}
function editSubDns(id,name,value,ttl){
  _sdId=id;
  document.getElementById('dns-sd-name').value=name;
  document.getElementById('dns-sd-value').value=value;
  document.getElementById('dns-sd-ttl').value=ttl||300;
  document.getElementById('dns-sd-type').value='A';
  document.getElementById('dns-sd-msg').textContent='';
  document.getElementById('dns-sd-modal').style.display='flex';
}
function closeDnsSd(){document.getElementById('dns-sd-modal').style.display='none';}
function saveSdDns(){
  var val=document.getElementById('dns-sd-value').value.trim();
  var name=document.getElementById('dns-sd-name').value.trim();
  var ttl=document.getElementById('dns-sd-ttl').value;
  var type=document.getElementById('dns-sd-type').value;
  if(!val||!name){document.getElementById('dns-sd-msg').textContent='Name and value required.';return;}
  var x=new XMLHttpRequest();
  x.open('POST','/user/subdomains/dns-update/'+_sdId,true);
  x.setRequestHeader('Content-Type','application/json');
  x.onload=function(){
    try{var r=JSON.parse(x.responseText);document.getElementById('dns-sd-msg').style.color=r.success?'#4ade80':'#f87171';document.getElementById('dns-sd-msg').textContent=r.message||(r.success?'Saved!':'Failed.');if(r.success)setTimeout(closeDnsSd,1000);}catch(e){document.getElementById('dns-sd-msg').textContent='Error.';}
  };
  x.send(JSON.stringify({name:name,value:val,ttl:parseInt(ttl)||300,type:type}));
}
</script>
