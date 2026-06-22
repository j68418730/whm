<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$q = $p->query("SELECT category, COUNT(*) as cnt FROM todos WHERE progress>0 AND progress<100 GROUP BY category HAVING cnt BETWEEN 1 AND 7 ORDER BY cnt");
foreach ($q as $r) {
    echo $r['category'] . " | " . $r['cnt'] . " active\n";
}
