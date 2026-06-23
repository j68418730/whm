<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Webmail - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
body{display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#000;font-family:Inter,sans-serif}
.bg{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.9),rgba(2,8,23,.97)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.login-wrap{width:100%;max-width:400px;padding:20px;position:relative}
.login-card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:36px 28px;text-align:center}
.logo img{width:48px;height:48px;border-radius:12px;margin-bottom:8px}
h1{color:#fff;font-size:22px;margin:0 0 4px}h1 span{color:#008cff}
p{color:#64748b;font-size:13px;margin:0 0 20px}
.btn{display:block;width:100%;padding:14px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;text-decoration:none;cursor:pointer;transition:.3s;margin-bottom:10px}
.btn:hover{transform:translateY(-1px);box-shadow:0 0 20px rgba(0,140,255,.3)}
.btn-outline{background:transparent;border:1px solid rgba(255,255,255,.1);color:#94a3b8}
.links{margin-top:16px;font-size:12px;color:#475569}
.links a{color:#64748b;text-decoration:none}.links a:hover{color:var(--accent)}
.apps{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
.app{border:1px solid rgba(255,255,255,.06);border-radius:10px;padding:14px;text-decoration:none;color:#fff;transition:.15s}
.app:hover{background:rgba(0,191,255,.05);border-color:rgba(0,191,255,.2)}
.app .icon{font-size:28px;margin-bottom:4px}
.app .name{font-size:12px;color:#94a3b8}
</style></head>
<body>
<div class="bg"></div>
<div class="login-wrap">
<div class="login-card">
<div class="logo"><img src="/theme/assets/img/logo.png" alt=""><h1>PLANET-<span>HOSTS</span></h1></div>
<p>Access your email from anywhere. Choose a webmail client below or configure your desktop/mobile email app.</p>
<div class="apps">
<a href="/snappymail/" class="app"><div class="icon">📧</div><div class="name">SnappyMail</div></a>
</div>
<p style="color:#64748b;font-size:12px;margin-top:12px">SnappyMail is installed and ready. Log in with your full email address and email password.</p>
<div class="links">
<strong style="color:#94a3b8">IMAP Settings:</strong><br>
Server: mail.yourdomain.com &middot; Port: 143 (TLS) or 993 (SSL)<br>
SMTP: mail.yourdomain.com &middot; Port: 587 (TLS) or 465 (SSL)
</div>
</div>
</div>
</body>
</html>
