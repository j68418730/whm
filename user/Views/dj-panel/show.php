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
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['generated_key'])): ?>
<div class="alert alert-success" style="border:2px solid #22c55e;background:rgba(34,197,94,.08)">
  <strong style="font-size:13px">🔑 Key Generated — Copy it now!</strong>
  <div style="font-size:11px;color:#64748b;margin:4px 0 8px">This key will not be shown again.</  <div style="display:flex;gap:6px;align-items:center">
    <code id="genKeyDisplay" style="flex:1;padding:8px 12px;border-radius:6px;font-size:13px;background:rgba(0,0,0,.4);border:1px solid rgba(0,191,255,.2);color:#22c55e;font-family:monospace;font-size:11px"><?php echo htmlspecialchars($_SESSION['generated_key']); unset($_SESSION['generated_key']); ?></code>
    <button class="btn primary" onclick="copyKey()" style="white-space:nowrap">📋 Copy</button>
  </div>
</div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2>DJ: <?php echo htmlspecialchars($dj->username); ?></h2>
    <a href="/user/dj-panel" class="btn secondary">← Back to DJs</a>
</div>

<div class="stats-grid" style="margin-bottom:24px">
    <div class="stat-card"><h3>Assigned Stations</h3><div class="value" style="font-size:28px;color:#4ade80"><?php echo count($stations); ?></div></div>
    <div class="stat-card"><h3>API Keys</h3><div class="value" style="font-size:28px;color:#008cff"><?php echo count($apiKeys); ?></div></div>
    <div class="stat-card"><h3>Status</h3><div class="value" style="font-size:28px;color:<?php echo $dj->status === 'active' ? '#4ade80' : ($dj->status === 'suspended' ? '#f87171' : '#facc15'); ?>"><?php echo ucfirst($dj->status); ?></div></div>
    <div class="stat-card"><h3>Role</h3><div class="value" style="font-size:28px;color:<?php echo $dj->role === 'super_admin' ? '#f87171' : ($dj->role === 'station_manager' ? '#facc15' : '#008cff'); ?>"><?php echo ucfirst($dj->role); ?></div></div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(450px,1fr));gap:16px;margin-bottom:24px">
    <div class="card">
        <h3 style="color:var(--accent);margin-bottom:12px">👤 DJ Information</h3>
        <div style="font-size:13px;color:var(--text-secondary);margin-bottom:8px"><strong>Username:</strong> <?php echo htmlspecialchars($dj->username); ?></div>
        <div style="font-size:13px;color:var(--text-secondary);margin-bottom:8px"><strong>Email:</strong> <?php echo htmlspecialchars($dj->email ?? 'Not set'); ?></div>
        <div style="font-size:13px;color:var(--text-secondary);margin-bottom:8px"><strong>Full Name:</strong> <?php echo htmlspecialchars($dj->full_name ?? 'Not set'); ?></div>
        <div style="font-size:13px;color:var(--text-secondary);margin-bottom:8px"><strong>Role:</strong> <span class="badge badge-<?php echo $dj->role === 'super_admin' ? 'danger' : ($dj->role === 'station_manager' ? 'warning' : 'primary'); ?>"><?php echo ucfirst($dj->role); ?></span></div>
        <div style="font-size:13px;color:var(--text-secondary);margin-bottom:8px"><strong>Status:</strong> <span class="status-badge status-<?php echo $dj->status; ?>"><?php echo ucfirst($dj->status); ?></span></div>
        <div style="font-size:13px;color:var(--text-secondary);margin-bottom:8px"><strong>Last Login:</strong> <?php echo $dj->last_login ? date('M j, Y H:i', strtotime($dj->last_login)) : 'Never'; ?></div>
        <div style="font-size:13px;color:var(--text-secondary);margin-bottom:8px"><strong>Created:</strong> <?php echo date('M j, Y', strtotime($dj->created_at)); ?></div>
        <div style="margin-top:12px">
            <a href="/user/dj-panel/edit/<?php echo $dj->id; ?>" class="btn primary" style="margin-right:8px">✏️ Edit DJ</a>
            <a href="/user/dj-panel/api-keys/<?php echo $dj->id; ?>" class="btn secondary" style="margin-right:8px">🔑 API Keys</a>
            <a href="/user/dj-panel/streams/<?php echo $dj->id; ?>" class="btn primary" style="margin-right:8px">📻 Streams</a>
            <a href="/user/dj-panel/stations/<?php echo $dj->id; ?>" class="btn secondary">🏷️ Stations</a>
            <a href="/user/dj-panel/activity/<?php echo $dj->id; ?>" class="btn secondary" style="margin-left:8px">📋 Activity</a>
        </div>
    </div>

    <div class="card">
        <h3 style="color:var(--accent);margin-bottom:12px">📻 Assigned Stations & Stream Config</h3>
        <?php if (empty($stations)): ?>
        <p style="color:var(--text-muted);padding:24px;text-align:center">No stations assigned yet. <a href="/user/dj-panel/stations/<?php echo $dj->id; ?>" class="btn primary" style="margin-top:8px;display:inline-block">Assign Stations</a></p>
        <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:16px">
            <?php foreach ($stations as $s): ?>
            <div class="card" style="border-color:rgba(0,191,255,.2)">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                    <div style="font-weight:600;color:#fff"><?php echo htmlspecialchars($s['station']->username); ?></div>
                    <span style="font-size:11px;color:#64748b"><?php echo htmlspecialchars($s['station']->domain ?? 'No domain'); ?></span>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:8px;font-size:11px">
                    <span class="badge badge-<?php echo $s['role'] === 'owner' ? 'danger' : ($s['role'] === 'manager' ? 'warning' : 'primary'); ?>"><?php echo ucfirst($s['role']); ?></span>
                    <span style="font-size:11px;color:#64748b">Assigned: <?php echo date('M j, Y', strtotime($s['assigned_at'])); ?></span>
                </div>
                <?php if (!empty($s['streamConfig'])): ?>
                <div style="margin-top:8px;padding:8px;background:rgba(0,191,255,.05);border-radius:6px;font-size:11px;font-family:monospace">
                    <div><strong>Icecast:</strong> <?php echo htmlspecialchars($s['streamConfig']->icecast_hostname ?? 'radio.planet-hosts.com'); ?>:<?php echo htmlspecialchars($s['streamConfig']->icecast_port ?? 8000); ?> (<?php echo htmlspecialchars($s['streamConfig']->icecast_username ?? 'source'); ?> / <?php echo htmlspecialchars($s['streamConfig']->icecast_password ?? '********'); ?>) Mount: <?php echo htmlspecialchars($s['streamConfig']->icecast_mount ?? '/live'); ?></div>
                    <div style="margin-top:4px"><strong>SHOUTcast v2:</strong> <?php echo htmlspecialchars($s['streamConfig']->shoutcast_v2_hostname ?? 'radio.planet-hosts.com'); ?>:<?php echo htmlspecialchars($s['streamConfig']->shoutcast_v2_port ?? 12000); ?> (<?php echo htmlspecialchars($s['streamConfig']->shoutcast_v2_username ?? 'source'); ?> / <?php echo htmlspecialchars($s['streamConfig']->shoutcast_v2_password ?? '********'); ?>)</div>
                    <div style="margin-top:4px"><strong>SHOUTcast v1:</strong> <?php echo htmlspecialchars($s['streamConfig']->shoutcast_v1_hostname ?? 'radio.planet-hosts.com'); ?>:<?php echo htmlspecialchars($s['streamConfig']->shoutcast_v1_port ?? 11000); ?> (<?php echo htmlspecialchars($s['streamConfig']->shoutcast_v1_password ?? '********'); ?>)</div>
                    <div style="margin-top:4px"><strong>Format:</strong> <?php echo htmlspecialchars($s['streamConfig']->format ?? 'mp3'); ?> <?php echo htmlspecialchars($s['streamConfig']->bitrate ?? 128); ?>kbps, <?php echo htmlspecialchars($s['streamConfig']->samplerate ?? 44100); ?>Hz, <?php echo htmlspecialchars($s['streamConfig']->channels ?? 2); ?>ch</div>
                </div>
                <?php else: ?>
                <div style="margin-top:8px;padding:8px;background:rgba(248,113,113,.1);border-radius:6px;font-size:11px;color:#f87171">No stream configuration yet. <a href="/user/dj-panel/stream-config/<?php echo $dj->id; ?>/<?php echo $s['station']->id; ?>" class="btn btn-sm primary" style="margin-left:8px">Configure</a></div>
                <?php endif; ?>
                <div style="margin-top:12px;display:flex;gap:8px">
                    <a href="/user/dj-panel/stream-config/<?php echo $dj->id; ?>/<?php echo $s['station']->id; ?>" class="btn btn-sm secondary">⚙️ Stream Config</a>
                    <a href="/api/dj/stream-config/<?php echo $s['station']->id; ?>" target="_blank" class="btn btn-sm secondary">🔗 API Config</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h3 style="color:var(--accent);margin-bottom:12px">🔑 API Keys</h3>
    <?php if (empty($apiKeys)): ?>
    <p style="color:var(--text-muted);padding:24px;text-align:center">No API keys yet. <a href="/user/dj-panel/api-keys/<?php echo $dj->id; ?>" class="btn primary" style="margin-top:8px;display:inline-block">Generate API Key</a></p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Prefix</th>
                    <th>Permissions</th>
                    <th>Rate Limit</th>
                    <th>Status</th>
                    <th>Last Used</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apiKeys as $k): ?>
                <tr>
                    <td><?php echo htmlspecialchars($k->name); ?></td>
                    <td><code><?php echo htmlspecialchars($k->key_prefix); ?>...</code></td>
                    <td><?php echo htmlspecialchars(implode(', ', json_decode($k->permissions ?? '[]'))); ?></td>
                    <td><?php echo $k->rate_limit ?: 'Unlimited'; ?>/min</td>
                    <td><span class="status-badge status-<?php echo $k->revoked_at ? 'terminated' : ($k->expires_at && $k->expires_at < date('Y-m-d H:i:s') ? 'terminated' : 'active'); ?>"><?php echo $k->revoked_at ? 'Revoked' : ($k->expires_at && $k->expires_at < date('Y-m-d H:i:s') ? 'Expired' : 'Active'); ?></span></td>
                    <td><?php echo $k->last_used_at ? date('M j, Y H:i', strtotime($k->last_used_at)) : 'Never'; ?></td>
                    <td><?php echo date('M j, Y', strtotime($k->created_at)); ?></td>
                    <td>
                        <?php if (!$k->revoked_at): ?>
                        <form method="POST" action="/user/dj-panel/api-keys/revoke/<?php echo $k->id; ?>" style="display:inline" onsubmit="return confirm('Revoke this API key?');">
                            <input type="hidden" name="dj_id" value="<?php echo $dj->id; ?>">
                            <button type="submit" class="btn btn-sm danger">Revoke</button>
                        </form>
                        <?php else: ?>
                        <span style="color:var(--text-muted);font-size:11px">Revoked</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    <div style="margin-top:16px;text-align:right">
        <a href="/user/dj-panel/api-keys/<?php echo $dj->id; ?>" class="btn primary">+ Generate New API Key</a>
    </div>
</div>

<script>
function copyKey() {
    var el = document.getElementById('genKeyDisplay');
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(el.textContent).then(function() {
            var btn = event.target;
            btn.textContent = '✅ Copied!';
            setTimeout(function() { btn.textContent = '📋 Copy'; }, 2000);
        });
    } else {
        var range = document.createRange();
        range.selectNode(document.getElementById('genKeyDisplay'));
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);
        document.execCommand('copy');
        window.getSelection().removeAllRanges();
        alert('Copied!');
    }
}
</script>