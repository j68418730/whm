<style>
.welcome-card{background:linear-gradient(135deg,rgba(0,140,255,.08),rgba(59,184,255,.04));border:1px solid rgba(0,140,255,.15);border-radius:14px;padding:22px 26px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px}
.welcome-card h2{margin:0;font-size:20px;font-weight:700}
.welcome-card .sub{color:var(--text_muted);font-size:13px;margin-top:2px}
.stat-action-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-bottom:18px}
.stat-action-card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:10px;padding:14px;text-align:center;transition:.15s;text-decoration:none;color:var(--text,#e0e0e0)}
.stat-action-card:hover{border-color:var(--primary,#008cff);transform:translateY(-2px)}
.stat-action-card .num{font-size:22px;font-weight:800}
.stat-action-card .lbl{font-size:11px;color:var(--text_muted);margin-top:2px}
.section-title{font-size:15px;font-weight:700;margin:0 0 12px;display:flex;align-items:center;gap:8px}
.section-title span{color:var(--text_muted);font-size:12px;font-weight:400}
.service-btn-group{display:flex;gap:4px;flex-wrap:wrap}
.service-btn-group .btn{padding:4px 10px;font-size:11px}
.quick-action-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px}
.quick-action-card{display:flex;align-items:center;gap:10px;padding:12px 14px;background:rgba(255,255,255,.02);border:1px solid var(--border,rgba(0,191,255,.06));border-radius:10px;text-decoration:none;color:var(--text,#e0e0e0);font-size:13px;font-weight:500;transition:.15s}
.quick-action-card:hover{border-color:var(--primary,#008cff);background:rgba(0,140,255,.04)}
.quick-action-card .icon{font-size:18px;width:28px;text-align:center}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:768px){.two-col{grid-template-columns:1fr}}
</style>

<?php if (!empty($_SESSION['sudo_login'])): ?>
<div style="background:rgba(0,140,255,.1);border:1px solid rgba(0,140,255,.3);border-radius:8px;padding:10px 16px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;font-size:13px">
<span><i class="bi bi-shield-fill-check"></i> Viewing as <strong><?php echo htmlspecialchars($hosting->username ?? $user->name ?? ''); ?></strong> (Sudo Mode)</span>
<a href="/admin/exit-sudo" class="btn btn-sm btn-primary">&#8592; Return to Admin</a>
</div>
<?php endif; ?>

<?php if (!empty($notifications)): foreach ($notifications as $n): ?>
<div class="alert alert-<?php echo $n['type']; ?>" style="margin-bottom:12px"><?php echo htmlspecialchars($n['msg']); ?></div>
<?php endforeach; endif; ?>

<!-- Welcome -->
<div class="welcome-card">
<div>
<h2>Welcome Back, <?php echo htmlspecialchars(($hosting->first_name ?? '') ?: ($hosting->username ?? 'User')); ?></h2>
<div class="sub">
<?php if ($package): ?><span class="badge bg-primary me-2"><?php echo htmlspecialchars($package->name); ?></span><?php endif; ?>
<span class="badge bg-<?php echo ($hosting->status ?? 'active') === 'active' ? 'success' : 'warning'; ?> me-2"><?php echo ucfirst($hosting->status ?? 'active'); ?></span>
<?php if ($isWeb): ?><span class="badge bg-info me-2">Web</span><?php endif; ?>
<?php if ($isRadio): ?><span class="badge bg-info me-2">Radio</span><?php endif; ?>
<?php if ($isVps): ?><span class="badge bg-info me-2">VPS</span><?php endif; ?>
<?php if ($isDedicated): ?><span class="badge bg-info me-2">Dedicated</span><?php endif; ?>
<?php if ($isChat): ?><span class="badge bg-info me-2">Chat</span><?php endif; ?>
Account ID: <?php echo $hosting->id ?? '-'; ?>
</div>
</div>
<div class="text-end" style="font-size:12px;color:var(--text_muted)">
<div>Last Login: <?php echo htmlspecialchars($lastLogin); ?></div>
<div>IP: <?php echo htmlspecialchars($userIp); ?></div>
</div>
</div>

<!-- Quick Stats -->
<div class="stat-action-grid">
<?php if ($isWeb || $isVps || $isDedicated): ?>
<div class="stat-action-card"><div class="num" style="color:var(--primary)"><?php echo max(1, count($services)); ?></div><div class="lbl">Active Services</div></div>
<?php endif; ?>
<?php if ($isWeb && count($domains) > 0): ?>
<div class="stat-action-card"><div class="num" style="color:#38bdf8"><?php echo count($domains); ?></div><div class="lbl">Domains</div></div>
<?php endif; ?>
<?php if ($hasStreams): ?>
<div class="stat-action-card"><div class="num" style="color:#a78bfa"><?php echo count($streams); ?></div><div class="lbl">Radio Streams</div></div>
<?php endif; ?>
<?php if ($hasGames): ?>
<div class="stat-action-card"><div class="num" style="color:#34d399"><?php echo count($gameServers); ?></div><div class="lbl">Game Servers</div></div>
<?php endif; ?>
<div class="stat-action-card"><div class="num" style="color:#facc15"><?php echo count($openInvoices); ?></div><div class="lbl">Invoices Due</div></div>
<div class="stat-action-card"><div class="num" style="color:#4ade80"><?php echo count($openTickets); ?></div><div class="lbl">Open Tickets</div></div>
<?php if ($hasChat): ?>
<div class="stat-action-card"><div class="num" style="color:#fb923c">1</div><div class="lbl">Live Chat</div></div>
<?php endif; ?>
<?php if ($emailAllowed): ?>
<div class="stat-action-card"><div class="num" style="color:#f472b6"><?php echo $emailCount; ?></div><div class="lbl">Email Accounts</div></div>
<?php endif; ?>
</div>

<div class="two-col">
<div>
<!-- Services -->
<?php if ($isWeb || $isVps || $isDedicated): ?>
<div class="card">
<h3 class="section-title">My Services</h3>
<?php if (count($services) > 0): foreach ($services as $svc): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border,rgba(0,191,255,.04))">
<div><strong><?php echo htmlspecialchars($svc->name ?? 'Service #'.$svc->id); ?></strong><br><span style="font-size:11px;color:var(--text_muted)"><?php echo htmlspecialchars($svc->type ?? ''); ?> &middot; $<?php echo number_format($svc->price ?? 0,2); ?>/mo</span></div>
<div class="service-btn-group"><a href="#" class="btn btn-sm btn-primary">Manage</a><a href="#" class="btn btn-sm btn-secondary">Renew</a></div>
</div>
<?php endforeach; else: ?>
<p style="color:var(--text_muted);font-size:13px">No active services.</p>
<?php endif; ?>
</div>
<?php endif; ?>

<!-- Domains (web only) -->
<?php if ($isWeb && count($domains) > 0): ?>
<div class="card">
<h3 class="section-title">My Domains <span>(<?php echo count($domains); ?>)</span></h3>
<?php foreach ($domains as $d): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--border,rgba(0,191,255,.04));font-size:13px">
<span><strong><?php echo htmlspecialchars($d->domain ?? ''); ?></strong> <span class="badge bg-<?php echo ($d->status ?? 'active') === 'active' ? 'success' : 'warning'; ?>" style="font-size:9px"><?php echo ucfirst($d->status ?? 'active'); ?></span></span>
<div class="service-btn-group"><a href="/user/domains" class="btn btn-sm btn-secondary">DNS</a><a href="/user/domains" class="btn btn-sm btn-secondary">Renew</a></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Invoices (all account types) -->
<div class="card">
<h3 class="section-title">Billing Overview</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px">
<div class="stat-card"><h3>Due</h3><div class="value" style="font-size:18px;color:#facc15">$<?php $dt=0; foreach($openInvoices as $i) $dt+=(float)$i->total; echo number_format($dt,2); ?></div></div>
<div class="stat-card"><h3>Paid</h3><div class="value" style="font-size:18px;color:#4ade80">$<?php $pt=0; foreach($paidInvoices as $i) $pt+=(float)$i->total; echo number_format($pt,2); ?></div></div>
</div>
<div class="d-flex gap-2 flex-wrap">
<a href="/user/billing" class="btn btn-sm btn-primary">View Invoices</a>
<?php if (count($openInvoices) > 0): ?><a href="/user/invoices" class="btn btn-sm" style="background:rgba(250,204,21,.12);color:#facc15;border:1px solid rgba(250,204,21,.2)">Pay Now</a><?php endif; ?>
</div>
</div>

<!-- Support (all account types) -->
<div class="card">
<h3 class="section-title">Support Center</h3>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:10px">
<div class="stat-card"><h3>Open</h3><div class="value" style="font-size:16px;color:#facc15"><?php echo count($openTickets); ?></div></div>
<div class="stat-card"><h3>Pending</h3><div class="value" style="font-size:16px;color:#38bdf8"><?php echo count($pendingTickets); ?></div></div>
<div class="stat-card"><h3>Resolved</h3><div class="value" style="font-size:16px;color:#4ade80"><?php echo count($resolvedTickets); ?></div></div>
</div>
<div class="d-flex gap-2 flex-wrap">
<a href="/user/tickets" class="btn btn-sm btn-primary">Create Ticket</a>
<a href="/user/support" class="btn btn-sm btn-secondary">Knowledge Base</a>
</div>
</div>
</div>

<div>
<!-- Quick Actions (all account types) -->
<div class="card">
<h3 class="section-title">Quick Actions</h3>
<div class="quick-action-grid">
<a href="/user/tickets" class="quick-action-card"><span class="icon">🎫</span> Create Ticket</a>
<a href="/user/invoices" class="quick-action-card"><span class="icon">💰</span> Pay Invoice</a>
<?php if ($isWeb): ?>
<a href="/user/services" class="quick-action-card"><span class="icon">🖥</span> Manage Hosting</a>
<a href="/user/domains" class="quick-action-card"><span class="icon">🌐</span> Manage Domain</a>
<?php endif; ?>
<?php if ($hasStreams || $isRadio): ?>
<a href="/user/dj-manager" class="quick-action-card"><span class="icon">📻</span> Radio Panel</a>
<?php endif; ?>
<?php if ($hasGames): ?>
<a href="/user/games" class="quick-action-card"><span class="icon">🎮</span> Game Servers</a>
<?php endif; ?>
<?php if ($hasChat): ?>
<a href="/user/chat" class="quick-action-card"><span class="icon">💬</span> Chat Settings</a>
<?php endif; ?>
<a href="/user/security" class="quick-action-card"><span class="icon">🔒</span> Security</a>
<a href="/user/profile" class="quick-action-card"><span class="icon">👤</span> My Profile</a>
</div>
</div>

<!-- Radio (if applicable) -->
<?php if ($hasStreams || $isRadio): ?>
<div class="card">
<h3 class="section-title">Radio Hosting</h3>
<?php foreach ($streams as $s): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--border,rgba(0,191,255,.04));font-size:13px">
<span><strong><?php echo htmlspecialchars($s->server_name ?? 'Stream'); ?></strong> <span class="badge bg-<?php echo $s->status === 'running' ? 'success' : 'secondary'; ?>"><?php echo $s->status; ?></span><br><span style="font-size:11px;color:var(--text_muted)">Port: <?php echo $s->port; ?></span></span>
<div class="service-btn-group"><a href="/user/dj-manager" class="btn btn-sm btn-primary">Control Panel</a></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Game Servers (if applicable) -->
<?php if ($hasGames): ?>
<div class="card">
<h3 class="section-title">Game Servers <span>(<?php echo count($gameServers); ?>)</span></h3>
<?php foreach ($gameServers as $g): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--border,rgba(0,191,255,.04));font-size:13px">
<span><strong><?php echo htmlspecialchars($g->server_name); ?></strong> <span class="badge bg-<?php echo $g->status === 'running' ? 'success' : 'secondary'; ?>"><?php echo $g->status; ?></span></span>
<div class="service-btn-group"><a href="/user/games/start/<?php echo $g->id; ?>" class="btn btn-sm btn-success">Start</a><a href="/user/games/stop/<?php echo $g->id; ?>" class="btn btn-sm btn-secondary">Stop</a></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Email (web only) -->
<?php if ($isWeb && $emailAllowed): ?>
<div class="card">
<h3 class="section-title">Email <span>(<?php echo $emailCount; ?> accounts)</span></h3>
<div class="d-flex gap-2 flex-wrap">
<a href="/user/email" class="btn btn-sm btn-primary">Manage Email</a>
<a href="http://45.61.59.55:2096/" target="_blank" class="btn btn-sm btn-secondary">Webmail</a>
</div>
</div>
<?php endif; ?>

<!-- Security -->
<div class="card">
<h3 class="section-title">Security</h3>
<div class="d-flex gap-2 flex-wrap">
<a href="/user/security" class="btn btn-sm btn-primary">Security Settings</a>
<a href="/user/security" class="btn btn-sm btn-secondary">Change Password</a>
</div>
</div>

<!-- Recent Activity -->
<?php if (!empty($recentActivity)): ?>
<div class="card">
<h3 class="section-title">Recent Activity</h3>
<?php foreach ($recentActivity as $act): ?>
<div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--border,rgba(0,191,255,.04));font-size:12px">
<span><?php echo htmlspecialchars($act->action ?? ''); ?></span>
<span style="color:var(--text_muted)"><?php echo htmlspecialchars($act->created_at ?? ''); ?></span>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>
