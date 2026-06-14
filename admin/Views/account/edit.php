<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<form method="POST" action="/admin/account/store">
<div class="stats-grid" style="grid-template-columns:1fr 1fr">
<div class="form-group"><label>Username</label><input name="username" value="<?php echo htmlspecialchars($account->username, ENT_QUOTES, 'UTF-8'); ?>" required></div>
<div class="form-group"><label>Domain</label><input name="domain" value="<?php echo htmlspecialchars($account->domain ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
<div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($account->email, ENT_QUOTES, 'UTF-8'); ?>" required></div>
<div class="form-group"><label>Package</label><select name="package_id"><option value="">No package</option><?php if (isset($packages)): foreach ($packages as $p): ?><option value="<?php echo $p->id; ?>" <?php echo ($account->package_id == $p->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; endif; ?></select></div>
<div class="form-group"><label>PHP Version</label><select name="php_version"><option value="">Server default</option><?php foreach (['5.6','7.0','7.1','7.2','7.3','7.4','8.0','8.1','8.2','8.3','8.4'] as $v): ?><option value="<?php echo $v; ?>" <?php echo ($account->php_version ?? '') === $v ? 'selected' : ''; ?>><?php echo $v; ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>First Name</label><input name="first_name" value="<?php echo htmlspecialchars($account->first_name ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
<div class="form-group"><label>Last Name</label><input name="last_name" value="<?php echo htmlspecialchars($account->last_name ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
</div>
<div style="display:flex;gap:12px;margin-top:20px">
<button type="submit" class="btn primary">Save Changes</button>
<a href="/admin/account/show/<?php echo $account->id; ?>" class="btn secondary">Cancel</a>
</div>
</form>
