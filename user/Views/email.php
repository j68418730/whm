<style>
.email-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px;margin-bottom:16px}
.email-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:14px;text-align:center}
.email-stat .num{font-size:22px;font-weight:800}
.email-stat .lbl{font-size:10px;color:#64748b;margin-top:2px}
.ecard{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;margin-bottom:12px}
.ecard h3{font-size:14px;font-weight:600;margin:0 0 4px;display:flex;align-items:center;gap:8px}
.ecard .email-addr{font-size:12px;color:#64748b;margin-bottom:8px}
.ecard .actions{display:flex;gap:4px;flex-wrap:wrap}
.ecard .actions a{padding:4px 10px;border-radius:5px;font-size:10px;text-decoration:none;border:1px solid rgba(255,255,255,.08)}
input,select,textarea{width:100%;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;box-sizing:border-box}
input:focus{border-color:#0A84FF}
.tab-bar{display:flex;gap:0;border-bottom:1px solid rgba(255,255,255,.06);margin-bottom:14px;flex-wrap:wrap}
.tab{padding:8px 14px;font-size:11px;cursor:pointer;color:#64748b;border-bottom:2px solid transparent;transition:.15s}
.tab:hover{color:#e0e0e0}
.tab.active{color:#0A84FF;border-bottom-color:#0A84FF}
.tab-content{display:none}
.tab-content.active{display:block}
</style>

<?php if (isset($_SESSION['success'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div><?php endif; ?>

<h2>📧 Email</h2>
<p style="color:#64748b;margin-bottom:16px">Manage your email accounts, forwarders, and settings.</p>

<?php $domain = $hosting->domain ?? ($_SERVER['HTTP_HOST'] ?? 'planet-hosts.com'); $webmailUrl = '/webmail_autologin.php'; ?>

<div class="email-grid">
<div class="email-stat"><div class="num" style="color:#0A84FF"><?php echo count($accounts ?? []);?></div><div class="lbl">Mailboxes</div></div>
<div class="email-stat"><div class="num" style="color:#38bdf8"><?php echo count($forwarders ?? []);?></div><div class="lbl">Forwarders</div></div>
<div class="email-stat"><div class="num" style="color:#4ade80"><?php echo $hasAuto?'1':'0';?></div><div class="lbl">Autoresponder</div></div>
</div>

<div class="tab-bar">
<div class="tab active" onclick="showTab('accounts',this)">📋 Mailboxes</div>
<div class="tab" onclick="showTab('create',this)">➕ Create</div>
</div>

<div id="tab-accounts" class="tab-content active">
<?php if (empty($accounts)): ?>
<div class="ecard" style="text-align:center;padding:30px"><p style="color:#64748b">No email accounts yet.</p></div>
<?php else: foreach ($accounts as $a): $local = explode('@', $a->email)[0]; ?>
<div class="ecard" style="border-left:3px solid #0A84FF">
<h3>📧 <?php echo htmlspecialchars($a->email); ?></h3>
<div class="email-addr">Quota: <?php echo $a->quota_mb ?? 1000; ?> MB · <span style="color:#4ade80">● Active</span></div>
<div class="actions">
<a href="<?php echo $webmailUrl; ?>" target="_blank" style="background:rgba(74,222,128,.1);color:#4ade80;border-color:rgba(74,222,128,.2)">📨 Webmail</a>
<a href="/user/email/password/<?php echo $a->id;?>" style="background:rgba(250,204,21,.1);color:#facc15;border-color:rgba(250,204,21,.2)" onclick="return promptEmailPw(<?php echo $a->id;?>)">🔑 Password</a>
<a href="/user/email/forwarder" style="background:rgba(56,189,248,.1);color:#38bdf8;border-color:rgba(56,189,248,.2)">↪ Forwarders</a>
<a href="/user/email/autoresponder" style="background:rgba(168,85,247,.1);color:#a855f7;border-color:rgba(168,85,247,.2)">📝 Autoresponder</a>
<a href="/user/email/spam" style="background:rgba(250,204,21,.1);color:#facc15;border-color:rgba(250,204,21,.2)">🛡️ Spam</a>
<a href="#" style="background:rgba(0,140,255,.1);color:#0A84FF;border-color:rgba(0,140,255,.2)" onclick="showMobile('<?php echo htmlspecialchars($a->email);?>')">📱 Mobile</a>
<a href="/user/email/delete/<?php echo $a->id;?>" style="background:rgba(248,113,113,.1);color:#f87171;border-color:rgba(248,113,113,.2)" onclick="return confirm('Delete <?php echo htmlspecialchars($a->email);?>?')">🗑 Delete</a>
</div>
</div>
<?php endforeach; endif; ?>
</div>

<div id="tab-create" class="tab-content">
<div class="ecard" style="max-width:450px">
<h3>➕ Create Email Account</h3>
<form method="POST" action="/user/email/create" onsubmit="return checkEmail(this)">
<div style="margin-bottom:8px"><label style="font-size:11px;color:#64748b">Email</label>
<div style="display:flex"><input name="email" id="newEmail" required placeholder="you" style="flex:1;border-radius:6px 0 0 6px">
<span style="padding:7px 10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-left:none;border-radius:0 6px 6px 0;color:#64748b;font-size:12px">@<?php echo htmlspecialchars($domain); ?></span></div>
<small id="emailCheck" style="color:#64748b;font-size:10px">Checking...</small></div>
<div style="margin-bottom:8px"><label style="font-size:11px;color:#64748b">Password</label>
<div style="display:flex;gap:6px"><input type="text" name="password" id="emailPw" required minlength="6" style="flex:1">
<button type="button" class="btn btn-sm" style="background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);padding:4px 8px;border-radius:4px;cursor:pointer;font-size:11px" onclick="document.getElementById('emailPw').value=Math.random().toString(36).slice(2,10)+'A1!'">Generate</button></div></div>
<div style="margin-bottom:8px"><label style="font-size:11px;color:#64748b">Quota</label>
<select name="quota"><option value="100">100 MB</option><option value="250">250 MB</option><option value="500">500 MB</option><option value="1000" selected>1 GB</option><option value="5000">5 GB</option></select></div>
<button type="submit" class="btn btn-sm" style="background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);padding:8px;border-radius:6px;cursor:pointer;width:100%;font-size:12px">➕ Create Email Account</button>
</form></div>
</div>

<!-- Mobile Setup Modal -->
<div id="mobileModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.8);z-index:9999;align-items:center;justify-content:center">
<div class="ecard" style="max-width:450px;width:92%;padding:24px">
<h3 id="mobileTitle" style="margin-bottom:12px">📱 Mobile Setup</h3>
<div id="mobileContent"></div>
<button class="btn btn-sm" style="margin-top:10px;background:rgba(255,255,255,.06);color:#ccc;border:1px solid rgba(255,255,255,.1);padding:6px 14px;border-radius:6px;cursor:pointer" onclick="document.getElementById('mobileModal').style.display='none'">Close</button>
</div></div>

<script>
function showTab(name, el) {
    document.querySelectorAll('.tab').forEach(function(t){t.classList.remove('active')});
    document.querySelectorAll('.tab-content').forEach(function(t){t.classList.remove('active')});
    if (el) el.classList.add('active');
    document.getElementById('tab-'+name).classList.add('active');
}

function checkEmail(form) {
    var val = form.querySelector('[name=email]').value;
    <?php if (!empty($accounts)): ?>
    var exists = [<?php foreach($accounts as $a): $local=explode('@',$a->email)[0]; echo "'$local',"; endforeach; ?>];
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
        x.onload = function(){location.reload()};
        x.send('password=' + encodeURIComponent(pw));
    }
    return false;
}

function showMobile(email) {
    var domain = '<?php echo htmlspecialchars($domain); ?>';
    document.getElementById('mobileTitle').textContent = '📱 Setup ' + email;
    document.getElementById('mobileContent').innerHTML =
        '<div style="margin-bottom:8px"><strong style="font-size:12px">📧 IMAP</strong>' +
        '<div style="background:rgba(0,0,0,.3);border:1px solid rgba(0,191,255,.1);border-radius:6px;padding:8px;font-family:monospace;font-size:11px;color:#4ade80;margin-top:4px">' +
        'Server: ' + domain + '<br>Port: 993 (SSL)<br>Username: ' + email + '<br>Password: (your password)</div></div>' +
        '<div style="margin-bottom:8px"><strong style="font-size:12px">📤 SMTP</strong>' +
        '<div style="background:rgba(0,0,0,.3);border:1px solid rgba(0,191,255,.1);border-radius:6px;padding:8px;font-family:monospace;font-size:11px;color:#38bdf8;margin-top:4px">' +
        'Server: ' + domain + '<br>Port: 587 (STARTTLS)<br>Auth: Required<br>Username: ' + email + '</div></div>' +
        '<div style="margin-bottom:8px"><strong style="font-size:12px">📩 POP3</strong>' +
        '<div style="background:rgba(0,0,0,.3);border:1px solid rgba(0,191,255,.1);border-radius:6px;padding:8px;font-family:monospace;font-size:11px;color:#a78bfa;margin-top:4px">' +
        'Server: ' + domain + '<br>Port: 995 (SSL)<br>Username: ' + email + '</div></div>';
    document.getElementById('mobileModal').style.display = 'flex';
}

document.getElementById('mobileModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
