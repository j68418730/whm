<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

$items = [
    ['Admin Portal', 'Service Status', 60, 'in_progress'],
    ['Admin Settings', 'Theme Settings', 70, 'in_progress'],
    ['Admin Settings', 'Branding', 60, 'in_progress'],
    ['Admin Settings', 'General Settings', 30, 'in_progress'],
    ['Admin Settings', 'Company Settings', 10, 'in_progress'],
    ['Account', 'Profile', 30, 'in_progress'],
    ['Account Management', 'Modify Account', 80, 'in_progress'],
    ['API', 'API Tokens', 10, 'in_progress'],
    ['Backups', 'Backup Configuration', 50, 'in_progress'],
    ['Backups', 'Backup Scheduling', 30, 'in_progress'],
    ['Backups', 'Restore Manager', 30, 'in_progress'],
    ['DNS', 'DNS Zones', 90, 'in_progress'],
    ['Email Server', 'Mail Server', 10, 'in_progress'],
    ['Nice To Have', 'Mobile Responsive', 80, 'in_progress'],
    ['Nice To Have', 'White Label Support', 10, 'in_progress'],
    ['Packages', 'Edit Package', 90, 'in_progress'],
    ['Packages', 'Resource Limits', 80, 'in_progress'],
    ['Radio Streaming', 'Create Station', 30, 'in_progress'],
    ['Radio Streaming', 'Start Stream', 30, 'in_progress'],
    ['Radio Streaming', 'Stop Stream', 30, 'in_progress'],
    ['Radio Streaming', 'AutoDJ Controls', 30, 'in_progress'],
    ['Radio Streaming', 'Create DJ', 30, 'in_progress'],
    ['Radio Streaming', 'Remove DJ', 30, 'in_progress'],
    ['Radio Streaming', 'File Upload', 10, 'in_progress'],
    ['Reseller Portal', 'Branding', 10, 'in_progress'],
    ['Security Admin', 'Firewall', 10, 'in_progress'],
    ['Server', 'Apache Manager', 90, 'in_progress'],
    ['Server', 'MySQL Manager', 80, 'in_progress'],
    ['Server', 'PHP Versions', 70, 'in_progress'],
];

$count = 0;
foreach ($items as $item) {
    $p->exec("INSERT INTO todos (title, category, progress, status) VALUES (" . $p->quote($item[1]) . ", " . $p->quote($item[0]) . ", " . $item[2] . ", " . $p->quote($item[3]) . ")");
    $count++;
}
echo "Restored {$count} todo items\n";
