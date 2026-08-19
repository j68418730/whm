<style>
.mg-wrap{max-width:900px}
.mg-header{display:flex;align-items:center;gap:16px;padding:20px;background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:14px;margin-bottom:20px}
.mg-header .mg-icon{font-size:40px}
.mg-header h2{margin:0;font-size:20px;font-weight:700}
.mg-header .mg-sub{color:#64748b;font-size:12px;margin-top:4px}
.mg-status{font-size:10px;padding:2px 10px;border-radius:8px;font-weight:600;text-transform:uppercase}
.mg-status.active{background:rgba(74,222,128,.15);color:#4ade80}
.mg-status.suspended{background:rgba(250,204,21,.12);color:#facc15}
.mg-status.terminated{background:rgba(239,68,68,.12);color:#ef4444}

.mg-cards{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.mg-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:14px;padding:20px}
.mg-card h3{font-size:14px;font-weight:700;margin:0 0 14px;display:flex;align-items:center;gap:8px}

.inc-list{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.inc-item{display:flex;align-items:center;gap:10px;padding:10px 12px;background:rgba(255,255,255,.03);border-radius:10px;font-size:12px}
.inc-item .ii{font-size:18px}
.inc-item .il{color:#64748b;font-size:10px;text-transform:uppercase}
.inc-item .iv{font-weight:700;font-size:13px;margin-top:2px}

.feat-badge{display:inline-block;padding:5px 12px;border-radius:8px;background:rgba(0,140,255,.1);border:1px solid rgba(0,140,255,.2);color:#0A84FF;font-size:11px;margin:0 6px 6px 0}

.mg-price{font-size:24px;font-weight:800;color:#0A84FF}
.mg-price small{font-size:11px;color:#64748b;font-weight:400}
.mg-tools{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:8px;margin-top:14px}
.mg-tools a{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 10px;background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.06);border-radius:10px;text-decoration:none;color:#e0e0e0;font-size:11px;transition:.15s}
.mg-tools a:hover{border-color:rgba(0,140,255,.3);background:rgba(0,140,255,.04)}
.mg-tools a span{font-size:22px}
</style>

<div class="mg-wrap">

<div class="mg-header">
<div class="mg-icon"><?php echo $service['icon'] ?? '🌐'; ?></div>
<div>
<h2><?php echo htmlspecialchars($service['name'] ?? 'Service'); ?></h2>
<div class="mg-sub"><?php echo htmlspecialchars($service['detail'] ?? ''); ?></div>
</div>
<?php $st = ($service['status'] ?? 'active') === 'active' || ($service['status'] ?? '') === 'running' ? 'active' : 'suspended'; ?>
<span class="mg-status <?php echo $st; ?>"><?php echo ucfirst($service['status'] ?? 'active'); ?></span>
</div>

<div class="mg-cards">

<div class="mg-card">
<h3>✅ What's Included in Your Plan</h3>
<?php if (empty($included)): ?><p style="color:#64748b;font-size:12px">No package details found.</p><?php else: ?>
<div class="inc-list">
<?php foreach ($included as $inc): ?>
<div class="inc-item"><div class="ii"><?php echo $inc[0]; ?></div><div><div class="il"><?php echo $inc[1]; ?></div><div class="iv"><?php echo htmlspecialchars($inc[2]); ?></div></div></div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<div class="mg-card">
<h3>🎁 Features</h3>
<?php if (empty($features)): ?><p style="color:#64748b;font-size:12px">No bonus features on this plan.</p><?php else: foreach ($features as $f): ?>
<span class="feat-badge">✓ <?php echo htmlspecialchars($f); ?></span>
<?php endforeach; endif; ?>
<?php if ($price > 0): ?>
<p style="margin-top:16px;color:#64748b;font-size:11px;text-transform:uppercase">Plan Price</p>
<div class="mg-price">$<?php echo number_format($price, 2); ?><small>/month</small></div>
<?php endif; ?>
</div>

</div>

<h3 style="font-size:14px;font-weight:700;margin:22px 0 10px">🔧 Manage This Service</h3>
<div class="mg-tools">
<a href="/user/files"><span>📁</span>File Manager</a>
<a href="/user/ftp"><span>⛓️</span>FTP Accounts</a>
<a href="/pma_autologin.php" target="_blank"><span>🐘</span>phpMyAdmin</a>
<a href="/webmail_autologin.php" target="_blank"><span>📨</span>Webmail</a>
<a href="/user/domains"><span>🌍</span>Domains</a>
<a href="/user/security"><span>🛡️</span>Security</a>
<a href="/user/php-switcher"><span>🐘</span>PHP Switcher</a>
<a href="/user/backup"><span>🗄️</span>Backups</a>
<a href="/user/installer"><span>🚀</span>Installer</a>
<a href="/user/terminal"><span>⌨️</span>Terminal</a>
<a href="/user/invoices"><span>💳</span>Invoices</a>
<a href="/user/tickets"><span>🎫</span>Support</a>
</div>

<div style="margin-top:20px">
<a href="/user/services" style="color:#0A84FF;text-decoration:none;font-size:13px">← Back to My Services</a>
</div>

</div>