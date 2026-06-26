<?php
session_start();
$host = $_SERVER["HTTP_HOST"] ?? "planet-hosts.com";
$error = $_GET['error'] ?? '';
$loggedOut = $_GET['loggedout'] ?? '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=radiohosting;charset=utf8mb4", "radiouser", "Skylinehosting171");
        $stmt = $pdo->prepare("SELECT * FROM hosting_users WHERE (email = ? OR username = ?) AND status = 'active' LIMIT 1");
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if ($user && password_verify($password, $user->password_hash)) {
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            header('Location: /user/');
            exit;
        }
        header('Location: /user_login.php?error=1');
        exit;
    } catch (Exception $e) {
        header('Location: /user_login.php?error=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Client Login - Planet Hosts</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
html,body{height:100%}
body{background:#020817;color:#fff;font-family:'Inter',sans-serif;display:flex;align-items:center;justify-content:center;position:relative}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;background-position:center;z-index:-2}
.grid-overlay{position:fixed;inset:0;background-image:linear-gradient(rgba(0,140,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(0,140,255,.04) 1px,transparent 1px);background-size:80px 80px;z-index:-1;opacity:.35}
.login-box{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;width:400px;max-width:94vw;position:relative;z-index:1;box-shadow:0 20px 60px rgba(0,0,0,.5)}
.logo{text-align:center;margin-bottom:28px}
.logo img{width:60px;height:60px;border-radius:12px;margin-bottom:10px}
.logo-text{font-size:1.4rem;font-weight:700;letter-spacing:1px}
.logo-text span{color:#0A84FF}
.logo-sub{color:#64748b;font-size:.7rem;letter-spacing:3px;text-transform:uppercase;margin-top:2px}
h2{text-align:center;font-size:18px;font-weight:600;margin-bottom:6px;color:#e0e0e0}
.sub{text-align:center;color:#64748b;font-size:13px;margin-bottom:24px}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:12px;color:#94a3b8;margin-bottom:4px;font-weight:500}
.form-group input{width:100%;padding:12px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);color:#fff;font-size:13px;outline:none;transition:.2s;font-family:'Inter',sans-serif}
.form-group input:focus{border-color:#0A84FF;background:rgba(0,140,255,.04)}
.form-group input::placeholder{color:#4a5568}
.btn{width:100%;padding:12px;border-radius:8px;border:none;font-size:14px;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;transition:.2s}
.btn-primary{background:linear-gradient(135deg,#0A84FF,#00E5FF);color:#fff}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 0 20px rgba(0,191,255,.3)}
.error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#f87171;padding:10px 14px;border-radius:8px;font-size:12px;margin-bottom:16px;display:none}
.error.show{display:block}
.success{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);color:#4ade80;padding:10px 14px;border-radius:8px;font-size:12px;margin-bottom:16px;display:none}
.success.show{display:block}
.links{text-align:center;margin-top:16px;font-size:12px}
.links a{color:#64748b;text-decoration:none}
.links a:hover{color:#0A84FF}
.footer{text-align:center;margin-top:24px;font-size:11px;color:#3a3f4b}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="grid-overlay"></div>
<div class="login-box">
<div class="logo">
<img src="/theme/assets/img/logo.png" alt="Planet Hosts">
<div class="logo-text">PLANET-<span>HOSTS</span></div>
<div class="logo-sub">Client Login</div>
</div>
<h2>Welcome Back</h2>
<p class="sub">Sign in to your hosting account</p>
<?php if ($error): ?>
<div class="error show">Invalid email or password. Please try again.</div>
<?php endif; ?>
<?php if ($loggedOut): ?>
<div class="success show">You have been logged out successfully.</div>
<?php endif; ?>
<form method="POST" action="/user_login.php">
<div class="form-group"><label>Email or Username</label><input type="text" name="email" placeholder="email@example.com or username" required autofocus></div>
<div class="form-group"><label>Password</label><input type="password" name="password" placeholder="Enter your password" required></div>
<button type="submit" class="btn btn-primary">Sign In</button>
</form>
<div class="links"><a href="https://<?php echo htmlspecialchars($host); ?>:2087/">Admin Login</a></div>
<div class="footer">&copy; 2026 Planet-Hosts</div>
</div>
</body>
</html>
