<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px">
<a href="javascript:void(0)" onclick="showTab('accounts')" class="btn primary">Email Accounts</a>
<a href="javascript:void(0)" onclick="showTab('forwarders')" class="btn secondary">Forwarders</a>
<a href="javascript:void(0)" onclick="showTab('autoresponder')" class="btn secondary">Autoresponder</a>
<a href="javascript:void(0)" onclick="showTab('spam')" class="btn secondary">Spam</a>
<a href="<?php echo htmlspecialchars($webmailUrl); ?>" target="_blank" class="btn secondary">📧 Webmail</a>
</div>

<div id="tab-accounts" class="tab-content active">
<div class="card" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/user/email/create"><div style="display:flex;gap:8px;flex-wrap:wrap;align-items:end">
<div class="form-group" style="flex:1"><label>Email</label><div style="display:flex"><input name="email" required placeholder="user" style="flex:1;padding:8px;border-radius:6px 0 0 6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none">
<span style="padding:8px 12px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-left:none;border-radius:0 6px 6px 0;color:var(--text-muted);font-size:13px">@<?php echo htmlspecialchars($domain); ?></span></div></div>
<div class="form-group"><label>Password</label><input type="password" name="password" required minlength="6" style="padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></div>
<div class="form-group"><label>Quota MB</label><input name="quota" value="1000" style="width:80px;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></div>
<div class="form-group"><button type="submit" class="btn primary">Create</button></div>
</div></form>
</div>
<table><tr><th>Email</th><th>Quota</th><th></th></tr>
<?php if (!empty($accounts)): foreach ($accounts as $a): ?>
<tr><td><?php echo htmlspecialchars($a->email); ?></td><td><?php echo $a->quota_mb ?? 1000; ?> MB</td>
<td><a href="/user/email/delete/<?php echo $a->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">✕</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b">No email accounts yet.</td></tr>
<?php endif; ?></table>
</div>

<div id="tab-forwarders" class="tab-content" style="display:none">
<div class="card" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/user/email/forwarder"><div style="display:flex;gap:8px;flex-wrap:wrap;align-items:end">
<div class="form-group"><label>From</label><div style="display:flex"><input name="from" required placeholder="info" style="padding:8px;border-radius:6px 0 0 6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none">
<span style="padding:8px 12px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-left:none;border-radius:0 6px 6px 0;color:var(--text-muted);font-size:13px">@<?php echo htmlspecialchars($domain); ?></span></div></div>
<div class="form-group" style="flex:1"><label>To</label><input name="to" type="email" required placeholder="forward@example.com" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></div>
<div class="form-group"><button type="submit" class="btn primary">Add</button></div>
</div></form>
</div>
<table><tr><th>From</th><th>To</th><th></th></tr>
<?php if (!empty($forwarders)): foreach ($forwarders as $f): ?>
<tr><td><?php echo htmlspecialchars($f->from_email); ?></td><td><?php echo htmlspecialchars($f->to_email); ?></td>
<td><a href="/user/email/forwarder/delete/<?php echo $f->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">✕</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b">No forwarders.</td></tr>
<?php endif; ?></table>
</div>

<div id="tab-autoresponder" class="tab-content" style="display:none">
<div class="card" style="max-width:500px"><form method="POST" action="/user/email/autoresponder">
<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:end">
<div class="form-group"><label>Email</label><div style="display:flex"><input name="email" required placeholder="info" style="padding:8px;border-radius:6px 0 0 6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none">
<span style="padding:8px 12px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-left:none;border-radius:0 6px 6px 0;color:var(--text-muted);font-size:13px">@<?php echo htmlspecialchars($domain); ?></span></div></div>
<div class="form-group" style="flex:1"><label>Subject</label><input name="subject" placeholder="Out of Office" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></div>
</div>
<div class="form-group"><label>Message</label><textarea name="message" rows="3" placeholder="I am currently out of the office..." style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></textarea></div>
<button type="submit" class="btn primary">Save Autoresponder</button>
</form></div>
</div>

<div id="tab-spam" class="tab-content" style="display:none">
<div class="card" style="max-width:500px"><form method="POST" action="/user/email/spam">
<div class="form-group"><label>Spam Action</label><select name="action" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"><option value="move_junk">Move to Junk</option><option value="delete">Delete</option><option value="subject_tag">Tag Subject</option></select></div>
<div class="form-group"><label>Threshold</label><input name="threshold" value="5.0" style="padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></div>
<button type="submit" class="btn primary">Save</button>
</form></div>
</div>

<script>
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(function(el) { el.style.display = 'none'; });
    document.getElementById('tab-' + tab).style.display = 'block';
}
</script>
