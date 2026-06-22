<?php
/**
 * Migration: Create gateways table
 * Run: php database/create_gateways_table.php
 */

define('BASE_PATH', realpath(__DIR__ . '/..'));
require BASE_PATH . '/core/helpers.php';

$config = require __DIR__ . '/../config/database.php';

$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
$pdo = new PDO($dsn, $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec("CREATE TABLE IF NOT EXISTS `gateways` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `display_name` VARCHAR(255) NOT NULL,
    `enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `config` JSON DEFAULT NULL,
    `test_mode` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `gateway_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Insert default gateways if table was just created
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM `gateways`");
$row = $stmt->fetch(PDO::FETCH_OBJ);

if ($row && (int)$row->cnt === 0) {
    $defaults = [
        ['name' => 'paypal',         'display_name' => 'PayPal',           'enabled' => 0, 'sort_order' => 1, 'config' => json_encode(['client_id' => '', 'secret' => '']), 'test_mode' => 1],
        ['name' => 'stripe',         'display_name' => 'Stripe',           'enabled' => 0, 'sort_order' => 2, 'config' => json_encode(['publishable_key' => '', 'secret_key' => '']), 'test_mode' => 1],
        ['name' => 'square',         'display_name' => 'Square',           'enabled' => 0, 'sort_order' => 3, 'config' => json_encode(['application_id' => '', 'access_token' => '', 'location_id' => '']), 'test_mode' => 1],
        ['name' => 'authorizenet',   'display_name' => 'Authorize.net',    'enabled' => 0, 'sort_order' => 4, 'config' => json_encode(['api_login_id' => '', 'transaction_key' => '']), 'test_mode' => 1],
        ['name' => 'cashapp',        'display_name' => 'Cash App',         'enabled' => 0, 'sort_order' => 5, 'config' => json_encode(['client_id' => '', 'client_secret' => '']), 'test_mode' => 1],
        ['name' => 'googlepay',      'display_name' => 'Google Pay',       'enabled' => 0, 'sort_order' => 6, 'config' => json_encode(['merchant_id' => '', 'gateway_merchant_id' => '']), 'test_mode' => 1],
        ['name' => 'applepay',       'display_name' => 'Apple Pay',        'enabled' => 0, 'sort_order' => 7, 'config' => json_encode(['merchant_identifier' => '', 'merchant_certificate' => '']), 'test_mode' => 1],
    ];

    $insertSql = "INSERT INTO `gateways` (`name`, `display_name`, `enabled`, `sort_order`, `config`, `test_mode`) VALUES (:name, :display_name, :enabled, :sort_order, :config, :test_mode)";
    $stmt = $pdo->prepare($insertSql);
    foreach ($defaults as $gw) {
        $stmt->execute($gw);
    }
    echo "Gateways table created and default gateways inserted.\n";
} else {
    echo "Gateways table already exists.\n";
}
