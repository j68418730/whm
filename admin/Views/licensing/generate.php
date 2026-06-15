<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<?php if (!$hasPrivateKey): ?>
<div class="alert alert-error" style="background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);color:#f87171;padding:12px;border-radius:8px;margin-bottom:16px">Private key not found on this server. Key generation is only available on the master license server.</div>
<?php endif; ?>

<div class="card" style="max-width:550px;margin-bottom:20px">
<form method="POST" action="/admin/licensing/generate">
<h3 style="color:var(--accent);margin-bottom:12px">Generate License Key</h3>
<div class="form-group"><label>Licensee Name</label><input name="licensee" required value="<?php echo htmlspecialchars($_POST['licensee'] ?? ''); ?>" placeholder="Customer or Company Name"></div>
<div class="form-group"><label>License ID</label><input name="license_id" value="<?php echo htmlspecialchars($_POST['license_id'] ?? ('LICS-' . date('Y') . '-0001')); ?>" placeholder="LICS-2026-0001"></div>
<div class="form-group"><label>Expiry Date</label><input name="expiry" type="date" value="<?php echo htmlspecialchars($_POST['expiry'] ?? date('Y-m-d', strtotime('+1 year'))); ?>"><br><small style="color:var(--text-secondary)">Leave blank or enter any date. Use <strong>never</strong> for lifetime.</small></div>
<div class="form-group"><label>License Type</label>
<select name="type">
<option value="full" <?php echo ($_POST['type'] ?? '') === 'full' ? 'selected' : ''; ?>>Full — Everything</option>
<option value="hosting" <?php echo ($_POST['type'] ?? '') === 'hosting' ? 'selected' : ''; ?>>Hosting — Accounts, DNS, Email, FTP, Databases, SSL</option>
<option value="icecast" <?php echo ($_POST['type'] ?? '') === 'icecast' ? 'selected' : ''; ?>>Icecast — Radio Streaming, AutoDJ, Transcoding</option>
</select>
</div>
<button type="submit" class="btn primary">Generate License</button>
</form>
</div>

<?php if ($generatedKey): ?>
<div class="card"><h3 style="color:var(--accent);margin-bottom:8px">Generated License Key</h3>
<p style="color:var(--text-secondary);font-size:12px;margin-bottom:8px">Copy the entire block below. Provide this to the customer. They upload or paste it in their panel's Licensing page.</p>
<textarea readonly style="width:100%;height:180px;font-family:monospace;font-size:11px;background:rgba(0,0,0,.3);border:1px solid rgba(0,191,255,.2);color:#4ade80;border-radius:6px;padding:10px" onclick="this.select()"><?php echo htmlspecialchars($generatedKey); ?></textarea>
<div style="display:flex;gap:8px;margin-top:8px">
<button class="btn primary" onclick="navigator.clipboard.writeText(document.querySelector('textarea').value)">Copy to Clipboard</button>
<a href="/admin/licensing" class="btn secondary">Back to Licensing</a>
</div>
</div>
<?php endif; ?>
