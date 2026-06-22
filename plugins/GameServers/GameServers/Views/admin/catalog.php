<h2 style="margin-bottom:16px">🎮 Game Catalog</h2>

<div class="card" style="margin-bottom:16px">
<p style="color:#64748b;font-size:13px;margin:0">Supported game profiles for package setup and one-click installs.</p>
</div>

<div class="stats-grid" style="margin-bottom:16px">
<div class="stat-card"><h3>Supported Games</h3><div class="value"><?php echo count($games); ?></div><div class="label">Profiles available</div></div>
</div>

<div class="pkg-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px">
<?php foreach ($games as $game): ?>
<div class="card" style="padding:16px">
<div style="font-size:28px;margin-bottom:8px"><?php echo $game['icon']; ?></div>
<div style="font-size:15px;font-weight:700;margin-bottom:4px"><?php echo htmlspecialchars($game['name']); ?></div>
<div style="font-size:11px;color:#64748b;margin-bottom:10px"><?php echo htmlspecialchars($game['note']); ?></div>
<div style="display:flex;justify-content:space-between;font-size:11px;color:#94a3b8">
<span>App ID: <?php echo (int)$game['app_id']; ?></span>
<span><?php echo htmlspecialchars($game['type']); ?></span>
</div>
</div>
<?php endforeach; ?>
</div>
