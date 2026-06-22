<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Total Notifications</h3><div class="value" id="notif-total"><?php echo count($notifications ?? []); ?></div></div>
<div class="stat-card"><h3>Unread</h3><div class="value" id="notif-unread" style="color:#facc15"><?php echo (int)($unread ?? 0); ?></div></div>
<div class="stat-card"><h3>Email Alerts</h3><div class="value"><?php echo (($settings['email_notifications_enabled'] ?? '0') === '1') ? 'On' : 'Off'; ?></div></div>
<div class="stat-card"><h3>SMS Alerts</h3><div class="value"><?php echo (($settings['sms_notifications_enabled'] ?? '0') === '1') ? 'On' : 'Off'; ?></div></div>
</div>

<div class="card" style="margin-bottom:20px">
<div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap">
<h3 style="margin:0;color:var(--primary)">Alert Settings</h3>
<a href="/admin/automation" class="btn btn-sm btn-secondary">Open Automation Settings</a>
</div>
<div style="margin-top:12px;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px">
<div class="stat-card"><h3>Admin Email</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($settings['notify_admin_email'] ?? '-'); ?></div></div>
<div class="stat-card"><h3>SMTP Host</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($settings['smtp_host'] ?? '-'); ?></div></div>
<div class="stat-card"><h3>SMS Provider</h3><div class="value" style="font-size:16px"><?php echo htmlspecialchars($settings['sms_provider'] ?? '-'); ?></div></div>
</div>
</div>

<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<h3 style="margin:0;color:var(--primary)">Recent Notifications <span id="live-badge" style="font-size:11px;color:#4ade80;font-weight:400">● LIVE</span></h3>
<button class="btn btn-sm btn-primary" onclick="markAllRead()" id="markAllBtn">✓ Mark All Read</button>
</div>
<div style="max-height:500px;overflow-y:auto">
<table id="notif-table">
<thead><tr><th>Type</th><th>Title</th><th>Message</th><th>State</th><th>Created</th><th></th></tr></thead>
<tbody>
<?php if (!empty($notifications)): foreach ($notifications as $n): ?>
<tr data-id="<?php echo $n->id; ?>" class="<?php echo empty($n->read_at) ? 'notif-unread' : 'notif-read'; ?>">
<td><span class="badge <?php echo $n->type === 'storage' ? 'bg-warning' : ($n->type === 'error' ? 'bg-danger' : 'bg-info'); ?>"><?php echo htmlspecialchars($n->type ?? 'info'); ?></span></td>
<td><?php echo htmlspecialchars($n->title ?? '-'); ?></td>
<td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?php echo htmlspecialchars($n->message ?? ''); ?>"><?php echo htmlspecialchars($n->message ?? '-'); ?></td>
<td><?php echo empty($n->read_at) ? '<span class="text-warning">Unread</span>' : 'Read'; ?></td>
<td style="font-size:11px;white-space:nowrap"><?php echo htmlspecialchars($n->created_at ?? '-'); ?></td>
<td style="white-space:nowrap"><?php if (empty($n->read_at)): ?><button class="btn btn-sm btn-secondary" onclick="markRead(<?php echo $n->id; ?>,this)" style="padding:2px 8px;font-size:10px">✓</button><?php endif; ?> <button class="btn btn-sm btn-danger" onclick="deleteNotif(<?php echo $n->id; ?>,this)" style="padding:2px 8px;font-size:10px">🗑</button></td>
</tr>
<?php endforeach; else: ?>
<tr id="notif-empty"><td colspan="6" style="text-align:center;color:var(--text_muted);padding:20px">No notifications yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<style>
.notif-unread{background:rgba(250,204,21,.03)}
.notif-unread td:first-child{border-left:3px solid #facc15}
#live-badge{animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
</style>

<script>
var lastFetch = '<?php echo date('Y-m-d H:i:s', strtotime('-5 minutes')); ?>';

function deleteNotif(id, btn) {
    if (!confirm('Delete this notification?')) return;
    fetch('/admin/notifications/delete/' + id, {method:'POST'}).then(function() {
        var row = btn ? btn.closest('tr') : document.querySelector('tr[data-id="' + id + '"]');
        if (row) row.remove();
        updateUnreadCount();
    });
}

function markRead(id, btn) {
    fetch('/admin/notifications/mark-read/' + id, {method:'POST'}).then(function(r) {
        var row = btn ? btn.closest('tr') : document.querySelector('tr[data-id="' + id + '"]');
        if (row) {
            row.classList.remove('notif-unread');
            row.classList.add('notif-read');
            row.querySelector('td:nth-child(4)').innerHTML = 'Read';
            if (btn) btn.remove();
            updateUnreadCount();
        }
    });
}

function markAllRead() {
    fetch('/admin/notifications/mark-all-read', {method:'POST'}).then(function() {
        document.querySelectorAll('.notif-unread').forEach(function(row) {
            row.classList.remove('notif-unread');
            row.classList.add('notif-read');
            row.querySelector('td:nth-child(4)').innerHTML = 'Read';
            var btn = row.querySelector('button');
            if (btn) btn.remove();
        });
        document.getElementById('notif-unread').textContent = '0';
    });
}

function updateUnreadCount() {
    var count = document.querySelectorAll('.notif-unread').length;
    document.getElementById('notif-unread').textContent = count;
}

// Poll for new notifications every 15s
setInterval(function() {
    var x = new XMLHttpRequest();
    x.open('GET', '/admin/notifications/api/latest?since=' + encodeURIComponent(lastFetch), true);
    x.onload = function() {
        try {
            var d = JSON.parse(x.responseText);
            if (d.notifications && d.notifications.length) {
                var tbody = document.querySelector('#notif-table tbody');
                var emptyRow = document.getElementById('notif-empty');
                if (emptyRow) emptyRow.remove();
                d.notifications.forEach(function(n) {
                    var existing = document.querySelector('tr[data-id="' + n.id + '"]');
                    if (existing) return;
                    var tr = document.createElement('tr');
                    tr.setAttribute('data-id', n.id);
                    tr.className = n.read_at ? 'notif-read' : 'notif-unread';
                    tr.innerHTML = '<td><span class="badge ' + (n.type === 'storage' ? 'bg-warning' : (n.type === 'error' ? 'bg-danger' : 'bg-info')) + '">' + n.type + '</span></td>' +
                        '<td>' + n.title + '</td>' +
                        '<td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="' + (n.message||'').replace(/"/g,'&quot;') + '">' + (n.message||'') + '</td>' +
                        '<td>' + (n.read_at ? 'Read' : '<span class="text-warning">Unread</span>') + '</td>' +
                        '<td style="font-size:11px;white-space:nowrap">' + (n.created_at||'') + '</td>' +
                        '<td style="white-space:nowrap">' + (n.read_at ? '' : '<button class="btn btn-sm btn-secondary" onclick="markRead(' + n.id + ',this)" style="padding:2px 8px;font-size:10px">✓</button>') + ' <button class="btn btn-sm btn-danger" onclick="deleteNotif(' + n.id + ',this)" style="padding:2px 8px;font-size:10px">🗑</button></td>';
                    tbody.prepend(tr);
                });
                var total = parseInt(document.getElementById('notif-total').textContent) + d.notifications.length;
                document.getElementById('notif-total').textContent = total;
            }
            if (typeof d.unread !== 'undefined') {
                document.getElementById('notif-unread').textContent = d.unread;
            }
            lastFetch = new Date().toISOString().slice(0,19).replace('T',' ');
        } catch(e) {}
    };
    x.send();
}, 15000);
</script>
