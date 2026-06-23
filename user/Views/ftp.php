<style>
.cred-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px}
.cred-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:18px}
.cred-card h3{font-size:14px;font-weight:600;margin:0 0 10px;color:var(--accent)}
.cred-row{display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:12px}
.cred-row:last-child{border:none}
.cred-label{color:#64748b}
.cred-value{font-weight:500;color:#e0e0e0}
.cred-value code{background:rgba(0,0,0,.4);padding:1px 6px;border-radius:3px;font-size:11px}
.ftp-list{width:100%;border-collapse:collapse;font-size:13px}
.ftp-list th{text-align:left;padding:8px 12px;color:#64748b;font-size:11px;text-transform:uppercase;border-bottom:1px solid rgba(255,255,255,.06)}
.ftp-list td{padding:8px 12px;border-bottom:1px solid rgba(255,255,255,.04)}
.ftp-list tr:hover td{background:rgba(0,191,255,.02)}
input,select{padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;width:100%;box-sizing:border-box}
input:focus{border-color:#0A84FF}
.btn{padding:7px 14px;border-radius:6px;font-size:12px;font-weight:500;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;border:none}
.btn-primary{background:rgba(0,140,255,.12);color:#0A84FF;border:1px solid rgba(0,140,255,.2)}
.btn-danger{background:rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.2)}
</style>

<?php if (isset($_SESSION['success'])): ?><div class="alert alert-success" style="margin-bottom:16px"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error'])): ?><div class="alert alert-danger" style="margin-bottom:16px"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div><?php endif; ?>

<h2>FTP Accounts</h2>
<p style="color:#64748b;margin-bottom:16px">Manage FTP users and connections for your account.</p>

<div class="cred-grid">
<div class="cred-card">
<h3>🔑 Primary Account</h3>
<div class="cred-row"><span class="cred-label">Server</span><span class="cred-value"><code><?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost'); ?></code></span></div>
<div class="cred-row"><span class="cred-label">Port</span><span class="cred-value">21 (FTP) / 22 (SFTP)</span></div>
<div class="cred-row"><span class="cred-label">Username</span><span class="cred-value"><code><?php echo htmlspecialchars($hosting->username ?? ''); ?></code></span></div>
<div class="cred-row"><span class="cred-label">Password</span><span class="cred-value"><code>Account password</code></span></div>
<div class="cred-row"><span class="cred-label">Home Dir</span><span class="cred-value"><code>/home/<?php echo htmlspecialchars($hosting->username ?? ''); ?>/</code></span></div>
</div>

<div class="cred-card">
<h3>➕ New FTP User</h3>
<form method="POST" action="/user/ftp/create">
<div style="margin-bottom:8px"><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Username</label>
<input name="username" placeholder="e.g. uploader" required></div>
<div style="margin-bottom:8px"><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Password</label>
<input type="password" name="password" required minlength="6"></div>
<div style="margin-bottom:8px"><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Folder (relative to home)</label>
<input name="directory" value="public_html" placeholder="e.g. public_html/uploads"></div>
<button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">➕ Create FTP User</button>
</form>
</div>
</div>

<div class="card" style="margin-top:16px">
<h3 style="margin-bottom:12px">Additional FTP Users</h3>
<?php if (empty($ftpAccounts)): ?>
<p style="color:#64748b;font-size:13px;text-align:center;padding:20px">No additional FTP users created yet.</p>
<?php else: ?>
<table class="ftp-list">
<thead><tr><th>Username</th><th>Directory</th><th>Status</th><th></th></tr></thead>
<tbody>
<?php foreach ($ftpAccounts as $a): ?>
<tr>
<td><code><?php echo htmlspecialchars($a->username); ?></code></td>
<td><code>/home/<?php echo htmlspecialchars($hosting->username ?? ''); ?>/<?php echo htmlspecialchars($a->directory); ?></code></td>
<td><span style="color:#4ade80">● Active</span></td>
<td><a href="/user/ftp/delete/<?php echo $a->id; ?>" class="btn btn-danger" onclick="return confirm('Delete <?php echo htmlspecialchars($a->username); ?>?')">✕ Delete</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:16px">
<a href="ftp://<?php echo htmlspecialchars($hosting->username ?? ''); ?>@<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost'); ?>" class="btn btn-primary" target="_blank">🔗 FTP Connection</a>
<a href="sftp://<?php echo htmlspecialchars($hosting->username ?? ''); ?>@<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost'); ?>" class="btn btn-primary" target="_blank">🔗 SFTP Connection</a>
<a href="/user/files" class="btn btn-primary">📁 File Manager</a>
</div>
