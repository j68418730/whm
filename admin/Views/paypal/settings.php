<div class="card" style="max-width:500px">
<form method="POST" action="/admin/paypal/settings/save">
<h3 style="color:var(--accent);margin-bottom:12px">PayPal Payment Gateway</h3>
<div class="form-group"><label><input name="paypal_enabled" type="checkbox" value="1" <?php echo $paypal_enabled==='1'?'checked':''; ?>> Enable PayPal Payments</label></div>
<div class="form-group"><label>PayPal Email (business)</label><input name="paypal_email" type="email" value="<?php echo htmlspecialchars($paypal_email); ?>" placeholder="merchant@example.com"></div>
<div class="form-group"><label>Client ID (REST API)</label><input name="paypal_client_id" value="<?php echo htmlspecialchars($paypal_client_id); ?>"></div>
<div class="form-group"><label>Secret (REST API)</label><input name="paypal_secret" type="password" value="<?php echo htmlspecialchars($paypal_secret); ?>"></div>
<div class="form-group"><label>Mode</label><select name="paypal_mode"><option value="sandbox" <?php echo $paypal_mode==='sandbox'?'selected':''; ?>>Sandbox (Test)</option><option value="live" <?php echo $paypal_mode==='live'?'selected':''; ?>>Live</option></select></div>
<button type="submit" class="btn primary">Save Settings</button>
</form></div>
