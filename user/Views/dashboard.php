<h2>Dashboard</h2>
<p class="subtitle">Welcome back, <?php echo htmlspecialchars(($hosting->first_name ?? '') ?: $username); ?></p>

<!-- Quick Actions -->
<div class="quick-grid" style="margin-bottom:20px">
<a href="/user/services" class="quick-link"><span class="qicon">🌐</span><span>Create Website</span></a>
<a href="/user/email" class="quick-link"><span class="qicon">📧</span><span>Create Email</span></a>
<a href="/user/files" class="quick-link"><span class="qicon">📁</span><span>File Manager</span></a>
<a href="/user/tickets" class="quick-link"><span class="qicon">🎫</span><span>Open Ticket</span></a>
<a href="/pma_autologin.php" target="_blank" class="quick-link"><span class="qicon">🐘</span><span>phpMyAdmin</span></a>
<a href="/webmail_autologin.php" target="_blank" class="quick-link"><span class="qicon">📨</span><span>Webmail</span></a>
<a href="/livechat" target="_blank" class="quick-link"><span class="qicon">💬</span><span>Live Chat</span></a>
<a href="/user/invoices" class="quick-link"><span class="qicon">💳</span><span>Pay Invoice</span></a>
</div>

<!-- Stats -->
<div class="stat-grid">
<div class="stat-card"><div class="num" style="color:#0A84FF"><?php echo count($services ?? []); ?></div><div class="lbl">Services</div></div>
<div class="stat-card"><div class="num" style="color:#38bdf8"><?php echo count($domains ?? []); ?></div><div class="lbl">Domains</div></div>
<div class="stat-card"><div class="num" style="color:#4ade80"><?php echo $emailCount ?? 0; ?></div><div class="lbl">Email Accounts</div></div>
<div class="stat-card"><div class="num" style="color:#facc15"><?php echo count($openInvoices ?? []); ?></div><div class="lbl">Invoices Due</div></div>
<div class="stat-card"><div class="num" style="color:#a78bfa"><?php echo count($openTickets ?? []); ?></div><div class="lbl">Open Tickets</div></div>
<div class="stat-card"><div class="num" style="color:#fb923c"><?php echo $diskPct ?? 0; ?>%</div><div class="lbl">Disk Used</div></div>
</div>

<div class="two-col">
<div>

<!-- Services -->
<?php if (!empty($services)): ?>
<div class="card">
<h3>My Services</h3>
<?php foreach ($services as $svc): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px">
<div><strong><?php echo htmlspecialchars($svc->name ?? "Service #{$svc->id}"); ?></strong><br><span style="color:#64748b;font-size:11px"><?php echo htmlspecialchars($svc->type ?? ''); ?> · $<?php echo number_format($svc->price ?? 0,2); ?>/mo</span></div>
<a href="<?php echo $svc->type === 'radio' ? '/user/dj-manager' : '/user/services'; ?>" class="btn btn-sm btn-primary">Manage</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Domains -->
<?php if (!empty($domains)): ?>
<div class="card">
<h3>My Domains <span>(<?php echo count($domains); ?>)</span></h3>
<?php foreach (array_slice($domains, 0, 5) as $d): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px">
<span><strong><?php echo htmlspecialchars($d->domain ?? ''); ?></strong> <span class="badge bg-<?php echo ($d->status ?? 'active') === 'active' ? 'success' : 'warning'; ?>" style="font-size:9px"><?php echo ucfirst($d->status ?? 'active'); ?></span></span>
<a href="/user/domains" class="btn btn-sm btn-secondary">Manage</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Invoices -->
<div class="card">
<h3>Billing Overview</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
<div><div style="font-size:11px;color:#64748b">Due</div><div style="font-size:20px;font-weight:700;color:#facc15">$<?php $dt=0; foreach($openInvoices ?? [] as $i) $dt+=(float)$i->total; echo number_format($dt,2); ?></div></div>
<div><div style="font-size:11px;color:#64748b">Paid Total</div><div style="font-size:20px;font-weight:700;color:#4ade80">$<?php $pt=0; foreach($paidInvoices ?? [] as $i) $pt+=(float)$i->total; echo number_format($pt,2); ?></div></div>
</div>
<a href="/user/invoices" class="btn btn-sm btn-primary">View All Invoices</a>
<?php if (!empty($openInvoices)): ?><a href="/user/billing" class="btn btn-sm" style="background:rgba(250,204,21,.12);color:#facc15;border:1px solid rgba(250,204,21,.2);margin-left:6px">Pay Now</a><?php endif; ?>
</div>

