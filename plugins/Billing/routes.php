<?php

if (!isset($router)) {
    $router = \Core\Application::getInstance()->get('router');
}

// Billing plugin routes (supplemental to core billing)
