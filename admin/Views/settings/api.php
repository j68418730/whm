<div class="card" style="max-width:500px">
<form method="POST" action="/admin/settings/api/save">
<h3 style="color:var(--accent);margin-bottom:12px">API Settings</h3>
<div class="form-group"><label><input name="api_enabled" type="checkbox" value="1" <?php echo $api_enabled==='1'?'checked':''; ?>> Enable REST API</label></div>
<div class="form-group"><label>Default Rate Limit (req/min)</label><input name="api_rate_limit_default" type="number" value="<?php echo htmlspecialchars($api_rate_limit_default); ?>"></div>
<div class="form-group"><label><input name="api_debug_mode" type="checkbox" value="1" <?php echo $api_debug_mode==='1'?'checked':''; ?>> Debug Mode (verbose error responses)</label></div>
<button type="submit" class="btn primary">Save</button>
<a href="/admin/settings" class="btn secondary">Back</a>
</form></div>
