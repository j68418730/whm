<?php
/**
 * License Key Generator for Planet Hosts Packages
 * Generates unique license keys for packages
 */

$pdo = new PDO("mysql:host=127.0.0.1;dbname=radiohosting", "root", "Skylinehosting171");

// Get packages that need license keys
$packages = $pdo->query("SELECT id, name FROM hosting_packages WHERE is_active = 1 AND (product_id IS NULL OR product_id = 0) ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);

foreach ($packages as $pkg) {
    // Generate license key
    $prefix = strtoupper(str_replace(' ', '', $pkg['name']));
    $prefix = preg_replace('/[^A-Z0-9]/', '', $prefix);
    if (strlen($prefix) > 10) $prefix = substr($prefix, 0, 10);
    $key = $prefix . '-' . bin2hex(random_bytes(16));
    
    // Update billing_products with license key
    $stmt = $pdo->prepare("UPDATE billing_products SET license_key = ? WHERE package_id = ?");
    $stmt->execute([$key, $pkg['id']]);
    
    echo "Package: {$pkg['name']} - Key: $key\n";
}

echo "Done\n";