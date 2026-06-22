<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px">
<div>
<a href="/user/games" style="color:#64748b;font-size:13px;text-decoration:none">← Back to Game Servers</a>
<h2 style="margin:8px 0 0">🎮 <?php echo htmlspecialchars($server->server_name); ?></h2>
<p style="color:#64748b;font-size:13px"><?php echo htmlspecialchars($server->game_type); ?> — Port <?php echo (int)$server->port; ?></p>
</div>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<a href="/user/games/start/<?php echo $server->id; ?>" class="btn btn-sm" style="background:rgba(74,222,128,.12);color:#4ade80;border:1px solid rgba(74,222,128,.2)"><i class="bi bi-play-fill"></i> Start</a>
<a href="/user/games/stop/<?php echo $server->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.2)"><i class="bi bi-stop-fill"></i> Stop</a>
<a href="/user/games/restart/<?php echo $server->id; ?>" class="btn btn-sm secondary"><i class="bi bi-arrow-clockwise"></i> Restart</a>
<a href="/user/games/uninstall/<?php echo $server->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.2)" onclick="return confirm('Uninstall this game server? All files will be deleted.')"><i class="bi bi-trash"></i> Uninstall</a>
</div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
<div class="stat-card"><h3>Status</h3>
<div class="value" style="font-size:24px">
<?php if ($server->status === 'running'): ?><span style="color:#4ade80">● Running</span>
<?php elseif ($server->status === 'installing'): ?><span style="color:#facc15">⟳ Installing</span>
<?php elseif ($server->status === 'error'): ?><span style="color:#f87171">● Error</span>
<?php else: ?><span style="color:#f87171">● Stopped</span><?php endif; ?>
</div></div>
<div class="stat-card"><h3>Players</h3><div class="value" style="font-size:24px"><?php echo (int)$server->current_players; ?> / <?php echo (int)$server->max_players; ?></div></div>
<div class="stat-card"><h3>Install Path</h3><p style="font-size:12px;color:#94a3b8;word-break:break-all"><?php echo htmlspecialchars($server->install_path); ?></p></div>
<div class="stat-card"><h3>FTP Access</h3><p style="font-size:12px;color:#94a3b8">Connect via FTP to your account. Game files are in the <code>gameservers/</code> directory.</p>
<a href="/user/ftp" class="btn btn-sm secondary" style="margin-top:6px"><i class="bi bi-ftp"></i> FTP Manager</a>
</div>
</div>

<!-- Console -->
<div class="card" style="margin-bottom:16px">
<h4 style="margin-bottom:12px;color:var(--accent)"><i class="bi bi-terminal"></i> Console</h4>
<div style="background:#000;border:1px solid rgba(255,255,255,.06);border-radius:8px;padding:12px;max-height:200px;overflow-y:auto;font-family:monospace;font-size:12px;color:#4ade80;margin-bottom:12px;white-space:pre-wrap" id="consoleOutput"><?php echo htmlspecialchars($consoleLog ?: 'No output yet.'); ?></div>
<form method="POST" action="/user/games/command/<?php echo $server->id; ?>" style="display:flex;gap:8px">
<input type="text" name="cmd" placeholder="Enter command..." style="flex:1;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.4);color:#fff;font-family:monospace;font-size:13px;outline:none">
<button type="submit" class="btn btn-sm primary"><i class="bi bi-send"></i> Send</button>
</form>
</div>

<!-- Config Editor -->
<div class="card">
<h4 style="margin-bottom:12px;color:var(--accent)"><i class="bi bi-file-earmark-text"></i> Configuration</h4>
<form method="POST" action="/user/games/save-config/<?php echo $server->id; ?>">
<textarea name="config_content" rows="12" style="width:100%;padding:12px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.4);color:#fff;font-family:monospace;font-size:12px;outline:none;resize:vertical"><?php echo htmlspecialchars($configContent ?: '# Configuration file for ' . $server->server_name . "\n# Edit settings below and click Save."); ?></textarea>
<div style="display:flex;gap:8px;margin-top:10px">
<button type="submit" class="btn btn-sm primary"><i class="bi bi-floppy"></i> Save Config</button>
<span style="font-size:11px;color:#64748b;align-self:center">
Config file: <?php echo htmlspecialchars($server->config_path ?: $server->install_path . '/server.cfg'); ?>
</span>
</div>
</form>
</div>
