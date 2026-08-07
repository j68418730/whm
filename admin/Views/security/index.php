<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<style>
.sc-tools{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px}
.sc-tool{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:16px}
.sc-tool h4{margin:0 0 4px;font-size:14px;color:#e0e0e0}
.sc-tool .sub{font-size:11px;color:#64748b;margin-bottom:10px}
.sc-tool .status-row{display:flex;align-items:center;gap:8px;margin-bottom:10px}
.sc-dot{width:9px;height:9px;border-radius:50%;display:inline-block}
.sc-dot.ok{background:#4ade80;box-shadow:0 0 6px rgba(74,222,128,.4)}
.sc-dot.warn{background:#ffc107}
.sc-dot.missing{background:#f87171}
.sc-actions{display:flex;gap:6px;flex-wrap:wrap}
.sc-actions a,.sc-actions button{padding:5px 12px;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:.1s}
.sc-actions .act{background:rgba(0,140,255,.2);color:#0A84FF}
.sc-actions .act:hover{background:rgba(0,140,255,.3)}
.sc-actions .scan{background:rgba(168,85,247,.18);color:#a855f7}
.sc-actions .scan:hover{background:rgba(168,85,247,.3)}
.sc-actions .log{background:rgba(255,255,255,.06);color:#94a3b8}
.sc-actions .log:hover{background:rgba(255,255,255,.12)}
.sc-score{text-align:center;padding:20px;background:rgba(8,16,28,.85);border:1px solid rgba(74,222,128,.15);border-radius:14px}
.sc-score .val{font-size:44px;font-weight:800;color:#4ade80}
.sc-score .lbl{font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:1px}
.sc-group{font-size:13px;font-weight:700;color:#94a3b8;margin:18px 0 8px;text-transform:uppercase;letter-spacing:.5px}
</style>

<h2>Security Center</h2>
<p style="color:#64748b;margin-bottom:16px">The firewall (firewalld/fail2ban/ModSecurity/CSF) is managed under <a href="/admin/firewall" style="color:#0A84FF">Firewall</a>.</p>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-bottom:20px">
  <div class="sc-score"><div class="val"><?php echo (int)$score; ?></div><div class="lbl">Security Score</div></div>
  <div class="sc-score"><div class="val" style="color:#0A84FF"><?php echo count($history); ?></div><div class="lbl">Scan Events</div></div>
  <div style="text-align:center;padding:20px;background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.1);border-radius:14px;display:flex;flex-direction:column;justify-content:center">
    <div style="font-size:12px;color:#64748b;margin-bottom:8px">Bulk Actions</div>
    <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap">
      <a href="/admin/security/install/all" class="btn primary" style="padding:7px 14px;font-size:11px">Install All Modules</a>
    </div>
  </div>
</div>

<?php foreach ($groups as $group => $tools): ?>
<div class="sc-group"><?php echo htmlspecialchars(ucfirst($group)); ?></div>
<div class="sc-tools">
<?php foreach ($tools as $t): $state = $t['state']; $inst = $t['installed']; ?>
<div class="sc-tool">
  <h4><?php echo htmlspecialchars($t['label']); ?></h4>
  <div class="sub"><?php echo htmlspecialchars($t['version'] ?: 'Not detected'); ?></div>
  <div class="status-row">
    <span class="sc-dot <?php echo $inst ? 'ok' : 'missing'; ?>"></span>
    <span style="font-size:12px;color:<?php echo $inst ? '#4ade80' : '#f87171'; ?>"><?php echo $inst ? 'Installed' : 'Not installed'; ?></span>
    <?php if ($t['updated']): ?><span style="font-size:10px;color:#64748b;margin-left:auto"><?php echo htmlspecialchars($t['updated']); ?></span><?php endif; ?>
  </div>
  <div class="sc-actions">
    <?php if (!$inst): ?>
    <a href="/admin/security/install/<?php echo $t['key']; ?>" class="act">Install</a>
    <?php else: ?>
    <a href="/admin/security/install/<?php echo $t['key']; ?>" class="act">Update</a>
    <?php endif; ?>
    <a href="/admin/security/scan/<?php echo $t['key']; ?>" class="scan">Scan Now</a>
    <a href="/admin/security/logs/<?php echo $t['key']; ?>" class="log" target="_blank">View Log</a>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>

<?php if (!empty($history)): ?>
<div class="card" style="margin-top:20px">
<h3>Recent Scan History</h3>
<table>
<tr><th>Tool</th><th>State</th><th>Time</th></tr>
<?php foreach ($history as $h): ?>
<tr>
  <td><?php echo htmlspecialchars($h['label']); ?></td>
  <td><span class="status-badge <?php echo $h['state']==='ok'?'status-running':($h['state']==='warn'?'status-starting':'status-stopped'); ?>"><?php echo htmlspecialchars($h['state']); ?></span></td>
  <td style="font-size:11px;color:#64748b"><?php echo htmlspecialchars($h['at']); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>
