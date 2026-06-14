<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<div class="stats-grid">
<div class="stat-card"><h3>Total IPs</h3><div class="value"><?php echo count($ips); ?></div></div>
</div>
<div class="card" style="max-width:600px;margin-bottom:20px">
<form method="POST" action="/admin/network">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="form-group"><label>IP Address</label><input name="ip_address" required placeholder="192.168.1.1"></div>
<div class="form-group"><label>Hostname</label><input name="hostname" placeholder="server.example.com"></div>
<div class="form-group"><label>Nameserver 1</label><input name="ns1" placeholder="ns1.planet-hosts.com"></div>
<div class="form-group"><label>Nameserver 2</label><input name="ns2" placeholder="ns2.planet-hosts.com"></div>
<div class="form-group"><label>Nameserver 3</label><input name="ns3" placeholder="(optional)"></div>
<div class="form-group"><label>Nameserver 4</label><input name="ns4" placeholder="(optional)"></div>
</div>
<button type="submit" class="btn primary">Add IP & Nameservers</button>
</form>
</div>
<table><tr><th>IP</th><th>Hostname</th><th>NS1</th><th>NS2</th><th>Actions</th></tr>
<?php if (!empty($ips)): foreach ($ips as $ip): ?>
<tr><td><?php echo htmlspecialchars($ip->ip_address); ?></td><td><?php echo htmlspecialchars($ip->hostname ?? '-'); ?></td>
<td><?php echo htmlspecialchars($ip->ns1 ?? '-'); ?></td><td><?php echo htmlspecialchars($ip->ns2 ?? '-'); ?></td>
<td><a href="/admin/network/delete/<?php echo $ip->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">✕</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No IPs configured yet.</td></tr>
<?php endif; ?></table>
