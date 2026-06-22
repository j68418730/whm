<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo htmlspecialchars($title ?? 'Planet Hosts'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
<?php $te = \Core\ThemeEngine::getInstance(); echo $te->getThemeCss('public'); ?>
:root{--bs-body-font-family:var(--font-body,'Inter',sans-serif);--bs-body-bg:var(--bg,#02050e);--bs-body-color:var(--text,#e0e0e0)}
.topbar{padding:18px 24px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border,rgba(0,191,255,.08))}
.topbar .logo{font-size:20px;font-weight:800;text-decoration:none;color:var(--text,#e0e0e0)}
.topbar .logo span{color:var(--primary,#008cff)}
.content{max-width:1200px;margin:0 auto;padding:40px 24px}
.card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:12px;padding:24px;margin-bottom:20px}
.btn{font-weight:600;font-size:14px;border-radius:8px;padding:10px 24px}
.btn-primary{background:var(--primary,#008cff);border-color:var(--primary,#008cff)}
.btn-primary:hover{opacity:.9;transform:translateY(-1px)}
@media(max-width:768px){.content{padding:16px}}
</style>
</head>
<body>
<div class="topbar">
<a href="/" class="logo">PLANET <span>HOSTS</span></a>
</div>
<div class="content">
<?php echo $content ?? ''; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
