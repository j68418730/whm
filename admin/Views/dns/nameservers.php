<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<?php
$nsList = $nameservers ?? [];
$ns1 = '';
$ns2 = '';
$ns3 = '';
$ns4 = '';
if (is_array($nsList)) {
    if (isset($nsList[0])) $ns1 = is_object($nsList[0]) ? ($nsList[0]->nameserver ?? '') : ($nsList[0] ?? '');
    if (isset($nsList[1])) $ns2 = is_object($nsList[1]) ? ($nsList[1]->nameserver ?? '') : ($nsList[1] ?? '');
    if (isset($nsList[2])) $ns3 = is_object($nsList[2]) ? ($nsList[2]->nameserver ?? '') : ($nsList[2] ?? '');
    if (isset($nsList[3])) $ns4 = is_object($nsList[3]) ? ($nsList[3]->nameserver ?? '') : ($nsList[3] ?? '');
}
if (!$ns1) $ns1 = 'ns1.planet-hosts.com';
if (!$ns2) $ns2 = 'ns2.planet-hosts.com';
?>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Nameservers</h3><div class="value"><?php echo count(array_filter([$ns1, $ns2, $ns3, $ns4])); ?></div></div>
<div class="stat-card"><h3>Primary NS</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($ns1); ?></div></div>
<div class="stat-card"><h3>Secondary NS</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($ns2); ?></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Set Nameserver Hostnames</h3>
<form method="post" action="/admin/dns/nameservers">
<div class="form-group"><label>Primary Nameserver (ns1)</label><input name="ns1" value="<?php echo htmlspecialchars($ns1); ?>" placeholder="ns1.example.com"></div>
<div class="form-group"><label>Secondary Nameserver (ns2)</label><input name="ns2" value="<?php echo htmlspecialchars($ns2); ?>" placeholder="ns2.example.com"></div>
<div class="form-group"><label>Tertiary Nameserver (ns3, optional)</label><input name="ns3" value="<?php echo htmlspecialchars($ns3); ?>" placeholder="ns3.example.com"></div>
<div class="form-group"><label>Quaternary Nameserver (ns4, optional)</label><input name="ns4" value="<?php echo htmlspecialchars($ns4); ?>" placeholder="ns4.example.com"></div>
<button type="submit" class="btn primary">Save Nameservers</button>
</form>
</div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Nameserver IP Addresses</h3>
<p style="color:var(--text-secondary);font-size:13px;margin-bottom:12px">Set the IP addresses for your nameservers (glue records).</p>
<form method="post" action="/admin/dns/nameservers">
<div class="form-group"><label><?php echo htmlspecialchars($ns1); ?> IP</label><input name="ns1_ip" placeholder="203.0.113.1"></div>
<div class="form-group"><label><?php echo htmlspecialchars($ns2); ?> IP</label><input name="ns2_ip" placeholder="203.0.113.2"></div>
<button type="submit" class="btn primary">Save IPs</button>
</form>
</div>
</div>

<div class="card" style="margin-top:20px">
<h3 style="color:var(--accent);margin-bottom:12px">Current Nameserver Status</h3>
<table>
<tr><th>Type</th><th>Hostname</th><th>Status</th></tr>
<tr><td>Primary (ns1)</td><td><?php echo htmlspecialchars($ns1); ?></td>
<td><span style="color:#4ade80">● Active</span></td></tr>
<tr><td>Secondary (ns2)</td><td><?php echo htmlspecialchars($ns2); ?></td>
<td><span style="color:#4ade80">● Active</span></td></tr>
<?php if ($ns3): ?>
<tr><td>Tertiary (ns3)</td><td><?php echo htmlspecialchars($ns3); ?></td>
<td><span style="color:#facc15">● Configured</span></td></tr>
<?php endif; ?>
<?php if ($ns4): ?>
<tr><td>Quaternary (ns4)</td><td><?php echo htmlspecialchars($ns4); ?></td>
<td><span style="color:#facc15">● Configured</span></td></tr>
<?php endif; ?>
</table>
</div>

<style>
.form-group{margin-bottom:10px}
.form-group label{display:block;font-size:12px;color:var(--text-secondary);margin-bottom:4px}
.form-group input{width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none;box-sizing:border-box}
.form-group input:focus{border-color:var(--accent,#008cff)}
</style>
