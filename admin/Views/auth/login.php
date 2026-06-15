<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Planet Hosts</title>
    <link rel="stylesheet" href="/theme/assets/css/style.css">
    <style>
body{display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#000;padding-top:120px}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.grid-overlay{position:fixed;inset:0;background-image:linear-gradient(rgba(0,140,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(0,140,255,.04) 1px,transparent 1px);background-size:80px 80px;z-index:-1;opacity:.35}
        .login-wrap{width:100%;max-width:420px;padding:20px;position:relative;z-index:1}
        .login-card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px 32px;box-shadow:0 0 40px rgba(0,140,255,.08)}
        .login-card h2{text-align:center;margin:0 0 8px;color:#fff;font-size:22px}
        .login-card .subtitle{text-align:center;color:#64748b;font-size:13px;margin-bottom:28px}
        .form-group{margin-bottom:18px}
        .form-group label{display:block;margin-bottom:6px;font-weight:600;font-size:13px;color:#94a3b8}
        .form-group input[type="text"],.form-group input[type="password"]{width:100%;padding:12px 14px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff;font-size:14px;outline:none;box-sizing:border-box;transition:.15s}
        .form-group input:focus{border-color:var(--accent,#008cff);box-shadow:0 0 0 3px rgba(0,140,255,.1)}
        .form-group input::placeholder{color:#475569}
        .form-group input[type="checkbox"]{width:auto;accent-color:#008cff}
        .form-group .remember-wrap{display:flex;align-items:center;gap:8px}
        .form-group .remember-wrap label{margin:0;font-weight:400;cursor:pointer;color:#94a3b8;font-size:13px}
        .btn{width:100%;padding:12px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:15px;font-weight:700;transition:.3s}
        .btn:hover{transform:translateY(-1px);box-shadow:0 0 20px rgba(0,140,255,.3)}
        .alert{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;text-align:center}
        .alert-danger{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#f87171}
        .footer{text-align:center;margin-top:20px;color:#475569;font-size:12px}
        .footer a{color:#64748b;text-decoration:none}
        .footer a:hover{color:var(--accent)}
        .logo{text-align:center;margin-bottom:24px}
        .logo img{width:48px;height:48px;border-radius:12px}
        .logo h1{font-size:20px;margin:8px 0 0;color:#fff}
        .logo h1 span{color:#008cff}
    </style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="grid-overlay"></div>
<header style="position:fixed;top:0;left:0;right:0;z-index:100;border-bottom:1px solid rgba(255,255,255,.06);overflow:hidden">
<div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(2,8,23,.85) 0%,rgba(2,8,23,.7) 50%,rgba(2,8,23,.85) 100%);z-index:0"></div>
<img src="/theme/assets/img/header.png" style="display:block;width:100%;height:auto;max-height:337px;object-fit:contain;object-position:center;opacity:.7" alt="">
<div style="position:absolute;inset:0;z-index:2;display:flex;align-items:center;justify-content:center;padding:0 20px">
<div style="display:flex;align-items:center;gap:28px;max-width:1200px;width:100%;justify-content:center">
<img src="/theme/assets/img/logo.png" style="width:38px;height:38px;border-radius:10px;box-shadow:0 0 20px rgba(0,140,255,.2)" alt="">
<div style="font-size:22px;font-weight:800;color:#fff;letter-spacing:1px">PLANET<span style="color:#008cff">-HOSTS</span></div>
<div style="width:1px;height:24px;background:rgba(255,255,255,.08)"></div>
<a href="/" style="color:#94a3b8;text-decoration:none;font-size:14px;transition:.15s">Home</a>
<a href="/admin/login" style="color:#fff;text-decoration:none;font-size:14px;font-weight:600;padding:6px 16px;background:rgba(0,140,255,.15);border-radius:6px;border:1px solid rgba(0,140,255,.25);transition:.15s">Login</a>
</div>
</div>
</header>
<div class="login-wrap">
    <div class="login-card">
        <div class="logo">
            <img src="/theme/assets/img/logo.png" alt="Planet Hosts">
            <h1>PLANET-<span>HOSTS</span></h1>
        </div>
        <h2>Admin Login</h2>
        <p class="subtitle">Sign in to your control panel</p>
        <?php if(isset($_SESSION['login_error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['login_error']); unset($_SESSION['login_error']); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="/admin/login/post">
            <div class="form-group">
                <label for="email">Username or Email</label>
                <input type="text" id="email" name="email" required placeholder="root@planet-hosts.com">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <div class="remember-wrap">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember">Remember me</label>
                </div>
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>
    </div>
    <div class="footer">
        <a href="/">Planet-Hosts</a> &middot; <a href="#">Terms</a> &middot; <a href="#">Privacy</a>
    </div>
</div>
</body>
</html>
