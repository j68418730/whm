<style>
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:22px;text-decoration:none;color:#e0e0e0;transition:.2s;margin-bottom:12px}
.section-card h3{font-size:14px;font-weight:600;margin:0 0 10px}
input,select{padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;width:100%;box-sizing:border-box;margin-bottom:8px}
.btn{padding:8px 16px;border-radius:6px;border:none;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;font-size:12px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block}
</style>

<h2>🌍 Subdomains</h2>
<p style="color:#64748b;margin-bottom:16px">Create and manage subdomains for your domains.</p>

<?php if (isset($_SESSION['success'])): ?><div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error'])): ?><div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div><?php endif; ?>

<div class="section-card">
<h3>➕ Create Subdomain</h3>
<form method="POST" action="/user/subdomains/create" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div style="flex:1;min-width:120px">
<label style="font-size:11px;color:#64748b;display:block;margin-bottom:2px">Subdomain</label>
<input name="subdomain" placeholder="blog" required>
</div>
<div style="flex:1;min-width:150px">
<label style="font-size:11px;color:#64748b;display:block;margin-bottom:2px">Domain</label>
<select name="domain" required>
<option value="">Select domain...</option>
<?php foreach ($zones as $z): ?>
<option value="<?php echo htmlspecialchars($z->domain); ?>"><?php echo htmlspecialchars($z->domain); ?></option>
<?php endforeach; ?>
</select>
</div>
<button type="submit" class="btn">Create Subdomain</button>
</form>
</div>
