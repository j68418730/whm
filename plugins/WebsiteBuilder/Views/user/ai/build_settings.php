<div style="max-width:640px;margin:0 auto">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
<h3 style="margin:0"><i class="bi bi-gear" style="color:#8b5cf6"></i> Build Settings</h3>
<a href="/user/websites/ai" class="btn btn-sm secondary" style="font-size:10px">Back</a>
</div>
<p style="color:#64748b;font-size:12px;margin-bottom:16px">Configure how AI-generated websites are stored and served. These settings will be used as defaults when you run the AI Wizard.</p>
<form method="POST" action="/user/websites/ai/build-settings/save">
<div class="card" style="padding:20px;margin-bottom:14px">
<h4 style="font-size:14px;margin-bottom:14px">Directory & Domain</h4>
<div style="margin-bottom:14px">
<label style="display:block;font-size:12px;color:#94a3b8;margin-bottom:4px">Directory Name <span style="color:#ef4444">*</span></label>
<input name="directory" value="<?php echo htmlspecialchars($settings->directory ?? ''); ?>" placeholder="e.g. my-website" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:14px;outline:none">
<p style="font-size:11px;color:#64748b;margin-top:4px">Directory under your hosting root where site files will be placed.</p>
</div>
<div style="margin-bottom:14px">
<label style="display:block;font-size:12px;color:#94a3b8;margin-bottom:4px">Subdomain / Custom Domain</label>
<input name="subdomain" value="<?php echo htmlspecialchars($settings->subdomain ?? ''); ?>" placeholder="my-site.planet-hosts.com" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:14px;outline:none">
<p style="font-size:11px;color:#64748b;margin-top:4px">Leave blank to auto-generate from business name. You can use your own domain (e.g. mysite.com).</p>
</div>
<div style="margin-bottom:14px">
<label style="display:block;font-size:12px;color:#94a3b8;margin-bottom:4px">Install Path</label>
<select name="install_path" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:14px">
<option value="">Default (public_html/)</option>
<option value="subdirectory" <?php echo ($settings->install_path ?? '') === 'subdirectory' ? 'selected' : ''; ?>>Subdirectory (public_html/sitename/)</option>
<option value="custom" <?php echo ($settings->install_path ?? '') === 'custom' ? 'selected' : ''; ?>>Custom Path</option>
</select>
</div>
</div>
<div class="card" style="padding:20px;margin-bottom:14px">
<h4 style="font-size:14px;margin-bottom:14px">Environment</h4>
<div style="margin-bottom:10px">
<label style="display:block;font-size:12px;color:#94a3b8;margin-bottom:4px">PHP Version</label>
<select name="php_version" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:14px">
<option value="8.3" <?php echo ($settings->php_version ?? '8.3') === '8.3' ? 'selected' : ''; ?>>PHP 8.3</option>
<option value="8.2" <?php echo ($settings->php_version ?? '') === '8.2' ? 'selected' : ''; ?>>PHP 8.2</option>
<option value="8.1" <?php echo ($settings->php_version ?? '') === '8.1' ? 'selected' : ''; ?>>PHP 8.1</option>
<option value="8.0" <?php echo ($settings->php_version ?? '') === '8.0' ? 'selected' : ''; ?>>PHP 8.0</option>
</select>
</div>
</div>
<?php if (!empty($domains)): ?>
<div class="card" style="padding:20px;margin-bottom:14px">
<h4 style="font-size:14px;margin-bottom:10px">Your Domains</h4>
<p style="font-size:11px;color:#64748b;margin-bottom:8px">Select an existing domain to point your site to:</p>
<div style="display:flex;flex-wrap:wrap;gap:6px">
<?php foreach ($domains as $d): ?>
<label style="display:flex;align-items:center;gap:6px;font-size:12px;padding:6px 10px;background:rgba(0,0,0,.15);border-radius:6px;cursor:pointer">
<input type="radio" name="selected_domain" value="<?php echo htmlspecialchars($d->domain ?? $d->name ?? ''); ?>" <?php echo ($settings->subdomain ?? '') === ($d->domain ?? $d->name ?? '') ? 'checked' : ''; ?>>
<?php echo htmlspecialchars($d->domain ?? $d->name ?? ''); ?>
</label>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>
<button type="submit" class="btn primary" style="padding:12px 32px;font-size:14px;font-weight:600"><i class="bi bi-check-lg"></i> Save Settings</button>
</form>
</div>
