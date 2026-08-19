<div class="card" style="max-width:500px">
<form method="POST" action="/admin/paypal/settings/save">
<h3 style="color:var(--accent);margin-bottom:12px">PayPal Payment Gateway (REST Orders v2)</h3>
<div class="form-group"><label><input name="paypal_enabled" type="checkbox" value="1" <?php echo $paypal_enabled==='1'?'checked':''; ?>> Enable PayPal Payments</label></div>
<div class="form-group"><label>Mode</label><select name="paypal_mode"><option value="sandbox" <?php echo $paypal_mode==='sandbox'?'selected':''; ?>>Sandbox (Test)</option><option value="live" <?php echo $paypal_mode==='live'?'selected':''; ?>>Live</option></select></div>
<h4 style="color:var(--accent);margin:14px 0 8px">Sandbox (Test) — REST App</h4>
<div class="form-group"><label>Client ID</label><input name="paypal_client_id" value="<?php echo htmlspecialchars($paypal_client_id); ?>"></div>
<div class="form-group"><label>Secret Key</label><input name="paypal_secret" type="password" value="<?php echo htmlspecialchars($paypal_secret); ?>"></div>
<h4 style="color:var(--accent);margin:14px 0 8px">Live — REST App</h4>
<div class="form-group"><label>Client ID</label><input name="paypal_live_client_id" value="<?php echo htmlspecialchars($paypal_live_client_id); ?>"></div>
<div class="form-group"><label>Secret Key</label><input name="paypal_live_secret" type="password" value="<?php echo htmlspecialchars($paypal_live_secret); ?>"></div>
<div class="form-group"><label>PayPal Email (business, legacy IPN fallback)</label><input name="paypal_email" type="email" value="<?php echo htmlspecialchars($paypal_email); ?>" placeholder="merchant@example.com"></div>
<p style="font-size:12px;color:#64748b;margin-top:8px">Checkout uses the REST Orders API (Client ID/Secret). Payment is captured when the buyer returns from PayPal, and services are provisioned automatically.</p>
<button type="submit" class="btn primary">Save Settings</button>
</form></div>