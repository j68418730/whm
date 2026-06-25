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
<span style="width:8px;height:8px;border-radius:50%;background:<?php echo $svc['active'] ? '#4ade80' : ((($svc['status'] ?? '') === 'active' || ($svc['status'] ?? '') === '') ? '#64748b' : '#facc15'); ?>;flex-shrink:0"></span>
<span style="color:<?php echo $svc['active'] ? '#e0e0e0' : ((($svc['status'] ?? '') === 'active' || ($svc['status'] ?? '') === '') ? '#64748b' : '#facc15'); ?>"><?php echo htmlspecialchars($svc['name']); ?></span>
</div>
<?php endforeach; ?>
</div>
</div>
</div>

<!-- ─── Hostname / SSL / DNS Status ─── -->
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);font-size:14px;margin-bottom:12px"><i class="fas fa-globe"></i> Hostname &amp; SSL Status</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px" id="hostnameStatus">
<div><span style="color:#64748b;font-size:12px">Hostname</span><div style="font-size:14px;font-weight:600"><?php echo htmlspecialchars($server['hostname'] ?? 'N/A'); ?></div></div>
<div><span style="color:#64748b;font-size:12px">Public IP</span><div style="font-size:14px"><?php echo htmlspecialchars($server['ip'] ?? $server['public_ip'] ?? 'N/A'); ?></div></div>
<div><span style="color:#64748b;font-size:12px">SSL</span><div style="font-size:14px" id="sslStatusDisplay"><span style="color:#64748b">Checking...</span></div></div>
<div><span style="color:#64748b;font-size:12px">DNS</span><div style="font-size:14px" id="dnsStatusDisplay"><span style="color:#64748b">Checking...</span></div></div>
<div><span style="color:#64748b;font-size:12px">Panel URL</span><div style="font-size:14px"><a href="https://<?php echo htmlspecialchars($server['hostname'] ?? ''); ?>" style="color:var(--accent);text-decoration:none" target="_blank">https://<?php echo htmlspecialchars($server['hostname'] ?? ''); ?> <i class="fas fa-external-link-alt" style="font-size:10px"></i></a></div></div>
</div>
</div>

<script>
// Fetch hostname health on page load
fetch('/admin/hostname/health').then(function(r){return r.json()}).then(function(d){
    var sslEl = document.getElementById('sslStatusDisplay');
    if (sslEl && d.ssl_status) {
        var color = d.ssl_status === 'valid' ? '#4ade80' : '#f87171';
        var label = d.ssl_status === 'valid' ? 'Valid (' + d.ssl_days_left + ' days)' : 'Missing';
        sslEl.innerHTML = '<span style="color:' + color + '">' + label + '</span>';
    }
    var dnsEl = document.getElementById('dnsStatusDisplay');
    if (dnsEl && d.dns_resolves !== undefined) {
        var color = d.dns_resolves ? '#4ade80' : '#f87171';
        var label = d.dns_resolves ? 'Resolves (' + d.resolved_ip + ')' : 'Not resolving';
        dnsEl.innerHTML = '<span style="color:' + color + '">' + label + '</span>';
    }
}).catch(function(){});
</script>

