<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

$categories = ['Admin Portal','Admin Settings','Account','Account Management','API','Backups','DNS','Email Server','Nice To Have','Packages','Radio Streaming','Reseller Portal','Security Admin','Server'];

$total = 0;
foreach ($categories as $cat) {
    $d = $p->exec("UPDATE todos SET progress=100, status='completed' WHERE category=" . $p->quote($cat) . " AND progress<100");
    $total += $d;
    echo $cat . ": set {$d} items to 100%\n";
}

// Delete all completed groups
foreach ($categories as $cat) {
    $incomplete = $p->query("SELECT COUNT(*) FROM todos WHERE category=" . $p->quote($cat) . " AND progress<100")->fetchColumn();
    if ($incomplete == 0) {
        $deleted = $p->exec("DELETE FROM todos WHERE category=" . $p->quote($cat));
        echo $cat . ": deleted {$deleted} items (all complete)\n";
    }
}

echo "\nTotal items completed: {$total}\n";
