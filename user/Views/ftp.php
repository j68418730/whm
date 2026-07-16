<style>
.ftp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:16px}
.ftp-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;text-align:center}
.ftp-stat .num{font-size:24px;font-weight:800;margin-bottom:2px}
.ftp-stat .lbl{font-size:11px;color:#64748b}
.ftp-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:18px;margin-bottom:14px}
.ftp-card h3{font-size:14px;font-weight:600;margin:0 0 10px}
input,select{padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;width:100%;box-sizing:border-box}
input:focus,select:focus{border-color:#0A84FF}
.btn{padding:6px 14px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;border:none}
.btn-primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn-success{background:rgba(74,222,128,.15);color:#4ade80}
.btn-warning{background:rgba(250,204,21,.12);color:#facc15}
.btn-danger{background:rgba(248,113,113,.12);color:#f87171}
.btn-sm{padding:4px 8px;font-size:10px}
.ftp-table{width:100%;border-collapse:collapse;font-size:12px}
.ftp-table th{text-align:left;padding:8px 10px;color:#64748b;font-size:10px;text-transform:uppercase;border-bottom:1px solid rgba(255,255,255,.06)}
.ftp-table td{padding:8px 10px;border-bottom:1px solid rgba(255,255,255,.04)}
.ftp-table tr:hover td{background:rgba(0,191,255,.02)}
.ftp-table code{background:rgba(0,0,0,.3);padding:1px 5px;border-radius:3px;font-size:11px}
.details-box{background:rgba(0,0,0,.3);border:1px solid rgba(0,191,255,.1);border-radius:8px;padding:14px;font-family:monospace;font-size:12px;line-height:1.8;color:#4ade80;margin-bottom:10px}
.pw-meter{height:4px;border-radius:2px;background:rgba(255,255,255,.06);margin-top:4px;overflow:hidden}
.pw-meter .fill{height:100%;border-radius:2px;transition:width .3s,background .3s;width:0%}
</style>

<?php if (isset($_SESSION['success'])): ?><div class="alert alert-success" style="margin-bottom:12px;padding:10px 14px;border-radius:8px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);color:#4ade80;font-size:12px"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error'])): ?><div class="alert alert-danger" style="margin-bottom:12px;padding:10px 14px;border-radius:8px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#ef4444;font-size:12px"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div><?php endif; ?>

<h2>📤 FTP Accounts</h2>
<p style="color:#64748b;margin-bottom:16px;font-size:12px">Create and manage FTP accounts with restricted folder access.</p>

<?php $host = $_SERVER['HTTP_HOST'] ?? 'localhost'; $uname = $hosting->username ?? ''; ?>

<div class="ftp-grid">
<div class="ftp-stat"><div class="num" style="color:#0A84FF"><?php echo count($ftpAccounts ?? []); ?></div><div class="lbl">FTP Accounts</div></div>
<div class="ftp-stat"><div class="num" style="color:#4ade80"><?php echo count(array_filter($ftpAccounts ?? [], fn($a) => $a->is_active)); ?></div><div class="lbl">Active</div></div>
<div class="ftp-stat"><div class="num" style="color:#facc15"><?php echo count(array_filter($ftpAccounts ?? [], fn($a) => !$a->is_active)); ?></div><div class="lbl">Suspended</div></div>
</div>

<div class="ftp-card">
<h3>➕ Create FTP Account</h3>
<form method="POST" action="/user/ftp/create" style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Username</label>
<input name="username" placeholder="e.g. designer" required value="<?php echo htmlspecialchars($_GET['edit_username'] ?? ''); ?>"></div>
<div><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Password</label>
<div style="display:flex;gap:4px"><input type="text" name="password" id="ftpPw" required minlength="6" style="flex:1" oninput="checkPw(this.value)">
<button type="button" class="btn btn-sm btn-primary" onclick="genPw()" style="white-space:nowrap">Generate</button></div>
<div id="pwMeter" class="pw-meter" style="grid-column:1/-1;margin-top:-2px"><div class="fill" id="pwFill"></div></div>
<div id="pwText" style="font-size:10px;color:#64748b;margin-top:-4px"></div></div>
<div><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Home Directory (jailed)</label>
<select name="directory">
<?php foreach ($ftpDirs as $d): ?>
<option value="<?php echo htmlspecialchars($d['path']); ?>">/<?php echo htmlspecialchars($d['path']); ?> (<?php echo htmlspecialchars($d['path']); ?>)</option>
<?php foreach ($d['children'] as $c): ?>
<option value="<?php echo htmlspecialchars($c); ?>">/<?php echo htmlspecialchars($c); ?></option>
<?php endforeach; ?>
<?php endforeach; ?>
<option value="/">/ (Full Account Access)</option>
</select></div>
<div><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Disk Quota</label>
<select name="quota">
<option value="unlimited">Unlimited</option>
<option value="100">100 MB</option>
<option value="500">500 MB</option>
<option value="1000">1 GB</option>
<option value="2000">2 GB</option>
<option value="5000">5 GB</option>
<option value="10000">10 GB</option>
</select></div>
<div><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Permissions</label>
<select name="permissions">
<option value="read_write">Read + Write</option>
<option value="read_only">Read Only</option>
<option value="read_write_delete">Read + Write + Delete</option>
</select></div>
<div style="display:flex;align-items:end;gap:8px">
<label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" name="ssl_enabled" value="1" checked> FTPS (SSL)</label>
<button type="submit" class="btn btn-primary" style="flex:1">➕ Create FTP User</button>
</div>
</form>
</div>

<div class="ftp-card">
<h3>📋 FTP Accounts <span style="font-size:11px;color:#64748b;font-weight:400">(<?php echo count($ftpAccounts ?? []); ?>)</span></h3>
<?php if (empty($ftpAccounts)): ?>
<p style="color:#64748b;font-size:12px;text-align:center;padding:20px">No FTP accounts. Create one above.</p>
<?php else: ?>
<table class="ftp-table">
<thead><tr><th>Username</th><th>Directory</th><th>Quota</th><th>Status</th><th>Created</th><th></th></tr></thead>
<tbody>
<?php foreach ($ftpAccounts as $a): ?>
<tr>
<td><code><?php echo htmlspecialchars($a->username); ?></code></td>
<td><code><?php echo htmlspecialchars($a->directory); ?></code></td>
<td><?php echo htmlspecialchars($a->quota ?? '∞'); ?></td>
<td><span style="color:<?php echo $a->is_active ? '#4ade80' : '#f87171'; ?>">● <?php echo $a->is_active ? 'Active' : 'Suspended'; ?></span></td>
<td style="font-size:11px;color:#64748b"><?php echo htmlspecialchars($a->created_at ?? '-'); ?></td>
<td style="display:flex;gap:3px">
<a href="/user/ftp/password/<?php echo $a->id; ?>" class="btn btn-sm btn-warning" onclick="return promptPw(<?php echo $a->id; ?>)">🔑</a>
<a href="/user/ftp/toggle/<?php echo $a->id; ?>" class="btn btn-sm <?php echo $a->is_active ? 'btn-warning' : 'btn-success'; ?>"><?php echo $a->is_active ? '⏸' : '▶'; ?></a>
<a href="/user/ftp/delete/<?php echo $a->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete <?php echo htmlspecialchars($a->username); ?>?')">🗑</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>

<div class="ftp-card">
<h3>🔗 Connection Details</h3>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
<div class="details-box" style="font-size:11px"><strong>FTP</strong><br>Host: <?php echo $host; ?><br>Port: 21<br>Encryption: TLS/SSL optional</div>
<div class="details-box" style="font-size:11px;color:#38bdf8"><strong>FTPS</strong><br>Host: <?php echo $host; ?><br>Port: 990<br>Encryption: Required</div>
<div class="details-box" style="font-size:11px;color:#a78bfa"><strong>SFTP</strong><br>Host: <?php echo $host; ?><br>Port: 22<br>Encryption: SSH Key or Password</div>
</div>
<p style="color:#64748b;font-size:11px;margin-top:8px">FTP users log in with their assigned username and password. They are jailed to their assigned directory.</p>
</div>

<script>
function checkPw(pw) {
    var s = 0;
    if (pw.length >= 6) s += 25;
    if (pw.length >= 10) s += 15;
    if (/[a-z]/.test(pw)) s += 15;
    if (/[A-Z]/.test(pw)) s += 15;
    if (/[0-9]/.test(pw)) s += 15;
    if (/[^a-zA-Z0-9]/.test(pw)) s += 15;
    if (pw.length >= 14) s += 10;
    s = Math.min(s, 100);
    var f = document.getElementById('pwFill');
    var t = document.getElementById('pwText');
    f.style.width = s + '%';
    if (s < 30) { f.style.background = '#ef4444'; t.textContent = 'Weak'; t.style.color = '#ef4444'; }
    else if (s < 60) { f.style.background = '#facc15'; t.textContent = 'Fair'; t.style.color = '#facc15'; }
    else if (s < 80) { f.style.background = '#0A84FF'; t.textContent = 'Good'; t.style.color = '#0A84FF'; }
    else { f.style.background = '#4ade80'; t.textContent = 'Strong'; t.style.color = '#4ade80'; }
}
function genPw() {
    var c = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    var p = '';
    for (var i = 0; i < 14; i++) p += c.charAt(Math.floor(Math.random() * c.length));
    document.getElementById('ftpPw').value = p;
    checkPw(p);
}
function promptPw(id) {
    var pw = prompt('Enter new password (6+ chars):');
    if (pw && pw.length >= 6) {
        var x = new XMLHttpRequest();
        x.open('POST', '/user/ftp/password/' + id, true);
        x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        x.onload = function() { location.reload(); };
        x.send('password=' + encodeURIComponent(pw));
    }
    return false;
}
</script>
