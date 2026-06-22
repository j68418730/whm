<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$tables = ['billing_orders', 'billing_payments', 'invoices', 'tickets', 'chat_visitors', 'radio_streams', 'hosting_users'];
foreach ($tables as $t) {
    $q = $p->query("SHOW TABLES LIKE '$t'");
    echo $t . ": " . ($q->rowCount() > 0 ? 'EXISTS' : 'MISSING') . "\n";
}
