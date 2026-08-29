<h3 style="color:var(--accent);margin:0 0 16px">My Packages</h3>
<p style="color:var(--text_muted,#94a3b8);font-size:13px;margin-bottom:16px">
You create <b>your own</b> packages here — Planet Hosts server packages are never reused. Each package gets a public id of <code>{username}_{name}</code>, and your customers order from these only.
</p>

<!-- Create / edit form -->
<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">➕ Add / Edit Package</h4>
<form method="POST" id="pkgForm" action="/reseller/packages/store">
<input type="hidden" name="id" id="pkg_id" value="0">
<div class="row g-3">
<div class="col-md-4">
<label class="form-label">Package Name *</label>
<input type="text" name="name" id="pkg_name" required placeholder="e.g. Starter">
<small style="color:#64748b">Public id becomes <span id="slugPreview">username_name</span></small>
</div>
<div class="col-md-4">
<label class="form-label">Type</label>
<select name="type" id="pkg_type">
<option value="hosting">Hosting</option>
<option value="billing">Billing</option>
<option value="chat">Chat</option>
<option value="support">Support</option>
<option value="game">Game Server</option>
<option value="music">Radio / Music</option>
<option value="vps">VPS</option>
<option value="domain">Domain</option>
<option value="custom">Custom</option>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Billing Cycle</label>
<select name="billing_cycle" id="pkg_cycle">
<option value="monthly">Monthly</option><option value="quarterly">Quarterly</option>
<option value="semiannual">Semi-annual</option><option value="annual">Annual</option>
</select>
</div>
<div class="col-md-3">
<label class="form-label">Price ($)</label><input type="number" step="0.01" name="price" id="pkg_price" value="0.00">
</div>
<div class="col-md-3">
<label class="form-label">Setup Fee ($)</label><input type="number" step="0.01" name="setup_fee" id="pkg_setup" value="0.00">
</div>
<div class="col-md-6">
<label class="form-label">Description</label><input type="text" name="description" id="pkg_desc" placeholder="Short description">
</div>

<div class="col-md-3"><label class="form-label">Slots</label><input type="number" name="slots" id="pkg_slots" value="10"></div>
<div class="col-md-3"><label class="form-label">Disk (MB)</label><input type="number" name="disk_space" id="pkg_disk" value="0"></div>
<div class="col-md-3"><label class="form-label">Bandwidth (GB)</label><input type="number" name="bandwidth" id="pkg_bw" value="0"></div>
<div class="col-md-3"><label class="form-label">Storage (MB)</label><input type="number" name="storage_limit" id="pkg_storage" value="0"></div>
<div class="col-md-3"><label class="form-label">Backups</label><input type="number" name="backup_limit" id="pkg_backups" value="0"></div>
<div class="col-md-3"><label class="form-label">Databases</label><input type="number" name="database_limit" id="pkg_dbs" value="0"></div>
<div class="col-md-3"><label class="form-label">Ports</label><input type="number" name="port_limit" id="pkg_ports" value="0"></div>
<div class="col-md-3"><label class="form-label">Player Slots</label><input type="number" name="player_slots" id="pkg_players" value="0"></div>
<div class="col-md-3"><label class="form-label">Max Stations</label><input type="number" name="max_stations" id="pkg_stations" value="0"></div>
<div class="col-md-3"><label class="form-label">Max DJs</label><input type="number" name="max_djs" id="pkg_djs" value="0"></div>
<div class="col-md-3"><label class="form-label">Max Listeners</label><input type="number" name="max_listeners" id="pkg_listeners" value="0"></div>
<div class="col-md-3"><label class="form-label">Max Bitrate</label><input type="number" name="max_bitrate" id="pkg_bitrate" value="0"></div>

<div class="col-md-12">
<label class="form-label">Modules granted (features)</label>
<div style="display:flex;flex-wrap:wrap;gap:6px">
<?php
$mods = ['billing'=>'Billing','chat'=>'Chat','support'=>'Support','game'=>'Game Servers','music'=>'Radio/Music','dj_panel'=>'DJ Panel','email'=>'Email','databases'=>'Databases','ssl'=>'SSL','backups'=>'Backups','vps'=>'VPS','domains'=>'Domains'];
foreach ($mods as $k=>$l): ?>
<label class="btn btn-sm pkg-feat" style="border:1px solid var(--border,rgba(0,191,255,.2));background:rgba(0,0,0,.2);color:#cbd5e1;cursor:pointer">
<input type="checkbox" name="features[]" value="<?php echo $k; ?>" style="display:none"> <?php echo $l; ?>
</label>
<?php endforeach; ?>
</div>
</div>

