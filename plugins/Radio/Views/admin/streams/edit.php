<?php
$stream = $stream ?? null;
$sid = (int)($streamId ?? ($stream->id ?? 0));
$type = strtolower($stream->server_type ?? $stream->engine ?? 'icecast');
$mount = $stream->mount_point ?? '/live';
$port = (int)($stream->port ?? 0);
$bitrate = (int)($stream->bitrate ?? 128);
$format = strtolower($stream->format ?? 'mp3');
$maxListeners = (int)($stream->max_listeners ?? 100);
$publicServer = !empty($stream->public_server);
$autodjEnabled = !empty($stream->autodj_enabled);
$sslEnabled = !empty($stream->ssl_enabled);
$status = $stream->status ?? 'active';
$desc = $stream->description ?? '';
$owner = $stream->user_name ?? '';
$typeLabel = $type === 'shoutcast1' ? 'SHOUTcast v1' : ($type === 'shoutcast' || $type === 'shoutcast2' ? 'SHOUTcast v2' : 'Icecast');
?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<style>
.se-wrap{max-width:1100px;margin:0 auto}
.se-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px}
.se-head h2{margin:0;font-size:20px;color:#e0e0e0;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.se-badge{padding:4px 12px;border-radius:999px;font-size:11px;font-weight:700}
.se-badge.on{background:rgba(74,222,128,.15);color:#4ade80}
.se-badge.off{background:rgba(248,113,113,.15);color:#f87171}
.se-badge.susp{background:rgba(250,204,21,.15);color:#facc15}
.se-grid{display:grid;grid-template-columns:1.5fr 1fr;gap:16px;align-items:start}
@media(max-width:900px){.se-grid{grid-template-columns:1fr}}
.se-card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:12px;padding:18px;margin-bottom:16px}
.se-card h4{margin:0 0 14px;font-size:14px;color:#e0e0e0;display:flex;align-items:center;gap:8px}
.se-card h4 .bi{color:var(--accent,#008cff)}
.se-form .form-group{margin-bottom:12px}
.se-form label{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#64748b;font-weight:600;margin-bottom:4px;display:block}
.se-form input,.se-form select,.se-form textarea{width:100%;padding:8px 10px;border-radius:8px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none;box-sizing:border-box}
.se-form input:focus,.se-form select:focus,.se-form textarea:focus{border-color:rgba(0,140,255,.4)}
.se-form select option{background:#0a0e1a;color:#e0e0e0}
.se-form small{display:block;font-size:10px;color:#64748b;margin-top:3px}
.se-2col{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.se-3col{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
.se-check{display:flex;align-items:center;gap:8px;padding:8px 10px;border:1px solid rgba(255,255,255,.06);border-radius:8px;background:rgba(0,0,0,.2);font-size:12px;color:#cbd5e1;cursor:pointer}
.se-check input{width:auto;accent-color:#008cff}
.se-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:6px}
.se-actions .btn{padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;border:none;cursor:pointer}
.btn-se-save{background:linear-gradient(135deg,#008cff,#0066cc);color:#fff}
.btn-se-ghost{background:rgba(255,255,255,.06);color:#94a3b8;border:1px solid rgba(255,255,255,.08)!important}
.se-kv{display:grid;grid-template-columns:auto 1fr;gap:6px 12px;font-size:12px}
.se-kv .k{color:#64748b}
.se-kv .v{color:#cbd5e1;word-break:break-all;font-family:ui-monospace,monospace;font-size:11px}
.se-quick{display:flex;flex-direction:column;gap:8px}
.se-quick a{display:flex;align-items:center;justify-content:space-between;padding:9px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.2);color:#e0e0e0;font-size:12px;text-decoration:none;transition:.12s}
.se-quick a:hover{border-color:rgba(0,140,255,.25)}
.se-quick a.danger{color:#f87171}
.se-quick a span.act{color:#64748b;font-size:11px}
</style>

<div class="se-wrap">
<div class="se-head">
  <h2>
    ✏️ <?php echo htmlspecialchars($stream->name ?? ('Stream #' . $sid)); ?>
    <span class="se-badge <?php echo $status === 'suspended' ? 'susp' : (!empty($stream->live_online) ? 'on' : 'off'); ?>">
      <?php echo $status === 'suspended' ? '⏸ Suspended' : (!empty($stream->live_online) ? '● Online' : '○ Offline'); ?>
    </span>
    <span class="se-badge" style="background:rgba(0,140,255,.12);color:#38bdf8"><?php echo htmlspecialchars($typeLabel); ?></span>
  </h2>
  <a href="/admin/streams" class="btn btn-sm btn-ghost" style="text-decoration:none;padding:8px 14px;border-radius:8px;background:rgba(255,255,255,.06);color:#94a3b8;border:1px solid rgba(255,255,255,.08)">← Back to Streams</a>
</div>

<div class="se-grid">
  <div>
    <div class="se-card">
      <h4><i class="bi bi-sliders"></i> Stream Settings</h4>
      <form class="se-form" method="POST" action="/admin/streams/edit/<?php echo $sid; ?>">
        <div class="form-group">
          <label>Stream Name</label>
          <input type="text" name="name" value="<?php echo htmlspecialchars($stream->name ?? ''); ?>" required>
        </div>
        <div class="se-2col">
          <div class="form-group">
            <label>Engine</label>
            <select name="server_type">
              <?php foreach (['icecast' => 'Icecast', 'shoutcast' => 'SHOUTcast v2', 'shoutcast1' => 'SHOUTcast v1'] as $tv => $tl): ?>
              <option value="<?php echo $tv; ?>" <?php echo $type === $tv ? 'selected' : ''; ?>><?php echo $tl; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status">
              <?php foreach (['active' => 'Active', 'stopped' => 'Stopped', 'suspended' => 'Suspended'] as $sv => $sl): ?>
              <option value="<?php echo $sv; ?>" <?php echo $status === $sv ? 'selected' : ''; ?>><?php echo $sl; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="se-3col">
          <div class="form-group">
            <label>Port</label>
            <input type="number" name="port" value="<?php echo $port; ?>" min="1024" max="65535">
          </div>
          <div class="form-group">
            <label>Mount Point</label>
            <input type="text" name="mount_point" value="<?php echo htmlspecialchars($mount); ?>" placeholder="/live">
          </div>
          <div class="form-group">
            <label>Max Listeners</label>
            <input type="number" name="max_listeners" value="<?php echo $maxListeners; ?>" min="1">
          </div>
        </div>
        <div class="se-3col">
          <div class="form-group">
            <label>Bitrate</label>
            <select name="bitrate">
              <?php foreach ([64, 96, 128, 192, 256, 320] as $b): ?>
              <option value="<?php echo $b; ?>" <?php echo $bitrate === $b ? 'selected' : ''; ?>><?php echo $b; ?> kbps</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Format</label>
            <select name="format">
              <?php foreach (['mp3' => 'MP3', 'aac' => 'AAC', 'ogg' => 'OGG'] as $fv => $fl): ?>
              <option value="<?php echo $fv; ?>" <?php echo $format === $fv ? 'selected' : ''; ?>><?php echo $fl; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Owner</label>
            <input type="text" value="<?php echo htmlspecialchars($owner ?: 'Unassigned'); ?>" readonly style="opacity:.6">
          </div>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" rows="3" placeholder="Station description..."><?php echo htmlspecialchars($desc); ?></textarea>
        </div>
        <div class="se-3col" style="margin-bottom:12px">
          <label class="se-check"><input type="hidden" name="public_server" value="0"><input type="checkbox" name="public_server" value="1" <?php echo $publicServer ? 'checked' : ''; ?>> Public server</label>
          <label class="se-check"><input type="hidden" name="autodj_enabled" value="0"><input type="checkbox" name="autodj_enabled" value="1" <?php echo $autodjEnabled ? 'checked' : ''; ?>> AutoDJ enabled</label>
          <label class="se-check"><input type="hidden" name="ssl_enabled" value="0"><input type="checkbox" name="ssl_enabled" value="1" <?php echo $sslEnabled ? 'checked' : ''; ?>> SSL stream</label>
        </div>
        <div class="se-actions">
          <button type="submit" class="btn btn-se-save">💾 Save Changes</button>
          <a href="/admin/streams" class="btn btn-se-ghost">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <div>
    <div class="se-card">
      <h4><i class="bi bi-broadcast"></i> Connection Details</h4>
      <div class="se-kv">
        <span class="k">Server</span><span class="v"><?php echo htmlspecialchars(primary_domain()); ?></span>
        <span class="k">Port</span><span class="v"><?php echo $port ?: '—'; ?></span>
        <span class="k">Mount</span><span class="v"><?php echo htmlspecialchars($mount); ?></span>
        <span class="k">Stream URL</span><span class="v"><?php echo htmlspecialchars(radio_stream_url($stream)); ?></span>
        <span class="k">Source PW</span><span class="v"><?php echo htmlspecialchars($stream->plain_password ?? '—'); ?></span>
        <span class="k">Admin PW</span><span class="v"><?php echo htmlspecialchars($stream->admin_plain_password ?? '—'); ?></span>
      </div>
    </div>

    <div class="se-card">
      <h4><i class="bi bi-lightning"></i> Quick Actions</h4>
      <div class="se-quick">
        <a href="/admin/streams/restart/<?php echo $sid; ?>" onclick="return confirm('Restart this stream?')"><span>🔄 Restart Stream</span><span class="act">restart</span></a>
        <?php if ($status === 'suspended'): ?>
        <a href="/admin/streams/unsuspend/<?php echo $sid; ?>"><span>▶ Unsuspend</span><span class="act">resume</span></a>
        <?php else: ?>
        <a href="/admin/streams/suspend/<?php echo $sid; ?>" onclick="return confirm('Suspend this stream?')"><span>⏸ Suspend</span><span class="act">pause</span></a>
        <?php endif; ?>
        <a href="/admin/autodj"><span>🤖 AutoDJ Manager</span><span class="act">manage</span></a>
        <a href="/admin/streams/clone/<?php echo $sid; ?>" onclick="return confirm('Clone this stream?')"><span>📑 Clone Stream</span><span class="act">duplicate</span></a>
        <a class="danger" href="/admin/streams/delete/<?php echo $sid; ?>" onclick="return confirm('Permanently DELETE this stream and all its data?')"><span>🗑 Delete Stream</span><span class="act">permanent</span></a>
      </div>
    </div>

    <div class="se-card">
      <h4><i class="bi bi-info-circle"></i> Details</h4>
      <div class="se-kv">
        <span class="k">Stream ID</span><span class="v">#<?php echo $sid; ?></span>
        <span class="k">Created</span><span class="v"><?php echo htmlspecialchars($stream->created_at ?? '—'); ?></span>
        <span class="k">Updated</span><span class="v"><?php echo htmlspecialchars($stream->updated_at ?? '—'); ?></span>
        <span class="k">Service</span><span class="v"><?php echo htmlspecialchars($stream->systemd_service ?: '—'); ?></span>
        <span class="k">Config</span><span class="v"><?php echo htmlspecialchars($stream->config_path ?: '—'); ?></span>
        <span class="k">Listeners</span><span class="v"><?php echo (int)($stream->live_listeners ?? $stream->listener_count ?? 0); ?></span>
        <span class="k">Now Playing</span><span class="v"><?php echo htmlspecialchars(($stream->current_artist ? $stream->current_artist . ' - ' : '') . ($stream->current_song ?? '—')); ?></span>
      </div>
    </div>
  </div>
</div>
</div>
