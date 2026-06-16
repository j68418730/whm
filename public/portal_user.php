<?php
// User Portal Login - handles both GET (show form) and POST (login)
session_start();
$error = '';
if ($_POST) {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM hosting_users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    if ($user && password_verify($pass, $user->password_hash)) {
        $_SESSION['user'] = ['id' => $user->id, 'email' => $user->email, 'username' => $user->username];
        $_SESSION['is_admin'] = false;
        header('Location: /user');
        exit;
    }
    $error = 'Invalid email or password';
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>User Login - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
body{display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#000;font-family:Inter,sans-serif}
.bg{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.9),rgba(2,8,23,.97)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.login-wrap{width:100%;max-width:400px;padding:20px;position:relative}
.login-card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:36px 28px;text-align:center}
.logo img{width:48px;height:48px;border-radius:12px;margin-bottom:8px}
h1{color:#fff;font-size:22px;margin:0 0 4px}h1 span{color:#008cff}
p{color:#64748b;font-size:13px;margin:0 0 20px}
.form-group{margin-bottom:16px;text-align:left}
.form-group label{display:block;margin-bottom:6px;font-size:13px;color:#94a3b8;font-weight:600}
.form-group input{width:100%;padding:12px 14px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff;font-size:14px;outline:none;box-sizing:border-box}
.form-group input:focus{border-color:#008cff}
.btn{display:block;width:100%;padding:14px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;transition:.3s}
.btn:hover{transform:translateY(-1px);box-shadow:0 0 20px rgba(0,140,255,.3)}
.alert{padding:10px;border-radius:8px;margin-bottom:16px;font-size:13px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#f87171}
.links{margin-top:16px;font-size:13px;color:#475569}
.links a{color:#64748b;text-decoration:none}.links a:hover{color:#008cff}
</style></head>
<body>
<div class="bg"></div>
<div class="login-wrap">
<div class="login-card">
<div class="logo"><img src="/theme/assets/img/logo.png" alt=""><h1>PLANET-<span>HOSTS</span></h1></div>
<p>Sign in to manage your hosting account</p>
<?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="POST">
<div class="form-group"><label>Username or Email</label><input name="email" required placeholder="user@example.com"></div>
<div class="form-group"><label>Password</label><input name="password" type="password" required></div>
<button type="submit" class="btn">Sign In</button>
</form>
<div class="links"><a href="http://45.61.59.55:2087/">Admin Login</a> &middot; <a href="http://45.61.59.55:2086/">Reseller Center</a></div>
</div>
</div>
</body>
</html>
