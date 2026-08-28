<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
<h3 style="margin:0">Resource Limits — <?php echo htmlspecialchars($reseller->company_name); ?></h3>
<a href="/admin/reseller/show/<?php echo $reseller->id; ?>" class="btn btn-sm secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
<form method="POST" action="/admin/reseller/resources/save/<?php echo $reseller->id; ?>">
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;max-width:1100px">
<?php
$limits = [
    ['customers_limit','👥 Customers', $reseller->customers_limit ?? 0, $usage['customers'] ?? 0],
    ['hosting_limit','🌐 Hosting accounts', $reseller->hosting_limit ?? 0, $usage['active_services'] ?? 0],
    ['storage_limit','💾 Storage (KB)', $reseller->storage_limit ?? 0, null],
    ['bandwidth_limit','📶 Bandwidth (KB)', $reseller->bandwidth_limit ?? 0, null],
    ['database_limit','🗄 Databases', $reseller->database_limit ?? 0, null],
    ['domain_limit','🌍 Domains', $reseller->domain_limit ?? 0, null],
    ['vps_limit','🖥 VPS', $reseller->vps_limit ?? 0, null],
    ['game_server_limit','🎮 Game Servers', $reseller->game_server_limit ?? 0, null],
    ['radio_station_limit','🎵 Radio Stations', $reseller->radio_station_limit ?? 0, null],
];
foreach ($limits as $lm): ?>
<div class="card" style="margin-bottom:0">
<label style="font-size:12px;color:#64748b;font-weight:700"><?php echo $lm[1]; ?></label>
<input type="number" name="<?php echo $lm[0]; ?>" value="<?php echo (int)$lm[2]; ?>" min="0" style="width:100%;margin-top:6px">
<?php if ($lm[3] !== null): ?><div style="font-size:11px;color:#4ade80;margin-top:4px">in use: <?php echo (int)$lm[3]; ?></div><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<div style="margin-top:16px;max-width:1100px">
<button type="submit" class="btn primary"><i class="bi bi-check-lg"></i> Save Limits</button>
</div>
</form>