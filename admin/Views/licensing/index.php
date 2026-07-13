<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Status</h3>
<div class="value" style="font-size:16px;color:<?php echo $status['valid'] ? '#4ade80' : (($status['trial'] ?? false) && ($trial_days_left > 0) ? '#facc15' : (($status['in_grace'] ?? false) ? '#fb923c' : '#f87171')); ?>">
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
<div class="stat-card"><h3>Error</h3><div class="value" style="font-size:14px;color:#f87171"><?php echo htmlspecialchars($status['error'] ?? ''); ?></div></div>
<div class="stat-card"><h3>Contact</h3><div class="value" style="font-size:14px">sales@planet-hosts.com</div></div>
<?php endif; ?>
</div>

<div class="card" style="margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">License Actions</h3>
<div style="display:flex;gap:10px;flex-wrap:wrap">
<a href="/admin/licensing/refresh" class="btn primary" style="padding:8px 16px;font-size:12px">🔄 Re-verify</a>
<?php if (is_file(BASE_PATH . '/license.key')): ?>
<a href="/admin/licensing/deactivate" class="btn danger" style="padding:8px 16px;font-size:12px" onclick="return confirm('Deactivate license and start grace period?')">✕ Deactivate</a>
<?php endif; ?>
<a href="/admin/licensing/generate" class="btn secondary" style="padding:8px 16px;font-size:12px">🔑 Generate License</a>
</div>
</div>

<div class="grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
  <div class="card">
    <h3 style="color:var(--accent);margin-bottom:12px">Online Activation</h3>
    <form method="POST" action="/admin/licensing/activate">
      <div class="form-group">
        <label>License Key</label>
        <input type="text" name="license_key" placeholder="PH-XXXX-XXXX-XXXX-XXXX" style="width:100%;padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0">
      </div>
      <div class="form-group">
        <label>Customer Email</label>
        <input type="email" name="customer_email" placeholder="admin@company.com" style="width:100%;padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0">
      </div>
      <div class="form-group">
        <label>Company Name</label>
        <input type="text" name="company_name" placeholder="Planet Hosts" style="width:100%;padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0">
      </div>
      <input type="hidden" name="action" value="online_activate">
      <button type="submit" class="btn primary">Activate Online</button>
    </form>
  </div>

  <div class="card">
    <h3 style="color:var(--accent);margin-bottom:12px">Trial & Upload</h3>
    <form method="POST" action="/admin/licensing/activate">
      <input type="hidden" name="action" value="start_trial">
      <button type="submit" class="btn warning" style="width:100%;margin-bottom:12px" <?php echo ($trial_days_left > 0) ? '' : ''; ?>>🎁 Start 30-Day Trial</button>
    </form>
    <form method="POST" action="/admin/licensing/upload" enctype="multipart/form-data">
      <div class="form-group">
        <label>Upload license.key file</label>
        <input name="license_file" type="file" accept=".key,.txt" style="width:100%">
      </div>
      <p style="text-align:center;color:#475569;margin:8px 0">— or paste key —</p>
      <div class="form-group">
        <textarea name="license_content" rows="3" placeholder="Paste license key content here..." style="width:100%;padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-family:monospace;font-size:11px"></textarea>
      </div>
      <button type="submit" class="btn primary">Upload License</button>
    </form>
  </div>
</div>

<div class="card" style="margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px">Current License Key</h3>
<?php $keyContent = @file_get_contents(BASE_PATH . '/license.key'); ?>
<?php if ($keyContent): ?>
<textarea readonly style="width:100%;height:100px;font-family:monospace;font-size:11px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);color:#8b949e;border-radius:6px;padding:10px"><?php echo htmlspecialchars($keyContent); ?></textarea>
<?php else: ?>
<p style="color:var(--text-secondary)">No license key file found.</p>
<?php endif; ?>
</div>

<div class="card" style="margin-bottom:20px"><h3 style="color:var(--accent);margin-bottom:12px">Feature Access</h3>
<table><tr><th>Feature</th><th>Status</th></tr>
<?php
$featureLabels = [
    'accounts'=>'Hosting Accounts', 'packages'=>'Packages', 'dns'=>'DNS Zones', 'email'=>'Email', 'ftp'=>'FTP',
    'databases'=>'Databases', 'backups'=>'Backups', 'ssl'=>'SSL', 'domains'=>'Domains', 'radio'=>'Radio Streaming',
    'streams'=>'Stream Management', 'autodj'=>'AutoDJ', 'shared_hosting'=>'Shared Hosting', 'radio_hosting'=>'Radio Hosting',
    'streaming_icecast'=>'Icecast Streaming', 'streaming_shoutcast_v1'=>'SHOUTcast v1', 'streaming_shoutcast_v2'=>'SHOUTcast v2',
    'streaming_autodj'=>'AutoDJ Streaming', 'email_hosting'=>'Email Hosting', 'ftp_hosting'=>'FTP Hosting',
    'database_hosting'=>'Database Hosting', 'ssl_auto'=>'Auto SSL', 'ssl_wildcard'=>'Wildcard SSL',
    'monitoring'=>'Monitoring', 'marketplace'=>'Marketplace', 'api_access'=>'API Access', 'desktop_app'=>'Desktop App',
    'reseller_hosting'=>'Reseller Hosting', 'vps_hosting'=>'VPS Hosting', 'game_hosting'=>'Game Hosting',
    'dns_clustering'=>'DNS Clustering', 'multi_server'=>'Multi-Server', 'white_label'=>'White Label',
    'streaming_rtmp'=>'RTMP Video', 'streaming_rtsp'=>'RTSP Cameras', 'streaming_relay'=>'Audio Relay',
    'backups'=>'Automated Backups',
];
?>
<?php foreach ($features as $k => $v): ?>
<tr><td><?php echo htmlspecialchars($featureLabels[$k] ?? ucfirst($k)); ?></td>
<td><span class="status-badge status-<?php echo $v ? 'active' : 'terminated'; ?>"><?php echo $v ? '✓ Enabled' : '— Locked'; ?></span></td></tr>
<?php endforeach; ?></table>
</div>

<?php if (!empty($activations)): ?>
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Activation History</h3>
<table><tr><th>Date</th><th>License Key</th><th>Type</th><th>Status</th></tr>
<?php foreach ($activations as $a): ?>
<tr>
  <td><?php echo htmlspecialchars($a->activation_date ?? $a->created_at ?? ''); ?></td>
  <td style="font-family:monospace;font-size:11px"><?php echo htmlspecialchars(substr($a->license_key, 0, 20) . '...'); ?></td>
  <td><?php echo htmlspecialchars($a->license_type ?? ''); ?></td>
  <td><span class="status-badge status-<?php echo $a->license_status === 'active' ? 'active' : 'terminated'; ?>"><?php echo htmlspecialchars($a->license_status ?? ''); ?></span></td>
</tr>
<?php endforeach; ?></table>
</div>
<?php endif; ?>
</div>
