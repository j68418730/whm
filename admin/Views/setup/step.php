<?php
$step = $setup_step ?? 1;
$steps = $setup_steps ?? [];
$progress = $setup_progress ?? 0;
$stepCount = $setup_step_count ?? 21;
$title = $setup_title ?? 'Setup Wizard';
$sd = $setup_data ?? [];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo htmlspecialchars($title); ?> - Planet Hosts Setup</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: #060d18; color: #d8e7f7; min-height: 100vh; }
.bg-overlay { position: fixed; inset: 0; background: linear-gradient(rgba(2,8,23,.92), rgba(2,8,23,.98)), url(/theme/assets/img/background.png); background-size: cover; z-index: -2; }
.grid-overlay { position: fixed; inset: 0; background-image: linear-gradient(rgba(0,191,255,.03) 1px, transparent 1px), linear-gradient(90deg, rgba(0,191,255,.03) 1px, transparent 1px); background-size: 60px 60px; z-index: -1; }
.setup-container { max-width: 860px; margin: 0 auto; padding: 30px 20px 60px; }
.setup-header { text-align: center; margin-bottom: 40px; }
.setup-header .logo { font-size: 28px; font-weight: 800; letter-spacing: 2px; }
.setup-header .logo span { color: #008cff; }
.setup-header p { color: #64748b; font-size: 14px; margin-top: 4px; }

.progress-bar-container { background: rgba(255,255,255,.06); border-radius: 20px; height: 8px; margin-bottom: 8px; overflow: hidden; }
.progress-bar-fill { height: 100%; background: linear-gradient(90deg, #008cff, #3bb8ff); border-radius: 20px; transition: width .5s ease; width: <?php echo $progress; ?>%; }
.progress-text { text-align: right; font-size: 12px; color: #64748b; margin-bottom: 30px; }
.progress-text span { color: #94a3b8; }

.step-indicator { display: flex; gap: 6px; justify-content: center; margin-bottom: 40px; flex-wrap: wrap; }
.step-dot { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; background: rgba(255,255,255,.06); color: #64748b; border: 2px solid transparent; transition: all .2s; }
.step-dot.active { background: rgba(0,140,255,.2); color: #008cff; border-color: #008cff; box-shadow: 0 0 15px rgba(0,140,255,.3); }
.step-dot.completed { background: #008cff; color: #fff; border-color: #008cff; }

.setup-card { background: rgba(8,16,28,.9); border: 1px solid rgba(0,191,255,.1); border-radius: 16px; padding: 36px; margin-bottom: 24px; backdrop-filter: blur(12px); }
.setup-card h2 { font-size: 22px; font-weight: 700; margin-bottom: 8px; color: #fff; }
.setup-card .subtitle { color: #64748b; font-size: 14px; margin-bottom: 24px; }

.form-group { margin-bottom: 18px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 6px; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,.08); background: rgba(0,0,0,.3); color: #e0e0e0; font-size: 14px; outline: none; transition: border .2s; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #008cff; box-shadow: 0 0 0 3px rgba(0,140,255,.15); }
.form-group .help-text { font-size: 11px; color: #475569; margin-top: 4px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

.btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border-radius: 8px; font-size: 14px; font-weight: 600; border: none; cursor: pointer; text-decoration: none; transition: all .2s; }
.btn-primary { background: linear-gradient(135deg, #008cff, #3bb8ff); color: #fff; box-shadow: 0 4px 15px rgba(0,140,255,.3); }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,140,255,.4); }
.btn-secondary { background: rgba(255,255,255,.08); color: #94a3b8; border: 1px solid rgba(255,255,255,.1); }
.btn-secondary:hover { background: rgba(255,255,255,.12); color: #d8e7f7; }
.btn-success { background: linear-gradient(135deg, #22c55e, #4ade80); color: #fff; box-shadow: 0 4px 15px rgba(34,197,94,.3); }
.btn-warning { background: linear-gradient(135deg, #f59e0b, #facc15); color: #000; }
.btn-danger { background: linear-gradient(135deg, #ef4444, #f87171); color: #fff; }
.btn:disabled { opacity: .5; cursor: not-allowed; }
.button-group { display: flex; gap: 12px; justify-content: space-between; margin-top: 28px; }

.check-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 8px; margin-bottom: 6px; background: rgba(255,255,255,.03); }
.check-pass { border-left: 3px solid #22c55e; }
.check-pass .check-status { color: #22c55e; }
.check-warning { border-left: 3px solid #f59e0b; }
.check-warning .check-status { color: #f59e0b; }
.check-fail { border-left: 3px solid #ef4444; }
.check-fail .check-status { color: #ef4444; }
.check-check { background: rgba(34,197,94,.1); border-left: 3px solid #22c55e; }
.check-check .check-status { color: #22c55e; }
.check-label { flex: 1; font-size: 13px; color: #cbd5e1; }
.check-value { font-size: 12px; color: #64748b; margin-right: 12px; }
.check-status { font-weight: 700; font-size: 12px; text-transform: uppercase; min-width: 60px; text-align: right; }

.alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
.alert-success { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.2); color: #4ade80; }
.alert-error { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.2); color: #f87171; }
.alert-info { background: rgba(0,140,255,.1); border: 1px solid rgba(0,140,255,.2); color: #38bdf8; }

.license-text { background: rgba(0,0,0,.3); border: 1px solid rgba(255,255,255,.06); border-radius: 8px; padding: 20px; max-height: 300px; overflow-y: auto; font-size: 12px; line-height: 1.7; color: #94a3b8; margin-bottom: 16px; }
.license-text h3 { color: #d8e7f7; margin-bottom: 8px; }

.toggle-group { display: flex; gap: 12px; flex-wrap: wrap; }
.toggle-item { display: flex; align-items: center; gap: 10px; padding: 10px 16px; background: rgba(255,255,255,.03); border-radius: 8px; cursor: pointer; transition: all .2s; border: 1px solid transparent; }
.toggle-item:hover { background: rgba(255,255,255,.06); }
.toggle-item.active { border-color: rgba(0,140,255,.3); background: rgba(0,140,255,.08); }
.toggle-item input[type="checkbox"] { width: 18px; height: 18px; accent-color: #008cff; }

.summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.summary-item { padding: 12px 16px; background: rgba(255,255,255,.03); border-radius: 8px; }
.summary-item .s-label { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; }
.summary-item .s-value { font-size: 14px; color: #e0e0e0; margin-top: 4px; word-break: break-all; }

.subdomain-list { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.subdomain-item { display: flex; align-items: center; gap: 8px; }
.subdomain-item input { flex: 1; }
.subdomain-item .sd-prefix { font-size: 13px; color: #008cff; font-weight: 600; min-width: 80px; }

@media (max-width: 640px) {
  .form-row, .form-row-3, .summary-grid, .subdomain-list { grid-template-columns: 1fr; }
  .setup-card { padding: 24px 16px; }
}

.completion-check { text-align: center; padding: 40px 0; }
.completion-check .check-icon { width: 80px; height: 80px; border-radius: 50%; background: rgba(34,197,94,.15); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 36px; color: #4ade80; }
.completion-check h2 { font-size: 28px; margin-bottom: 8px; }
.completion-check p { color: #64748b; font-size: 15px; max-width: 500px; margin: 0 auto 24px; line-height: 1.6; }

.file-upload { border: 2px dashed rgba(255,255,255,.1); border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all .2s; }
.file-upload:hover { border-color: rgba(0,140,255,.3); background: rgba(0,140,255,.03); }
.file-upload input[type="file"] { display: none; }
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="grid-overlay"></div>

<div class="setup-container">
  <div class="setup-header">
    <div class="logo">PLANET <span>HOSTS</span></div>
    <p>Setup Wizard</p>
  </div>

  <div class="progress-bar-container">
    <div class="progress-bar-fill" style="width: <?php echo $progress; ?>%"></div>
  </div>
  <div class="progress-text">Step <span><?php echo $step; ?></span> of <span><?php echo $stepCount; ?></span></div>

  <?php if (isset($_SESSION['success_message'])): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error_message'])): ?>
  <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
  <?php endif; ?>

  <form method="POST" action="/setup/<?php echo $step; ?>/post" enctype="multipart/form-data">
  <?php
  // ─── STEP 1: LICENSE AGREEMENT ───
  if ($step === 1): ?>
  <div class="setup-card">
    <h2>License Agreement</h2>
    <p class="subtitle">Please read and accept the following agreements to continue.</p>

    <div class="license-text">
      <h3>Terms of Service</h3>
      <p>By using Planet Hosts software, you agree to the following terms and conditions. This software is provided "as is" without warranty of any kind. You may not redistribute, sublicense, or resell this software without explicit written permission from Planet Hosts.</p>
      <p>You are responsible for maintaining the security of your server and any data stored within. Planet Hosts reserves the right to suspend or terminate access for violations of these terms.</p>

      <h3 style="margin-top:16px">Privacy Policy</h3>
      <p>Planet Hosts collects minimal information necessary for license validation and system operation. We do not sell or share your personal data with third parties. Server statistics and usage data may be collected for improving our services.</p>

      <h3 style="margin-top:16px">Software License Agreement</h3>
      <p>This license grants you a non-exclusive, non-transferable right to use the Planet Hosts WHM Panel on a single server. License types include Trial, Monthly, Yearly, Lifetime, Internal Development, Reseller, and Enterprise.</p>
      <p>You may not:</p>
      <p>- Reverse engineer, decompile, or disassemble the software<br>
      - Remove or alter any proprietary notices<br>
      - Use the software for illegal purposes<br>
      - Transfer the license to another party without approval</p>
      <p>License violations may result in immediate termination and legal action.</p>
    </div>

    <label class="toggle-item" style="display:flex;border:1px solid rgba(255,255,255,.08)">
      <input type="checkbox" name="accept" value="yes" required>
      <span>I have read and agree to the Terms of Service, Privacy Policy, and Software License Agreement</span>
    </label>
  </div>
  <div class="button-group">
    <div></div>
    <button type="submit" class="btn btn-primary">Accept & Continue →</button>
  </div>

  <?php // ─── STEP 2: SYSTEM REQUIREMENTS ───
  elseif ($step === 2):
  $checks = $checks ?? [];
  ?>
  <div class="setup-card">
    <h2>System Requirements Check</h2>
    <p class="subtitle">Verifying that your server meets the minimum requirements.</p>

    <?php foreach ($checks as $ck => $cv): ?>
    <div class="check-item check-<?php echo $cv['status']; ?>">
      <span class="check-label"><?php echo htmlspecialchars($cv['label']); ?></span>
      <span class="check-value"><?php echo htmlspecialchars($cv['value']); ?></span>
      <span class="check-status">
        <?php if ($cv['status'] === 'pass'): ?>✓ PASS
        <?php elseif ($cv['status'] === 'warning'): ?>⚠ WARNING
        <?php else: ?>✗ FAIL<?php endif; ?>
      </span>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="button-group">
    <a href="/setup/1" class="btn btn-secondary">← Back</a>
    <div>
      <button type="submit" name="action" value="force" class="btn btn-warning">Force Continue</button>
      <button type="submit" class="btn btn-primary">Continue →</button>
    </div>
  </div>

  <?php // ─── STEP 3: LICENSE ACTIVATION ───
  elseif ($step === 3): ?>
  <div class="setup-card">
    <h2>License Activation</h2>
    <p class="subtitle">Activate your Planet Hosts license or start a free trial.</p>

    <div style="display:flex;gap:12px;margin-bottom:20px">
      <button type="button" class="btn btn-primary" onclick="showTab('online')" id="tab-online-btn">Online Activation</button>
      <button type="button" class="btn btn-secondary" onclick="showTab('trial')" id="tab-trial-btn">30-Day Trial</button>
      <button type="button" class="btn btn-secondary" onclick="showTab('upload')" id="tab-upload-btn">Upload License</button>
    </div>

    <div id="tab-online" class="tab-content">
      <div class="form-group">
        <label>License Key</label>
        <input type="text" name="license_key" placeholder="PH-XXXX-XXXX-XXXX-XXXX" value="<?php echo htmlspecialchars($license_key ?? ''); ?>">
        <div class="help-text">Enter your license key purchased from Planet-Hosts.com</div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Customer Email</label>
          <input type="email" name="customer_email" placeholder="admin@company.com">
        </div>
        <div class="form-group">
          <label>Company Name</label>
          <input type="text" name="company_name" placeholder="Planet Hosts">
        </div>
      </div>
      <div class="form-group">
        <label>Server IP Address</label>
        <input type="text" name="server_ip" value="<?php echo htmlspecialchars($server_ip ?? $_SERVER['SERVER_ADDR'] ?? ''); ?>" placeholder="15.204.114.226" required>
        <div class="help-text">Enter your server's public IP. This will be validated with Planet-Hosts.com licensing server.</div>
      </div>
      <input type="hidden" name="license_mode" value="online">
    </div>

    <div id="tab-trial" class="tab-content" style="display:none">
      <div class="completion-check" style="padding:20px">
        <div class="check-icon" style="width:60px;height:60px;font-size:24px">🎁</div>
        <h2 style="font-size:20px">Start Your 30-Day Free Trial</h2>
        <p>Get full access to all features with no commitment. No credit card required.</p>
        <div class="form-row" style="margin-top:12px">
          <div class="form-group">
            <label>Your Email</label>
            <input type="email" name="trial_email" placeholder="admin@company.com">
          </div>
          <div class="form-group">
            <label>Company Name</label>
            <input type="text" name="trial_company" placeholder="My Company">
          </div>
        </div>
        <input type="hidden" name="license_mode" value="trial">
        <input type="hidden" name="trial_mode" value="yes">
      </div>
    </div>

    <div id="tab-upload" class="tab-content" style="display:none">
      <div class="form-group">
        <label>Upload License File</label>
        <div class="file-upload" onclick="document.getElementById('licenseFileInput').click()">
          <div style="font-size:32px;margin-bottom:8px">📄</div>
          <p style="color:#94a3b8;font-size:13px">Click to upload your <strong>license.key</strong> file</p>
          <input type="file" id="licenseFileInput" name="license_file" accept=".key,.txt" style="display:none" onchange="this.closest('.file-upload').querySelector('p').innerHTML='Selected: '+this.files[0].name">
        </div>
      </div>
      <p style="text-align:center;color:#475569;margin:12px 0">— or paste license key content below —</p>
      <div class="form-group">
        <label>Paste License Key</label>
        <textarea name="license_content" rows="5" style="font-family:monospace;font-size:12px"></textarea>
      </div>
      <input type="hidden" name="license_mode" value="upload">
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/2" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Activate & Continue →</button>
  </div>
  <script>
  function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(function(el) { el.style.display = 'none'; });
    document.getElementById('tab-' + tab).style.display = '';
    document.querySelectorAll('[id$="-btn"]').forEach(function(el) { el.className = 'btn btn-secondary'; });
    document.getElementById('tab-' + tab + '-btn').className = 'btn btn-primary';
  }
  </script>

  <?php // ─── STEP 4: COMPANY INFORMATION ───
  elseif ($step === 4): ?>
  <div class="setup-card">
    <h2>Company Information</h2>
    <p class="subtitle">Tell us about your company. This information will appear on invoices and the client portal.</p>

    <div class="form-row">
      <div class="form-group">
        <label>Company Name</label>
        <input type="text" name="company_name" value="<?php echo htmlspecialchars($company_name); ?>" placeholder="Planet Hosts" required>
      </div>
      <div class="form-group">
        <label>Company Website</label>
        <input type="url" name="company_website" value="<?php echo htmlspecialchars($company_website); ?>" placeholder="https://planet-hosts.com">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Support Email</label>
        <input type="email" name="support_email" value="<?php echo htmlspecialchars($support_email); ?>" placeholder="support@planet-hosts.com">
      </div>
      <div class="form-group">
        <label>Billing Email</label>
        <input type="email" name="billing_email" value="<?php echo htmlspecialchars($billing_email); ?>" placeholder="billing@planet-hosts.com">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Abuse Email</label>
        <input type="email" name="abuse_email" value="<?php echo htmlspecialchars($abuse_email); ?>" placeholder="abuse@planet-hosts.com">
      </div>
      <div class="form-group">
        <label>NOC Email</label>
        <input type="email" name="noc_email" value="<?php echo htmlspecialchars($noc_email); ?>" placeholder="noc@planet-hosts.com">
      </div>
    </div>
    <div class="form-group">
      <label>Company Logo</label>
      <div class="file-upload" onclick="document.getElementById('logoInput').click()">
        <div style="font-size:32px;margin-bottom:8px">🖼️</div>
        <p style="color:#94a3b8;font-size:13px">Click to upload your company logo</p>
        <input type="file" id="logoInput" name="company_logo" accept="image/*" style="display:none" onchange="this.closest('.file-upload').querySelector('p').innerHTML='Selected: '+this.files[0].name">
      </div>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/3" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Continue →</button>
  </div>

  <?php // ─── STEP 5: PRIMARY DOMAIN ───
  elseif ($step === 5): ?>
  <div class="setup-card">
    <h2>Primary Domain Configuration</h2>
    <p class="subtitle">Enter your primary domain. Subdomains will be auto-generated; you can override any of them.</p>

    <div class="form-group">
      <label>Primary Domain</label>
      <input type="text" name="primary_domain" value="<?php echo htmlspecialchars($primary_domain ?? ''); ?>" placeholder="planet-hosts.com" required>
    </div>

    <div style="margin-top:20px">
      <h3 style="font-size:14px;color:#94a3b8;margin-bottom:12px">Auto-Generated Subdomains</h3>
      <div class="subdomain-list">
        <?php
        $prefixes = ['panel','clients','api','mail','webmail','ftp','support','status','cdn','downloads','billing','radio'];
        foreach ($prefixes as $p):
          $val = $subdomains[$p] ?? ($p . '.' . ($primary_domain ?? 'planet-hosts.com'));
        ?>
        <div class="subdomain-item">
          <span class="sd-prefix"><?php echo $p; ?>.</span>
          <input type="text" name="subdomain_<?php echo $p; ?>" value="<?php echo htmlspecialchars($val); ?>">
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/4" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Continue →</button>
  </div>

  <?php // ─── STEP 6: SERVER CONFIGURATION ───
  elseif ($step === 6): ?>
  <div class="setup-card">
    <h2>Server Configuration</h2>
    <p class="subtitle">Configure your server hostname, IP, and location.</p>

    <div class="form-row">
      <div class="form-group">
        <label>Hostname</label>
        <input type="text" name="server_hostname" value="<?php echo htmlspecialchars($server_hostname); ?>" placeholder="server1.planet-hosts.com">
      </div>
      <div class="form-group">
        <label>Server IP Address</label>
        <input type="text" name="server_ip" value="<?php echo htmlspecialchars($server_ip); ?>" placeholder="15.204.114.226">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Timezone</label>
        <select name="server_timezone">
          <?php
          $tzs = ['UTC','America/New_York','America/Chicago','America/Denver','America/Los_Angeles','Europe/London','Europe/Berlin','Europe/Paris','Asia/Tokyo','Asia/Shanghai','Asia/Singapore','Australia/Sydney','Pacific/Auckland'];
          foreach ($tzs as $tz): ?>
          <option value="<?php echo $tz; ?>" <?php echo ($server_timezone === $tz) ? 'selected' : ''; ?>><?php echo $tz; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Country</label>
        <select name="server_country">
          <option value="US" <?php echo ($server_country === 'US') ? 'selected' : ''; ?>>United States</option>
          <option value="GB" <?php echo ($server_country === 'GB') ? 'selected' : ''; ?>>United Kingdom</option>
          <option value="DE" <?php echo ($server_country === 'DE') ? 'selected' : ''; ?>>Germany</option>
          <option value="NL" <?php echo ($server_country === 'NL') ? 'selected' : ''; ?>>Netherlands</option>
          <option value="SG" <?php echo ($server_country === 'SG') ? 'selected' : ''; ?>>Singapore</option>
          <option value="AU" <?php echo ($server_country === 'AU') ? 'selected' : ''; ?>>Australia</option>
          <option value="CA" <?php echo ($server_country === 'CA') ? 'selected' : ''; ?>>Canada</option>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Datacenter Name</label>
        <input type="text" name="datacenter_name" value="<?php echo htmlspecialchars($datacenter_name); ?>" placeholder="Skyline Hosting">
      </div>
      <div class="form-group">
        <label>Server Role</label>
        <select name="server_role">
          <option value="master" <?php echo ($server_role === 'master') ? 'selected' : ''; ?>>Master Server</option>
          <option value="web" <?php echo ($server_role === 'web') ? 'selected' : ''; ?>>Web Node</option>
          <option value="streaming" <?php echo ($server_role === 'streaming') ? 'selected' : ''; ?>>Streaming Node</option>
          <option value="dns" <?php echo ($server_role === 'dns') ? 'selected' : ''; ?>>DNS Node</option>
          <option value="game" <?php echo ($server_role === 'game') ? 'selected' : ''; ?>>Game Hosting Node</option>
        </select>
      </div>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/5" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Configure Hostname →</button>
  </div>

  <?php // ─── STEP 7: DATABASE ───
  elseif ($step === 7): ?>
  <div class="setup-card">
    <h2>Database Configuration</h2>
    <p class="subtitle">Enter your database connection details. We'll test the connection before continuing.</p>

    <div class="form-row">
      <div class="form-group">
        <label>Database Host</label>
        <input type="text" name="db_host" value="<?php echo htmlspecialchars($db_host); ?>" placeholder="localhost">
      </div>
      <div class="form-group">
        <label>Database Port</label>
        <input type="text" name="db_port" value="<?php echo htmlspecialchars($db_port); ?>" placeholder="3306">
      </div>
    </div>
    <div class="form-group">
      <label>Database Name</label>
      <input type="text" name="db_name" value="<?php echo htmlspecialchars($db_name); ?>" placeholder="radiohosting">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Database Username</label>
        <input type="text" name="db_user" value="<?php echo htmlspecialchars($db_user); ?>" placeholder="radiouser">
      </div>
      <div class="form-group">
        <label>Database Password</label>
        <input type="password" name="db_pass" value="<?php echo htmlspecialchars($db_pass); ?>" placeholder="Enter password">
      </div>
    </div>
    <?php if ($db_connection_ok): ?>
    <div class="alert alert-success">✓ Database connection verified!</div>
    <?php endif; ?>
  </div>
  <div class="button-group">
    <a href="/setup/6" class="btn btn-secondary">← Back</a>
    <div>
      <button type="submit" name="action" value="test" class="btn btn-secondary">Test Connection</button>
      <button type="submit" class="btn btn-primary">Continue →</button>
    </div>
  </div>

  <?php // ─── STEP 8: ADMIN ACCOUNT ───
  elseif ($step === 8): ?>
  <div class="setup-card">
    <h2>Administrator Account</h2>
    <p class="subtitle">Create the first administrator account for the panel.</p>

    <div class="form-row">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="admin_username" value="<?php echo htmlspecialchars($admin_username); ?>" placeholder="root" required>
      </div>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="admin_email" value="<?php echo htmlspecialchars($admin_email); ?>" placeholder="admin@planet-hosts.com">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="admin_password" placeholder="Min 8 chars, uppercase, lowercase, number" required>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="admin_password_confirm" placeholder="Re-enter password" required>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="admin_phone" value="<?php echo htmlspecialchars($admin_phone); ?>" placeholder="+1-555-0123">
      </div>
      <div class="form-group">
        <label>Two-Factor Authentication</label>
        <select name="admin_twofactor">
          <option value="0">Disabled</option>
          <option value="1">Enabled</option>
        </select>
      </div>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/7" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Create Admin →</button>
  </div>

  <?php // ─── STEP 9: NAMESERVERS ───
  elseif ($step === 9): ?>
  <div class="setup-card">
    <h2>Nameserver Configuration</h2>
    <p class="subtitle">Configure your nameservers for DNS hosting.</p>

    <div class="form-row">
      <div class="form-group">
        <label>Primary Nameserver</label>
        <input type="text" name="ns1" value="<?php echo htmlspecialchars($ns1); ?>" placeholder="ns1.planet-hosts.com">
      </div>
      <div class="form-group">
        <label>Secondary Nameserver</label>
        <input type="text" name="ns2" value="<?php echo htmlspecialchars($ns2); ?>" placeholder="ns2.planet-hosts.com">
      </div>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/8" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Continue →</button>
  </div>

  <?php // ─── STEP 10: WEB HOSTING ───
  elseif ($step === 10): ?>
  <div class="setup-card">
    <h2>Web Hosting Configuration</h2>
    <p class="subtitle">Configure default web server settings for new accounts.</p>

    <div class="form-row">
      <div class="form-group">
        <label>Web Server</label>
        <select name="web_server">
          <option value="apache" <?php echo ($web_server === 'apache') ? 'selected' : ''; ?>>Apache</option>
          <option value="nginx" <?php echo ($web_server === 'nginx') ? 'selected' : ''; ?>>Nginx</option>
          <option value="both" <?php echo ($web_server === 'both') ? 'selected' : ''; ?>>Both (Nginx Proxy)</option>
        </select>
      </div>
      <div class="form-group">
        <label>Default PHP Version</label>
        <input type="text" name="default_php" value="<?php echo htmlspecialchars($default_php); ?>" placeholder="8.2">
      </div>
    </div>
    <div class="form-group">
      <label>Default Document Root</label>
      <input type="text" name="default_docroot" value="<?php echo htmlspecialchars($default_docroot); ?>" placeholder="/var/www">
    </div>
    <div class="form-row-3">
      <div class="form-group">
        <label><input type="checkbox" name="http2" value="1" <?php echo $http2 === '1' ? 'checked' : ''; ?>> Enable HTTP/2</label>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="compression" value="1" <?php echo $compression === '1' ? 'checked' : ''; ?>> Enable Compression</label>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="security_headers" value="1" <?php echo $security_headers === '1' ? 'checked' : ''; ?>> Security Headers</label>
      </div>
    </div>
    <div class="form-group">
      <label><input type="checkbox" name="php_fpm" value="1" <?php echo $php_fpm === '1' ? 'checked' : ''; ?>> PHP-FPM (recommended)</label>
    </div>

    <h3 style="font-size:14px;color:#94a3b8;margin:20px 0 12px">Default Resource Limits</h3>
    <div class="form-row">
      <div class="form-group">
        <label>Disk Quota (MB)</label>
        <input type="number" name="default_disk_quota" value="<?php echo htmlspecialchars($default_disk_quota); ?>" placeholder="1024">
      </div>
      <div class="form-group">
        <label>Bandwidth (MB)</label>
        <input type="number" name="default_bandwidth" value="<?php echo htmlspecialchars($default_bandwidth); ?>" placeholder="10240">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>CPU Limit (%)</label>
        <input type="number" name="default_cpu" value="<?php echo htmlspecialchars($default_cpu); ?>" placeholder="100">
      </div>
      <div class="form-group">
        <label>RAM Limit (MB)</label>
        <input type="number" name="default_ram" value="<?php echo htmlspecialchars($default_ram); ?>" placeholder="512">
      </div>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/9" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Continue →</button>
  </div>

  <?php // ─── STEP 11: MAIL SERVER ───
  elseif ($step === 11): ?>
  <div class="setup-card">
    <h2>Mail Server Configuration</h2>
    <p class="subtitle">Configure email services and security protocols.</p>

    <div class="form-group">
      <label class="toggle-item" style="display:flex">
        <input type="checkbox" name="enable_smtp" value="1" <?php echo $enable_smtp === '1' ? 'checked' : ''; ?>> Enable SMTP (sending mail)
      </label>
      <label class="toggle-item" style="display:flex;margin-top:8px">
        <input type="checkbox" name="enable_imap" value="1" <?php echo $enable_imap === '1' ? 'checked' : ''; ?>> Enable IMAP (mailboxes)
      </label>
      <label class="toggle-item" style="display:flex;margin-top:8px">
        <input type="checkbox" name="enable_pop3" value="1" <?php echo $enable_pop3 === '1' ? 'checked' : ''; ?>> Enable POP3
      </label>
    </div>

    <h3 style="font-size:14px;color:#94a3b8;margin:20px 0 12px">Email Authentication</h3>
    <div class="form-row-3">
      <div class="form-group">
        <label><input type="checkbox" name="enable_dkim" value="1" <?php echo $enable_dkim === '1' ? 'checked' : ''; ?>> DKIM Signing</label>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="enable_spf" value="1" <?php echo $enable_spf === '1' ? 'checked' : ''; ?>> SPF Records</label>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="enable_dmarc" value="1" <?php echo $enable_dmarc === '1' ? 'checked' : ''; ?>> DMARC Policy</label>
      </div>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/10" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Continue →</button>
  </div>

  <?php // ─── STEP 12: STREAMING ───
  elseif ($step === 12): ?>
  <div class="setup-card">
    <h2>Streaming Configuration</h2>
    <p class="subtitle">Enable streaming services and configure port ranges.</p>

    <div class="form-row-3">
      <div class="form-group">
        <label><input type="checkbox" name="streaming_shoutcast_v1" value="1" <?php echo $streaming_shoutcast_v1 === '1' ? 'checked' : ''; ?>> SHOUTcast v1</label>
        <div class="help-text">Ports 11000-11999</div>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="streaming_shoutcast_v2" value="1" <?php echo $streaming_shoutcast_v2 === '1' ? 'checked' : ''; ?>> SHOUTcast v2</label>
        <div class="help-text">Ports 12000-13999</div>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="streaming_icecast" value="1" <?php echo $streaming_icecast === '1' ? 'checked' : ''; ?>> Icecast</label>
        <div class="help-text">Ports 14000-15999</div>
      </div>
    </div>
    <div class="form-row-3">
      <div class="form-group">
        <label><input type="checkbox" name="streaming_autodj" value="1" <?php echo $streaming_autodj === '1' ? 'checked' : ''; ?>> AutoDJ</label>
        <div class="help-text">Ports 16000-16499</div>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="streaming_rtmp" value="1" <?php echo $streaming_rtmp === '1' ? 'checked' : ''; ?>> RTMP Video</label>
        <div class="help-text">Ports 17000-17999</div>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="streaming_rtsp" value="1" <?php echo $streaming_rtsp === '1' ? 'checked' : ''; ?>> RTSP Cameras</label>
        <div class="help-text">Ports 18000-18999</div>
      </div>
    </div>
    <div class="form-group">
      <label><input type="checkbox" name="streaming_relay" value="1" <?php echo $streaming_relay === '1' ? 'checked' : ''; ?>> Audio Relay</label>
      <div class="help-text">Ports 20000-20999</div>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/11" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Continue →</button>
  </div>

  <?php // ─── STEP 13: SSL ───
  elseif ($step === 13): ?>
  <div class="setup-card">
    <h2>SSL Configuration</h2>
    <p class="subtitle">Configure SSL certificates for your panel and domains.</p>

    <div class="form-group">
      <label>Let's Encrypt Email</label>
      <input type="email" name="letsencrypt_email" value="<?php echo htmlspecialchars($letsencrypt_email); ?>" placeholder="admin@planet-hosts.com">
      <div class="help-text">Used for urgent renewal notices and lost key recovery</div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label><input type="checkbox" name="auto_renewal" value="1" <?php echo $auto_renewal === '1' ? 'checked' : ''; ?>> Auto-Renew Certificates</label>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="wildcard_support" value="1" <?php echo $wildcard_support === '1' ? 'checked' : ''; ?>> Wildcard SSL Support</label>
      </div>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/12" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Continue →</button>
  </div>

  <?php // ─── STEP 14: FIREWALL ───
  elseif ($step === 14): ?>
  <div class="setup-card">
    <h2>Firewall Configuration</h2>
    <p class="subtitle">Configure firewall settings and allowed ports.</p>

    <div class="form-row">
      <div class="form-group">
        <label>Firewall Engine</label>
        <select name="firewall_engine">
          <option value="iptables" <?php echo $firewall_engine === 'iptables' ? 'selected' : ''; ?>>iptables / nftables</option>
          <option value="firewalld" <?php echo $firewall_engine === 'firewalld' ? 'selected' : ''; ?>>firewalld</option>
          <option value="csf" <?php echo $firewall_engine === 'csf' ? 'selected' : ''; ?>>CSF</option>
        </select>
      </div>
      <div class="form-group">
        <label>SSH Port</label>
        <input type="number" name="ssh_port" value="<?php echo htmlspecialchars($ssh_port); ?>" placeholder="22">
      </div>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/13" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Continue →</button>
  </div>

  <?php // ─── STEP 15: SECURITY ───
  elseif ($step === 15): ?>
  <div class="setup-card">
    <h2>Security Configuration</h2>
    <p class="subtitle">Configure security measures to protect your server.</p>

    <div class="form-row-3">
      <div class="form-group">
        <label><input type="checkbox" name="enable_fail2ban" value="1" <?php echo $enable_fail2ban === '1' ? 'checked' : ''; ?>> Fail2Ban</label>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="enable_modsecurity" value="1" <?php echo $enable_modsecurity === '1' ? 'checked' : ''; ?>> ModSecurity</label>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="enable_malware_scan" value="1" <?php echo $enable_malware_scan === '1' ? 'checked' : ''; ?>> Malware Scanning</label>
      </div>
    </div>
    <div class="form-row-3">
      <div class="form-group">
        <label><input type="checkbox" name="enable_bruteforce" value="1" <?php echo $enable_bruteforce === '1' ? 'checked' : ''; ?>> Brute Force Protection</label>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="enable_ssh_restrict" value="1" <?php echo $enable_ssh_restrict === '1' ? 'checked' : ''; ?>> SSH Restrictions</label>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="enable_account_isolation" value="1" <?php echo $enable_account_isolation === '1' ? 'checked' : ''; ?>> Account Isolation</label>
      </div>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/14" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Continue →</button>
  </div>

  <?php // ─── STEP 16: BACKUP ───
  elseif ($step === 16): ?>
  <div class="setup-card">
    <h2>Backup Configuration</h2>
    <p class="subtitle">Configure automated backups for accounts and system data.</p>

    <div class="form-group">
      <label>Backup Location</label>
      <input type="text" name="backup_location" value="<?php echo htmlspecialchars($backup_location); ?>" placeholder="/var/backups/planethosts">
    </div>
    <div class="form-row-3">
      <div class="form-group">
        <label><input type="checkbox" name="backup_daily" value="1" <?php echo $backup_daily === '1' ? 'checked' : ''; ?>> Daily Backups</label>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="backup_weekly" value="1" <?php echo $backup_weekly === '1' ? 'checked' : ''; ?>> Weekly Backups</label>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="backup_monthly" value="1" <?php echo $backup_monthly === '1' ? 'checked' : ''; ?>> Monthly Backups</label>
      </div>
    </div>
    <div class="form-row-3">
      <div class="form-group">
        <label>Daily Retention</label>
        <input type="number" name="backup_retention_daily" value="<?php echo htmlspecialchars($backup_retention_daily); ?>" placeholder="7">
      </div>
      <div class="form-group">
        <label>Weekly Retention</label>
        <input type="number" name="backup_retention_weekly" value="<?php echo htmlspecialchars($backup_retention_weekly); ?>" placeholder="4">
      </div>
      <div class="form-group">
        <label>Monthly Retention</label>
        <input type="number" name="backup_retention_monthly" value="<?php echo htmlspecialchars($backup_retention_monthly); ?>" placeholder="3">
      </div>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/15" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Continue →</button>
  </div>

  <?php // ─── STEP 17: PAYMENT GATEWAYS ───
  elseif ($step === 17): ?>
  <div class="setup-card">
    <h2>Payment Gateways</h2>
    <p class="subtitle">Configure payment gateways (optional). You can skip this step and configure later.</p>

    <div style="margin-bottom:20px">
      <h3 style="font-size:14px;color:#94a3b8;margin-bottom:8px">PayPal</h3>
      <div class="form-group">
        <label><input type="checkbox" name="enable_paypal" value="1" <?php echo $enable_paypal === '1' ? 'checked' : ''; ?>> Enable PayPal</label>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Client ID</label>
          <input type="text" name="paypal_client_id" value="<?php echo htmlspecialchars($paypal_client_id); ?>">
        </div>
        <div class="form-group">
          <label>Secret</label>
          <input type="password" name="paypal_secret" value="<?php echo htmlspecialchars($paypal_secret); ?>">
        </div>
      </div>
    </div>

    <div style="margin-bottom:20px">
      <h3 style="font-size:14px;color:#94a3b8;margin-bottom:8px">Stripe</h3>
      <div class="form-group">
        <label><input type="checkbox" name="enable_stripe" value="1" <?php echo $enable_stripe === '1' ? 'checked' : ''; ?>> Enable Stripe</label>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Publishable Key</label>
          <input type="text" name="stripe_publishable_key" value="<?php echo htmlspecialchars($stripe_publishable_key); ?>">
        </div>
        <div class="form-group">
          <label>Secret Key</label>
          <input type="password" name="stripe_secret_key" value="<?php echo htmlspecialchars($stripe_secret_key); ?>">
        </div>
      </div>
    </div>

    <div>
      <h3 style="font-size:14px;color:#94a3b8;margin-bottom:8px">Square</h3>
      <div class="form-group">
        <label><input type="checkbox" name="enable_square" value="1" <?php echo $enable_square === '1' ? 'checked' : ''; ?>> Enable Square</label>
      </div>
      <div class="form-group">
        <label>Access Token</label>
        <input type="password" name="square_access_token" value="<?php echo htmlspecialchars($square_access_token); ?>">
      </div>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/16" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Continue →</button>
  </div>

  <?php // ─── STEP 18: API CONFIG ───
  elseif ($step === 18): ?>
  <div class="setup-card">
    <h2>API Configuration</h2>
    <p class="subtitle">Configure the Planet Hosts API for integrations.</p>

    <div class="form-row">
      <div class="form-group">
        <label>Master API Key</label>
        <input type="text" name="api_key" value="<?php echo htmlspecialchars($api_key); ?>">
      </div>
      <div class="form-group">
        <label>Master API Secret</label>
        <input type="text" name="api_secret" value="<?php echo htmlspecialchars($api_secret); ?>">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Rate Limit (requests/min)</label>
        <input type="number" name="api_rate_limit" value="<?php echo htmlspecialchars($api_rate_limit); ?>">
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="api_logging" value="1" <?php echo $api_logging === '1' ? 'checked' : ''; ?>> Enable API Logging</label>
      </div>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/17" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Continue →</button>
  </div>

  <?php // ─── STEP 19: SERVICE VALIDATION ───
  elseif ($step === 19):
  $validation = $validation ?? [];
  ?>
  <div class="setup-card">
    <h2>Service Validation</h2>
    <p class="subtitle">Verifying that all required services are running correctly.</p>

    <?php foreach ($validation as $vk => $vv): ?>
    <div class="check-item check-<?php echo $vv['status']; ?>">
      <span class="check-label"><?php echo htmlspecialchars($vv['label']); ?></span>
      <span class="check-value"><?php echo htmlspecialchars($vv['message'] ?? ''); ?></span>
      <span class="check-status">
        <?php if ($vv['status'] === 'pass'): ?>✓ PASS
        <?php elseif ($vv['status'] === 'warning'): ?>⚠ WARNING
        <?php elseif ($vv['status'] === 'info'): ?>ℹ INFO
        <?php else: ?>✗ FAIL<?php endif; ?>
      </span>
      <?php if ($vv['label'] === 'Storage Directories' && $vv['status'] !== 'pass'): ?>
      <a href="/admin/system/storage-setup" class="btn btn-sm" style="padding:4px 10px;background:rgba(250,204,21,.15);color:#facc15;border-radius:6px;font-size:10px;font-weight:600;text-decoration:none;margin-left:8px">Fix</a>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="button-group">
    <a href="/setup/18" class="btn btn-secondary">← Back</a>
    <button type="submit" class="btn btn-primary">Continue →</button>
  </div>

  <?php // ─── STEP 20: INSTALL SUMMARY ───
  elseif ($step === 20): ?>
  <div class="setup-card">
    <h2>Installation Summary</h2>
    <p class="subtitle">Review your configuration before completing the installation.</p>

    <div class="summary-grid">
      <div class="summary-item">
        <div class="s-label">Company</div>
        <div class="s-value"><?php echo htmlspecialchars($sd['company_name'] ?? 'Not set'); ?></div>
      </div>
      <div class="summary-item">
        <div class="s-label">Primary Domain</div>
        <div class="s-value"><?php echo htmlspecialchars($sd['primary_domain'] ?? 'Not set'); ?></div>
      </div>
      <div class="summary-item">
        <div class="s-label">Hostname</div>
        <div class="s-value"><?php echo htmlspecialchars($sd['server_hostname'] ?? 'Not set'); ?></div>
      </div>
      <div class="summary-item">
        <div class="s-label">Server IP</div>
        <div class="s-value"><?php echo htmlspecialchars($sd['server_ip'] ?? 'Not set'); ?></div>
      </div>
      <div class="summary-item">
        <div class="s-label">Nameservers</div>
        <div class="s-value"><?php echo htmlspecialchars(($sd['ns1'] ?? '') . ' / ' . ($sd['ns2'] ?? '')); ?></div>
      </div>
      <div class="summary-item">
        <div class="s-label">Admin</div>
        <div class="s-value"><?php echo htmlspecialchars($sd['admin_username'] ?? 'Not set'); ?></div>
      </div>
      <div class="summary-item">
        <div class="s-label">Timezone</div>
        <div class="s-value"><?php echo htmlspecialchars($sd['server_timezone'] ?? 'UTC'); ?></div>
      </div>
      <div class="summary-item">
        <div class="s-label">License</div>
        <div class="s-value"><?php echo strtoupper($sd['license']['type'] ?? 'Trial'); ?></div>
      </div>
    </div>

    <div style="margin-top:20px">
      <p style="color:#475569;font-size:13px;text-align:center">Review your settings above. You can go back to change anything before finalizing.</p>
    </div>
  </div>
  <div class="button-group">
    <a href="/setup/19" class="btn btn-secondary">← Back</a>
    <div>
      <button type="submit" name="action" value="install" class="btn btn-success">✓ Install Planet Hosts</button>
    </div>
  </div>

  <?php // ─── STEP 21: COMPLETE ───
  elseif ($step === 21): ?>
  <div class="setup-card">
    <div class="completion-check">
      <div class="check-icon">✓</div>
      <h2>Installation Complete!</h2>
      <p>Planet Hosts has been successfully installed and configured. You can now access your admin dashboard.</p>
    </div>

    <div class="summary-grid" style="margin-top:20px">
      <div class="summary-item">
        <div class="s-label">Admin Panel</div>
        <div class="s-value"><a href="/admin/dashboard" style="color:#008cff">/admin/dashboard</a></div>
      </div>
      <div class="summary-item">
        <div class="s-label">Admin Username</div>
        <div class="s-value"><?php echo htmlspecialchars($sd['admin_username'] ?? 'root'); ?></div>
      </div>
      <div class="summary-item">
        <div class="s-label">Primary Domain</div>
        <div class="s-value"><?php echo htmlspecialchars($sd['primary_domain'] ?? 'N/A'); ?></div>
      </div>
      <div class="summary-item">
        <div class="s-label">Hostname</div>
        <div class="s-value"><?php echo htmlspecialchars($sd['server_hostname'] ?? gethostname()); ?></div>
      </div>
      <div class="summary-item">
        <div class="s-label">Server IP</div>
        <div class="s-value"><?php echo htmlspecialchars($sd['server_ip'] ?? $_SERVER['SERVER_ADDR'] ?? 'N/A'); ?></div>
      </div>
      <div class="summary-item">
        <div class="s-label">Nameservers</div>
        <div class="s-value"><?php echo htmlspecialchars(($sd['ns1'] ?? '') . ' / ' . ($sd['ns2'] ?? '')); ?></div>
      </div>
    </div>
  </div>
  <div class="button-group">
    <div></div>
    <a href="/admin/dashboard" class="btn btn-primary">Go to Dashboard →</a>
  </div>

  <?php endif; ?>
  </form>
</div>

<script>
document.querySelectorAll('form').forEach(function(f) {
    f.addEventListener('submit', function() {
        var btn = this.querySelector('button[type="submit"]');
        if (btn) { btn.disabled = true; btn.innerHTML = 'Processing...'; }
    });
});
</script>
</body>
</html>
