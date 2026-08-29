<?php
http_response_code(404);
$requested = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title>404 Page Not Found - Planet Hosts</title>
<link rel="stylesheet" href="/errors/error.css">
</head>
<body>
<div class="bg"></div>
<div class="wrap">
<div class="card">
    <a class="logo" href="/">
        <img src="/theme/assets/img/logo.png" alt="Planet Hosts logo" width="44" height="44">
        <span class="logo-text"><h1>PLANET-<span>HOSTS</span></h1><p>Hosting Panel</p></span>
    </a>
    <div class="code">404</div>
    <div class="name">Page Not Found</div>
    <p class="desc">The page you're looking for doesn't exist, was moved, or is no longer available. Check the address for typos or head back to the homepage.</p>
    <?php if ($requested !== '' && $requested !== '/'): ?>
    <div class="detail"><strong>Requested URL:</strong> <?php echo htmlspecialchars($requested, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <div class="actions">
        <a class="btn primary" href="/">Return to Homepage</a>
        <a class="btn ghost" href="/support">Contact Support</a>
    </div>
    <div class="meta">
        <span class="status-dot dot-error"></span>Status: 404 Not Found<br>
        Need help? <a href="mailto:support@planet-hosts.com">support@planet-hosts.com</a>
    </div>
</div>
</div>
</body>
</html>
