<style>
.action-card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:10px;padding:16px;margin-bottom:12px}
.action-card h4{font-size:13px;font-weight:600;margin:0 0 8px;display:flex;align-items:center;gap:6px}
.action-card .actions{display:flex;gap:6px;flex-wrap:wrap}
.account-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}@media(max-width:768px){.account-grid{grid-template-columns:1fr}}
code{background:rgba(255,255,255,.06);padding:2px 6px;border-radius:4px;font-size:12px}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px">
<div>
<h2 style="margin:0"><?php echo htmlspecialchars($account->username); ?></h2>
<p style="color:#64748b;margin:4px 0 0">Client details — scoped to your reseller.</p>
</div>
<a href="/reseller-clients" class="btn btn-secondary btn-sm">&larr; Client List</a>
</div>

<div class="stats-grid">
<div class="stat-card"><h3>Username</h3><div class="value" style="font-size:20px"><?php echo htmlspecialchars($account->username); ?></div></div>
<div class="stat-card"><h3>Status</h3><div class="value" style="font-size:20px"><span class="badge bg-<?php echo $account->status === 'active' ? 'success' : ($account->status === 'suspended' ? 'warning' : ($account->status === 'pending' ? 'info' : 'danger')); ?>" style="font-size:14px"><?php echo ucfirst($account->status); ?></span></div></div>
<div class="stat-card"><h3>Package</h3><div class="value" style="font-size:20px"><?php echo $package ? htmlspecialchars($package->name) : ($retailPkg ? htmlspecialchars($retailPkg->name) . ' (retail)' : 'None'); ?></div></div>
<div class="stat-card"><h3>Domain</h3><div class="value" style="font-size:20px"><?php echo htmlspecialchars($account->domain ?? '-'); ?></div></div>
</div>

<div class="card">
<h3 style="margin-bottom:12px;color:var(--accent,#008cff)">Account Details</h3>
<div style="display:grid;grid-template-columns:160px 1fr;gap:6px;font-size:13px">
<span style="color:var(--text_muted,#94a3b8)">Email</span><span><?php echo htmlspecialchars($account->email); ?></span>
<span style="color:var(--text_muted,#94a3b8)">Name</span><span><?php echo htmlspecialchars(($account->first_name??'') . ' ' . ($account->last_name??'')); ?></span>
<span style="color:var(--text_muted,#94a3b8)">PHP Version</span><span><?php echo $account->php_version ?: 'Server default'; ?></span>
<span style="color:var(--text_muted,#94a3b8)">Home Dir</span><span><code>/home/<?php echo htmlspecialchars($account->username); ?>/</code></span>
<span style="color:var(--text_muted,#94a3b8)">IP</span><span><?php echo htmlspecialchars($account->ip ?? '-'); ?></span>
<span style="color:var(--text_muted,#94a3b8)">Nameservers</span><span><?php echo htmlspecialchars($account->nameserver1 ?? '-'); ?> / <?php echo htmlspecialchars($account->nameserver2 ?? '-'); ?></span>
<span style="color:var(--text_muted,#94a3b8)">Created</span><span><?php echo $account->created_at ?? 'N/A'; ?></span>
</div>
</div>

<?php if (!empty($domains)): ?>
<div class="card">
<h3 style="margin-bottom:12px;color:var(--accent,#008cff)">Domains</h3>
<table class="table"><tr><th>Domain</th><th>Type</th><th>Status</th></tr>
<?php foreach ($domains as $d): ?>
<tr><td><?php echo htmlspecialchars($d->domain); ?></td><td><?php echo htmlspecialchars($d->type ?? '-'); ?></td><td><?php echo htmlspecialchars($d->status ?? '-'); ?></td></tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<div class="account-grid">

