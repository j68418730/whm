<style>
.cred-box{border:1px solid rgba(0,191,255,.15);border-radius:12px;padding:20px;margin-bottom:16px}
.cred-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.cred-row:last-child{border:none}
.cred-label{color:#64748b;font-size:13px}
.cred-value{font-weight:600;font-size:13px}
.login-link{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:500;margin:4px}
.feature-tag{display:inline-block;padding:4px 10px;border-radius:6px;font-size:11px;margin:2px;background:rgba(0,191,255,.08);border:1px solid rgba(0,191,255,.15)}
</style>

<div class="card" style="max-width:800px;margin:auto">
<div style="text-align:center;margin-bottom:24px">
<div style="font-size:48px;margin-bottom:8px">✅</div>
<h2 style="color:#4ade80;margin:0">Account Created Successfully</h2>
<p style="color:#64748b;margin:4px 0 0">Account <strong><?php echo htmlspecialchars($account->username); ?></strong> has been provisioned.</p>
</div>

<div class="cred-box">
<h3 style="color:var(--accent);margin-bottom:16px;font-size:15px">Account Credentials</h3>
<div class="cred-row"><span class="cred-label">Username</span><span class="cred-value"><?php echo htmlspecialchars($account->username); ?></span></div>
<div class="cred-row"><span class="cred-label">Password</span><span class="cred-value"><code style="user-select:all"><?php echo htmlspecialchars($plainPassword); ?></code> <button class="btn btn-sm secondary" style="padding:2px 8px;font-size:11px" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($plainPassword, ENT_QUOTES); ?>')">Copy</button></span></div>
<div class="cred-row"><span class="cred-label">Domain</span><span class="cred-value"><?php echo htmlspecialchars($account->domain); ?></span></div>
<div class="cred-row"><span class="cred-label">IP Address</span><span class="cred-value"><?php echo htmlspecialchars($account->ip ?? '45.61.59.55'); ?></span></div>
<div class="cred-row"><span class="cred-label">Package</span><span class="cred-value"><?php echo $package ? htmlspecialchars($package->name) : 'N/A'; ?></span></div>
<div class="cred-row"><span class="cred-label">Nameserver 1</span><span class="cred-value">ns1.planet-hosts.com</span></div>
<div class="cred-row"><span class="cred-label">Nameserver 2</span><span class="cred-value">ns2.planet-hosts.com</span></div>
</div>

<div class="cred-box">
<h3 style="color:var(--accent);margin-bottom:16px;font-size:15px">Login Links</h3>
<div style="display:flex;flex-wrap:wrap;gap:8px">
<a href="http://<?php echo htmlspecialchars($account->domain); ?>/" class="login-link" style="background:rgba(0,191,255,.1);color:#0A84FF;border:1px solid rgba(0,191,255,.2)" target="_blank">🌐 Website</a>
<a href="http://<?php echo htmlspecialchars($account->domain); ?>/cpanel" class="login-link" style="background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.2)" target="_blank">🔑 cPanel</a>
<a href="http://<?php echo htmlspecialchars($account->domain); ?>/webmail" class="login-link" style="background:rgba(250,204,21,.1);color:#facc15;border:1px solid rgba(250,204,21,.2)" target="_blank">📧 Webmail</a>
<a href="http://<?php echo htmlspecialchars($account->domain); ?>/phpmyadmin" class="login-link" style="background:rgba(168,85,247,.1);color:#a855f7;border:1px solid rgba(168,85,247,.2)" target="_blank">🗄️ phpMyAdmin</a>
<a href="ftp://<?php echo htmlspecialchars($account->domain); ?>/" class="login-link" style="background:rgba(251,146,60,.1);color:#fb923c;border:1px solid rgba(251,146,60,.2)" target="_blank">📁 FTP</a>
</div>
</div>

<?php if ($featureList): ?>
<div class="cred-box">
<h3 style="color:var(--accent);margin-bottom:16px;font-size:15px">Features & Limits</h3>
<div style="display:flex;flex-wrap:wrap;gap:4px">
<?php
$features = [
    'Email Accounts' => $featureList->email_accounts,
    'FTP Accounts' => $featureList->ftp_accounts,
    'Databases' => $featureList->databases,
    'Database Users' => $featureList->database_users,
    'Subdomains' => $featureList->subdomains,
    'Parked Domains' => $featureList->parked_domains,
    'Addon Domains' => $featureList->addon_domains,
];
foreach ($features as $label => $val) {
    $display = $val < 0 ? '∞' : $val;
    echo "<span class=\"feature-tag\">{$label}: {$display}</span>";
}
?>
</div>
<div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px;font-size:12px">
<?php $toggles = ['cron_jobs'=>'Cron Jobs','ssh_access'=>'SSH','ssl_allowed'=>'SSL','git_access'=>'Git','nodejs'=>'Node.js','python'=>'Python','ruby'=>'Ruby','terminal'=>'Terminal','backups'=>'Backups'];
foreach ($toggles as $key => $label): ?>
<?php if ($featureList->$key): ?>
<span style="color:#4ade80">✓ <?php echo $label; ?></span>
<?php else: ?>
<span style="color:#64748b">✗ <?php echo $label; ?></span>
<?php endif; ?>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:20px">
<a href="/admin/account" class="btn primary"><i class="bi bi-arrow-left"></i> Back to Accounts</a>
<button class="btn secondary" onclick="window.print()"><i class="bi bi-printer"></i> Print / Save PDF</button>
<button class="btn secondary" onclick="sendEmail()"><i class="bi bi-envelope"></i> Email Client</button>
<a href="/admin/account/create" class="btn secondary"><i class="bi bi-person-plus"></i> Create Another</a>
</div>
</div>

<script>
function sendEmail() {
    var email = prompt('Send account details to email:', '<?php echo htmlspecialchars($account->email); ?>');
    if (!email) return;
    var x = new XMLHttpRequest();
    x.open('POST', '/admin/account/email-summary/<?php echo $account->id; ?>', true);
    x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    x.onload = function() { alert(x.responseText); };
    x.send('email=' + encodeURIComponent(email));
}
</script>
