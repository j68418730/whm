<?php $currentTab = 'localization'; require __DIR__ . '/_tabs.php'; ?>
<div class="card" style="max-width:500px">
<form method="POST" action="/admin/settings/localization/save">
<h3 style="color:var(--accent);margin-bottom:12px">Localization</h3>
<div class="form-group"><label>Language</label><select name="language"><option value="en" <?php echo $language==='en'?'selected':''; ?>>English</option><option value="es" <?php echo $language==='es'?'selected':''; ?>>Spanish</option><option value="fr" <?php echo $language==='fr'?'selected':''; ?>>French</option><option value="de" <?php echo $language==='de'?'selected':''; ?>>German</option></select></div>
<div class="form-group" style="display:flex;gap:8px"><div style="flex:1"><label>Date Format</label><input name="date_format" value="<?php echo htmlspecialchars($date_format); ?>"></div><div style="flex:1"><label>Time Format</label><input name="time_format" value="<?php echo htmlspecialchars($time_format); ?>"></div></div>
<div class="form-group" style="display:flex;gap:8px"><div style="flex:1"><label>Currency</label><select name="currency"><option value="USD" <?php echo $currency==='USD'?'selected':''; ?>>USD</option><option value="EUR" <?php echo $currency==='EUR'?'selected':''; ?>>EUR</option><option value="GBP" <?php echo $currency==='GBP'?'selected':''; ?>>GBP</option><option value="CAD" <?php echo $currency==='CAD'?'selected':''; ?>>CAD</option></select></div><div style="flex:1"><label>Symbol</label><input name="currency_symbol" value="<?php echo htmlspecialchars($currency_symbol); ?>" style="width:60px"></div></div>
<div class="form-group"><label>Timezone</label><select name="timezone"><?php foreach (timezone_identifiers_list() as $tz): ?><option value="<?php echo $tz; ?>" <?php echo $tz===$timezone?'selected':''; ?>><?php echo $tz; ?></option><?php endforeach; ?></select></div>
<button type="submit" class="btn primary">Save</button>
<a href="/admin/settings" class="btn secondary">Back</a>
</form></div>

