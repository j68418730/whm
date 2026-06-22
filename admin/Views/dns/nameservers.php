<div class="card">
<h3>Nameservers</h3>
<form method="post" action="/admin/dns/nameservers">
<div class="form-group"><label>Primary Nameserver</label><input name="ns1" value="<?php echo htmlspecialchars($ns['ns1'] ?? 'ns1.planet-hosts.com'); ?>"></div>
<div class="form-group"><label>Secondary Nameserver</label><input name="ns2" value="<?php echo htmlspecialchars($ns['ns2'] ?? 'ns2.planet-hosts.com'); ?>"></div>
<button class="btn btn-primary">Save Nameservers</button>
</form>
</div>
