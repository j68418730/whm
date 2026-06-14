<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<form method="POST" action="/admin/account/store">
<div class="stats-grid" style="grid-template-columns:1fr 1fr">
<div class="form-group"><label>Username</label><input name="username" required placeholder="e.g. johndoe"></div>
<div class="form-group"><label>Email</label><input type="email" name="email" required placeholder="user@example.com"></div>
<div class="form-group"><label>Password</label><input type="password" name="password" required minlength="8"></div>
<div class="form-group"><label>Package</label><select name="package_id"><option value="">No package</option><?php if (isset($packages)): foreach ($packages as $p): ?><option value="<?php echo $p->id; ?>"><?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; endif; ?></select></div>
<div class="form-group"><label>First Name</label><input name="first_name"></div>
<div class="form-group"><label>Last Name</label><input name="last_name"></div>
</div>
<div style="display:flex;gap:12px;margin-top:20px">
<button type="submit" class="btn primary">Create Account</button>
<a href="/admin/account" class="btn secondary">Cancel</a>
</div>
</form>
