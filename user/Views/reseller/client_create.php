<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
<div>
<h2 style="margin:0"><i class="bi bi-person-plus"></i> Create Client</h2>
<p style="color:var(--text_muted,#94a3b8);margin:4px 0 0">Create a new account under your reseller. Runs through Planet Hosts backend — no SSH/root needed.</p>
</div>
<a href="/reseller/clients" class="btn btn-secondary">&larr; Client List</a>
</div>

<form method="POST" action="/reseller/clients/store">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">

<!-- Left Column -->
<div class="card">
<h4 style="color:var(--accent,#008cff);margin-bottom:16px"><i class="bi bi-person"></i> Account Details</h4>
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
<h4 style="color:var(--accent,#008cff);margin-bottom:16px"><i class="bi bi-globe"></i> Domain & Package</h4>
<div class="form-group"><label>Domain</label>
<input name="domain" placeholder="example.com (blank = username.planet-hosts.com)" style="width:100%">
<small style="color:#64748b">Primary domain for this account.</small>
</div>
<div class="form-group"><label>Your Retail Package</label>
<select name="reseller_package_id" style="width:100%">
<option value="">-- No Package --</option>
<?php foreach ($pkgs as $p): ?>
<option value="<?php echo (int)$p->id; ?>"><?php echo htmlspecialchars($p->name); ?> ($<?php echo number_format((float)$p->price, 2); ?>/<?php echo htmlspecialchars($p->billing_cycle); ?>)</option>
<?php endforeach; ?>
</select>
<small style="color:#64748b">Only your own retail packages. Server packages are never used.</small>
</div>
<div class="form-group"><label>PHP Version</label>
<select name="php_version" style="width:100%">
<option value="">Server Default (8.2)</option>
<?php foreach (['7.4','8.0','8.1','8.2','8.3','8.4'] as $v): ?>
<option value="<?php echo $v; ?>"<?php if ($v === '8.2') echo ' selected'; ?>><?php echo $v; ?></option>
<?php endforeach; ?>
</select>
</div>
<div style="padding:10px;background:rgba(0,140,255,.05);border:1px solid rgba(0,140,255,.15);border-radius:8px;font-size:12px;color:#94a3b8">
<i class="bi bi-info-circle"></i> The account is created as <b>pending</b>. Activate it from the Provisioning page; that runs through the Planet Hosts backend.
</div>
</div>

</div>

<div style="display:flex;gap:12px;margin-top:20px;justify-content:flex-start">
<button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Create Client</button>
<a href="/reseller/clients" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancel</a>
</div>
</form>