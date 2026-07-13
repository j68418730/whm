<?php

namespace Admin\Services;

class FtpBackupEngine
{
    protected $pdo;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->pdo = $app->get('db')->pdo();
    }

    public function testConnection($dest)
    {
        $dest = (object)$dest;
        try {
            if (in_array($dest->type, ['ftp', 'ftps'])) {
                return $this->testFtp($dest);
            } elseif ($dest->type === 'sftp') {
                return $this->testSftp($dest);
            }
            return ['success' => false, 'message' => 'Unsupported destination type'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function testFtp($dest)
    {
        $port = (int)($dest->port ?? 21);
        $ssl = $dest->type === 'ftps' || !empty($dest->ssl);
        $conn = $ssl ? @ftp_ssl_connect($dest->host, $port, 30) : @ftp_connect($dest->host, $port, 30);
        if (!$conn) return ['success' => false, 'message' => "Cannot connect to {$dest->host}:{$port}"];
        $ok = @ftp_login($conn, $dest->username, $dest->password);
        if (!$ok) { @ftp_close($conn); return ['success' => false, 'message' => 'Login failed']; }
        if (!empty($dest->passive)) @ftp_pasv($conn, true);
        if (!empty($dest->path) && $dest->path !== '/') {
            if (!@ftp_chdir($conn, $dest->path)) {
                if (!@ftp_mkdir($conn, $dest->path)) { @ftp_close($conn); return ['success' => false, 'message' => "Cannot access path: {$dest->path}"]; }
                @ftp_chdir($conn, $dest->path);
            }
        }
        $pwd = @ftp_pwd($conn);
        $files = @ftp_nlist($conn, '.');
        @ftp_close($conn);
        return [
            'success' => true,
            'message' => "Connected to {$dest->host}:{$port} at " . ($pwd ?: $dest->path),
            'pwd' => $pwd,
            'file_count' => $files ? count($files) : 0,
        ];
    }

    protected function testSftp($dest)
    {
        $port = (int)($dest->port ?? 22);
        $host = $dest->host;
        $user = escapeshellarg($dest->username);
        $pass = escapeshellarg($dest->password);
        $path = escapeshellarg($dest->path ?? '/');
        $cmd = "sshpass -p {$pass} ssh -o StrictHostKeyChecking=no -o ConnectTimeout=10 -p {$port} {$user}@{$host} 'ls {$path} 2>/dev/null | wc -l' 2>/dev/null";
        $output = trim(shell_exec($cmd) ?: '');
        if ($output === '' && !empty(trim(shell_exec("ssh -o StrictHostKeyChecking=no -o ConnectTimeout=10 -p {$port} {$user}@{$host} 'echo ok' 2>/dev/null") ?: ''))) {
            return ['success' => true, 'message' => "SFTP connected to {$host}:{$port}"];
        }
        if (is_numeric($output)) {
            return ['success' => true, 'message' => "SFTP connected to {$host}:{$port} ($output items)"];
        }
        return ['success' => false, 'message' => "Cannot connect SFTP to {$host}:{$port} - install sshpass"];
    }

    public function upload($localPath, $dest)
    {
        $dest = (object)$dest;
        $filename = basename($localPath);
        if (!is_file($localPath)) return ['success' => false, 'message' => 'Local file not found'];
        $result = in_array($dest->type, ['ftp', 'ftps']) ? $this->uploadFtp($localPath, $dest) : $this->uploadSftp($localPath, $dest);
        if ($result['success']) {
            $checksum = md5_file($localPath);
            $size = filesize($localPath);
            $this->logTransfer($dest->id, $filename, 'upload', 'completed', $size, $checksum);
        }
        return $result;
    }

    protected function uploadFtp($localPath, $dest)
    {
        $port = (int)($dest->port ?? 21);
        $ssl = $dest->type === 'ftps' || !empty($dest->ssl);
        $conn = $ssl ? @ftp_ssl_connect($dest->host, $port, 60) : @ftp_connect($dest->host, $port, 60);
        if (!$conn) return ['success' => false, 'message' => "Cannot connect to {$dest->host}:{$port}"];
        $ok = @ftp_login($conn, $dest->username, $dest->password);
        if (!$ok) { @ftp_close($conn); return ['success' => false, 'message' => 'FTP login failed']; }
        if (!empty($dest->passive)) @ftp_pasv($conn, true);
        if (!empty($dest->path) && $dest->path !== '/') {
            $this->ensureFtpPath($conn, $dest->path);
        }
        $remoteFile = basename($localPath);
        $start = microtime(true);
        $ok = @ftp_put($conn, $remoteFile, $localPath, FTP_BINARY);
        $elapsed = (int)((microtime(true) - $start) * 1000);
        @ftp_close($conn);
        return $ok
            ? ['success' => true, 'message' => "Uploaded {$remoteFile}", 'duration_ms' => $elapsed]
            : ['success' => false, 'message' => "FTP upload failed for {$remoteFile}"];
    }

    protected function uploadSftp($localPath, $dest)
    {
        $port = (int)($dest->port ?? 22);
        $host = $dest->host;
        $user = escapeshellarg($dest->username);
        $pass = escapeshellarg($dest->password);
        $local = escapeshellarg($localPath);
        $remotePath = rtrim($dest->path ?? '/', '/') . '/' . basename($localPath);
        $remote = escapeshellarg($remotePath);
        $start = microtime(true);
        exec("sshpass -p {$pass} scp -o StrictHostKeyChecking=no -P {$port} {$local} {$user}@{$host}:{$remote} 2>/dev/null", $out, $code);
        $elapsed = (int)((microtime(true) - $start) * 1000);
        return $code === 0
            ? ['success' => true, 'message' => "Uploaded via SFTP", 'duration_ms' => $elapsed]
            : ['success' => false, 'message' => 'SFTP upload failed (install sshpass)'];
    }

    public function download($remoteFile, $localPath, $dest)
    {
        $dest = (object)$dest;
        $result = in_array($dest->type, ['ftp', 'ftps']) ? $this->downloadFtp($remoteFile, $localPath, $dest) : $this->downloadSftp($remoteFile, $localPath, $dest);
        if ($result['success'] && is_file($localPath)) {
            $checksum = md5_file($localPath);
            $size = filesize($localPath);
            $this->logTransfer($dest->id, $remoteFile, 'download', 'completed', $size, $checksum);
        }
        return $result;
    }

    protected function downloadFtp($remoteFile, $localPath, $dest)
    {
        $port = (int)($dest->port ?? 21);
        $ssl = $dest->type === 'ftps' || !empty($dest->ssl);
        $conn = $ssl ? @ftp_ssl_connect($dest->host, $port, 60) : @ftp_connect($dest->host, $port, 60);
        if (!$conn) return ['success' => false, 'message' => "Cannot connect"];
        $ok = @ftp_login($conn, $dest->username, $dest->password);
        if (!$ok) { @ftp_close($conn); return ['success' => false, 'message' => 'Login failed']; }
        if (!empty($dest->passive)) @ftp_pasv($conn, true);
        $start = microtime(true);
        $ok = @ftp_get($conn, $localPath, $remoteFile, FTP_BINARY);
        $elapsed = (int)((microtime(true) - $start) * 1000);
        @ftp_close($conn);
        return $ok
            ? ['success' => true, 'message' => "Downloaded {$remoteFile}", 'duration_ms' => $elapsed]
            : ['success' => false, 'message' => "FTP download failed"];
    }

    protected function downloadSftp($remoteFile, $localPath, $dest)
    {
        $port = (int)($dest->port ?? 22);
        $user = escapeshellarg($dest->username);
        $pass = escapeshellarg($dest->password);
        $remote = escapeshellarg(rtrim($dest->path ?? '/', '/') . '/' . ltrim($remoteFile, '/'));
        $local = escapeshellarg($localPath);
        exec("sshpass -p {$pass} scp -o StrictHostKeyChecking=no -P {$port} {$user}@{$dest->host}:{$remote} {$local} 2>/dev/null", $out, $code);
        return $code === 0
            ? ['success' => true, 'message' => "Downloaded via SFTP"]
            : ['success' => false, 'message' => 'SFTP download failed'];
    }

    public function listFiles($dest, $remotePath = '.')
    {
        $dest = (object)$dest;
        if (in_array($dest->type, ['ftp', 'ftps'])) return $this->listFtp($dest, $remotePath);
        if ($dest->type === 'sftp') return $this->listSftp($dest, $remotePath);
        return [];
    }

    protected function listFtp($dest, $remotePath)
    {
        $port = (int)($dest->port ?? 21);
        $ssl = $dest->type === 'ftps' || !empty($dest->ssl);
        $conn = $ssl ? @ftp_ssl_connect($dest->host, $port, 15) : @ftp_connect($dest->host, $port, 15);
        if (!$conn) return [];
        @ftp_login($conn, $dest->username, $dest->password);
        if (!empty($dest->passive)) @ftp_pasv($conn, true);
        $files = @ftp_nlist($conn, $remotePath);
        @ftp_close($conn);
        return $files ?: [];
    }

    protected function listSftp($dest, $remotePath)
    {
        $port = (int)($dest->port ?? 22);
        $user = escapeshellarg($dest->username);
        $pass = escapeshellarg($dest->password);
        $path = escapeshellarg($remotePath);
        $cmd = "sshpass -p {$pass} ssh -o StrictHostKeyChecking=no -p {$port} {$user}@{$dest->host} 'ls -la {$path}' 2>/dev/null";
        $out = shell_exec($cmd);
        return $out ? explode("\n", trim($out)) : [];
    }

    protected function ensureFtpPath($conn, $path)
    {
        $parts = explode('/', trim($path, '/'));
        foreach ($parts as $part) {
            if (!@ftp_chdir($conn, $part)) {
                @ftp_mkdir($conn, $part);
                @ftp_chdir($conn, $part);
            }
        }
    }

    protected function logTransfer($destId, $filename, $action, $status, $size = 0, $checksum = null)
    {
        try {
            $this->pdo->prepare("INSERT INTO backup_logs (destination_id, action, status, file_path, file_size, checksum, created_at) VALUES (?,?,?,?,?,?,NOW())")
                ->execute([$destId, $action, $status, $filename, $size, $checksum]);
        } catch (\Exception $e) {}
    }

    public function enforceRetention($dest, $maxDailyBackups = 7)
    {
        $files = $this->listFiles($dest, $dest->path ?? '/');
        if (empty($files)) return ['deleted' => 0];
        $backupFiles = array_filter($files, fn($f) => preg_match('/\.tar\.gz$|\.zip$/', $f));
        $backupFiles = array_values($backupFiles);
        $count = count($backupFiles);
        $deleted = 0;
        if ($count > $maxDailyBackups) {
            $toDelete = array_slice($backupFiles, 0, $count - $maxDailyBackups);
            foreach ($toDelete as $f) {
                $dest = (object)$dest;
                if (in_array($dest->type, ['ftp', 'ftps'])) {
                    $port = (int)($dest->port ?? 21);
                    $ssl = $dest->type === 'ftps' || !empty($dest->ssl);
                    $conn = $ssl ? @ftp_ssl_connect($dest->host, $port, 15) : @ftp_connect($dest->host, $port, 15);
                    if ($conn) {
                        @ftp_login($conn, $dest->username, $dest->password);
                        @ftp_delete($conn, $f);
                        @ftp_close($conn);
                        $deleted++;
                    }
                }
            }
        }
        return ['deleted' => $deleted];
    }
}
