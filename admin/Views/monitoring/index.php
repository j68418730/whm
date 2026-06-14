<div class="stats-grid">
<div class="stat-card"><h3>Services</h3><div class="value"><?php echo count($services); ?></div><div class="label">Monitored</div></div>
<div class="stat-card"><h3>Active</h3><div class="value" style="color:#4ade80"><?php echo count(array_filter($services, fn($v) => $v === 'active')); ?></div></div>
<div class="stat-card"><h3>Inactive</h3><div class="value" style="color:#f87171"><?php echo count(array_filter($services, fn($v) => $v !== 'active')); ?></div></div>
</div>
<div class="card">
<h3 style="color:var(--accent);margin-bottom:16px">Service Status</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:10px">
<?php foreach ($services as $label => $st): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:rgba(255,255,255,.02);border-radius:8px;border:1px solid rgba(255,255,255,.04)">
<span style="font-weight:600;font-size:14px"><?php echo htmlspecialchars($label); ?></span>
<span style="font-size:12px;padding:3px 10px;border-radius:5px;font-weight:600;<?php echo $st === 'active' ? 'background:#1a3a2a;color:#4ade80' : ($st === 'inactive' ? 'background:#3a3a1a;color:#facc15' : 'background:#3a1a1a;color:#f87171'); ?>"><?php echo $st; ?></span>
</div>
<?php endforeach; ?>
</div>
</div>
