<?php
/**
 * Planet Hosts - migration runner.
 *
 * Modes:
 *   php scripts/migrate.php --run [--files="a.sql,b.php"] [--db=radiohosting] [--fail-on-error]
 *       Runs the idempotent ledger-based migration batch. With --files, only those
 *       (relative names) are considered; without, the whole database/migrations dir
 *       is run in sorted order.
 *   php scripts/migrate.php --run-all [--db=radiohosting] [--fail-on-error]
 *       Same as --run without --files (convenience for installers).
 *   php scripts/migrate.php --mark-applied <file> [--db=radiohosting]
 *       Records a migration as applied without executing it (rescue tool).
 *   php scripts/migrate.php <path-to-migration.php>
 *       Single-file mode used internally by the runner (process isolation for .php
 *       migrations). Also kept for legacy updater invocations.
 *
 * Behaviour:
 *   - Protocol: bootstrap.sql (idempotent base tables) runs first, every time.
 *   - A `migrations` ledger table records every applied file (filename, when, ok).
 *   - Already-applied files are skipped. SQL files that fail because a column/key/
 *     table already exists are treated as already applied (idempotent convergence).
 *   - `--fail-on-error` makes any NEW failure abort with a non-zero exit; otherwise
 *     failures are recorded and reported (the updater still completes but reports).
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "migrate.php must run from the CLI.\n");
    exit(1);
}

define('BASE_PATH', realpath(__DIR__ . '/..'));
if (!BASE_PATH || !is_file(BASE_PATH . '/.env')) {
    fwrite(STDERR, "migrate.php: cannot locate application root (BASE_PATH or .env missing).\n");
    exit(1);
}

// Load .env into the environment (mirrors public/index.php).
foreach (file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) { continue; }
    if (str_contains($line, '=')) {
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
        if (!getenv(trim($key))) { putenv(trim($key)); } // all vars via files (no $_ENV duplication needed)
    }
}

require BASE_PATH . '/core/helpers.php';

spl_autoload_register(function ($class) {
    $r = str_replace('\\', '/', $class) . '.php';
    $f = BASE_PATH . '/' . $r;
    if (is_file($f)) { require $f; return; }
    $p = explode('/', $r);
    $p[0] = strtolower($p[0]);
    $l = BASE_PATH . '/' . implode('/', $p);
    if (is_file($l)) { require $l; }
});

require BASE_PATH . '/core/Database.php';

$argv = $_SERVER['argv'] ?? [];
$args = array_slice($argv, 1);

$dbName  = getenv('DB_DATABASE') ?: 'radiohosting';
$failOnError = false;
$filesArg = null;
$mode = null;
$positional = [];

foreach ($args as $i => $arg) {
    switch (true) {
        case $arg === '--run':
        case $arg === '--run-all':
            $mode = 'run';
            break;
        case $arg === '--fail-on-error':
            $failOnError = true;
            break;
        case str_starts_with($arg, '--files='):
            $filesArg = substr($arg, strlen('--files='));
            break;
        case str_starts_with($arg, '--db='):
            $dbName = substr($arg, strlen('--db='));
            break;
        case $arg === '--mark-applied':
            $mode = 'mark';
            break;
        default:
            if (str_starts_with($arg, '-')) {
                fwrite(STDERR, "migrate.php: unknown option $arg\n");
                exit(2);
            }
            $positional[] = $arg;
    }
}

// ---------------------------------------------------------------------------
// Database connection (multi-statement capable)
// ---------------------------------------------------------------------------
function db_connect(string $dbName): PDO
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $user = getenv('DB_USERNAME') ?: 'radiouser';
    $pass = getenv('DB_PASSWORD') ?: '';
    $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Buffered results so SELECT (applied_files) doesn't block later INSERTs
        // (avoids "General error: 2014 Cannot execute queries while other unbuffered
        // queries are active" when the migration ledger is written on the same conn).
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    ]);
}

function migration_pdo(PDO $pdo, string $sql): ?int
{
    // Returns MariaDB error code on failure, null on success.
    // PDO::exec() cannot reliably run multiple statements in one call on
    // MariaDB 11.x / PHP 8.x (fails with 1064 or only runs the first one),
    // and statement types like CALL / INSERT..SELECT can leave the connection
    // with an active result set causing "2014 Cannot execute queries while other
    // unbuffered queries are active" on the NEXT statement.
    // So: split into statements and run each on a DISPOSABLE connection, fully
    // isolating result-state between statements.
    $schemas = null;
    $firstError = null;
    foreach (split_sql_statements($sql) as $stmt) {
        if (trim($stmt) === '') continue;
        $conn = clone_connection($pdo, $schemas);
        try {
            $conn->exec($stmt);
        } catch (PDOException $e) {
            $info = $e->errorInfo ?? [];
            $code = (int)($info[1] ?? 0);
            if ($firstError === null) $firstError = $code;
            unset($conn);
            return $code;
        }
        unset($conn);
    }
    return $firstError;
}

/** Open a fresh connection to the same server/db as $pdo (for isolation). */
function clone_connection(PDO $pdo, &$schemaCache): PDO
{
    if ($schemaCache === null) {
        $schemaCache = [
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => getenv('DB_PORT') ?: '3306',
            'user' => getenv('DB_USERNAME') ?: 'radiouser',
            'pass' => getenv('DB_PASSWORD') ?: '',
        ];
    }
    // Use the same database as $pdo
    $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
    if (!$db) $db = getenv('DB_DATABASE') ?: 'radiohosting';
    return new \PDO("mysql:host={$schemaCache['host']};port={$schemaCache['port']};dbname={$db};charset=utf8mb4",
        $schemaCache['user'], $schemaCache['pass'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        ]);
}

