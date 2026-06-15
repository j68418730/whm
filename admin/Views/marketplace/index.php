<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<div style="margin-bottom:16px"><a href="/admin/marketplace/pricing" class="btn secondary">💰 Set Prices</a></div>
<?php foreach ($categories as $cat):
$items = array_filter($grouped[$cat] ?? [], fn($a) => !empty($a)); if (empty($items)) continue; ?>
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);font-size:16px;margin-bottom:12px"><?php echo htmlspecialchars($cat); ?></h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px">
<?php foreach ($items as $app): ?>
<div style="background:rgba(255,255,255,.03);border:1px solid rgba(0,191,255,.1);border-radius:10px;padding:16px;text-align:center">
<div style="font-size:32px;margin-bottom:6px"><?php echo htmlspecialchars($app->icon ?? '📦'); ?></div>
<div style="font-weight:600;font-size:14px"><?php echo htmlspecialchars($app->name); ?></div>
<div style="color:var(--text-muted);font-size:11px;margin:4px 0">v<?php echo htmlspecialchars($app->version ?? '1.0'); ?></div>
<div style="color:var(--accent);font-size:16px;font-weight:700;margin-bottom:8px"><?php echo ($app->price ?? 0) > 0 ? '$' . number_format($app->price, 2) : 'Free'; ?></div>
<form method="POST" action="/admin/marketplace/install/<?php echo $app->id; ?>" style="display:flex;flex-direction:column;gap:6px">
<select name="account_id" style="width:100%;padding:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:11px;outline:none">
<option value="">— New account —</option>
<?php foreach ($accounts as $a): ?>
<option value="<?php echo $a->id; ?>"><?php echo htmlspecialchars($a->domain ?: $a->username); ?> (<?php echo htmlspecialchars($a->username); ?>)</option>
<?php endforeach; ?>
</select>
<input name="username" placeholder="Or enter new username" style="width:100%;padding:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:11px;text-align:center;outline:none">
<button type="submit" class="btn btn-sm primary" style="width:100%">Install</button>
</form>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>
