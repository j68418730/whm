<div class="card" style="max-width:500px">
<form method="POST" action="/admin/settings/smtp/save">
<h3 style="color:var(--accent);margin-bottom:12px">SMTP Settings</h3>
<div class="form-group"><label><input name="smtp_enabled" type="checkbox" value="1" <?php echo $smtp_enabled==='1'?'checked':''; ?>> Enable SMTP</label></div>
<div class="form-group"><label>SMTP Host</label><input name="smtp_host" value="<?php echo htmlspecialchars($smtp_host); ?>" placeholder="smtp.example.com"></div>
<div class="form-group" style="display:flex;gap:8px"><div style="flex:1"><label>Port</label><input name="smtp_port" value="<?php echo htmlspecialchars($smtp_port); ?>"></div><div style="flex:1"><label>Encryption</label><select name="smtp_encryption"><option value="tls" <?php echo $smtp_encryption==='tls'?'selected':''; ?>>TLS</option><option value="ssl" <?php echo $smtp_encryption==='ssl'?'selected':''; ?>>SSL</option><option value="none" <?php echo $smtp_encryption==='none'?'selected':''; ?>>None</option></select></div></div>
<div class="form-group"><label>Username</label><input name="smtp_username" value="<?php echo htmlspecialchars($smtp_username); ?>"></div>
<div class="form-group"><label>Password</label><input name="smtp_password" type="password" value="<?php echo htmlspecialchars($smtp_password); ?>"></div>
<div class="form-group"><label>From Email</label><input name="smtp_from" type="email" value="<?php echo htmlspecialchars($smtp_from); ?>"></div>
<button type="submit" class="btn primary">Save</button>
<a href="/admin/settings" class="btn secondary">Back</a>
</form></div>
