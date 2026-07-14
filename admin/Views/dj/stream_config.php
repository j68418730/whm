<?php $theme_settings = json_decode($user->theme_settings ?? '{}', true); ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2>Stream Configuration: <?php echo htmlspecialchars($station->username ?? 'Station'); ?></h2>
    <a href="/admin/dj/show/<?php echo $dj->id; ?>" class="btn secondary">← Back to DJ</a>
</div>

<div class="card mb-24">
    <h3 style="color:var(--accent);margin-bottom:12px">DJ: <?php echo htmlspecialchars($dj->username); ?></h3>
    <p style="color:var(--text-secondary);margin-bottom:16px">Station: <?php echo htmlspecialchars($station->username ?? 'Unknown'); ?> (<?php echo htmlspecialchars($station->domain ?? 'No domain'); ?>)</p>
</div>

<div class="card mb-24">
    <h3 style="color:var(--accent);margin-bottom:12px">Icecast Configuration</h3>
    <form method="POST" action="/admin/dj/stream-config/update/<?php echo $dj->id; ?>/<?php echo $stationId; ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Hostname</label>
                <input name="icecast_hostname" class="inp" value="<?php echo htmlspecialchars($streamConfig->icecast_hostname ?? 'radio.planet-hosts.com'); ?>">
            </div>
            <div class="form-group">
                <label>Port</label>
                <input type="number" name="icecast_port" class="inp" value="<?php echo htmlspecialchars($streamConfig->icecast_port ?? 8000); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Username</label>
                <input name="icecast_username" class="inp" value="<?php echo htmlspecialchars($streamConfig->icecast_username ?? 'source'); ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="icecast_password" class="inp" value="<?php echo htmlspecialchars($streamConfig->icecast_password ?? ''); ?>" placeholder="Leave blank to keep current">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Mount Point</label>
                <input name="icecast_mount" class="inp" value="<?php echo htmlspecialchars($streamConfig->icecast_mount ?? '/live'); ?>">
            </div>
            <div class="form-group">
                <label>Protocol</label>
                <select name="icecast_protocol" class="inp">
                    <option value="icecast" <?php echo ($streamConfig->icecast_protocol ?? 'icecast') === 'icecast' ? 'selected' : ''; ?>>Icecast</option>
                    <option value="icecast_kh" <?php echo ($streamConfig->icecast_protocol ?? '') === 'icecast_kh' ? 'selected' : ''; ?>>Icecast-KH</option>
                    <option value="shoutcast_v1" <?php echo ($streamConfig->icecast_protocol ?? '') === 'shoutcast_v1' ? 'selected' : ''; ?>>SHOUTcast v1</option>
                    <option value="shoutcast_v2" <?php echo ($streamConfig->icecast_protocol ?? '') === 'shoutcast_v2' ? 'selected' : ''; ?>>SHOUTcast v2</option>
                </select>
            </div>
        </div>
    </form>
</div>

<div class="card mb-24">
    <h3 style="color:var(--accent);margin-bottom:12px">SHOUTcast v1 Configuration</h3>
    <form method="POST" action="/admin/dj/stream-config/update/<?php echo $dj->id; ?>/<?php echo $stationId; ?>">
        <div class="form-row">
            <div class="form-group"><label>Hostname</label><input name="shoutcast_v1_hostname" class="inp" value="<?php echo htmlspecialchars($streamConfig->shoutcast_v1_hostname ?? 'radio.planet-hosts.com'); ?>"></div>
            <div class="form-group"><label>Port</label><input type="number" name="shoutcast_v1_port" class="inp" value="<?php echo htmlspecialchars($streamConfig->shoutcast_v1_port ?? 11000); ?>"></div>
        </div>
        <div class="form-group"><label>Password</label><input type="password" name="shoutcast_v1_password" class="inp" placeholder="Leave blank to keep current"></div>
    </form>
</div>

<div class="card mb-24">
    <h3 style="color:var(--accent);margin-bottom:12px">SHOUTcast v2 Configuration</h3>
    <form method="POST" action="/admin/dj/stream-config/update/<?php echo $dj->id; ?>/<?php echo $stationId; ?>">
        <div class="form-row">
            <div class="form-group"><label>Hostname</label><input name="shoutcast_v2_hostname" class="inp" value="<?php echo htmlspecialchars($streamConfig->shoutcast_v2_hostname ?? 'radio.planet-hosts.com'); ?>"></div>
            <div class="form-group"><label>Port</label><input type="number" name="shoutcast_v2_port" class="inp" value="<?php echo htmlspecialchars($streamConfig->shoutcast_v2_port ?? 12000); ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Username</label><input name="shoutcast_v2_username" class="inp" value="<?php echo htmlspecialchars($streamConfig->shoutcast_v2_username ?? 'source'); ?>"></div>
            <div class="form-group"><label>Password</label><input type="password" name="shoutcast_v2_password" class="inp" placeholder="Leave blank to keep current"></div>
        </div>
    </form>
</div>

