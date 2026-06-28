<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<a href="/admin/migration" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🔄 Migration Center</a>
<a href="/admin/restore-center" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo empty($reportsView) && empty($historyView) && empty($pointsView) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>">🔄 Restore Center</a>
<a href="/admin/restore-center/points" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">⏱ Restore Points</a>
<a href="/admin/restore-center/reports" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📊 Reports</a>
<a href="/admin/restore-center/history" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📜 History</a>
<a href="/admin/restore-center/browse" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo !empty($browseView) || !empty($browseDetailView) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>">📂 Browse Backups</a>
</div>

<?php if (!empty($pointsView)): ?>
<h3 style="color:var(--accent);margin-bottom:12px">⏱ Restore Points</h3>
<?php if (empty($points)): ?>
<div class="card" style="text-align:center;padding:24px;color:#64748b">No restore points yet. They are created automatically before migrations and restores.</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:10px">
<?php foreach ($points as $pt): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="display:flex;justify-content:space-between;align-items:center">
<div>
<span style="font-weight:600;font-size:13px">#<?php echo $pt['id']; ?> <?php echo htmlspecialchars($pt['type']); ?></span>
<span class="status-badge status-<?php echo !empty($pt['favorite']) ? 'active' : ''; ?>" style="font-size:9px;margin-left:6px"><?php echo $pt['status']; ?></span>
</div>
<div style="display:flex;gap:4px">
<a href="/admin/restore-center/rollback/<?php echo $pt['id']; ?>" class="btn btn-sm danger" onclick="return confirm('Rollback to this point? This will restore the data at the time this point was created.')">↩ Rollback</a>
<a href="/admin/restore-center/favorite/<?php echo $pt['id']; ?>" class="btn btn-sm secondary"><?php echo empty($pt['favorite']) ? '☆' : '★'; ?></a>
<a href="/admin/restore-center/delete-point/<?php echo $pt['id']; ?>" class="btn btn-sm danger" onclick="return confirm('Delete this restore point?')">🗑</a>
</div>
</div>
<div style="font-size:10px;color:#64748b;margin-top:4px">
User: <?php echo htmlspecialchars($pt['user_id']); ?> · Type: <?php echo $pt['type']; ?>
</div>
<?php if (!empty($pt['data'])): ?>
<details style="margin-top:4px"><summary style="font-size:10px;color:var(--accent);cursor:pointer">View Data</summary>
<pre style="font-size:9px;background:rgba(0,0,0,.3);padding:6px;border-radius:4px;max-height:100px;overflow-y:auto;margin-top:4px;color:#8b949e"><?php echo htmlspecialchars(json_encode(json_decode($pt['data'], true), JSON_PRETTY_PRINT)); ?></pre>
</details>
<?php endif; ?>
<div style="font-size:9px;color:#64748b;margin-top:4px"><?php echo $pt['created_at']; ?></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php elseif (!empty($reportsView)): ?>
<h3 style="color:var(--accent);margin-bottom:12px">📊 Restore Reports (Last 30 Days)</h3>
<?php if (empty($stats)): ?>
<div class="card" style="text-align:center;padding:24px;color:#64748b">No restore data yet.</div>
<?php else: ?>
<div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));margin-bottom:16px">
<div class="stat-card"><h3>Total</h3><div class="value"><?php echo $stats['total'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Successful</h3><div class="value" style="color:#4ade80"><?php echo $stats['success'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Failed</h3><div class="value" style="color:#f87171"><?php echo $stats['failed'] ?? 0; ?></div></div>
</div>
<?php endif; ?>

<h4 style="color:var(--accent);margin:14px 0 8px">Full History (Last 100)</h4>
<?php if (empty($history)): ?>
<div class="card" style="text-align:center;padding:16px;color:#64748b">No restore history yet.</div>
<?php else: ?>
<div style="overflow-x:auto">
<table style="width:100%;border-collapse:collapse;font-size:11px">
<thead><tr style="background:var(--bg-card);border-bottom:1px solid rgba(255,255,255,.06)">
<th style="padding:6px;text-align:left">ID</th><th style="padding:6px;text-align:left">User</th><th style="padding:6px;text-align:left">Type</th><th style="padding:6px;text-align:left">Items</th><th style="padding:6px;text-align:left">Status</th><th style="padding:6px;text-align:left">Date</th></tr></thead>
<tbody>
<?php foreach ($history as $h): ?>
<tr style="border-bottom:1px solid rgba(255,255,255,.04)">
<td style="padding:6px">#<?php echo $h['id'] ?? '-'; ?></td>
<td style="padding:6px"><?php echo htmlspecialchars($h['user_id'] ?? '-'); ?></td>
<td style="padding:6px"><?php echo $h['type'] ?? '-'; ?></td>
<td style="padding:6px"><?php echo $h['items_restored'] ?? 0; ?>/<?php echo $h['total_items'] ?? 0; ?></td>
<td style="padding:6px"><span class="status-badge status-<?php echo ($h['status'] ?? '') === 'completed' ? 'active' : (($h['status'] ?? '') === 'failed' ? 'terminated' : ''); ?>"><?php echo $h['status'] ?? '-'; ?></span></td>
<td style="padding:6px"><?php echo $h['created_at'] ?? '-'; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>

<?php elseif (!empty($historyView)): ?>
<h3 style="color:var(--accent);margin-bottom:12px">📜 Restore History</h3>
<?php if (empty($history)): ?>
<div class="card" style="text-align:center;padding:24px;color:#64748b">No restore history yet.</div>
<?php else: ?>
<div style="overflow-x:auto">
<table style="width:100%;border-collapse:collapse;font-size:12px">
<thead><tr style="background:var(--bg-card);border-bottom:1px solid rgba(255,255,255,.06)">
<th style="padding:8px;text-align:left">ID</th><th style="padding:8px;text-align:left">User</th><th style="padding:8px;text-align:left">Type</th><th style="padding:8px;text-align:left">Items</th><th style="padding:8px;text-align:left">Status</th><th style="padding:8px;text-align:left">Date</th><th style="padding:8px;text-align:left">Actions</th></tr></thead>
<tbody>
<?php foreach ($history as $h): ?>
<tr style="border-bottom:1px solid rgba(255,255,255,.04)">
<td style="padding:8px">#<?php echo $h['id'] ?? '-'; ?></td>
<td style="padding:8px"><?php echo htmlspecialchars($h['user_id'] ?? '-'); ?></td>
<td style="padding:8px"><?php echo $h['type'] ?? '-'; ?></td>
<td style="padding:8px"><?php echo $h['items_restored'] ?? 0; ?>/<?php echo $h['total_items'] ?? 0; ?></td>
<td style="padding:8px"><span class="status-badge status-<?php echo ($h['status'] ?? '') === 'completed' ? 'active' : (($h['status'] ?? '') === 'failed' ? 'terminated' : ''); ?>"><?php echo $h['status'] ?? '-'; ?></span></td>
<td style="padding:8px"><?php echo $h['created_at'] ?? '-'; ?></td>
<td style="padding:8px">
<?php if (($h['status'] ?? '') === 'pending'): ?>
<a href="/admin/restore-center/execute/<?php echo $h['id']; ?>" class="btn btn-sm primary" onclick="return confirm('Execute this restore?')">▶ Run</a>
<a href="/admin/restore-center/cancel/<?php echo $h['id']; ?>" class="btn btn-sm danger">✕ Cancel</a>
<?php elseif (($h['status'] ?? '') === 'running'): ?>
<a href="/admin/restore-center/pause/<?php echo $h['id']; ?>" class="btn btn-sm secondary">⏸ Pause</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>

<?php elseif (!empty($browseView)): ?>
<h3 style="color:var(--accent);margin-bottom:12px">📂 Browse Backups</h3>
<p style="font-size:12px;color:#64748b;margin-bottom:12px">Select a backup to browse its contents and restore individual items page-by-page.</p>
<?php if (empty($backups)): ?>
<div class="card" style="text-align:center;padding:24px;color:#64748b">No backups found. Create a backup first from the <a href="/admin/backup" style="color:var(--accent)">Backup page</a>.</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:10px">
<?php foreach ($backups as $bk): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="display:flex;justify-content:space-between;align-items:center">
<div>
<span style="font-weight:600;font-size:13px"><?php echo htmlspecialchars($bk['name']); ?></span>
</div>
<a href="/admin/restore-center/browse/<?php echo urlencode($bk['name']); ?>" class="btn btn-sm primary">📂 Browse</a>
</div>
<div style="font-size:10px;color:#64748b;margin-top:6px">
<?php echo $bk['size_formatted']; ?> · <?php echo $bk['date']; ?>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php elseif (!empty($browseDetailView)): ?>
<h3 style="color:var(--accent);margin-bottom:12px">📂 <?php echo htmlspecialchars($contents['backup_name'] ?? ''); ?></h3>
<div style="font-size:11px;color:#64748b;margin-bottom:12px">
Size: <?php echo htmlspecialchars($contents['backup_size'] ?? ''); ?> · 
Date: <?php echo htmlspecialchars($contents['backup_date'] ?? ''); ?> · 
Total items: <?php echo $contents['total'] ?? 0; ?>
</div>
<a href="/admin/restore-center/browse" class="btn btn-sm secondary" style="margin-bottom:12px">← Back to backups</a>

<?php if (!empty($contents['error'])): ?>
<div class="card" style="text-align:center;padding:24px;color:#f87171"><?php echo htmlspecialchars($contents['error']); ?></div>
<?php elseif (empty($contents['files'])): ?>
<div class="card" style="text-align:center;padding:24px;color:#64748b">No files found in this backup.</div>
<?php else: ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="display:flex;gap:6px;margin-bottom:8px">
<input type="text" id="fileFilter" class="form-control" placeholder="Filter files..." style="flex:1;padding:6px 10px;font-size:12px">
<span style="padding:6px 10px;background:rgba(255,255,255,.05);border-radius:6px;font-size:11px;color:#64748b" id="fileCount"><?php echo $contents['total']; ?> files</span>
</div>
<div style="max-height:500px;overflow-y:auto;border:1px solid rgba(255,255,255,.06);border-radius:6px;font-size:11px;font-family:monospace">
<form method="POST" action="/admin/restore-center/restore-item" id="restoreForm">
<input type="hidden" name="filename" value="<?php echo htmlspecialchars($contents['backup_name'] ?? ''); ?>">
<input type="hidden" name="item_path" id="selectedItemPath" value="">
<table style="width:100%;border-collapse:collapse">
<thead><tr style="background:rgba(0,0,0,.3);position:sticky;top:0">
<th style="padding:6px 8px;text-align:left;width:24px"></th>
<th style="padding:6px 8px;text-align:left">Path</th>
<th style="padding:6px 8px;text-align:left">Name</th>
<th style="padding:6px 8px;text-align:left;width:60px">Type</th>
<th style="padding:6px 8px;text-align:left;width:80px">Action</th>
</tr></thead>
<tbody>
<?php foreach ($contents['files'] as $fi): ?>
<tr class="file-row" data-path="<?php echo htmlspecialchars($fi['path']); ?>" style="border-bottom:1px solid rgba(255,255,255,.04)">
<td style="padding:4px 8px">
<input type="radio" name="item_path_radio" value="<?php echo htmlspecialchars($fi['path']); ?>" class="item-radio" style="cursor:pointer">
</td>
<td style="padding:4px 8px;color:#64748b;font-size:10px;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?php echo htmlspecialchars($fi['parent']); ?>">
<?php echo htmlspecialchars($fi['parent'] !== '/' ? $fi['parent'] : ''); ?>
</td>
<td style="padding:4px 8px">
<?php echo $fi['is_dir'] ? '📁' : '📄'; ?>
<span style="<?php echo $fi['is_dir'] ? 'font-weight:600' : ''; ?>"><?php echo htmlspecialchars($fi['name']); ?></span>
</td>
<td style="padding:4px 8px;font-size:10px;color:#64748b"><?php echo $fi['is_dir'] ? 'DIR' : 'FILE'; ?></td>
<td style="padding:4px 8px">
<?php if (!$fi['is_dir']): ?>
<button type="button" class="btn btn-sm primary restore-btn" style="font-size:10px;padding:3px 8px" data-path="<?php echo htmlspecialchars($fi['path']); ?>">Restore</button>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</form>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filter = document.getElementById('fileFilter');
    const rows = document.querySelectorAll('.file-row');
    const count = document.getElementById('fileCount');
    const radios = document.querySelectorAll('.item-radio');

    // Single-selection via radio buttons
    radios.forEach(r => {
        r.addEventListener('change', function() {
            document.querySelectorAll('.file-row').forEach(row => row.style.background = 'transparent');
            if (this.checked) {
                this.closest('tr').style.background = 'rgba(0,191,255,.08)';
            }
        });
    });

    // Filter
    if (filter) {
        filter.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            let visible = 0;
            rows.forEach(row => {
                const path = row.getAttribute('data-path').toLowerCase();
                const match = !q || path.includes(q);
                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            if (count) count.textContent = visible + ' files';
        });
    }

    // Restore buttons
    document.querySelectorAll('.restore-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const path = this.getAttribute('data-path');
            const name = path.split('/').pop();
            if (!confirm('Restore "' + name + '" from backup?\n\nPath: ' + path + '\n\nThis will overwrite the current version.')) return;
            document.getElementById('selectedItemPath').value = path;
            document.getElementById('restoreForm').submit();
        });
    });
});
</script>
<?php endif; ?>