<!-- ─── Quick Actions + Recent Activity ─── -->
<div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;margin-bottom:16px">
<div class="card">
<h3 style="color:var(--accent);font-size:14px;margin-bottom:12px"><i class="fas fa-bolt"></i> Quick Actions</h3>
<a href="/admin/account" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(0,140,255,.08);border:1px solid rgba(0,191,255,.12);border-radius:8px;text-decoration:none;color:#fff;margin-bottom:6px;transition:.2s;font-size:13px"><i class="fas fa-user-plus" style="color:var(--accent)"></i> Create Account</a>
<a href="/admin/account" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(248,113,113,.06);border:1px solid rgba(248,113,113,.12);border-radius:8px;text-decoration:none;color:#fff;margin-bottom:6px;transition:.2s;font-size:13px"><i class="fas fa-pause-circle" style="color:#f87171"></i> Suspend Account</a>
<a href="/admin/packages" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(74,222,128,.06);border:1px solid rgba(74,222,128,.12);border-radius:8px;text-decoration:none;color:#fff;margin-bottom:6px;transition:.2s;font-size:13px"><i class="fas fa-box" style="color:#4ade80"></i> Manage Packages</a>
<a href="/admin/backup" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(251,191,36,.06);border:1px solid rgba(251,191,36,.12);border-radius:8px;text-decoration:none;color:#fff;margin-bottom:6px;transition:.2s;font-size:13px"><i class="fas fa-database" style="color:#fbbf24"></i> Backups</a>
<a href="/admin/security" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(168,85,247,.06);border:1px solid rgba(168,85,247,.12);border-radius:8px;text-decoration:none;color:#fff;margin-bottom:6px;transition:.2s;font-size:13px"><i class="fas fa-shield-alt" style="color:#a855f7"></i> Security Center</a>
<a href="/admin/support" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(236,72,153,.06);border:1px solid rgba(236,72,153,.12);border-radius:8px;text-decoration:none;color:#fff;transition:.2s;font-size:13px"><i class="fas fa-headset" style="color:#ec4899"></i> Support Center</a>
</div>

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
</div>

<!-- ─── Widget System: Customizable Dashboard Zones ─── -->
<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<h3 style="color:var(--primary,#008cff);font-size:14px;margin:0"><i class="bi bi-grid-3x3-gap"></i> Dashboard Widgets</h3>
<div>
<button class="btn btn-sm btn-secondary" onclick="document.getElementById('widgetPicker').classList.toggle('hidden')">+ Add Widget</button>
</div>
</div>

<div id="widgetPicker" class="hidden" style="margin-bottom:12px;padding:12px;background:rgba(255,255,255,.02);border:1px solid var(--border,rgba(0,191,255,.1));border-radius:8px">
<div style="display:flex;gap:8px;flex-wrap:wrap">
<?php foreach ($all_widgets as $key => $w): ?>
<button class="btn btn-sm btn-secondary add-widget-btn" data-key="<?php echo $key; ?>"><i class="bi <?php echo $w->getIcon(); ?>"></i> <?php echo htmlspecialchars($w->getName()); ?></button>
<?php endforeach; ?>
</div>
</div>

<div class="widget-zone" id="widget-zone-main" data-zone="main">
<?php echo $widgets_main; ?>
</div>
</div>

<div class="card">
<div class="widget-zone" id="widget-zone-side" data-zone="side" style="min-height:60px">
<h3 style="color:var(--text_muted,#64748b);font-size:12px;margin:0 0 8px">Side Widgets</h3>
<?php echo $widgets_side; ?>
</div>
</div>

