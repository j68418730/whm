<?php
/**
 * Planet Hosts — Intrusion Detection Monitor (attack_alert.php)
 * Watches Apache access + error logs for attack signatures (brute-force,
 * SQL injection, file inclusion, XSS probes, scanners, auth abuse).
 * On detection: records the event, creates an in-app security alert, and
 * emails root@planet-hosts.com (rate-limited, no repeat spam).
 *
 * Run every 1 minute via cron. Safe / non-destructive: it NEVER blocks or
 * changes anything — it only detects + notifies. fail2ban does the blocking.
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '0');

define('BASE', __DIR__ . '/..');
$pdo = new PDO(
    'mysql:host=localhost;dbname=radiohosting;charset=utf8mb4',
    'radiouser', 'Skylinehosting171',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Ensure tables exist
$pdo->exec("CREATE TABLE IF NOT EXISTS attack_events (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(40) NOT NULL,
    severity VARCHAR(10) NOT NULL DEFAULT 'high',
    ip VARCHAR(45) NOT NULL,
    uri VARCHAR(500) DEFAULT NULL,
    user_agent VARCHAR(300) DEFAULT NULL,
    details VARCHAR(1000) DEFAULT NULL,
    count INT NOT NULL DEFAULT 1,
    first_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    emailed TINYINT NOT NULL DEFAULT 0,
    resolved TINYINT NOT NULL DEFAULT 0,
    KEY idx_ip (ip),
    KEY idx_type (type),
    KEY idx_last_seen (last_seen),
    KEY idx_emailed (emailed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS attack_alert_state (
    id INT PRIMARY KEY,
    last_access_pos BIGINT NOT NULL DEFAULT 0,
    last_error_pos BIGINT NOT NULL DEFAULT 0,
    last_email_at DATETIME DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$state = $pdo->query("SELECT * FROM attack_alert_state WHERE id=1")->fetch(PDO::FETCH_OBJ);
if (!$state) {
    $pdo->exec("INSERT INTO attack_alert_state (id, last_access_pos, last_error_pos) VALUES (1, 0, 0)");
    $state = (object)['last_access_pos' => 0, 'last_error_pos' => 0, 'last_email_at' => null];
}

$accessPos = (int)$state->last_access_pos;
$errorPos  = (int)$state->last_error_pos;

$accessLog = '/var/log/apache2/access.log';
$errorLog  = '/var/log/apache2/error.log';

function readNewLines(string $file, int $pos, int $maxBytes = 2097152): array {
    if (!is_file($file)) return [];
    $size = filesize($file);
    // If log rotated (file smaller than last pos), restart from 0
    if ($size < $pos) $pos = 0;
    $lines = [];
    if ($size > $pos) {
        $h = @fopen($file, 'r');
        if ($h) {
            fseek($h, $pos);
            $chunk = fread($h, $maxBytes);
            fclose($h);
            foreach (explode("\n", $chunk) as $line) if (trim($line)) $lines[] = $line;
        }
    }
    return [$lines, $size];
}

[$accessLines, $accessPos] = readNewLines($accessLog, $accessPos);
[$errorLines,  $errorPos]  = readNewLines($errorLog, $errorPos);

// ─────────────────────────────────────────────
// Attack signatures
// ─────────────────────────────────────────────

// Access log signatures: 'ip - - [date] "GET /path HTTP/1.1" status size ...'
$sigAccess = [
    // SQL injection attempts
    'sqli' => [
        '/\bunion\s+select\b/i', '/\bselect\s+.*\bfrom\b.*\bwhere\b/i',
        '/\bor\s+1\s*=\s*1\b/i', '/\bsleep\s*\(/i', '/\bbenchmark\s*\(/i',
        '/\bconcat\s*\(/i', '/\bupdatexml\s*\(/i', '/\bextractvalue\s*\(/i',
        '/\b0x[0-9a-f]{8,}/i', '/--\s*$/i', "/['\"]\s*(?:or|and)\s+['\"]?[0-9a-z]+['\"]?\s*=/i",
        '/information_schema/i', '/\bgroup\s+by\s+.*\bwith\s+rollup\b/i',
    ],
    // File inclusion (LFI/RFI)
    'lfi' => [
        '/\.\.\/\.\.\/|\/etc\/passwd|\.\.%2f%2f|\.\.%2f/i',
        '/php:\/\/filter|php:\/\/input|data:\/\/|expect:\/\//i',
        '/\.\.\/|\.\.%2f|%00/i', '/file=.*(etc|passwd|shadow|\.env|config)/i',
    ],
    // XSS probes
    'xss' => [
        '/<script>/i', '/%3cscript%3e/i', '/<img[^>]+onerror=/i', '/javascript:/i',
        '/onload\s*=/i', '/<svg[^>]+onclick=/i',
    ],
    // Known scanners / bots (high-confidence tool UAs + obvious probe paths)
    'scanner' => [
        '/acunetix/i', '/nikto/i', '/sqlmap/i', '/wpscan/i', '/nessus/i', '/burp/i',
        '/masscan/i', '/zmap/i', '/netsparker/i', '/apachebench/i', '/hydra/i',
        '/dirbuster/i', '/wfuzz/i', '/nmap/i', '/gobuster/i', '/c99shell/i',
        '/\.git\/config/i', '/\.env/i', '/wp-config\.php/i',
    ],
    // Auth abuse probes (legit /api/login for DJ/desktop is EXCLUDED)
    'auth_abuse' => [
        '/wp-login\.php/i', '/\.htpasswd/i', '/\.htaccess/i', '/adminer/i',
        '/cgi-bin\//i', '/\.\.\/\.\.\/.*(etc|shadow)/i',
    ],
];

// Error log signatures: '[timestamp] [php:error] ... message'
// NOTE: We deliberately do NOT detect SQLi from PHP error logs — legitimate
// app bugs always contain SQL keywords ("Unknown column X in SELECT"), which
// produces false positives. Real injection attempts appear in the ACCESS log
// (the raw request URL), which we detect above. Error logs only flag
// unambiguous code-execution indicators.
$sigError = [
    'code_exec' => [
        '/Call to undefined function (system|shell_exec|passthru|exec)/i',
        '/preg_replace.*\s*\/\s*[a-z]*e\b/i',
        '/create_function/i',
    ],
];

// Severity by type
$severity = [
    'sqli' => 'critical', 'lfi' => 'critical', 'xss' => 'high',
    'scanner' => 'medium', 'auth_abuse' => 'high', 'code_exec' => 'critical',
];

$hosts = []; // type => ip => details (aggregate this run)

function detectAccess(string $line, array $sigs): array {
    if (!preg_match('/^(\S+)\s+.*?"([^"]*)"\s+\d+/i', $line, $m)) return [];
    $ip = $m[1]; $req = $m[2];
    // Decode URL-encoding so "UNION%20SELECT" becomes "UNION SELECT" for matching
    $reqDecoded = urldecode($req);
    $ua = '';
    if (preg_match('/"([^"]*)"\s*$/', $line, $um)) $ua = $um[1];
    $found = [];
    foreach ($sigs as $type => $patterns) {
        foreach ($patterns as $p) {
            if (preg_match($p, $reqDecoded)) { $found[] = [$type, $req]; break; }
        }
    }
    return $found ? [$ip, $ua, $found] : [];
}

function detectError(string $line, array $sigs): array {
    if (!preg_match('/\[client (\S+)\]/', $line, $m)) return [];
    $ip = $m[1];
    $found = [];
    foreach ($sigs as $type => $patterns) {
        foreach ($patterns as $p) {
            if (preg_match($p, $line)) { $found[] = [$type, $line]; break; }
        }
    }
    return $found ? [$ip, '', $found] : [];
}

foreach ($accessLines as $line) {
    $r = detectAccess($line, $sigAccess);
    if (!$r) continue;
    [$ip, $ua, $found] = $r;
    foreach ($found as [$type, $detail]) {
        $hosts[$type][$ip] ??= ['ua' => $ua, 'detail' => $detail, 'count' => 0];
        $hosts[$type][$ip]['count']++;
        $hosts[$type][$ip]['detail'] = substr($detail, 0, 500);
    }
}
foreach ($errorLines as $line) {
    $r = detectError($line, $sigError);
    if (!$r) continue;
    [$ip, $ua, $found] = $r;
    foreach ($found as [$type, $detail]) {
        $hosts[$type][$ip] ??= ['ua' => $ua, 'detail' => $detail, 'count' => 0];
        $hosts[$type][$ip]['count']++;
        $hosts[$type][$ip]['detail'] = substr($detail, 0, 500);
    }
}

// Persist state
$st = $pdo->prepare("UPDATE attack_alert_state SET last_access_pos=?, last_error_pos=?, updated_at=NOW() WHERE id=1");
$st->execute([$accessPos, $errorPos]);

if (!$hosts) exit(0); // nothing detected

// ─────────────────────────────────────────────
// Record + aggregate into attack_events
// ─────────────────────────────────────────────
$newCritical = false;
$msgLines = [];
foreach ($hosts as $type => $ips) {
    foreach ($ips as $ip => $info) {
        $sev = $severity[$type] ?? 'high';
        $exists = $pdo->prepare("SELECT id, count FROM attack_events WHERE type=? AND ip=? AND resolved=0");
        $exists->execute([$type, $ip]);
        $row = $exists->fetch(PDO::FETCH_OBJ);
        if ($row) {
            $pdo->prepare("UPDATE attack_events SET count=count+?, last_seen=NOW(), details=? WHERE id=?")
                ->execute([$info['count'], substr($info['detail'], 0, 1000), $row->id]);
        } else {
            $pdo->prepare("INSERT INTO attack_events (type, severity, ip, uri, user_agent, details, count) VALUES (?,?,?,?,?,?,?)")
                ->execute([$type, $sev, $ip, $info['detail'], substr($info['ua'] ?? '', 0, 300), $info['detail'], $info['count']]);
            if ($sev === 'critical') $newCritical = true;
        }
        $msgLines[] = "[$sev] $type from $ip ({$info['count']}x) — " . substr($info['detail'], 0, 120);
    }
}

// In-app alert (global customer_id 0 = platform-wide)
try {
    $pdo->prepare("INSERT INTO security_alerts (customer_id, type, message, created_at) VALUES (0, ?, ?, NOW())")
        ->execute(['intrusion', implode(' | ', array_slice($msgLines, 0, 5))]);
} catch (\Exception $e) {}

// ─────────────────────────────────────────────
// Email alert (rate-limited: max 1 per 15 min, only critical)
// ─────────────────────────────────────────────
$lastEmail = $state->last_email_at;
$canEmail = $newCritical && ($lastEmail === null || strtotime($lastEmail) < time() - 900);
if ($canEmail) {
    $body = "Planet Hosts — Intrusion Detection Alert\n"
          . "==========================================\n"
          . "Time: " . date('Y-m-d H:i:s') . " UTC\n\n"
          . implode("\n", $msgLines)
          . "\n\nCritical attacks detected. fail2ban will ban repeat offenders.\n"
          . "View details: http://planet-hosts.com:2087/admin/security/intrusions\n";
    $subject = '[ALERT] ' . count($msgLines) . ' attack(s) detected on planet-hosts.com';
    @mail('root@planet-hosts.com', $subject, $body, "From: alerts@planet-hosts.com\r\nReply-To: support@planet-hosts.com\r\nX-Priority: 1");
    $pdo->prepare("UPDATE attack_alert_state SET last_email_at=NOW() WHERE id=1")->execute();
    $pdo->prepare("UPDATE attack_events SET emailed=1 WHERE type=? AND ip=? AND resolved=0")
        ->execute([array_key_first($hosts), array_key_first($hosts[array_key_first($hosts)])]);
}
exit(0);
