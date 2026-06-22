<h2 style="margin-bottom:16px">🔄 PHP Version Switcher</h2>

<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div><?php endif; ?>

<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">Available PHP Versions</h3>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<?php foreach ($versions as $v): ?>
<div style="background:rgba(0,140,255,.08);border:1px solid rgba(0,191,255,.12);border-radius:8px;padding:12px 20px;text-align:center">
<div style="font-size:18px;font-weight:700">PHP <?php echo $v; ?></div>
<div style="font-size:11px;color:#64748b">✓ Installed</div>
</div>
<?php endforeach; ?>
</div>
</div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Set PHP Version Per Domain</h3>
<form method="POST" action="/admin/php-switcher" style="display:flex;gap:8px;flex-wrap:wrap">
<select name="domain" required style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#e0e0e0;flex:2;min-width:180px">
<option value="">Select domain...</option>
<?php foreach ($domains as $d): if (!$d->domain) continue; ?>
<option value="<?php echo htmlspecialchars($d->domain); ?>"><?php echo htmlspecialchars($d->domain); ?> (<?php echo htmlspecialchars($d->username); ?>)</option>
<?php endforeach; ?>
</select>
<select name="version" required style="padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#e0e0e0">
<?php foreach ($versions as $v): ?>
<option value="<?php echo $v; ?>">PHP <?php echo $v; ?></option>
<?php endforeach; ?>
</select>
<button type="submit" class="btn btn-sm primary">Apply</button>
</form>
</div>
