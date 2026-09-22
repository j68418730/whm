<?php
/**
 * Mailbox read helper (run as root via sudo from the web app).
 * Lists / reads messages in a virtual mailbox maildir.
 *
 * Usage:
 *   sudo php8.4 mailbox_read.php list <domain> <user>
 *   sudo php8.4 mailbox_read.php read  <domain> <user> <filename>
 *   sudo php8.4 mailbox_read.php count <domain> <user>
 */

$action = $argv[1] ?? 'list';
$domain = $argv[2] ?? '';
$user = $argv[3] ?? '';
$file = $argv[4] ?? '';

$base = '/var/vmail/' . $domain . '/' . $user . '/Maildir';

if ($action === 'list') {
    $out = [];
    foreach (['new', 'cur'] as $sub) {
        $dir = $base . '/' . $sub;
        if (!is_dir($dir)) continue;
        foreach (glob($dir . '/*') as $f) {
            if (!is_file($f)) continue;
            $raw = @file_get_contents($f);
            if ($raw === false) continue;
            // Parse headers
            $subject = ''; $from = ''; $date = ''; $to = '';
            $lines = explode("\n", $raw);
            foreach ($lines as $line) {
                if ($line === "\r" || $line === '' || $line === "\n") break;
                if (str_starts_with($line, 'Subject:')) $subject = trim(substr($line, 8));
                elseif (str_starts_with($line, 'From:')) $from = trim(substr($line, 5));
                elseif (str_starts_with($line, 'Date:')) $date = trim(substr($line, 5));
                elseif (str_starts_with($line, 'To:')) $to = trim(substr($line, 3));
            }
            $out[] = [
                'file' => basename($f),
                'folder' => $sub,
                'subject' => $subject,
                'from' => $from,
                'to' => $to,
                'date' => $date,
                'size' => filesize($f),
            ];
        }
    }
    // Newest first
    usort($out, fn($a, $b) => ($b['date'] <=> $a['date']));
    echo json_encode($out);
    exit;
}

if ($action === 'read') {
    $f = $base . '/' . ($argv[4] ?? '');
    $path = $base . '/new/' . basename($file);
    if (!is_file($path)) $path = $base . '/cur/' . basename($file);
    if (!is_file($path)) { echo json_encode(['error' => 'not found']); exit; }
    $raw = file_get_contents($path);
    // Split headers/body
    $sep = strpos($raw, "\r\n\r\n");
    if ($sep === false) $sep = strpos($raw, "\n\n");
    $headers = $sep === false ? $raw : substr($raw, 0, $sep);
    $body = $sep === false ? '' : substr($raw, $sep + 4);
    // Decode subject/from (MIME encoded words)
    $dec = function ($s) {
        $s = preg_replace('/=\?([^?]+)\?([BQ])\?([^?]*)\?=/i', function ($m) {
            $v = $m[2] === 'B' ? base64_decode($m[3]) : quoted_printable_decode($m[3]);
            return $v;
        }, $s);
        return $s;
    };
    $subject = ''; $from = ''; $date = ''; $to = ''; $cc = '';
    foreach (explode("\r\n", $headers) as $line) {
        if (str_starts_with($line, 'Subject:')) $subject = trim(substr($line, 8));
        elseif (str_starts_with($line, 'From:')) $from = trim(substr($line, 5));
        elseif (str_starts_with($line, 'Date:')) $date = trim(substr($line, 5));
        elseif (str_starts_with($line, 'To:')) $to = trim(substr($line, 3));
        elseif (str_starts_with($line, 'Cc:')) $cc = trim(substr($line, 3));
    }
    echo json_encode([
        'file' => basename($path),
        'headers' => $headers,
        'body' => $body,
        'subject' => $dec($subject),
        'from' => $dec($from),
        'to' => $to,
        'cc' => $cc,
        'date' => $date,
    ]);
    exit;
}

if ($action === 'count') {
    $n = 0; $u = 0;
    foreach (['new', 'cur'] as $sub) {
        $dir = $base . '/' . $sub;
        if (!is_dir($dir)) continue;
        foreach (glob($dir . '/*') as $f) {
            if (!is_file($f)) continue;
            $n++;
            if ($sub === 'new') $u++;
        }
    }
    echo json_encode(['total' => $n, 'unseen' => $u]);
    exit;
}

echo json_encode(['error' => 'invalid action']);
exit;