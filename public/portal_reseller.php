<?php
session_start();
$error = '';
if ($_POST) {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? OR username = ?");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    if ($user && password_verify($pass, $user->password_hash)) {
        $_SESSION['user'] = ['id' => $user->id, 'email' => $user->email, 'name' => $user->username ?? 'Admin'];
        $_SESSION['is_admin'] = true;
        header('Location: /reseller');
        exit;
    }
    $error = 'Invalid credentials';
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Reseller Login - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
body{display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#000;font-family:Inter,sans-serif}
.bg{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.9),rgba(2,8,23,.97)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.login-wrap{width:100%;max-width:400px;padding:20px;position:relative}
.login-card{background:rgba(8,16,28,.95);border:1px solid rgba(139,92,246,.12);border-radius:16px;padding:36px 28px;text-align:center}
.logo img{width:52px;height:52px;border-radius:12px;margin-bottom:8px}
h1{color:#fff;font-size:22px;margin:0 0 4px}h1 span{color:#8b5cf6}
p{color:#64748b;font-size:13px;margin:0 0 20px}
.form-group{margin-bottom:16px;text-align:left}
.form-group label{display:block;margin-bottom:6px;font-size:13px;color:#94a3b8;font-weight:600}
.form-group input{width:100%;padding:12px 14px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff;font-size:14px;outline:none;box-sizing:border-box}
.form-group input:focus{border-color:#8b5cf6}
.btn{display:block;width:100%;padding:14px;background:linear-gradient(135deg,#8b5cf6,#a78bfa);color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;transition:.3s}
.btn:hover{transform:translateY(-1px);box-shadow:0 0 20px rgba(139,92,246,.3)}
.alert{padding:10px;border-radius:8px;margin-bottom:16px;font-size:13px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#f87171}
.links{margin-top:16px;font-size:13px;color:#475569}
.links a{color:#64748b;text-decoration:none}.links a:hover{color:#8b5cf6}
</style></head>
<body>
<div class="bg"></div>
<div class="login-wrap">
<div class="login-card">
<div class="logo"><img src="/theme/assets/img/logo.png" alt=""><h1>Reseller <span>Center</span></h1></div>
<p>Sign in to manage your clients and billing</p>
<?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="POST">
<div class="form-group"><label>Email</label><input name="email" required placeholder="admin@planet-hosts.com"></div>
<div class="form-group"><label>Password</label><input name="password" type="password" required></div>
<button type="submit" class="btn">Sign In</button>
</form>
<div class="links"><a href="http://planet-hosts.com:2082/">User Portal</a> &middot; <a href="http://planet-hosts.com:2087/">Admin Login</a></div>
</div>
</div>
</body>
</html>

