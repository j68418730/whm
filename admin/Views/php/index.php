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
<style>
.ph-vers{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.ph-ver{padding:10px 16px;border-radius:9px;border:1px solid #1c3d60;background:#0c2340;color:#d4e1ef;text-decoration:none;font-size:13px;font-weight:600;transition:.15s;text-align:center;min-width:70px}
.ph-ver:hover{border-color:#1683ff;color:#fff}
.ph-ver.active{background:linear-gradient(90deg,#147ff0,#0c65c8);border-color:#147ff0;color:#fff}
.ph-ver small{display:block;font-weight:400;font-size:10px;color:#8ea8c5;margin-top:2px}
.ph-ext{display:inline-block;padding:5px 11px;border-radius:6px;font-size:12px;margin:3px;background:rgba(22,217,135,.1);border:1px solid rgba(22,217,135,.25);color:#4ade80}
.ph-ext.missing{background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.25);color:#f87171;text-decoration:none}
.ph-ext.missing:hover{background:rgba(248,113,113,.18)}
</style>

<div class="stats-grid">
<div class="stat-card"><h3>PHP Versions</h3><div class="value"><?php echo count($versions); ?></div></div>
<div class="stat-card"><h3>Extensions Loaded</h3><div class="value"><?php echo count($loaded); ?></div></div>
<div class="stat-card"><h3>Available</h3><div class="value"><?php echo count($available); ?></div></div>
</div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Installed PHP Versions — select one to manage extensions</h3>
<div class="ph-vers">
<?php foreach ($versions as $v => $info): ?>
<a href="/admin/php?version=<?php echo $v; ?>" class="ph-ver <?php echo ($selected_version ?? '') === $v ? 'active' : ''; ?>">PHP <?php echo $v; ?><small><?php echo count($info['loaded']); ?> ext</small></a>
<?php endforeach; ?>
</div>

<?php $sv = $versions[$selected_version ?? ''] ?? null; ?>
<?php if ($sv): ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin:8px 0 4px;flex-wrap:wrap;gap:8px">
<strong style="color:#fff">PHP <?php echo htmlspecialchars($sv['php_version']); ?> — <?php echo count($sv['loaded']); ?> extensions</strong>
<span style="font-size:11px;color:#8ea8c5"><?php echo htmlspecialchars($sv['binary']); ?></span>
</div>

<div style="margin-bottom:14px">
<strong style="font-size:12px;color:#8ea8c5;text-transform:uppercase;letter-spacing:.4px">Installed Extensions</strong>
<div style="margin-top:6px">
<?php if (!empty($sv['loaded'])): foreach ($sv['loaded'] as $e): ?><span class="ph-ext"><?php echo htmlspecialchars($e); ?></span><?php endforeach; else: ?><span style="color:#8ea8c5;font-size:12px">None loaded</span><?php endif; ?>
</div>
</div>

<div>
<strong style="font-size:12px;color:#8ea8c5;text-transform:uppercase;letter-spacing:.4px">Installable Extensions for PHP <?php echo $v; ?></strong>
<div style="margin-top:6px">
<?php foreach ($sv['available'] as $e): ?>
<a href="/admin/php/install/<?php echo htmlspecialchars($e); ?>/<?php echo $v; ?>" class="ph-ext missing" onclick="return confirm('Install <?php echo htmlspecialchars($e); ?> for PHP <?php echo $v; ?>? This runs apt-get install php<?php echo $v; ?>-<?php echo htmlspecialchars($e); ?>.')">+ <?php echo htmlspecialchars($e); ?></a>
<?php endforeach; ?>
</div>
<p style="font-size:11px;color:#8ea8c5;margin-top:10px">Installs via <code>apt-get install php<?php echo $v; ?>-&lt;ext&gt;</code>. Requires the Sury PHP repo (already configured). Restart PHP-FPM after installing.</p>
</div>
<?php endif; ?>
</div>

<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">Installed Extensions (default PHP)</h3>
<div style="display:flex;flex-wrap:wrap;gap:6px"><?php foreach ($loaded as $e): ?><span class="ph-ext"><?php echo $e; ?></span><?php endforeach; ?></div>
</div>
<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">Available to Install (default PHP)</h3>
<div style="display:flex;flex-wrap:wrap;gap:6px"><?php foreach ($available as $e): ?><a href="/admin/php/install/<?php echo $e; ?>" class="ph-ext missing" onclick="return confirm('Install <?php echo $e; ?>?')">+ <?php echo $e; ?></a><?php endforeach; ?></div>
</div>
<?php endif; ?>