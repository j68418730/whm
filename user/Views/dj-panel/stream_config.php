<?php $theme_settings = json_decode($user->theme_settings ?? '{}', true); ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['generated_credentials'])): ?>
<div class="alert alert-success" style="border:2px solid #22c55e;background:rgba(34,197,94,.08)">
  <strong style="font-size:13px">🔐 Stream Credentials Generated!</strong>
  <div style="font-size:11px;color:#64748b;margin:4px 0 8px">Save these credentials — they won't be shown again.</div>
  <pre style="background:rgba(0,0,0,.4);border:1px solid rgba(0,191,255,.2);border-radius:6px;padding:12px;font-size:11px;color:#22c55e;font-family:monospace;overflow:auto;max-height:300px"><?php echo htmlspecialchars($_SESSION['generated_credentials']); unset($_SESSION['generated_credentials']); ?></pre>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2>Stream Config: <?php echo htmlspecialchars($station->username); ?></h2>
    <div style="display:flex;gap:8px">
        <a href="/user/dj-panel/streams/<?php echo $dj->id; ?>" class="btn secondary">← Back to Streams</a>
        <a href="/user/dj-panel/show/<?php echo $dj->id; ?>" class="btn secondary">← Back to DJ</a>
    </div>
</div>

<div style="max-width:900px">
    <div style="margin-bottom:16px;padding:12px;background:rgba(0,191,255,.08);border:1px solid rgba(0,191,255,.2);border-radius:8px;font-size:12px;color:#64748b">
        <strong>📡 Three-Layer Auth Flow for Studio App:</strong><br>
        1️⃣ <strong>Planet Hosts API</strong> — DJ logs in with API Key → validates against Planet Hosts API<br>
        2️⃣ <strong>DJ Auth</strong> — DJ selects station → validates DJ has access to station<br>
        3️⃣ <strong>Stream Auth</strong> — Encoder connects using credentials below (Icecast/SHOUTcast)
    </div>

    <form method="POST" action="/user/dj-panel/stream-config/<?php echo $dj->id; ?>/<?php echo $station->id; ?>" style="display:flex;flex-direction:column;gap:20px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <fieldset style="border:1px solid rgba(0,191,255,.2);border-radius:8px;padding:16px">
                <legend style="color:var(--accent);font-size:13px;font-weight:600;padding:0 8px">🧊 Icecast Configuration</legend>
                <div style="display:flex;flex-direction:column;gap:12px">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Hostname</label><input type="text" name="icecast_hostname" class="inp" value="<?php echo htmlspecialchars($config->icecast_hostname ?? 'radio.planet-hosts.com'); ?>" style="width:100%"></div>
                        <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Port</label><input type="number" name="icecast_port" class="inp" value="<?php echo htmlspecialchars($config->icecast_port ?? 8000); ?>" style="width:100%"></div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Username</label><input type="text" name="icecast_username" class="inp" value="<?php echo htmlspecialchars($config->icecast_username ?? 'source'); ?>" style="width:100%"></div>
                        <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Mount Point</label><input type="text" name="icecast_mount" class="inp" value="<?php echo htmlspecialchars($config->icecast_mount ?? '/live'); ?>" style="width:100%"></div>
                    </div>
                    <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Password (blank = auto-generate)</label><input type="text" name="icecast_password" class="inp" value="<?php echo htmlspecialchars($config->icecast_password ?? ''); ?>" style="width:100%"></div>
                </div>
            </fieldset>

            <fieldset style="border:1px solid rgba(251,146,60,.2);border-radius:8px;padding:16px">
                <legend style="color:#fb923c;font-size:13px;font-weight:600;padding:0 8px">📻 SHOUTcast v2</legend>
                <div style="display:flex;flex-direction:column;gap:12px">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Hostname</label><input type="text" name="shoutcast_v2_hostname" class="inp" value="<?php echo htmlspecialchars($config->shoutcast_v2_hostname ?? 'radio.planet-hosts.com'); ?>" style="width:100%"></div>
                        <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Port</label><input type="number" name="shoutcast_v2_port" class="inp" value="<?php echo htmlspecialchars($config->shoutcast_v2_port ?? 12000); ?>" style="width:100%"></div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Username</label><input type="text" name="shoutcast_v2_username" class="inp" value="<?php echo htmlspecialchars($config->shoutcast_v2_username ?? 'source'); ?>" style="width:100%"></div>
                        <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Password (blank = auto-generate)</label><input type="text" name="shoutcast_v2_password" class="inp" value="<?php echo htmlspecialchars($config->shoutcast_v2_password ?? ''); ?>" style="width:100%"></div>
                    </div>
                </div>
            </fieldset>

            <fieldset style="border:1px solid rgba(248,113,113,.2);border-radius:8px;padding:16px">
                <legend style="color:#f87171;font-size:13px;font-weight:600;padding:0 8px">📻 SHOUTcast v1 (Legacy)</legend>
                <div style="display:flex;flex-direction:column;gap:12px">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Hostname</label><input type="text" name="shoutcast_v1_hostname" class="inp" value="<?php echo htmlspecialchars($config->shoutcast_v1_hostname ?? 'radio.planet-hosts.com'); ?>" style="width:100%"></div>
                        <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Port</label><input type="number" name="shoutcast_v1_port" class="inp" value="<?php echo htmlspecialchars($config->shoutcast_v1_port ?? 11000); ?>" style="width:100%"></div>
                    </div>
                    <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Password (blank = auto-generate)</label><input type="text" name="shoutcast_v1_password" class="inp" value="<?php echo htmlspecialchars($config->shoutcast_v1_password ?? ''); ?>" style="width:100%"></div>
                </div>
            </fieldset>

            <fieldset style="border:1px solid rgba(34,197,94,.2);border-radius:8px;padding:16px">
                <legend style="color:#4ade80;font-size:13px;font-weight:600;padding:0 8px">🎵 Stream Format</legend>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                    <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Format</label><select name="format" class="inp" style="width:100%"><option value="mp3" <?php echo ($config->format ?? 'mp3') === 'mp3' ? 'selected' : ''; ?>>MP3</option><option value="aac" <?php echo ($config->format ?? '') === 'aac' ? 'selected' : ''; ?>>AAC</option><option value="ogg" <?php echo ($config->format ?? '') === 'ogg' ? 'selected' : ''; ?>>OGG</option></select></div>
                    <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Bitrate (kbps)</label><input type="number" name="bitrate" class="inp" value="<?php echo htmlspecialchars($config->bitrate ?? 128); ?>" style="width:100%"></div>
                    <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Sample Rate (Hz)</label><input type="number" name="samplerate" class="inp" value="<?php echo htmlspecialchars($config->samplerate ?? 44100); ?>" style="width:100%"></div>
                    <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Channels</label><select name="channels" class="inp" style="width:100%"><option value="1" <?php echo ($config->channels ?? 2) == 1 ? 'selected' : ''; ?>>Mono</option><option value="2" <?php echo ($config->channels ?? 2) == 2 ? 'selected' : ''; ?>>Stereo</option></select></div>
                    <div><label style="display:block;margin-bottom:4px;font-size:12px;color:var(--text-secondary)">Public</label><select name="public" class="inp" style="width:100%"><option value="1" <?php echo ($config->public ?? 1) == 1 ? 'selected' : ''; ?>>Yes</option><option value="0" <?php echo ($config->public ?? 1) == 0 ? 'selected' : ''; ?>>No</option></select></div>
                </div>
            </fieldset>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px">
            <button type="submit" name="action" value="save" class="btn primary">💾 Save Configuration</button>
            <button type="submit" name="action" value="regenerate" class="btn warning" onclick="return confirm('Regenerate ALL passwords? This will invalidate current encoder connections!')">🔄 Regenerate All Passwords</button>
            <a href="/api/dj/stream-config/<?php echo $station->id; ?>" target="_blank" class="btn secondary">🔗 View API Endpoint</a>
            <a href="/user/dj-panel/streams/<?php echo $dj->id; ?>" class="btn secondary">← Cancel</a>
        </div>
    </form>
</div>