<?php else: ?>
<h3 style="color:var(--accent);margin-bottom:12px">🔄 Restore Center</h3>
<div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));margin-bottom:16px">
<div class="stat-card"><h3>Total Restores (30d)</h3><div class="value"><?php echo $stats['total'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Successful</h3><div class="value" style="color:#4ade80"><?php echo $stats['success'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Failed</h3><div class="value" style="color:#f87171"><?php echo $stats['failed'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Restore Points</h3><div class="value"><?php echo count($points ?? []); ?></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
<div class="card" style="margin-bottom:0;padding:14px">
<h4 style="color:var(--accent);margin-bottom:10px">Quick Restore</h4>
<form method="POST" action="/admin/restore-center/queue">
<div class="form-group"><label>Select User</label>
<select name="user_id" class="form-control" required>
<option value="">Choose...</option>
<?php foreach ($hostingUsers as $hu): ?>
<option value="<?php echo $hu->id; ?>"><?php echo htmlspecialchars($hu->username ?? $hu->email ?? 'User #'.$hu->id); ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Restore Type</label>
<select name="type" class="form-control" id="restoreTypeSelect" required>
<?php foreach ($restoreTypes as $rtype => $rdata): ?>
<option value="<?php echo $rtype; ?>"><?php echo ucfirst($rtype); ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group" id="restoreItemsGroup"><label>Items to Restore</label>
<div style="display:flex;flex-wrap:wrap;gap:6px;">
<?php if (!empty($restoreTypes)): $first = reset($restoreTypes); foreach ($first['items'] ?? [] as $itemKey => $itemLabel): ?>
<label style="font-size:11px;display:flex;align-items:center;gap:4px">
<input type="checkbox" name="restore_items[]" value="<?php echo $itemKey; ?>" checked> <?php echo $itemLabel; ?>
</label>
<?php endforeach; endif; ?>
</div></div>
<div class="form-group"><label>Backup File (optional)</label>
<input name="backup_file" class="form-control" placeholder="path/to/backup.tar.gz"></div>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px">
<label style="font-size:11px;display:flex;align-items:center;gap:4px"><input type="checkbox" name="dry_run" value="1"> Dry Run Only</label>
<label style="font-size:11px;display:flex;align-items:center;gap:4px"><input type="checkbox" name="execute_now" value="1" checked> Execute Now</label>
</div>
<button type="submit" class="btn primary" style="margin-top:8px">Queue Restore</button>
</form>
</div>

<div class="card" style="margin-bottom:0;padding:14px">
<h4 style="color:var(--accent);margin-bottom:10px">Pending Queue</h4>
<?php if (empty($queue)): ?>
<div style="font-size:12px;color:#64748b;text-align:center;padding:12px">No queued jobs.</div>
<?php else: ?>
<div style="display:grid;gap:6px">
<?php foreach ($queue as $q): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:8px;background:var(--bg-card);border-radius:6px">
<div><strong style="font-size:12px">#<?php echo $q['id'] ?? '-'; ?></strong>
<span style="font-size:10px;color:#64748b;margin-left:4px"><?php echo $q['type'] ?? '-'; ?> · User #<?php echo $q['user_id'] ?? '-'; ?></span></div>
<div style="display:flex;gap:4px">
<a href="/admin/restore-center/execute/<?php echo $q['id']; ?>" class="btn btn-sm primary">▶</a>
<a href="/admin/restore-center/cancel/<?php echo $q['id']; ?>" class="btn btn-sm danger">✕</a>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>

<script>
const restoreTypes = <?php echo json_encode($restoreTypes); ?>;
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('restoreTypeSelect');
    const itemsGroup = document.getElementById('restoreItemsGroup');
    function updateItems() {
        const type = typeSelect.value;
        const data = restoreTypes[type];
        if (!data || !data.items) { itemsGroup.innerHTML = '<div style="font-size:11px;color:#64748b">No items for this type</div>'; return; }
        let html = '<label>Items to Restore</label><div style="display:flex;flex-wrap:wrap;gap:6px;">';
        for (const [k, v] of Object.entries(data.items)) {
            html += '<label style="font-size:11px;display:flex;align-items:center;gap:4px"><input type="checkbox" name="restore_items[]" value="' + k + '" checked> ' + v + '</label>';
        }
        html += '</div>';
        itemsGroup.innerHTML = html;
    }
    typeSelect.addEventListener('change', updateItems);
});
</script>
<?php endif; ?>