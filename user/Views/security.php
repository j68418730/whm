<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px">
<a href="javascript:void(0)" onclick="showSecTab('ssl')" class="btn primary">SSL</a>
<a href="javascript:void(0)" onclick="showSecTab('ipblock')" class="btn secondary">IP Blocker</a>
<a href="javascript:void(0)" onclick="showSecTab('password')" class="btn secondary">Password</a>
<a href="javascript:void(0)" onclick="showSecTab('2fa')" class="btn secondary">2FA</a>
</div>

<!-- SSL -->
<div id="sec-ssl" class="sec-tab">
<div class="card" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/user/ssl/install"><div style="display:flex;gap:8px;align-items:end">
<div class="form-group" style="flex:1"><label>Domain for SSL</label><input name="domain" value="<?php echo htmlspecialchars($hosting->domain ?? ''); ?>" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></div>
<div class="form-group"><button type="submit" class="btn primary">Install SSL</button></div>
</div></form>
</div>
<table><tr><th>Domain</th><th>Status</th><th>Type</th><th></th></tr>
<?php if (!empty($certs)): foreach ($certs as $c): ?>
<tr><td><?php echo htmlspecialchars($c->domain); ?></td><td><span class="status-badge status-<?php echo $c->status === 'active' ? 'active' : 'suspended'; ?>"><?php echo ucfirst($c->status); ?></span></td><td><?php echo $c->type; ?></td>
<td><a href="/user/ssl/delete/<?php echo $c->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">✕</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No SSL certificates yet.</td></tr>
<?php endif; ?></table>
</div>

<!-- IP Blocker -->
<div id="sec-ipblock" class="sec-tab" style="display:none">
<div class="card" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/user/ipblock"><div style="display:flex;gap:8px;flex-wrap:wrap;align-items:end">
<div class="form-group"><label>IP Address</label><input name="ip" required placeholder="192.168.1.100" style="padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></div>
<div class="form-group"><label>Notes</label><input name="notes" placeholder="Spam bot" style="padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></div>
<div class="form-group"><button type="submit" class="btn primary">Block IP</button></div>
</div></form>
</div>
<table><tr><th>IP</th><th>Notes</th><th></th></tr>
<?php if (!empty($blocks)): foreach ($blocks as $b): ?>
<tr><td><?php echo htmlspecialchars($b->ip_address); ?></td><td><?php echo htmlspecialchars($b->notes ?? '-'); ?></td>
<td><a href="/user/ipblock/delete/<?php echo $b->id; ?>" class="btn btn-sm secondary">Unblock</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b">No IPs blocked.</td></tr>
<?php endif; ?></table>
</div>

<!-- Password -->
<div id="sec-password" class="sec-tab" style="display:none">
<div class="card" style="max-width:400px">
<form method="POST" action="/user/password">
<div class="form-group"><label>New Password</label><input type="password" name="password" required minlength="8" style="width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></div>
<button type="submit" class="btn primary">Change Password</button>
</form></div>
</div>

<!-- 2FA -->
<div id="sec-2fa" class="sec-tab" style="display:none">
<div class="card" style="max-width:400px">
<h4 style="color:var(--accent);margin-bottom:12px">Two-Factor Authentication</h4>
<?php if ($twoFactor && $twoFactor->enabled): ?>
<p style="color:#4ade80;margin-bottom:12px">✅ 2FA is enabled</p>
<p style="color:var(--text-muted);font-size:13px;margin-bottom:12px">Secret: <code><?php echo htmlspecialchars($twoFactor->secret); ?></code></p>
<a href="/user/2fa/disable" class="btn danger">Disable 2FA</a>
<?php else: ?>
<p style="color:var(--text-secondary);margin-bottom:12px">Add an extra layer of security to your account.</p>
<a href="/user/2fa/enable" class="btn primary">Enable 2FA</a>
<?php endif; ?>
</div>
</div>

<script>
function showSecTab(tab) {
    document.querySelectorAll('.sec-tab').forEach(function(el) { el.style.display = 'none'; });
    document.getElementById('sec-' + tab).style.display = 'block';
}
</script>
