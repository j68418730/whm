<style>
.action-card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:10px;padding:16px;margin-bottom:12px}
.action-card h4{font-size:13px;font-weight:600;margin:0 0 8px;display:flex;align-items:center;gap:6px}
.action-card .actions{display:flex;gap:6px;flex-wrap:wrap}
.account-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}@media(max-width:768px){.account-grid{grid-template-columns:1fr}}
</style>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="stats-grid">
<div class="stat-card"><h3>Username</h3><div class="value" style="font-size:20px"><?php echo htmlspecialchars($account->username); ?></div></div>
<div class="stat-card"><h3>Status</h3><div class="value" style="font-size:20px"><span class="badge bg-<?php echo $account->status === 'active' ? 'success' : ($account->status === 'suspended' ? 'warning' : 'danger'); ?>" style="font-size:14px"><?php echo ucfirst($account->status); ?></span></div></div>
<div class="stat-card"><h3>Package</h3><div class="value" style="font-size:20px"><?php echo $package ? htmlspecialchars($package->name) : 'None'; ?></div></div>
<div class="stat-card"><h3>Domain</h3><div class="value" style="font-size:20px"><?php echo htmlspecialchars($account->domain ?? '-'); ?></div></div>
</div>

<div class="card">
<h3 style="margin-bottom:12px">Account Details</h3>
<div style="display:grid;grid-template-columns:160px 1fr;gap:6px;font-size:13px">
<span style="color:var(--text_muted)">Email</span><span><?php echo htmlspecialchars($account->email); ?></span>
<span style="color:var(--text_muted)">Name</span><span><?php echo htmlspecialchars(($account->first_name??'') . ' ' . ($account->last_name??'')); ?></span>
<span style="color:var(--text_muted)">PHP Version</span><span><?php echo $account->php_version ?: 'Server default'; ?></span>
<span style="color:var(--text_muted)">Home Dir</span><span><code>/home/<?php echo htmlspecialchars($account->username); ?>/</code></span>
<span style="color:var(--text_muted)">Created</span><span><?php echo $account->created_at ?? 'N/A'; ?></span>
</div>
</div>

<div class="account-grid">
<div class="action-card">
<h4><i class="bi bi-pencil-square" style="color:var(--primary)"></i> Modify Account</h4>
<div class="actions">
<a href="/admin/account/edit/<?php echo $account->id; ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i> Edit Account</a>
</div>
</div>

<div class="action-card">
<h4><i class="bi bi-pause-circle" style="color:#facc15"></i> Suspend / Unsuspend</h4>
<div class="actions">
<a href="/admin/account/suspend/<?php echo $account->id; ?>" class="btn btn-sm" style="background:rgba(250,204,21,.12);color:#facc15;border:1px solid rgba(250,204,21,.2)" onclick="return confirm('Suspend this account?')"><i class="bi bi-pause-circle"></i> Suspend</a>
<a href="/admin/account/unsuspend/<?php echo $account->id; ?>" class="btn btn-sm" style="background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.15)"><i class="bi bi-play-circle"></i> Unsuspend</a>
</div>
</div>

<div class="action-card">
<h4><i class="bi bi-x-octagon" style="color:#f87171"></i> Terminate</h4>
<div class="actions">
<a href="/admin/account/terminate/<?php echo $account->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Terminate this account? This will delete the Linux user and all files.')"><i class="bi bi-x-octagon"></i> Terminate Account</a>
</div>
</div>

<div class="action-card">
<h4><i class="bi bi-arrow-left-right" style="color:#38bdf8"></i> Change Ownership</h4>
<form method="POST" action="/admin/account/change-owner/<?php echo $account->id; ?>" class="actions" style="flex-wrap:wrap">
<select name="reseller_id" style="width:auto;padding:6px 10px;font-size:12px;flex:1"><option value="">No reseller</option>
<?php foreach ($resellers as $r): ?><option value="<?php echo $r->id; ?>"><?php echo htmlspecialchars($r->name ?? $r->username ?? 'Reseller #' . $r->id); ?></option><?php endforeach; ?></select>
<input name="owner_email" placeholder="New owner email" style="width:auto;padding:6px 10px;font-size:12px;flex:1.5">
<button class="btn btn-sm btn-secondary" style="padding:6px 12px">Transfer</button>
</form>
</div>

