<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Total Clients</h3><div class="value"><?php echo $totalAccounts ?? 0; ?></div></div>
<div class="stat-card"><h3>Active</h3><div class="value"><?php echo $activeAccounts ?? 0; ?></div></div>
<div class="stat-card"><h3>Company</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($reseller->company_name); ?></div></div>
</div>
<div class="page-grid">
<a href="/reseller/clients" class="action-card"><div class="icon">👥</div><div class="name">Clients</div></a>
<a href="/reseller/packages" class="action-card"><div class="icon">📦</div><div class="name">Packages</div></a>
<?php if (!empty($addons['billing'])): ?><a href="/reseller/billing-system" class="action-card"><div class="icon">🧾</div><div class="name">Billing System</div></a><?php endif; ?>
<?php if (!empty($addons['chat'])): ?><a href="/reseller/chat-system" class="action-card"><div class="icon">💬</div><div class="name">Chat System</div></a><?php endif; ?>
<?php if (!empty($addons['support'])): ?><a href="/reseller/support-system" class="action-card"><div class="icon">🎧</div><div class="name">Support System</div></a><?php endif; ?>
<a href="/reseller/provisioning" class="action-card"><div class="icon">⚙️</div><div class="name">Provisioning</div></a>
<a href="/reseller/billing" class="action-card"><div class="icon">💰</div><div class="name">Billing</div></a>
<a href="/reseller/support" class="action-card"><div class="icon">🎫</div><div class="name">Support</div></a>
<a href="/reseller/branding" class="action-card"><div class="icon">🎨</div><div class="name">Branding</div></a>
</div>
