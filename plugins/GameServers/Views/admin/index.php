<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
<div>
<h2 style="margin:0">🎮 Game Servers</h2>
<p style="color:#64748b;margin:4px 0 0">Manage all game server instances.</p>
</div>
<button class="btn primary" onclick="document.getElementById('createPanel').style.display='block';document.getElementById('createPanel').scrollIntoView({behavior:'smooth'})"><i class="bi bi-plus-circle"></i> Create Server</button>
</div>

<div class="stats-grid" style="margin-bottom:24px">
<div class="stat-card"><h3>Total</h3><div class="value"><?php echo count($servers); ?></div></div>
<div class="stat-card"><h3>Running</h3><div class="value" style="color:#4ade80"><?php echo count(array_filter($servers, fn($s) => $s->status === 'running')); ?></div></div>
<div class="stat-card"><h3>Stopped</h3><div class="value" style="color:#f87171"><?php echo count(array_filter($servers, fn($s) => $s->status === 'stopped')); ?></div></div>
<div class="stat-card"><h3>Suspended</h3><div class="value" style="color:#facc15"><?php echo count(array_filter($servers, fn($s) => $s->status === 'suspended')); ?></div></div>
</div>

<!-- Create Panel -->
<div id="createPanel" class="card" style="display:none;max-width:700px;margin-bottom:24px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
<h4 style="margin:0;color:var(--accent)"><i class="bi bi-plus-circle"></i> Create Game Server</h4>
<button class="btn btn-sm secondary" onclick="document.getElementById('createPanel').style.display='none'"><i class="bi bi-x"></i></button>
</div>
<form method="POST" action="/admin/games/create">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
<div class="form-group"><label>Server Name</label><input name="name" required placeholder="My Rust Server"></div>
<div class="form-group"><label>Game Type</label>
<select name="game_type">
<option value="">Custom (no Steam)</option>
<?php foreach ($gameTypes as $gt): ?>
<option value="<?php echo htmlspecialchars($gt->name); ?>"><?php echo htmlspecialchars($gt->name); ?> (App <?php echo htmlspecialchars($gt->game_id ?? 'N/A'); ?>)</option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Assign To User</label>
<select name="user_id" required>
<option value="">-- Select User --</option>
<?php foreach ($hostingUsers as $hu): ?>
<option value="<?php echo $hu->id; ?>"><?php echo htmlspecialchars($hu->username . ' (' . $hu->email . ')', ENT_QUOTES, 'UTF-8'); ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Max Players</label><input name="max_players" type="number" value="16" min="1"></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px">
<div class="form-group"><label>Port (leave 0 for auto-assign)</label><input name="port" type="number" value="0" min="0" max="65535" placeholder="0 = auto"></div>
<div class="form-group"><label>Steam App ID (optional)</label><input name="app_id" type="text" placeholder="e.g. 4020 for Garry's Mod"></div>
</div>
<button type="submit" class="btn primary" style="margin-top:14px"><i class="bi bi-server"></i> Create & Install</button>
</form>
</div>

<!-- Server List -->
<?php if (!empty($servers)): ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px">
<?php foreach ($servers as $s):
$owner = null;
foreach ($hostingUsers as $hu) { if ($hu->id == $s->user_id) { $owner = $hu; break; } }
$statusColors = ['running'=>'#4ade80','stopped'=>'#f87171','installing'=>'#facc15','suspended'=>'#f97316','error'=>'#f87171'];
$statusColor = $statusColors[$s->status] ?? '#64748b';
?>
<div style="background:var(--bg-card);border:1px solid rgba(0,191,255,.08);border-radius:14px;padding:20px;transition:.2s;position:relative">
<div style="position:absolute;top:0;left:0;width:4px;height:100%;background:<?php echo $statusColor; ?>;border-radius:14px 0 0 14px"></div>
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px">
<div>
<h4 style="margin:0;font-size:14px"><?php echo htmlspecialchars($s->server_name); ?></h4>
<span style="font-size:11px;color:#64748b"><?php echo htmlspecialchars($s->game_type); ?> · Port <?php echo (int)$s->port; ?></span>
</div>
<span style="font-size:11px;font-weight:700;padding:2px 10px;border-radius:6px;background:<?php echo $statusColor; ?>20;color:<?php echo $statusColor; ?>">
<?php echo ucfirst($s->status); ?>
</span>
</div>
<div style="font-size:12px;color:#64748b;margin-bottom:12px;line-height:1.6">
<div>👤 Owner: <?php echo $owner ? htmlspecialchars($owner->username) : 'Unknown'; ?></div>
<div>🎯 Players: <?php echo (int)$s->current_players; ?>/<?php echo (int)$s->max_players; ?></div>
<div>📁 <?php echo htmlspecialchars($s->install_path); ?></div>
</div>
<div style="display:flex;gap:6px;flex-wrap:wrap">
<a href="/admin/games/show/<?php echo $s->id; ?>" class="btn btn-sm primary"><i class="bi bi-eye"></i> Manage</a>
<a href="/admin/games/start/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(74,222,128,.12);color:#4ade80"><i class="bi bi-play-fill"></i></a>
<a href="/admin/games/stop/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171"><i class="bi bi-stop-fill"></i></a>
<?php if ($s->status === 'suspended'): ?>
<a href="/admin/games/unsuspend/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(74,222,128,.12);color:#4ade80"><i class="bi bi-unlock"></i> Unsuspend</a>
<?php else: ?>
<a href="/admin/games/suspend/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(250,204,21,.12);color:#facc15"><i class="bi bi-lock"></i> Suspend</a>
<?php endif; ?>
<a href="/admin/games/uninstall/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171" onclick="return confirm('Uninstall <?php echo htmlspecialchars(addslashes($s->server_name), ENT_QUOTES, 'UTF-8'); ?>?')"><i class="bi bi-trash"></i></a>
</div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<div style="text-align:center;padding:60px 20px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px">
<div style="font-size:48px;margin-bottom:16px;opacity:.3"><i class="bi bi-controller"></i></div>
<h4 style="color:var(--text-muted);margin-bottom:8px">No Game Servers</h4>
<p style="color:#64748b;font-size:13px">Create a server to get started.</p>
</div>
<?php endif; ?>
