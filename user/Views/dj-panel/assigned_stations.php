<?php $theme_settings = json_decode($user->theme_settings ?? '{}', true); ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2>Assigned Stations for: <?php echo htmlspecialchars($dj->username); ?></h2>
    <a href="/user/dj-panel/show/<?php echo $dj->id; ?>" class="btn secondary">← Back to DJ</a>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <h3 style="color:var(--accent)">🏷️ Station Assignments</h3>
        <a href="/user/dj-panel/stations/assign/<?php echo $dj->id; ?>" class="btn primary">+ Assign Station</a>
    </div>

    <?php if (empty($stations)): ?>
    <p style="color:var(--text-muted);padding:24px;text-align:center">No stations assigned yet. Click "Assign Station" to add one.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Station</th>
                    <th>Domain</th>
                    <th>Role</th>
                    <th>Permissions</th>
                    <th>Assigned</th>
                    <th>Expires</th>
                    <th>Stream Config</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stations as $s): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($s['station']->username); ?></strong>
                        <?php if (!empty($s['station']->server_name)): ?>
                        <br><span style="font-size:11px;color:#64748b"><?php echo htmlspecialchars($s['station']->server_name); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($s['station']->domain ?? 'No domain'); ?></td>
                    <td><span class="badge badge-<?php echo $s['role'] === 'owner' ? 'danger' : ($s['role'] === 'manager' ? 'warning' : 'primary'); ?>"><?php echo ucfirst($s['role']); ?></span></td>
                    <td>
                        <?php 
                        $perms = json_decode($s['permissions'] ?? '[]', true);
                        if (!empty($perms)) {
                            foreach ($perms as $p) echo '<span class="badge badge-secondary" style="font-size:10px;margin:1px">' . htmlspecialchars($p) . '</span> ';
                        } else {
                            echo '<span style="color:var(--text-muted);font-size:11px">Default</span>';
                        }
                        ?>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($s['assigned_at'])); ?></td>
                    <td><?php echo $s['expires_at'] ? date('M j, Y', strtotime($s['expires_at'])) : 'Never'; ?></td>
                    <td>
                        <?php if (!empty($s['streamConfig'])): ?>
                        <span class="badge badge-success">Configured</span>
                        <br><a href="/user/dj-panel/stream-config/<?php echo $dj->id; ?>/<?php echo $s['station']->id; ?>" class="btn btn-sm secondary" style="margin-top:4px;display:inline-block">View</a>
                        <?php else: ?>
                        <span class="badge badge-warning">Not Configured</span>
                        <br><a href="/user/dj-panel/stream-config/<?php echo $dj->id; ?>/<?php echo $s['station']->id; ?>" class="btn btn-sm primary" style="margin-top:4px;display:inline-block">Configure</a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="/user/dj-panel/stream-config/<?php echo $dj->id; ?>/<?php echo $s['station']->id; ?>" class="btn btn-sm secondary">⚙️ Stream</a>
                            <form method="POST" action="/user/dj-panel/stations/unassign/<?php echo $dj->id; ?>/<?php echo $s['station']->id; ?>" style="display:inline" onsubmit="return confirm('Unassign this station from DJ?');">
                                <button type="submit" class="btn btn-sm danger">✖ Unassign</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="card" style="margin-top:16px">
    <h3 style="color:var(--accent);margin-bottom:16px">🔌 Available Stations (Not Assigned)</h3>
    <?php if (empty($availableStations)): ?>
    <p style="color:var(--text-muted);padding:16px">All stations are already assigned to this DJ.</p>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px">
        <?php foreach ($availableStations as $station): ?>
        <div class="card" style="border-color:rgba(0,191,255,.15);background:rgba(0,191,255,.02)">
            <div style="display:flex;justify-content:space-between;align-items:center">
                <div>
                    <strong><?php echo htmlspecialchars($station->username); ?></strong>
                    <?php if (!empty($station->server_name)): ?>
                    <br><span style="font-size:11px;color:#64748b"><?php echo htmlspecialchars($station->server_name); ?></span>
                    <?php endif; ?>
                    <br><span style="font-size:11px;color:#64748b"><?php echo htmlspecialchars($station->domain ?? 'No domain'); ?></span>
                </div>
                <form method="POST" action="/user/dj-panel/stations/assign/<?php echo $dj->id; ?>">
                    <input type="hidden" name="station_id" value="<?php echo $station->id; ?>">
                    <input type="hidden" name="role" value="dj">
                    <button type="submit" class="btn btn-sm primary">Assign</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>