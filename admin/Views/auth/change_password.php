<div class="card" style="max-width:400px;margin:40px auto;text-align:center">
<h2 style="color:var(--accent);margin-bottom:8px">🔒 Password Change Required</h2>
<p style="color:#64748b;font-size:13px;margin-bottom:20px">For security, you must change your password before continuing.</p>

<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<form method="POST" action="/admin/change-password">
<div class="form-group"><label>New Password</label><input name="password" type="password" minlength="6" required style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;outline:none"></div>
<div class="form-group"><label>Confirm Password</label><input name="confirm" type="password" minlength="6" required style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;outline:none"></div>
<button type="submit" class="btn primary" style="width:100%">Change Password & Continue</button>
</form>
</div>
