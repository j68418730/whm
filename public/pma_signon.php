<?php
session_start();
// Redirect to the proper auto-login which handles scoped access
header('Location: /pma_autologin.php');
exit;
