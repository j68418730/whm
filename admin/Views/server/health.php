<?php $pass = count(array_filter($checks, fn($c) => $c['test']['status'] === 'pass'));
$warn = count(array_filter($checks, fn($c) => $c['test']['status'] === 'warn'));
$fail = count(array_filter($checks, fn($c) => $c['test']['status'] === 'fail'));
?>
<div class="stats-grid">
<div class="stat-card"><h3>Passed</h3><div class="value" style="color:#4ade80"><?php echo $pass; ?></div></div>
<div class="stat-card"><h3>Warnings</h3><div class="value" style="color:#facc15"><?php echo $warn; ?></div></div>
<div class="stat-card"><h3>Failed</h3><div class="value" style="color:#f87171"><?php echo $fail; ?></div></div>
</div>
<div class="card">
<h3 style="color:var(--accent);margin-bottom:16px">Health Checks</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:10px">
<?php foreach ($checks as $c): $t = $c['test']; ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:14px 18px;background:rgba(255,255,255,.02);border-radius:8px;border:1px solid <?php echo $t['status'] === 'pass' ? 'rgba(74,222,128,.2)' : ($t['status'] === 'warn' ? 'rgba(250,204,21,.2)' : 'rgba(248,113,113,.2)'); ?>">
<div><strong style="font-size:14px"><?php echo htmlspecialchars($c['name']); ?></strong>
<div style="color:var(--text-muted);font-size:12px;margin-top:2px"><?php echo htmlspecialchars($t['msg']); ?></div></div>
<div style="display:flex;align-items:center;gap:6px">
<span style="width:10px;height:10px;border-radius:50%;background:<?php echo $t['status'] === 'pass' ? '#4ade80' : ($t['status'] === 'warn' ? '#facc15' : '#f87171'); ?>"></span>
<span style="font-size:11px;text-transform:uppercase;font-weight:600;color:<?php echo $t['status'] === 'pass' ? '#4ade80' : ($t['status'] === 'warn' ? '#facc15' : '#f87171'); ?>"><?php echo $t['status']; ?></span>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
