<?php $theme_settings = json_decode($user->theme_settings ?? '{}', true); ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2>Activity Log: <?php echo htmlspecialchars($dj->username); ?></h2>
    <a href="/user/dj-panel/show/<?php echo $dj->id; ?>" class="btn secondary">← Back to DJ</a>
</div>

<div class="card">
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
        <input type="text" id="activitySearch" placeholder="Search activity..." class="inp" style="width:250px">
        <select id="actionFilter" class="inp" style="width:180px">
            <option value="">All Actions</option>
            <option value="login">Login</option>
            <option value="logout">Logout</option>
            <option value="api_request">API Request</option>
            <option value="stream_config_view">Stream Config View</option>
            <option value="stream_config_update">Stream Config Update</option>
            <option value="api_key_create">API Key Created</option>
            <option value="api_key_revoke">API Key Revoked</option>
            <option value="station_assign">Station Assigned</option>
            <option value="station_unassign">Station Unassigned</option>
        </select>
        <select id="daysFilter" class="inp" style="width:140px">
            <option value="">All Time</option>
            <option value="1">Last 24 Hours</option>
            <option value="7">Last 7 Days</option>
            <option value="30">Last 30 Days</option>
        </select>
    </div>

    <?php if (empty($activity)): ?>
    <p style="color:var(--text-muted);padding:24px;text-align:center">No activity recorded yet.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                    <th>Metadata</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activity as $a): ?>
                <tr>
                    <td style="white-space:nowrap;font-size:12px"><?php echo date('M j, Y H:i:s', strtotime($a->created_at)); ?></td>
                    <td><span class="badge badge-<?php 
                        $c = 'primary';
                        if (str_contains($a->action, 'login')) $c = 'success';
                        elseif (str_contains($a->action, 'logout')) $c = 'secondary';
                        elseif (str_contains($a->action, 'revoke') || str_contains($a->action, 'unassign')) $c = 'danger';
                        elseif (str_contains($a->action, 'create') || str_contains($a->action, 'assign')) $c = 'warning';
                        echo $c;
                    ?>"><?php echo ucfirst(str_replace('_', ' ', $a->action)); ?></span></td>
                    <td style="color:var(--text-secondary);font-size:13px"><?php echo htmlspecialchars($a->description ?? '—'); ?></td>
                    <td style="font-family:monospace;font-size:11px"><?php echo htmlspecialchars($a->ip_address ?? '—'); ?></td>
                    <td style="font-size:11px;color:var(--text-muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo htmlspecialchars($a->user_agent ?? '—'); ?></td>
                    <td style="font-size:11px;font-family:monospace;color:var(--text-muted);max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?php echo $a->metadata ? htmlspecialchars(json_encode(json_decode($a->metadata, true) ?? [])) : '—'; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:center;gap:8px;margin-top:16px;flex-wrap:wrap">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?php echo $i; ?><?php echo isset($_GET['action']) ? '&action=' . urlencode($_GET['action']) : ''; ?><?php echo isset($_GET['days']) ? '&days=' . urlencode($_GET['days']) : ''; ?>"
           class="btn btn-sm <?php echo $i == $currentPage ? 'primary' : 'secondary'; ?>">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
document.getElementById('activitySearch')?.addEventListener('input', function() {
    const val = this.value.toLowerCase().trim();
    document.querySelectorAll('tbody tr').forEach(function(row) {
        row.style.display = !val || row.textContent.toLowerCase().indexOf(val) > -1 ? '' : 'none';
    });
});

document.getElementById('actionFilter')?.addEventListener('change', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(function(row) {
        var action = row.querySelector('td:nth-child(2)')?.textContent?.toLowerCase();
        row.style.display = !val || action?.includes(val) ? '' : 'none';
    });
});

document.getElementById('daysFilter')?.addEventListener('change', function() {
    var url = new URL(window.location.href);
    if (this.value) url.searchParams.set('days', this.value);
    else url.searchParams.delete('days');
    url.searchParams.delete('page');
    window.location.href = url.toString();
});
</script>