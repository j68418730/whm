<?php
$userName = htmlspecialchars($user->name ?? 'Administrator', ENT_QUOTES, 'UTF-8');
$s = $stats ?? [];
$addons = $addons ?? [];
$recentAccounts = $recentAccounts ?? [];
$recentTickets = $recentTickets ?? [];
$recentOrders = $recentOrders ?? [];
$server = $server ?? [];
$services = $services ?? [];
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- ─── Top Stats Bar ─── -->
<div class="stats-grid" style="margin-bottom:16px;grid-template-columns:repeat(auto-fit,minmax(140px,1fr))">
<div class="stat-card"><h3>Accounts</h3><div class="value"><?php echo $s['total_accounts'] ?? 0; ?></div><div class="label"><?php echo $s['active_accounts'] ?? 0; ?> active / <?php echo $s['suspended_accounts'] ?? 0; ?> suspended</div></div>
<div class="stat-card"><h3>Tickets</h3><div class="value" style="color:#facc15"><?php echo $s['open_tickets'] ?? 0; ?></div><div class="label">Open tickets</div></div>
<div class="stat-card"><h3>Revenue (Month)</h3><div class="value" style="color:#4ade80">$<?php echo number_format($s['revenue_month'] ?? 0, 2); ?></div><div class="label"><?php echo $s['pending_invoices'] ?? 0; ?> unpaid ($<?php echo number_format($s['pending_invoice_total'] ?? 0, 2); ?>)</div></div>
<?php if (($s['paypal_balance'] ?? null) !== null): ?>
<div class="stat-card"><h3>PayPal Balance</h3><div class="value" style="color:#00d4ff">$<?php echo number_format($s['paypal_balance'], 2); ?></div><div class="label"><i class="fab fa-paypal"></i> Available</div></div>
<?php endif; ?>
</div>

<!-- ─── Server Health + Services ─── -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
<div class="card">
<h3 style="color:var(--accent);font-size:14px;margin-bottom:12px"><i class="fas fa-server"></i> Server Health <span style="font-size:10px;color:#4ade80;font-weight:400">● live</span></h3>
<div id="serverHealth" style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
<div><span style="color:#64748b;font-size:12px">Hostname</span><div style="font-size:14px;font-weight:600"><?php echo htmlspecialchars($server['hostname'] ?? 'N/A'); ?></div></div>
<div><span style="color:#64748b;font-size:12px">Uptime</span><div style="font-size:14px;font-weight:600"><?php echo $server['uptime'] ?? 'N/A'; ?></div></div>
<div><span style="color:#64748b;font-size:12px">CPU</span><div style="font-size:14px"><?php echo $server['cpu'] ?? 'N/A'; ?></div></div>
<div><span style="color:#64748b;font-size:12px">Load</span><div style="font-size:14px"><?php echo $server['load'] ?? 'N/A'; ?></div></div>
</div>
<div style="margin-top:10px">
<div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:2px"><span>RAM</span><span><?php echo $server['ram'] ?? 'N/A'; ?></span></div>
<div style="height:6px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden"><div style="height:100%;width:<?php echo $server['ram_pct'] ?? 0; ?>%;background:linear-gradient(90deg,#008cff,#3bb8ff);border-radius:3px"></div></div>
</div>
<div style="margin-top:8px">
<div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:2px"><span>Disk</span><span><?php echo $server['disk'] ?? 'N/A'; ?></span></div>
<div style="height:6px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden"><div style="height:100%;width:<?php echo $server['disk_pct'] ?? 0; ?>%;background:linear-gradient(90deg,#f59e0b,#ef4444);border-radius:3px"></div></div>
</div>
</div>

<div class="card">
<h3 style="color:var(--accent);font-size:14px;margin-bottom:12px"><i class="fas fa-cogs"></i> Services</h3>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<?php foreach ($services as $svc): ?>
<div style="display:flex;align-items:center;gap:6px;padding:4px 8px;border-radius:4px;font-size:12px;background:rgba(255,255,255,.02)">
<span style="width:8px;height:8px;border-radius:50%;background:<?php echo $svc['active'] ? '#4ade80' : '#ef4444'; ?>;flex-shrink:0"></span>
<span style="color:<?php echo $svc['active'] ? '#e0e0e0' : '#f87171'; ?>"><?php echo htmlspecialchars($svc['name']); ?></span>
</div>
<?php endforeach; ?>
</div>
</div>
</div>

