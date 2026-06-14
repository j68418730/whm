<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
<?php endif; ?>
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px">
<a href="/user/domains/add" class="btn primary">+ Add Domain</a>
</div>
<div class="card">
<h3 style="color:var(--accent);margin-bottom:12px">Your Domains</h3>
<table><tr><th>Domain</th><th>Nameservers</th><th>Actions</th></tr>
<?php if (!empty($domains)): foreach ($domains as $d): ?>
<tr><td><strong><?php echo htmlspecialchars($d->domain); ?></strong></td><td><?php echo htmlspecialchars($d->ns1 ?? '-'); ?>, <?php echo htmlspecialchars($d->ns2 ?? '-'); ?></td>
<td><a href="/user/domains/zone/<?php echo $d->id; ?>" class="btn btn-sm secondary">DNS</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b">No domains yet.</td></tr>
<?php endif; ?></table></div>
