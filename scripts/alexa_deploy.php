<?php
/**
 * Alexa Skill Deploy
 * Runs the generator then `ask deploy` for a station's skill using the
 * configured ASK CLI. Requires ask-cli installed + authenticated once
 * (`ask init`) and the path set in /admin/settings/alexa.
 *
 * Usage:
 *   php alexa_deploy.php <station_id>   (generate + deploy)
 *   php alexa_deploy.php --check        (verify ask-cli + auth)
 */

define('BASE_PATH', dirname(__DIR__));
$envFile = BASE_PATH . '/.env';
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;
    if (str_contains($line, '=')) { [$k, $v] = explode('=', $line, 2); putenv(trim($k) . '=' . trim($v)); }
}

$pdo = new PDO(
    'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') . ';dbname=' . getenv('DB_DATABASE') . ';charset=utf8mb4',
    getenv('DB_USERNAME'), getenv('DB_PASSWORD'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$setting = function ($key, $default = '') use ($pdo) {
    try {
        $st = $pdo->prepare("SELECT setting_value FROM automation_settings WHERE setting_key = ?");
        $st->execute([$key]);
        $v = trim((string)$st->fetchColumn());
        return $v !== '' ? $v : $default;
    } catch (Exception $e) { return $default; }
};

$askPath = $setting('alexa_ask_cli_path', '');
$profile = $setting('alexa_ask_profile', 'default');

if (($argv[1] ?? '') === '--check') {
    $cmd = $askPath !== '' ? $askPath : 'npx ask';
    $out = @shell_exec(escapeshellarg($cmd) . ' --version 2>&1');
    echo "ask-cli: " . ($out ? trim($out) : 'NOT FOUND') . "\n";
    $whoami = @shell_exec('sudo -u www-data ' . escapeshellarg($cmd) . ' config get profile 2>&1');
    echo "profile: " . ($whoami ? trim($whoami) : 'not configured') . "\n";
    exit(0);
}

$stationId = (int)($argv[1] ?? 0);
if (!$stationId) { fwrite(STDERR, "Usage: php alexa_deploy.php <station_id>\n"); exit(1); }

$st = $pdo->prepare("SELECT id, name, port FROM streaming_stations WHERE id = ?");
$st->execute([$stationId]);
$station = $st->fetch(PDO::FETCH_OBJ);
if (!$station) { fwrite(STDERR, "Station $stationId not found\n"); exit(1); }

if ($setting('alexa_enabled', '0') !== '1') { fwrite(STDERR, "Alexa auto-deploy is disabled in settings\n"); exit(1); }
if ($askPath === '') { fwrite(STDERR, "ASK CLI path not configured in /admin/settings/alexa\n"); exit(1); }

$log = BASE_PATH . '/storage/alexa_deploy.log';
file_put_contents($log, "[" . date('c') . "] Deploy skill for station $stationId ({$station->name})\n", FILE_APPEND);

// 1) Generate the skill package
$genCmd = '/usr/bin/php8.4 ' . escapeshellarg(BASE_PATH . '/scripts/alexa_generate.php') . ' ' . $stationId;
$gen = @shell_exec($genCmd . ' 2>&1');
file_put_contents($log, $gen . "\n", FILE_APPEND);
$genData = json_decode((string)$gen, true);
if (!$genData || empty($genData['ok'])) { fwrite(STDERR, "Generator failed:\n$gen\n"); exit(1); }
$skillDir = dirname($genData['zip']);

// 2) ask deploy (skills directory + package)
// ask-cli expects the skill folder; deploy from the package dir
$cmd = 'cd ' . escapeshellarg($skillDir) . ' && ' . escapeshellarg($askPath) . ' deploy --profile ' . escapeshellarg($profile) . ' --force 2>&1';
$out = @shell_exec($cmd);
file_put_contents($log, "--- ask deploy ---\n" . $out . "\n", FILE_APPEND);

// 3) Record result
$ok = str_contains((string)$out, 'SKILL_MODELS_UPDATED') || str_contains((string)$out, 'skill successfully') || str_contains((string)$out, 'deployed') || str_contains((string)$out, 'Model deployed');
try {
    $pdo->prepare("UPDATE streaming_stations SET alexa_status = ?, alexa_skill_id = ?, alexa_deployed_at = NOW() WHERE id = ?")
        ->execute([$ok ? 'deployed' : 'failed', $genData['skill_key'] ?? '', $stationId]);
} catch (Exception $e) {}

echo json_encode(['ok' => $ok, 'station_id' => $stationId, 'skill_key' => $genData['skill_key'] ?? '', 'output' => $out], JSON_PRETTY_PRINT);
exit($ok ? 0 : 1);