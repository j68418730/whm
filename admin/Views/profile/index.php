<?php
$activeTab = $_GET['tab'] ?? 'profile';
?>
<style>
.tab-buttons { display:flex; gap:4px; margin-bottom:16px; }
.tab-btn { padding:8px 16px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:6px; color:var(--text-secondary); cursor:pointer; font-size:13px; font-weight:500; transition:.15s; }
.tab-btn:hover { background:rgba(0,191,255,.1); border-color:rgba(0,191,255,.3); color:#fff; }
.tab-btn.active { background:rgba(0,191,255,.2); border-color:#0A84FF; color:#0A84FF; }
.tab-content { display:none; }
.tab-content.active { display:block; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:12px; font-weight:600; margin-bottom:6px; color:var(--text); }
.form-group input, .form-group select { width:100%; padding:10px 12px; background:rgba(0,0,0,.3); border:1px solid rgba(255,255,255,.1); border-radius:6px; color:#fff; font-size:13px; outline:none; }
.form-group input:focus, .form-group select:focus { border-color:#0A84FF; }
.avatar-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(80px,1fr)); gap:8px; margin-top:8px; }
.avatar-option { position:relative; cursor:pointer; border:2px solid transparent; border-radius:8px; overflow:hidden; transition:.15s; }
.avatar-option img { width:100%; aspect-ratio:1; object-fit:cover; }
.avatar-option.selected { border-color:#0A84FF; box-shadow:0 0 12px rgba(0,191,255,.3); }
.avatar-option input { display:none; }
.password-section { background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.05); border-radius:8px; padding:16px; margin-top:24px; }
.section-title { font-size:14px; font-weight:600; margin-bottom:12px; color:var(--text); }
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <h2 style="margin:0">My Profile</h2>
</div>

<div class="tab-buttons">
    <button class="tab-btn <?php echo $activeTab === 'profile' ? 'active' : ''; ?>" onclick="switchTab('profile')">Profile</button>
    <button class="tab-btn <?php echo $activeTab === 'password' ? 'active' : ''; ?>" onclick="switchTab('password')">Password</button>
</div>

<div id="tab-profile" class="tab-content <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
    <form method="POST" action="/admin/profile" enctype="multipart/form-data">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($admin->name ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($admin->email ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="form-group">
            <label>Avatar</label>
            <div class="avatar-grid">
                <?php foreach ($avatars as $avatarFile): ?>
                    <label class="avatar-option <?php echo ($admin->avatar ?? '') === $avatarFile ? 'selected' : ''; ?>">
                        <input type="radio" name="avatar" value="<?php echo htmlspecialchars($avatarFile); ?>" <?php echo ($admin->avatar ?? '') === $avatarFile ? 'checked' : ''; ?>>
                        <img src="/theme/assets/img/avatars/<?php echo htmlspecialchars($avatarFile); ?>" alt="<?php echo htmlspecialchars($avatarFile); ?>">
                    </label>
                <?php endforeach; ?>
            </div>
            <small style="color:var(--text-muted)">Click to select avatar</small>
        </div>
        <button type="submit" class="btn" style="padding:10px 20px;font-size:13px;font-weight:600">Save Profile</button>
    </form>
</div>

<div id="tab-password" class="tab-content <?php echo $activeTab === 'password' ? 'active' : ''; ?>">
    <div class="password-section">
        <div class="section-title">Change Password</div>
        <form method="POST" action="/admin/profile/password">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required autocomplete="new-password" minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required autocomplete="new-password" minlength="6">
            </div>
            <button type="submit" class="btn btn-secondary" style="padding:10px 20px;font-size:13px;font-weight:600">Change Password</button>
        </form>
    </div>
</div>

<script>
function switchTab(name) {
    document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    document.querySelectorAll('.tab-content').forEach(function(content) {
        content.classList.remove('active');
    });
    document.querySelector('.tab-btn[onclick="switchTab(\'' + name + '\')"]').classList.add('active');
    document.getElementById('tab-' + name).classList.add('active');
    // Update URL without reload
    history.replaceState(null, '', '?tab=' + name);
}

document.querySelectorAll('.avatar-option').forEach(function(opt) {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.avatar-option').forEach(function(o) { o.classList.remove('selected'); });
        this.classList.add('selected');
        this.querySelector('input[type="radio"]').checked = true;
    });
});
</script>