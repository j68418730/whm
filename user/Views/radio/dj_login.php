<style>
body{background:#02050e;color:#fff;font-family:'Inter',sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0}
.bg{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.9),rgba(2,8,23,.97)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:36px 28px;max-width:400px;width:92%;text-align:center;position:relative;z-index:1}
h1{font-size:22px;margin-bottom:4px}h1 span{color:#008cff}
p{color:#64748b;font-size:13px;margin-bottom:20px}
input{width:100%;padding:11px 14px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff;font-size:14px;outline:none;box-sizing:border-box;margin-bottom:14px}
input:focus{border-color:#008cff}
.btn{width:100%;padding:12px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer}
.error{padding:10px;border-radius:8px;margin-bottom:14px;font-size:13px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#f87171}
</style>
<body><div class="bg"></div><div class="card">
<div style="font-size:38px;margin-bottom:8px">🎤</div>
<h1>Planet <span>DJ</span></h1>
<p>Sign in with your DJ credentials</p>
<?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="POST"><input name="username" placeholder="DJ Username" required autofocus>
<input name="password" type="password" placeholder="Password" required>
<button type="submit" class="btn">Sign In</button></form>
<p style="margin-top:14px;font-size:11px;color:#475569">Powered by Planet-Hosts Radio</p>
</div></body>
