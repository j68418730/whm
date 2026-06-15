<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (!empty($restricted)): ?>
<div class="card"><h3 style="color:var(--accent)">Databases</h3>
<p style="color:var(--text-secondary)">Database management is not available for your current package type.</p>
</div>
<?php else: ?>

<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px">
<a href="/user/databases/create" onclick="document.getElementById('newDbForm').style.display='block';return false" class="btn primary">+ New Database</a>
<a href="/user/databases/user" onclick="document.getElementById('newUserForm').style.display='block';return false" class="btn secondary">+ New User</a>
<a href="/pma_signon.php" target="_blank" class="btn primary">🔑 Auto-Login phpMyAdmin</a>
</div>

<div id="newDbForm" style="display:none" class="card" style="margin-bottom:16px;max-width:400px">
<form method="POST" action="/user/databases/create"><div style="display:flex;gap:8px;align-items:end">
<div class="form-group" style="flex:1"><label>Database Name</label>
<div style="display:flex"><span style="padding:8px 10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px 0 0 6px;color:var(--text-muted);font-size:13px"><?php echo htmlspecialchars($hosting->username ?? 'user'); ?>_</span>
<input name="name" required style="flex:1;padding:8px;border-radius:0 6px 6px 0;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></div></div>
<div class="form-group"><button type="submit" class="btn primary btn-sm">Create</button></div>
</div></form>
</div>

<div id="newUserForm" style="display:none" class="card" style="margin-bottom:16px;max-width:500px">
<form method="POST" action="/user/databases/user"><div style="display:flex;gap:8px;flex-wrap:wrap;align-items:end">
<div class="form-group" style="flex:1"><label>Username</label>
<div style="display:flex"><span style="padding:8px 10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px 0 0 6px;color:var(--text-muted);font-size:13px"><?php echo htmlspecialchars($hosting->username ?? 'user'); ?>_</span>
<input name="username" required style="flex:1;padding:8px;border-radius:0 6px 6px 0;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></div></div>
<div class="form-group" style="flex:1"><label>Password</label><input name="password" style="padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none" placeholder="auto-generated if empty"></div>
<div class="form-group"><label>Database</label><select name="database" style="padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"><option value="">None</option></select></div>
<div class="form-group"><button type="submit" class="btn primary btn-sm">Create</button></div>
</div></form>
</div>

<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">Databases</h3>
<table><tr><th>Database</th><th>Size</th><th>Actions</th></tr>
<?php if (!empty($databases)): foreach ($databases as $db): ?>
<tr><td><?php echo htmlspecialchars($db->name); ?></td><td><?php echo $db->size ?? '-'; ?></td>
<td><a href="/user/databases/delete/<?php echo urlencode($db->name); ?>" class="btn btn-sm danger" onclick="return confirm('Drop database?')">Drop</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b">No databases yet.</td></tr>
<?php endif; ?></table>
</div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Database Users</h3>
<table><tr><th>Username</th><th></th></tr>
<?php if (!empty($users)): foreach ($users as $u): ?>
<tr><td><?php echo htmlspecialchars($u->username); ?></td><td>-</td></tr>
<?php endforeach; else: ?><tr><td colspan="2" style="text-align:center;padding:20px;color:#64748b">No database users yet.</td></tr>
<?php endif; ?></table>
</div>

<?php endif; ?>