/**
 * Split SQL text into individual statements, tolerating:
 *  - MySQL `--` and `#` comments
 *  - Multi-line statements with no trailing semicolon
 *  - Statements that already end with `;`
 *  - `DELIMITER $$` ... `DELIMITER ;` blocks (stored procedures) — passed whole
 * Statements are separated by a semicolon that is either at a line end
 * (optionally followed by whitespace) or on its own line.
 */
function split_sql_statements(string $sql): array
{
    $out = [];
    $lineBuf = '';
    $delim = ';';
    foreach (preg_split('/\r\n|\r|\n/', $sql) as $line) {
        $trim = rtrim($line);
        $t = ltrim($trim);
        // Handle DELIMITER directives
        if (preg_match('/^DELIMITER\s+(\S+)/i', $t, $m)) {
            // flush whatever accumulated before the directive
            if (trim($lineBuf) !== '') { $out[] = $lineBuf; $lineBuf = ''; }
            $delim = $m[1];
            continue;
        }
        // Skip pure comment lines
        if (str_starts_with($t, '--') || str_starts_with($t, '#')) {
            // keep block comments (-- ... within a procedure body is dropped, safe)
            continue;
        }
        $lineBuf .= $trim . "\n";
        // A statement ends when the line ends with the current delimiter
        if ($delim === ';') {
            if (preg_match('/;\s*$/', $trim)) { $out[] = $lineBuf; $lineBuf = ''; }
        } else {
            // In a DELIMITER block: statement ends at the delimiter token at line end.
            // The delimiter token is client-side only — strip it before sending to server.
            if (preg_match('/' . preg_quote($delim, '/') . '\s*$/', $trim)) {
                $out[] = rtrim(preg_replace('/' . preg_quote($delim, '/') . '\s*$/u', '', $lineBuf));
                $lineBuf = '';
                $delim = ';';
            }
        }
    }
    if (trim($lineBuf) !== '') $out[] = $lineBuf;
    return $out;
}

// Errors that mean "this already happened" for schema migrations.
const DUPLICATE_STRUCTURAL = [1050, 1060, 1061, 1831, 1901];

function ensure_ledger(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
        file VARCHAR(255) NOT NULL PRIMARY KEY,
        applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        success TINYINT(1) NOT NULL DEFAULT 1,
        error TEXT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function applied_files(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT file, success FROM migrations");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor(); // release the result so later writes on the same conn work
    $ok = [];
    foreach ($rows as $r) {
        if ((int)$r['success'] === 1) { $ok[$r['file']] = true; }
    }
    return $ok;
}

function record_applied(PDO $pdo, string $file, bool $success, ?string $error = null): void
{
    // Use a FRESH connection for the ledger write. The main PDO may still have an
    // open (unbuffered) result from executing migration SQL or a CALL/PROCEDURE,
    // which makes a concurrent INSERT on the same connection throw PDO 2014.
    // IMPORTANT: connect to the SAME database the main $pdo is using (not .env's
    // DB_DATABASE), so the ledger lands in the right schema on --run--all/--db=.
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $user = getenv('DB_USERNAME') ?: 'radiouser';
    $pass = getenv('DB_PASSWORD') ?: '';
    $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
    if (!$db) { $db = getenv('DB_DATABASE') ?: 'radiohosting'; }
    $ledger = new \PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    ]);
    $stmt = $ledger->prepare("INSERT INTO migrations (file, applied_at, success, error)
        VALUES (?, NOW(), ?, ?)
        ON DUPLICATE KEY UPDATE success = VALUES(success), error = VALUES(error)");
    $stmt->execute([$file, $success ? 1 : 0, $error]);
    unset($stmt, $ledger);
}

function bootstrap_tables(PDO $pdo, $out = null): void
{
    $out = $out ?? STDOUT;
    $file = BASE_PATH . '/database/bootstrap.sql';
    if (!is_file($file)) {
        fwrite($out, "migrate.php: bootstrap.sql not found at $file\n");
        return;
    }
    $code = migration_pdo($pdo, file_get_contents($file));
    if ($code !== null) {
        fwrite($out, "migrate.php: bootstrap.sql error [{$code}] (continuing; tables may already exist)\n");
    }
}

