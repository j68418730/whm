<style>
.gs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin-bottom:16px}
.gs-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;text-align:center}
.gs-card .num{font-size:22px;font-weight:800;color:var(--accent)}
.gs-card .lbl{font-size:11px;color:#64748b}
</style>
<h2>🎮 Game Servers</h2>
<p style="color:#64748b;margin-bottom:16px">Manage your game servers.</p>
<?php
$gameServers = [];
try { $gameServers = $this->db->table('game_servers')->where('user_id', $hosting->id ?? 0)->get() ?: []; } catch (\Exception $e) {}
?>
<div class="gs-grid">
<div class="gs-card"><div class="num"><?php echo count($gameServers); ?></div><div class="lbl">Servers</div></div>
<div class="gs-card"><div class="num"><?php echo count(array_filter($gameServers, function($s){return $s->status==='running';})); ?></div><div class="lbl">Running</div></div>
<div class="gs-card"><div class="num">0</div><div class="lbl">Players</div></div>
</div>
<div class="card"><h3>My Game Servers</h3>
<?php if (empty($gameServers)): ?>
<p style="color:#64748b;font-size:13px;text-align:center;padding:30px">No game servers yet.</p>
<?php else: ?>
<table class="table"><thead><tr><th>Name</th><th>Game</th><th>Status</th><th>Players</th><th></th></tr></thead>
<tbody><?php foreach($gameServers as $gs): ?>
<tr><td><?php echo htmlspecialchars($gs->server_name ?? 'Server'); ?></td><td><?php echo htmlspecialchars($gs->game ?? '-'); ?></td>
<td><span style="color:<?php echo $gs->status==='running'?'#4ade80':'#64748b'; ?>">● <?php echo $gs->status ?? 'stopped'; ?></span></td>
<td>0/<?php echo $gs->max_players ?? 0; ?></td>
<td style="display:flex;gap:3px">
<a href="/user/games?start=<?php echo $gs->id; ?>" class="btn btn-sm btn-success">▶ Start</a>
<a href="/user/games?stop=<?php echo $gs->id; ?>" class="btn btn-sm btn-warning">⏹ Stop</a>
</td></tr>
<?php endforeach; ?></tbody></table>
<?php endif; ?>
</div>
