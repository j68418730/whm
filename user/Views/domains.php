<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
<?php endif; ?>
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Your Domains</h3>
<table><tr><th>Domain</th></tr>
<?php if (!empty($domains)): foreach ($domains as $d): ?>
<tr><td><strong><?php echo htmlspecialchars($d->domain); ?></strong></td></tr>
<?php endforeach; else: ?><tr><td style="text-align:center;padding:20px;color:#64748b">No domains yet.</td></tr>
<?php endif; ?></table></div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Your Subdomains</h3>
<table><tr><th>Subdomain</th></tr>
<?php $hasSd = false; foreach ($domains as $d): $sdList = array_filter($subdomains, function($s) use ($d) { return $s->domain === $d->domain; }); if (empty($sdList)) continue; $hasSd = true; foreach ($sdList as $s): ?>
<tr><td><?php echo htmlspecialchars($s->name . '.' . $d->domain); ?></td></tr>
<?php endforeach; endforeach; if (!$hasSd): ?><tr><td style="text-align:center;padding:20px;color:#64748b">No subdomains yet.</td></tr>
<?php endif; ?></table></div>
