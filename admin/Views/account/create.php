<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
<div>
<h2 style="margin:0"><i class="bi bi-person-plus"></i> Create Account</h2>
<p style="color:#64748b;margin:4px 0 0">Create a new hosting account. All fields marked * are required.</p>
</div>
</div>

<form method="POST" action="/admin/account/store">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">

<!-- Left Column -->
<div class="card">
<h4 style="color:var(--accent);margin-bottom:16px"><i class="bi bi-person"></i> Account Details</h4>
<div class="form-group"><label>Username *</label>
<input name="username" required placeholder="e.g. johndoe" style="width:100%">
<small style="color:#64748b">Used for FTP, SSH, and system user. Lowercase only.</small>
</div>
<div class="form-group"><label>Email *</label>
<input type="email" name="email" required placeholder="user@example.com" style="width:100%">
</div>
<div class="form-group"><label>Password *</label>
<div style="display:flex;gap:6px">
<input type="password" name="password" required minlength="8" id="pw" style="flex:1" placeholder="Min 8 characters">
<button type="button" class="btn btn-sm secondary" onclick="var p=Math.random().toString(36).slice(2,10)+Math.random().toString(36).toUpperCase().slice(2,4);document.getElementById('pw').value=p" style="white-space:nowrap">Generate</button>
</div>
</div>
<div class="form-group"><label>First Name</label><input name="first_name" style="width:100%"></div>
<div class="form-group"><label>Last Name</label><input name="last_name" style="width:100%"></div>
</div>

<!-- Right Column -->
<div class="card">
<h4 style="color:var(--accent);margin-bottom:16px"><i class="bi bi-globe"></i> Domain & Package</h4>
<div class="form-group"><label>Domain *</label>
<input name="domain" required placeholder="example.com" style="width:100%">
<small style="color:#64748b">Primary domain for this account.</small>
</div>
<div class="form-group"><label>Package</label>
<select name="package_id" style="width:100%" onchange="updatePkgDetails(this)">
<option value="">-- No Package --</option>
<option value="custom">-- Manual Custom --</option>
<?php if (isset($packages)): foreach ($packages as $p): ?>
<option value="<?php echo $p->id; ?>" data-disk="<?php echo $p->disk_space ?? 0; ?>" data-bw="<?php echo $p->bandwidth ?? 0; ?>" data-email="<?php echo $p->email_accounts ?? 0; ?>" data-db="<?php echo $p->databases ?? 0; ?>" data-price="<?php echo $p->monthly_price ?? 0; ?>">
<?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?> ($<?php echo number_format($p->monthly_price ?? 0, 2); ?>/mo)
</option>
<?php endforeach; endif; ?>
</select>
<div id="pkgDetails" style="display:none;margin-top:8px"></div>
</div>
<div id="customPkgFields" style="display:none">
<div style="margin-top:10px;padding:12px;background:rgba(0,140,255,.04);border-radius:8px;border:1px solid rgba(0,140,255,.1)">
<h5 style="margin:0 0 8px;font-size:13px;color:var(--accent)">Resource Limits</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
<div class="form-group" style="margin:0"><label style="font-size:11px">Disk Space (GB)</label><input name="custom_disk" type="number" value="10" style="width:100%;padding:5px 8px;font-size:12px"></div>
<div class="form-group" style="margin:0"><label style="font-size:11px">Bandwidth (GB)</label><input name="custom_bw" type="number" value="100" style="width:100%;padding:5px 8px;font-size:12px"></div>
<div class="form-group" style="margin:0"><label style="font-size:11px">Max Email Accounts</label><input name="custom_email" type="number" value="-1" style="width:100%;padding:5px 8px;font-size:12px"></div>
<div class="form-group" style="margin:0"><label style="font-size:11px">Max Databases</label><input name="custom_dbs" type="number" value="-1" style="width:100%;padding:5px 8px;font-size:12px"></div>
<div class="form-group" style="margin:0"><label style="font-size:11px">Price ($/mo)</label><input name="custom_price" type="number" step="0.01" value="0" style="width:100%;padding:5px 8px;font-size:12px"></div>
</div>
<h5 style="margin:12px 0 8px;font-size:13px;color:var(--accent)">Features</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;font-size:12px">
<label><input type="checkbox" name="custom_features[]" value="cron" checked> Cron Jobs</label>
<label><input type="checkbox" name="custom_features[]" value="ssh"> SSH Access</label>
<label><input type="checkbox" name="custom_features[]" value="ssl" checked> SSL</label>
<label><input type="checkbox" name="custom_features[]" value="git" checked> Git</label>
<label><input type="checkbox" name="custom_features[]" value="nodejs"> Node.js</label>
<label><input type="checkbox" name="custom_features[]" value="python"> Python</label>
<label><input type="checkbox" name="custom_features[]" value="ruby"> Ruby</label>
<label><input type="checkbox" name="custom_features[]" value="terminal"> Terminal</label>
<label><input type="checkbox" name="custom_features[]" value="backups" checked> Backups</label>
<label><input type="checkbox" name="custom_features[]" value="installer" checked> One Click Installer</label>
<label><input type="checkbox" name="custom_features[]" value="builder"> Website Builder</label>
<label><input type="checkbox" name="custom_features[]" value="ai_builder"> AI Website Builder</label>
<label><input type="checkbox" name="custom_features[]" value="ai_assistant"> AI Assistant</label>
<label><input type="checkbox" name="custom_features[]" value="marketplace"> Plugin Marketplace</label>
<label><input type="checkbox" name="custom_features[]" value="api"> API Access</label>
<label><input type="checkbox" name="custom_features[]" value="webhooks"> Webhooks</label>
<label><input type="checkbox" name="custom_features[]" value="chat"> Chatbox</label>
<label><input type="checkbox" name="custom_features[]" value="dj_panel"> DJ Panel</label>
<label><input type="checkbox" name="custom_features[]" value="streaming"> Streaming</label>
<label><input type="checkbox" name="custom_features[]" value="game"> Game Servers</label>
<label><input type="checkbox" name="custom_features[]" value="vps"> VPS</label>
</div>
</div>
</div>
<div class="form-group"><label>PHP Version</label>
<select name="php_version" style="width:100%">
<option value="">Server Default (8.2)</option>
<?php foreach (['5.6','7.0','7.1','7.2','7.3','7.4','8.0','8.1','8.2','8.3','8.4','8.5'] as $v): ?>
<option value="<?php echo $v; ?>"<?php if ($v === '8.2') echo ' selected'; ?>><?php echo $v; ?></option>
<?php endforeach; ?>
</select>
</div>
</div>

