<?php $currentTab = 'api'; require __DIR__ . '/_tabs.php'; ?>
<div class="card" style="max-width:500px">
<form method="POST" action="/admin/settings/api/save">
<h3 style="color:var(--accent);margin-bottom:12px">API Settings</h3>
<div class="form-group"><label><input name="api_enabled" type="checkbox" value="1" <?php echo $api_enabled==='1'?'checked':''; ?>> Enable REST API</label></div>
<div class="form-group"><label>Default Rate Limit (req/min)</label><input name="api_rate_limit_default" type="number" value="<?php echo htmlspecialchars($api_rate_limit_default); ?>"></div>
<div class="form-group"><label><input name="api_debug_mode" type="checkbox" value="1" <?php echo $api_debug_mode==='1'?'checked':''; ?>> Debug Mode (verbose error responses)</label></div>
<div class="form-group"><label>OpenAI API Key</label>
<input name="openai_api_key" type="password" value="<?php echo htmlspecialchars($openai_api_key ?? ''); ?>" placeholder="sk-..." style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;outline:none">
<div style="font-size:11px;color:#64748b;margin-top:4px">Required for AI Website Generator. Get your key at <a href="https://platform.openai.com/api-keys" target="_blank" style="color:#38bdf8">platform.openai.com</a></div>
</div>
<button type="submit" class="btn primary">Save</button>
<a href="/admin/settings" class="btn secondary">Back</a>
</form></div>

