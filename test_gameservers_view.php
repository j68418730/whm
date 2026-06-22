<?php
define('BASE_PATH', '/var/www/radiohosting');
require BASE_PATH . '/core/View.php';
$v = new Core\View('Plugins.GameServers.Views.admin.gameservers.index');
$r = new ReflectionClass($v);
$p = $r->getProperty('viewPath');
$p->setAccessible(true);
$path = $p->getValue($v) . '.php';
echo "Path: $path\n";
echo "Exists: " . (is_file($path) ? 'YES' : 'NO') . "\n";
$v2 = new Core\View('admin.gameservers.index');
$p2 = (new ReflectionClass($v2))->getProperty('viewPath');
$p2->setAccessible(true);
$path2 = $p2->getValue($v2) . '.php';
echo "Old Path: $path2\n";
echo "Old Exists: " . (is_file($path2) ? 'YES' : 'NO') . "\n";
