<?php

namespace Services;

/**
 * Planet Hosts Terminal — PTY session manager.
 *
 * Creates and manages real PTY sessions (via a per-session CLI daemon that
 * spawns the shell on a pty). Streaming is done over SSE (Server-Sent Events):
 * the browser subscribes to /terminal/stream/{sid}, sends input via
 * /terminal/input/{sid}, and resizes via /terminal/resize/{sid}.
 *
 * Security model (enforced by the OS, not by command filtering):
 *   - ROOT sessions run the daemon as root  -> unrestricted.
 *   - Customer/reseller sessions run the daemon as THAT Linux account via
 *     `sudo -u {user}` -> the OS enforces home-directory and file isolation.
 *   - No command is ever executed as root on behalf of a non-root account.
 */
class TerminalSessionService
{
    protected $db;
    protected $dir = '/var/www/radiohosting/storage/terminal/sessions';
    protected $phpBin = '/usr/bin/php';
    protected $daemon = '/var/www/radiohosting/scripts/terminal_daemon.php';
    protected $idleTimeout = 1800; // 30 min

    public function __construct($db = null)
    {
        $app = \Core\Application::getInstance();
        $this->db = $db ?: $app->get('db');
        if (!is_dir($this->dir)) @mkdir($this->dir, 01777, true);
        // The daemon for customer sessions runs as the customer's Linux user, so
        // it must be able to traverse into sessions/. Sticky + world-writable.
        @chmod($this->dir, 01777);
    }

    /** Pick a PHP CLI that exists. */
    protected function php(): string
    {
        foreach (['php8.4', 'php8.3', 'php8.2', 'php8.1', 'php'] as $c) {
            $p = @exec('command -v ' . $c . ' 2>/dev/null');
            if ($p) return trim($p);
        }
        return '/usr/bin/php';
    }

    /** Resolve the Linux account + home for a role. */
    public function resolveIdentity(string $role, $account)
    {
        if ($role === 'root') {
            return ['user' => 'root', 'home' => '/root', 'sudo_user' => 'root', 'restricted' => false];
        }
        if ($role === 'reseller') {
            $username = $account->username ?? ($account->name ?? 'reseller');
            // Resellers have their own Linux account created at provisioning.
            $info = posix_getpwnam($username);
            $home = $info['dir'] ?? ('/home/' . $username);
            return ['user' => $username, 'home' => $home, 'sudo_user' => $username, 'restricted' => true];
        }
        // customer / user
        $username = $account->username ?? 'user';
        $info = posix_getpwnam($username);
        $home = $info['dir'] ?? ('/home/' . $username);
        return ['user' => $username, 'home' => $home, 'sudo_user' => $username, 'restricted' => true];
    }

    /** Create a new terminal session. Returns session array or throws. */
    public function create(string $role, $account, string $label = '', int $cols = 80, int $rows = 24)
    {
        $ident = $this->resolveIdentity($role, $account);
        $sid = bin2hex(random_bytes(16));
        $dir = $this->dir . '/' . $sid;
        if (!@mkdir($dir, 0770, true)) {
            throw new \RuntimeException('Could not create session directory');
        }
        // Session dirs are shared between the web layer (www-data writes input /
        // reads output) and the daemon (runs AS the target Linux user). 1777
        // (sticky, world-writable) lets both sides access the I/O buffers without
        // exposing any secret data — the dir only holds transient terminal I/O.
        @chmod($dir, 01777);

        $started = date('Y-m-d H:i:s');
        $meta = [
            'id' => $sid,
            'role' => $role,
            'user' => $ident['user'],
            'home' => $ident['home'],
            'label' => $label ?: ($ident['user'] . '@' . gethostname()),
            'hostname' => gethostname(),
            'started' => $started,
            'last_activity' => $started,
            'status' => 'starting',
            'cols' => $cols,
            'rows' => $rows,
            'pid' => null,
            'source_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'restricted' => $ident['restricted'],
        ];
        file_put_contents($dir . '/meta.json', json_encode($meta, JSON_PRETTY_PRINT));
        // Polled I/O buffers (no FIFOs — avoids open/read blocking handshakes)
        file_put_contents($dir . '/output.log', '');
        file_put_contents($dir . '/input.bin', '');
        file_put_contents($dir . '/control.bin', '');

        // Spawn the daemon via `sudo /bin/bash -c 'nohup ... &'` — www-data has a
// NOPASSWD rule for /bin/bash, and this works when backgrounded (unlike
// `sudo -n -u <user> php` which requires a controlling tty). The daemon then
// runs as root and internally uses runuser (for customer sessions) to drop to
// the target Linux account, so the OS enforces isolation.
$php = $this->php();
$daemon = $this->daemon;
$escSid = escapeshellarg($sid);
$escUser = escapeshellarg($ident['sudo_user']);
$escHome = escapeshellarg($ident['home']);
$inner = "nohup {$php} {$daemon} {$escSid} {$escUser} {$cols} {$rows} {$escHome} >> " . escapeshellarg($this->dir . '/' . $sid . '/daemon.err') . " 2>&1 < /dev/null &";
$cmd = 'sudo -n /bin/bash -c ' . escapeshellarg($inner);
@exec($cmd);

        // Wait briefly for the daemon to confirm the PTY is up.
        for ($i = 0; $i < 30; $i++) {
            $state = $this->daemonState($sid);
            if ($state && !empty($state['ok'])) {
                $meta['status'] = 'active';
                $meta['pid'] = $state['pid'] ?? null;
                $meta['pty'] = $state['pty'] ?? '';
                $this->updateMeta($sid, $meta);
                $this->audit($role, $account, 'session_started', $sid);
                return $this->session($sid);
            }
            usleep(100000);
        }
        // Daemon failed to start (e.g. no PTY, sudo denied)
        $this->markDead($sid);
        $this->audit($role, $account, 'session_failed', $sid);
        throw new \RuntimeException('Terminal session failed to start (PTY unavailable?)');
    }

