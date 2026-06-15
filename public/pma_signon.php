<!DOCTYPE html>
<html><head><title>Auto-Login phpMyAdmin</title></head>
<body>
<form id="pmaForm" method="POST" action="/phpmyadmin/index.php?route=/">
<input type="hidden" name="pma_username" value="root">
<input type="hidden" name="pma_password" value="Skylinehosting171">
<input type="hidden" name="server" value="1">
</form>
<script>
var xhr = new XMLHttpRequest();
var fd = new FormData(document.getElementById('pmaForm'));
xhr.open('POST', '/phpmyadmin/index.php?route=/', true);
xhr.withCredentials = true;
xhr.onload = function() {
    window.location.href = '/phpmyadmin/index.php?route=/';
};
xhr.onerror = function() {
    // Fallback: direct submit
    document.getElementById('pmaForm').submit();
};
xhr.send(fd);
setTimeout(function() { document.getElementById('pmaForm').submit(); }, 3000);
</script>
<p>Redirecting to phpMyAdmin...</p>
</body>
</html>
