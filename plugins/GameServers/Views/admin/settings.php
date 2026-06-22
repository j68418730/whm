<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<h3 style="color:var(--accent);margin-bottom:16px">⚙️ Game Server Settings</h3>
<p style="color:#64748b;margin-bottom:24px;font-size:14px">Configure SteamCMD credentials, default install paths, and port ranges for game server deployment.</p>

<div class="card" style="max-width:600px">
<form method="POST" action="/admin/games/settings/save">
<div class="form-group"><label>Steam Username</label>
<input name="steam_username" value="<?php echo htmlspecialchars($steam_username ?? 'planet_hosts_dev', ENT_QUOTES, 'UTF-8'); ?>" placeholder="planet_hosts_dev">
<small style="color:#64748b;font-size:11px">Steam account username for downloading game servers.</small>
</div>
<div class="form-group"><label>Steam Password</label>
<input name="steam_password" type="password" value="<?php echo htmlspecialchars($steam_password ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Leave blank for anonymous">
<small style="color:#64748b;font-size:11px">Most games support anonymous login.</small>
</div>
<div class="form-group"><label>Default Install Directory</label>
<input name="game_install_dir" value="<?php echo htmlspecialchars($game_install_dir ?? '/home/gameservers', ENT_QUOTES, 'UTF-8'); ?>" placeholder="/home/gameservers">
</div>
<div class="form-group"><label>Default Game Port</label>
<input name="game_default_port" value="<?php echo htmlspecialchars($game_default_port ?? '27015', ENT_QUOTES, 'UTF-8'); ?>" placeholder="27015">
</div>
<button type="submit" class="btn primary"><i class="bi bi-floppy"></i> Save Settings</button>
</form>
</div>

<div class="card" style="max-width:600px;margin-top:20px">
<h4 style="color:var(--accent);margin-bottom:12px">📊 Port Ranges</h4>
<p style="color:#64748b;font-size:13px;margin-bottom:12px">Port ranges managed by PortManager (<code>core/PortManager.php</code>):</p>
<ul style="list-style:none;padding:0;font-size:13px;color:#94a3b8">
<li><strong style="color:#e0e0e0">Game Servers:</strong> 27000 - 28000</li>
<li><strong style="color:#e0e0e0">Minecraft:</strong> 25560 - 25660</li>
<li><strong style="color:#e0e0e0">Icecast:</strong> 6000 - 10000</li>
<li><strong style="color:#e0e0e0">Voice:</strong> 10000 - 20000</li>
</ul>
</div>
