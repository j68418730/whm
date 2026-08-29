<h3 style="color:var(--accent);margin:0 0 8px">My Packages</h3>
<p style="color:var(--text_muted,#94a3b8);font-size:13px;margin-bottom:16px">
You sell <b>your own retail packages</b> — Planet Hosts server packages are never reused. Each gets a public id <code>{username}_{name}</code>.
<?php $rType = $reseller->type ?? 'web_reseller'; echo $rType === 'icecast_reseller' ? 'As a radio reseller you can sell: <b>Radio / Music, Game Server, Hosting, Custom</b>.' : 'As a web reseller you can sell: <b>Hosting, VPS, Domain, Email, Custom</b>.'; ?>
</p>

<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

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
<label class="form-label">Type (locked to your reseller)</label>
<select name="type" id="pkg_type">
<?php foreach ($allowedTypes as $tk => $tl): ?>
<option value="<?php echo $tk; ?>" <?php echo $tk === 'hosting' ? 'selected' : ''; ?>><?php echo $tl; ?></option>
<?php endforeach; ?>
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
<label class="form-label">Price ($) *</label><input type="number" step="0.01" name="price" id="pkg_price" value="0.00" required>
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
<label class="form-label">Modules granted (features) — locked to your reseller type</label>
<div style="display:flex;flex-wrap:wrap;gap:6px">
<?php foreach ($allowedFeatures as $k=>$l): ?>
<label class="btn btn-sm pkg-feat" style="border:1px solid var(--border,rgba(0,191,255,.2));background:rgba(0,0,0,.2);color:#cbd5e1;cursor:pointer">
<input type="checkbox" name="features[]" value="<?php echo $k; ?>" style="display:none"> <?php echo $l; ?>
</label>
<?php endforeach; ?>
</div>
</div>

<div class="col-md-12" id="gamesWrap" style="display:none">
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

