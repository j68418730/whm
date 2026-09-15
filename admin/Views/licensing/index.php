<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="page-header">
<div class="d-flex justify-content-between align-items-center">
<h2 style="margin:0; color:var(--accent, #008cff)">🔐 License Management</h2>
<a href="/admin/dashboard" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i> Dashboard</a>
</div>
</div>

<div class="stats-grid">
<div class="stat-card"><h3>Status</h3>
<div class="value" style="font-size:16px;color:<?php echo $status['valid'] ? 'var(--success, #4ade80)' : (($status['trial'] ?? false) && $trial_days_left > 0 ? 'var(--warning, #facc15)' : (($status['in_grace'] ?? false) ? 'var(--danger, #f87171)' : 'var(--danger, #f87171)')); ?>">
<?php if ($status['valid']): ?>✓ ACTIVE
<?php elseif (($status['trial'] ?? false) && $trial_days_left > 0): ?>⚠ TRIAL (<?php echo $trial_days_left; ?> days left)
<?php elseif ($status['in_grace'] ?? false): ?>⚠ GRACE (<?php echo $grace_days_left; ?> days left)
<?php else: ?>✗ EXPIRED<?php endif; ?>
</div></div>
<div class="stat-card"><h3>Type</h3><div class="value" style="font-size:16px"><?php echo strtoupper($status['type'] ?? 'N/A'); ?></div></div>
<?php if ($status['valid']): ?>
<div class="stat-card"><h3>Licensee</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($status['data']['licensee'] ?? ($status['licensee'] ?? '')); ?></div></div>
<div class="stat-card"><h3>Expiry</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($status['data']['expiry'] ?? ($status['expiry'] ?? 'N/A')); ?></div></div>
<?php else: ?>
<div class="stat-card"><h3>Error</h3><div class="value" style="font-size:14px;color:var(--danger, #f87171)"><?php echo htmlspecialchars($status['error'] ?? ''); ?></div></div>
<div class="stat-card"><h3>Contact</h3><div class="value" style="font-size:14px">sales@planet-hosts.com</div></div>
<?php endif; ?>
</div>
</div>

<div class="card" style="margin-bottom:20px">
<h3 style="color:var(--accent, #008cff);margin-bottom:12px">License Actions</h3>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<a href="/admin/licensing/refresh" class="btn primary" style="padding:8px 16px;font-size:12px">🔄 Re-verify</a>
<?php if (is_file(BASE_PATH . '/license.key')): ?>
<a href="/admin/licensing/deactivate" class="btn danger" style="padding:8px 16px;font-size:12px" onclick="return confirm('Deactivate license and start grace period?')">✕ Deactivate</a>
<?php endif; ?>
<a href="/admin/licensing/generate" class="btn secondary" style="padding:8px 16px;font-size:12px">🔑 Generate License</a>
</div>
</div>

<div class="grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
  <div class="card">
    <h3 style="color:var(--accent, #008cff);margin-bottom:12px">Online Activation</h3>
    <form method="POST" action="/admin/licensing/activate">
      <div class="form-group"><label>Activation Code</label><input name="activation_code" type="text" required></div>
      <button type="submit" class="btn w-100" style="background:linear-gradient(135deg,var(--accent, #008cff),var(--accent-hover, #3bb8ff));border:none;border-radius:6px;padding:10px;font-weight:600">Activate</button>
    </form>
  </div>
  <div class="card">
    <h3 style="color:var(--accent, #008cff);margin-bottom:12px">License Key</h3>
    <p style="font-size:12px;color:var(--secondary, #64748b);margin-bottom:12px">Your license key is stored securely. Use the form above to activate.</p>
    <button class="btn btn-sm secondary" style="padding:6px 12px;font-size:11px" onclick="document.querySelector('.activation-form').scrollIntoView()">View Key</button>
  </div>
</div>
</div>
</div>