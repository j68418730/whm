<div class="card"><h3 style="color:var(--accent)">Current Package</h3>
<?php if ($package): ?>
<p style="color:var(--text-secondary);margin-top:8px"><strong>Name:</strong> <?php echo htmlspecialchars($package->name); ?><br>
<strong>Bitrate:</strong> <?php echo $package->bitrate ?: 'N/A'; ?> kbps<br>
<strong>Listeners:</strong> <?php echo $package->listener_limit ?: 'N/A'; ?><br>
<strong>Storage:</strong> <?php echo $package->storage_limit ?: 'N/A'; ?> GB<br>
<strong>Price:</strong> $<?php echo number_format($package->monthly_price, 2); ?>/mo</p>
<?php else: ?><p style="color:#64748b">No package assigned.</p>
<?php endif; ?></div>
