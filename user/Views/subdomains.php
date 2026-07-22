<style>
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:22px;text-decoration:none;color:#e0e0e0;transition:.2s;margin-bottom:12px}
.section-card h3{font-size:14px;font-weight:600;margin:0 0 10px}
input,select{padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;width:100%;box-sizing:border-box;margin-bottom:8px}
.btn{padding:8px 16px;border-radius:6px;border:none;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;font-size:12px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block}
</style>

<h2>🌍 Subdomains</h2>
<p style="color:#64748b;margin-bottom:16px">Create and manage subdomains for your domains.</p>

<?php if (isset($_SESSION['success'])): ?><div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error'])): ?><div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div><?php endif; ?>

<div class="section-card">
<h3>➕ Create Subdomain</h3>
<form method="POST" action="/user/subdomains/create" style="display:flex;flex-direction:column;gap:10px">
<div style="display:flex;gap:10px;flex-wrap:wrap">
<div style="flex:1;min-width:120px">
<label style="font-size:11px;color:#64748b;display:block;margin-bottom:2px">Subdomain</label>
<input name="subdomain" placeholder="blog" required>
</div>
<div style="flex:1;min-width:150px">
<label style="font-size:11px;color:#64748b;display:block;margin-bottom:2px">Domain</label>
<select name="domain" required>
<option value="">Select domain...</option>
<?php foreach ($zones as $z): ?>
<option value="<?php echo htmlspecialchars($z->domain); ?>"><?php echo htmlspecialchars($z->domain); ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
<label style="font-size:11px;color:#64748b;display:flex;align-items:center;gap:6px;cursor:pointer">
<input type="checkbox" name="create_ftp" value="1" onchange="document.getElementById('ftp-fields').style.display=this.checked?'block':'none'"> Create FTP Account
</label>
</div>
<div id="ftp-fields" style="display:none">
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px">
<div style="flex:1;min-width:150px">
<label style="font-size:11px;color:#64748b;display:block;margin-bottom:2px">Directory</label>
<input name="ftp_dir" placeholder="public_html/blog" value="public_html">
</div>
<div style="flex:1;min-width:120px">
<label style="font-size:11px;color:#64748b;display:block;margin-bottom:2px">FTP Username</label>
<input name="ftp_username" placeholder="blog">
</div>
<div style="flex:1;min-width:120px">
<label style="font-size:11px;color:#64748b;display:block;margin-bottom:2px">FTP Password</label>
<input name="ftp_password" type="password" placeholder="Min 6 chars">
</div>
</div>
</div>
<button type="submit" class="btn">Create Subdomain</button>
</form>
</div>

<?php if (!empty($subdomainRecords)): ?>
<div class="section-card">
<h3>🗑 Your Subdomains</h3>
<table style="width:100%"><tr><th>Subdomain</th><th>Points To</th><th>Actions</th></tr>
<?php foreach ($subdomainRecords as $r): $zone = null; foreach ($zones as $z) { if ($z->id == $r->zone_id) { $zone = $z; break; } } $full = $r->name . '.' . ($zone->domain ?? '?'); ?>
<tr><td><strong><?php echo htmlspecialchars($full); ?></strong></td><td><?php echo htmlspecialchars($r->value); ?></td>
<td>
<a href="#" class="btn" style="background:rgba(56,189,248,.15);color:#38bdf8;padding:4px 10px;font-size:10px" onclick="editDns(<?php echo $r->id; ?>,'<?php echo htmlspecialchars($r->name); ?>','<?php echo htmlspecialchars($r->value); ?>',<?php echo $r->ttl ?? 300; ?>);return false">DNS</a>
<a href="/user/subdomains/delete/<?php echo $r->id; ?>" class="btn" style="background:rgba(255,68,68,.15);color:#ff4444;padding:4px 10px;font-size:10px" onclick="return confirm('Delete <?php echo htmlspecialchars($full); ?>?')">Delete</a>
</td></tr>
<?php endforeach; ?></table>
</div>

<!-- DNS Edit Modal -->
<div id="dns-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.8);align-items:center;justify-content:center" onclick="if(event.target===this)closeDns()">
<div style="background:rgba(8,16,28,.95);border:1px solid rgba(56,189,248,.15);border-radius:16px;padding:28px;max-width:400px;width:92%;margin:auto">
<div style="font-size:16px;font-weight:700;margin-bottom:16px;color:#38bdf8">🌍 Edit DNS Record</div>
<div class="form-group"><label>Record Name</label><input id="dns-name" readonly style="background:rgba(0,0,0,.2);font-family:monospace"></div>
<div class="form-group"><label>Type</label><select id="dns-type" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none"><option value="A">A (IPv4)</option><option value="AAAA">AAAA (IPv6)</option><option value="CNAME">CNAME</option><option value="TXT">TXT</option></select></div>
<div class="form-group"><label>Value</label><input id="dns-value" placeholder="IP address or target"></div>
<div class="form-group"><label>TTL (seconds)</label><input id="dns-ttl" type="number" value="300"></div>
<div style="display:flex;gap:8px;margin-top:16px">
<button class="btn" onclick="saveDns()" style="flex:1">Save</button>
<button class="btn" onclick="closeDns()" style="flex:1;background:rgba(255,255,255,.08);color:#94a3b8">Cancel</button>
</div>
<div id="dns-msg" style="margin-top:8px;font-size:12px;text-align:center"></div>
</div>
</div>

<script>
var _dnsId = 0;
function editDns(id,name,value,ttl){
  _dnsId=id;
  document.getElementById('dns-name').value=name+'.planet-hosts.com';
  document.getElementById('dns-value').value=value;
  document.getElementById('dns-ttl').value=ttl||300;
  document.getElementById('dns-type').value='A';
  document.getElementById('dns-msg').textContent='';
  document.getElementById('dns-modal').style.display='flex';
}
function closeDns(){
  document.getElementById('dns-modal').style.display='none';
}
function saveDns(){
  var val=document.getElementById('dns-value').value.trim();
  var ttl=document.getElementById('dns-ttl').value;
  var type=document.getElementById('dns-type').value;
  if(!val){document.getElementById('dns-msg').textContent='Enter a value.';return;}
  var x=new XMLHttpRequest();
  x.open('POST','/user/subdomains/dns-update/'+_dnsId,true);
  x.setRequestHeader('Content-Type','application/json');
  x.onload=function(){
    try{var r=JSON.parse(x.responseText);document.getElementById('dns-msg').style.color=r.success?'#4ade80':'#f87171';document.getElementById('dns-msg').textContent=r.message||(r.success?'Saved!':'Failed.');if(r.success)setTimeout(closeDns,1000);}catch(e){document.getElementById('dns-msg').textContent='Error.';}
  };
  x.send(JSON.stringify({value:val,ttl:parseInt(ttl)||300,type:type}));
}
</script>
<?php endif; ?>
