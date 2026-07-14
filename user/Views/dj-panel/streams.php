<?php $theme_settings = json_decode($user->theme_settings ?? '{}', true); ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2>Streams for: <?php echo htmlspecialchars($dj->username); ?></h2>
    <a href="/user/dj-panel/show/<?php echo $dj->id; ?>" class="btn secondary">← Back to DJ</a>
</div>

<div class="card">
    <h3 style="color:var(--accent);margin-bottom:16px">📻 Assigned Stations & Stream Details</h3>
    <?php if (empty($stations)): ?>
    <p style="color:var(--text-muted);padding:24px;text-align:center">No stations assigned. <a href="/user/dj-panel/stations/<?php echo $dj->id; ?>" class="btn primary">Assign Stations</a></p>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(500px,1fr));gap:16px">
        <?php foreach ($stations as $s): ?>
        <div class="card" style="border-color:rgba(0,191,255,.2);background:rgba(0,191,255,.02)">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid rgba(0,191,255,.1)">
                <div>
                    <div style="font-weight:600;color:#fff;font-size:15px"><?php echo htmlspecialchars($s['station']->username); ?></div>
                    <div style="font-size:11px;color:#64748b"><?php echo htmlspecialchars($s['station']->domain ?? 'No domain'); ?></div>
                </div>
                <div style="display:flex;gap:8px">
                    <span class="badge badge-<?php echo $s['role'] === 'owner' ? 'danger' : ($s['role'] === 'manager' ? 'warning' : 'primary'); ?>"><?php echo ucfirst($s['role']); ?></span>
                </div>
            </div>

            <?php if (!empty($s['streamConfig'])): ?>
            <div style="font-size:12px;font-family:monospace;line-height:1.8">
                <div style="color:var(--accent);margin-bottom:8px">🎵 Stream Configuration</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                    <div>
                        <strong>Icecast:</strong><br>
                        Host: <?php echo htmlspecialchars($s['streamConfig']->icecast_hostname ?? 'radio.planet-hosts.com'); ?><br>
                        Port: <?php echo htmlspecialchars($s['streamConfig']->icecast_port ?? 8000); ?><br>
                        User: <?php echo htmlspecialchars($s['streamConfig']->icecast_username ?? 'source'); ?><br>
                        Pass: <?php echo htmlspecialchars($s['streamConfig']->icecast_password ?? '********'); ?><br>
                        Mount: <?php echo htmlspecialchars($s['streamConfig']->icecast_mount ?? '/live'); ?>
                    </div>
                    <div>
                        <strong>SHOUTcast v2:</strong><br>
                        Host: <?php echo htmlspecialchars($s['streamConfig']->shoutcast_v2_hostname ?? 'radio.planet-hosts.com'); ?><br>
                        Port: <?php echo htmlspecialchars($s['streamConfig']->shoutcast_v2_port ?? 12000); ?><br>
                        User: <?php echo htmlspecialchars($s['streamConfig']->shoutcast_v2_username ?? 'source'); ?><br>
                        Pass: <?php echo htmlspecialchars($s['streamConfig']->shoutcast_v2_password ?? '********'); ?>
                    </div>
                    <div>
                        <strong>SHOUTcast v1:</strong><br>
                        Host: <?php echo htmlspecialchars($s['streamConfig']->shoutcast_v1_hostname ?? 'radio.planet-hosts.com'); ?><br>
                        Port: <?php echo htmlspecialchars($s['streamConfig']->shoutcast_v1_port ?? 11000); ?><br>
                        Pass: <?php echo htmlspecialchars($s['streamConfig']->shoutcast_v1_password ?? '********'); ?>
                    </div>
                    <div>
                        <strong>Format:</strong><br>
                        <?php echo htmlspecialchars($s['streamConfig']->format ?? 'mp3'); ?> <?php echo htmlspecialchars($s['streamConfig']->bitrate ?? 128); ?>kbps<br>
                        Sample Rate: <?php echo htmlspecialchars($s['streamConfig']->samplerate ?? 44100); ?>Hz<br>
                        Channels: <?php echo htmlspecialchars($s['streamConfig']->channels ?? 2); ?>
                    </div>
                </div>
                <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
                    <a href="/user/dj-panel/stream-config/<?php echo $dj->id; ?>/<?php echo $s['station']->id; ?>" class="btn btn-sm secondary">⚙️ Edit Config</a>
                    <a href="/api/dj/stream-config/<?php echo $s['station']->id; ?>" target="_blank" class="btn btn-sm secondary">🔗 API Endpoint</a>
                    <button class="btn btn-sm secondary" onclick="copyStreamConfig(<?php echo $s['station']->id; ?>)">📋 Copy All</button>
                </div>
            </div>
            <?php else: ?>
            <div style="padding:16px;text-align:center;background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);border-radius:8px;color:#f87171">
                No stream configuration yet.
                <a href="/user/dj-panel/stream-config/<?php echo $dj->id; ?>/<?php echo $s['station']->id; ?>" class="btn primary" style="margin-top:8px;display:inline-block">Configure Stream</a>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function copyStreamConfig(stationId) {
    fetch('/api/dj/stream-config/' + stationId, { headers: { 'Authorization': 'Bearer ' + localStorage.getItem('dj_api_key') || '' } })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                var txt = 'Icecast: ' + data.data.icecast_hostname + ':' + data.data.icecast_port +
                    '\nUser: ' + data.data.icecast_username + '\nPass: ' + data.data.icecast_password +
                    '\nMount: ' + data.data.icecast_mount +
                    '\n\nSHOUTcast v2: ' + data.data.shoutcast_v2_hostname + ':' + data.data.shoutcast_v2_port +
                    '\nUser: ' + data.data.shoutcast_v2_username + '\nPass: ' + data.data.shoutcast_v2_password +
                    '\n\nSHOUTcast v1: ' + data.data.shoutcast_v1_hostname + ':' + data.data.shoutcast_v1_port +
                    '\nPass: ' + data.data.shoutcast_v1_password;
                navigator.clipboard.writeText(txt).then(() => alert('Stream config copied!'));
            }
        });
}
</script>