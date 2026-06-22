<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
<div>
<h2 style="margin:0">Game Templates</h2>
<p style="color:#64748b;margin:4px 0 0">Manage Steam game server templates with auto-generated install/config scripts.</p>
</div>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<a href="/admin/games/templates/import" class="btn btn-sm" style="background:rgba(250,204,21,.12);color:#facc15;border:1px solid rgba(250,204,21,.2)" onclick="return confirm('Import all games from game_types table?')"><i class="bi bi-download"></i> Import from Game Types</a>
<button class="btn primary" onclick="showCreatePanel()"><i class="bi bi-plus-circle"></i> New Template</button>
</div>
</div>

<div class="stats-grid" style="margin-bottom:24px">
<div class="stat-card"><h3>All</h3><div class="value"><?php echo (int)($stats['all'] ?? 0); ?></div></div>
<?php foreach ($categories as $cat): ?>
<?php $cnt = (int)($stats[$cat] ?? 0); ?>
<div class="stat-card"><h3><?php echo htmlspecialchars($cat); ?></h3><div class="value"><?php echo $cnt; ?></div></div>
<?php endforeach; ?>
</div>

<!-- Category Filter -->
<div style="margin-bottom:20px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
<span style="font-size:13px;color:#64748b;margin-right:4px">Filter:</span>
<a href="/admin/games/templates" class="btn btn-sm <?php echo $currentCategory === 'all' ? 'primary' : 'secondary'; ?>">All</a>
<?php foreach ($categories as $cat): ?>
<a href="/admin/games/templates?category=<?php echo urlencode($cat); ?>" class="btn btn-sm <?php echo $currentCategory === $cat ? 'primary' : 'secondary'; ?>"><?php echo htmlspecialchars($cat); ?></a>
<?php endforeach; ?>
</div>

<!-- Create Panel -->
<div id="createPanel" class="card" style="display:none;max-width:800px;margin-bottom:24px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
<h4 style="margin:0;color:var(--accent)"><i class="bi bi-plus-circle"></i> <span id="formTitle">Create Template</span></h4>
<button class="btn btn-sm secondary" onclick="document.getElementById('createPanel').style.display='none'"><i class="bi bi-x"></i></button>
</div>
<form method="POST" action="/admin/games/templates/store">
<input type="hidden" name="id" id="editId" value="0">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
<div class="form-group"><label>Game Name</label><input name="name" id="f_name" required placeholder="Counter-Strike 2"></div>
<div class="form-group"><label>Steam AppID</label><input name="appid" id="f_appid" placeholder="e.g. 730"></div>
<div class="form-group"><label>Engine</label>
<select name="engine" id="f_engine">
<option value="Source">Source</option>
<option value="Unreal">Unreal</option>
<option value="Unity">Unity</option>
<option value="Java">Java</option>
<option value="Native">Native</option>
<option value="Frostbite">Frostbite</option>
<option value="Real Virtuality">Real Virtuality</option>
<option value="Enfusion">Enfusion</option>
<option value="id Tech">id Tech</option>
<option value="CryEngine">CryEngine</option>
<option value="Dagor">Dagor</option>
</select></div>
<div class="form-group"><label>Category</label>
<select name="category" id="f_category">
<option value="FPS">FPS</option>
<option value="Survival">Survival</option>
<option value="Sandbox">Sandbox</option>
<option value="RPG">RPG</option>
<option value="Simulation">Simulation</option>
<option value="Racing">Racing</option>
<option value="Military">Military</option>
</select></div>
<div class="form-group"><label>SteamCMD Login</label><input name="steamcmd_login" id="f_steamcmd_login" value="anonymous"></div>
<div class="form-group"><label>Default Slots</label><input name="default_slots" id="f_default_slots" type="number" value="16"></div>
<div class="form-group"><label>Min Slots</label><input name="min_slots" id="f_min_slots" type="number" value="10"></div>
<div class="form-group"><label>Max Slots</label><input name="max_slots" id="f_max_slots" type="number" value="64"></div>
<div class="form-group"><label>Query Port</label><input name="query_port" id="f_query_port" type="number" value="27015"></div>
<div class="form-group"><label>Game Port</label><input name="game_port" id="f_game_port" type="number" value="27015"></div>
<div class="form-group"><label>RCON Port</label><input name="rcon_port" id="f_rcon_port" type="number" value="27020"></div>
<div class="form-group" style="display:flex;gap:16px;align-items:center;padding-top:8px">
<label style="display:flex;align-items:center;gap:6px;font-size:13px"><input type="checkbox" name="steam_client" id="f_steam_client" value="1"> Steam Client</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px"><input type="checkbox" name="anonymous_login" id="f_anonymous_login" value="1" checked> Anonymous Login</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px"><input type="checkbox" name="requires_game_purchase" id="f_requires_game_purchase" value="1"> Requires Purchase</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px"><input type="checkbox" name="supports_linux" id="f_supports_linux" value="1" checked> Linux</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px"><input type="checkbox" name="supports_windows" id="f_supports_windows" value="1"> Windows</label>
</div>
</div>
<div class="form-group" style="margin-top:14px"><label>Description</label><textarea name="description" id="f_description" rows="2" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px;resize:vertical"></textarea></div>
<div class="form-group"><label>Notes</label><textarea name="notes" id="f_notes" rows="2" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px;resize:vertical"></textarea></div>
<button type="submit" class="btn primary" style="margin-top:14px"><i class="bi bi-floppy"></i> Save Template</button>
</form>
</div>