<!-- ─── Recent Activity + Quick Actions ─── -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px">
<div class="card">
<h3 style="color:var(--accent);font-size:14px;margin-bottom:12px"><i class="fas fa-clock"></i> Recent Activity</h3>

<?php if (!empty($recentOrders)): ?>
<div style="font-size:12px;color:#64748b;margin-bottom:6px">Recent Orders</div>
<?php foreach ($recentOrders as $ord): ?>
<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px">
<span>Order #<?php echo $ord->id; ?> — $<?php echo number_format($ord->total ?? 0, 2); ?></span>
<span style="color:#64748b;font-size:11px"><?php echo htmlspecialchars($ord->status ?? ''); ?></span>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($recentAccounts)): ?>
<div style="font-size:12px;color:#64748b;margin-top:10px;margin-bottom:6px">New Accounts</div>
<?php foreach ($recentAccounts as $a): ?>
<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px">
<span><?php echo htmlspecialchars($a->username ?? $a->email ?? ''); ?></span>
<span style="color:#64748b;font-size:11px"><?php echo htmlspecialchars($a->status ?? ''); ?> — <?php echo htmlspecialchars($a->plan_type ?? ''); ?></span>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($recentTickets)): ?>
<div style="font-size:12px;color:#64748b;margin-top:10px;margin-bottom:6px">Open Tickets</div>
<?php foreach ($recentTickets as $t): ?>
<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px">
<span>#<?php echo $t->id; ?> — <?php echo htmlspecialchars(substr($t->subject ?? '', 0, 40)); ?></span>
<a href="/admin/support/tickets" style="color:var(--accent);font-size:11px;text-decoration:none">View</a>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (empty($recentOrders) && empty($recentAccounts) && empty($recentTickets)): ?>
<p style="color:#64748b;font-size:13px">No recent activity yet.</p>
<?php endif; ?>
</div>

<div class="card">
<h3 style="color:var(--accent);font-size:14px;margin-bottom:12px"><i class="fas fa-bolt"></i> Quick Actions</h3>
<a href="/admin/account" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(0,140,255,.08);border:1px solid rgba(0,191,255,.12);border-radius:8px;text-decoration:none;color:#fff;margin-bottom:6px;transition:.2s;font-size:13px"><i class="fas fa-user-plus" style="color:var(--accent)"></i> Create Account</a>
<a href="/admin/account" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(248,113,113,.06);border:1px solid rgba(248,113,113,.12);border-radius:8px;text-decoration:none;color:#fff;margin-bottom:6px;transition:.2s;font-size:13px"><i class="fas fa-pause-circle" style="color:#f87171"></i> Suspend Account</a>
<a href="/admin/packages" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(74,222,128,.06);border:1px solid rgba(74,222,128,.12);border-radius:8px;text-decoration:none;color:#fff;margin-bottom:6px;transition:.2s;font-size:13px"><i class="fas fa-box" style="color:#4ade80"></i> Manage Packages</a>
<a href="/admin/backup" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(251,191,36,.06);border:1px solid rgba(251,191,36,.12);border-radius:8px;text-decoration:none;color:#fff;margin-bottom:6px;transition:.2s;font-size:13px"><i class="fas fa-database" style="color:#fbbf24"></i> Backups</a>
<a href="/admin/security" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(168,85,247,.06);border:1px solid rgba(168,85,247,.12);border-radius:8px;text-decoration:none;color:#fff;margin-bottom:6px;transition:.2s;font-size:13px"><i class="fas fa-shield-alt" style="color:#a855f7"></i> Security Center</a>
<a href="/admin/support" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(236,72,153,.06);border:1px solid rgba(236,72,153,.12);border-radius:8px;text-decoration:none;color:#fff;transition:.2s;font-size:13px"><i class="fas fa-headset" style="color:#ec4899"></i> Support Center</a>
</div>
</div>

