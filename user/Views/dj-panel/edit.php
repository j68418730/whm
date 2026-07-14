<?php $theme_settings = json_decode($user->theme_settings ?? '{}', true); ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2>Edit DJ: <?php echo htmlspecialchars($dj->username); ?></h2>
    <a href="/user/dj-panel" class="btn secondary">← Back to DJs</a>
</div>

<div class="card" style="max-width:700px">
    <form method="POST" action="/user/dj-panel/edit/<?php echo $dj->id; ?>" style="display:flex;flex-direction:column;gap:16px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div>
                <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Username</label>
                <input type="text" name="username" class="inp" value="<?php echo htmlspecialchars($dj->username); ?>" required style="width:100%" autocomplete="username">
            </div>
            <div>
                <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Email</label>
                <input type="email" name="email" class="inp" value="<?php echo htmlspecialchars($dj->email ?? ''); ?>" style="width:100%" autocomplete="email">
            </div>
        </div>
        <div>
            <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Full Name</label>
            <input type="text" name="full_name" class="inp" value="<?php echo htmlspecialchars($dj->full_name ?? ''); ?>" style="width:100%" autocomplete="name">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div>
                <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Role</label>
                <select name="role" class="inp" style="width:100%">
                    <option value="dj" <?php echo $dj->role === 'dj' ? 'selected' : ''; ?>>DJ</option>
                    <option value="guest_dj" <?php echo $dj->role === 'guest_dj' ? 'selected' : ''; ?>>Guest DJ</option>
                    <option value="station_manager" <?php echo $dj->role === 'station_manager' ? 'selected' : ''; ?>>Station Manager</option>
                    <option value="super_admin" <?php echo $dj->role === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                </select>
            </div>
            <div>
                <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Status</label>
                <select name="status" class="inp" style="width:100%">
                    <option value="active" <?php echo $dj->status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $dj->status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="suspended" <?php echo $dj->status === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                </select>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:8px">
            <div>
                <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">New Password (leave blank to keep current)</label>
                <input type="password" name="password" class="inp" style="width:100%" autocomplete="new-password">
            </div>
            <div>
                <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Confirm New Password</label>
                <input type="password" name="password_confirm" class="inp" style="width:100%" autocomplete="new-password">
            </div>
        </div>
        <div style="margin-top:8px;padding:12px;background:rgba(251,146,60,.08);border:1px solid rgba(251,146,60,.2);border-radius:8px;font-size:12px;color:#fb923c">
            <strong>Note:</strong> Leave password fields blank to keep current password. Changing password will invalidate all active sessions.
        </div>
        <div style="display:flex;gap:8px;margin-top:8px">
            <button type="submit" class="btn primary">Save Changes</button>
            <a href="/user/dj-panel/show/<?php echo $dj->id; ?>" class="btn secondary">Cancel</a>
            <?php if ($dj->status !== 'suspended'): ?>
            <form method="POST" action="/user/dj-panel/suspend/<?php echo $dj->id; ?>" style="display:inline" onsubmit="return confirm('Suspend this DJ account?');">
                <button type="submit" class="btn btn-sm warning">Suspend</button>
            </form>
            <?php else: ?>
            <form method="POST" action="/user/dj-panel/activate/<?php echo $dj->id; ?>" style="display:inline" onsubmit="return confirm('Activate this DJ account?');">
                <button type="submit" class="btn btn-sm success">Activate</button>
            </form>
            <?php endif; ?>
        </div>
    </form>
</div>