<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
<h3 style="margin:0">Pricing &amp; Margins — <?php echo htmlspecialchars($reseller->company_name); ?></h3>
<a href="/admin/reseller/show/<?php echo $reseller->id; ?>" class="btn btn-sm secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
<form method="POST" action="/admin/reseller/pricing/save/<?php echo $reseller->id; ?>">
<div class="card" style="max-width:700px">
<div class="form-group"><label>Margin Mode</label>
<select name="markup_mode" style="width:100%">
<option value="percent" <?php echo ($reseller->markup_mode ?? 'percent')==='percent'?'selected':''; ?>>Percent markup</option>
<option value="fixed" <?php echo ($reseller->markup_mode ?? '')==='fixed'?'selected':''; ?>>Fixed $ markup</option>
</select>
</div>
<?php
$margins = [
    ['hosting_margin','🌐 Hosting margin', $reseller->hosting_margin ?? 0],
    ['radio_margin','🎵 Radio/streaming margin', $reseller->radio_margin ?? 0],
    ['vps_margin','🖥 VPS margin', $reseller->vps_margin ?? 0],
    ['game_margin','🎮 Game server margin', $reseller->game_margin ?? 0],
    ['domain_margin','🌍 Domain margin', $reseller->domain_margin ?? 0],
];
foreach ($margins as $m): ?>
<div class="form-group" style="display:flex;align-items:center;justify-content:space-between;gap:12px;max-width:520px">
<label style="margin:0;color:#94a3b8"><?php echo $m[1]; ?></label>
<input name="<?php echo $m[0]; ?>" type="number" step="0.01" value="<?php echo number_format((float)$m[2],2); ?>" style="width:110px;text-align:right">
</div>
<?php endforeach; ?>
<p style="font-size:12px;color:#64748b;max-width:600px">Margins apply to the reseller's retail price over the Planet Hosts cost. With "percent" mode they are added as a % on top of the Planet Hosts product price; with "fixed" mode they are a flat $ add-on.</p>
<button type="submit" class="btn primary"><i class="bi bi-check-lg"></i> Save</button>
</div>
</form>