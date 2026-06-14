<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
body{font-family:Inter,sans-serif;background:#000;color:#fff;margin:0;padding:40px}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.card{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;max-width:700px;margin:auto;position:relative;z-index:1}
h1{color:#0A84FF;margin-bottom:30px}
.form-group{margin-bottom:20px}
label{display:block;margin-bottom:6px;color:#94a3b8;font-size:14px;font-weight:600}
input,select{width:100%;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:15px;outline:none;box-sizing:border-box}
input:focus,select:focus{border-color:#0A84FF}
.row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.btn{padding:14px 28px;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:15px;transition:.3s;text-decoration:none;display:inline-block}
.btn.primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn.primary:hover{transform:translateY(-2px)}
.btn.secondary{background:rgba(255,255,255,.06);color:#ccc;border:1px solid rgba(255,255,255,.1)}
.alert{padding:14px;border-radius:10px;margin-bottom:20px;font-size:14px}
.alert-error{background:rgba(255,50,50,.12);border:1px solid rgba(255,50,50,.3);color:#ff6b6b}
@media(max-width:600px){.row{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="card">
<h1>Create Account</h1>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<form method="POST" action="/admin/account/store">
<div class="row">
<div class="form-group"><label>Username</label><input type="text" name="username" required placeholder="e.g. johndoe"></div>
<div class="form-group"><label>Email</label><input type="email" name="email" required placeholder="user@example.com"></div>
</div>
<div class="row">
<div class="form-group"><label>Password</label><input type="password" name="password" required minlength="8"></div>
<div class="form-group"><label>Package</label><select name="package_id">
<option value="">No package</option>
<?php if (isset($packages)): foreach ($packages as $p): ?>
<option value="<?php echo $p->id; ?>"><?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></option>
<?php endforeach; endif; ?>
</select></div>
</div>
<div class="row">
<div class="form-group"><label>First Name</label><input type="text" name="first_name"></div>
<div class="form-group"><label>Last Name</label><input type="text" name="last_name"></div>
</div>
<div style="display:flex;gap:12px;margin-top:24px">
<button type="submit" class="btn primary">Create Account</button>
<a href="/admin/account" class="btn secondary">Cancel</a>
</div>
</form>
</div>
</body>
</html>