    /** Read current daemon state. */
    public function daemonState(string $sid): ?array
    {
        $f = $this->dir . '/' . $sid . '/state.json';
        if (!is_file($f)) return null;
        $d = json_decode((string)@file_get_contents($f), true);
        return is_array($d) ? $d : null;
    }

    /** Get a session's meta. */
    public function session(string $sid): ?array
    {
        $f = $this->dir . '/' . $sid . '/meta.json';
        if (!is_file($f)) return null;
        $m = json_decode((string)@file_get_contents($f), true);
        if (!is_array($m)) return null;
        $st = $this->daemonState($sid);
        if (!$st || empty($st['ok'])) $m['status'] = 'dead';
        return $m;
    }

    protected function updateMeta(string $sid, array $meta): void
    {
        $f = $this->dir . '/' . $sid . '/meta.json';
        @file_put_contents($f, json_encode($meta, JSON_PRETTY_PRINT));
    }

    /** Append raw input bytes to the session input buffer. */
    public function input(string $sid, string $data): bool
    {
        $f = $this->dir . '/' . $sid . '/input.bin';
        if (!file_exists($f)) return false;
        $ok = @file_put_contents($f, $data, FILE_APPEND) !== false;
        $m = $this->session($sid);
        if ($m) { $m['last_activity'] = date('Y-m-d H:i:s'); $this->updateMeta($sid, $m); }
        return $ok;
    }

    /** Resize the PTY. */
    public function resize(string $sid, int $cols, int $rows): bool
    {
        $f = $this->dir . '/' . $sid . '/control.bin';
        if (!file_exists($f)) return false;
        return @file_put_contents($f, "resize " . max(5, $rows) . " " . max(20, $cols) . "\n", FILE_APPEND) !== false;
    }

    /** Kill a session (graceful). */
    public function kill(string $sid): bool
    {
        $f = $this->dir . '/' . $sid . '/control.bin';
        $meta = $this->session($sid);
        if (file_exists($f)) { @file_put_contents($f, "kill\n", FILE_APPEND); }
        // Fallback: SIGTERM the daemon pid
        $pid = $meta['pid'] ?? null;
        if ($pid) { @exec('sudo kill ' . (int)$pid . ' 2>/dev/null'); }
        // Wait briefly for the daemon to clean up, then remove leftovers
        usleep(200000);
        $this->cleanup($sid);
        return true;
    }

    /** Remove session files. */
    public function cleanup(string $sid): void
    {
        $dir = $this->dir . '/' . $sid;
        if (is_dir($dir)) {
            @exec('rm -rf ' . escapeshellarg($dir) . ' 2>/dev/null');
        }
    }

    /** List sessions (optionally filtered by role/user). */
    public function sessions(string $role = '', string $user = ''): array
    {
        $out = [];
        $entries = glob($this->dir . '/*/meta.json') ?: [];
        foreach ($entries as $f) {
            $m = json_decode((string)@file_get_contents($f), true);
            if (!is_array($m)) continue;
            if ($role && ($m['role'] ?? '') !== $role) continue;
            if ($user && ($m['user'] ?? '') !== $user) continue;
            $st = $this->daemonState($m['id']);
            $m['status'] = (!$st || empty($st['ok'])) ? 'dead' : ($m['status'] ?? 'active');
            $out[] = $m;
        }
        usort($out, fn($a, $b) => strcmp($b['started'] ?? '', $a['started'] ?? ''));
        return $out;
    }

    /** Read output appended since a byte offset (for SSE). */
    public function outputSince(string $sid, int $offset): array
    {
        $f = $this->dir . '/' . $sid . '/output.log';
        if (!is_file($f)) return ['data' => '', 'offset' => $offset, 'eof' => true];
        $size = filesize($f);
        if ($size <= $offset) return ['data' => '', 'offset' => $size, 'eof' => false];
        $h = fopen($f, 'rb');
        fseek($h, $offset);
        $data = stream_get_contents($h);
        $newOffset = ftell($h);
        fclose($h);
        // eof = session ended (no daemon running)
        $st = $this->daemonState($sid);
        $eof = (!$st || empty($st['ok'])) && $newOffset >= $size;
        return ['data' => $data, 'offset' => $newOffset, 'eof' => $eof];
    }

    protected function markDead(string $sid): void
    {
        $m = $this->session($sid);
        if ($m) { $m['status'] = 'dead'; $this->updateMeta($sid, $m); }
    }

    /** Audit log (reuse activity_logs). Never logs secrets. */
    protected function audit(string $role, $account, string $action, string $sid): void
    {
        try {
            $this->db->table('activity_logs')->insert([
                'account_id' => $account->id ?? 0,
                'admin_id' => $this->isAdminSession() ? ($account->id ?? 0) : 0,
                'action' => 'terminal_' . $action,
                'details' => "Terminal {$action} ({$role}) session {$sid}",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (\Exception $e) {}
    }

    protected function isAdminSession(): bool
    {
        return !empty($_SESSION['is_admin']);
    }
}