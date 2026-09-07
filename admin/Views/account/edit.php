<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<form method="POST" action="/admin/account/update/<?php echo $account->id; ?>">
<h3 style="color:var(--accent);margin:0 0 12px">Account</h3>
<div class="stats-grid" style="grid-template-columns:1fr 1fr 1fr">
<div class="form-group"><label>Username</label><input name="username" value="<?php echo htmlspecialchars($account->username, ENT_QUOTES, 'UTF-8'); ?>" required></div>
<div class="form-group"><label>Domain</label><input name="domain" value="<?php echo htmlspecialchars($account->domain ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
<div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($account->email, ENT_QUOTES, 'UTF-8'); ?>" required></div>
<div class="form-group"><label>Package</label><select name="package_id"><option value="">No package</option><?php if (isset($packages)): foreach ($packages as $p): ?><option value="<?php echo $p->id; ?>" <?php echo ($account->package_id == $p->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; endif; ?></select></div>
<div class="form-group"><label>PHP Version</label><select name="php_version"><option value="">Server default</option><?php foreach (['5.6','7.0','7.1','7.2','7.3','7.4','8.0','8.1','8.2','8.3','8.4'] as $v): ?><option value="<?php echo $v; ?>" <?php echo ($account->php_version ?? '') === $v ? 'selected' : ''; ?>><?php echo $v; ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>First Name</label><input name="first_name" value="<?php echo htmlspecialchars($account->first_name ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
<div class="form-group"><label>Last Name</label><input name="last_name" value="<?php echo htmlspecialchars($account->last_name ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
<div class="form-group"><label>Phone</label><input name="phone" value="<?php echo htmlspecialchars($account->phone ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="+1 (555) 000-0000"></div>
<div class="form-group"><label>Country</label><input name="country" value="<?php echo htmlspecialchars($account->country ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
</div>

<h3 style="color:var(--accent);margin:18px 0 12px">Address</h3>
<div class="stats-grid" style="grid-template-columns:2fr 1fr 1fr 1fr">
<div class="form-group"><label>Street Address</label><input name="address" value="<?php echo htmlspecialchars($account->address ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
<div class="form-group"><label>City</label><input name="city" value="<?php echo htmlspecialchars($account->city ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
<div class="form-group"><label>State</label><input name="state" value="<?php echo htmlspecialchars($account->state ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
<div class="form-group"><label>ZIP</label><input name="zip" value="<?php echo htmlspecialchars($account->zip ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
</div>

<h3 style="color:var(--accent);margin:18px 0 12px">Avatar &amp; Payment Gateway</h3>
<div class="stats-grid" style="grid-template-columns:1fr 1fr 2fr">
<div class="form-group"><label>Avatar URL</label><input name="avatar" value="<?php echo htmlspecialchars($account->avatar ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://…/avatar.png"><small style="color:#64748b;font-size:11px">Shown in support &amp; chat contexts</small></div>
<div class="form-group"><label>Payment Gateway</label><select name="payment_gateway">
<option value="">None</option>
<?php foreach (['paypal' => 'PayPal', 'cashapp' => 'Cash App', 'stripe' => 'Stripe', 'other' => 'Other'] as $gv => $gl): ?>
<option value="<?php echo $gv; ?>" <?php echo ($account->payment_gateway ?? '') === $gv ? 'selected' : ''; ?>><?php echo $gl; ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Gateway Identifier</label><input name="payment_gateway_id" value="<?php echo htmlspecialchars($account->payment_gateway_id ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="PayPal email / $cashtag / account id"></div>
</div>

<h3 style="color:var(--accent);margin:18px 0 12px">Account Authorization Code (4-digit PIN)</h3>
<div class="stats-grid" style="grid-template-columns:1fr 2fr;align-items:end">
<div class="form-group">
<label>Set / Replace PIN</label>
<input name="account_pin" inputmode="numeric" pattern="\d{4}" maxlength="4" placeholder="<?php echo !empty($account->support_pin_hash) ? '•••• (PIN is set)' : '4 digits'; ?>">
</div>
<div class="form-group">
<label>&nbsp;</label>
<label style="font-size:12px;color:var(--text-muted);display:flex;gap:8px;align-items:center"><input type="checkbox" name="clear_pin" value="1" style="width:auto"> Remove PIN (disables PIN verification for this account)</label>
</div>
</div>
<small style="color:#64748b;font-size:11px;display:block;margin:-6px 0 0">Used by Support Desktop billing verification and required at checkout when this email already belongs to an account. Stored hashed — never plaintext. Leave blank to keep the current PIN.</small>

<div style="display:flex;gap:12px;margin-top:20px">
<button type="submit" class="btn primary">Save Changes</button>
<a href="/admin/account/show/<?php echo $account->id; ?>" class="btn secondary">Cancel</a>
</div>
</form>