<div class="action-card">
<h4><i class="bi bi-key" style="color:#a78bfa"></i> Password Reset</h4>
<form method="POST" action="/admin/account/password/<?php echo $account->id; ?>" class="actions">
<input type="password" name="password" required minlength="8" placeholder="New password" style="flex:1;padding:6px 10px;font-size:12px">
<button class="btn btn-sm btn-primary">Change Password</button>
</form>
</div>

<div class="action-card">
<h4><i class="bi bi-person-check" style="color:#4ade80"></i> Login As User</h4>
<div class="actions">
<a href="/admin/account/login-as/<?php echo $account->id; ?>" class="btn btn-sm btn-primary" onclick="return confirm('Login as <?php echo htmlspecialchars($account->username); ?>?')"><i class="bi bi-box-arrow-in-right"></i> Login as <?php echo htmlspecialchars($account->username); ?></a>
</div>
</div>

<div class="action-card">
<h4><i class="bi bi-box-seam" style="color:#fb923c"></i> Package Assignment</h4>
<form method="POST" action="/admin/package/upgrade/<?php echo $account->id; ?>" class="actions">
<select name="package_id" style="width:auto;padding:6px 10px;font-size:12px;flex:1">
<?php foreach ($packages as $p): ?><option value="<?php echo $p->id; ?>" <?php echo ($package && $package->id === $p->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name); ?></option><?php endforeach; ?></select>
<button class="btn btn-sm btn-secondary" style="padding:6px 12px">Assign</button>
</form>
</div>

<div class="action-card">
<h4><i class="bi bi-archive" style="color:#34d399"></i> Account Backups</h4>
<div class="actions">
<a href="/admin/backup" class="btn btn-sm btn-secondary"><i class="bi bi-camera"></i> Create Backup</a>
</div>
<?php if (!empty($backup_files)): ?>
<div style="margin-top:8px;max-height:120px;overflow-y:auto">
<?php foreach (array_slice($backup_files, 0, 5) as $bf): $bn = basename($bf); $sz = filesize($bf); ?>
<div style="display:flex;justify-content:space-between;padding:3px 0;font-size:11px;border-bottom:1px solid rgba(255,255,255,.04)">
<span><?php echo htmlspecialchars($bn); ?></span>
<span><?php echo $sz > 1048576 ? round($sz/1048576,1).' MB' : round($sz/1024,1).' KB'; ?></span>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p style="font-size:11px;color:var(--text_muted);margin-top:6px">No backup files found.</p>
<?php endif; ?>
</div>

<div class="action-card">
<h4><i class="bi bi-speedometer2" style="color:#38bdf8"></i> Bandwidth Usage</h4>
<p style="font-size:13px;margin:4px 0"><?php echo $bandwidth_usage; ?></p>
<?php if ($package && $package->bandwidth): ?>
<div class="progress" style="height:6px;margin-top:4px"><div class="progress-bar" style="width:0%;background:#38bdf8"></div></div>
<?php endif; ?>
</div>

<div class="action-card">
<h4><i class="bi bi-hdd-stack" style="color:#facc15"></i> Disk Usage</h4>
<p style="font-size:13px;margin:4px 0"><?php echo $disk_usage; ?></p>
<?php if ($package && $package->disk_space): ?>
<?php
$diskVal = (float)($disk_usage ? str_replace(' MB','',$disk_usage) : 0);
$diskPct = $package->disk_space > 0 ? min(100, round($diskVal / ($package->disk_space * 1024) * 100)) : 0;
?>
<div class="progress" style="height:6px;margin-top:4px"><div class="progress-bar" style="width:<?php echo $diskPct; ?>%;background:#facc15"></div></div>
<?php endif; ?>
</div>

<div class="action-card" style="grid-column:1/-1">
<h4><i class="bi bi-clock-history" style="color:#94a3b8"></i> Account History</h4>
<?php if (!empty($history)): ?>
<table style="font-size:12px"><tr><th>Action</th><th>Details</th><th>Date</th></tr>
<?php foreach ($history as $h): ?>
<tr><td><?php echo htmlspecialchars($h->action ?? '-'); ?></td><td><?php echo htmlspecialchars($h->details ?? '-'); ?></td><td style="white-space:nowrap"><?php echo htmlspecialchars($h->created_at ?? '-'); ?></td></tr>
<?php endforeach; ?></table>
<?php else: ?>
<p style="font-size:12px;color:var(--text_muted)">No history recorded yet.</p>
<?php endif; ?>
</div>
</div>

<style>
code{background:rgba(255,255,255,.06);padding:2px 6px;border-radius:4px;font-size:12px}
</style>
