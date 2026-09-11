<?php require __DIR__ . '/_tabs.php'; ?>
<?php
$iv = $installed ?? null;
$av = $available ?? null;
$ivVer = is_array($iv) ? ($iv['version'] ?? 'unknown') : 'unknown';
$ivCode = is_array($iv) ? (int)($iv['version_code'] ?? 0) : 0;
$avVer = is_array($av) ? ($av['version'] ?? '') : '';
$avCode = is_array($av) ? (int)($av['version_code'] ?? 0) : 0;
$avChan = is_array($av) ? ($av['channel'] ?? '') : '';
$notes = is_array($av) ? ($av['release_notes'] ?? '') : '';
$avChecksum = is_array($av) ? (string)($av['checksum'] ?? '') : '';
$avDate = is_array($av) ? (string)($av['release_date'] ?? '') : '';
$instDate = is_array($iv) ? (string)($iv['installed_at'] ?? '') : '';
$chan = htmlspecialchars($channel ?? 'stable');
?>
<div class="set-wrap">
<div class="set-head"><h2>🔄 System Update <span style="font-size:12px;color:#64748b;font-weight:400">Release system</span></h2>
<p>Installed: <code><?php echo htmlspecialchars($ivVer); ?></code> (v<?php echo $ivCode; ?>) — Channel: <code><?php echo $chan; ?></code> — <?php echo $behind > 0 ? '<span style="color:#facc15;font-weight:700">Update available: ' . htmlspecialchars($avVer) . ' (v' . $avCode . ')</span>' : '<span style="color:#4ade80">Up to date</span>'; ?></p>
</div>

<?php if ($behind > 0): ?>
<div class="set-alert-err" style="background:rgba(250,204,21,.08);border-color:rgba(250,204,21,.2);color:#facc15">🔔 Update available — <?php echo htmlspecialchars($avVer) . ' (v' . $avCode . ')'; ?> on channel <b><?php echo htmlspecialchars($avChan); ?></b>. <a href="#update-actions" style="color:#facc15;text-decoration:underline">Update now</a></div>
<?php endif; ?>

<div class="set-grid">
<div>
<div class="set-card">
<h4><i class="bi bi-download"></i> Version Status</h4>
<div class="set-kv"><span class="k">Current</span><span class="v">Ph-Whm <?php echo htmlspecialchars($ivVer); ?> (v<?php echo $ivCode; ?>)<?php if (!empty($gitSha)): ?> <code><?php echo htmlspecialchars($gitSha); ?></code><?php endif; ?><?php if ($instDate): ?><small style="display:block;color:#64748b;font-size:11px">Installed <?php echo htmlspecialchars($instDate); ?></small><?php endif; ?></span><span class="k">Latest Release</span><span class="v"><?php echo $behind > 0 ? 'Ph-Whm ' . htmlspecialchars($avVer) . ' <b style="color:#facc15">(v' . $avCode . ')</b>' : '— same as installed'; ?></span><span class="k">Channel</span><span class="v"><?php echo $chan; ?> </span><span class="k">Backup</span><span class="v"><?php echo $hasBackup ? '✓ Available' : '— None'; ?></span></div>
<?php if ($behind > 0 && $notes): ?>
<div style="margin-top:12px"><div style="font-size:11px;color:#64748b;margin-bottom:4px">Release notes (v<?php echo $avCode; ?> — <?php echo htmlspecialchars($avDate); ?>):</div><pre style="background:rgba(0,0,0,.3);padding:10px;border-radius:6px;font-size:11px;white-space:pre-wrap;color:#94a3b8;max-height:120px;overflow:auto"><?php echo htmlspecialchars($notes); ?></pre></div>
<?php endif; ?>
<?php if ($behind > 0 && $avChecksum): ?><div style="font-size:11px;color:#64748b;margin-top:8px">Package SHA-256 (verified after download): <code style="font-size:10px"><?php echo htmlspecialchars(substr($avChecksum, 0, 12)); ?>…</code></div><?php endif; ?>
</div>

<div class="set-card" id="update-actions">
<h4><i class="bi bi-download"></i> Actions</h4>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<button type="button" class="btn set-btn-ghost" onclick="checkUpdates(this)">🔍 Check for Updates</button> <span id="checkSpinner" style="display:none;color:#0A84FF;font-size:12px"><i class="bi bi-arrow-repeat" style="display:inline-block;animation:spin 1s linear infinite"></i> Checking...</span>
<style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>
<script>
function checkUpdates(btn){
  var s=document.getElementById('checkSpinner');
  s.style.display=''; btn.disabled=true; btn.style.opacity='.5';
  fetch('/admin/update/check', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'_csrf_token=<?php echo htmlspecialchars($_SESSION["_csrf_token"] ?? ""); ?>'})
    .then(function(r){return r.json()}).then(function(d){
      s.innerHTML = d.available ? '✓ v' + (d.latest_code||'') + ' available' : '✓ Up to date';
      s.style.color = d.available ? '#facc15' : '#4ade80';
      setTimeout(function(){ location.reload(); }, 800);
    }).catch(function(){ s.textContent='✗ Check failed'; s.style.color='#f87171'; btn.disabled=false; btn.style.opacity='1'; });
}
</script>
<?php if ($behind > 0): ?>
<form method="POST" action="/admin/update/install" style="display:inline" onsubmit="return confirm('Download & install Ph-Whm <?php echo htmlspecialchars($avVer); ?> (v<?php echo $avCode; ?>)? This will download the release package, verify its checksum, back up, migrate, and reload services. Continue?')"><input type="hidden" name="confirm" value="yes"><button type="submit" class="btn set-btn-save">⬇ Download &amp; Install (v<?php echo $avCode; ?>)</button></form>
<?php endif; ?>
<?php if ($hasBackup): ?>
<form method="POST" action="/admin/update/rollback" style="display:inline" onsubmit="return confirm('Rollback to previous release?')"><button type="submit" class="btn" style="background:rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.2)">↩ Rollback</button></form>
<?php endif; ?>
</div>
</div>
</div>

<div>
<div class="set-card">
<h4><i class="bi bi-clock-history"></i> Update Log</h4>
<pre style="background:rgba(0,0,0,.3);padding:12px;border-radius:6px;font-size:11px;white-space:pre-wrap;color:#94a3b8;max-height:300px;overflow:auto"><?php echo htmlspecialchars($logContent ?: 'No log yet. Check for updates or install to generate log at storage/update.log'); ?></pre>
<a href="/admin/update/log" target="_blank" style="font-size:11px;color:#0A84FF">Open raw log →</a>
</div>
</div>
</div>
</div>

<?php if ($behind > 0): ?>
<script>
// Auto-refresh check every 5 min
setInterval(function(){ fetch('/admin/update/check', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'_csrf_token=<?php echo htmlspecialchars($_SESSION["_csrf_token"] ?? ""); ?>'}).then(function(){ location.reload(); }); }, 300000);
</script>
<?php endif; ?>