<?php
http_response_code(503);
$retryAfter = isset($retryAfter) ? (int) $retryAfter : 0;
if ($retryAfter > 0) {
    header('Retry-After: ' . $retryAfter);
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title>503 Service Unavailable - Planet Hosts</title>
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
    <div class="code">503</div>
    <div class="name">Service Temporarily Unavailable</div>
    <p class="desc">The service is temporarily overloaded or undergoing maintenance. Please check back in a few minutes.</p>
    <div class="actions">
        <a class="btn primary" href="javascript:location.reload()">Try Again</a>
        <a class="btn ghost" href="/">Return to Homepage</a>
    </div>
    <div class="meta">
        <span class="status-dot dot-error"></span>Status: 503 Service Unavailable<br>
        More details: <a href="mailto:support@planet-hosts.com">support@planet-hosts.com</a>
    </div>
</div>
</div>
</body>
</html>
