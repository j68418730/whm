<?php $theme_settings = json_decode($user->theme_settings ?? '{}', true); ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-24">
    <h2>Create DJ Account</h2>
    <a href="/user/dj-panel" class="btn secondary">← Back to DJs</a>
</div>

<div class="card" style="max-width:700px">
    <form method="POST" action="/user/dj-panel/create" style="display:flex;flex-direction:column;gap:16px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div>
                <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Username *</label>
                <input type="text" name="username" class="inp" required style="width:100%" autocomplete="username">
            </div>
            <div>
                <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Email *</label>
                <input type="email" name="email" class="inp" required style="width:100%" autocomplete="email">
            </div>
        </div>
        <div>
            <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Full Name</label>
            <input type="text" name="full_name" class="inp" style="width:100%" autocomplete="name">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div>
                <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Password *</label>
                <input type="password" name="password" class="inp" required style="width:100%" autocomplete="new-password">
            </div>
            <div>
                <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Confirm Password *</label>
                <input type="password" name="password_confirm" class="inp" required style="width:100%" autocomplete="new-password">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div>
                <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Role *</label>
                <select name="role" class="inp" required style="width:100%">
                    <option value="dj">DJ</option>
                    <option value="guest_dj">Guest DJ</option>
                    <option value="station_manager">Station Manager</option>
                </select>
            </div>
            <div>
                <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Status *</label>
                <select name="status" class="inp" required style="width:100%">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div>
                <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Primary Station (optional)</label>
                <select name="station_id" class="inp" style="width:100%">
                    <option value="0">— None —</option>
                    <?php foreach (($stations ?? []) as $st): ?>
                    <option value="<?php echo $st->id; ?>"><?php echo htmlspecialchars($st->username); ?><?php echo !empty($st->domain) ? ' (' . htmlspecialchars($st->domain) . ')' : ''; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display:block;margin-bottom:6px;font-size:13px;color:var(--text-secondary)">Station Role</label>
                <select name="station_role" class="inp" style="width:100%">
                    <option value="dj">DJ</option>
                    <option value="manager">Manager</option>
                    <option value="owner">Owner</option>
                    <option value="guest_dj">Guest DJ</option>
                </select>
            </div>
        </div>

        <!-- Multi-Station Assignment (Checkbox Grid) -->
        <div style="margin-top:16px;padding:16px;background:rgba(251,146,60,.08);border:1px solid rgba(251,146,60,.2);border-radius:8px">
            <label style="display:block;margin-bottom:12px;font-size:13px;font-weight:600;color:var(--text-secondary)">Additional Stations (optional)</label>
            <p style="font-size:12px;color:var(--text-muted);margin-bottom:12px">Select additional stations this DJ can access. Primary station above is automatically included.</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px">
                <?php foreach (($stations ?? []) as $st): ?>
                <label style="display:flex;align-items:center;gap:8px;padding:8px;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.08);border-radius:6px;cursor:pointer;font-size:13px;color:#e0e0e0;transition:all .15s">
                    <input type="checkbox" name="station_ids[]" value="<?php echo $st->id; ?>" style="margin:0;transform:scale(1.1)">
                    <span><?php echo htmlspecialchars($st->username); ?><?php echo !empty($st->domain) ? ' (' . htmlspecialchars($st->domain) . ')' : ''; ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div style="display:flex;gap:8px;margin-top:8px">
            <button type="submit" class="btn primary">Create DJ Account</button>
            <a href="/user/dj-panel" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>