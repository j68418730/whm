<style>
.dash-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-bottom:24px}
.dash-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:14px;padding:20px;transition:.2s;position:relative;overflow:hidden}
.dash-card:hover{border-color:rgba(0,140,255,.2);transform:translateY(-2px)}
.dash-card .icon-box{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:10px}
.dash-card h3{font-size:14px;font-weight:600;margin:0 0 4px;color:#e0e0e0}
.dash-card .val{font-size:28px;font-weight:800;margin:4px 0}
.dash-card .sub{font-size:11px;color:#64748b}
.dash-card .bar{height:4px;border-radius:2px;margin-top:10px;background:rgba(255,255,255,.05)}
.dash-card .bar .fill{height:100%;border-radius:2px;transition:width .5s}

.quick-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin-bottom:24px}
.quick-link{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 12px;background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.06);border-radius:12px;text-decoration:none;color:#e0e0e0;font-size:12px;font-weight:500;transition:.15s}
.quick-link:hover{border-color:rgba(0,140,255,.3);background:rgba(0,140,255,.04);transform:translateY(-2px)}
.quick-link .qicon{font-size:24px}

.section-title{font-size:15px;font-weight:700;margin:0 0 14px;display:flex;align-items:center;gap:8px}
.section-title span{font-weight:400;font-size:12px;color:#64748b}
.activity-item{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:12px}
.activity-item:last-child{border:none}
.activity-item .time{color:#64748b;white-space:nowrap;font-size:11px}
</style>

<h2 style="font-size:22px;font-weight:700;margin-bottom:4px">Dashboard</h2>
<p class="subtitle" style="color:#64748b;font-size:13px;margin-bottom:24px">Welcome back, <?php echo htmlspecialchars(($hosting->first_name ?? '') ?: ($hosting->username ?? 'User')); ?></p>

<!-- Quick Actions -->
<div class="quick-grid">
<a href="/user/services" class="quick-link"><span class="qicon">🌐</span>My Services</a>
<a href="/user/files" class="quick-link"><span class="qicon">📁</span>File Manager</a>
<a href="/user/tickets" class="quick-link"><span class="qicon">🎫</span>Open Ticket</a>
<a href="/pma_autologin.php" target="_blank" class="quick-link"><span class="qicon">🐘</span>phpMyAdmin</a>
<a href="/webmail_autologin.php" target="_blank" class="quick-link"><span class="qicon">📨</span>Webmail</a>
<a href="/user/invoices" class="quick-link"><span class="qicon">💳</span>Pay Invoice</a>
<a href="/livechat" target="_blank" class="quick-link"><span class="qicon">💬</span>Live Chat</a>
<a href="/user/domains" class="quick-link"><span class="qicon">🌍</span>Domains</a>
</div>

<!-- Stats Grid -->
<div class="dash-grid">
<div class="dash-card"><div class="icon-box" style="background:rgba(0,140,255,.12)">🖥</div><h3>Services</h3><div class="val" style="color:#0A84FF"><?php echo count($services ?? []); ?></div><div class="sub">Active services</div></div>

<div class="dash-card"><div class="icon-box" style="background:rgba(56,189,248,.12)">🌍</div><h3>Domains</h3><div class="val" style="color:#38bdf8"><?php echo count($domains ?? []); ?></div><div class="sub">Registered domains</div></div>

<div class="dash-card"><div class="icon-box" style="background:rgba(74,222,128,.12)">📧</div><h3>Email</h3><div class="val" style="color:#4ade80"><?php echo $emailCount ?? 0; ?></div><div class="sub">Email accounts</div></div>

<div class="dash-card"><div class="icon-box" style="background:rgba(250,204,21,.12)">📄</div><h3>Invoices</h3><div class="val" style="color:#facc15"><?php echo count($openInvoices ?? []); ?></div><div class="sub">Unpaid invoices</div></div>

<div class="dash-card"><div class="icon-box" style="background:rgba(168,85,247,.12)">🎫</div><h3>Tickets</h3><div class="val" style="color:#a78bfa"><?php echo count($openTickets ?? []); ?></div><div class="sub">Open support tickets</div></div>

<div class="dash-card"><div class="icon-box" style="background:rgba(251,146,60,.12)">💾</div><h3>Disk Usage</h3><div class="val" style="color:#fb923c"><?php echo $diskPct ?? 0; ?>%</div><div class="sub">of <?php echo $diskTotal ?? '∞'; ?> GB used</div>
<div class="bar"><div class="fill" style="width:<?php echo min(100, $diskPct ?? 0); ?>%;background:<?php echo ($diskPct ?? 0) > 90 ? '#f87171' : '#fb923c'; ?>"></div></div></div>
</div>

<!-- Bottom Row -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
<div>

<!-- Services List -->
<div class="dash-card" style="grid-column:1/-1">
<h3 class="section-title">My Services <span>(<?php echo count($services ?? []); ?>)</span></h3>
<?php if (!empty($services)): foreach ($services as $svc): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px">
<div><strong><?php echo htmlspecialchars($svc->name ?? "Service #{$svc->id}"); ?></strong><br><span style="color:#64748b;font-size:11px"><?php echo htmlspecialchars($svc->type ?? ''); ?></span></div>
<a href="/user/services" class="btn btn-sm" style="background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);text-decoration:none;padding:4px 12px;border-radius:6px;font-size:12px">Manage</a>
</div>
<?php endforeach; else: ?>
<p style="color:#64748b;font-size:13px;text-align:center;padding:20px 0">No active services.</p>
<?php endif; ?>
</div>

<!-- Domains -->
<div class="dash-card">
<h3 class="section-title">Domains <span>(<?php echo count($domains ?? []); ?>)</span></h3>
<?php if (!empty($domains)): foreach (array_slice($domains, 0, 4) as $d): ?>
<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:12px">
<span><?php echo htmlspecialchars($d->domain ?? ''); ?></span>
<span style="font-size:10px;color:<?php echo ($d->status ?? 'active') === 'active' ? '#4ade80' : '#facc15'; ?>">● <?php echo ucfirst($d->status ?? 'active'); ?></span>
</div>
<?php endforeach; else: ?>
<p style="color:#64748b;font-size:12px;text-align:center;padding:12px 0">No domains yet.</p>
<?php endif; ?>
<a href="/user/domains" style="display:block;text-align:center;margin-top:8px;font-size:12px;color:#0A84FF;text-decoration:none">Manage Domains →</a>
</div>

<!-- Billing -->
<div class="dash-card">
<h3 class="section-title">Billing</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
<div style="background:rgba(250,204,21,.06);border-radius:8px;padding:12px;text-align:center">
<div style="font-size:10px;color:#64748b;text-transform:uppercase">Due</div>
<div style="font-size:22px;font-weight:700;color:#facc15">$<?php $dt=0; foreach($openInvoices ?? [] as $i) $dt+=(float)$i->total; echo number_format($dt,2); ?></div>
</div>
<div style="background:rgba(74,222,128,.06);border-radius:8px;padding:12px;text-align:center">
<div style="font-size:10px;color:#64748b;text-transform:uppercase">Paid</div>
<div style="font-size:22px;font-weight:700;color:#4ade80">$<?php $pt=0; foreach($paidInvoices ?? [] as $i) $pt+=(float)$i->total; echo number_format($pt,2); ?></div>
</div>
</div>
<a href="/user/invoices" class="btn btn-sm" style="background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);text-decoration:none;padding:6px 14px;border-radius:6px;font-size:12px;display:inline-block">View Invoices</a>
</div>

<!-- Support -->
<div class="dash-card">
<h3 class="section-title">Support</h3>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:12px">
<div style="text-align:center"><div style="font-size:10px;color:#64748b">Open</div><div style="font-size:20px;font-weight:700;color:#facc15"><?php echo count($openTickets ?? []); ?></div></div>
<div style="text-align:center"><div style="font-size:10px;color:#64748b">Pending</div><div style="font-size:20px;font-weight:700;color:#38bdf8"><?php echo count($pendingTickets ?? []); ?></div></div>
<div style="text-align:center"><div style="font-size:10px;color:#64748b">Closed</div><div style="font-size:20px;font-weight:700;color:#4ade80"><?php echo count($resolvedTickets ?? []); ?></div></div>
</div>
<a href="/user/tickets" class="btn btn-sm" style="background:rgba(0,140,255,.1);color:#0A84FF;border:1px solid rgba(0,140,255,.2);text-decoration:none;padding:6px 14px;border-radius:6px;font-size:12px;display:inline-block">Create Ticket</a>
</div>

<?php if (!empty($recentActivity)): ?>
<!-- Activity -->
<div class="dash-card" style="grid-column:1/-1">
<h3 class="section-title">Recent Activity</h3>
<?php foreach ($recentActivity as $act): ?>
<div class="activity-item"><span><?php echo htmlspecialchars($act->action ?? ''); ?></span><span class="time"><?php echo htmlspecialchars($act->created_at ?? ''); ?></span></div>
<?php endforeach; ?>
</div>
<?php endif; ?>

</div>
</div>
