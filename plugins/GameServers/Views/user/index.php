<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Total Servers</h3><div class="value"><?php echo count($servers); ?></div></div>
<div class="stat-card"><h3>Running</h3><div class="value" style="color:#4ade80"><?php echo count(array_filter($servers, fn($s) => $s->status === 'running')); ?></div></div>
<div class="stat-card"><h3>Stopped</h3><div class="value" style="color:#f87171"><?php echo count(array_filter($servers, fn($s) => $s->status === 'stopped')); ?></div></div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<!-- My Servers -->
<div class="card" style="margin-bottom:20px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
<h3 style="margin:0;color:var(--accent)">🎮 My Game Servers</h3>
</div>
<?php if (empty($servers)): ?>
<p style="color:#64748b;text-align:center;padding:30px 0">No game servers yet. Purchase one from the store to get started!</p>
<?php else: ?>
<table>
<thead><tr><th>Server</th><th>Game</th><th>Port</th><th>Status</th><th>Players</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($servers as $s): ?>
<tr>
<td><strong><?php echo htmlspecialchars($s->server_name); ?></strong></td>
<td><?php echo htmlspecialchars($s->game_type); ?></td>
<td><?php echo (int)$s->port; ?></td>
<td>
<?php if ($s->status === 'running'): ?><span class="status-badge status-active">● Running</span>
<?php elseif ($s->status === 'installing'): ?><span class="status-badge" style="background:rgba(250,204,21,.12);color:#facc15">⟳ Installing</span>
<?php elseif ($s->status === 'error'): ?><span class="status-badge status-terminated">● Error</span>
<?php else: ?><span class="status-badge status-terminated">● Stopped</span>
<?php endif; ?>
</td>
<td><?php echo (int)$s->current_players; ?> / <?php echo (int)$s->max_players; ?></td>
<td style="white-space:nowrap">
<a href="/user/games/show/<?php echo $s->id; ?>" class="btn btn-sm primary"><i class="bi bi-eye"></i> Manage</a>
<a href="/user/games/start/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(74,222,128,.12);color:#4ade80;border:1px solid rgba(74,222,128,.2)"><i class="bi bi-play-fill"></i></a>
<a href="/user/games/stop/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.2)"><i class="bi bi-stop-fill"></i></a>
<a href="/user/games/uninstall/<?php echo $s->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.2)" onclick="return confirm('Uninstall this game server? All files will be deleted.')"><i class="bi bi-trash"></i></a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>

<!-- Available Games -->
<div class="card">
<h3 style="color:var(--accent);margin-bottom:16px">📦 Available Games</h3>
<p style="color:#64748b;font-size:13px;margin-bottom:16px">Click a game to order and install instantly.</p>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">
<?php foreach ($gameTypes as $gt): ?>
<a href="/game-servers.php?game=<?php echo urlencode($gt->name); ?>" class="btn btn-secondary" style="display:flex;align-items:center;gap:8px;padding:12px 16px;text-align:left;font-size:13px;text-decoration:none">
<span style="font-weight:600;"><?php echo htmlspecialchars($gt->name); ?></span>
<small style="color:#64748b;margin-left:auto">$<?php echo number_format($gt->price_per_slot ?? 0.50, 2); ?>/slot</small>
</a>
<?php endforeach; ?>
</div>
</div>
