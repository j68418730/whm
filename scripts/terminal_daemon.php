<?php
/**
 * Planet Hosts Terminal — PTY session daemon.
 * Long-running worker per session: opens a real PTY, spawns a shell for the
 * target Linux account, bridges input/output/resize to polled files in
 * storage/terminal/sessions/{sid}/.
 *
 * Usage (spawned by TerminalSessionService, always via sudo as the target user):
 *   sudo -u {linuxUser} php scripts/terminal_daemon.php {sid} {linuxUser} {cols} {rows} {cwd}
 *
 * I/O protocol (polled plain files — no FIFOs, so no blocking handshake):
 *   meta.json       session metadata
 *   output.log      PTY output appended here (daemon writes)
 *   input.bin       browser APPENDS raw bytes; daemon reads+truncates
 *   control.bin     browser APPENDS "resize R C" / "kill"; daemon reads+truncates
 *   state.json      daemon liveness + pty path
 *
 * Security: daemon RUNS AS the target Linux user (OS-enforced isolation).
 * ROOT = unrestricted. Customer/reseller = that account only.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { fwrite(STDERR, "terminal_daemon.php must run from CLI.\n"); exit(1); }

$sid = $argv[1] ?? '';
$linuxUser = $argv[2] ?? '';
$cols = max(20, (int)($argv[3] ?? 80));
$rows = max(5, (int)($argv[4] ?? 24));
$cwd = $argv[5] ?? '';

if ($sid === '' || $linuxUser === '') { fwrite(STDERR, "usage: terminal_daemon.php <sid> <linuxUser> <cols> <rows> [cwd]\n"); exit(1); }

$base = '/var/www/radiohosting/storage/terminal/sessions/' . $sid;
if (!is_dir($base)) { fwrite(STDERR, "session dir missing: $base\n"); exit(1); }

$outputLog = $base . '/output.log';
$inputFile = $base . '/input.bin';
$controlFile = $base . '/control.bin';
$stateFile = $base . '/state.json';
$pidFile = $base . '/pid';

file_put_contents($pidFile, (string)getmypid());

// Home dir for the target account
$home = $cwd;
if ($home === '') {
    $info = posix_getpwnam($linuxUser);
    $home = $info['dir'] ?? ('/home/' . $linuxUser);
    if (!is_dir($home)) $home = '/';
}
$home = rtrim($home, '/') . '/';

// ─── Spawn the shell on a real PTY ───
// ROOT sessions: run bash directly (root).
// Customer/reseller sessions: run via runuser so the shell is THAT Linux user
// (OS-enforced isolation — never run customer commands as root).
$descriptors = [
    0 => ['pty', 'r'],
    1 => ['pty', 'w'],
    2 => ['pty', 'w'],
];
$env = $_ENV;
$env['TERM'] = 'xterm-256color';
$env['HOME'] = $home;
$env['LOGNAME'] = $linuxUser;
$env['USER'] = $linuxUser;
$env['SHELL'] = '/bin/bash';
$env['PWD'] = $home;

$argvCmd = ['/bin/bash', '--noprofile', '--norc', '-i'];
if ($linuxUser !== 'root') {
    $argvCmd = ['/usr/sbin/runuser', '-u', $linuxUser, '--', '/bin/bash', '--noprofile', '--norc', '-i'];
}

$proc = @proc_open(
    $argvCmd,
    $descriptors,
    $pipes,
    $home,
    $env,
    ['bypass_shell' => true]
);
if (!is_resource($proc)) {
    file_put_contents($stateFile, json_encode(['ok' => false, 'error' => 'proc_open PTY failed', 'at' => date('c')]));
    exit(1);
}

// Discover PTY slave path via /proc of the child (non-blocking).
$ptyName = '';
$status = proc_get_status($proc);
$pid = $status['pid'] ?? 0;
if ($pid) {
    for ($i = 0; $i < 20; $i++) {
        $slave = @readlink('/proc/' . $pid . '/fd/0');
        if ($slave && preg_match('#^/dev/pts/\d+$#', $slave)) { $ptyName = $slave; break; }
        usleep(100000);
    }
}
if ($ptyName !== '') { @exec('stty cols ' . (int)$cols . ' rows ' . (int)$rows . ' < ' . escapeshellarg($ptyName) . ' 2>/dev/null'); }

file_put_contents($stateFile, json_encode(['ok' => true, 'pid' => getmypid(), 'child_pid' => $pid, 'pty' => $ptyName, 'at' => date('c')]));

// ─── I/O loop (polled files) ───
stream_set_blocking($pipes[0], false);
stream_set_blocking($pipes[1], false);
stream_set_blocking($pipes[2], false);
$outH = fopen($outputLog, 'ab');
$lastActivity = time();
$running = true;

// Drain a non-blocking stream fully.
$drainStream = function ($stream) {
    if (!is_resource($stream)) return '';
    $data = '';
    while (($chunk = @fread($stream, 8192)) !== false && $chunk !== '') { $data .= $chunk; }
    return $data;
};
// Read + truncate a polled buffer file (input.bin / control.bin).
$drainFile = function ($file) {
    if (!is_file($file)) return '';
    $size = @filesize($file);
    if (!$size || $size <= 0) return '';
    $data = (string)@file_get_contents($file);
    @file_put_contents($file, '');
    return $data;
};

while ($running) {
    // 1) PTY output -> output.log
    $out = $drainStream($pipes[1]);
    $err = $drainStream($pipes[2]);
    if ($out !== '' || $err !== '') {
        fwrite($outH, $out . $err);
        fflush($outH);
        $lastActivity = time();
    }
    // 2) input.bin -> PTY stdin
    $input = $drainFile($inputFile);
    if ($input !== '') { @fwrite($pipes[0], $input); @fflush($pipes[0]); $lastActivity = time(); }
    // 3) control.bin -> resize/kill
    $ctl = $drainFile($controlFile);
    foreach (preg_split('/\R/', $ctl) as $line) {
        $line = trim($line);
        if ($line === 'kill') { $running = false; }
        elseif (preg_match('/^resize\s+(\d+)\s+(\d+)$/', $line, $m)) {
            $rows = max(5, (int)$m[1]); $cols = max(20, (int)$m[2]);
            if ($ptyName !== '') { @exec('stty cols ' . (int)$cols . ' rows ' . (int)$rows . ' < ' . escapeshellarg($ptyName) . ' 2>/dev/null'); }
        }
    }
    // 4) idle timeout (30 min)
    if (time() - $lastActivity > 1800) $running = false;
    // 5) shell exit
    $status = @proc_get_status($proc);
    if (!$status || !$status['running']) $running = false;
    usleep(40000);
}

fclose($outH);
foreach ($pipes as $p) { if (is_resource($p)) @fclose($p); }
@proc_close($proc);
file_put_contents($stateFile, json_encode(['ok' => true, 'ended' => date('c'), 'pid' => getmypid()]));
@unlink($pidFile);
exit(0);