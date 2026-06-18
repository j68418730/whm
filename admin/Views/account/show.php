<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="stats-grid">
<div class="stat-card"><h3>Username</h3><div class="value" style="font-size:22px"><?php echo htmlspecialchars($account->username, ENT_QUOTES, 'UTF-8'); ?></div></div>
<div class="stat-card"><h3>Status</h3><div class="value" style="font-size:22px"><span class="status-badge status-<?php echo $account->status; ?>"><?php echo ucfirst($account->status); ?></span></div></div>
<div class="stat-card"><h3>Package</h3><div class="value" style="font-size:22px"><?php echo $package ? htmlspecialchars($package->name, ENT_QUOTES, 'UTF-8') : 'None'; ?></div></div>
<div class="stat-card"><h3>Domain</h3><div class="value" style="font-size:22px"><?php echo htmlspecialchars($account->domain ?? '-', ENT_QUOTES, 'UTF-8'); ?></div></div>
</div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:16px">Account Details</h3>
<div style="display:grid;grid-template-columns:180px 1fr;gap:8px">
<div style="color:var(--text-secondary);font-size:14px">Email</div><div><?php echo htmlspecialchars($account->email, ENT_QUOTES, 'UTF-8'); ?></div>
<div style="color:var(--text-secondary);font-size:14px">Name</div><div><?php echo htmlspecialchars(($account->first_name ?? '') . ' ' . ($account->last_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
<div style="color:var(--text-secondary);font-size:14px">PHP Version</div><div><?php echo $account->php_version ?: 'Server default'; ?></div>
<div style="color:var(--text-secondary);font-size:14px">Home Directory</div><div><code>/home/<?php echo htmlspecialchars($account->username, ENT_QUOTES, 'UTF-8'); ?>/</code></div>
<div style="color:var(--text-secondary);font-size:14px">Created</div><div><?php echo $account->created_at ?? 'N/A'; ?></div>
</div>
</div>

<?php if ($package): ?>
<div class="card">
<h3 style="color:var(--accent);margin-bottom:16px">Package Limits: <?php echo htmlspecialchars($package->name, ENT_QUOTES, 'UTF-8'); ?></h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:10px">
<?php $limits = [
    'Disk Quota' => $package->disk_space ? $package->disk_space . ' GB' : 'Unlimited',
    'Monthly Bandwidth' => $package->bandwidth ? $package->bandwidth . ' GB' : 'Unlimited',
    'Max FTP Accounts' => $package->ftp_accounts ?: 'Unlimited',
    'Max Email Accounts' => $package->email_accounts ?: 'Unlimited',
    'Max Databases' => $package->databases ?: 'Unlimited',
    'Max Subdomains' => $package->subdomains ?: 'Unlimited',
    'Max Parked Domains' => $package->parked_domains ?: 'Unlimited',
    'Max Addon Domains' => $package->addon_domains ?: 'Unlimited',
    'Listener Limit' => $package->listener_limit ?: 'N/A',
    'Bitrate' => $package->bitrate ? $package->bitrate . ' kbps' : 'N/A',
    'Storage Limit' => $package->storage_limit ? $package->storage_limit . ' GB' : 'N/A',
    'DJ Accounts' => $package->dj_accounts ?: 'N/A',
];
foreach ($limits as $label => $val): ?>
<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:8px;padding:14px 18px">
<div style="color:var(--text-secondary);font-size:12px;text-transform:uppercase;letter-spacing:.5px"><?php echo $label; ?></div>
<div style="font-size:20px;font-weight:700;margin-top:4px"><?php echo $val; ?></div>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<h3 style="color:var(--accent);margin-bottom:12px">Password Reset</h3>
<form method="POST" action="/admin/account/password/<?php echo $account->id; ?>" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:20px">
<input type="password" name="password" required minlength="8" placeholder="New password" style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none;flex:1;min-width:200px">
<button type="submit" class="btn primary">Change Password</button>
</form>

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
<a href="/admin/account/edit/<?php echo $account->id; ?>" class="btn secondary">Edit</a>
<a href="/admin/account/login-as/<?php echo $account->id; ?>" class="btn primary">🔑 Login as User</a>
<a href="/admin/account/suspend/<?php echo $account->id; ?>" class="btn secondary" onclick="return confirm('Suspend this account?')">Suspend</a>
<a href="/admin/account/unsuspend/<?php echo $account->id; ?>" class="btn secondary">Unsuspend</a>
<a href="/admin/account/terminate/<?php echo $account->id; ?>" class="btn" style="background:rgba(255,50,50,.15);color:#ff6b6b;border:1px solid rgba(255,50,50,.2)" onclick="return confirm('Terminate? This deletes the Linux user.')">Terminate</a>
<a href="/admin/package/upgrade/<?php echo $account->id; ?>" class="btn secondary">Change Package</a>
</div>

<!-- SSH Access Management -->
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px"><i class="fas fa-terminal"></i> SSH Access</h3>
<div style="display:grid;grid-template-columns:180px 1fr;gap:8px;margin-bottom:12px">
<div style="color:var(--text-secondary);font-size:14px">Current Access</div>
<div>
<?php
$sshStatus = \Core\SshJail::getStatus($account->username);
$accessLabels = ['full' => 'Full SSH', 'jailed' => 'Jailed SSH', 'sftp' => 'SFTP Only', 'none' => 'No Access'];
$currentAccess = $account->ssh_access ?? 'jailed';
$accessColor = ['full' => '#4ade80', 'jailed' => '#facc15', 'sftp' => '#38bdf8', 'none' => '#f87171'];
?>
<span style="color:<?php echo $accessColor[$currentAccess] ?? '#94a3b8'; ?>;font-weight:600">● <?php echo $accessLabels[$currentAccess] ?? 'Unknown'; ?></span>
<?php if ($sshStatus['has_key']): ?><span style="margin-left:10px;font-size:12px;color:#4ade80">🔑 SSH Key Installed</span><?php endif; ?>
</div>
</div>

<form method="POST" action="/admin/account/ssh/access/<?php echo $account->id; ?>" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
<select name="ssh_access" style="padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;outline:none;font-size:13px">
<option value="full" <?php echo $currentAccess === 'full' ? 'selected' : ''; ?>>Full SSH</option>
<option value="jailed" <?php echo $currentAccess === 'jailed' ? 'selected' : ''; ?>>Jailed SSH</option>
<option value="sftp" <?php echo $currentAccess === 'sftp' ? 'selected' : ''; ?>>SFTP Only</option>
<option value="none" <?php echo $currentAccess === 'none' ? 'selected' : ''; ?>>No SSH Access</option>
</select>
<button type="submit" class="btn btn-sm primary">Apply</button>
</form>

<div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap">
<form method="POST" action="/admin/account/ssh/key-generate/<?php echo $account->id; ?>" style="display:inline">
<button type="submit" class="btn btn-sm secondary">🔑 Generate SSH Key</button>
</form>
<form method="POST" action="/admin/account/ssh/key-delete/<?php echo $account->id; ?>" style="display:inline" onsubmit="return confirm('Delete SSH key?')">
<button type="submit" class="btn btn-sm secondary" style="border-color:rgba(248,113,113,.2);color:#f87171">🗑 Delete SSH Key</button>
</form>
<?php if ($sshStatus['has_key']): ?>
<button class="btn btn-sm secondary" onclick="navigator.clipboard.writeText('<?php echo str_replace("\n", "\\n", addslashes(file_get_contents('/home/' . $account->username . '/.ssh/id_rsa.pub'))); ?>')">📋 Copy Public Key</button>
<?php endif; ?>
</div>

<?php if ($currentAccess !== 'none'): ?>
<div style="margin-top:12px;padding:10px;background:rgba(0,0,0,.2);border-radius:8px;font-size:12px;color:#64748b">
<strong>Connection Info:</strong><br>
Host: <?php echo $_SERVER['SERVER_NAME'] ?? '45.61.59.55'; ?> — Port: 22 — User: <?php echo htmlspecialchars($account->username); ?><br>
<?php if ($currentAccess === 'jailed'): ?>⚠ Jailed to home directory. Only limited commands available.<?php endif; ?>
</div>
<?php endif; ?>
</div>
