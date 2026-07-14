<?php $theme_settings = json_decode($user->theme_settings ?? '{}', true); ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2>Manage Stations for DJ: <?php echo htmlspecialchars($dj->username); ?></h2>
    <a href="/admin/dj/show/<?php echo $dj->id; ?>" class="btn secondary">← Back to DJ</a>
</div>

<div class="card mb-24">
    <h3>Assigned Stations</h3>
    <?php if (empty($assigned)): ?>
    <p style="color:var(--text-muted);padding:24px;text-align:center">No stations assigned yet.</    <?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Station</th>
                    <th>Domain</th>
                    <th>Role</th>
                    <th>Assigned</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assigned as $a): ?>
                <tr>
                    <td><?php echo htmlspecialchars($a->username); ?></td>
                    <td><?php echo htmlspecialchars($a->domain ?? 'No domain'); ?></td>
                    <td><span class="badge badge-<?php echo $a->role === 'owner' ? 'danger' : ($a->role === 'manager' ? 'warning' : 'primary'); ?>"><?php echo ucfirst($a->role); ?></span></td>
                    <td><?php echo date('M j, Y', strtotime($a->assigned_at)); ?></td>
                    <td>
                        <button class="btn btn-sm danger" onclick="confirmUnassign(<?php echo $dj->id; ?>, <?php echo $a->station_id; ?>)">Unassign</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Available Stations</h3>
    <?php if (empty($stations)): ?>
    <p style="color:var(--text-muted);padding:24px;text-align:center">No stations available to assign.</    <?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Station</th>
                    <th>Domain</th>
                    <th>Package</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stations as $station): ?>
                <?php if (in_array($station->id, $assignedIds)) continue; ?>
                <tr>
                    <td><?php echo htmlspecialchars($station->username); ?></td>
                    <td><?php echo htmlspecialchars($station->domain ?? 'No domain'); ?></td>
                    <td><?php echo htmlspecialchars($station->package_id ?? 'Default'); ?></td>
                    <td>
                        <form method="POST" action="/admin/dj/stations/assign/<?php echo $dj->id; ?>" style="display:inline">
                            <input type="hidden" name="station_id" value="<?php echo $station->id; ?>">
                            <select name="role" class="inp" style="width:auto;margin-right:8px">
                                <option value="dj">DJ</option>
                                <option value="manager">Manager</option>
                                <option value="guest_dj">Guest DJ</option>
                            </select>
                            <button type="submit" class="btn btn-sm primary">Assign</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
function confirmUnassign(djId, stationId) {
    if (confirm('Unassign this station from the DJ?')) {
        window.location.href = '/admin/dj/stations/unassign/' + djId + '/' + stationId;
    }
}
</script>