<style>
.widget-item{background:var(--card_bg,rgba(8,16,28,.8));border:1px solid var(--border,rgba(0,191,255,.1));border-radius:10px;margin-bottom:10px;overflow:hidden}
.widget-item.dragging{opacity:.5;border-style:dashed}
.widget-item.drag-over{border-color:var(--primary,#008cff);box-shadow:0 0 15px rgba(0,140,255,.15)}
.widget-header{display:flex;align-items:center;gap:8px;padding:8px 12px;border-bottom:1px solid var(--border,rgba(0,191,255,.06));cursor:move;background:rgba(0,0,0,.15)}
.widget-header .widget-handle{color:var(--text_muted,#64748b);font-size:14px;cursor:grab}
.widget-header .widget-title{flex:1;font-size:12px;font-weight:600}
.widget-actions{display:flex;gap:4px}
.widget-actions .btn-icon{background:none;border:none;color:var(--text_muted,#64748b);cursor:pointer;padding:2px 6px;border-radius:4px;font-size:12px}
.widget-actions .btn-icon:hover{background:rgba(255,255,255,.06);color:#fff}
.widget-body{padding:12px}
.widget-body .stats-grid{margin:0}
.widget-body table{font-size:12px}
.widget-body td,.widget-body th{padding:4px 6px}
.hidden{display:none!important}
.widget-empty{text-align:center;padding:30px;color:var(--text_muted,#64748b);font-size:13px}
.widget-zone.drag-over-zone{background:rgba(0,140,255,.03);border-radius:8px}
</style>

<script>
// Drag and drop with cross-zone and intra-zone reordering
var dragSrcId = null;
document.querySelectorAll('.widget-item[draggable]').forEach(function(el) {
    el.addEventListener('dragstart', function(e) {
        dragSrcId = this.dataset.widgetId;
        e.dataTransfer.setData('text/plain', this.dataset.widgetId);
        this.classList.add('dragging');
    });
    el.addEventListener('dragend', function(e) {
        this.classList.remove('dragging');
        dragSrcId = null;
    });
    el.addEventListener('dragover', function(e) {
        e.preventDefault();
        var rect = this.getBoundingClientRect();
        var y = e.clientY - rect.top;
        if (y < rect.height / 2) this.style.borderTop = '2px solid var(--primary)';
        else this.style.borderBottom = '2px solid var(--primary)';
    });
    el.addEventListener('dragleave', function(e) {
        this.style.borderTop = '';
        this.style.borderBottom = '';
    });
    el.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderTop = '';
        this.style.borderBottom = '';
        var id = e.dataTransfer.getData('text/plain');
        if (!id || id === this.dataset.widgetId) return;
        var widget = document.querySelector('[data-widget-id="' + id + '"]');
        if (!widget) return;
        var rect = this.getBoundingClientRect();
        var y = e.clientY - rect.top;
        if (y < rect.height / 2) this.parentNode.insertBefore(widget, this);
        else this.parentNode.insertBefore(widget, this.nextSibling);
        saveWidgetLayout();
    });
});

document.querySelectorAll('.widget-zone').forEach(function(zone) {
    zone.addEventListener('dragover', function(e) {
        e.preventDefault();
        zone.classList.add('drag-over-zone');
    });
    zone.addEventListener('dragleave', function(e) {
        zone.classList.remove('drag-over-zone');
    });
    zone.addEventListener('drop', function(e) {
        e.preventDefault();
        zone.classList.remove('drag-over-zone');
        var id = e.dataTransfer.getData('text/plain');
        if (!id) return;
        var widget = document.querySelector('[data-widget-id="' + id + '"]');
        var target = e.target.closest('.widget-item');
        if (widget) {
            if (target && target.parentElement === zone) {
                var rect = target.getBoundingClientRect();
                var y = e.clientY - rect.top;
                if (y < rect.height / 2) target.parentNode.insertBefore(widget, target);
                else target.parentNode.insertBefore(widget, target.nextSibling);
            } else {
                zone.appendChild(widget);
            }
            saveWidgetLayout();
        }
    });
});

function saveWidgetLayout() {
    var layout = [];
    document.querySelectorAll('.widget-zone').forEach(function(zone) {
        var zoneName = zone.dataset.zone;
        zone.querySelectorAll('.widget-item').forEach(function(w, i) {
            if (w.dataset.widgetId) layout.push({id: parseInt(w.dataset.widgetId), zone: zoneName, sort_order: i});
        });
    });
    fetch('/admin/widgets/save-layout', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({layout: layout})
    }).catch(function(e){});
}

// Remove widget
document.querySelectorAll('.widget-remove').forEach(function(btn) {
    btn.addEventListener('click', function() {
        if (!confirm('Remove this widget?')) return;
        var key = this.dataset.key;
        var item = this.closest('.widget-item');
        var form = new FormData();
        form.append('key', key);
        fetch('/admin/widgets/remove', {method: 'POST', body: form}).then(function(r) {
            item.remove();
        });
    });
});

// Add widget
document.querySelectorAll('.add-widget-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var key = this.dataset.key;
        var form = new FormData();
        form.append('key', key);
        form.append('zone', 'main');
        fetch('/admin/widgets/add', {method: 'POST', body: form}).then(function(r) {
            location.reload();
        });
    });
});
</script>

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
