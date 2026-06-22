<style>
.server-shell{display:grid;grid-template-columns:2fr 1fr;gap:16px}
.term-box{background:#000;border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:12px;font-family:monospace;font-size:12px;color:#4ade80;height:300px;overflow-y:auto;white-space:pre-wrap}
.term-box::-webkit-scrollbar{width:4px}
.term-box::-webkit-scrollbar-thumb{background:rgba(0,191,255,.2);border-radius:2px}
.editor-box{width:100%;min-height:300px;background:rgba(0,0,0,.5);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:12px;color:#e0e0e0;font-family:monospace;font-size:12px;outline:none;resize:vertical}
</style>

<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div><?php endif; ?>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap">
<h2 style="margin:0;font-size:20px"><?php echo $game['icon']; ?> <?php echo htmlspecialchars($server->server_name); ?></h2>
<span style="font-size:13px;color:#64748b">Port <?php echo $server->port; ?> · <?php echo htmlspecialchars($game['name']); ?></span>
<div style="margin-left:auto;display:flex;gap:6px">
<a href="/admin/games/start/<?php echo $server->id; ?>" class="btn btn-sm" style="background:rgba(74,222,128,.12);color:#4ade80;text-decoration:none;padding:6px 14px;border-radius:6px">▶ Start</a>
<a href="/admin/games/stop/<?php echo $server->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171;text-decoration:none;padding:6px 14px;border-radius:6px">⏹ Stop</a>
<a href="/admin/games/uninstall/<?php echo $server->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#ef4444;text-decoration:none;padding:6px 14px;border-radius:6px" onclick="return confirm('Uninstall?')">🗑 Uninstall</a>
</div>
</div>

<div class="server-shell">

<!-- Main Panel -->
<div>

<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);font-size:14px;margin-bottom:8px"><i class="fas fa-server"></i> Server Status <span id="gsLiveStatus" style="font-size:11px;color:#64748b;font-weight:400">● checking...</span></h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:8px">
<div><span style="color:#64748b;font-size:11px">Status</span><div style="font-size:16px;font-weight:700"><span id="gsRunning"><?php echo $server->status; ?></span></div></div>
<div><span style="color:#64748b;font-size:11px">Players</span><div style="font-size:16px;font-weight:700" id="gsPlayers"><?php echo $server->current_players; ?></div></div>
<div><span style="color:#64748b;font-size:11px">Map</span><div style="font-size:16px;font-weight:700" id="gsMap"><?php echo htmlspecialchars($server->map_name ?: '-'); ?></div></div>
<div><span style="color:#64748b;font-size:11px">PID</span><div style="font-size:16px;font-weight:700"><?php echo $server->pid ?: '-'; ?></div></div>
</div>
</div>

<!-- Console Log -->
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);font-size:14px;margin-bottom:8px"><i class="fas fa-terminal"></i> Console Log</h3>
<div class="term-box" id="gsConsole"><span style="color:#64748b">Waiting for log data...</span></div>
</div>

<!-- Run Command -->
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);font-size:14px;margin-bottom:8px"><i class="fas fa-terminal"></i> Run Command</h3>
<form method="POST" action="/admin/games/command/<?php echo $server->id; ?>" style="display:flex;gap:8px">
<input name="cmd" placeholder="e.g. say Hello, restart, status" required style="flex:1;padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none;font-size:13px;font-family:monospace">
<button type="submit" class="btn btn-sm primary">Execute</button>
</form>
</div>

<!-- Config Editor -->
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);font-size:14px;margin-bottom:8px"><i class="fas fa-file-code"></i> Config Editor (server.cfg / ini)</h3>
<form method="POST" action="/admin/games/save-config/<?php echo $server->id; ?>">
<?php
$configContent = '';
$configPath = $server->config_path ?: $server->install_path . '/server.cfg';
if (file_exists($configPath)) $configContent = file_get_contents($configPath);
?>
<textarea name="config_content" class="editor-box" placeholder="; Server configuration file&#10;hostname &quot;My Server&quot;&#10;sv_password &quot;&#10;rcon_password &quot;mypass&quot;"><?php echo htmlspecialchars($configContent); ?></textarea>
<div style="margin-top:8px">
<button type="submit" class="btn btn-sm primary">💾 Save Config</button>
</div>
</form>
</div>

</div>

<!-- Info Panel -->
<div>
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);font-size:14px;margin-bottom:8px">📋 Server Info</h3>
<div style="font-size:12px;display:grid;gap:6px">
<div><span style="color:#64748b">Game:</span> <?php echo $game['icon']; ?> <?php echo htmlspecialchars($game['name']); ?></div>
<div><span style="color:#64748b">Port:</span> <?php echo $server->port; ?></div>
<div><span style="color:#64748b">Query Port:</span> <?php echo $server->query_port; ?></div>
<div><span style="color:#64748b">Install Path:</span><br><code style="font-size:11px"><?php echo htmlspecialchars($server->install_path); ?></code></div>
<div><span style="color:#64748b">RCon Password:</span> <?php echo $server->rcon_password ? 'Set' : 'Not set'; ?></div>
<div><span style="color:#64748b">Created:</span> <?php echo $server->created_at; ?></div>
</div>
</div>

<div class="card">
<h3 style="color:var(--accent);font-size:14px;margin-bottom:8px">🔌 Quick Actions</h3>
<div style="display:flex;flex-direction:column;gap:6px">
<a href="/admin/games/install/<?php echo $server->game_type; ?>" style="padding:8px 12px;background:rgba(0,140,255,.06);border:1px solid rgba(0,191,255,.1);border-radius:6px;text-decoration:none;color:#e0e0e0;font-size:13px">🔄 Reinstall Game Files</a>
<a href="#" onclick="fetch('/admin/games/status/<?php echo $server->id; ?>').then(r=>r.json()).then(d=>alert('Players: '+d.players+'\nMap: '+d.map+'\nRunning: '+d.running))" style="padding:8px 12px;background:rgba(0,140,255,.06);border:1px solid rgba(0,191,255,.1);border-radius:6px;text-decoration:none;color:#e0e0e0;font-size:13px">🔍 Refresh Status</a>
</div>
</div>
</div>
</div>

<script>
// Auto-refresh status every 10s
setInterval(function() {
    var id = <?php echo $server->id; ?>;
    fetch('/admin/games/status/' + id).then(function(r) { return r.json(); }).then(function(d) {
        var run = document.getElementById('gsRunning');
        var players = document.getElementById('gsPlayers');
        var map = document.getElementById('gsMap');
        var status = document.getElementById('gsLiveStatus');
        if (run) run.innerHTML = d.running ? '<span style="color:#4ade80">● Running</span>' : '<span style="color:#f87171">● Stopped</span>';
        if (players) players.textContent = d.players;
        if (map) map.textContent = d.map || '-';
        if (status) status.innerHTML = d.running ? '<span style="color:#4ade80">● live</span>' : '<span style="color:#f87171">● offline</span>';
    });
}, 10000);

// Load console log
function loadLog() {
    var term = document.getElementById('gsConsole');
    var id = <?php echo $server->id; ?>;
    fetch('/admin/games/status/' + id).then(function(r) { return r.json(); }).then(function(d) {
        if (d.log) term.textContent = d.log;
    });
}
setInterval(loadLog, 5000);
</script>
