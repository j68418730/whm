<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Mail Accounts</h3><div class="value"><?php echo $emailStats['total_accounts']; ?></div></div>
<div class="stat-card"><h3>Queue Size</h3><div class="value" style="font-size:20px"><?php echo $emailStats['queue_size']; ?></div></div>
<div class="stat-card"><h3>Postfix</h3><div class="value" style="font-size:16px;color:<?php echo $emailStats['postfix'] === 'active' ? '#4ade80' : '#f87171'; ?>"><?php echo $emailStats['postfix']; ?></div></div>
<div class="stat-card"><h3>Dovecot</h3><div class="value" style="font-size:16px;color:<?php echo $emailStats['dovecot'] === 'active' ? '#4ade80' : '#f87171'; ?>"><?php echo $emailStats['dovecot']; ?></div></div>
</div>
<div class="page-grid" style="margin-bottom:20px">
<a href="/admin/email" class="action-card"><div class="icon">📧</div><div class="name">Email Accounts</div></a>
<a href="/admin/email" class="action-card"><div class="icon">📨</div><div class="name">Mail Queue</div></a>
<a href="/admin/email" class="action-card"><div class="icon">🛡️</div><div class="name">Spam Filters</div></a>
</div>
<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">Email Accounts</h3>
<table><tr><th>Email</th><th>Domain</th><th>Status</th></tr>
<?php if (!empty($accounts)): foreach ($accounts as $a): ?>
<tr><td><?php echo htmlspecialchars($a->email ?? $a->username . '@' . $a->domain); ?></td>
<td><?php echo htmlspecialchars($a->domain ?? '-'); ?></td>
<td><span class="status-badge status-<?php echo ($a->is_active ?? 1) ? 'active' : 'terminated'; ?>"><?php echo ($a->is_active ?? 1) ? 'Active' : 'Inactive'; ?></span></td></tr>
<?php endforeach; else: ?><tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b">No mail accounts.</td></tr>
<?php endif; ?></table></div>
