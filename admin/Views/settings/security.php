<?php $currentTab = 'security'; require __DIR__ . '/_tabs.php'; ?>
<div class="card" style="max-width:500px">
<form method="POST" action="/admin/settings/security/save">
<h3 style="color:var(--accent);margin-bottom:12px">Security Settings</h3>
<div class="form-group"><label>Minimum Password Length</label><input name="min_password_length" type="number" value="<?php echo htmlspecialchars($min_password_length); ?>"></div>
<div class="form-group"><label>Max Login Attempts (before lockout)</label><input name="max_login_attempts" type="number" value="<?php echo htmlspecialchars($max_login_attempts); ?>"></div>
<div class="form-group"><label>Session Timeout (minutes)</label><input name="session_timeout" type="number" value="<?php echo htmlspecialchars($session_timeout); ?>"></div>
<div class="form-group"><label><input name="require_ssl" type="checkbox" value="1" <?php echo $require_ssl==='1'?'checked':''; ?>> Require SSL for all services</label></div>
<div class="form-group"><label><input name="twofactor_required" type="checkbox" value="1" <?php echo $twofactor_required==='1'?'checked':''; ?>> Require 2FA for all admins</label></div>
<div class="form-group"><label>Admin Notification Email</label><input name="notify_admin_email" type="email" value="<?php echo htmlspecialchars($notify_admin_email); ?>"></div>
<button type="submit" class="btn primary">Save</button>
<a href="/admin/settings" class="btn secondary">Back</a>
</form></div>

