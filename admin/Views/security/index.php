<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>SSL Certs</h3><div class="value"><?php echo $sslCount ?? 0; ?></div></div>
<div class="stat-card"><h3>IP Blocks</h3><div class="value"><?php echo $blockCount ?? 0; ?></div></div>
<div class="stat-card"><h3>2FA Users</h3><div class="value"><?php echo $twoFactorUsers ?? 0; ?></div></div>
<div class="stat-card"><h3>Login Attempts</h3><div class="value"><?php echo $loginAttempts['total'] ?? 0; ?></div></div>
</div>

<div class="page-grid" style="margin-bottom:20px">
<a href="/admin/ssl" class="action-card"><div class="icon">🔒</div><div class="name">SSL/TLS</div></a>
<a href="/admin/ssl/autossl" class="action-card"><div class="icon">🔄</div><div class="name">AutoSSL</div></a>
<a href="/admin/twofactor" class="action-card"><div class="icon">🔐</div><div class="name">Two-Factor Auth</div></a>
<a href="/admin/ipblocker" class="action-card"><div class="icon">🌐</div><div class="name">IP Blocker</div></a>
<a href="/admin/roles" class="action-card"><div class="icon">👥</div><div class="name">User Roles</div></a>
<a href="/admin/settings/security" class="action-card"><div class="icon">⚙️</div><div class="name">Security Settings</div></a>
<a href="/admin/network" class="action-card"><div class="icon">🖧</div><div class="name">IP & Nameservers</div></a>
</div>

<div class="card" style="margin-bottom:16px"><h3 style="color:var(--accent);margin-bottom:12px">Service Status</h3>
<table><tr><th>Service</th><th>Status</th></tr>
<tr><td>Firewall (firewalld)</td><td><span class="status-badge status-<?php echo $firewall === 'active' ? 'active' : 'terminated'; ?>"><?php echo $firewall; ?></span></td></tr>
<tr><td>Fail2ban</td><td><span class="status-badge status-<?php echo $fail2ban === 'active' ? 'active' : 'terminated'; ?>"><?php echo $fail2ban; ?></span></td></tr>
<tr><td>ModSecurity</td><td><span class="status-badge status-<?php echo $modsec === 'enabled' ? 'active' : 'terminated'; ?>"><?php echo $modsec; ?></span></td></tr>
<tr><td>SSL Certificates</td><td><span class="status-badge status-<?php echo $sslCount > 0 ? 'active' : 'terminated'; ?>"><?php echo $sslCount > 0 ? "$sslCount installed" : 'None'; ?></span></td></tr>
<tr><td>Two-Factor Auth</td><td><span class="status-badge status-<?php echo $twoFactorUsers > 0 ? 'active' : 'terminated'; ?>"><?php echo $twoFactorUsers > 0 ? "$twoFactorUsers users" : 'Disabled'; ?></span></td></tr>
<tr><td>IP Blocker</td><td><span class="status-badge status-<?php echo $blockCount > 0 ? 'active' : 'terminated'; ?>"><?php echo $blockCount > 0 ? "$blockCount IPs blocked" : 'No blocks'; ?></span></td></tr>
</table></div>

<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">Login Security</h3>
<table><tr><th>Metric</th><th>Value</th></tr>
<tr><td>Recent Login Attempts</td><td><?php echo $loginAttempts['total'] ?? 0; ?></td></tr>
<tr><td>Unique IPs</td><td><?php echo $loginAttempts['unique_ips'] ?? 0; ?></td></tr>
<?php if (!empty($loginAttempts['top_ips'])): ?>
<tr><td colspan="2" style="padding:0"><table style="margin:0"><tr><th style="color:var(--text-secondary);font-size:12px">Top IPs</th><th style="color:var(--text-secondary);font-size:12px">Count</th></tr>
<?php arsort($loginAttempts['top_ips']); $i=0; foreach (array_slice($loginAttempts['top_ips'], 0, 5) as $ip => $cnt): if (++$i > 5) break; ?>
<tr><td style="font-family:monospace;font-size:12px"><?php echo htmlspecialchars($ip); ?></td><td><?php echo $cnt; ?></td></tr>
<?php endforeach; ?></table></td></tr>
<?php endif; ?>
</table></div>
