<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<div class="card">
<div style="display:grid;grid-template-columns:160px 1fr;gap:8px">
<div style="color:var(--text-secondary);font-weight:600;font-size:14px">Username</div><div><?php echo htmlspecialchars($account->username, ENT_QUOTES, 'UTF-8'); ?></div>
<div style="color:var(--text-secondary);font-weight:600;font-size:14px">Email</div><div><?php echo htmlspecialchars($account->email, ENT_QUOTES, 'UTF-8'); ?></div>
<div style="color:var(--text-secondary);font-weight:600;font-size:14px">Name</div><div><?php echo htmlspecialchars(($account->first_name ?? '') . ' ' . ($account->last_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
<div style="color:var(--text-secondary);font-weight:600;font-size:14px">Status</div><div><span class="status-badge status-<?php echo $account->status; ?>"><?php echo ucfirst($account->status); ?></span></div>
<div style="color:var(--text-secondary);font-weight:600;font-size:14px">Package</div><div><?php echo $package ? htmlspecialchars($package->name, ENT_QUOTES, 'UTF-8') : 'None'; ?></div>
<div style="color:var(--text-secondary);font-weight:600;font-size:14px">Home Directory</div><div><code>/home/<?php echo htmlspecialchars($account->username, ENT_QUOTES, 'UTF-8'); ?>/</code></div>
<div style="color:var(--text-secondary);font-weight:600;font-size:14px">Created</div><div><?php echo $account->created_at ?? 'N/A'; ?></div>
</div>
</div>

<h3 style="color:var(--accent);margin-bottom:12px">Password Reset</h3>
<form method="POST" action="/admin/account/password/<?php echo $account->id; ?>" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:20px">
<input type="password" name="password" required minlength="8" placeholder="New password" style="padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none;flex:1;min-width:200px">
<button type="submit" class="btn primary">Change Password</button>
</form>

<div style="display:flex;gap:12px;flex-wrap:wrap">
<a href="/admin/account" class="btn secondary">&larr; Back</a>
<a href="/admin/account/suspend/<?php echo $account->id; ?>" class="btn secondary" onclick="return confirm('Suspend?')">Suspend</a>
<a href="/admin/account/unsuspend/<?php echo $account->id; ?>" class="btn secondary">Unsuspend</a>
<a href="/admin/account/terminate/<?php echo $account->id; ?>" class="btn" style="background:rgba(255,50,50,.15);color:#ff6b6b;border:1px solid rgba(255,50,50,.2)" onclick="return confirm('Terminate? This deletes the Linux user.')">Terminate</a>
</div>
