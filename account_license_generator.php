<?php
/**
 * License Key Generator for Client Accounts
 * Generates and stores license key when account is created
 */

function generateAccountLicenseKey($pdo, $accountId, $packageId) {
    // Get package info
    $pkg = $pdo->prepare("SELECT name, product_id FROM hosting_packages WHERE id = ?");
    $pkg->execute([$packageId]);
    $package = $pkg->fetch(PDO::FETCH_ASSOC);
    
    if (!$package) return false;
    
    // Get product license key
    $prod = $pdo->prepare("SELECT license_key FROM billing_products WHERE id = ?");
    $prod->execute([$package['product_id']]);
    $productKey = $prod->fetchColumn();
    
    if (!$productKey) return false;
    
    // Generate unique account license key
    $accountKey = 'ACC-' . bin2hex(random_bytes(16));
    
    // Store in account license table
    $stmt = $pdo->prepare("
        INSERT INTO account_licenses (account_id, package_id, product_key, account_key, status, created_at)
        VALUES (?, ?, ?, ?, 'active', NOW())
    ");
    $stmt->execute([$accountId, $package['id'], $productKey, $accountKey]);
    
    return [
        'account_key' => $accountKey,
        'product_key' => $productKey
    ];
}

function validateLicenseWithPlanetHosts($licenseKey, $serverIp, $domain) {
    $ch = curl_init('https://license.planet-hosts.com/api/validate');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'license_key' => $licenseKey,
            'domain' => $domain,
            'ip' => $serverIp,
            'machine_id' => getMachineId(),
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        return $data['valid'] ?? false;
    }
    
    return false;
}

function getMachineId() {
    // Try to get unique machine ID
    $mac = exec('cat /sys/class/net/eth0/address 2>/dev/null') ?: exec('cat /sys/class/net/ens*/address 2>/dev/null') ?: '';
    return $mac ? md5($mac) : gethostname();
}