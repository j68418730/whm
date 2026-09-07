<?php $currentTab = 'smtp'; require __DIR__ . '/_tabs.php'; ?>
<div class="set-wrap">
<div class="set-head">
  <h2>📧 SMTP Settings</h2>
  <p>Outgoing mail relay for client emails, invoices and notifications. Leave disabled to send via the local Postfix server.</p>
</div>

<div class="set-grid">
  <div>
    <div class="set-card">
      <h4><i class="bi bi-envelope-gear"></i> Relay Configuration</h4>
      <form class="set-form" method="POST" action="/admin/settings/smtp/save">
        <label class="set-toggle">
          <input type="hidden" name="smtp_enabled" value="0">
          <input type="checkbox" name="smtp_enabled" value="1" <?php echo $smtp_enabled==='1'?'checked':''; ?>>
          <span>Use external SMTP relay<span class="tdesc">Off = mail is sent by the server's own Postfix (default)</span></span>
        </label>
        <div class="set-2col">
          <div class="form-group" style="grid-column:1/-1">
            <label>SMTP Host</label>
            <input name="smtp_host" value="<?php echo htmlspecialchars($smtp_host); ?>" placeholder="smtp.example.com">
          </div>
        </div>
        <div class="set-2col">
          <div class="form-group">
            <label>Port</label>
            <input name="smtp_port" value="<?php echo htmlspecialchars($smtp_port); ?>" placeholder="587">
          </div>
          <div class="form-group">
            <label>Encryption</label>
            <select name="smtp_encryption">
              <option value="tls" <?php echo $smtp_encryption==='tls'?'selected':''; ?>>TLS (recommended)</option>
              <option value="ssl" <?php echo $smtp_encryption==='ssl'?'selected':''; ?>>SSL</option>
              <option value="none" <?php echo $smtp_encryption==='none'?'selected':''; ?>>None</option>
            </select>
          </div>
        </div>
        <div class="set-2col">
          <div class="form-group">
            <label>Username</label>
            <input name="smtp_username" value="<?php echo htmlspecialchars($smtp_username); ?>" autocomplete="off">
          </div>
          <div class="form-group">
            <label>Password</label>
            <div style="display:flex;gap:6px">
              <input name="smtp_password" type="password" id="smtp-pw" value="<?php echo htmlspecialchars($smtp_password); ?>" autocomplete="new-password" style="flex:1">
              <button type="button" onclick="var e=document.getElementById('smtp-pw');e.type=e.type==='password'?'text':'password';this.textContent=e.type==='password'?'👁':'🙈'" class="btn set-btn-ghost" style="padding:6px 10px">👁</button>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>From Email</label>
          <input name="smtp_from" type="email" value="<?php echo htmlspecialchars($smtp_from); ?>" placeholder="noreply@planet-hosts.com">
        </div>
        <div class="set-actions">
          <button type="submit" class="btn set-btn-save">💾 Save Changes</button>
          <a href="/admin/settings" class="btn set-btn-ghost">Back</a>
        </div>
      </form>
    </div>
  </div>

  <div>
    <div class="set-card">
      <h4><i class="bi bi-activity"></i> Local Mail Status</h4>
      <div class="set-kv">
        <span class="k">Postfix</span><span class="v" style="color:#4ade80">● Running (local delivery)</span>
        <span class="k">Dovecot</span><span class="v" style="color:#4ade80">● Running (webmail boxes)</span>
        <span class="k">Local Copy</span><span class="v">admin@planet-hosts.com — always kept</span>
        <span class="k">Alerts Recipient</span><span class="v"><?php echo htmlspecialchars($company_email ?? 'admin@planet-hosts.com'); ?></span>
      </div>
      <div class="set-note" style="margin-top:12px">
        <b>Attack alerts</b> and system emails are delivered via local Postfix regardless of this relay. Use the relay if you need higher deliverability to Gmail/Outlook (SPF + DKIM are already configured on this server).
      </div>
    </div>
    <div class="set-card">
      <h4><i class="bi bi-lightbulb"></i> Tips</h4>
      <div class="set-note">
        <b>Port 587 + TLS</b> is the standard auth relay port.<br>
        <b>Port 465</b> usually means SSL.<br>
        If your host requires an API-based relay (SendGrid/Postmark), the SMTP bridge still works: host <b>smtp.sendgrid.net</b>, port 587, user <b>apikey</b>, password = API key.
      </div>
    </div>
  </div>
</div>
</div>