<!-- IP Selection -->
<div class="card">
<h4 style="color:var(--accent);margin-bottom:16px"><i class="bi bi-ethernet"></i> IP Address</h4>
<?php
$availIps = [];
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT * FROM server_ips WHERE assigned_to IS NULL OR assigned_to = '' ORDER BY ip");
    if ($q) $availIps = $q->fetchAll(PDO::FETCH_OBJ);
} catch (\Exception $e) {}
?>
<div class="form-group">
<select name="ip" style="width:100%">
<option value="" selected>Auto-assign (shared IP)</option>
<?php if (!empty($availIps)): foreach ($availIps as $ip): ?>
<option value="<?php echo htmlspecialchars($ip->ip); ?>"><?php echo htmlspecialchars($ip->ip); ?> (<?php echo htmlspecialchars($ip->server ?? 'main'); ?>)</option>
<?php endforeach; endif; ?>
</select>
<small style="color:#64748b">Choose a specific IP or leave as auto-assign.</small>
</div>
</div>

<!-- Additional Features -->
<div class="card">
<h4 style="color:var(--accent);margin-bottom:16px"><i class="bi bi-gear"></i> Features</h4>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;cursor:pointer">
<input type="checkbox" name="features[]" value="ssh" checked> SSH Access
</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;cursor:pointer">
<input type="checkbox" name="features[]" value="ftp" checked> FTP Access
</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;cursor:pointer">
<input type="checkbox" name="features[]" value="email" checked> Email
</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;cursor:pointer">
<input type="checkbox" name="features[]" value="mysql" checked> MySQL
</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;cursor:pointer">
<input type="checkbox" name="features[]" value="ssl" checked> Free SSL
</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;cursor:pointer">
<input type="checkbox" name="features[]" value="dns" checked> DNS Zone
</label>
</div>
</div>

</div>

<div style="display:flex;gap:12px;margin-top:20px;justify-content:flex-start">
<button type="submit" class="btn btn-lg primary"><i class="bi bi-check-circle"></i> Create Account</button>
<a href="/admin/account" class="btn btn-lg secondary"><i class="bi bi-x-circle"></i> Cancel</a>
</div>
</form>

<script>
function updatePkgDetails(sel) {
    var opt = sel.options[sel.selectedIndex];
    var div = document.getElementById('pkgDetails');
    var customDiv = document.getElementById('customPkgFields');
    if (opt.value === 'custom') {
        div.style.display = 'none';
        customDiv.style.display = 'block';
        return;
    }
    customDiv.style.display = 'none';
    if (!opt.value || !opt.dataset) { div.style.display = 'none'; return; }
    var disk = opt.dataset.disk || 0;
    var bw = opt.dataset.bw || 0;
    var email = opt.dataset.email || 0;
    var db = opt.dataset.db || 0;
    var price = opt.dataset.price || 0;
    div.innerHTML = '<div style="background:rgba(0,140,255,.06);border-radius:8px;padding:10px;font-size:12px;color:#94a3b8">' +
        '<strong>' + opt.text + '</strong><br>' +
        '💾 ' + disk + ' GB Disk · 📶 ' + bw + ' GB Bandwidth · 📧 ' + email + ' Emails · 🗄 ' + db + ' Databases<br>' +
        '<span style="color:#0A84FF;font-weight:700">$' + parseFloat(price).toFixed(2) + '/month</span></div>';
    div.style.display = 'block';
}
</script>