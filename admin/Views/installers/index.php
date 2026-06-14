<p style="color:var(--text-secondary);margin-bottom:16px">Select an application to install. You can also browse the <a href="/admin/marketplace" style="color:var(--accent)">Application Marketplace</a> for more options.</p>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px">
<?php foreach ($apps as $a): ?>
<div class="app-card" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:16px;text-align:center;cursor:pointer;transition:.15s" onclick="installApp('<?php echo htmlspecialchars($a['name']); ?>')">
<div style="font-size:36px;margin-bottom:8px"><?php echo $a['icon']; ?></div>
<h4 style="margin:0;font-size:14px"><?php echo htmlspecialchars($a['name']); ?></h4>
<p style="color:var(--text-secondary);font-size:12px;margin:4px 0 0"><?php echo htmlspecialchars($a['desc']); ?></p>
</div>
<?php endforeach; ?>
</div>

<div id="installModal" class="modal-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.7);z-index:9999;align-items:center;justify-content:center">
<div class="card" style="max-width:500px;width:90%">
<h3 style="color:var(--accent);margin-bottom:12px">Install <span id="installName"></span></h3>
<form method="POST" action="/admin/installers/install">
<input type="hidden" name="app_name" id="installAppName">
<div class="form-group"><label>Domain</label><input name="domain" required placeholder="example.com"></div>
<div class="form-group"><label>Directory (optional)</label><input name="directory" placeholder="blog"></div>
<div class="form-group"><label>Admin Email</label><input name="admin_email" type="email" required placeholder="admin@example.com"></div>
<div class="form-group"><label>Admin Username</label><input name="admin_user" required value="admin"></div>
<div class="form-group"><label>Admin Password</label><input name="admin_pass" type="password" required></div>
<div style="display:flex;gap:8px;margin-top:16px">
<button type="submit" class="btn primary">Install</button>
<button type="button" class="btn secondary" onclick="document.getElementById('installModal').style.display='none'">Cancel</button>
</div>
</form></div></div>

<script>
function installApp(name) {
    document.getElementById('installName').textContent = name;
    document.getElementById('installAppName').value = name;
    document.getElementById('installModal').style.display = 'flex';
}
document.getElementById('installModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
