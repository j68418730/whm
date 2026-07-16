<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
<?php endif; ?>
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Your Domains</h3>
<table><tr><th>Domain</th><th>Nameservers</th><th>Subdomains</th><th>Actions</th></tr>
<?php if (!empty($domains)): foreach ($domains as $d): 
$sdList = array_filter($subdomains, function($s) use ($d) { return $s->domain === $d->domain; });
?>
<tr><td><strong><?php echo htmlspecialchars($d->domain); ?></strong></td><td><?php echo htmlspecialchars($d->ns1 ?? '-'); ?>, <?php echo htmlspecialchars($d->ns2 ?? '-'); ?></td>
<td><?php if (empty($sdList)): ?><span style="color:#64748b;font-size:11px">None</span><?php else: foreach ($sdList as $s): ?><div style="font-size:11px;color:#94a3b8"><?php echo htmlspecialchars($s->name . '.' . $s->domain); ?> → <?php echo htmlspecialchars($s->value); ?></div><?php endforeach; endif; ?></td>
<td><a href="/user/domains/zone/<?php echo $d->id; ?>" class="btn btn-sm secondary">DNS</a> <a href="/user/subdomains" class="btn btn-sm secondary">Subdomains</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No domains yet.</td></tr>
<?php endif; ?></table></div>
