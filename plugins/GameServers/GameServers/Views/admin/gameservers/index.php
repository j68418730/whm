<h2 style="margin-bottom:16px">🎮 Game Servers</h2>

<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div><?php endif; ?>

<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">⚡ Create Game Server</h3>
<form method="POST" action="/admin/games/create" style="display:flex;gap:8px;flex-wrap:wrap">
<input name="name" placeholder="Server Name (e.g. My CS2 Server)" required style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none;flex:2;min-width:180px">
<input name="app_id" type="number" placeholder="Steam App ID (0 = demo)" value="0" style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none;width:100px">
<input name="port" type="number" placeholder="Port" value="27015" style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none;width:80px">
<input name="max_players" type="number" placeholder="Max Players" value="16" style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none;width:80px">
<button type="submit" class="btn btn-sm primary">Install</button>
</form>
<p style="font-size:11px;color:#64748b;margin-top:6px">Enter a Steam App ID from <a href="https://developer.valvesoftware.com/wiki/Dedicated_Servers_List" target="_blank" style="color:#38bdf8">Valve's list</a>. Use App ID 0 for a demo/test server.</p>
</div>

<script>
setInterval(function() {
    document.querySelectorAll('[data-sid]').forEach(function(el) {
        fetch('/admin/games/status/' + el.dataset.sid).then(function(r){return r.json()}).then(function(d){
            var s = el.querySelector('.gs-status');
            if(s) s.innerHTML = d.running ? '<span style="color:#4ade80">● Running</span>' : '<span style="color:#f87171">● Stopped</span>';
        }).catch(function(){});
    });
}, 10000);
</script>

<h3 style="margin:16px 0 12px">Servers (<?php echo count($servers); ?>)</h3>
<?php if (empty($servers)): ?>
<div class="card"><p style="color:#64748b;text-align:center;padding:20px">No game servers yet. Create one above.</p></div>
<?php else: ?>
<table><tr><th>Name</th><th>Port</th><th>Status</th><th>Players</th><th>Actions</th></tr>
<?php foreach ($servers as $s): ?>
<tr data-sid="<?php echo $s->id; ?>">
<td><strong><?php echo htmlspecialchars($s->server_name); ?></strong> <?php if ($s->is_demo): ?><span style="color:#facc15;font-size:10px">DEMO</span><?php endif; ?></td>
<td><?php echo $s->port; ?></td>
<td class="gs-status"><?php echo $s->status === 'running' ? '<span style="color:#4ade80">● Running</span>' : ($s->status === 'installing' ? '<span style="color:#facc15">⟳ Installing</span>' : '<span style="color:#f87171">● Stopped</span>'); ?></td>
<td><?php echo $s->current_players; ?></td>
<td style="display:flex;gap:4px">
<a href="/admin/games/show/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(0,140,255,.1);color:#38bdf8;text-decoration:none">📊</a>
<a href="/admin/games/start/<?php echo $s->id; ?>" class="btn btn-sm secondary" style="padding:4px 8px;font-size:11px">▶</a>
<a href="/admin/games/stop/<?php echo $s->id; ?>" class="btn btn-sm secondary" style="padding:4px 8px;font-size:11px">⏹</a>
<a href="/admin/games/uninstall/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171;padding:4px 8px;font-size:11px" onclick="return confirm('Uninstall?')">🗑</a>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
