<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="card" style="max-width:700px">
<h3 style="color:var(--accent);margin-bottom:20px"><i class="bi bi-ui-checks"></i> Create Feature List</h3>
<form method="POST" action="/admin/feature-lists/store">
<div class="form-group"><label>Name *</label><input name="name" required placeholder="e.g. Basic Feature Set" style="width:100%"></div>

<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:16px">
<div class="form-group"><label>Email Accounts</label><input name="email_accounts" type="number" value="-1" style="width:100%"><small>-1 = unlimited</small></div>
<div class="form-group"><label>FTP Accounts</label><input name="ftp_accounts" type="number" value="-1" style="width:100%"><small>-1 = unlimited</small></div>
<div class="form-group"><label>Databases</label><input name="databases" type="number" value="-1" style="width:100%"><small>-1 = unlimited</small></div>
<div class="form-group"><label>Database Users</label><input name="database_users" type="number" value="-1" style="width:100%"><small>-1 = unlimited</small></div>
<div class="form-group"><label>Subdomains</label><input name="subdomains" type="number" value="-1" style="width:100%"><small>-1 = unlimited</small></div>
<div class="form-group"><label>Parked Domains</label><input name="parked_domains" type="number" value="-1" style="width:100%"><small>-1 = unlimited</small></div>
<div class="form-group"><label>Addon Domains</label><input name="addon_domains" type="number" value="-1" style="width:100%"><small>-1 = unlimited</small></div>
</div>

<h4 style="color:var(--accent);margin:16px 0 10px">Toggles</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="cron_jobs" value="1" checked> Cron Jobs</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="ssh_access" value="1"> SSH Access</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="ssl_allowed" value="1" checked> SSL Allowed</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="git_access" value="1" checked> Git Access</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="nodejs" value="1"> Node.js</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="python" value="1"> Python</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="ruby" value="1"> Ruby</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="terminal" value="1"> Terminal</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="backups" value="1" checked> Backups</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="installer" value="1" checked> One-Click Installer</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="chatbox" value="1"> Chatbox (Text)</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="chatbox_voice" value="1"> Chatbox (Voice)</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="chatbox_video" value="1"> Chatbox (Voice + Video)</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="game" value="1"> Game Servers</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="radio" value="1"> Radio (Icecast)</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="dj_panel" value="1"> DJ Panel</label>
<label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer"><input type="checkbox" name="builder" value="1"> Website Builder</label>
</div>

<div style="margin-top:24px;display:flex;gap:12px">
<button type="submit" class="btn primary"><i class="bi bi-check-lg"></i> Create</button>
<a href="/admin/feature-lists" class="btn secondary">Cancel</a>
</div>
</form>
</div>
