<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px">
<div>
<a href="/admin/games/templates" style="color:#64748b;font-size:13px;text-decoration:none">← Back to Templates</a>
<h2 style="margin:8px 0 0"><?php echo htmlspecialchars($template->name); ?></h2>
<p style="color:#64748b;font-size:13px">App <?php echo htmlspecialchars($template->appid ?: 'N/A'); ?> · <?php echo htmlspecialchars($template->engine); ?> · <?php echo htmlspecialchars($template->category); ?> · Slots <?php echo (int)$template->min_slots; ?>-<?php echo (int)$template->max_slots; ?></p>
</div>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<button class="btn btn-sm primary" onclick="document.getElementById('editForm').style.display = document.getElementById('editForm').style.display === 'none' ? 'block' : 'none'"><i class="bi bi-pencil"></i> Edit Template</button>
</div>
</div>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Ports</h3><p style="font-size:14px;margin-top:6px">Query: <?php echo (int)$template->query_port; ?> · Game: <?php echo (int)$template->game_port; ?> · RCON: <?php echo (int)$template->rcon_port; ?></p></div>
<div class="stat-card"><h3>Slots</h3><p style="font-size:14px;margin-top:6px">Default: <?php echo (int)$template->default_slots; ?> · Min: <?php echo (int)$template->min_slots; ?> · Max: <?php echo (int)$template->max_slots; ?></p></div>
<div class="stat-card"><h3>Login</h3><p style="font-size:14px;margin-top:6px"><?php echo htmlspecialchars($template->steamcmd_login ?: 'N/A'); ?> · <?php echo $template->anonymous_login ? 'Anonymous' : 'Named'; ?> <?php echo $template->steam_client ? '· Client' : ''; ?></p></div>
<div class="stat-card"><h3>Platform</h3><p style="font-size:14px;margin-top:6px"><?php echo $template->supports_linux ? 'Linux' : ''; ?><?php echo $template->supports_linux && $template->supports_windows ? ' + ' : ''; ?><?php echo $template->supports_windows ? 'Windows' : ''; ?></p></div>
</div>

<!-- Description -->
<?php if ($template->description): ?>
<div class="card" style="margin-bottom:16px">
<h4 style="margin-bottom:8px;color:var(--accent)">Description</h4>
<p style="color:#64748b;font-size:13px;line-height:1.6"><?php echo nl2br(htmlspecialchars($template->description)); ?></p>
</div>
<?php endif; ?>

<!-- Edit Form -->
<div id="editForm" class="card" style="display:none;margin-bottom:20px">
<h4 style="margin-bottom:12px;color:var(--accent)"><i class="bi bi-pencil"></i> Edit Template</h4>
<form method="POST" action="/admin/games/templates/store">
<input type="hidden" name="id" value="<?php echo $template->id; ?>">
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
<div class="form-group"><label>Game Name</label><input name="name" value="<?php echo htmlspecialchars($template->name); ?>" required></div>
<div class="form-group"><label>AppID</label><input name="appid" value="<?php echo htmlspecialchars($template->appid); ?>"></div>
<div class="form-group"><label>Engine</label>
<select name="engine">
<?php foreach (['Source','Unreal','Unity','Java','Native','Frostbite','Real Virtuality','Enfusion','id Tech','CryEngine','Dagor'] as $e): ?>
<option value="<?php echo $e; ?>" <?php if ($template->engine === $e) echo 'selected'; ?>><?php echo $e; ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Category</label>
<select name="category">
<?php foreach (['FPS','Survival','Sandbox','RPG','Simulation','Racing','Military'] as $c): ?>
<option value="<?php echo $c; ?>" <?php if ($template->category === $c) echo 'selected'; ?>><?php echo $c; ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>SteamCMD Login</label><input name="steamcmd_login" value="<?php echo htmlspecialchars($template->steamcmd_login); ?>"></div>
<div class="form-group"><label>Default Slots</label><input name="default_slots" type="number" value="<?php echo (int)$template->default_slots; ?>"></div>
<div class="form-group"><label>Min Slots</label><input name="min_slots" type="number" value="<?php echo (int)$template->min_slots; ?>"></div>
<div class="form-group"><label>Max Slots</label><input name="max_slots" type="number" value="<?php echo (int)$template->max_slots; ?>"></div>
<div class="form-group"><label>Query Port</label><input name="query_port" type="number" value="<?php echo (int)$template->query_port; ?>"></div>
<div class="form-group"><label>Game Port</label><input name="game_port" type="number" value="<?php echo (int)$template->game_port; ?>"></div>
<div class="form-group"><label>RCON Port</label><input name="rcon_port" type="number" value="<?php echo (int)$template->rcon_port; ?>"></div>
</div>
<div style="display:flex;gap:16px;margin:14px 0;flex-wrap:wrap">
<label style="display:flex;align-items:center;gap:6px;font-size:13px"><input type="checkbox" name="steam_client" value="1" <?php if ($template->steam_client) echo 'checked'; ?>> Steam Client</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px"><input type="checkbox" name="anonymous_login" value="1" <?php if ($template->anonymous_login) echo 'checked'; ?>> Anonymous Login</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px"><input type="checkbox" name="requires_game_purchase" value="1" <?php if ($template->requires_game_purchase) echo 'checked'; ?>> Requires Purchase</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px"><input type="checkbox" name="supports_linux" value="1" <?php if ($template->supports_linux) echo 'checked'; ?>> Linux</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px"><input type="checkbox" name="supports_windows" value="1" <?php if ($template->supports_windows) echo 'checked'; ?>> Windows</label>
<select name="status" style="padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px">
<option value="active" <?php if ($template->status === 'active') echo 'selected'; ?>>Active</option>
<option value="inactive" <?php if ($template->status === 'inactive') echo 'selected'; ?>>Inactive</option>
</select>
</div>
<div class="form-group"><label>Description</label><textarea name="description" rows="2" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px;resize:vertical"><?php echo htmlspecialchars($template->description ?? ''); ?></textarea></div>
<div class="form-group"><label>Notes</label><textarea name="notes" rows="2" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px;resize:vertical"><?php echo htmlspecialchars($template->notes ?? ''); ?></textarea></div>
<button type="submit" class="btn primary" style="margin-top:8px"><i class="bi bi-floppy"></i> Save Changes</button>
</form>
</div>

