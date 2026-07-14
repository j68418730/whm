<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['generated_key'])): ?>
<div class="alert alert-success" style="border:2px solid #22c55e;background:rgba(34,197,94,.08)">
  <strong style="font-size:13px">🔑 Key Generated — Copy it now!</strong>
  <div style="font-size:11px;color:#64748b;margin:6px 0 8px">This key will not be shown again.</div>
  <div style="display:flex;gap:6px;align-items:center">
    <code id="genKeyDisplay" style="flex:1;padding:8px 12px;border-radius:6px;font-size:13px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1);color:#22c55e;font-family:monospace;font-size:11px"><?php echo htmlspecialchars($_SESSION['generated_key']); unset($_SESSION['generated_key']); ?></code>
    <button class="btn primary" onclick="copyKey()" style="white-space:nowrap">📋 Copy</button>
  </div>
</div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2>API Keys for <?php echo htmlspecialchars($dj->username); ?></h2>
    <a href="/admin/dj/show/<?php echo $dj->id; ?>" class="btn secondary">← Back to DJ</a>
</div>

<div class="card mb-24">
    <h3 style="color:var(--accent);margin-bottom:12px">Generate New API Key</h3>
    <form method="POST" action="/admin/dj/api-key/generate/<?php echo $dj->id; ?>" style="display:grid;gap:12px;max-width:500px">
        <div class="form-group">
            <label>Key Name</label>
            <input name="name" class="inp" placeholder="e.g., Production Server, Mobile App" required>
        </div>
        <div class="form-group">
            <label>Permissions</label>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <label style="display:flex;align-items:center;gap:4px;font-size:12px"><input type="checkbox" name="permissions[]" value="read" checked> Read</label>
                <label style="display:flex;align-items:center;gap:4px;font-size:12px"><input type="checkbox" name="permissions[]" value="write" checked> Write</label>
                <label style="display:flex;align-items:center;gap:4px;font-size:12px"><input type="checkbox" name="permissions[]" value="stream"> Stream</label>
                <label style="display:flex;align-items:center;gap:4px;font-size:12px"><input type="checkbox" name="permissions[]" value="admin"> Admin</label>
            </div>
        </div>
        <div class="form-group">
            <label>Rate Limit (req/min, 0 = unlimited)</label>
            <input name="rate_limit" type="number" value="60" min="0" class="inp" style="width:100px">
        </div>
        <div class="form-group">
            <label>Expires (optional)</label>
            <input name="expires_at" type="datetime-local" class="inp" style="width:200px">
        </div>
        <button type="submit" class="btn primary">Generate Key</button>
    </form>
</div>

<div class="card" style="margin-top:24px">
    <h3 style="color:var(--accent);margin-bottom:12px">Existing API Keys</h3>
    <?php if (empty($keys)): ?>
    <p style="color:var(--text-muted);padding:24px;text-align:center">No API keys yet.</    <?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Prefix</th>
                    <th>Permissions</th>
                    <th>Rate Limit</th>
                    <th>Status</th>
                    <th>Last Used</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($keys as $k): ?>
                <tr>
                    <td><?php echo htmlspecialchars($k->name); ?></td>
                    <td><code><?php echo htmlspecialchars($k->key_prefix); ?>...</code></td>
                    <td><?php echo htmlspecialchars(implode(', ', json_decode($k->permissions ?? '[]'))); ?></td>
                    <td><?php echo $k->rate_limit ?: 'Unlimited'; ?>/min</td>
                    <td><span class="status-badge status-<?php echo $k->revoked_at ? 'terminated' : ($k->expires_at && $k->expires_at < date('Y-m-d H:i:s') ? 'terminated' : 'active'); ?>"><?php echo $k->revoked_at ? 'Revoked' : ($k->expires_at && $k->expires_at < date('Y-m-d H:i:s') ? 'Expired' : 'Active'); ?></span></td>
                    <td><?php echo $k->last_used_at ? date('M j, Y H:i', strtotime($k->last_used_at)) : 'Never'; ?></td>
                    <td><?php echo date('M j, Y', strtotime($k->created_at)); ?></td>
                    <td>
                        <?php if (!$k->revoked_at): ?>
                        <form method="POST" action="/admin/dj/api-key/revoke/<?php echo $k->id; ?>" style="display:inline" onsubmit="return confirm('Revoke this API key?');">
                            <input type="hidden" name="dj_id" value="<?php echo $dj->id; ?>">
                            <button type="submit" class="btn btn-sm danger">Revoke</button>
                        </form>
                        <?php else: ?>
                        <span style="color:var(--text-muted);font-size:11px">Revoked</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>