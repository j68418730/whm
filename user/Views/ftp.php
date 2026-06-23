<style>
.ftp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin-bottom:20px}
.ftp-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;text-align:center}
.ftp-stat .num{font-size:24px;font-weight:800;margin-bottom:2px}
.ftp-stat .lbl{font-size:11px;color:#64748b}
.ftp-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:18px;margin-bottom:16px}
.ftp-card h3{font-size:14px;font-weight:600;margin:0 0 12px;display:flex;align-items:center;gap:8px}
.ftp-card h3 span{font-size:12px;color:#64748b;font-weight:400}
.quick-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;margin-bottom:20px}
.quick-btn{display:flex;flex-direction:column;align-items:center;gap:4px;padding:12px;background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.06);border-radius:8px;text-decoration:none;color:#e0e0e0;font-size:11px;cursor:pointer;transition:.15s}
.quick-btn:hover{border-color:rgba(0,140,255,.3)}
.quick-btn .icon{font-size:20px}
select,input{padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;width:100%;box-sizing:border-box}
input:focus,select:focus{border-color:#0A84FF}
.btn{padding:6px 12px;border-radius:6px;font-size:11px;font-weight:500;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;border:1px solid transparent}
.btn-primary{background:rgba(0,140,255,.1);color:#0A84FF;border-color:rgba(0,140,255,.2)}
.btn-success{background:rgba(74,222,128,.1);color:#4ade80;border-color:rgba(74,222,128,.2)}
.btn-warning{background:rgba(250,204,21,.1);color:#facc15;border-color:rgba(250,204,21,.2)}
.btn-danger{background:rgba(248,113,113,.1);color:#f87171;border-color:rgba(248,113,113,.2)}
.btn-sm{padding:4px 8px;font-size:10px}
.tab-bar{display:flex;gap:0;border-bottom:1px solid rgba(255,255,255,.06);margin-bottom:16px}
.tab{padding:10px 16px;font-size:12px;cursor:pointer;color:#64748b;border-bottom:2px solid transparent;transition:.15s}
.tab:hover{color:#e0e0e0}
.tab.active{color:#0A84FF;border-bottom-color:#0A84FF}
.tab-content{display:none}
.tab-content.active{display:block}
.ftp-table{width:100%;border-collapse:collapse;font-size:12px}
.ftp-table th{text-align:left;padding:8px 10px;color:#64748b;font-size:10px;text-transform:uppercase;border-bottom:1px solid rgba(255,255,255,.06)}
.ftp-table td{padding:8px 10px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle}
.ftp-table tr:hover td{background:rgba(0,191,255,.02)}
.ftp-table code{background:rgba(0,0,0,.3);padding:1px 5px;border-radius:3px;font-size:11px}
.details-box{background:rgba(0,0,0,.3);border:1px solid rgba(0,191,255,.1);border-radius:8px;padding:14px;font-family:'Courier New',monospace;font-size:12px;line-height:1.8;color:#4ade80;user-select:all;margin-bottom:12px}
.copy-btn{position:absolute;right:8px;top:8px;padding:3px 8px;font-size:10px;border-radius:4px;cursor:pointer;background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2)}
</style>

<?php if (isset($_SESSION['success'])): ?><div class="alert alert-success" style="margin-bottom:12px"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error'])): ?><div class="alert alert-danger" style="margin-bottom:12px"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div><?php endif; ?>

<h2>📤 FTP Manager</h2>
<p style="color:#64748b;margin-bottom:16px">Manage FTP accounts, connections, and access for your hosting account.</p>

<?php
$total = count($ftpAccounts ?? []);
$active = count(array_filter($ftpAccounts ?? [], function($a) { return $a->is_active; }));
$quota = $package->ftp_accounts ?? -1;
$quotaDisplay = $quota < 0 ? '∞' : $quota;
$quotaUsed = $quota < 0 ? $total : $total . ' / ' . $quota;
$quotaPct = $quota > 0 ? min(100, round(($total / $quota) * 100)) : 0;
?>

<!-- Stats -->
<div class="ftp-grid">
<div class="ftp-stat"><div class="num" style="color:#0A84FF"><?php echo $total; ?></div><div class="lbl">Total FTP Accounts</div></div>
<div class="ftp-stat"><div class="num" style="color:#4ade80"><?php echo $active; ?></div><div class="lbl">Active Accounts</div></div>
<div class="ftp-stat"><div class="num" style="color:#38bdf8">0</div><div class="lbl">Active Connections</div></div>
<div class="ftp-stat"><div class="num" style="color:#facc15"><?php echo $quotaUsed; ?></div><div class="lbl">Accounts Used</div></div>
</div>

<!-- Quick Actions -->
<div class="quick-grid">
<button class="quick-btn" onclick="showTab('create')"><span class="icon">➕</span>Create Account</button>
<button class="quick-btn" onclick="showTab('accounts')"><span class="icon">👥</span>FTP Accounts</button>
<button class="quick-btn" onclick="showTab('connections')"><span class="icon">🔗</span>Connections</button>
<button class="quick-btn" onclick="showTab('details')"><span class="icon">ℹ️</span>Connection Info</button>
<button class="quick-btn" onclick="showTab('security')"><span class="icon">🔒</span>Security</button>
</div>

<!-- Tabs -->
<div class="tab-bar">
<div class="tab active" onclick="showTab('accounts',this)">📋 Accounts</div>
<div class="tab" onclick="showTab('create',this)">➕ Create</div>
<div class="tab" onclick="showTab('details',this)">🔗 Connection Info</div>
<div class="tab" onclick="showTab('security',this)">🔒 Security</div>
</div>

<!-- Tab: Accounts List -->
<div id="tab-accounts" class="tab-content active">
<div class="ftp-card">
<h3>FTP Accounts <span>(<?php echo $total; ?>)</span></h3>
<?php if (empty($ftpAccounts)): ?>
<p style="color:#64748b;font-size:13px;text-align:center;padding:20px">No FTP accounts created yet.</p>
<?php else: ?>
<table class="ftp-table">
<thead><tr><th>Username</th><th>Directory</th><th>Permissions</th><th>Quota</th><th>Status</th><th></th></tr></thead>
<tbody>
<?php foreach ($ftpAccounts as $a): ?>
<tr>
<td><code><?php echo htmlspecialchars($a->username); ?></code></td>
<td><code>/home/<?php echo htmlspecialchars($hosting->username ?? ''); ?>/<?php echo htmlspecialchars($a->directory); ?></code></td>
<td><?php echo htmlspecialchars($a->permissions ?? 'read_write'); ?></td>
<td><?php echo htmlspecialchars($a->quota ?? 'unlimited'); ?></td>
<td><span style="color:<?php echo $a->is_active ? '#4ade80' : '#f87171'; ?>">● <?php echo $a->is_active ? 'Active' : 'Suspended'; ?></span></td>
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
</div>

<!-- Tab: Create FTP Account -->
<div id="tab-create" class="tab-content">
<div class="ftp-card" style="max-width:450px">
<h3>➕ Create FTP Account</h3>
<form method="POST" action="/user/ftp/create">
<div style="margin-bottom:10px"><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Username</label>
<input name="username" placeholder="e.g. uploader" required></div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Password</label>
<div style="display:flex;gap:6px"><input type="text" name="password" id="ftpPw" required minlength="6" style="flex:1">
<button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('ftpPw').value=Math.random().toString(36).slice(2,10)+'A1!'">Generate</button></div></div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Home Directory</label>
<select name="directory">
<option value="public_html">/public_html</option>
<option value="public_html/uploads">/public_html/uploads</option>
<option value="public_html/images">/public_html/images</option>
<option value="public_html/blog">/public_html/blog</option>
<option value="/">/ (Full Access)</option>
</select>
</div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Permissions</label>
<select name="permissions"><option value="read_write">Read + Write</option><option value="read_only">Read Only</option><option value="read_write_delete">Read + Write + Delete</option></select></div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Disk Quota</label>
<select name="quota"><option value="unlimited">Unlimited</option><option value="100">100 MB</option><option value="500">500 MB</option><option value="1000">1 GB</option><option value="5000">5 GB</option></select></div>
<div style="margin-bottom:10px"><label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" name="ssl_enabled" value="1" checked> Enable FTPS (FTP over SSL)</label></div>
<button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:10px">➕ Create FTP Account</button>
</form>
</div>
</div>

<!-- Tab: Connection Info -->
<div id="tab-details" class="tab-content">
<div class="ftp-card" style="max-width:500px">
<h3>🔗 Connection Details</h3>
<h4 style="font-size:12px;color:#64748b;margin:0 0 8px">Standard FTP</h4>
<div class="details-box" id="ftpDetails">Host: <?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost'); ?>
Port: 21
Protocol: FTP
Username: <?php echo htmlspecialchars($hosting->username ?? ''); ?>
Password: (your account password)
Directory: /home/<?php echo htmlspecialchars($hosting->username ?? ''); ?>/</div>
<button class="btn btn-sm btn-primary" style="margin-bottom:12px" onclick="copyDetails()">📋 Copy Connection Details</button>

<h4 style="font-size:12px;color:#64748b;margin:12px 0 8px">SFTP (Secure)</h4>
<div class="details-box" style="color:#38bdf8">Host: <?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost'); ?>
Port: 22
Protocol: SFTP
Username: <?php echo htmlspecialchars($hosting->username ?? ''); ?>
Password: (your account password)</div>

<h4 style="font-size:12px;color:#64748b;margin:12px 0 8px">FileZilla Configuration</h4>
<div class="details-box" style="color:#a78bfa">Host: <?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost'); ?>
Port: 21
Protocol: FTP - File Transfer Protocol
Encryption: Use explicit FTP over TLS if available
Logon Type: Normal
User: <?php echo htmlspecialchars($hosting->username ?? ''); ?>
Password: (your account password)</div>
</div>
</div>

<!-- Tab: Security -->
<div id="tab-security" class="tab-content">
<div class="ftp-card" style="max-width:450px">
<h3>🔒 FTP Security</h3>
<div style="margin-bottom:12px">
<label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" checked disabled> FTP over SSL (FTPS) — Enabled</label>
<label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;margin-top:6px"><input type="checkbox" disabled> Disable Plain FTP (unencrypted)</label>
</div>
<div style="margin-bottom:12px">
<label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">IP Address Restrictions</label>
<textarea rows="3" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none" placeholder="Enter allowed IPs (one per line)"></textarea>
</div>
<button class="btn btn-primary" onclick="alert('Security settings saved (simulated)')">Save Security Settings</button>
</div>
</div>

<script>
function showTab(name, el) {
    document.querySelectorAll('.tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('.tab-content').forEach(function(t) { t.classList.remove('active'); });
    if (el) el.classList.add('active');
    else document.querySelector('.tab[onclick*="'+name+'"]')?.classList.add('active');
    document.getElementById('tab-'+name).classList.add('active');
}

function copyDetails() {
    var txt = document.getElementById('ftpDetails').textContent;
    navigator.clipboard.writeText(txt);
    alert('Connection details copied!');
}

function promptPw(id) {
    var pw = prompt('Enter new password:');
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
