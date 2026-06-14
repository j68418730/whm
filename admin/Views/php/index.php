<?php if (!empty($phpConfig)): ?>
<div class="card" style="max-width:700px;margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">PHP Configuration</h3>
<table><tr><th>Directive</th><th>Value</th></tr>
<?php foreach ($phpConfig as $k => $v): ?>
<tr><td style="font-family:monospace"><?php echo htmlspecialchars($k); ?></td><td><?php echo htmlspecialchars($v ?? ''); ?></td></tr>
<?php endforeach; ?></table>
<a href="/admin/php" class="btn secondary" style="margin-top:8px">&larr; Back to PHP Manager</a>
</div>
<?php else: ?>
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
<?php endif; ?>
