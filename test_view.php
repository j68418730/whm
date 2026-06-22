<?php
define('BASE_PATH', '/var/www/radiohosting');
require BASE_PATH . '/core/View.php';
$v = new Core\View('Plugins.Radio.Views.admin.radio_dashboard.index');
$r = new ReflectionClass($v);
$p = $r->getProperty('viewPath');
$p->setAccessible(true);
$path = $p->getValue($v) . '.php';
echo "PATH: $path\n";
echo "EXISTS: " . (is_file($path) ? 'YES' : 'NO') . "\n";
if (is_file($path)) {
    // Quick render test (without full app context)
    $content = file_get_contents($path);
    echo "CONTENT: " . substr($content, 0, 50) . "...\n";
}