<!-- ─── Summary Grid ─── -->
<div class="card">
<h3 style="color:var(--accent);font-size:14px;margin-bottom:12px"><i class="fas fa-th-large"></i> Module Access</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px">
<a href="/admin/account" style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:8px;text-decoration:none;color:#e0e0e0;font-size:13px;transition:.2s"><i class="fas fa-users" style="color:var(--accent);width:16px"></i> Accounts</a>
<a href="/admin/packages" style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:8px;text-decoration:none;color:#e0e0e0;font-size:13px;transition:.2s"><i class="fas fa-cube" style="color:#4ade80;width:16px"></i> Packages</a>
<a href="/admin/dns" style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:8px;text-decoration:none;color:#e0e0e0;font-size:13px;transition:.2s"><i class="fas fa-globe" style="color:#facc15;width:16px"></i> DNS Zones</a>
<a href="/admin/email" style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:8px;text-decoration:none;color:#e0e0e0;font-size:13px;transition:.2s"><i class="fas fa-envelope" style="color:#fb923c;width:16px"></i> Email</a>
<a href="/admin/mysql" style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:8px;text-decoration:none;color:#e0e0e0;font-size:13px;transition:.2s"><i class="fas fa-database" style="color:#a78bfa;width:16px"></i> Databases</a>
<a href="/admin/ftp" style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:8px;text-decoration:none;color:#e0e0e0;font-size:13px;transition:.2s"><i class="fas fa-upload" style="color:#34d399;width:16px"></i> FTP</a>
<a href="/admin/billing" style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:8px;text-decoration:none;color:#e0e0e0;font-size:13px;transition:.2s"><i class="fas fa-credit-card" style="color:#f472b6;width:16px"></i> Billing</a>
<a href="/admin/livechat" style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:8px;text-decoration:none;color:#e0e0e0;font-size:13px;transition:.2s"><i class="fas fa-comment-dots" style="color:#38bdf8;width:16px"></i> Live Chat</a>
<a href="/admin/ssl" style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:8px;text-decoration:none;color:#e0e0e0;font-size:13px;transition:.2s"><i class="fas fa-lock" style="color:#e879f9;width:16px"></i> SSL</a>
<a href="/admin/serverconfig" style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:8px;text-decoration:none;color:#e0e0e0;font-size:13px;transition:.2s"><i class="fas fa-sliders-h" style="color:#fbbf24;width:16px"></i> Server Config</a>
<a href="http://45.61.59.55:2096/" target="_blank" style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:8px;text-decoration:none;color:#e0e0e0;font-size:13px;transition:.2s"><i class="fas fa-envelope" style="color:#fb923c;width:16px"></i> Webmail (2096)</a>
</div>
</div>

<script>
// Auto-refresh server health every 15 seconds
setInterval(function() {
    var x = new XMLHttpRequest();
    x.open('GET', '/admin/dashboard/health', true);
    x.onload = function() {
        try {
            var d = JSON.parse(x.responseText);
            var el = document.getElementById('serverHealth');
            if (el && d) {
                el.innerHTML = '<div style=\"display:grid;grid-template-columns:1fr 1fr;gap:8px\">' +
                    '<div><span style=\"color:#64748b;font-size:12px\">Hostname</span><div style=\"font-size:14px;font-weight:600\">' + d.hostname + '</div></div>' +
                    '<div><span style=\"color:#64748b;font-size:12px\">Uptime</span><div style=\"font-size:14px;font-weight:600\">' + d.uptime + '</div></div>' +
                    '<div><span style=\"color:#64748b;font-size:12px\">CPU</span><div style=\"font-size:14px\">' + d.cpu + '</div></div>' +
                    '<div><span style=\"color:#64748b;font-size:12px\">Load</span><div style=\"font-size:14px\">' + d.load + '</div></div>' +
                    '</div>' +
                    '<div style=\"margin-top:10px\"><div style=\"display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:2px\"><span>RAM</span><span>' + d.ram + '</span></div><div style=\"height:6px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden\"><div style=\"height:100%;width:' + d.ram_pct + '%;background:linear-gradient(90deg,#008cff,#3bb8ff);border-radius:3px\"></div></div></div>' +
                    '<div style=\"margin-top:8px\"><div style=\"display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:2px\"><span>Disk</span><span>' + d.disk + '</span></div><div style=\"height:6px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden\"><div style=\"height:100%;width:' + d.disk_pct + '%;background:linear-gradient(90deg,#f59e0b,#ef4444);border-radius:3px\"></div></div></div>';
            }
        } catch(e) {}
    };
    x.send();
}, 15000);
</script>
