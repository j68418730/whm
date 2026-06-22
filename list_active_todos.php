<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$q = $p->query("SELECT id, category, title, progress FROM todos WHERE progress>0 AND progress<100 ORDER BY category, progress DESC");
$currentCat = '';
foreach ($q as $r) {
    if ($r['category'] !== $currentCat) {
        echo "\n=== " . $r['category'] . " ===\n";
        $currentCat = $r['category'];
    }
    echo "  [" . $r['progress'] . "%] " . $r['title'] . " (id:" . $r['id'] . ")\n";
}
