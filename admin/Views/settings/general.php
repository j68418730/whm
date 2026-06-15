<div class="card" style="max-width:500px">
<form method="POST" action="/admin/settings/general/save">
<h3 style="color:var(--accent);margin-bottom:12px">General Settings</h3>
<div class="form-group"><label>Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($hostname); ?>"></div>
<div class="form-group"><label>Timezone</label><select name="timezone"><?php foreach (timezone_identifiers_list() as $tz): ?><option value="<?php echo $tz; ?>" <?php echo $tz===$timezone?'selected':''; ?>><?php echo $tz; ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>Default Language</label><select name="language"><option value="en" <?php echo $language==='en'?'selected':''; ?>>English</option><option value="es" <?php echo $language==='es'?'selected':''; ?>>Spanish</option><option value="fr" <?php echo $language==='fr'?'selected':''; ?>>French</option><option value="de" <?php echo $language==='de'?'selected':''; ?>>German</option></select></div>
<button type="submit" class="btn primary">Save</button>
<a href="/admin/settings" class="btn secondary">Back</a>
</form></div>
