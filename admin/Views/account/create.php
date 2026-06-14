<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<form method="POST" action="/admin/account/store">
<div class="stats-grid" style="grid-template-columns:1fr 1fr">
<div class="form-group"><label>Username</label><input name="username" required placeholder="e.g. johndoe"></div>
<div class="form-group"><label>Domain</label><input name="domain" placeholder="example.com (auto-generated if empty)"></div>
<div class="form-group"><label>Email</label><input type="email" name="email" required placeholder="user@example.com"></div>
<div class="form-group"><label>Password</label><input type="password" name="password" required minlength="8"></div>
<div class="form-group"><label>Package</label><select name="package_id"><option value="">No package</option><?php if (isset($packages)): foreach ($packages as $p): ?><option value="<?php echo $p->id; ?>"><?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; endif; ?></select></div>
<div class="form-group"><label>PHP Version</label><select name="php_version"><option value="">Server default</option><option value="5.6">5.6</option><option value="7.0">7.0</option><option value="7.1">7.1</option><option value="7.2">7.2</option><option value="7.3">7.3</option><option value="7.4">7.4</option><option value="8.0">8.0</option><option value="8.1">8.1</option><option value="8.2">8.2</option><option value="8.3">8.3</option><option value="8.4">8.4</option></select></div>
<div class="form-group"><label>First Name</label><input name="first_name"></div>
<div class="form-group"><label>Last Name</label><input name="last_name"></div>
</div>
<div style="display:flex;gap:12px;margin-top:20px">
<button type="submit" class="btn primary">Create Account</button>
<a href="/admin/account" class="btn secondary">Cancel</a>
</div>
</form>