// ---------------------------------------------------------------------------
// Mode: single-file (process-isolated). No ledger (the batch mode records).
// ---------------------------------------------------------------------------
if ($mode === null && count($positional) === 1) {
    $migration = $positional[0];
    if (!is_file($migration)) {
        fwrite(STDERR, "migrate.php: migration not found: $migration\n");
        exit(2);
    }
    try {
        $pdo = db_connect($dbName);
        bootstrap_tables($pdo, STDERR);
        require $migration;
        exit(0);
    } catch (\Throwable $e) {
        fwrite(STDERR, 'migrate.php: ' . $e->getMessage() . "\n");
        fwrite(STDERR, '  at ' . $e->getFile() . ':' . $e->getLine() . "\n");
        exit(1);
    }
}

// ---------------------------------------------------------------------------
// Mode: mark-applied (rescue)
// ---------------------------------------------------------------------------
if ($mode === 'mark' && count($positional) === 1) {
    $file = $positional[0];
    $pdo = db_connect($dbName);
    ensure_ledger($pdo);
    record_applied($pdo, $file, true, 'marked applied manually');
    echo "marked applied: $file\n";
    exit(0);
}

// ---------------------------------------------------------------------------
// Mode: batch run (--run / --run-all)
// ---------------------------------------------------------------------------
if ($mode === 'run') {
    $pdo = db_connect($dbName);
    ensure_ledger($pdo);
    bootstrap_tables($pdo, STDOUT);

    $applied = applied_files($pdo);
    $migDir = BASE_PATH . '/database/migrations';

    if ($filesArg !== null && $filesArg !== '') {
        $wanted = [];
        foreach (explode(',', $filesArg) as $name) {
            $name = trim($name);
            if ($name !== '' && is_file($migDir . '/' . $name)) { $wanted[] = $name; }
        }
        sort($wanted, SORT_STRING);
    } else {
        $wanted = [];
        foreach (glob($migDir . '/*.sql') ?: [] as $f) { $wanted[] = basename($f); }
        foreach (glob($migDir . '/*.php') ?: [] as $f) { $wanted[] = basename($f); }
        sort($wanted, SORT_STRING);
    }

    $stats = ['applied' => 0, 'skipped' => 0, 'already' => 0, 'failed' => 0];
    $failures = [];

    foreach ($wanted as $name) {
        $rel = $name;
        $full = $migDir . '/' . $name;
        if (isset($applied[$rel])) {
            $stats['skipped']++;
            continue;
        }
        $isPhp = str_ends_with($name, '.php');
        if ($isPhp) {
            // Run in a fresh process so migration code that exits/echoes cannot
            // corrupt our state; the subprocess also re-runs bootstrap (harmless).
            $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' ' . escapeshellarg($full) . ' --db=' . escapeshellarg($dbName);
            $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            $out = is_resource($proc) ? stream_get_contents($pipes[1]) : '';
            $err = is_resource($proc) ? stream_get_contents($pipes[2]) : '';
            if (is_resource($proc)) {
                fclose($pipes[1]);
                fclose($pipes[2]);
                $code = proc_close($proc);
            } else {
                $code = 127;
            }
            $trimmed = trim($out . $err);
            if ($code === 0) {
                record_applied($pdo, $rel, true);
                $stats['applied']++;
                fwrite(STDOUT, "[migrate] applied $rel\n");
            } else {
                $stats['failed']++;
                $failures[] = $rel;
                record_applied($pdo, $rel, false, $trimmed);
                fwrite(STDOUT, "[migrate] FAILED $rel (exit $code)\n");
                if ($trimmed !== '') { fwrite(STDOUT, '    ' . str_replace("\n", "\n    ", substr($trimmed, 0, 1500)) . "\n"); }
            }
            continue;
        }

        // SQL file
        $code = migration_pdo($pdo, file_get_contents($full));
        if ($code === null) {
            record_applied($pdo, $rel, true);
            $stats['applied']++;
            fwrite(STDOUT, "[migrate] applied $rel\n");
        } elseif (in_array($code, DUPLICATE_STRUCTURAL, true)) {
            record_applied($pdo, $rel, true, "already present (err $code)");
            $stats['already']++;
            fwrite(STDOUT, "[migrate] already present $rel\n");
        } else {
            $stats['failed']++;
            $failures[] = $rel;
            record_applied($pdo, $rel, false, "mysql error $code");
            fwrite(STDOUT, "[migrate] FAILED $rel (mysql error $code)\n");
        }
    }

    $summary = sprintf(
        "migrate: %d applied, %d skipped, %d already-present, %d failed",
        $stats['applied'], $stats['skipped'], $stats['already'], $stats['failed']
    );
    fwrite(STDOUT, $summary . "\n");

    if ($stats['failed'] > 0) {
        fwrite(STDOUT, 'migrate: failures: ' . implode(', ', $failures) . "\n");
        if ($failOnError) {
            fwrite(STDERR, "migrate: aborting because --fail-on-error was set\n");
            exit(1);
        }
    }
    exit(0);
}

fwrite(STDERR, "Usage: migrate.php --run [--files=\"a,b\"] [--db=name] [--fail-on-error] | --run-all | --mark-applied <file> | <file>.php\n");
exit(2);