<!-- List: card grid mirroring admin/packages -->
<style>
.pkg-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;margin-top:4px}
.pkg-card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:10px;padding:16px;transition:.3s}
.pkg-card:hover{border-color:rgba(0,191,255,.2);transform:translateY(-2px)}
.pkg-card .p-name{font-size:15px;font-weight:700;margin-bottom:2px}
.pkg-card .p-type{font-size:11px;color:#64748b;margin-bottom:6px}
.pkg-card .p-price{font-size:20px;font-weight:800;color:#4ade80;margin-bottom:8px}
.pkg-card .p-price small{font-size:11px;color:#64748b;font-weight:400}
.pkg-card .p-features{font-size:11px;color:#94a3b8;margin-bottom:10px;line-height:1.6}
.pkg-card .p-status{display:inline-block;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:600}
.pkg-card .p-actions{display:flex;gap:6px;margin-top:10px;flex-wrap:wrap}
.pkg-card .p-actions a,.pkg-card .p-actions button{padding:4px 12px;border-radius:5px;font-size:11px;text-decoration:none;font-weight:600;border:none;cursor:pointer;font-family:inherit}
</style>
<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px"><?php echo count($packages ?? []); ?> Retail Package<?php echo count($packages ?? []) !== 1 ? 's' : ''; ?></h4>
<?php if (!empty($packages)): ?>
<div class="pkg-grid">
<?php foreach ($packages as $p): $feats = is_string($p->features??null) ? json_decode($p->features,true) : ($p->features ?? []); $feats = is_array($feats) ? $feats : []; $used = $clientCounts[$p->id] ?? 0; ?>
<div class="pkg-card">
<div class="p-name"><?php echo htmlspecialchars($p->name); ?></div>
<div class="p-type"><?php echo htmlspecialchars($typeLabel($p->type)); ?> · <code style="font-size:10px"><?php echo htmlspecialchars($p->slug ?? ''); ?></code></div>
<div class="p-price">$<?php echo number_format((float)$p->price,2); ?><small>/<?php echo htmlspecialchars($p->billing_cycle); ?><?php if ((float)$p->setup_fee>0): ?> + $<?php echo number_format((float)$p->setup_fee,2); ?> setup<?php endif; ?></small></div>
<div class="p-features">
<?php if ($p->disk_space): ?>📁 Disk <?php echo (int)$p->disk_space; ?> MB<br><?php endif; ?>
<?php if ($p->bandwidth): ?>📶 BW <?php echo (int)$p->bandwidth; ?> GB<br><?php endif; ?>
<?php if ($p->max_stations): ?>🎧 <?php echo (int)$p->max_stations; ?> Stations<br><?php endif; ?>
<?php if ($p->max_listeners): ?>👤 <?php echo (int)$p->max_listeners; ?> Listeners<br><?php endif; ?>
<?php if ($p->max_djs): ?>🎤 <?php echo (int)$p->max_djs; ?> DJs<br><?php endif; ?>
<?php if ($p->database_limit): ?>🗄 <?php echo (int)$p->database_limit; ?> DBs<br><?php endif; ?>
<?php foreach ($feats as $f): $fl = ['billing'=>'Billing','chat'=>'Chat','support'=>'Support','game'=>'Game','music'=>'Radio','dj_panel'=>'DJ Panel','email'=>'Email','databases'=>'DBs','ssl'=>'SSL','backups'=>'Backups','vps'=>'VPS','domains'=>'Domains'][$f] ?? null; if ($fl) echo '💠 ' . $fl . '<br>'; endforeach; ?>
<?php if (!$p->disk_space && !$p->bandwidth && !$p->max_stations && !$p->max_listeners && !$p->max_djs && !$p->database_limit && empty($feats)): ?><span style="color:#475569">Minimal package</span><?php endif; ?>
</div>
<div style="margin-bottom:8px;font-size:11px;color:#64748b">👥 <?php echo $used; ?> client<?php echo $used !== 1 ? 's' : ''; ?> on this package</div>
<div><span class="p-status" style="background:<?php echo ($p->is_active ?? 1) ? 'rgba(74,222,128,.12);color:#4ade80' : 'rgba(248,113,113,.12);color:#f87171'; ?>"><?php echo ($p->is_active ?? 1) ? 'Active' : 'Inactive'; ?></span></div>
<div class="p-actions">
<button class="btn btn-sm secondary" style="background:rgba(0,140,255,.1);color:#38bdf8" onclick='editPkg(<?php echo json_encode($p); ?>)'>Edit</button>
<a href="/reseller/packages/delete/<?php echo (int)$p->id; ?>" style="background:rgba(250,204,21,.1);color:#facc15" onclick="return confirm('Deactivate package <?php echo htmlspecialchars($p->name); ?>?')">Activate/Deactivate</a>
<form method="POST" action="/reseller/packages/delete/<?php echo (int)$p->id; ?>" style="display:inline" onsubmit="return confirm('Deactivate <?php echo htmlspecialchars($p->name); ?>?')">
<button type="submit" style="background:rgba(248,113,113,.12);color:#f87171">Delete</button>
</form>
</div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p style="color:#64748b;text-align:center;padding:20px">No packages yet — create your first retail package above.</p>
<?php endif; ?>
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
  if (document.querySelector('#pkg_type option[value="'+p.type+'"]')) document.getElementById('pkg_type').value = p.type;
  toggleGames();
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
  var ag = Array.isArray(p.allowed_games)?p.allowed_games.join(', '):'';
  document.getElementById('pkg_games').value = ag || '';
  window.scrollTo(0,0);
}
function resetPkg(){
  document.getElementById('pkg_id').value=0; document.getElementById('pkgForm').action='/reseller/packages/store';
  document.getElementById('pkgForm').reset(); previewSlug(); toggleGames();
}
function toggleGames(){
  var t = document.getElementById('pkg_type').value;
  document.getElementById('gamesWrap').style.display = (t === 'game') ? 'block' : 'none';
}
document.getElementById('pkg_type').addEventListener('change', toggleGames);
document.getElementById('pkg_type').addEventListener('load', toggleGames);
toggleGames();
</script>