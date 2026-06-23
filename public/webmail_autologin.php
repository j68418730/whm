<?php
session_start();
$user = $_SESSION['user'] ?? null;
$email = '';
$password = '';
if (is_object($user)) {
    $email = $user->email ?? '';
} elseif (is_array($user)) {
    $email = $user['email'] ?? '';
}
if (!empty($_SESSION['webmail_email'])) $email = $_SESSION['webmail_email'];
if (!empty($_SESSION['webmail_password'])) $password = $_SESSION['webmail_password'];

// Simple auto-login form that posts to Roundcube
?><!DOCTYPE html>
<html><head><title>Redirecting to Webmail...</title></head>
<body>
<form id="rcForm" method="POST" action="/roundcube/">
<input type="hidden" name="_task" value="login">
<input type="hidden" name="_action" value="login">
<input type="hidden" name="_user" value="<?php echo htmlspecialchars($email); ?>">
<input type="hidden" name="_pass" value="<?php echo htmlspecialchars($password); ?>">
</form>
<script>
<?php if ($email && $password): ?>
document.getElementById('rcForm').submit();
<?php else: ?>
window.location.href = '/roundcube/';
<?php endif; ?>
</script>
<p>Redirecting to webmail...</p>
</body></html>
