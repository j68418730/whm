<style>
.tpl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(420px,1fr));gap:14px}
.tpl-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:16px;transition:.2s}
.tpl-card:hover{border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.tpl-head{display:flex;justify-content:space-between;align-items:start;margin-bottom:8px}
.tpl-title{font-size:15px;font-weight:700;color:#e2e8f0}
.tpl-sub{font-size:11px;color:#64748b}
.tpl-cmd{font-family:monospace;font-size:10px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.06);border-radius:6px;padding:8px;margin:6px 0;color:#7dd3fc;white-space:pre-wrap;word-break:break-word;max-height:90px;overflow-y:auto}
.tpl-cmd-label{font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px;margin-top:8px;display:block}
.tpl-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600}
.tpl-badge-appid{background:rgba(0,140,255,.12);color:#38bdf8}
.tpl-badge-cat{background:rgba(168,85,247,.12);color:#c084fc}
.tpl-badge-linux{background:rgba(74,222,128,.1);color:#4ade80}
.tpl-badge-windows{background:rgba(56,189,248,.1);color:#7dd3fc}
.tpl-badge-off{background:rgba(248,113,113,.12);color:#f87171}
.tpl-badge-inactive{background:rgba(100,116,139,.15);color:#94a3b8}
.filter-bar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px}
.filter-input{padding:8px 10px;font-size:12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);color:#e0e0e0;border-radius:6px}
.filter-btn{padding:6px 12px;font-size:11px;background:rgba(0,140,255,.1);color:#38bdf8;border:1px solid rgba(0,140,255,.2);border-radius:6px;cursor:pointer;text-decoration:none}
.filter-btn:hover{background:rgba(0,140,255,.2)}
.form-group textarea{font-family:monospace;font-size:11px;width:100%;padding:8px 10px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);color:#e0e0e0;border-radius:6px;box-sizing:border-box}
.form-group input[type=text],.form-group input[type=number],.form-group select{width:100%;padding:8px 10px;font-size:12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);color:#e0e0e0;border-radius:6px;box-sizing:border-box}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:4px">
<h3 style="color:var(--accent)">Game Templates <span style="color:#64748b;font-size:12px;font-weight:400">(<?php echo (int)$total; ?> templates)</span></h3>
<a class="btn primary" onclick="document.getElementById('tplForm').classList.toggle('hidden');document.getElementById('tplForm').scrollIntoView({behavior:'smooth'})">Add Template</a>
</div>
<p style="color:#64748b;font-size:12px;margin-bottom:12px">SteamCMD install recipes used to provision game servers. Each template contains the appid, install script and start/stop/restart commands.</p>

<!-- Filters -->
<form method="GET" action="/admin/games/templates" class="filter-bar">
<input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search name, appid, category..." class="filter-input" style="flex:1;min-width:180px">
<select name="category" class="filter-input" onchange="this.form.submit()">
<option value="">All categories</option>
<?php foreach ($categories as $c): ?>
<option value="<?php echo htmlspecialchars($c->category); ?>" <?php echo $cat === $c->category ? 'selected' : ''; ?>><?php echo htmlspecialchars($c->category); ?> (<?php echo (int)$c->cnt; ?>)</option>
<?php endforeach; ?>
</select>
<button type="submit" class="filter-btn">Filter</button>
<?php if ($q !== '' || $cat !== ''): ?><a href="/admin/games/templates" class="filter-btn" style="background:rgba(248,113,113,.1);color:#f87171;border-color:rgba(248,113,113,.2)">Clear</a><?php endif; ?>
</form>

<!-- Create/Edit Form -->
<div id="tplForm" class="card hidden" style="margin-bottom:20px">
<h4 style="color:var(--accent);margin:0 0 12px">Template Details</h4>
<form method="POST" action="/admin/games/templates/store">
<?php echo $csrfField ?? ''; ?>
<input type="hidden" name="id" id="editId" value="0">
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;margin-bottom:10px">
<div class="form-group" style="margin:0"><label>Game Name</label><input name="name" id="editName" required placeholder="e.g. Counter-Strike 2"></div>
<div class="form-group" style="margin:0"><label>Steam AppID</label><input name="appid" id="editAppid" required placeholder="e.g. 730"></div>
<div class="form-group" style="margin:0"><label>Engine</label><input name="engine" id="editEngine" list="engineList" placeholder="Source / Native / Unreal / Java">
<datalist id="engineList"><?php foreach ($engines as $e): ?><option value="<?php echo htmlspecialchars($e); ?>"><?php endforeach; ?></datalist></div>
<div class="form-group" style="margin:0"><label>Category</label><input name="category" id="editCategory" placeholder="e.g. FPS, Survival, Sandbox"></div>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:10px">
<div class="form-group" style="margin:0"><label>Query Port</label><input name="query_port" id="editQp" type="number" value="27015"></div>
<div class="form-group" style="margin:0"><label>Game Port</label><input name="game_port" id="editGp" type="number" value="27015"></div>
<div class="form-group" style="margin:0"><label>RCON Port</label><input name="rcon_port" id="editRp" type="number" value="27020"></div>
<div class="form-group" style="margin:0"><label>Default Slots</label><input name="default_slots" id="editDslots" type="number" value="16"></div>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:10px">
<div class="form-group" style="margin:0"><label>Min Slots</label><input name="min_slots" id="editMin" type="number" value="10"></div>
<div class="form-group" style="margin:0"><label>Max Slots</label><input name="max_slots" id="editMax" type="number" value="64"></div>
<div class="form-group" style="margin:0"><label>SteamCMD Login</label><input name="steamcmd_login" id="editLogin" value="anonymous"></div>
<div class="form-group" style="margin:0"><label>Status</label><select name="status" id="editStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px">
<label style="font-size:11px;color:#94a3b8"><input type="checkbox" name="steam_client" value="1" id="editSteamClient"> Steam Client</label>
<label style="font-size:11px;color:#94a3b8"><input type="checkbox" name="anonymous_login" value="1" id="editAnon" checked> Anonymous Login</label>
<label style="font-size:11px;color:#94a3b8"><input type="checkbox" name="requires_game_purchase" value="1" id="editPurch"> Requires Game Purchase</label>
<label style="font-size:11px;color:#94a3b8"><input type="checkbox" name="supports_linux" value="1" id="editLinux" checked> Supports Linux</label>
<label style="font-size:11px;color:#94a3b8"><input type="checkbox" name="supports_windows" value="1" id="editWin"> Supports Windows</label>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div class="form-group" style="margin:0"><label>Install Script <span style="color:#64748b">({INSTALL_DIR}, {APPID}, {LOGIN})</span></label><textarea name="install_script" id="editInstall" rows="3" placeholder="steamcmd +force_install_dir {INSTALL_DIR} +login {LOGIN} +app_update {APPID} validate +quit"></textarea></div>
<div class="form-group" style="margin:0"><label>Config Template</label><textarea name="config_template" id="editConfig" rows="3" placeholder="Optional server.cfg / server.properties"></textarea></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div class="form-group" style="margin:0"><label>Start Command <span style="color:#64748b">({INSTALL_DIR}, {PORT}, {MAX_PLAYERS}, {MAP})</span></label><textarea name="start_command" id="editStart" rows="3" placeholder="cd {INSTALL_DIR} && ./srcds_run -port {PORT} +maxplayers {MAX_PLAYERS} +map {MAP} +exec server.cfg"></textarea></div>
<div class="form-group" style="margin:0"><label>Stop Command</label><textarea name="stop_command" id="editStop" rows="2"></textarea></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div class="form-group" style="margin:0"><label>Restart Command</label><textarea name="restart_command" id="editRestart" rows="2"></textarea></div>
<div class="form-group" style="margin:0"><label>Notes</label><textarea name="notes" id="editNotes" rows="2"></textarea></div>
</div>
<div class="form-group"><label>Description</label><textarea name="description" id="editDesc" rows="2"></textarea></div>
<div style="display:flex;gap:8px;margin-top:12px">
<button type="submit" class="btn primary">Save Template</button>
<a class="btn btn-sm" style="background:#333;color:#ccc;cursor:pointer" onclick="cancelEdit()">Cancel</a>
</div>
</form>
</div>

<!-- Template Cards -->
<div class="tpl-grid">
<?php if (!empty($templates)): foreach ($templates as $t): ?>
<div class="tpl-card" style="opacity:<?php echo $t->status === 'active' ? '1' : '.55'; ?>">
<div class="tpl-head">
<div>
<div class="tpl-title"><?php echo htmlspecialchars($t->name); ?></div>
<div class="tpl-sub"><?php echo htmlspecialchars($t->engine ?? 'Native'); ?> · slots <?php echo (int)$t->min_slots; ?>–<?php echo (int)$t->max_slots; ?> · ports <?php echo (int)$t->game_port; ?>/<?php echo (int)$t->query_port; ?></div>
</div>
<div style="display:flex;flex-direction:column;gap:3px;align-items:flex-end">
<span class="tpl-badge tpl-badge-appid">AppID <?php echo htmlspecialchars($t->appid); ?></span>
<span class="tpl-badge tpl-badge-cat"><?php echo htmlspecialchars($t->category); ?></span>
<?php if ($t->status !== 'active'): ?><span class="tpl-badge tpl-badge-inactive">Inactive</span><?php endif; ?>
</div>
</div>
<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:4px">
<span class="tpl-badge <?php echo $t->supports_linux ? 'tpl-badge-linux' : 'tpl-badge-off'; ?>">🐧 Linux: <?php echo $t->supports_linux ? 'Yes' : 'No'; ?></span>
<span class="tpl-badge <?php echo $t->supports_windows ? 'tpl-badge-windows' : 'tpl-badge-off'; ?>">🪟 Windows: <?php echo $t->supports_windows ? 'Yes' : 'No'; ?></span>
<?php if ($t->requires_game_purchase): ?><span class="tpl-badge tpl-badge-appid">🔒 Owned Required</span><?php endif; ?>
</div>
<label class="tpl-cmd-label">Install Script</label>
<div class="tpl-cmd"><?php echo htmlspecialchars($t->install_script ?: '—'); ?></div>
<label class="tpl-cmd-label">Start Command</label>
<div class="tpl-cmd"><?php echo htmlspecialchars($t->start_command ?: '—'); ?></div>
<div style="display:flex;gap:6px;margin-top:10px">
<a class="btn btn-sm" style="background:#333;color:#ccc;cursor:pointer" onclick="editTemplate(<?php echo htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8'); ?>)">Edit</a>
<a href="/admin/games/templates/toggle/<?php echo $t->id; ?>" class="btn btn-sm" style="background:<?php echo $t->status === 'active' ? 'rgba(250,204,21,.12)' : 'rgba(74,222,128,.12)'; ?>;color:<?php echo $t->status === 'active' ? '#facc15' : '#4ade80'; ?>;text-decoration:none;border-radius:4px"><?php echo $t->status === 'active' ? 'Deactivate' : 'Activate'; ?></a>
<a href="/admin/games/templates/delete/<?php echo $t->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete template '<?php echo htmlspecialchars($t->name); ?>'?')">Delete</a>
</div>
</div>
<?php endforeach; else: ?><p style="color:#64748b;grid-column:1/-1;text-align:center;padding:30px">No templates<?php echo ($q !== '' || $cat !== '') ? ' match your filter' : ' yet'; ?>.</p>
<?php endif; ?>
</div>

<script>
function editTemplate(t) {
    document.getElementById('editId').value = t.id;
    document.getElementById('editName').value = t.name;
    document.getElementById('editAppid').value = t.appid;
    document.getElementById('editEngine').value = t.engine || 'Native';
    document.getElementById('editCategory').value = t.category || 'FPS';
    document.getElementById('editQp').value = t.query_port || 27015;
    document.getElementById('editGp').value = t.game_port || 27015;
    document.getElementById('editRp').value = t.rcon_port || 27020;
    document.getElementById('editDslots').value = t.default_slots || 16;
    document.getElementById('editMin').value = t.min_slots || 10;
    document.getElementById('editMax').value = t.max_slots || 64;
    document.getElementById('editLogin').value = t.steamcmd_login || 'anonymous';
    document.getElementById('editStatus').value = t.status === 'active' ? 'active' : 'inactive';
    document.getElementById('editSteamClient').checked = t.steam_client == 1;
    document.getElementById('editAnon').checked = t.anonymous_login == 1;
    document.getElementById('editPurch').checked = t.requires_game_purchase == 1;
    document.getElementById('editLinux').checked = t.supports_linux == 1;
    document.getElementById('editWin').checked = t.supports_windows == 1;
    document.getElementById('editInstall').value = t.install_script || '';
    document.getElementById('editConfig').value = t.config_template || '';
    document.getElementById('editStart').value = t.start_command || '';
    document.getElementById('editStop').value = t.stop_command || '';
    document.getElementById('editRestart').value = t.restart_command || '';
    document.getElementById('editNotes').value = t.notes || '';
    document.getElementById('editDesc').value = t.description || '';
    document.getElementById('tplForm').classList.remove('hidden');
    document.getElementById('tplForm').scrollIntoView({behavior:'smooth'});
}
function cancelEdit() {
    document.getElementById('editId').value = 0;
    document.getElementById('tplForm').classList.add('hidden');
}
</script>