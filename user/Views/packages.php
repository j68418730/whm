<div class="card"><h3 style="color:var(--accent)">Current Package</h3>
<?php if ($package):
$pf = is_string($package->features ?? null) ? json_decode($package->features, true) ?? [] : ($package->features ?? []);
$sp = $pf['streaming_package'] ?? [];
?>
<p style="color:var(--text-secondary);margin-top:8px"><strong>Name:</strong> <?php echo htmlspecialchars($package->name); ?><br>
<strong>Bitrate:</strong> <?php echo ($sp['max_bitrate'] ?? 'N/A'); ?> kbps<br>
<strong>Listeners:</strong> <?php echo ($sp['max_listeners'] ?? 'N/A'); ?><br>
<strong>Storage:</strong> <?php echo ($sp['upload_limit'] ?? 'N/A'); ?> MB<br>
<strong>Price:</strong> $<?php echo number_format($package->monthly_price, 2); ?>/mo</p>
<?php else: ?><p style="color:#64748b">No package assigned.</p>
<?php endif; ?></div>
