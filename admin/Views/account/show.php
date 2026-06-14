<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Account Details - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
body{font-family:Inter,sans-serif;background:#000;color:#fff;margin:0;padding:40px}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.card{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;max-width:800px;margin:auto;position:relative;z-index:1}
h1{color:#0A84FF;margin-bottom:24px}
.detail{display:grid;grid-template-columns:160px 1fr;gap:8px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.detail .label{color:#94a3b8;font-weight:600;font-size:14px}
.detail .value{color:#fff}
.badge{display:inline-block;padding:4px 12px;border-radius:6px;font-size:13px;font-weight:600}
.badge.active{background:#1a3a2a;color:#4ade80}
.badge.suspended{background:#3a3a1a;color:#facc15}
.badge.terminated{background:#3a1a1a;color:#f87171}
.actions{display:flex;gap:12px;margin-top:28px}
.btn{padding:12px 24px;border:none;border-radius:10px;font-weight:600;cursor:pointer;text-decoration:none;font-size:14px;transition:.3s;display:inline-block}
.btn.primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn.primary:hover{transform:translateY(-2px)}
.btn.secondary{background:rgba(255,255,255,.06);color:#ccc;border:1px solid rgba(255,255,255,.1)}
.btn.danger{background:rgba(255,50,50,.2);color:#ff6b6b;border:1px solid rgba(255,50,50,.3)}
.alert{padding:14px;border-radius:10px;margin-bottom:20px;font-size:14px}
.alert-success{background:rgba(50,255,50,.08);border:1px solid rgba(50,255,50,.2);color:#4ade80}
@media(max-width:600px){.detail{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="card">
<h1>Account Details</h1>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="detail"><span class="label">Username</span><span class="value"><?php echo htmlspecialchars($account->username, ENT_QUOTES, 'UTF-8'); ?></span></div>
<div class="detail"><span class="label">Email</span><span class="value"><?php echo htmlspecialchars($account->email, ENT_QUOTES, 'UTF-8'); ?></span></div>
<div class="detail"><span class="label">Name</span><span class="value"><?php echo htmlspecialchars(($account->first_name ?? '') . ' ' . ($account->last_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></div>
<div class="detail"><span class="label">Status</span><span class="value"><span class="badge <?php echo $account->status; ?>"><?php echo ucfirst($account->status); ?></span></span></div>
<div class="detail"><span class="label">Package</span><span class="value"><?php echo $package ? htmlspecialchars($package->name, ENT_QUOTES, 'UTF-8') : 'None'; ?></span></div>
<div class="detail"><span class="label">Home Directory</span><span class="value">/home/<?php echo htmlspecialchars($account->username, ENT_QUOTES, 'UTF-8'); ?>/</span></div>
<div class="detail"><span class="label">Created</span><span class="value"><?php echo $account->created_at ?? 'N/A'; ?></span></div>

<hr style="border-color:rgba(255,255,255,.06);margin:20px 0">

<h2 style="font-size:18px;color:#0A84FF;margin-bottom:16px">Password Reset</h2>
<form method="POST" action="/admin/account/password/<?php echo $account->id; ?>" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
<input type="password" name="password" required minlength="8" placeholder="New password" style="padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none;flex:1;min-width:200px">
<button type="submit" class="btn primary" style="padding:12px 20px">Change Password</button>
</form>

<div class="actions">
<a href="/admin/account" class="btn secondary">&larr; Back</a>
<a href="/admin/account/suspend/<?php echo $account->id; ?>" class="btn secondary" onclick="return confirm('Suspend this account?')">Suspend</a>
<a href="/admin/account/unsuspend/<?php echo $account->id; ?>" class="btn secondary">Unsuspend</a>
<a href="/admin/account/terminate/<?php echo $account->id; ?>" class="btn danger" onclick="return confirm('Terminate? This deletes the Linux user and all files.')">Terminate</a>
</div>
</div>
</body>
</html>
