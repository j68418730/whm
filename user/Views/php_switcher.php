<style>
.php-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:12px;margin-top:14px}
.php-ver{background:rgba(8,16,28,.85);border:2px solid rgba(255,255,255,.08);border-radius:12px;padding:18px 12px;text-align:center;cursor:pointer;color:#e0e0e0;font-family:inherit;transition:.2s}
.php-ver:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.4)}
.php-ver.active{border-color:var(--primary,#0A84FF);background:rgba(0,140,255,.12)}
.php-ver .num{font-size:24px;font-weight:800}
.php-ver .lbl{font-size:11px;color:#64748b;margin-top:4px}
</style>
<h2>⚙️ PHP Version Selector</h2>
<p style="color:#64748b;margin-bottom:16px">Select your preferred PHP version. Changes affect your website immediately.</p>

<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="card">
<h3 style="color:var(--accent)">Current Version: <span style="color:#4ade80">PHP <?php echo htmlspecialchars($currentVersion); ?></span></h3>
<p style="color:#64748b;font-size:13px;margin-top:4px">Choose a version below to switch your site's PHP runtime.</p>
<form method="POST">
<div class="php-grid">
<?php foreach ($versions as $v): ?>
<button type="submit" name="version" value="<?php echo htmlspecialchars($v); ?>" class="php-ver <?php echo $v === $currentVersion ? 'active' : ''; ?>">
<div class="num"><?php echo htmlspecialchars($v); ?></div>
<div class="lbl">PHP <?php echo htmlspecialchars($v); ?></div>
<?php echo $v === $currentVersion ? '<div style="font-size:10px;color:#4ade80;margin-top:4px">● In Use</div>' : ''; ?>
</button>
<?php endforeach; ?>
</div>
</form>
<div style="margin-top:14px;padding:10px;background:rgba(250,204,21,.06);border:1px solid rgba(250,204,21,.2);border-radius:8px;font-size:12px;color:#facc15">
⚠️ Changing PHP version may break your site if your code uses version-specific features or extensions.
</div>
</div>