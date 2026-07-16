<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
<?php endif; ?>
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Your Domains</h3>
<table><tr><th>Domain</th><th>Nameservers</th><th>Actions</th></tr>
<?php if (!empty($domains)): foreach ($domains as $d): ?>
<tr><td><strong><?php echo htmlspecialchars($d->domain); ?></strong></td><td><?php echo htmlspecialchars($d->ns1 ?? '-'); ?>, <?php echo htmlspecialchars($d->ns2 ?? '-'); ?></td>
<td><a href="/user/domains/zone/<?php echo $d->id; ?>" class="btn btn-sm secondary">DNS</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b">No domains yet.</td></tr>
<?php endif; ?></table></div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Your Subdomains <span style="font-size:11px;color:#64748b;font-weight:400">(stored in <code style="color:#a855f7">dns_records</code> table, type A)</span></h3>
<table><tr><th>Subdomain</th><th>Points To</th><th>Actions</th></tr>
<?php $hasSd = false; foreach ($domains as $d): $sdList = array_filter($subdomains, function($s) use ($d) { return $s->domain === $d->domain; }); if (empty($sdList)) continue; $hasSd = true; foreach ($sdList as $s): ?>
<tr><td><strong><?php echo htmlspecialchars($s->name . '.' . $s->domain); ?></strong></td><td><?php echo htmlspecialchars($s->value); ?></td>
<td><a href="/user/domains/zone/<?php echo $s->zone_id; ?>" class="btn btn-sm secondary">DNS</a></td></tr>
<?php endforeach; endforeach; if (!$hasSd): ?><tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b">No subdomains yet.</td></tr>
<?php endif; ?></table></div>
