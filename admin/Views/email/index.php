<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Mail Accounts</h3><div class="value"><?php echo count($accounts); ?></div></div>
<div class="stat-card"><h3>Queue</h3><div class="value" style="font-size:20px"><?php echo $queueSize; ?></div></div>
<div class="stat-card"><h3>Postfix</h3><div class="value" style="font-size:16px;color:<?php echo $postfix === 'active' ? '#4ade80' : '#f87171'; ?>"><?php echo $postfix; ?></div></div>
<div class="stat-card"><h3>Dovecot</h3><div class="value" style="font-size:16px;color:<?php echo $dovecot === 'active' ? '#4ade80' : '#f87171'; ?>"><?php echo $dovecot; ?></div></div>
</div>

<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:20px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<a href="/admin/email?tab=accounts" style="padding:8px 16px;border-radius:6px 6px 0 0;text-decoration:none;font-size:14px;<?php echo $tab==='accounts'?'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff':'color:var(--text-secondary)'; ?>">📧 Accounts</a>
<a href="/admin/email?tab=forwarders" style="padding:8px 16px;border-radius:6px 6px 0 0;text-decoration:none;font-size:14px;<?php echo $tab==='forwarders'?'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff':'color:var(--text-secondary)'; ?>">↪ Forwarders</a>
<a href="/admin/email?tab=autoresponders" style="padding:8px 16px;border-radius:6px 6px 0 0;text-decoration:none;font-size:14px;<?php echo $tab==='autoresponders'?'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff':'color:var(--text-secondary)'; ?>">🤖 Autoresponder</a>
<a href="/admin/email?tab=spam" style="padding:8px 16px;border-radius:6px 6px 0 0;text-decoration:none;font-size:14px;<?php echo $tab==='spam'?'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff':'color:var(--text-secondary)'; ?>">🛡️ Spam</a>
<a href="/admin/email?tab=queue" style="padding:8px 16px;border-radius:6px 6px 0 0;text-decoration:none;font-size:14px;<?php echo $tab==='queue'?'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff':'color:var(--text-secondary)'; ?>">📨 Mail Queue</a>
</div>

<?php if ($tab === 'accounts'): ?>
<div style="display:flex;gap:8px;margin-bottom:12px">
<a class="btn primary" onclick="document.getElementById('acctForm').classList.toggle('hidden')">+ Add Account</a>
</div>
<div id="acctForm" class="card hidden" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/admin/email/account/create">
<div class="form-group" style="display:flex;gap:6px"><div style="flex:1"><label>Username</label><input name="email" required></div><div style="flex:1"><label>@ Domain</label>
<select name="domain"><?php foreach ($domains as $d): if ($d->domain): ?><option value="<?php echo htmlspecialchars($d->domain); ?>"><?php echo htmlspecialchars($d->domain); ?></option><?php endif; endforeach; ?></select></div></div>
<div class="form-group" style="display:flex;gap:6px"><div style="flex:1"><label>Password</label><input name="password" type="password" required></div><div style="flex:1"><label>Quota (MB)</label><input name="quota" type="number" value="1000"></div></div>
<button type="submit" class="btn primary">Create</button>
</form></div>
<table><tr><th>Email</th><th>Domain</th><th>Quota</th><th>Created</th><th></th></tr>
<?php if (!empty($accounts)): foreach ($accounts as $a): ?>
<tr><td><?php echo htmlspecialchars($a->email); ?></td><td><?php echo htmlspecialchars($a->domain); ?></td><td><?php echo $a->quota_mb; ?> MB</td><td><?php echo $a->created_at; ?></td>
<td><a href="/admin/email/account/delete/<?php echo $a->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">Delete</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No mail accounts.</td></tr>
<?php endif; ?></table>

<?php elseif ($tab === 'forwarders'): ?>
<div style="display:flex;gap:8px;margin-bottom:12px">
<a class="btn primary" onclick="document.getElementById('fwdForm').classList.toggle('hidden')">+ Add Forwarder</a>
</div>
<div id="fwdForm" class="card hidden" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/admin/email/forwarder/create">
<div class="form-group"><label>From (local part)</label><input name="from" required placeholder="info"></div>
<div class="form-group"><label>@ Domain</label><select name="domain"><?php foreach ($domains as $d): if ($d->domain): ?><option value="<?php echo htmlspecialchars($d->domain); ?>"><?php echo htmlspecialchars($d->domain); ?></option><?php endif; endforeach; ?></select></div>
<div class="form-group"><label>Forward To</label><input name="to" type="email" required placeholder="user@example.com"></div>
<button type="submit" class="btn primary">Add Forwarder</button>
</form></div>
<table><tr><th>From</th><th>To</th><th></th></tr>
<?php if (!empty($forwarders)): foreach ($forwarders as $f): ?>
<tr><td><?php echo htmlspecialchars($f->from_email); ?></td><td><?php echo htmlspecialchars($f->to_email); ?></td>
<td><a href="/admin/email/forwarder/delete/<?php echo $f->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">Delete</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b">No forwarders.</td></tr>
<?php endif; ?></table>

