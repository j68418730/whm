<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Total IPs</h3><div class="value"><?php echo count($ips); ?></div></div>
<div class="stat-card"><h3>Assigned</h3><div class="value"><?php echo count(array_filter($ips, function($i) { return $i->assigned_to; })); ?></div></div>
<div class="stat-card"><h3>Available</h3><div class="value"><?php echo count(array_filter($ips, function($i) { return !$i->assigned_to; })); ?></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Add IP to Pool</h3>
<form method="POST" action="/admin/ip/store">
<div class="form-group"><label>IP Address</label><input name="ip_address" required placeholder="203.0.113.1"></div>
<div class="form-group"><label>Server Label</label><input name="server" placeholder="Web Server 01"></div>
<div class="form-group"><label>Primary Nameserver</label><input name="ns1" placeholder="ns1.planet-hosts.com"></div>
<div class="form-group"><label>Secondary Nameserver</label><input name="ns2" placeholder="ns2.planet-hosts.com"></div>
<button type="submit" class="btn primary">Add IP</button>
</form>
</div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Nameserver Settings</h3>
<form method="POST" action="/admin/ip/nameservers">
<div class="form-group"><label>Nameserver 1</label><input name="ns1" value="<?php echo isset($nameservers[0]) ? htmlspecialchars($nameservers[0]->nameserver) : 'ns1.planet-hosts.com'; ?>"></div>
<div class="form-group"><label>Nameserver 2</label><input name="ns2" value="<?php echo isset($nameservers[1]) ? htmlspecialchars($nameservers[1]->nameserver) : 'ns2.planet-hosts.com'; ?>"></div>
<div class="form-group"><label>Nameserver 3 (optional)</label><input name="ns3" value="<?php echo isset($nameservers[2]) ? htmlspecialchars($nameservers[2]->nameserver) : ''; ?>"></div>
<div class="form-group"><label>Nameserver 4 (optional)</label><input name="ns4" value="<?php echo isset($nameservers[3]) ? htmlspecialchars($nameservers[3]->nameserver) : ''; ?>"></div>
<button type="submit" class="btn primary">Save Nameservers</button>
</form>
</div>
</div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">All Server IPs</h3>
<table>
<tr><th>IP Address</th><th>Server</th><th>Assigned To</th><th>Nameservers</th><th>Actions</th></tr>
<?php if (!empty($ips)): foreach ($ips as $ip): ?>
<tr>
<td style="font-family:monospace"><?php echo htmlspecialchars($ip->ip_address); ?></td>
<td><?php echo htmlspecialchars($ip->server ?: $ip->hostname ?: '-'); ?></td>
<td>
<?php if ($ip->assigned_to && isset($accountMap[$ip->assigned_to])): ?>
<a href="/admin/account/show/<?php echo $ip->assigned_to; ?>" style="color:#4ade80"><?php echo htmlspecialchars($accountMap[$ip->assigned_to]->username); ?></a>
<a href="/admin/ip/unassign/<?php echo $ip->id; ?>" class="btn btn-sm" style="background:rgba(250,204,21,.1);color:#facc15;padding:2px 8px;font-size:10px" onclick="return confirm('Unassign this IP?')">Unassign</a>
<?php else: ?>
<form method="GET" action="/admin/ip/assign/<?php echo $ip->id; ?>" style="display:inline-flex;gap:4px;align-items:center">
<select name="account_id" style="font-size:11px;padding:2px 4px;width:auto">
<option value="">— Select —</option>
<?php foreach ($accounts as $a): ?>
<option value="<?php echo $a->id; ?>"><?php echo htmlspecialchars($a->username); ?></option>
<?php endforeach; ?>
</select>
<button type="submit" class="btn btn-sm" style="background:rgba(74,222,128,.1);color:#4ade80;padding:2px 8px;font-size:10px">Assign</button>
</form>
<?php endif; ?>
</td>
<td style="font-size:11px"><?php echo htmlspecialchars($ip->ns1 ?? ''); ?><br><?php echo htmlspecialchars($ip->ns2 ?? ''); ?></td>
<td>
<a href="/admin/ip/delete/<?php echo $ip->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete IP <?php echo htmlspecialchars($ip->ip_address); ?>?')">Delete</a>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5" style="text-align:center;padding:30px;color:#64748b">No IPs configured yet. Add one above.</td></tr>
<?php endif; ?>
</table>
</div>

<style>
.alert-danger{background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.2);border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px}
.form-group{margin-bottom:10px}
.form-group label{display:block;font-size:12px;color:var(--text-secondary);margin-bottom:4px}
.form-group input, .form-group select{width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none;box-sizing:border-box}
.form-group input:focus{border-color:var(--accent,#008cff)}
</style>
