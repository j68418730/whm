<?php $theme_settings = json_decode($user->theme_settings ?? '{}', true); ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2>Stream Configuration: <?php echo htmlspecialchars($station->username); ?></h2>
    <a href="/admin/dj/stations/<?php echo $dj->id; ?>" class="btn secondary">← Back to Stations</a>
</div>

<div class="grid-2" style="gap:24px;margin-bottom:24px">
    <div class="card">
        <h3 style="color:var(--accent);margin-bottom:12px">📻 Icecast Configuration</h3>
        <div class="form-group">
            <label>Hostname</label>
            <input type="text" name="icecast_hostname" value="<?php echo htmlspecialchars($streamConfig->icecast_hostname ?? 'radio.planet-hosts.com'); ?>" class="inp" placeholder="radio.planet-hosts.com">
        </div>
        <div class="form-row">
            <div class="form-group"><label>Port</label><input type="number" name="icecast_port" value="<?php echo $streamConfig->icecast_port ?? 8000; ?>" class="inp" style="width:100px"></div>
            <div class="form-group"><label>Username</label><input type="text" name="icecast_username" value="<?php echo htmlspecialchars($streamConfig->icecast_username ?? 'source'); ?>" class="inp" style="width:100%" placeholder="source"></div>
        </div>
        <div class="form-group"><label>Password</label><input type="password" name="icecast_password" value="<?php echo htmlspecialchars($streamConfig->icecast_password ?? ''); ?>" class="inp" placeholder="Generated password"></div>
        <div class="form-row">
            <div class="form-group"><label>Mount Point</label><input type="text" name="icecast_mount" value="<?php echo htmlspecialchars($streamConfig->icecast_mount ?? '/live'); ?>" class="inp" style="width:150px"></div>
            <div class="form-group"><label>Protocol</label>
                <select name="icecast_protocol" class="inp" style="width:180px">
                    <option value="icecast" <?php echo ($streamConfig->icecast_protocol ?? 'icecast') === 'icecast' ? 'selected' : ''; ?>>Icecast 2.x</option>
                    <option value="icecast_kh" <?php echo ($streamConfig->icecast_protocol ?? '') === 'icecast_kh' ? 'selected' : ''; ?>>Icecast KH</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 style="color:var(--accent);margin-bottom:12px">📻 SHOUTcast v1 Configuration</h3>
        <div class="form-row">
            <div class="form-group"><label>Hostname</label><input type="text" name="shoutcast_v1_hostname" value="<?php echo htmlspecialchars($streamConfig->shoutcast_v1_hostname ?? 'radio.planet-hosts.com'); ?>" class="inp" placeholder="radio.planet-hosts.com"></div>
            <div class="form-group"><label>Port</label><input type="number" name="shoutcast_v1_port" value="<?php echo $streamConfig->shoutcast_v1_port ?? 11000; ?>" class="inp" style="width:100px"></div>
        </div>
        <div class="form-group"><label>Password</label><input type="password" name="shoutcast_v1_password" value="<?php echo htmlspecialchars($streamConfig->shoutcast_v1_password ?? ''); ?>" class="inp" placeholder="SC v1 password"></div>
    </div>

    <div class="card">
        <h3 style="color:var(--accent);margin-bottom:12px">📻 SHOUTcast v2 Configuration</h3>
        <div class="form-row">
            <div class="form-group"><label>Hostname</label><input type="text" name="shoutcast_v2_hostname" value="<?php echo htmlspecialchars($streamConfig->shoutcast_v2_hostname ?? 'radio.planet-hosts.com'); ?>" class="inp" placeholder="radio.planet-hosts.com"></div>
            <div class="form-group"><label>Port</label><input type="number" name="shoutcast_v2_port" value="<?php echo $streamConfig->shoutcast_v2_port ?? 12000; ?>" class="inp" style="width:100px"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Username</label><input type="text" name="shoutcast_v2_username" value="<?php echo htmlspecialchars($streamConfig->shoutcast_v2_username ?? 'source'); ?>" class="inp" style="width:100%" placeholder="source"></div>
            <div class="form-group"><label>Password</label><input type="password" name="shoutcast_v2_password" value="<?php echo htmlspecialchars($streamConfig->shoutcast_v2_password ?? ''); ?>" class="inp" placeholder="SC v2 password"></div>
        </div>
    </div>

    <div class="card">
        <h3 style="color:var(--accent);margin-bottom:12px">⚙️ Stream Settings</h3>
        <div class="form-row">
            <div class="form-group"><label>Bitrate (kbps)</label><input type="number" name="bitrate" value="<?php echo $streamConfig->bitrate ?? 128; ?>" class="inp" style="width:100px"></div>
            <div class="form-group"><label>Format</label><select name="format" class="inp" style="width:120px"><option value="mp3" <?php echo ($streamConfig->format ?? 'mp3') === 'mp3' ? 'selected' : ''; ?>>MP3</option><option value="aac" <?php echo ($streamConfig->format ?? '') === 'aac' ? 'selected' : ''; ?>>AAC</option><option value="ogg" <?php echo ($streamConfig->format ?? '') === 'ogg' ? 'selected' : ''; ?>>OGG Vorbis</option><option value="opus" <?php echo ($streamConfig->format ?? '') === 'opus' ? 'selected' : ''; ?>>Opus</option></select></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Samplerate (Hz)</label><input type="number" name="samplerate" value="<?php echo $streamConfig->samplerate ?? 44100; ?>" class="inp" style="width:100px"></div>
            <div class="form-group"><label>Channels</label><select name="channels" class="inp" style="width:100px"><option value="1" <?php echo ($streamConfig->channels ?? 2) == 1 ? 'selected' : ''; ?>>Mono</option><option value="2" <?php echo ($streamConfig->channels ?? 2) == 2 ? 'selected' : ''; ?>>Stereo</option></select></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Bitrate (kbps)</label><input type="number" name="bitrate" value="<?php echo $streamConfig->bitrate ?? 128; ?>" class="inp" style="width:100px"></div>
            <div class="form-group"><label>Format</label><select name="format" class="inp"><option value="mp3" <?php echo ($streamConfig->format ?? 'mp3') === 'mp3' ? 'selected' : ''; ?>>MP3</option><option value="aac" <?php echo ($streamConfig->format ?? '') === 'aac' ? 'selected' : ''; ?>>AAC</option><option value="ogg" <?php echo ($streamConfig->format ?? '') === 'ogg' ? 'selected' : ''; ?>>OGG Vorbis</option><option value="opus" <?php echo ($streamConfig->format ?? '') === 'opus' ? 'selected' : ''; ?>>Opus</option></select></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Samplerate (Hz)</label><input type="number" name="samplerate" value="<?php echo $streamConfig->samplerate ?? 44100; ?>" class="inp" style="width:100px"></div>
            <div class="form-group"><label>Channels</label><select name="channels" class="inp"><option value="1" <?php echo ($streamConfig->channels ?? 2) == 1 ? 'selected' : ''; ?>>Mono</option><option value="2" <?php echo ($streamConfig->channels ?? 2) == 2 ? 'selected' : ''; ?>>Stereo</option></select></div>
        </div>
    </div>

    <div class="card">
        <h3 style="color:var(--accent);margin-bottom:12px">🔄 Reconnection Settings</h3>
        <div class="form-row">
            <div class="form-group"><label>Auto Reconnect</label><select name="auto_reconnect" class="inp"><option value="1" <?php echo ($streamConfig->auto_reconnect ?? 1) ? 'selected' : ''; ?>>Enabled</option><option value="0" <?php echo ($streamConfig->auto_reconnect ?? 1) ? '' : 'selected'; ?>>Disabled</option></select></div>
            <div class="form-group"><label>Reconnect Interval (sec)</label><input type="number" name="reconnect_interval" value="<?php echo $streamConfig->reconnect_interval ?? 5; ?>" class="inp" style="width:100px"></div>
            <div class="form-group"><label>Max Reconnect Attempts</label><input type="number" name="max_reconnect_attempts" value="<?php echo $streamConfig->max_reconnect_attempts ?? 10; ?>" class="inp" style="width:100px"></div>
        </div>
    </div>
</div>

<div class="d-flex gap-8 mt-24">
    <a href="/admin/dj/stations/<?php echo $dj->id; ?>" class="btn secondary">← Back to Stations</a>
    <button type="submit" class="btn primary" style="flex:1">💾 Save Stream Configuration</button>
</div>
</form>