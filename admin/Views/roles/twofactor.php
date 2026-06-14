<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<div class="card" style="max-width:500px">
<h3 style="color:var(--accent);margin-bottom:16px">Two-Factor Authentication</h3>
<?php if ($secret && $secret->enabled): ?>
<div style="background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.2);border-radius:8px;padding:16px;margin-bottom:16px">
<p style="color:#4ade80;font-weight:600">✅ 2FA is enabled</p>
<p style="color:var(--text-muted);font-size:13px;margin-top:8px">Secret: <code style="background:rgba(255,255,255,.06);padding:2px 6px;border-radius:4px"><?php echo htmlspecialchars($secret->secret); ?></code></p>
<p style="color:var(--text-muted);font-size:12px;margin-top:4px">Add this secret to Google Authenticator or Authy.</p>
</div>
<a href="/admin/twofactor/disable" class="btn danger">Disable 2FA</a>
<?php else: ?>
<p style="color:var(--text-secondary);margin-bottom:16px">Two-factor authentication adds an extra layer of security to your admin account.</p>
<a href="/admin/twofactor/enable" class="btn primary">Enable 2FA</a>
<?php endif; ?>
</div>
