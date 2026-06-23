<style>
.email-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:12px}
.ecard{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;transition:.15s}
.ecard:hover{border-color:rgba(0,140,255,.2)}
.ecard .top{display:flex;justify-content:space-between;align-items:start;margin-bottom:8px}
.ecard .addr{font-size:14px;font-weight:600}
.ecard .quota{font-size:11px;color:#64748b;margin-bottom:6px}
.ecard .bar{height:4px;border-radius:2px;background:rgba(255,255,255,.05);margin-bottom:10px}
.ecard .bar .fill{height:100%;border-radius:2px;background:#0A84FF;transition:width .5s}
.ecard .actions{display:flex;gap:4px;flex-wrap:wrap}
.ecard .actions a{padding:5px 10px;border-radius:5px;font-size:10px;text-decoration:none;border:1px solid rgba(255,255,255,.08);transition:.1s}
.ecard .actions a:hover{filter:brightness(1.2)}
input,select{width:100%;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;box-sizing:border-box;margin-bottom:6px}
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
<p style="color:#64748b;margin-bottom:14px">Manage your email accounts.</p>
<?php $domain = $hosting->domain ?? ($_SERVER['HTTP_HOST'] ?? 'planet-hosts.com'); ?>
<div class="tab-bar">
<div class="tab active" onclick="showTab('accounts',this)">📋 Mailboxes (<?php echo count($accounts ?? []);?>)</div>
<div class="tab" onclick="showTab('create',this)">➕ Create</div>
</div>
<div id="tab-accounts" class="tab-content active">
<?php if (empty($accounts)): ?><div class="ecard" style="text-align:center;padding:30px"><p style="color:#64748b">No email accounts.</p></div>
<?php else: foreach ($accounts as $a): $quota = (int)($a->quota_mb ?? 1000); $used = 0; $pct = $quota > 0 ? min(100, round(($used / $quota) * 100)) : 0; ?>
<div class="ecard" style="border-left:3px solid #0A84FF">
<div class="top"><div><div class="addr"><?php echo htmlspecialchars($a->email); ?></div>
<div class="quota"><?php echo $used; ?> MB / <?php echo $quota; ?> MB used</div></div>
<span style="font-size:11px;color:#4ade80">● Active</span></div>
<div class="bar"><div class="fill" style="width:<?php echo $pct; ?>%"></div></div>
<div class="actions">
<a href="/sso_webmail.php?email=<?php echo urlencode($a->email); ?>" target="_blank" style="background:linear-gradient(135deg,#0A84FF,#3bb8ff);color:#fff;border:none">📨 Open Webmail</a>
<a href="javascript:void(0)" onclick="showConnections('<?php echo htmlspecialchars($a->email); ?>')" style="background:rgba(0,140,255,.1);color:#0A84FF;border-color:rgba(0,140,255,.2)">📱 Connect</a>
<a href="/user/email/password/<?php echo $a->id;?>" style="background:rgba(250,204,21,.1);color:#facc15;border-color:rgba(250,204,21,.2)" onclick="return promptEmailPw(<?php echo $a->id;?>)">🔑 Password</a>
<a href="/user/email/delete/<?php echo $a->id;?>" style="background:rgba(248,113,113,.1);color:#f87171;border-color:rgba(248,113,113,.2)" onclick="return confirm('Delete <?php echo htmlspecialchars($a->email);?>?')">🗑 Delete</a>
</div></div>
<?php endforeach; endif; ?>
</div>
<div id="tab-create" class="tab-content">
<div class="ecard" style="max-width:420px"><h3 style="margin:0 0 8px;font-size:14px">➕ Create Email Account</h3>
<form method="POST" action="/user/email/create" onsubmit="return checkEmail(this)">
<div style="display:flex"><input name="email" id="newEmail" required placeholder="you" style="border-radius:6px 0 0 6px;margin:0">
<span style="padding:7px 10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-left:none;border-radius:0 6px 6px 0;color:#64748b;font-size:12px">@<?php echo htmlspecialchars($domain); ?></span></div>
<small id="emailCheck" style="color:#64748b;font-size:10px">Checking...</small>
<div style="display:flex;gap:6px"><input type="text" name="password" id="emailPw" required minlength="6" placeholder="Password" style="margin:0;flex:1">
<button type="button" style="background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);padding:4px 8px;border-radius:4px;cursor:pointer;font-size:11px;white-space:nowrap" onclick="document.getElementById('emailPw').value=Math.random().toString(36).slice(2,10)+'A1!'">Generate</button></div>
<select name="quota"><option value="100">100 MB</option><option value="250">250 MB</option><option value="500">500 MB</option><option value="1000" selected>1 GB</option><option value="5000">5 GB</option></select>
<button type="submit" style="background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);padding:8px;border-radius:6px;cursor:pointer;width:100%;font-size:12px">➕ Create Account</button>
</form></div></div>
<!-- Connect Devices Modal -->
<div id="connModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.85);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px)">
<div class="ecard" style="max-width:420px;width:92%;padding:24px">
<h3 id="connTitle" style="margin-bottom:10px">📱 Connect Devices</h3>
<div id="connContent"></div>
<button style="margin-top:10px;background:rgba(255,255,255,.06);color:#ccc;border:1px solid rgba(255,255,255,.1);padding:6px 14px;border-radius:6px;cursor:pointer;width:100%" onclick="document.getElementById('connModal').style.display='none'">Close</button>
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
function showConnections(email) {
    var domain = '<?php echo htmlspecialchars($domain); ?>';
    document.getElementById('connTitle').textContent = '📱 ' + email;
    document.getElementById('connContent').innerHTML =
        '<div style="margin-bottom:6px;padding:8px;background:rgba(0,0,0,.3);border-radius:6px">' +
        '<div style="font-size:11px;font-weight:600;color:#0A84FF;margin-bottom:4px">📧 IMAP</div>' +
        '<div style="font-size:10px;font-family:monospace;color:#4ade80">Server: <span id="imapSrv">' + domain + '</span><br>Port: 993 (SSL)<br>Username: ' + email + '</div>' +
        '<button class="btn btn-sm" style="margin-top:4px;background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);padding:2px 6px;border-radius:3px;cursor:pointer;font-size:9px" onclick="copyText(\'Server: ' + domain + '\\nPort: 993 (SSL)\\nUsername: ' + email + '\\nPassword: (your password)\')">📋 Copy</button></div>' +
        '<div style="margin-bottom:6px;padding:8px;background:rgba(0,0,0,.3);border-radius:6px">' +
        '<div style="font-size:11px;font-weight:600;color:#38bdf8;margin-bottom:4px">📤 SMTP</div>' +
        '<div style="font-size:10px;font-family:monospace;color:#38bdf8">Server: ' + domain + '<br>Port: 587 (STARTTLS)<br>Username: ' + email + '</div>' +
        '<button class="btn btn-sm" style="margin-top:4px;background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);padding:2px 6px;border-radius:3px;cursor:pointer;font-size:9px" onclick="copyText(\'Server: ' + domain + '\\nPort: 587 (STARTTLS)\\nUsername: ' + email + '\\nPassword: (your password)\')">📋 Copy</button></div>' +
        '<div style="padding:8px;background:rgba(0,0,0,.3);border-radius:6px">' +
        '<div style="font-size:11px;font-weight:600;color:#a78bfa;margin-bottom:4px">📩 POP3</div>' +
        '<div style="font-size:10px;font-family:monospace;color:#a78bfa">Server: ' + domain + '<br>Port: 995 (SSL)<br>Username: ' + email + '</div>' +
        '<button class="btn btn-sm" style="margin-top:4px;background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);padding:2px 6px;border-radius:3px;cursor:pointer;font-size:9px" onclick="copyText(\'Server: ' + domain + '\\nPort: 995 (SSL)\\nUsername: ' + email + '\\nPassword: (your password)\')">📋 Copy</button></div>';
    document.getElementById('connModal').style.display = 'flex';
}
function copyText(t) { navigator.clipboard.writeText(t); alert('Copied!'); }
document.getElementById('connModal')?.addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
</script>
