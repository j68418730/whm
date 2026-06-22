<?php
/**
 * phpMyAdmin Auto-Login for Planet-Hosts
 * Uses phpMyAdmin signon auth system for secure SSO.
 */
session_start();

// Determine DB credentials
$dbUser = 'radiouser';
$dbPass = 'Skylinehosting171';
if (!empty($_SESSION['is_admin'])) {
    $dbUser = 'root';
    $dbPass = 'Skylinehosting171';
} elseif (!empty($_SESSION['db_username'])) {
    $dbUser = $_SESSION['db_username'];
    $dbPass = $_SESSION['db_password'];
}

// Store in session for phpMyAdmin signon to pick up
$_SESSION['PMA_signon_username'] = $dbUser;
$_SESSION['PMA_signon_password'] = $dbPass;
$_SESSION['PMA_signon_server'] = 1;
session_write_close();

// Redirect to phpMyAdmin with signon
header('Location: /phpmyadmin/index.php?route=/&server=1');
exit;
