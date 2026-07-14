<?php $theme_settings = json_decode($user->theme_settings ?? '{}', true); ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2>DJ Management</h2>
    <a href="/admin/dj/create" class="btn primary">➕ Create DJ</a>
</div>

<div class="stats-grid" style="margin-bottom:24px">
    <div class="stat-card"><h3>Total DJs</h3><div class="value"><?php echo $stats['total']; ?></div></div>
    <div class="stat-card"><h3>Active</h3><div class="value" style="color:#4ade80"><?php echo $stats['active']; ?></div></div>
    <div class="stat-card"><h3>Inactive</h3><div class="value" style="color:#f87171"><?php echo $stats['inactive']; ?></div></div>
    <div class="stat-card"><h3>Suspended</h3><div class="value" style="color:#fb923c"><?php echo $stats['suspended']; ?></div></div>
</div>

<div class="card">
    <div style="display:flex;gap:8px;margin-bottom:16px">
        <input type="text" id="searchInput" placeholder="Search DJs..." class="inp" style="width:250px">
        <select id="statusFilter" class="inp" style="width:150px">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
        </select>
        <select id="roleFilter" class="inp" style="width:150px">
            <option value="">All Roles</option>
            <option value="super_admin">Super Admin</option>
            <option value="station_manager">Station Manager</option>
            <option value="dj">DJ</option>
            <option value="guest_dj">Guest DJ</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Stations</th>
                    <th>Last Login</th>
                    <th>Created</th>
                    <th></th>
                </thead>
                <tbody>
                    <?php foreach ($djs as $dj): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($dj->username); ?></td>
                        <td><?php echo htmlspecialchars($dj->email ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($dj->full_name ?? '-'); ?></td>
                        <td><span class="badge badge-<?php echo $dj->role === 'super_admin' ? 'danger' : ($dj->role === 'station_manager' ? 'warning' : 'primary'); ?>"><?php echo ucfirst($dj->role); ?></span></td>
                        <td><span class="status-badge status-<?php echo $dj->status; ?>"><?php echo ucfirst($dj->status); ?></span></td>
                        <td><?php echo $dj->station_count ?? 0; ?></td>
                        <td><?php echo $dj->last_login ? date('M j, Y H:i', strtotime($dj->last_login)) : 'Never'; ?></td>
                        <td><?php echo date('M j, Y', strtotime($dj->created_at)); ?></td>
                        <td>
                            <a href="/admin/dj/show/<?php echo $dj->id; ?>" class="btn btn-sm secondary" style="margin-right:4px">View</a>
                            <a href="/admin/dj/edit/<?php echo $dj->id; ?>" class="btn btn-sm secondary" style="margin-right:4px">Edit</a>
                            <a href="/admin/dj/stations/<?php echo $dj->id; ?>" class="btn btn-sm primary" style="margin-right:4px">Stations</a>
                            <a href="/admin/dj/api-keys/<?php echo $dj->id; ?>" class="btn btn-sm secondary" style="margin-right:4px">API Keys</a>
                            <a href="/admin/dj/destroy/<?php echo $dj->id; ?>" class="btn btn-sm danger" onclick="return confirm('Suspend this DJ?')">Suspend</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('searchInput')?.addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});

document.getElementById('statusFilter')?.addEventListener('change', function() {
    const val = this.value;
    document.querySelectorAll('tbody tr').forEach(row => {
        const status = row.querySelector('.status-badge')?.textContent?.toLowerCase();
        row.style.display = !val || status === val ? '' : 'none';
    });
});

document.getElementById('roleFilter')?.addEventListener('change', function() {
    const val = this.value;
    document.querySelectorAll('tbody tr').forEach(row => {
        const role = row.querySelector('td:nth-child(4)')?.textContent?.toLowerCase();
        row.style.display = !val || role === val ? '' : 'none';
    });
});
</script>