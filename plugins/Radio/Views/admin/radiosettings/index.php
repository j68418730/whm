<h2 style="margin-bottom:16px">⚙️ Radio Settings</h2>
<form method="POST" action="/admin/radiosettings/update">
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">Icecast Configuration</h3>
<div class="form-group"><label>Default Port</label><input name="default_port" value="<?php echo $config['servers']['icecast']['default_port'] ?? 6000; ?>"></div>
<div class="form-group"><label>Port Range</label><input name="port_range" value="<?php echo $config['servers']['icecast']['port_range'] ?? '6000-10000'; ?>"></div>
<div class="form-group"><label>Binary Path</label><input name="binary_path" value="<?php echo $config['servers']['icecast']['binary_path'] ?? '/usr/bin/icecast'; ?>"></div>
</div>
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">AutoDJ Configuration</h3>
<div class="form-group"><label>Bitrate</label><input name="bitrate" value="<?php echo $config['autodj']['bitrate'] ?? 128; ?>"></div>
<div class="form-group"><label>Format</label><select name="format"><option value="mp3" <?php echo ($config['autodj']['format'] ?? 'mp3') === 'mp3' ? 'selected' : ''; ?>>MP3</option><option value="aac" <?php echo ($config['autodj']['format'] ?? '') === 'aac' ? 'selected' : ''; ?>>AAC</option><option value="ogg" <?php echo ($config['autodj']['format'] ?? '') === 'ogg' ? 'selected' : ''; ?>>OGG</option></select></div>
</div>
<button type="submit" class="btn primary">Save Settings</button>
</form>
