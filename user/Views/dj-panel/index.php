<?php $theme_settings = json_decode($user->theme_settings ?? '{}', true); ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['generated_key'])): ?>
<div class="alert alert-success" style="border:2px solid #22c55e;background:rgba(34,197,94,.08)">
  <strong style="font-size:13px">🔑 Key Generated — Copy it now!</strong>
  <div style="font-size:11px;color:#64748b;margin:4px 0 8px">This key will not be shown again.</div>
  <div style="display:flex;gap:6px;align-items:center">
    <code id="genKeyDisplay" style="flex:1;padding:8px 12px;border-radius:6px;font-size:13px;background:rgba(0,0,0,.4);border:1px solid rgba(0,191,255,.2);color:#22c55e;font-family:monospace;font-size:11px"><?php echo htmlspecialchars($_SESSION['generated_key']); unset($_SESSION['generated_key']); ?></code>
    <button class="btn primary" onclick="copyKey()" style="white-space:nowrap">📋 Copy</button>
  </div>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2>DJ Panel</h2>
    <a href="/user/dj-panel/create" class="btn primary">➕ Create DJ Account</a>
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
                </tr>
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
                        <div class="btn-group btn-group-sm">
                            <a href="/user/dj-panel/show/<?php echo $dj->id; ?>" class="btn btn-sm secondary">View</a>
                            <a href="/user/dj-panel/edit/<?php echo $dj->id; ?>" class="btn btn-sm secondary">Edit</a>
                            <a href="/user/dj-panel/api-keys/<?php echo $dj->id; ?>" class="btn btn-sm secondary" style="margin-right:4px">API Keys</a>
                            <a href="/user/dj-panel/streams/<?php echo $dj->id; ?>" class="btn btn-sm primary" style="margin-right:4px">Streams</a>
                            <a href="/user/dj-panel/stations/<?php echo $dj->id; ?>" class="btn btn-sm primary" style="margin-right:4px">Stations</a>
                            <a href="/user/dj-panel/activity/<?php echo $dj->id; ?>" class="btn btn-sm secondary" style="margin-right:4px">Activity</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('searchInput')?.addEventListener('input', function() {
    const val = this.value.toLowerCase().trim();
    document.querySelectorAll('tbody tr').forEach(function(a) {
        var m = !val || a.textContent.toLowerCase().indexOf(val) > -1;
        a.style.display = m ? '' : 'none';
    });
});

document.getElementById('statusFilter')?.addEventListener('change', function() {
    const val = this.value;
    document.querySelectorAll('tbody tr').forEach(function(a) {
        var s = a.querySelector('.status-badge')?.textContent?.toLowerCase();
        a.style.display = !val || s === val ? '' : 'none';
    });
});

document.getElementById('roleFilter')?.addEventListener('change', function() {
    const val = this.value;
    document.querySelectorAll('tbody tr').forEach(function(a) {
        var r = a.querySelector('td:nth-child(4)')?.textContent?.toLowerCase();
        a.style.display = !val || r === val ? '' : 'none';
    });
});
</script>