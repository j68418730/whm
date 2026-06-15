<?php
// Planet-Hosts License Keygen v2 - standalone edition
$dir = __DIR__ . '/keys';
@mkdir($dir, 0700, true);

// Config
$privateKeyFile = $dir . '/license_private.pem';
$publicKeyFile = $dir . '/license_public.pem';

// Check if keys exist, generate if not
if (!is_file($privateKeyFile)) {
    echo "Generating RSA 2048 key pair...\n";
    $kp = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    if (!$kp) { die("Key generation failed\n"); }
    openssl_pkey_export($kp, $priv);
    file_put_contents($privateKeyFile, $priv);
    chmod($privateKeyFile, 0600);
    $det = openssl_pkey_get_details($kp);
    file_put_contents($publicKeyFile, $det['key']);
    echo "Keys saved to $dir/\n";
}

// CLI args
$licensee = $argv[1] ?? 'Planet-Hosts';
$licenseId = $argv[2] ?? 'LICS-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
$expiry = $argv[3] ?? 'never';

$payload = json_encode([
    'license_id' => $licenseId,
    'licensee' => $licensee,
    'issued' => date('Y-m-d'),
    'expiry' => $expiry,
    'product' => 'Planet-Hosts WHM Panel',
    'version' => '1.0.0',
], JSON_PRETTY_PRINT);

$privKey = file_get_contents($privateKeyFile);
openssl_sign($payload, $signature, $privKey, OPENSSL_ALGO_SHA256);
$signatureB64 = base64_encode($signature);

$license = "-----BEGIN PLANET HOSTS LICENSE-----\n";
$license .= chunk_split($signatureB64, 64, "\n");
$license .= "-----BEGIN LICENSE DATA-----\n";
$license .= $payload . "\n";
$license .= "-----END LICENSE DATA-----\n";
$license .= "-----END PLANET HOSTS LICENSE-----\n";

echo $license;
