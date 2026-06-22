<?php
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$q = $pdo->query("SELECT category, COUNT(*) as cnt, SUM(IF(progress=0,1,0)) as zero FROM todos GROUP BY category");
foreach ($q as $r) {
    echo $r['category'] . " | total:" . $r['cnt'] . " | zero:" . $r['zero'] . "\n";
}