<?php elseif ($tab === 'autoresponders'): ?>
<div style="display:flex;gap:8px;margin-bottom:12px">
<a class="btn primary" onclick="document.getElementById('autoForm').classList.toggle('hidden')">+ Add Autoresponder</a>
</div>
<div id="autoForm" class="card hidden" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/admin/email/autoresponder/set">
<div class="form-group"><label>Email</label><input name="email" required placeholder="info@domain.com"></div>
<div class="form-group"><label>@ Domain</label><select name="domain"><?php foreach ($domains as $d): if ($d->domain): ?><option value="<?php echo htmlspecialchars($d->domain); ?>"><?php echo htmlspecialchars($d->domain); ?></option><?php endif; endforeach; ?></select></div>
<div class="form-group"><label>Subject</label><input name="subject" required value="Out of Office"></div>
<div class="form-group"><label>Message</label><textarea name="message" rows="4" required></textarea></div>
<button type="submit" class="btn primary">Save</button>
</form></div>
<table><tr><th>Email</th><th>Subject</th><th>Status</th><th></th></tr>
<?php if (!empty($autoresponders)): foreach ($autoresponders as $a): ?>
<tr><td><?php echo htmlspecialchars($a->email); ?></td><td><?php echo htmlspecialchars($a->subject); ?></td>
<td><span class="status-badge status-<?php echo $a->enabled ? 'active' : 'terminated'; ?>"><?php echo $a->enabled ? 'On' : 'Off'; ?></span></td>
<td><?php if ($a->enabled): ?><a href="/admin/email/autoresponder/disable/<?php echo $a->id; ?>" class="btn btn-sm secondary">Disable</a><?php endif; ?></td></tr>
<?php endforeach; else: ?><tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No autoresponders.</td></tr>
<?php endif; ?></table>

<?php elseif ($tab === 'spam'): ?>
<div class="card" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/admin/email/spam/set">
<h3 style="color:var(--accent);margin-bottom:8px">Spam Filter Settings</h3>
<div class="form-group"><label>Domain</label><select name="domain"><?php foreach ($domains as $d): if ($d->domain): ?><option value="<?php echo htmlspecialchars($d->domain); ?>"><?php echo htmlspecialchars($d->domain); ?></option><?php endif; endforeach; ?></select></div>
<div class="form-group"><label>Action</label><select name="action"><option value="move_junk">Move to Junk</option><option value="delete">Delete</option><option value="tag">Tag Subject</option></select></div>
<div class="form-group"><label>Threshold</label><input name="threshold" value="5.0" placeholder="1.0 - 10.0"></div>
<button type="submit" class="btn primary">Save</button>
</form></div>
<table><tr><th>Domain</th><th>Action</th><th>Threshold</th><th>Date</th></tr>
<?php if (!empty($spam)): foreach ($spam as $s): ?>
<tr><td><?php echo htmlspecialchars($s->domain); ?></td><td><?php echo $s->action; ?></td><td><?php echo $s->threshold; ?></td><td><?php echo $s->created_at; ?></td></tr>
<?php endforeach; else: ?><tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No spam settings.</td></tr>
<?php endif; ?></table>

<?php elseif ($tab === 'queue'): ?>
<div class="card" style="max-width:500px;margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:8px">Mail Queue</h3>
<p style="color:var(--text-secondary)">Queue size: <strong><?php echo $queueSize; ?></strong> messages</p>
<a href="/admin/email/queue/clear" class="btn danger" onclick="return confirm('Delete all queued messages?')">Clear Queue</a>
</div>
<pre style="background:rgba(0,0,0,.3);padding:16px;border-radius:8px;font-size:12px;color:#8b949e;max-height:400px;overflow-y:auto;font-family:monospace"><?php echo htmlspecialchars(shell_exec('mailq 2>/dev/null') ?: 'No mail in queue.'); ?></pre>

<?php endif; ?>
