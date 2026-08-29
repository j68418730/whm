<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(500);
}
$errMsg = isset($error) ? $error : '';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title>500 Internal Server Error - Planet Hosts</title>
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
    <div class="code">500</div>
    <div class="name">Unexpected Server Error</div>
    <p class="desc">Something went wrong on our end while processing your request. Our team has been notified, but please try again in a moment.</p>
    <?php if ($errMsg !== '' && (($_SERVER['HTTP_HOST'] ?? '') === 'localhost' || (($_SERVER['REMOTE_ADDR'] ?? '') === '127.0.0.1'))): ?>
    <div class="detail"><strong>Error:</strong> <?php echo htmlspecialchars($errMsg, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <div class="actions">
        <a class="btn primary" href="javascript:location.reload()">Try Again</a>
        <a class="btn ghost" href="/">Return to Homepage</a>
    </div>
    <div class="meta">
        <span class="status-dot dot-error"></span>Status: 500 Internal Server Error<br>
        If this keeps happening, <a href="mailto:support@planet-hosts.com">support@planet-hosts.com</a>
    </div>
</div>
</div>
</body>
</html>
