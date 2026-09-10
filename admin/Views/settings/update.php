<?php require __DIR__ . '/_tabs.php'; ?>
<div class="set-wrap">
<div class="set-head"><h2>🔄 System Update <span style="font-size:12px;color:#64748b;font-weight:400">Ph- V1.3Beta-Whm</span></h2><p>Current: <code><?php echo htmlspecialchars($current); ?></code> → Upstream: <code><?php echo htmlspecialchars($upstream); ?></code> — <?php echo $behind > 0 ? '<span style="color:#facc15;font-weight:700">' . $behind . ' update(s) available</span>' : '<span style="color:#4ade80">Up to date</span>'; ?></p></div>

<?php if ($behind > 0): ?>
<div class="set-alert-err" style="background:rgba(250,204,21,.08);border-color:rgba(250,204,21,.2);color:#facc15">🔔 Update available — <?php echo $behind; ?> commit(s) behind. <a href="#update-actions" style="color:#facc15;text-decoration:underline">Update now</a></div>
<?php endif; ?>

<div class="set-grid">
<div>
<div class="set-card">
<h4><i class="bi bi-git"></i> Version Status</h4>
<div class="set-kv"><span class="k">Current</span><span class="v">Ph- V1.3Beta-Whm (<code><?php echo htmlspecialchars($current); ?></code>)</span><span class="k">Upstream</span><span class="v">Ph- V1.3Beta-Whm (<code><?php echo htmlspecialchars($upstream); ?></code>)</span><span class="k">Behind</span><span class="v"><?php echo $behind; ?> commit(s)</span><span class="k">Backup</span><span class="v"><?php echo $hasBackup ? '✓ Available' : '— None'; ?></span></div>
<?php if (!empty($commits)): ?>
<div style="margin-top:12px"><div style="font-size:11px;color:#64748b;margin-bottom:4px">Changelog (upstream):</div><pre style="background:rgba(0,0,0,.3);padding:10px;border-radius:6px;font-size:11px;white-space:pre-wrap;color:#94a3b8;max-height:120px;overflow:auto"><?php foreach ($commits as $c) echo htmlspecialchars($c) . "\n"; ?></pre></div>
<?php endif; ?>
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
      s.innerHTML = d.update_available ? '✓ ' + d.behind + ' update(s) found' : '✓ Up to date';
      s.style.color = d.update_available ? '#facc15' : '#4ade80';
      setTimeout(function(){ location.reload(); }, 800);
    }).catch(function(){ s.textContent='✗ Check failed'; s.style.color='#f87171'; btn.disabled=false; btn.style.opacity='1'; });
}
</script>
<?php if ($behind > 0): ?>
<form method="POST" action="/admin/update/install" style="display:inline" onsubmit="return confirm('Install update? This will backup, pull, migrate, and reload services. Continue?')"><input type="hidden" name="confirm" value="yes"><button type="submit" class="btn set-btn-save">⬇ Install Update (<?php echo $behind; ?>)</button></form>
<?php endif; ?>
<?php if ($hasBackup): ?>
<form method="POST" action="/admin/update/rollback" style="display:inline" onsubmit="return confirm('Rollback to previous version?')"><button type="submit" class="btn" style="background:rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.2)">↩ Rollback</button></form>
<?php endif; ?>
</div>
</div>
</div>

<div>
<div class="set-card">
<h4><i class="bi bi-clock-history"></i> Update Log</h4>
<pre style="background:rgba(0,0,0,.3);padding:12px;border-radius:6px;font-size:11px;white-space:pre-wrap;color:#94a3b8;max-height:300px;overflow:auto"><?php echo htmlspecialchars($logContent ?: 'No log yet. Check or install to generate log at storage/update.log'); ?></pre>
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
