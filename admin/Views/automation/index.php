<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success" style="margin-bottom:16px"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div style="display:flex;gap:8px;margin-bottom:16px">
<a href="/admin/automation/run" class="btn primary" onclick="return confirm('Run automation now?')">Run Automation Now</a>
</div>

<form method="POST" action="/admin/automation/save">
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">Account Automation</h3>
<div class="form-group"><label><input name="auto_provision_enabled" type="checkbox" value="1" <?php echo ($settings['auto_provision_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>> Auto Provision — Activate accounts when orders are marked pending</label></div>
<div class="form-group"><label><input name="auto_suspend_enabled" type="checkbox" value="1" <?php echo ($settings['auto_suspend_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>> Auto Suspend — Suspend accounts with overdue invoices</label></div>
<div class="form-group" style="margin-left:24px"><label>Suspend after (days overdue):</label><input name="auto_suspend_days" type="number" value="<?php echo htmlspecialchars($settings['auto_suspend_days'] ?? '7'); ?>" style="width:80px"></div>
<div class="form-group"><label><input name="auto_terminate_enabled" type="checkbox" value="1" <?php echo ($settings['auto_terminate_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>> Auto Terminate — Terminate accounts that remain suspended</label></div>
<div class="form-group" style="margin-left:24px"><label>Terminate after (days suspended):</label><input name="auto_terminate_days" type="number" value="<?php echo htmlspecialchars($settings['auto_terminate_days'] ?? '30'); ?>" style="width:80px"></div>
</div>

<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">Email Notifications</h3>
<div class="form-group"><label><input name="email_notifications_enabled" type="checkbox" value="1" <?php echo ($settings['email_notifications_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>> Enable Email Notifications</label></div>
<div class="form-group"><label>SMTP Host</label><input name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" placeholder="smtp.example.com"></div>
<div class="form-group" style="display:flex;gap:8px"><div style="flex:1"><label>SMTP Port</label><input name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>"></div><div style="flex:1"><label>From Email</label><input name="smtp_from" value="<?php echo htmlspecialchars($settings['smtp_from'] ?? 'noreply@planet-hosts.com'); ?>"></div></div>
<div class="form-group"><label>SMTP Username</label><input name="smtp_username" value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>"></div>
<div class="form-group"><label>SMTP Password</label><input name="smtp_password" type="password" value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>"></div>
<div class="form-group"><label>Admin Notification Email</label><input name="notify_admin_email" type="email" value="<?php echo htmlspecialchars($settings['notify_admin_email'] ?? 'admin@planet-hosts.com'); ?>"></div>
</div>

<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">SMS Notifications</h3>
<div class="form-group"><label><input name="sms_notifications_enabled" type="checkbox" value="1" <?php echo ($settings['sms_notifications_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>> Enable SMS Notifications</label></div>
<div class="form-group"><label>SMS Provider</label><input name="sms_provider" value="<?php echo htmlspecialchars($settings['sms_provider'] ?? ''); ?>" placeholder="twilio"></div>
<div class="form-group"><label>API Key</label><input name="sms_api_key" type="password" value="<?php echo htmlspecialchars($settings['sms_api_key'] ?? ''); ?>"></div>
<div class="form-group"><label>From Number</label><input name="sms_from" value="<?php echo htmlspecialchars($settings['sms_from'] ?? ''); ?>"></div>
<div class="form-group"><label>Admin Notification Number</label><input name="notify_admin_sms" value="<?php echo htmlspecialchars($settings['notify_admin_sms'] ?? ''); ?>" placeholder="+1234567890"></div>
</div>

<button type="submit" class="btn primary">Save All Settings</button>
</form>
