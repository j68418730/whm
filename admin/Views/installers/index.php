<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
<div>
<h2 style="margin:0"><i class="bi bi-download"></i> One-Click Installer</h2>
<p style="color:#64748b;margin:4px 0 0">Install applications on any hosting account with one click.</p>
</div>
<div style="display:flex;gap:8px">
<a href="/admin/marketplace" class="btn secondary"><i class="bi bi-shop"></i> Marketplace</a>
<a href="/download_app_logos.php" class="btn btn-sm secondary"><i class="bi bi-arrow-clockwise"></i> Refresh Logos</a>
<a href="/admin/api/logs" class="btn btn-sm secondary"><i class="bi bi-journal-code"></i> Install Logs</a>
</div>
</div>

<?php
$cats = [];
foreach ($apps as $a) {
    $cat = $a['category'] ?? 'General';
    $cats[$cat][] = $a;
}
?>

<?php foreach ($cats as $catName => $catApps): ?>
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);font-size:15px;margin-bottom:12px"><i class="bi bi-grid"></i> <?php echo htmlspecialchars($catName); ?></h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">
<?php foreach ($catApps as $a): ?>
<div style="background:rgba(255,255,255,.03);border:1px solid rgba(0,191,255,.1);border-radius:10px;padding:16px;text-align:center;transition:.2s">
<div style="font-size:36px;margin-bottom:6px"><?php echo $a['icon']; ?></div>
<div style="font-weight:600;font-size:14px"><?php echo htmlspecialchars($a['name']); ?></div>
<div style="color:#94a3b8;font-size:11px;margin:4px 0"><?php echo htmlspecialchars($a['desc']); ?> v<?php echo htmlspecialchars($a['version']); ?></div>
<button class="btn btn-sm primary" style="width:100%;margin-top:8px" onclick="installApp('<?php echo htmlspecialchars($a['name']); ?>')"><i class="bi bi-download"></i> Install</button>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>

<?php if (!empty($installs)): ?>
<div class="card" style="margin-bottom:20px">
<h3 style="color:var(--accent);margin-bottom:12px"><i class="bi bi-clock-history"></i> Recent Installations</h3>
<div style="overflow-x:auto">
<table>
<thead><tr><th>App</th><th>Domain</th><th>Status</th><th>Date</th></tr></thead>
<tbody>
<?php foreach ($installs as $t): ?>
<tr>
<td><?php echo htmlspecialchars($t->app_name); ?></td>
<td><?php echo htmlspecialchars($t->domain); ?></td>
<td><span class="status-badge status-<?php echo $t->status === 'completed' ? 'active' : 'suspended'; ?>"><?php echo ucfirst($t->status); ?></span></td>
<td style="font-size:12px"><?php echo date('M j, Y g:i a', strtotime($t->created_at)); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<?php endif; ?>

<!-- Install Modal -->
<div id="installModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px)">
<div class="card" style="max-width:480px;width:92%;padding:28px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
<h3 style="margin:0;color:var(--accent)"><i class="bi bi-download"></i> Install <span id="modalAppName"></span></h3>
<button onclick="closeModal()" style="background:none;border:none;color:#64748b;font-size:22px;cursor:pointer">&times;</button>
</div>
<form method="POST" action="/admin/installers/install">
<input type="hidden" name="app_name" id="installAppName">
<div class="form-group"><label>Select Existing Account</label>
<select name="account_id" style="width:100%">
<option value="">— Create new account —</option>
<?php if (!empty($accounts)): foreach ($accounts as $acct): ?>
<option value="<?php echo $acct->id; ?>"><?php echo htmlspecialchars($acct->domain ?: $acct->username); ?> (<?php echo htmlspecialchars($acct->username); ?>)</option>
<?php endforeach; endif; ?>
</select>
</div>
<div class="form-group"><label>Or Create New Username</label>
<input name="username" placeholder="Leave blank to use existing account" style="width:100%">
</div>
<div class="form-group"><label>Domain</label>
<input name="domain" placeholder="app.example.com" style="width:100%">
</div>
<button type="submit" class="btn btn-lg primary" style="width:100%"><i class="bi bi-download"></i> Install Now</button>
</form>
</div>
</div>

<script>
function installApp(name) {
    document.getElementById('installAppName').value = name;
    document.getElementById('modalAppName').textContent = name;
    document.getElementById('installModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('installModal').style.display = 'none';
}
document.getElementById('installModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
