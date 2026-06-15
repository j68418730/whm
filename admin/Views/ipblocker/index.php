<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Blocked IPs</h3><div class="value"><?php echo count($blocks); ?></div></div>
</div>

<div class="card" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/ipblocker/store">
<h3 style="color:var(--accent);margin-bottom:8px">Block an IP</h3>
<div class="form-group"><label>IP Address</label><input name="ip_address" required placeholder="192.168.1.100"></div>
<div class="form-group"><label>Reason (optional)</label><input name="notes" placeholder="Spam, abuse, etc."></div>
<button type="submit" class="btn primary">Block IP</button>
</form></div>

<table><tr><th>IP Address</th><th>Reason</th><th>Blocked Date</th><th></th></tr>
<?php if (!empty($blocks)): foreach ($blocks as $b): ?>
<tr><td style="font-family:monospace"><?php echo htmlspecialchars($b->ip_address); ?></td>
<td><?php echo htmlspecialchars($b->notes ?: '-'); ?></td>
<td><?php echo $b->created_at; ?></td>
<td><a href="/admin/ipblocker/delete/<?php echo $b->id; ?>" class="btn btn-sm primary">Unblock</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No IPs blocked.</td></tr>
<?php endif; ?></table>
