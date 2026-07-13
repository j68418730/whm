<?php $currentTab = 'general'; require __DIR__ . '/_tabs.php'; ?>
<div class="card" style="max-width:500px">
<form method="POST" action="/admin/settings/general/save">
<h3 style="color:var(--accent);margin-bottom:12px">General Settings</h3>
<div class="form-group"><label>Hostname</label><input name="hostname" value="<?php echo htmlspecialchars($hostname); ?>"></div>
<div class="form-group"><label>Timezone</label><select name="timezone"><?php foreach (timezone_identifiers_list() as $tz): ?><option value="<?php echo $tz; ?>" <?php echo $tz===$timezone?'selected':''; ?>><?php echo $tz; ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>Default Language</label><select name="language"><option value="en" <?php echo $language==='en'?'selected':''; ?>>English</option><option value="es" <?php echo $language==='es'?'selected':''; ?>>Spanish</option><option value="fr" <?php echo $language==='fr'?'selected':''; ?>>French</option><option value="de" <?php echo $language==='de'?'selected':''; ?>>German</option></select></div>
<button type="submit" class="btn primary">Save</button>
<a href="/admin/settings" class="btn secondary">Back</a>
</form>
</div>
<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:16px">Server IPs</h3>
<p style="color:#64748b;font-size:13px;margin-bottom:12px">Manage server IP addresses and see which are in use.</p>
<?php
$ipList = [];
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT * FROM server_ips ORDER BY server, ip");
    if ($q) $ipList = $q->fetchAll(PDO::FETCH_OBJ);
} catch (\Exception $e) {}
$totalIps = count($ipList);
$usedIps = count(array_filter($ipList, fn($i) => !empty($i->assigned_to)));
?>
<div class="stats-grid" style="margin-bottom:16px">
<div class="stat-card"><h3>Total IPs</h3><div class="value"><?php echo $totalIps; ?></div></div>
<div class="stat-card"><h3>In Use</h3><div class="value" style="color:#facc15"><?php echo $usedIps; ?></div></div>
<div class="stat-card"><h3>Available</h3><div class="value" style="color:#4ade80"><?php echo $totalIps - $usedIps; ?></div></div>
</div>
<form method="POST" action="/admin/ip/store" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;margin-bottom:16px;padding:16px;background:rgba(255,255,255,.02);border-radius:8px">
<div class="form-group" style="flex:1;min-width:150px"><label>IP Address</label><input name="ip" placeholder="192.168.1.100" required style="width:100%"></div>
<div class="form-group" style="flex:1;min-width:150px"><label>Server</label><input name="server" value="main" placeholder="main" style="width:100%"></div>
<button type="submit" class="btn btn-sm primary"> Add IP</button>
</form>
<table>
<thead><tr><th>IP</th><th>Server</th><th>Assigned To</th><th>Nameservers</th><th>Actions</th></tr></thead>
<tbody>
<?php if (!empty($ipList)): foreach ($ipList as $ip): ?>
<tr>
<td><?php echo htmlspecialchars($ip->ip); ?></td>
<td><?php echo htmlspecialchars($ip->server ?? "main"); ?></td>
<td><?php echo $ip->assigned_to ? htmlspecialchars($ip->assigned_to) : '<span style="color:#4ade80">Available</span>'; ?></td>
<td><?php echo $ip->ns1 ? "ns1: {$ip->ns1}" : "-"; ?></td>
<td><a href="/admin/ip/delete/<?php echo $ip->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171" onclick="return confirm('Delete this IP?')"> Delete</a></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No IPs added yet. Add your server IPs above.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
