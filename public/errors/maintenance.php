<?php
http_response_code(503);
header('Retry-After: 3600');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title>Maintenance - Planet Hosts</title>
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
    <div class="code" style="font-size:54px">MAINTENANCE</div>
    <div class="name">We'll Be Right Back</div>
    <p class="desc">We're performing scheduled maintenance to improve our services. This should only take a short time. Please check back shortly.</p>
    <div class="actions">
        <a class="btn ghost" href="javascript:location.reload()">Check Again</a>
    </div>
    <div class="meta">
        <span class="status-dot dot-info"></span>Scheduled maintenance in progress<br>
        Questions? <a href="mailto:support@planet-hosts.com">support@planet-hosts.com</a>
    </div>
</div>
</div>
</body>
</html>