<!-- Script Preview Tabs -->
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
<button class="btn btn-sm primary" onclick="showTab('install')">Install Script</button>
<button class="btn btn-sm secondary" onclick="showTab('start')">Start Script</button>
<button class="btn btn-sm secondary" onclick="showTab('stop')">Stop Script</button>
<button class="btn btn-sm secondary" onclick="showTab('restart')">Restart Script</button>
<button class="btn btn-sm secondary" onclick="showTab('config')">Config Template</button>
</div>

<div id="tab-install" class="card tab-content" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
<h4 style="margin:0;color:var(--accent)"><i class="bi bi-download"></i> Install Script</h4>
</div>
<textarea readonly rows="4" style="width:100%;padding:12px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.5);color:#4ade80;font-family:monospace;font-size:12px;outline:none;resize:vertical"><?php echo htmlspecialchars($installScript ?? $template->install_script); ?></textarea>
<div style="margin-top:8px;font-size:11px;color:#64748b">Variables: {INSTALL_DIR}, {STEAMCMD_LOGIN}, {APPID}</div>
</div>

<div id="tab-start" class="card tab-content" style="display:none;margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
<h4 style="margin:0;color:var(--accent)"><i class="bi bi-play-fill"></i> Start Script</h4>
</div>
<textarea readonly rows="6" style="width:100%;padding:12px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.5);color:#4ade80;font-family:monospace;font-size:12px;outline:none;resize:vertical"><?php echo htmlspecialchars($startScript ?? $template->start_command); ?></textarea>
<div style="margin-top:8px;font-size:11px;color:#64748b">Variables: {INSTALL_DIR}, {PORT}, {MAX_PLAYERS}, {MAP}, {SERVER_NAME}, {RCON_PORT}, {PASSWORD}, {MOTD}, {ADMIN_LIST}, {WORKSHOP_COLLECTIONS}</div>
</div>

<div id="tab-stop" class="card tab-content" style="display:none;margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
<h4 style="margin:0;color:var(--accent)"><i class="bi bi-stop-fill"></i> Stop Script</h4>
</div>
<textarea readonly rows="4" style="width:100%;padding:12px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.5);color:#4ade80;font-family:monospace;font-size:12px;outline:none;resize:vertical"><?php echo htmlspecialchars($stopScript ?? $template->stop_command); ?></textarea>
</div>

<div id="tab-restart" class="card tab-content" style="display:none;margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
<h4 style="margin:0;color:var(--accent)"><i class="bi bi-arrow-clockwise"></i> Restart Script</h4>
</div>
<textarea readonly rows="6" style="width:100%;padding:12px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.5);color:#4ade80;font-family:monospace;font-size:12px;outline:none;resize:vertical"><?php echo htmlspecialchars($restartScript ?? $template->restart_command); ?></textarea>
</div>

<div id="tab-config" class="card tab-content" style="display:none;margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
<h4 style="margin:0;color:var(--accent)"><i class="bi bi-file-earmark-text"></i> Config Template</h4>
<span style="font-size:11px;color:#64748b">Variables: {SERVER_NAME}, {PORT}, {MAX_PLAYERS}, {MAP}, {PASSWORD}, {RCON_PASSWORD}, {RCON_PORT}, {QUERY_PORT}, {MOTD}, {ADMIN_LIST}, {SERVER_IP}, {WORKSHOP_COLLECTIONS}</span>
</div>
<textarea readonly rows="12" style="width:100%;padding:12px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.5);color:#4ade80;font-family:monospace;font-size:12px;outline:none;resize:vertical"><?php echo htmlspecialchars($configContent ?? $template->config_template); ?></textarea>
</div>

<!-- Raw Template Data -->
<div class="card" style="margin-bottom:16px">
<h4 style="margin-bottom:8px;color:var(--accent)"><i class="bi bi-code"></i> Raw Install Script Template</h4>
<div class="form-group"><label>Install Script</label>
<textarea name="install_script_raw" readonly rows="3" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-family:monospace;font-size:12px;resize:vertical"><?php echo htmlspecialchars($template->install_script); ?></textarea></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:12px">
<div class="form-group"><label>Start Command</label>
<textarea readonly rows="4" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-family:monospace;font-size:12px;resize:vertical"><?php echo htmlspecialchars($template->start_command); ?></textarea></div>
<div class="form-group"><label>Stop Command</label>
<textarea readonly rows="4" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-family:monospace;font-size:12px;resize:vertical"><?php echo htmlspecialchars($template->stop_command); ?></textarea></div>
</div>
<div class="form-group" style="margin-top:12px"><label>Config Template</label>
<textarea readonly rows="8" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-family:monospace;font-size:12px;resize:vertical"><?php echo htmlspecialchars($template->config_template); ?></textarea></div>
</div>

<script>
function showTab(tab) {
    var tabs = ['install','start','stop','restart','config'];
    tabs.forEach(function(t) {
        var el = document.getElementById('tab-' + t);
        if (el) el.style.display = t === tab ? 'block' : 'none';
    });
}
</script>
