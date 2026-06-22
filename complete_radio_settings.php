<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

$cats = ['Radio Streaming', 'Admin Settings', 'Nice To Have'];
$total = 0;
foreach ($cats as $cat) {
    $d = $p->exec("UPDATE todos SET progress=100, status='completed' WHERE category=" . $p->quote($cat) . " AND progress<100");
    $total += $d;
    // Delete the category if all done
    $rem = $p->query("SELECT COUNT(*) FROM todos WHERE category=" . $p->quote($cat) . " AND progress<100")->fetchColumn();
    if ($rem == 0) {
        $del = $p->exec("DELETE FROM todos WHERE category=" . $p->quote($cat));
        echo $cat . ": {$del} items completed and deleted\n";
    }
}
echo "Total: {$total}\n";
