<?php $currentTab = 'security'; require __DIR__ . '/_tabs.php'; ?>
<div class="set-wrap">
<div class="set-head">
  <h2>🔒 Security Settings</h2>
  <p>Access policy, enforcement rules and security notifications.</p>
</div>

<form class="set-form" method="POST" action="/admin/settings/security/save">
<div class="set-grid">
  <div>
    <div class="set-card">
      <h4><i class="bi bi-shield-lock"></i> Password &amp; Login Policy</h4>
      <div class="set-3col">
        <div class="form-group">
          <label>Min Password Length</label>
          <input name="min_password_length" type="number" min="6" max="64" value="<?php echo htmlspecialchars($min_password_length); ?>">
        </div>
        <div class="form-group">
          <label>Max Login Attempts</label>
          <input name="max_login_attempts" type="number" min="1" max="20" value="<?php echo htmlspecialchars($max_login_attempts); ?>">
          <small>Lockout threshold</small>
        </div>
        <div class="form-group">
          <label>Session Timeout</label>
          <input name="session_timeout" type="number" min="5" max="1440" value="<?php echo htmlspecialchars($session_timeout); ?>">
          <small>minutes</small>
        </div>
      </div>
    </div>

    <div class="set-card">
      <h4><i class="bi bi-patch-check"></i> Enforcement</h4>
      <label class="set-toggle">
        <input type="hidden" name="require_ssl" value="0">
        <input type="checkbox" name="require_ssl" value="1" <?php echo $require_ssl==='1'?'checked':''; ?>>
        <span>Require SSL for all services<span class="tdesc">Force HTTPS/SSL panel and client connections</span></span>
      </label>
      <label class="set-toggle">
        <input type="hidden" name="twofactor_required" value="0">
        <input type="checkbox" name="twofactor_required" value="1" <?php echo $twofactor_required==='1'?'checked':''; ?>>
        <span>Require 2FA for all admins<span class="tdesc">Second factor mandatory at admin login</span></span>
      </label>
    </div>
  </div>

  <div>
    <div class="set-card">
      <h4><i class="bi bi-envelope-exclamation"></i> Security Alerts</h4>
      <div class="form-group">
        <label>Admin Notification Email</label>
        <input name="notify_admin_email" type="email" value="<?php echo htmlspecialchars($notify_admin_email); ?>">
        <small>Security warnings and admin notices.</small>
      </div>
      <div class="set-note" style="margin-bottom:12px">
        <b>Attack alerts</b> (brute force, Fail2Ban bans, unknown SSH logins) are emailed automatically every 5 minutes to the <b>Company Email</b> set under the Company tab — the ph-attack-alert service handles it.
      </div>
    </div>

    <div class="set-card">
      <h4><i class="bi bi-cpu"></i> Active Defenses</h4>
      <div class="set-kv">
        <span class="k">Fail2Ban</span><span class="v" style="color:#4ade80">● permanent bans after 3 attempts</span>
        <span class="k">Attack Alerts</span><span class="v" style="color:#4ade80">● every 5 min via email</span>
        <span class="k">Log Watchdog</span><span class="v" style="color:#4ade80">● every 15 min, auto-truncate</span>
        <span class="k">CSRF</span><span class="v">token validation on POST</span>
      </div>
      <div class="set-actions">
        <button type="submit" class="btn set-btn-save">💾 Save Changes</button>
        <a href="/admin/settings" class="btn set-btn-ghost">Back</a>
      </div>
    </div>
  </div>
</div>
</form>
</div>