<!-- Templates Grid -->
<?php if (!empty($templates)): ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:16px">
<?php foreach ($templates as $t): ?>
<?php
$engineColors = ['Source'=>'#f97316','Unreal'=>'#8b5cf6','Unity'=>'#3b82f6','Java'=>'#ef4444','Native'=>'#10b981','Frostbite'=>'#06b6d4','Real Virtuality'=>'#ec4899','Enfusion'=>'#14b8a6','id Tech'=>'#f59e0b','CryEngine'=>'#6366f1','Dagor'=>'#84cc16'];
$catColors = ['FPS'=>'#ef4444','Survival'=>'#22c55e','Sandbox'=>'#f59e0b','RPG'=>'#a855f7','Simulation'=>'#3b82f6','Racing'=>'#f97316','Military'=>'#64748b'];
$ec = $engineColors[$t->engine] ?? '#64748b';
$cc = $catColors[$t->category] ?? '#64748b';
?>
<div style="background:var(--bg-card);border:1px solid rgba(0,191,255,.08);border-radius:14px;padding:20px;transition:.2s;position:relative">
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
<div>
<h4 style="margin:0;font-size:14px"><?php echo htmlspecialchars($t->name); ?></h4>
<span style="font-size:11px;color:#64748b">App <?php echo htmlspecialchars($t->appid ?: 'N/A'); ?></span>
</div>
<div style="display:flex;gap:4px;flex-wrap:wrap">
<span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:4px;background:<?php echo $ec; ?>20;color:<?php echo $ec; ?>"><?php echo htmlspecialchars($t->engine); ?></span>
<span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:4px;background:<?php echo $cc; ?>20;color:<?php echo $cc; ?>"><?php echo htmlspecialchars($t->category); ?></span>
</div>
</div>
<div style="font-size:12px;color:#64748b;margin-bottom:12px;line-height:1.6">
<div>Ports: Q<?php echo (int)$t->query_port; ?> G<?php echo (int)$t->game_port; ?> R<?php echo (int)$t->rcon_port; ?></div>
<div>Slots: <?php echo (int)$t->min_slots; ?> - <?php echo (int)$t->max_slots; ?> (Default: <?php echo (int)$t->default_slots; ?>)</div>
<?php if ($t->description): ?><div style="margin-top:4px;font-style:italic"><?php echo htmlspecialchars(mb_substr($t->description, 0, 100)); ?></div><?php endif; ?>
</div>
<div style="display:flex;gap:6px;flex-wrap:wrap">
<a href="/admin/games/templates/preview/<?php echo $t->id; ?>" class="btn btn-sm primary"><i class="bi bi-eye"></i> Preview</a>
<button class="btn btn-sm secondary" onclick="editTemplate(<?php echo $t->id; ?>)"><i class="bi bi-pencil"></i> Edit</button>
<a href="/admin/games/templates/delete/<?php echo $t->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171" onclick="return confirm('Delete template for <?php echo htmlspecialchars(addslashes($t->name), ENT_QUOTES, 'UTF-8'); ?>?')"><i class="bi bi-trash"></i></a>
</div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<div style="text-align:center;padding:60px 20px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px">
<div style="font-size:48px;margin-bottom:16px;opacity:.3"><i class="bi bi-controller"></i></div>
<h4 style="color:var(--text-muted);margin-bottom:8px">No Game Templates</h4>
<p style="color:#64748b;font-size:13px">Import from the Game Types table or create a new template manually.</p>
<a href="/admin/games/templates/import" class="btn primary" style="margin-top:8px"><i class="bi bi-download"></i> Import from Game Types</a>
</div>
<?php endif; ?>

