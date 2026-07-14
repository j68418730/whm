<?php $theme_settings = json_decode($user->theme_settings ?? '{}', true); ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2><?php echo $dj ? 'Edit DJ' : 'Create DJ Account'; ?></h2>
    <a href="/admin/dj" class="btn secondary">← Back to DJs</a>
</div>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="<?php echo $dj ? '/admin/dj/update/' . $dj->id : '/admin/dj/store'; ?>">
        <div class="form-group">
            <label for="username">Username *</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($dj->username ?? ''); ?>" <?php echo $dj ? 'readonly' : 'required'; ?> class="inp">
            <?php if ($dj): ?><small style="color:var(--text-muted);font-size:11px">Username cannot be changed after creation</small><?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($dj->email ?? ''); ?>" required class="inp">
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($dj->full_name ?? ''); ?>" class="inp">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="password">Password <?php echo $dj ? '' : '*'; ?></label>
                <input type="password" name="password" id="password" <?php echo $dj ? '' : 'required'; ?> class="inp" placeholder="<?php echo $dj ? 'Leave blank to keep current' : 'Enter password'; ?>">
                <?php if ($dj): ?><small style="color:var(--text-muted);font-size:11px">Leave blank to keep current password</small><?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="role">Role *</label>
                <select name="role" id="role" class="inp" required>
                    <option value="dj" <?php echo ($dj->role ?? 'dj') === 'dj' ? 'selected' : ''; ?>>DJ</option>
                    <option value="guest_dj" <?php echo ($dj->role ?? '') === 'guest_dj' ? 'selected' : ''; ?>>Guest DJ</option>
                    <option value="station_manager" <?php echo ($dj->role ?? '') === 'station_manager' ? 'selected' : ''; ?>>Station Manager</option>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status *</label>
                <select name="status" id="status" class="inp" required>
                    <option value="active" <?php echo ($dj->status ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($dj->status ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="suspended" <?php echo ($dj->status ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($dj->email ?? ''); ?>" required class="inp">
        </div>

        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($dj->full_name ?? ''); ?>" class="inp">
        </div>

        <div class="d-flex gap-8 mt-24">
            <a href="/admin/dj" class="btn secondary">Cancel</a>
            <button type="submit" class="btn primary"><?php echo $dj ? 'Update DJ' : 'Create DJ'; ?></button>
        </div>
    </form>
</div>