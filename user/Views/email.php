<style>
.email-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-bottom:16px}
.email-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:14px;text-align:center}
.email-stat .num{font-size:22px;font-weight:800}
.email-stat .lbl{font-size:10px;color:#64748b;margin-top:2px}
.tab-bar{display:flex;gap:0;border-bottom:1px solid rgba(255,255,255,.06);margin-bottom:16px;flex-wrap:wrap}
.tab{padding:8px 14px;font-size:12px;cursor:pointer;color:#64748b;border-bottom:2px solid transparent;transition:.15s}
.tab:hover{color:#e0e0e0}
.tab.active{color:#0A84FF;border-bottom-color:#0A84FF}
.tab-content{display:none}
.tab-content.active{display:block}
input,select,textarea{width:100%;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;box-sizing:border-box}
input:focus,select:focus{border-color:#0A84FF}
</style>

<?php if (isset($_SESSION['success'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div><?php endif; ?>

<h2>📧 Email</h2>
<p style="color:#64748b;margin-bottom:16px">Manage your email accounts, forwarders, and spam filters.</p>

<?php
$domain = $hosting->domain ?? ($_SERVER['HTTP_HOST'] ?? 'planet-hosts.com');
$totalMailboxes = count($accounts ?? []);
$totalFwd = count($forwarders ?? []);
$hasAuto = !empty($autoresponder);
$spamScore = $_SESSION['spam_threshold'] ?? '5.0';
$webmailUrl = '/webmail_autologin.php';
?>

<div class="email-grid">
<div class="email-stat"><div class="num" style="color:#0A84FF"><?php echo $totalMailboxes; ?></div><div class="lbl">Mailboxes</div></div>
<div class="email-stat"><div class="num" style="color:#4ade80">0</div><div class="lbl">Unread</div></div>
<div class="email-stat"><div class="num" style="color:#38bdf8"><?php echo $totalFwd; ?></div><div class="lbl">Forwarders</div></div>
<div class="email-stat"><div class="num" style="color:#facc15">--</div><div class="lbl">Disk Used</div></div>
</div>

<div class="tab-bar">
<div class="tab active" onclick="showTab('accounts',this)">📋 Accounts</div>
<div class="tab" onclick="showTab('create',this)">➕ Create</div>
<div class="tab" onclick="showTab('forwarders',this)">↪ Forwarders</div>
<div class="tab" onclick="showTab('autoresponder',this)">📝 Autoresponder</div>
<div class="tab" onclick="showTab('spam',this)">🛡️ Spam</div>
<div class="tab" onclick="showTab('security',this)">🔒 Security</div>
<div class="tab" onclick="showTab('mobile',this)">📱 Mobile</div>
</div>

<!-- Accounts Tab -->
<div id="tab-accounts" class="tab-content active">
<div class="card"><h3>Email Accounts <span style="font-size:12px;color:#64748b;font-weight:400">(<?php echo $totalMailboxes; ?>)</span></h3>
<?php if (empty($accounts)): ?>
<p style="color:#64748b;font-size:13px;text-align:center;padding:20px">No email accounts yet. <a href="javascript:void(0)" onclick="showTab('create',document.querySelectorAll('.tab')[1])" style="color:#0A84FF">Create one</a>.</p>
<?php else: ?>
<table class="table"><thead><tr><th>Email</th><th>Quota</th><th>Status</th><th></th></tr></thead>
<tbody><?php foreach ($accounts as $a): ?>
<tr><td><?php echo htmlspecialchars($a->email); ?></td><td><?php echo $a->quota_mb ?? 1000; ?> MB</td>
<td><span style="color:#4ade80">● Active</span></td>
<td style="display:flex;gap:3px">
<a href="/user/email/password/<?php echo $a->id; ?>" class="btn btn-sm btn-warning" onclick="return promptEmailPw(<?php echo $a->id; ?>)">🔑</a>
<a href="/user/email/delete/<?php echo $a->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete <?php echo htmlspecialchars($a->email); ?>?')">🗑</a>
</td></tr>
<?php endforeach; ?></tbody></table>
<?php endif; ?>
<div style="margin-top:12px;display:flex;gap:8px">
<a href="<?php echo $webmailUrl; ?>" target="_blank" class="btn btn-sm" style="background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.2);padding:6px 14px;border-radius:6px;text-decoration:none">📧 Open Webmail</a>
</div>
</div>
</div>

<!-- Create Tab -->
<div id="tab-create" class="tab-content">
<div class="card" style="max-width:450px">
<h3>➕ Create Email Account</h3>
<form method="POST" action="/user/email/create" onsubmit="return checkEmail(this)">
<div style="margin-bottom:10px"><label style="font-size:11px;color:#64748b">Email Address</label>
<div style="display:flex"><input name="email" id="newEmail" required placeholder="you" style="flex:1;border-radius:6px 0 0 6px" oninput="checkEmailExists(this.value)">
<span style="padding:7px 10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-left:none;border-radius:0 6px 6px 0;color:#64748b;font-size:12px">@<?php echo htmlspecialchars($domain); ?></span></div>
<small id="emailCheck" style="color:#64748b;font-size:10px">Checking availability...</small></div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#64748b">Password</label>
<div style="display:flex;gap:6px"><input type="text" name="password" id="emailPw" required minlength="6" style="flex:1">
<button type="button" class="btn btn-sm btn-primary" style="white-space:nowrap" onclick="document.getElementById('emailPw').value=Math.random().toString(36).slice(2,10)+'A1!'">Generate</button></div></div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#64748b">Mailbox Quota</label>
<select name="quota"><option value="100">100 MB</option><option value="250">250 MB</option><option value="500">500 MB</option><option value="1000" selected>1 GB</option><option value="5000">5 GB</option><option value="unlimited">Unlimited</option></select></div>
<button type="submit" class="btn btn-sm btn-primary" style="width:100%;padding:10px;justify-content:center">➕ Create Email Account</button>
</form></div>
</div>

<!-- Forwarders Tab -->
<div id="tab-forwarders" class="tab-content">
<div class="card" style="max-width:500px;margin-bottom:12px">
<h3>➕ Add Forwarder</h3>
<form method="POST" action="/user/email/forwarder"><div style="display:flex;gap:8px;flex-wrap:wrap;align-items:end">
<div class="form-group" style="flex:1"><label style="font-size:11px;color:#64748b">From</label>
<div style="display:flex"><input name="from" required placeholder="info" style="padding:7px;border-radius:6px 0 0 6px">
<span style="padding:7px 10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-left:none;border-radius:0 6px 6px 0;color:#64748b;font-size:12px">@<?php echo htmlspecialchars($domain); ?></span></div></div>
<div class="form-group" style="flex:2"><label style="font-size:11px;color:#64748b">Forward To</label>
<input name="to" type="email" required placeholder="forward@example.com"></div>
<div class="form-group"><button type="submit" class="btn btn-sm btn-primary" style="padding:7px 16px">Add Forwarder</button></div>
</div></form></div>
<div class="card"><h3>Forwarders <span style="font-size:12px;color:#64748b;font-weight:400">(<?php echo $totalFwd; ?>)</span></h3>
<?php if (empty($forwarders)): ?>
<p style="color:#64748b;font-size:13px;text-align:center;padding:15px">No forwarders.</p>
<?php else: ?>
<table class="table"><thead><tr><th>From</th><th>To</th><th></th></tr></thead>
<tbody><?php foreach ($forwarders as $f): ?>
<tr><td><?php echo htmlspecialchars($f->from_email); ?></td><td><?php echo htmlspecialchars($f->to_email); ?></td>
<td><a href="/user/email/forwarder/delete/<?php echo $f->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete forwarder?')">✕</a></td></tr>
<?php endforeach; ?></tbody></table>
<?php endif; ?></div>
</div>

<!-- Autoresponder Tab -->
<div id="tab-autoresponder" class="tab-content">
<div class="card" style="max-width:500px">
<h3>📝 Vacation / Out of Office</h3>
<form method="POST" action="/user/email/autoresponder">
<div style="margin-bottom:10px"><label style="font-size:11px;color:#64748b">Email</label>
<div style="display:flex"><input name="email" required placeholder="you" style="border-radius:6px 0 0 6px">
<span style="padding:7px 10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-left:none;border-radius:0 6px 6px 0;color:#64748b;font-size:12px">@<?php echo htmlspecialchars($domain); ?></span></div></div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#64748b">Subject</label>
<input name="subject" value="Out of Office" placeholder="Out of Office"></div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#64748b">Message</label>
<textarea name="message" rows="4" placeholder="I am currently out of the office and will respond when I return."></textarea></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px">
<div><label style="font-size:11px;color:#64748b">Start Date</label><input name="start_date" type="date"></div>
<div><label style="font-size:11px;color:#64748b">End Date</label><input name="end_date" type="date"></div>
</div>
<button type="submit" class="btn btn-sm btn-primary" style="width:100%">💾 Save Autoresponder</button>
</form></div>
</div>

<!-- Spam Tab -->
<div id="tab-spam" class="tab-content">
<div class="card" style="max-width:500px">
<h3>🛡️ Spam Filters</h3>
<form method="POST" action="/user/email/spam">
<div style="margin-bottom:10px"><label style="font-size:11px;color:#64748b">Spam Action</label>
<select name="action"><option value="move_junk">Move to Junk Folder</option><option value="delete">Delete</option><option value="subject_tag">Tag Subject [SPAM]</option></select></div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#64748b">Spam Score Threshold</label>
<input name="threshold" value="<?php echo htmlspecialchars($spamScore); ?>" placeholder="5.0"></div>
<button type="submit" class="btn btn-sm btn-primary">💾 Save Spam Settings</button>
</form>
<div style="margin-top:16px;border-top:1px solid rgba(255,255,255,.06);padding-top:12px">
<h4 style="font-size:12px;margin:0 0 8px">Blacklist / Whitelist</h4>
<textarea rows="3" placeholder="Enter email addresses or domains (one per line)" style="margin-bottom:6px"></textarea>
<button class="btn btn-sm btn-primary">Save Lists</button>
</div>
</div>
</div>

<!-- Security Tab -->
<div id="tab-security" class="tab-content">
<div class="card" style="max-width:500px">
<h3>🔒 Email Security</h3>
<div style="margin-bottom:10px;padding:10px;background:rgba(74,222,128,.06);border-radius:6px"><span style="color:#4ade80">✅ SPF Record</span><br><small style="color:#64748b">v=spf1 ip4:45.61.59.55 ~all</small></div>
<div style="margin-bottom:10px;padding:10px;background:rgba(250,204,21,.06);border-radius:6px"><span style="color:#facc15">⚠️ DKIM</span><br><small style="color:#64748b">Not configured — <a href="#" style="color:#0A84FF">Generate DKIM key</a></small></div>
<div style="margin-bottom:10px;padding:10px;background:rgba(74,222,128,.06);border-radius:6px"><span style="color:#4ade80">✅ DMARC</span><br><small style="color:#64748b">v=DMARC1; p=quarantine; rua=mailto:admin@<?php echo htmlspecialchars($domain); ?></small></div>
<button class="btn btn-sm btn-primary">🔧 Fix Issues</button>
</div>
</div>

<!-- Mobile Tab -->
<div id="tab-mobile" class="tab-content">
<div class="card" style="max-width:500px">
<h3>📱 Mobile & Email Client Setup</h3>
<div style="margin-bottom:12px"><h4 style="font-size:12px;margin:0 0 6px">📧 IMAP Settings</h4>
<div style="background:rgba(0,0,0,.3);border:1px solid rgba(0,191,255,.1);border-radius:6px;padding:10px;font-family:monospace;font-size:11px;color:#4ade80">
Server: <?php echo htmlspecialchars($domain); ?><br>
Port: 993 (SSL/TLS)<br>
Username: your@email.com<br>
Password: (your email password)</div></div>
<div style="margin-bottom:12px"><h4 style="font-size:12px;margin:0 0 6px">📤 SMTP Settings</h4>
<div style="background:rgba(0,0,0,.3);border:1px solid rgba(0,191,255,.1);border-radius:6px;padding:10px;font-family:monospace;font-size:11px;color:#38bdf8">
Server: <?php echo htmlspecialchars($domain); ?><br>
Port: 465 (SSL/TLS) or 587 (STARTTLS)<br>
Auth: Required<br>
Username: your@email.com<br>
Password: (your email password)</div></div>
<div style="margin-bottom:12px"><h4 style="font-size:12px;margin:0 0 6px">📩 POP3 Settings</h4>
<div style="background:rgba(0,0,0,.3);border:1px solid rgba(0,191,255,.1);border-radius:6px;padding:10px;font-family:monospace;font-size:11px;color:#a78bfa">
Server: <?php echo htmlspecialchars($domain); ?><br>
Port: 995 (SSL/TLS)<br>
Username: your@email.com<br>
Password: (your email password)</div></div>
</div>
</div>

<script>
function showTab(name, el) {
    document.querySelectorAll('.tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('.tab-content').forEach(function(t) { t.classList.remove('active'); });
    if (el) el.classList.add('active'); else document.querySelector('.tab[onclick*="'+name+'"]')?.classList.add('active');
    document.getElementById('tab-'+name).classList.add('active');
}

function checkEmailExists(val) {
    var check = document.getElementById('emailCheck');
    if (!val) { check.textContent = 'Enter a username'; check.style.color = '#64748b'; return; }
    if (val.length < 2) { check.textContent = 'Too short'; check.style.color = '#f87171'; return; }
    <?php if (!empty($accounts)): ?>
    var exists = [<?php foreach($accounts as $a): $local = explode('@', $a->email)[0]; echo "'$local',"; endforeach; ?>];
    if (exists.indexOf(val) > -1) { check.textContent = '❌ Username already exists'; check.style.color = '#f87171'; return false; }
    <?php endif; ?>
    check.textContent = '✅ Username available'; check.style.color = '#4ade80';
}

function checkEmail(form) {
    var val = form.querySelector('[name=email]').value;
    <?php if (!empty($accounts)): ?>
    var exists = [<?php foreach($accounts as $a): $local = explode('@', $a->email)[0]; echo "'$local',"; endforeach; ?>];
    if (exists.indexOf(val) > -1) { alert('Username already exists!'); return false; }
    <?php endif; ?>
    return true;
}

function promptEmailPw(id) {
    var pw = prompt('New password (min 6 chars):');
    if (pw && pw.length >= 6) {
        var x = new XMLHttpRequest();
        x.open('POST', '/user/email/password/' + id, true);
        x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        x.onload = function() { location.reload(); };
        x.send('password=' + encodeURIComponent(pw));
    }
    return false;
}
</script>
