<div class="card"><h3>🎮 <?php echo htmlspecialchars($server->server_name); ?></h3>
<p>Type: <?php echo htmlspecialchars($games[$server->game_type] ?? $server->game_type); ?> | Port: <?php echo $server->port; ?></p>
<p>Status: <span id="gstatus">Loading...</span></p>
<div style="display:flex;gap:8px;margin:12px 0">
<a href="/user/games/start/<?php echo $server->id; ?>" class="btn btn-sm btn-primary">Start</a>
<a href="/user/games/stop/<?php echo $server->id; ?>" class="btn btn-sm btn-secondary">Stop</a>
<a href="/user/games/uninstall/<?php echo $server->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Uninstall?')">Delete</a>
</div>
<form method="post" action="/user/games/command/<?php echo $server->id; ?>" style="margin-bottom:12px">
<input name="cmd" placeholder="Enter command" style="width:70%;display:inline"><button class="btn btn-sm btn-primary" style="width:auto">Send</button>
</form>
<form method="post" action="/user/games/save-config/<?php echo $server->id; ?>">
<textarea name="config_content" rows="10" style="font-family:monospace;font-size:12px"><?php echo htmlspecialchars(file_exists($server->config_path ?: $server->install_path.'/server.cfg') ? file_get_contents($server->config_path ?: $server->install_path.'/server.cfg') : ''); ?></textarea>
<button class="btn btn-primary">Save Config</button>
</form>
</div>
<script>
fetch('/user/games/status/<?php echo $server->id; ?>').then(r=>r.json()).then(d=>document.getElementById('gstatus').textContent=d.status||'Unknown');
</script>