<div class="card mb-24">
    <h3 style="color:var(--accent);margin-bottom:12px">Stream Settings</h3>
    <form method="POST" action="/admin/dj/stream-config/update/<?php echo $dj->id; ?>/<?php echo $stationId; ?>">
        <div class="form-row">
            <div class="form-group"><label>Auto Reconnect</label><label style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="auto_reconnect" value="1" <?php echo ($streamConfig->auto_reconnect ?? 1) ? 'checked' : ''; ?>> Enable Auto Reconnect</label></div>
            <div class="form-group"><label>Reconnect Interval (sec)</label><input type="number" name="reconnect_interval" class="inp" value="<?php echo htmlspecialchars($streamConfig->reconnect_interval ?? 5); ?>" min="1" max="60"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Max Reconnect Attempts</label><input type="number" name="max_reconnect_attempts" class="inp" value="<?php echo htmlspecialchars($streamConfig->max_reconnect_attempts ?? 10); ?>"></div>
            <div class="form-group"><label>Bitrate (kbps)</label><input type="number" name="bitrate" class="inp" value="<?php echo htmlspecialchars($streamConfig->bitrate ?? 128); ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Format</label><select name="format" class="inp"><option value="mp3" <?php echo ($streamConfig->format ?? 'mp3') === 'mp3' ? 'selected' : ''; ?>>MP3</option><option value="aac" <?php echo ($streamConfig->format ?? '') === 'aac' ? 'selected' : ''; ?>>AAC</option><option value="ogg" <?php echo ($streamConfig->format ?? '') === 'ogg' ? 'selected' : ''; ?>>OGG</option><option value="opus" <?php echo ($streamConfig->format ?? '') === 'opus' ? 'selected' : ''; ?>>Opus</option></select></div>
            <div class="form-group"><label>Sample Rate</label><input type="number" name="samplerate" class="inp" value="<?php echo htmlspecialchars($streamConfig->samplerate ?? 44100); ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Channels</label><input type="number" name="channels" class="inp" value="<?php echo htmlspecialchars($streamConfig->channels ?? 2); ?>" min="1" max="2"></div>
        </div>
        <button type="submit" class="btn primary mt-16">Save All Stream Settings</div>
    </form>
</div>

<div class="card">
    <h3 style="color:var(--accent);margin-bottom:12px">Connection Details for Encoders</h3>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;margin-top:16px">
        <div class="card" style="border-color:rgba(0,191,255,.3)">
            <h4 style="color:#00bfff">Icecast</h4>
            <table style="width:100%;font-size:12px">
                <tr><td>Host</td><td><?php echo htmlspecialchars($streamConfig->icecast_hostname ?? 'radio.planet-hosts.com'); ?></td></tr>
                <tr><td>Port</td><td><?php echo htmlspecialchars($streamConfig->icecast_port ?? 8000); ?></td></tr>
                <tr><td>Username</td><td><?php echo htmlspecialchars($streamConfig->icecast_username ?? 'source'); ?></td></tr>
                <tr><td>Password</td><td><?php echo htmlspecialchars($streamConfig->icecast_password ?? '********'); ?></td></tr>
                <tr><td>Mount</td><td><?php echo htmlspecialchars($streamConfig->icecast_mount ?? '/live'); ?></td></tr>
                <tr><td>Protocol</td><td><?php echo htmlspecialchars($streamConfig->icecast_protocol ?? 'icecast'); ?></td></tr>
            </table>
        </div>
        <div class="card" style="border-color:rgba(251,191,36,.3)">
            <h4 style="color:#fbbf24">SHOUTcast v2</h4>
            <table style="width:100%;font-size:12px">
                <tr><td>Host</td><td><?php echo htmlspecialchars($streamConfig->shoutcast_v2_hostname ?? 'radio.planet-hosts.com'); ?></td></tr>
                <tr><td>Port</td><td><?php echo htmlspecialchars($streamConfig->shoutcast_v2_port ?? 12000); ?></td></tr>
                <tr><td>Username</td><td><?php echo htmlspecialchars($streamConfig->shoutcast_v2_username ?? 'source'); ?></td></tr>
                <tr><td>Password</td><td><?php echo htmlspecialchars($streamConfig->shoutcast_v2_password ?? '********'); ?></td></tr>
            </table>
        </div>
        <div class="card" style="border-color:rgba(248,113,113,.3)">
            <h4 style="color:#f87171">SHOUTcast v1</h4>
            <table style="width:100%;font-size:12px">
                <tr><td>Host</td><td><?php echo htmlspecialchars($streamConfig->shoutcast_v1_hostname ?? 'radio.planet-hosts.com'); ?></td></tr>
                <tr><td>Port</td><td><?php echo htmlspecialchars($streamConfig->shoutcast_v1_port ?? 11000); ?></td></tr>
                <tr><td>Password</td><td><?php echo htmlspecialchars($streamConfig->shoutcast_v1_password ?? '********'); ?></td></tr>
            </table>
        </div>
    </div>
</div>