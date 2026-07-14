<?php $theme_settings = json_decode($user->theme_settings ?? '{}', true); ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['generated_key'])): ?>
<div class="alert alert-success" style="border:2px solid #22c55e;background:rgba(34,197,94,.08)">
  <strong style="font-size:13px">🔑 API Key Generated — Copy it now!</strong>
  <div style="font-size:11px;color:#64748b;margin:4px 0 8px">This key will not be shown again.</div>
  <div style="display:flex;gap:6px;align-items:center">
    <code id="genKeyDisplay" style="flex:1;padding:8px 12px;border-radius:6px;font-size:13px;background:rgba(0,0,0,.4);border:1px solid rgba(0,191,255,.2);color:#22c55e;font-family:monospace;font-size:11px"><?php echo htmlspecialchars($_SESSION['generated_key']); unset($_SESSION['generated_key']); ?></code>
    <button class="btn primary" onclick="copyKey()" style="white-space:nowrap">📋 Copy</button>
  </div>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2>API Keys for: <?php echo htmlspecialchars($dj->username); ?></h2>
    <div style="display:flex;gap:8px">
        <a href="/user/dj-panel/show/<?php echo $dj->id; ?>" class="btn secondary">← Back to DJ</a>
        <button class="btn primary" onclick="generateKey()">🔑 Generate API Key</button>
    </div>
</div>

<div class="card">
    <div style="display:flex;gap:8px;margin-bottom:16px">
        <input type="text" id="keySearch" placeholder="Search API keys..." class="inp" style="width:250px">
        <select id="keyStatusFilter" class="inp" style="width:150px">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="revoked">Revoked</option>
            <option value="expired">Expired</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Key Prefix</th>
                    <th>Name</th>
                    <th>Permissions</th>
                    <th>Rate Limit</th>
                    <th>Expires</th>
                    <th>Last Used</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apiKeys as $key): ?>
                <tr>
                    <td><code style="font-size:11px;color:var(--accent)"><?php echo htmlspecialchars($key->key_prefix); ?>****</code></td>
                    <td><?php echo htmlspecialchars($key->name); ?></td>
                    <td><span class="badge badge-primary"><?php echo htmlspecialchars($key->permissions ?? 'read'); ?></span></td>
                    <td><?php echo number_format($key->rate_limit); ?>/hr</td>
                    <td><?php echo $key->expires_at ? date('M j, Y', strtotime($key->expires_at)) : 'Never'; ?></td>
                    <td><?php echo $key->last_used_at ? date('M j, Y H:i', strtotime($key->last_used_at)) : 'Never'; ?></td>
                    <?php $keyStatus = $key->revoked_at ? 'revoked' : ($key->expires_at && $key->expires_at < date('Y-m-d H:i:s') ? 'expired' : 'active'); ?>
                    <td><span class="status-badge status-<?php echo $keyStatus; ?>"><?php echo ucfirst($keyStatus); ?></span></td>
                    <td><?php echo date('M j, Y', strtotime($key->created_at)); ?></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <?php if ($keyStatus === 'active'): ?>
                            <form method="POST" action="/user/dj-panel/api-keys/revoke/<?php echo $key->id; ?>" style="display:inline" onsubmit="return confirm('Revoke this API key?');">
                                <button type="submit" class="btn btn-sm warning">Revoke</button>
                            </form>
                            <?php else: ?>
                            <span class="badge badge-secondary">Revoked</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (empty($apiKeys)): ?>
    <p style="text-align:center;color:var(--text-muted);padding:32px">No API keys created yet. Click "Generate API Key" to create one.</p>
    <?php endif; ?>
</div>

<script>
function generateKey() {
    var name = prompt('Enter a name for this API key (e.g., "Studio App", "Mobile App"):');
    if (!name) return;
    var perms = prompt('Permissions (comma-separated): read,write,stream,admin', 'read,write,stream');
    var rate = prompt('Rate limit (requests/hour):', '1000');
    var expires = prompt('Expires in days (empty for never):', '365');
    
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/user/dj-panel/api-keys/generate/<?php echo $dj->id; ?>';
    form.innerHTML = '<input name="name" value="'+name+'"><input name="permissions" value="'+perms+'"><input name="rate_limit" value="'+rate+'"><input name="expires_days" value="'+expires+'">';
    document.body.appendChild(form);
    form.submit();
}

function copyKey() {
    var el = document.getElementById('genKeyDisplay');
    navigator.clipboard.writeText(el.textContent).then(() => {
        var btn = event.target;
        var orig = btn.textContent;
        btn.textContent = '✅ Copied!';
        setTimeout(() => btn.textContent = orig, 2000);
    });
}

document.getElementById('keySearch')?.addEventListener('input', function() {
    var val = this.value.toLowerCase().trim();
    document.querySelectorAll('tbody tr').forEach(function(tr) {
        tr.style.display = !val || tr.textContent.toLowerCase().indexOf(val) > -1 ? '' : 'none';
    });
});
document.getElementById('keyStatusFilter')?.addEventListener('change', function() {
    var val = this.value;
    document.querySelectorAll('tbody tr').forEach(function(tr) {
        var status = tr.querySelector('.status-badge')?.textContent?.toLowerCase() || '';
        tr.style.display = !val || status === val ? '' : 'none';
    });
});
</script>