<style>
.rs-wrap{max-width:1200px;margin:0 auto}
.rs-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:18px}
.rs-stat{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;text-align:center}
.rs-stat .num{font-size:24px;font-weight:800}.rs-stat .lbl{font-size:11px;color:#64748b;margin-top:2px}
.rs-card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:16px;margin-bottom:16px}
.rs-card h3{margin:0 0 12px;font-size:15px;color:#e0e0e0}
.rs-table{width:100%;border-collapse:collapse;font-size:12px}
.rs-table th{padding:8px 6px;text-align:left;font-weight:600;color:#64748b;border-bottom:1px solid rgba(255,255,255,.06)}
.rs-table td{padding:8px 6px;border-bottom:1px solid rgba(255,255,255,.04);color:#c0c0c0}
.rs-table code{background:rgba(255,255,255,.06);padding:2px 6px;border-radius:4px;font-size:11px}
.rs-toggle{display:flex;align-items:center;gap:8px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.rs-toggle:last-child{border-bottom:none}
.rs-toggle label{font-size:13px;color:#e0e0e0;flex:1}
.rs-toggle .desc{font-size:11px;color:#64748b;display:block}
.switch{position:relative;display:inline-block;width:40px;height:22px}
.switch input{opacity:0;width:0;height:0}
.slider{position:absolute;inset:0;background:#374151;border-radius:11px;transition:.3s;cursor:pointer}
.slider:before{content:'';position:absolute;top:2px;left:2px;width:18px;height:18px;background:#fff;border-radius:50%;transition:.3s}
input:checked + .slider{background:#4ade80}
input:checked + .slider:before{transform:translateX(18px)}
</style>

<div class="rs-wrap">
<h2 style="margin:0 0 16px">⚙️ Radio Settings</h2>

<div class="rs-grid">
<div class="rs-stat"><div class="num" style="color:#38bdf8"><?php echo $radioStats['total_streams']; ?></div><div class="lbl">Stations</div></div>
<div class="rs-stat"><div class="num"><?php echo count($v1); ?></div><div class="lbl">SHOUTcast v1</div></div>
<div class="rs-stat"><div class="num"><?php echo count($v2); ?></div><div class="lbl">SHOUTcast v2</div></div>
<div class="rs-stat"><div class="num"><?php echo count($icecast); ?></div><div class="lbl">Icecast</div></div>
</div>

<form method="POST" action="/admin/radiosettings/update">
<div class="rs-card">
<h3>🌐 Global Settings</h3>
<div class="rs-toggle"><label>Global Radio Enabled<span class="desc">Allow radio hosting across the platform</span></label>
<label class="switch"><input type="checkbox" name="global_enabled" value="1" <?php echo $radioStats['global_enabled'] ? 'checked' : ''; ?>><span class="slider"></span></label></div>
<div class="rs-toggle"><label>AutoDJ Enabled<span class="desc">Allow AutoDJ players on stations</span></label>
<label class="switch"><input type="checkbox" name="autodj_enabled" value="1" <?php echo $radioStats['autodj_enabled'] ? 'checked' : ''; ?>><span class="slider"></span></label></div>
<div class="rs-toggle"><label>Transcoding Enabled<span class="desc">Allow bitrate/format transcoding</span></label>
<label class="switch"><input type="checkbox" name="transcoding_enabled" value="1" <?php echo $radioStats['transcoding_enabled'] ? 'checked' : ''; ?>><span class="slider"></span></label></div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-top:12px">
<div><label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px">Default Listener Limit</label><input type="number" name="listener_limit" value="<?php echo (int)$radioStats['listener_limit']; ?>" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></div>
<div><label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px">Storage Limit (GB)</label><input type="number" name="storage_limit_gb" value="<?php echo (int)$radioStats['storage_limit_gb']; ?>" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></div>
<div><label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px">DJ Accounts Limit</label><input type="number" name="dj_accounts_limit" value="<?php echo (int)$radioStats['dj_accounts_limit']; ?>" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></div>
</div>
</div>

<div class="rs-card">
<h3>🤖 AutoDJ Defaults</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div><label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px">Bitrate</label><input name="bitrate" type="number" value="<?php echo $config['autodj']['bitrate'] ?? 128; ?>" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px"></div>
<div><label style="font-size:11px;color:#64748b;display:block;margin-bottom:4px">Format</label>
<select name="format" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px">
<option value="mp3" <?php echo ($config['autodj']['format'] ?? 'mp3') === 'mp3' ? 'selected' : ''; ?>>MP3</option>
<option value="aac" <?php echo ($config['autodj']['format'] ?? '') === 'aac' ? 'selected' : ''; ?>>AAC</option>
<option value="ogg" <?php echo ($config['autodj']['format'] ?? '') === 'ogg' ? 'selected' : ''; ?>>OGG</option>
</select></div>
</div>
</div>
<button type="submit" class="btn primary">Save Settings</button>
</form>

<div class="rs-card">
<h3>📡 SHOUTcast v1 Stations</h3>
<?php if (!empty($v1)): ?>
<table class="rs-table"><thead><tr><th>Station</th><th>Port</th><th>Source Port</th><th>DJ Port</th><th>Source Pass</th><th>Admin Pass</th><th>Status</th></tr></thead><tbody>
<?php foreach ($v1 as $s): ?>
<tr><td><strong><?php echo htmlspecialchars($s->name); ?></strong></td>
<td><code><?php echo $s->port; ?></code></td>
<td><code><?php echo $s->port + 1; ?></code></td>
<td><code><?php echo $s->dj_port ?: '-'; ?></code></td>
<td><code><?php echo htmlspecialchars($s->source_pw); ?></code></td>
<td><code><?php echo htmlspecialchars($s->admin_pw); ?></code></td>
<td><span style="color:<?php echo $s->status === 'running' ? '#4ade80' : '#f87171'; ?>"><?php echo $s->status; ?></span></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php else: ?><p style="color:#64748b;font-size:12px">No SHOUTcast v1 stations.</p><?php endif; ?>
</div>

<div class="rs-card">
<h3>📡 SHOUTcast v2 Stations</h3>
<?php if (!empty($v2)): ?>
<table class="rs-table"><thead><tr><th>Station</th><th>Port</th><th>Source Port</th><th>DJ Port</th><th>Source Pass</th><th>Admin Pass</th><th>Status</th></tr></thead><tbody>
<?php foreach ($v2 as $s): ?>
<tr><td><strong><?php echo htmlspecialchars($s->name); ?></strong></td>
<td><code><?php echo $s->port; ?></code></td>
<td><code><?php echo $s->port + 1; ?></code></td>
<td><code><?php echo $s->dj_port ?: '-'; ?></code></td>
<td><code><?php echo htmlspecialchars($s->source_pw); ?></code></td>
<td><code><?php echo htmlspecialchars($s->admin_pw); ?></code></td>
<td><span style="color:<?php echo $s->status === 'running' ? '#4ade80' : '#f87171'; ?>"><?php echo $s->status; ?></span></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php else: ?><p style="color:#64748b;font-size:12px">No SHOUTcast v2 stations.</p><?php endif; ?>
</div>

<div class="rs-card">
<h3>📡 Icecast Stations</h3>
<?php if (!empty($icecast)): ?>
<table class="rs-table"><thead><tr><th>Station</th><th>Port</th><th>Mount</th><th>DJ Port</th><th>Source Pass</th><th>Admin Pass</th><th>Status</th></tr></thead><tbody>
<?php foreach ($icecast as $s): ?>
<tr><td><strong><?php echo htmlspecialchars($s->name); ?></strong></td>
<td><code><?php echo $s->port; ?></code></td>
<td><code><?php echo htmlspecialchars($s->mount); ?></code></td>
<td><code><?php echo $s->dj_port ?: '-'; ?></code></td>
<td><code><?php echo htmlspecialchars($s->source_pw); ?></code></td>
<td><code><?php echo htmlspecialchars($s->admin_pw); ?></code></td>
<td><span style="color:<?php echo $s->status === 'running' ? '#4ade80' : '#f87171'; ?>"><?php echo $s->status; ?></span></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php else: ?><p style="color:#64748b;font-size:12px">No Icecast stations.</p><?php endif; ?>
</div>
</div>
