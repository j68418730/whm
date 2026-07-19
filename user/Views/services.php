<style>
.svc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;margin-bottom:16px}
.svc-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:18px;text-decoration:none;color:#e0e0e0;transition:.2s;display:block}
.svc-card:hover{transform:translateY(-2px);border-color:rgba(0,140,255,.3);box-shadow:0 4px 20px rgba(0,140,255,.08)}
.svc-card .top{display:flex;justify-content:space-between;align-items:start;margin-bottom:8px}
.svc-card .icon{font-size:28px}
.svc-card .name{font-size:14px;font-weight:600}
.svc-card .status{font-size:10px;padding:2px 8px;border-radius:8px;font-weight:600}
.svc-card .status.active{background:rgba(74,222,128,.15);color:#4ade80}
.svc-card .status.suspended{background:rgba(250,204,21,.12);color:#facc15}
.svc-card .status.terminated{background:rgba(239,68,68,.12);color:#ef4444}
.svc-card .status.pending{background:rgba(0,140,255,.12);color:#0A84FF}
.svc-card .detail{font-size:11px;color:#64748b;margin-bottom:4px}
.svc-card .actions{margin-top:8px}
.svc-card .actions a{padding:4px 10px;border-radius:4px;font-size:10px;text-decoration:none;background:rgba(0,140,255,.12);color:#0A84FF}
</style>

<h2>My Services</h2>
<p style="color:#64748b;margin-bottom:16px;font-size:12px">All your hosting services in one place.</p>

<?php if (empty($services)): ?>
<div class="svc-card" style="text-align:center;padding:30px;color:#64748b;font-size:13px">No services found.</div>
<?php else: ?>
<div class="svc-grid">
<?php foreach ($services as $svc): $st = $svc['status'] === 'active' || $svc['status'] === 'running' ? 'active' : ($svc['status'] === 'suspended' || $svc['status'] === 'stopped' ? 'suspended' : 'terminated'); ?>
<div class="svc-card" onclick="window.location='<?php echo $svc['link']; ?>'" style="cursor:pointer">
<div class="top"><div class="icon"><?php echo $svc['icon']; ?></div><span class="status <?php echo $st; ?>"><?php echo ucfirst($svc['status']); ?></span></div>
<div class="name"><?php echo htmlspecialchars($svc['name']); ?></div>
<div class="detail"><?php echo htmlspecialchars($svc['detail']); ?></div>
<div class="actions"><a href="<?php echo $svc['link']; ?>">Manage</a></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