<div class="action-card">
<h4><i class="bi bi-pause-circle" style="color:#facc15"></i> Suspend / Reactivate</h4>
<div class="actions">
<a href="/reseller/clients/suspend/<?php echo (int)$account->id; ?>" class="btn btn-sm" style="background:rgba(250,204,21,.12);color:#facc15;border:1px solid rgba(250,204,21,.2)" onclick="return confirm('Suspend this client?')"><i class="bi bi-pause-circle"></i> Suspend</a>
<a href="/reseller/clients/unsuspend/<?php echo (int)$account->id; ?>" class="btn btn-sm" style="background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.15)"><i class="bi bi-play-circle"></i> Reactivate</a>
</div>
</div>

<div class="action-card">
<h4><i class="bi bi-hdd-stack" style="color:#facc15"></i> Disk Usage</h4>
<p style="font-size:13px;margin:4px 0"><?php echo $disk_usage; ?></p>
</div>

<div class="action-card">
<h4><i class="bi bi-archive" style="color:#34d399"></i> Backups</h4>
<?php if (!empty($backup_files)): ?>
<div style="max-height:120px;overflow-y:auto">
<?php foreach (array_slice($backup_files, 0, 5) as $bf): $sz = filesize($bf); ?>
<div style="display:flex;justify-content:space-between;padding:3px 0;font-size:11px;border-bottom:1px solid rgba(255,255,255,.04)">
<span><?php echo htmlspecialchars(basename($bf)); ?></span>
<span><?php echo $sz > 1048576 ? round($sz/1048576,1).' MB' : round($sz/1024,1).' KB'; ?></span>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p style="font-size:11px;color:var(--text_muted,#94a3b8);margin-top:6px">No backup files found.</p>
<?php endif; ?>
</div>

<div class="action-card">
<h4><i class="bi bi-key" style="color:#a78bfa"></i> Password Reset</h4>
<form method="POST" action="/reseller/clients/password/<?php echo (int)$account->id; ?>" class="actions">
<input type="password" name="password" required minlength="8" placeholder="New password" style="flex:1;padding:6px 10px;font-size:12px">
<button class="btn btn-sm btn-primary">Change Password</button>
</form>
</div>

<div class="action-card">
<h4><i class="bi bi-server" style="color:#0A84FF"></i> Apache Virtual Host</h4>
<?php if ($vhost_content): ?>
<pre style="background:rgba(0,0,0,.5);border:1px solid rgba(0,191,255,.1);border-radius:8px;padding:12px;font-size:12px;overflow-x:auto;color:#e0e0e0;margin:0 0 8px"><?php echo htmlspecialchars($vhost_content); ?></pre>
<?php else: ?>
<p style="font-size:12px;color:var(--text_muted,#94a3b8)">No HTTP vhost found.</p>
<?php endif; ?>
<?php if ($vhost_ssl_content): ?>
<pre style="background:rgba(0,0,0,.5);border:1px solid rgba(0,191,255,.1);border-radius:8px;padding:12px;font-size:12px;overflow-x:auto;color:#e0e0e0;margin:0"><?php echo htmlspecialchars($vhost_ssl_content); ?></pre>
<?php endif; ?>
</div>

<div class="action-card" style="grid-column:1/-1">
<h4><i class="bi bi-clock-history" style="color:#94a3b8"></i> Client History</h4>
<?php if (!empty($history)): ?>
<table style="font-size:12px"><tr><th>Action</th><th>Details</th><th>Date</th></tr>
<?php foreach ($history as $h): ?>
<tr><td><?php echo htmlspecialchars($h->action ?? '-'); ?></td><td><?php echo htmlspecialchars($h->details ?? '-'); ?></td><td style="white-space:nowrap"><?php echo htmlspecialchars($h->created_at ?? '-'); ?></td></tr>
<?php endforeach; ?></table>
<?php else: ?>
<p style="font-size:12px;color:var(--text_muted,#94a3b8)">No activity recorded yet.</p>
<?php endif; ?>
</div>

</div>
<a href="/reseller-clients" class="btn secondary" style="margin-top:12px">&larr; Back to Clients</a>