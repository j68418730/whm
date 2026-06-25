<style>
.summary-wrap{max-width:700px;margin:auto}
.summary-card{background:rgba(8,16,28,.8);border:1px solid rgba(0,191,255,.12);border-radius:14px;padding:28px;margin-bottom:16px}
.summary-title{color:#4ade80;font-size:18px;font-weight:600;margin-bottom:20px;display:flex;align-items:center;gap:8px}
.summary-grid{display:grid;gap:4px}
.summary-line{display:flex;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px;font-family:'Courier New',monospace}
.summary-line:last-child{border:none}
.summary-key{color:#64748b;min-width:180px;flex-shrink:0}
.summary-val{color:#e0e0e0;font-weight:500}
.login-links{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
.login-btn{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:500;font-family:inherit;cursor:pointer;border:1px solid rgba(255,255,255,.08)}
.action-bar{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
</style>

<div class="summary-wrap">
<div class="summary-card">
  <div class="summary-title">✅ Account Created Successfully</div>
  <div class="summary-grid">
    <div class="summary-line"><span class="summary-key">Domain:</span><span class="summary-val"><?php echo htmlspecialchars($account->domain); ?></span></div>
    <div class="summary-line"><span class="summary-key">Ip:</span><span class="summary-val"><?php echo htmlspecialchars($account->ip ?? ($_SERVER['SERVER_ADDR'] ?? 'planet-hosts.com')); ?></span></div>
    <div class="summary-line"><span class="summary-key">HasCgi:</span><span class="summary-val">y</span></div>
    <div class="summary-line"><span class="summary-key">UserName:</span><span class="summary-val"><?php echo htmlspecialchars($account->username); ?></span></div>
    <div class="summary-line"><span class="summary-key">PassWord:</span><span class="summary-val"><code style="user-select:all;background:rgba(0,0,0,.4);padding:2px 6px;border-radius:4px"><?php echo htmlspecialchars($plainPassword); ?></code> <button class="login-btn" style="background:rgba(0,191,255,.1);color:#0A84FF;padding:2px 8px;font-size:11px" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($plainPassword, ENT_QUOTES); ?>')">Copy</button></span></div>
    <div class="summary-line"><span class="summary-key">CpanelMod:</span><span class="summary-val">jupiter</span></div>
    <div class="summary-line"><span class="summary-key">HomeRoot:</span><span class="summary-val">/home</span></div>
    <div class="summary-line"><span class="summary-key">Quota:</span><span class="summary-val">unlimited</span></div>
<?php
$nsDisplay = $nameservers ?: [];
if (empty($nsDisplay)) {
    $serverIp = $_SERVER['SERVER_ADDR'] ?? 'planet-hosts.com';
    $nsDisplay = [ (object)['nameserver'=>'ns1.planet-hosts.com','ip_address'=>$serverIp], (object)['nameserver'=>'ns2.planet-hosts.com','ip_address'=>$serverIp] ];
}
$nsIdx = 1;
foreach ($nsDisplay as $ns): ?>
<div class="summary-line"><span class="summary-key">NameServer<?php echo $nsIdx++; ?>:</span><span class="summary-val"><?php echo htmlspecialchars($ns->nameserver ?? 'ns'.$nsIdx.'.planet-hosts.com'); ?> (<?php echo htmlspecialchars($ns->ip_address ?? $serverIp); ?>)</span></div>
<?php endforeach; ?>
    <div class="summary-line"><span class="summary-key">Contact Email:</span><span class="summary-val"><?php echo htmlspecialchars($account->email); ?></span></div>
    <div class="summary-line"><span class="summary-key">Package:</span><span class="summary-val"><?php echo $package ? htmlspecialchars($package->name) : 'default'; ?></span></div>
    <div class="summary-line"><span class="summary-key">Feature List:</span><span class="summary-val"><?php echo $featureList ? htmlspecialchars($featureList->name) : 'default'; ?></span></div>
    <div class="summary-line"><span class="summary-key">Account Enhancements:</span><span class="summary-val">None</span></div>
    <div class="summary-line"><span class="summary-key">Language:</span><span class="summary-val">en</span></div>
  </div>
</div>

<div class="summary-card">
  <div class="summary-title" style="color:#0A84FF">🔗 Login Links</div>
  <div class="login-links">
    <a href="http://<?php echo htmlspecialchars($account->domain); ?>/" class="login-btn" style="background:rgba(0,191,255,.08);color:#0A84FF" target="_blank">🌐 Website</a>
    <a href="http://<?php echo htmlspecialchars($account->domain); ?>:2083" class="login-btn" style="background:rgba(74,222,128,.08);color:#4ade80" target="_blank">🔑 cPanel (2083)</a>
    <a href="http://<?php echo htmlspecialchars($account->domain); ?>:2096" class="login-btn" style="background:rgba(250,204,21,.08);color:#facc15" target="_blank">📧 Webmail (2096)</a>
    <a href="http://<?php echo htmlspecialchars($account->domain); ?>/phpmyadmin" class="login-btn" style="background:rgba(168,85,247,.08);color:#a855f7" target="_blank">🗄️ phpMyAdmin</a>
    <a href="ftp://<?php echo htmlspecialchars($account->domain); ?>" class="login-btn" style="background:rgba(251,146,60,.08);color:#fb923c" target="_blank">📁 FTP</a>
  </div>
</div>

<div class="action-bar">
  <a href="/admin/account" class="login-btn" style="background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none">← Back to Accounts</a>
  <button class="login-btn" style="background:rgba(255,255,255,.06);color:#ccc" onclick="window.print()">🖨️ Print / Save PDF</button>
  <button class="login-btn" style="background:rgba(255,255,255,.06);color:#ccc" onclick="sendEmail()">📧 Email Client</button>
  <a href="/admin/account/create" class="login-btn" style="background:rgba(255,255,255,.06);color:#ccc">➕ Create Another</a>
</div>
</div>

<script>
function sendEmail() {
    var email = prompt('Send account details to:', '<?php echo htmlspecialchars($account->email); ?>');
    if (!email) return;
    var x = new XMLHttpRequest();
    x.open('POST', '/admin/account/email-summary/<?php echo $account->id; ?>', true);
    x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    x.onload = function() { alert(x.responseText); };
    x.send('email=' + encodeURIComponent(email));
}
</script>
