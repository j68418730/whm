<div class="card"><h3 style="color:var(--accent)">🎮 My Game Servers</h3>
<p style="color:var(--text-secondary);margin-top:8px">Deploy and manage Linux game servers with one click.</p></div>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">⚡ Available Games</h3>
<div class="page-grid">
<?php foreach ($games as $type => $g): ?>
<a href="/user/games/install/<?php echo $type; ?>" class="action-card" style="padding:16px;text-decoration:none;color:#fff;display:block;text-align:center">
<div style="font-size:28px;margin-bottom:6px"><?php echo $g['icon']; ?></div>
<div style="font-size:13px;font-weight:600"><?php echo htmlspecialchars($g['name']); ?></div>
<div style="font-size:10px;color:#64748b;margin-top:2px">Port <?php echo $g['port']; ?></div>
</a>
<?php endforeach; ?>
</div>
</div>

<div class="card" style="border-color:rgba(250,204,21,.15)">
<h3 style="color:#facc15;margin-bottom:8px">📦 Demo Package</h3>
<p style="color:#64748b;font-size:13px;margin-bottom:8px">Try game hosting with a demo server.</p>
<a href="/user/games/install/sample" class="btn btn-sm" style="background:rgba(250,204,21,.1);border:1px solid rgba(250,204,21,.2);color:#facc15">Install Demo</a>
</div>

<h3 style="margin:16px 0 12px">My Servers (<?php echo count($servers); ?>)</h3>
<?php if (empty($servers)): ?>
<div class="card"><p style="color:#64748b;text-align:center;padding:20px">No servers installed. Click a game above to install.</p></div>
<?php else: ?>
<table><tr><th>Game</th><th>Name</th><th>Port</th><th>Status</th><th>Actions</th></tr>
<?php foreach ($servers as $s): $g = $games[$s->game_type] ?? ['icon' => '🎮', 'name' => $s->game_type]; ?>
<tr>
<td><?php echo $g['icon']; ?> <?php echo htmlspecialchars($g['name']); ?></td>
<td><?php echo htmlspecialchars($s->server_name); ?></td>
<td><?php echo $s->port; ?></td>
<td><?php echo $s->status === 'running' ? '<span style="color:#4ade80">● Running</span>' : ($s->status === 'installing' ? '<span style="color:#facc15">⟳ Installing</span>' : '<span style="color:#f87171">● Stopped</span>'); ?></td>
<td><a href="/user/games/start/<?php echo $s->id; ?>" class="btn btn-sm">▶</a> <a href="/user/games/stop/<?php echo $s->id; ?>" class="btn btn-sm">⏹</a> <a href="/user/games/uninstall/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171" onclick="return confirm('Uninstall?')">🗑</a></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