<script>
var templates = <?php
$allT = $this->db->table('game_templates')->where('status', 'active')->get() ?: [];
echo json_encode($allT);
?>;

function showCreatePanel() {
    document.getElementById('editId').value = '0';
    document.getElementById('formTitle').textContent = 'Create Template';
    document.getElementById('f_name').value = '';
    document.getElementById('f_appid').value = '';
    document.getElementById('f_engine').value = 'Source';
    document.getElementById('f_category').value = 'FPS';
    document.getElementById('f_steamcmd_login').value = 'anonymous';
    document.getElementById('f_default_slots').value = '16';
    document.getElementById('f_min_slots').value = '10';
    document.getElementById('f_max_slots').value = '64';
    document.getElementById('f_query_port').value = '27015';
    document.getElementById('f_game_port').value = '27015';
    document.getElementById('f_rcon_port').value = '27020';
    document.getElementById('f_steam_client').checked = false;
    document.getElementById('f_anonymous_login').checked = true;
    document.getElementById('f_requires_game_purchase').checked = false;
    document.getElementById('f_supports_linux').checked = true;
    document.getElementById('f_supports_windows').checked = false;
    document.getElementById('f_description').value = '';
    document.getElementById('f_notes').value = '';
    document.getElementById('createPanel').style.display = 'block';
    document.getElementById('createPanel').scrollIntoView({behavior:'smooth'});
}

function editTemplate(id) {
    var t = templates.find(function(x) { return x.id == id; });
    if (!t) return;
    document.getElementById('editId').value = t.id;
    document.getElementById('formTitle').textContent = 'Edit: ' + t.name;
    document.getElementById('f_name').value = t.name;
    document.getElementById('f_appid').value = t.appid;
    document.getElementById('f_engine').value = t.engine;
    document.getElementById('f_category').value = t.category;
    document.getElementById('f_steamcmd_login').value = t.steamcmd_login;
    document.getElementById('f_default_slots').value = t.default_slots;
    document.getElementById('f_min_slots').value = t.min_slots;
    document.getElementById('f_max_slots').value = t.max_slots;
    document.getElementById('f_query_port').value = t.query_port;
    document.getElementById('f_game_port').value = t.game_port;
    document.getElementById('f_rcon_port').value = t.rcon_port;
    document.getElementById('f_steam_client').checked = t.steam_client == 1;
    document.getElementById('f_anonymous_login').checked = t.anonymous_login == 1;
    document.getElementById('f_requires_game_purchase').checked = t.requires_game_purchase == 1;
    document.getElementById('f_supports_linux').checked = t.supports_linux == 1;
    document.getElementById('f_supports_windows').checked = t.supports_windows == 1;
    document.getElementById('f_description').value = t.description || '';
    document.getElementById('f_notes').value = t.notes || '';
    document.getElementById('createPanel').style.display = 'block';
    document.getElementById('createPanel').scrollIntoView({behavior:'smooth'});
}
</script>
