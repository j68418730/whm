<div style="max-width:640px;margin:0 auto">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
<h3 style="margin:0"><i class="bi bi-gear" style="color:#8b5cf6"></i> Build Settings</h3>
<a href="/user/websites/ai" class="btn btn-sm secondary" style="font-size:10px">Back</a>
</div>
<p style="color:#64748b;font-size:12px;margin-bottom:16px">Configure how AI-generated websites are stored and served.</p>
<form method="POST" action="/user/websites/ai/build-settings/save">
<div class="card" style="padding:20px;margin-bottom:14px">
<h4 style="font-size:14px;margin-bottom:14px">Directory</h4>
<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:10px">
<input name="directory" id="dirInput" value="<?php echo htmlspecialchars($settings->directory ?? ''); ?>" placeholder="e.g. my-website" style="flex:1;min-width:180px;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:14px;outline:none">
<button type="button" class="btn btn-sm secondary" style="font-size:11px" onclick="document.getElementById('dirPicker').style.display=document.getElementById('dirPicker').style.display==='none'?'block':'none'">Browse</button>
</div>
<div id="dirPicker" style="display:none;margin-bottom:10px">
<?php if (!empty($existingDirs)): ?>
<label style="font-size:12px;color:#94a3b8;margin-bottom:6px;display:block">Existing directories in your hosting root:</label>
<div style="display:flex;flex-wrap:wrap;gap:6px">
<?php foreach ($existingDirs as $dir): ?>
<label style="display:flex;align-items:center;gap:6px;font-size:12px;padding:6px 10px;background:rgba(0,0,0,.15);border-radius:6px;cursor:pointer;transition:.2s" onmouseover="this.style.background='rgba(139,92,246,.2)'" onmouseout="this.style.background='rgba(0,0,0,.15)'">
<input type="radio" name="dir_sel" value="<?php echo htmlspecialchars($dir); ?>" <?php echo ($settings->directory ?? '') === $dir ? 'checked' : ''; ?> onclick="document.getElementById('dirInput').value=this.value">
<?php echo htmlspecialchars($dir); ?>
</label>
<?php endforeach; ?>
</div>
<p style="font-size:11px;color:#64748b;margin-top:6px">Click a directory to select it, or type a new name above.</p>
<?php else: ?>
<p style="font-size:11px;color:#64748b">No existing directories found. Type a name above to create a new one.</p>
<?php endif; ?>
</div>
</div>

<div class="card" style="padding:20px;margin-bottom:14px">
<h4 style="font-size:14px;margin-bottom:14px">Subdomain / Domain</h4>
<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:10px">
<input name="subdomain" id="subInput" value="<?php echo htmlspecialchars($settings->subdomain ?? ''); ?>" placeholder="my-site.planet-hosts.com" style="flex:1;min-width:180px;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:14px;outline:none">
<button type="button" class="btn btn-sm secondary" style="font-size:11px" onclick="document.getElementById('subPicker').style.display=document.getElementById('subPicker').style.display==='none'?'block':'none'">Browse</button>
</div>
<div id="subPicker" style="display:none;margin-bottom:10px">
<?php if (!empty($subdomainRecords)): ?>
<label style="font-size:12px;color:#94a3b8;margin-bottom:6px;display:block">Your existing subdomains:</label>
<div style="display:flex;flex-wrap:wrap;gap:6px">
<?php foreach ($subdomainRecords as $sr): $fullSub = $sr->name . '.' . ($sr->domain ?? 'planet-hosts.com'); ?>
<label style="display:flex;align-items:center;gap:6px;font-size:12px;padding:6px 10px;background:rgba(0,0,0,.15);border-radius:6px;cursor:pointer;transition:.2s" onmouseover="this.style.background='rgba(139,92,246,.2)'" onmouseout="this.style.background='rgba(0,0,0,.15)'">
<input type="radio" name="sub_sel" value="<?php echo htmlspecialchars($fullSub); ?>" <?php echo ($settings->subdomain ?? '') === $fullSub ? 'checked' : ''; ?> onclick="document.getElementById('subInput').value=this.value">
<?php echo htmlspecialchars($fullSub); ?>
</label>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php if (!empty($domains)): ?>
<label style="font-size:12px;color:#94a3b8;margin:8px 0 6px;display:block">Your registered domains:</label>
<div style="display:flex;flex-wrap:wrap;gap:6px">
<?php foreach ($domains as $d): $domainVal = $d->domain ?? $d->name ?? ''; ?>
<label style="display:flex;align-items:center;gap:6px;font-size:12px;padding:6px 10px;background:rgba(0,0,0,.15);border-radius:6px;cursor:pointer;transition:.2s" onmouseover="this.style.background='rgba(139,92,246,.2)'" onmouseout="this.style.background='rgba(0,0,0,.15)'">
<input type="radio" name="sub_sel" value="<?php echo htmlspecialchars($domainVal); ?>" <?php echo ($settings->subdomain ?? '') === $domainVal ? 'checked' : ''; ?> onclick="document.getElementById('subInput').value=this.value">
<?php echo htmlspecialchars($domainVal); ?>
</label>
<?php endforeach; ?>
</div>
<?php endif; ?>
<p style="font-size:11px;color:#64748b;margin-top:6px">Click to select, or type a custom domain above. Leave blank to auto-generate from business name.</p>
</div>
</div>

<div class="card" style="padding:20px;margin-bottom:14px">
<h4 style="font-size:14px;margin-bottom:14px">Install Path & Environment</h4>
<div style="margin-bottom:10px">
<label style="display:block;font-size:12px;color:#94a3b8;margin-bottom:4px">Install Path</label>
<select name="install_path" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:14px">
<option value="">Default (public_html/)</option>
<option value="subdirectory" <?php echo ($settings->install_path ?? '') === 'subdirectory' ? 'selected' : ''; ?>>Subdirectory (public_html/sitename/)</option>
<option value="custom" <?php echo ($settings->install_path ?? '') === 'custom' ? 'selected' : ''; ?>>Custom Path</option>
</select>
</div>
<div style="margin-bottom:10px">
<label style="display:block;font-size:12px;color:#94a3b8;margin-bottom:4px">PHP Version</label>
<select name="php_version" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:14px">
<option value="8.3" <?php echo ($settings->php_version ?? '8.3') === '8.3' ? 'selected' : ''; ?>>PHP 8.3</option>
<option value="8.2" <?php echo ($settings->php_version ?? '') === '8.2' ? 'selected' : ''; ?>>PHP 8.2</option>
<option value="8.1" <?php echo ($settings->php_version ?? '') === '8.1' ? 'selected' : ''; ?>>PHP 8.1</option>
</select>
</div>
</div>

<button type="submit" class="btn primary" style="padding:12px 32px;font-size:14px;font-weight:600"><i class="bi bi-check-lg"></i> Save Settings</button>
</form>
</div>