<!-- Support -->
<div class="card">
<h3>Support Center</h3>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:10px">
<div><div style="font-size:11px;color:#64748b">Open</div><div style="font-size:18px;font-weight:700;color:#facc15"><?php echo count($openTickets ?? []); ?></div></div>
<div><div style="font-size:11px;color:#64748b">Pending</div><div style="font-size:18px;font-weight:700;color:#38bdf8"><?php echo count($pendingTickets ?? []); ?></div></div>
<div><div style="font-size:11px;color:#64748b">Resolved</div><div style="font-size:18px;font-weight:700;color:#4ade80"><?php echo count($resolvedTickets ?? []); ?></div></div>
</div>
<a href="/user/tickets" class="btn btn-sm btn-primary">Create Ticket</a>
<a href="/user/support" class="btn btn-sm btn-secondary">Knowledge Base</a>
</div>

</div>
<div>

<!-- Resource Usage -->
<div class="card">
<h3>Resource Usage</h3>
<div style="margin-bottom:10px">
<div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px"><span>Disk Space</span><span><?php echo $diskUsed ?? 0; ?> / <?php echo $diskTotal ?? '∞'; ?> GB</span></div>
<div class="progress" style="height:6px"><div class="progress-bar" style="width:<?php echo min(100, $diskPct ?? 0); ?>%;background:<?php echo ($diskPct ?? 0) > 90 ? '#f87171' : '#0A84FF'; ?>"></div></div>
</div>
<div style="margin-bottom:10px">
<div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px"><span>Bandwidth</span><span>---</span></div>
<div class="progress" style="height:6px"><div class="progress-bar" style="width:0%;background:#38bdf8"></div></div>
</div>
<a href="/user/usage" class="btn btn-sm btn-secondary">View Details</a>
</div>

<!-- Email -->
<?php if ($hasEmail): ?>
<div class="card">
<h3>Email <span>(<?php echo $emailCount ?? 0; ?> accounts)</span></h3>
<a href="/user/email" class="btn btn-sm btn-primary">Manage Email</a>
<a href="/webmail_autologin.php" target="_blank" class="btn btn-sm btn-secondary">Webmail</a>
</div>
<?php endif; ?>

<!-- Security -->
<div class="card">
<h3>Security</h3>
<a href="/user/security" class="btn btn-sm btn-primary">Security Settings</a>
<a href="/user/security" class="btn btn-sm btn-secondary">Change Password</a>
</div>

<!-- Quick Links -->
<div class="card">
<h3>Quick Links</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
<a href="/user/files" style="font-size:13px;padding:6px 0;color:#94a3b8;text-decoration:none">📁 File Manager</a>
<a href="/user/databases" style="font-size:13px;padding:6px 0;color:#94a3b8;text-decoration:none">🗄️ Databases</a>
<a href="/user/domains" style="font-size:13px;padding:6px 0;color:#94a3b8;text-decoration:none">🌐 Domains</a>
<a href="/user/profile" style="font-size:13px;padding:6px 0;color:#94a3b8;text-decoration:none">👤 Profile</a>
</div>
</div>

<!-- Recent Activity -->
<?php if (!empty($recentActivity)): ?>
<div class="card">
<h3>Recent Activity</h3>
<?php foreach ($recentActivity as $act): ?>
<div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:12px">
<span><?php echo htmlspecialchars($act->action ?? ''); ?></span>
<span style="color:#64748b"><?php echo htmlspecialchars($act->created_at ?? ''); ?></span>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

</div>
</div>

<script>
// Auto-redirect phpMyAdmin and webmail links to SSO
document.querySelectorAll('a[href*="phpmyadmin"], a[href*="pma_autologin"]').forEach(function(a) {
    if (!a.href.includes('pma_autologin')) {
        a.href = '/pma_autologin.php';
    }
});
</script>
