<div class="card"><h3 style="color:var(--accent)">Profile</h3>
<?php if ($hosting): ?>
<p style="color:var(--text-secondary);margin-top:8px"><strong>Username:</strong> <?php echo htmlspecialchars($hosting->username); ?><br>
<strong>Email:</strong> <?php echo htmlspecialchars($hosting->email); ?><br>
<strong>Domain:</strong> <?php echo htmlspecialchars($hosting->domain ?? '-'); ?><br>
<strong>Package:</strong> <?php echo $package ? htmlspecialchars($package->name) : 'None'; ?></p>
<?php endif; ?>
<hr style="border-color:rgba(255,255,255,.06);margin:20px 0">
<h4 style="color:var(--text-secondary);margin-bottom:12px">Change Password</h4>
<form method="POST" action="/user/password"><div class="form-group"><input type="password" name="password" required minlength="8" placeholder="New password"></div>
<button type="submit" class="btn primary">Update Password</button></form>
</div>
