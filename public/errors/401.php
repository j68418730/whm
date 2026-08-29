<?php
http_response_code(401);
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title>401 Unauthorized - Planet Hosts</title>
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
    <div class="code">401</div>
    <div class="name">Authentication Required</div>
    <p class="desc">You need to sign in to view this page. Your session may have expired, or you may have been logged out.</p>
    <div class="actions">
        <a class="btn primary" href="/login">Sign In</a>
        <a class="btn ghost" href="/">Return to Homepage</a>
    </div>
    <div class="meta">
        <span class="status-dot dot-warn"></span>Status: 401 Unauthorized<br>
        Still having trouble? <a href="mailto:support@planet-hosts.com">support@planet-hosts.com</a>
    </div>
</div>
</div>
</body>
</html>
