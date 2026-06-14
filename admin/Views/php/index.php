<div class="stats-grid">
<div class="stat-card"><h3>Extensions Loaded</h3><div class="value"><?php echo count($loaded); ?></div></div>
<div class="stat-card"><h3>Available</h3><div class="value"><?php echo count($available); ?></div></div>
<div class="stat-card"><h3>PHP Version</h3><div class="value" style="font-size:20px"><?php echo PHP_VERSION; ?></div></div>
</div>
<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">Installed Extensions</h3>
<div style="display:flex;flex-wrap:wrap;gap:6px"><?php foreach ($loaded as $e): ?><span style="padding:4px 10px;border-radius:5px;font-size:12px;background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.2);color:#4ade80"><?php echo $e; ?></span><?php endforeach; ?></div>
</div>
<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">Available to Install</h3>
<div style="display:flex;flex-wrap:wrap;gap:6px"><?php foreach ($available as $e): ?><a href="/admin/php/install/<?php echo $e; ?>" style="padding:4px 10px;border-radius:5px;font-size:12px;background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);color:#f87171;text-decoration:none;display:inline-block" onclick="return confirm('Install <?php echo $e; ?>?')"><?php echo $e; ?></a><?php endforeach; ?></div>
</div>
