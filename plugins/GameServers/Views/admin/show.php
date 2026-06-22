<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px">
<div>
<a href="/admin/games" style="color:#64748b;font-size:13px;text-decoration:none">← Back to Game Servers</a>
<h2 style="margin:8px 0 0">🎮 <?php echo htmlspecialchars($server->server_name); ?></h2>
<p style="color:#64748b;font-size:13px"><?php echo htmlspecialchars($server->game_type); ?> · Port <?php echo (int)$server->port; ?> · PID: <?php echo $server->pid ?: 'N/A'; ?></p>
</div>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<span class="status-badge" style="background:<?php echo $server->status === 'running' ? 'rgba(74,222,128,.12);color:#4ade80' : ($server->status === 'suspended' ? 'rgba(250,204,21,.12);color:#facc15' : 'rgba(248,113,113,.12);color:#f87171'); ?>;font-size:13px;padding:6px 16px">
● <?php echo ucfirst($server->status); ?>
</span>
</div>
</div>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card">
<h3>Controls</h3>
<div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap">
<a href="/admin/games/start/<?php echo $server->id; ?>" class="btn btn-sm" style="background:rgba(74,222,128,.12);color:#4ade80;border:1px solid rgba(74,222,128,.2)"><i class="bi bi-play-fill"></i> Start</a>
<a href="/admin/games/stop/<?php echo $server->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.2)"><i class="bi bi-stop-fill"></i> Stop</a>
<a href="/admin/games/restart/<?php echo $server->id; ?>" class="btn btn-sm secondary"><i class="bi bi-arrow-clockwise"></i> Restart</a>
<a href="/admin/games/uninstall/<?php echo $server->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.2)" onclick="return confirm('Uninstall this server?')"><i class="bi bi-trash"></i> Uninstall</a>
</div>
</div>
<div class="stat-card">
<h3>Status</h3>
<div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap">
<?php if ($server->status === 'suspended'): ?>
<a href="/admin/games/unsuspend/<?php echo $server->id; ?>" class="btn btn-sm" style="background:rgba(74,222,128,.12);color:#4ade80"><i class="bi bi-unlock"></i> Unsuspend</a>
<?php else: ?>
<a href="/admin/games/suspend/<?php echo $server->id; ?>" class="btn btn-sm" style="background:rgba(250,204,21,.12);color:#facc15"><i class="bi bi-lock"></i> Suspend</a>
<?php endif; ?>
</div>
</div>
<div class="stat-card">
<h3>Owner</h3>
<p style="font-size:14px;color:#fff;margin-top:6px"><?php echo $owner ? htmlspecialchars($owner->username) . ' (' . htmlspecialchars($owner->email) . ')' : 'Unknown'; ?></p>
</div>
<div class="stat-card">
<h3>Reassign</h3>
<form method="POST" action="/admin/games/assign/<?php echo $server->id; ?>" style="display:flex;gap:6px;margin-top:6px">
<select name="user_id" style="flex:1;padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px">
<?php foreach ($hostingUsers as $hu): ?>
<option value="<?php echo $hu->id; ?>"<?php if ($hu->id == $server->user_id) echo ' selected'; ?>><?php echo htmlspecialchars($hu->username); ?></option>
<?php endforeach; ?>
</select>
<button type="submit" class="btn btn-sm primary"><i class="bi bi-arrow-repeat"></i></button>
</form>
</div>
</div>

<!-- Console -->
<div class="card" style="margin-bottom:16px">
<h4 style="margin-bottom:12px;color:var(--accent)"><i class="bi bi-terminal"></i> Console</h4>
<div style="background:#000;border:1px solid rgba(255,255,255,.06);border-radius:8px;padding:12px;max-height:250px;overflow-y:auto;font-family:monospace;font-size:12px;color:#4ade80;margin-bottom:12px;white-space:pre-wrap"><?php echo htmlspecialchars($consoleLog ?: 'No output yet.'); ?></div>
<form method="POST" action="/admin/games/command/<?php echo $server->id; ?>" style="display:flex;gap:8px">
<input type="text" name="cmd" placeholder="Enter command..." style="flex:1;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.4);color:#fff;font-family:monospace;font-size:13px;outline:none">
<button type="submit" class="btn btn-sm primary"><i class="bi bi-send"></i> Send</button>
</form>
</div>

<!-- Config -->
<div class="card">
<h4 style="margin-bottom:12px;color:var(--accent)"><i class="bi bi-file-earmark-text"></i> Configuration</h4>
<form method="POST" action="/admin/games/save-config/<?php echo $server->id; ?>">
<textarea name="config_content" rows="15" style="width:100%;padding:12px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.4);color:#fff;font-family:monospace;font-size:12px;outline:none;resize:vertical"><?php echo htmlspecialchars($configContent ?: '# Configuration for ' . $server->server_name . "\n# Edit settings below and click Save."); ?></textarea>
<div style="display:flex;gap:8px;margin-top:10px;justify-content:space-between;align-items:center">
<button type="submit" class="btn btn-sm primary"><i class="bi bi-floppy"></i> Save Config</button>
<span style="font-size:11px;color:#64748b"><?php echo htmlspecialchars($server->config_path ?: $server->install_path . '/server.cfg'); ?></span>
</div>
</form>
</div>