<div class="col-md-12">
<label class="form-label">Allowed Games (AppIDs / names)</label>
<input type="text" name="allowed_games_raw" id="pkg_games" placeholder="e.g. 730, 258550 or comma list">
</div>

<div class="col-md-12 d-flex gap-2 mt-2">
<button type="submit" class="btn btn-primary">Save Package</button>
<button type="button" class="btn btn-secondary" onclick="resetPkg()">Reset</button>
</div>
</div>
</form>
</div>

<!-- List -->
<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">My Packages</h4>
<?php if (!empty($packages)): ?>
<table class="table">
<tr><th>Public ID</th><th>Name</th><th>Type</th><th>Price</th><th>Cycle</th><th>Status</th><th></th></tr>
<?php foreach ($packages as $p): $feats = is_string($p->features??null) ? json_decode($p->features,true) : ($p->features ?? []); ?>
<tr>
<td><code><?php echo htmlspecialchars($p->slug ?? ''); ?></code></td>
<td><?php echo htmlspecialchars($p->name); ?><?php if ($p->description): ?><br><small style="color:#94a3b8"><?php echo htmlspecialchars($p->description); ?></small><?php endif; ?></td>
<td><?php echo htmlspecialchars($p->type); ?></td>
<td>$<?php echo number_format((float)$p->price,2); ?><?php if((float)$p->setup_fee>0): ?><br><small>+ $<?php echo number_format((float)$p->setup_fee,2); ?> setup</small><?php endif; ?></td>
<td><?php echo htmlspecialchars($p->billing_cycle); ?></td>
<td><span class="status-badge status-<?php echo $p->is_active?'active':'terminated'; ?>"><?php echo $p->is_active?'Active':'Inactive'; ?></span></td>
<td style="white-space:nowrap">
<button class="btn btn-sm secondary" onclick='editPkg(<?php echo json_encode($p); ?>)'>Edit</button>
<a href="/reseller/packages/delete/<?php echo $p->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete this package?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?><p style="color:#64748b">You have no own packages yet — create one above.</p><?php endif; ?>
</div>

<script>
function esc(s){var d=document.createElement('div');d.textContent=s==null?'':String(s);return d.innerHTML;}
var userSlug = '<?php echo strtolower(preg_replace("/[^a-z0-9]+/","", strtolower($user->name ?? "reseller"))); ?>';
function previewSlug() {
  var n = (document.getElementById('pkg_name').value||'').toLowerCase().replace(/[^a-z0-9]+/g,'_').replace(/^_|_$/g,'');
  document.getElementById('slugPreview').textContent = userSlug + '_' + (n||'name');
}
document.getElementById('pkg_name').addEventListener('input', previewSlug);
previewSlug();
function editPkg(p){
  document.getElementById('pkg_id').value = p.id;
  document.getElementById('pkgForm').action = '/reseller/packages/update/' + p.id;
  document.getElementById('pkg_name').value = p.name;
  document.getElementById('pkg_type').value = p.type;
  document.getElementById('pkg_cycle').value = p.billing_cycle;
  document.getElementById('pkg_price').value = p.price;
  document.getElementById('pkg_setup').value = p.setup_fee;
  document.getElementById('pkg_desc').value = p.description||'';
  document.getElementById('pkg_slots').value = p.slots; document.getElementById('pkg_disk').value = p.disk_space;
  document.getElementById('pkg_bw').value = p.bandwidth; document.getElementById('pkg_storage').value = p.storage_limit;
  document.getElementById('pkg_backups').value = p.backup_limit; document.getElementById('pkg_dbs').value = p.database_limit;
  document.getElementById('pkg_ports').value = p.port_limit; document.getElementById('pkg_players').value = p.player_slots;
  document.getElementById('pkg_stations').value = p.max_stations; document.getElementById('pkg_djs').value = p.max_djs;
  document.getElementById('pkg_listeners').value = p.max_listeners; document.getElementById('pkg_bitrate').value = p.max_bitrate;
  var feats = Array.isArray(p.features)?p.features:(typeof p.features==='string'?JSON.parse(p.features||'[]'):[]);
  document.querySelectorAll('.pkg-feat input').forEach(function(c){c.checked = feats.indexOf(c.value)>=0;});
  var ag = Array.isArray(p.allowed_games)?p.allowed_games.join(', '):(p.allowed_games||'');
  document.getElementById('pkg_games').value = Array.isArray(p.allowed_games)?p.allowed_games.join(', '):'';
  window.scrollTo(0,0);
}
function resetPkg(){
  document.getElementById('pkg_id').value=0; document.getElementById('pkgForm').action='/reseller/packages/store';
  document.getElementById('pkgForm').reset(); previewSlug();
}
</script>