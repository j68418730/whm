<?php

namespace Admin\Services;

/**
 * Multi-destination backup engine.
 * Supports: local, ftp, ftps, sftp, rsync, s3, s3-compat, b2, googledrive (rclone), webdav, custom.
 */
class DestinationEngine
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
            switch ($dest->type) {
                case 'local':        return $this->testLocal($dest);
                case 'ftp':
                case 'ftps':         return $this->testFtp($dest);
                case 'sftp':         return $this->testSftp($dest);
                case 'rsync':        return $this->testRsync($dest);
                case 's3':
                case 's3-compat':
                case 'b2':           return $this->testS3($dest);
                case 'googledrive':  return $this->testGdrive($dest);
                case 'webdav':       return $this->testWebdav($dest);
                case 'custom':       return $this->testCustom($dest);
            }
            return ['success' => false, 'message' => 'Unsupported destination type: ' . ($dest->type ?? '?')];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function upload($localPath, $dest)
    {
        $dest = (object)$dest;
        $filename = basename($localPath);
        if (!is_file($localPath)) return ['success' => false, 'message' => 'Local file not found'];

        $start = microtime(true);
        $result = $this->uploadByType($localPath, $dest);
        $result['duration_ms'] = (int)((microtime(true) - $start) * 1000);

        if (!empty($result['success'])) {
            $checksum = md5_file($localPath);
            $size = filesize($localPath);
            $this->logTransfer($dest->id, $filename, 'upload', 'completed', $size, $checksum, $result['message'] ?? null, $result['duration_ms']);
            $this->bumpUsage($dest->id, $size);
        } else {
            $this->logTransfer($dest->id, $filename, 'upload', 'failed', 0, null, $result['message'] ?? null, $result['duration_ms']);
        }
        return $result;
    }

    public function download($remoteFile, $localPath, $dest)
    {
        $dest = (object)$dest;
        $filename = basename($remoteFile);
        $start = microtime(true);
        $result = $this->downloadByType($remoteFile, $localPath, $dest);
        $result['duration_ms'] = (int)((microtime(true) - $start) * 1000);

        if (!empty($result['success']) && is_file($localPath)) {
            $checksum = md5_file($localPath);
            $size = filesize($localPath);
            $this->logTransfer($dest->id, $filename, 'download', 'completed', $size, $checksum, $result['message'] ?? null, $result['duration_ms']);
        } else {
            $this->logTransfer($dest->id, $filename, 'download', 'failed', 0, null, $result['message'] ?? null, $result['duration_ms']);
        }
        return $result;
    }

    public function deleteFile($dest, $remoteFile)
    {
        $dest = (object)$dest;
        try {
            switch ($dest->type) {
                case 'local':
                    $path = rtrim($dest->path ?? '', '/') . '/' . ltrim($remoteFile, '/');
                    if (is_file($path)) @unlink($path);
                    return true;
                case 'ftp':
                case 'ftps': return $this->ftpDelete($dest, $remoteFile);
                case 'sftp':
                case 'rsync': return $this->sshDelete($dest, $remoteFile);
                case 's3':
                case 's3-compat':
                case 'b2': {
                    [$code] = $this->s3Request($dest, 'DELETE', $remoteFile);
                    return $code >= 200 && $code < 300;
                }
                case 'webdav': return $this->webdavDelete($dest, $remoteFile);
                case 'googledrive': return $this->gdriveDelete($dest, $remoteFile);
                case 'custom': $this->runTemplate($dest, 'delete', $remoteFile); return true;
            }
        } catch (\Exception $e) {}
        return false;
    }

    public function listFiles($dest, $remotePath = '')
    {
        $dest = (object)$dest;
        try {
            switch ($dest->type) {
                case 'local':
                    $dir = rtrim($dest->path ?? '', '/');
                    return array_map('basename', glob($dir . '/' . ($remotePath ? trim($remotePath, '/') . '/' : '') . '*.tar.gz') ?: []);
                case 'ftp':
                case 'ftps': return $this->ftpList($dest, $remotePath);
                case 'sftp':
                case 'rsync': return $this->sshList($dest, $remotePath);
                case 's3':
                case 's3-compat':
                case 'b2': return $this->s3ListNames($dest, $remotePath);
                case 'webdav': return $this->webdavList($dest, $remotePath);
                case 'googledrive': return $this->gdriveList($dest, $remotePath);
                case 'custom': return $this->customList($dest, $remotePath);
            }
        } catch (\Exception $e) {}
        return [];
    }

    public function getUsage($dest)
    {
        $dest = (object)$dest;
        $result = ['count' => 0, 'bytes' => 0];
        try {
            switch ($dest->type) {
                case 'local':
                    return $this->localUsage($dest);
                case 's3':
                case 's3-compat':
                case 'b2':
                    return $this->s3Usage($dest);
                case 'webdav':
                    $count = count($this->listFiles($dest));
                    return ['count' => $count, 'bytes' => 0];
                default:
                    $result['count'] = count($this->listFiles($dest));
            }
        } catch (\Exception $e) {}
        return $result;
    }

    public function enforceRetention($dest, $maxDailyBackups = 7)
    {
        $files = $this->listFiles($dest, '');
        $backupFiles = array_values(array_filter($files, fn($f) => preg_match('/\.tar\.gz$|\.zip$/i', $f)));
        sort($backupFiles);
        $count = count($backupFiles);
        $deleted = 0;
        if ($count > $maxDailyBackups) {
            $toDelete = array_slice($backupFiles, 0, $count - $maxDailyBackups);
            foreach ($toDelete as $f) {
                if ($this->deleteFile($dest, $f)) $deleted++;
            }
        }
        return ['deleted' => $deleted, 'kept' => count($backupFiles) - $deleted];
    }

    // ── Local ──
    protected function testLocal($dest)
    {
        $path = $dest->path ?? '';
        if (!$path || !is_dir($path)) return ['success' => false, 'message' => 'Local path not found: ' . $path];
        if (!is_writable($path)) return ['success' => false, 'message' => 'Local path not writable: ' . $path];
        $count = $this->localUsage($dest)['count'];
        return ['success' => true, 'message' => "Local storage ready at {$path}", 'file_count' => $count];
    }

    protected function localUsage($dest)
    {
        $dir = rtrim($dest->path ?? '', '/');
        $files = glob($dir . '/*.tar.gz') ?: [];
        $bytes = 0;
        foreach ($files as $f) $bytes += filesize($f);
        return ['count' => count($files), 'bytes' => $bytes];
    }

    protected function uploadByType($localPath, $dest)
    {
        switch ($dest->type) {
            case 'local': {
                $dir = rtrim($dest->path ?? '', '/');
                if (!is_dir($dir)) @mkdir($dir, 0755, true);
                $ok = @copy($localPath, $dir . '/' . basename($localPath));
                return $ok ? ['success' => true, 'message' => "Copied to {$dir}"] : ['success' => false, 'message' => 'Local copy failed'];
            }
            case 'ftp':
            case 'ftps': return $this->ftpUpload($localPath, $dest);
            case 'sftp': return $this->sftpUpload($localPath, $dest);
            case 'rsync': return $this->rsyncUpload($localPath, $dest);
            case 's3':
            case 's3-compat':
            case 'b2': return $this->s3Upload($localPath, $dest);
            case 'webdav': return $this->webdavUpload($localPath, $dest);
            case 'googledrive': return $this->gdriveUpload($localPath, $dest);
            case 'custom': return $this->customUpload($localPath, $dest);
        }
        return ['success' => false, 'message' => 'Unsupported type'];
    }

    protected function downloadByType($remoteFile, $localPath, $dest)
    {
        switch ($dest->type) {
            case 'local': {
                $src = rtrim($dest->path ?? '', '/') . '/' . ltrim($remoteFile, '/');
                $ok = is_file($src) && @copy($src, $localPath);
                return $ok ? ['success' => true, 'message' => 'Copied from local'] : ['success' => false, 'message' => 'Local file missing'];
            }
            case 'ftp':
            case 'ftps': return $this->ftpDownload($remoteFile, $localPath, $dest);
            case 'sftp': return $this->sftpDownload($remoteFile, $localPath, $dest);
            case 'rsync': return $this->rsyncDownload($remoteFile, $localPath, $dest);
            case 's3':
            case 's3-compat':
            case 'b2': return $this->s3Download($remoteFile, $localPath, $dest);
            case 'webdav': return $this->webdavDownload($remoteFile, $localPath, $dest);
            case 'googledrive': return $this->gdriveDownload($remoteFile, $localPath, $dest);
            case 'custom': return $this->customDownload($remoteFile, $localPath, $dest);
        }
        return ['success' => false, 'message' => 'Unsupported type'];
    }

    // ── FTP / FTPS ──
    protected function ftpConn($dest)
    {
        $port = (int)($dest->port ?? 21);
        $ssl = $dest->type === 'ftps' || !empty($dest->ssl);
        $conn = $ssl ? @ftp_ssl_connect($dest->host, $port, 30) : @ftp_connect($dest->host, $port, 30);
        if (!$conn) return null;
        if (!@ftp_login($conn, $dest->username, $dest->password)) { @ftp_close($conn); return null; }
        if (!empty($dest->passive)) @ftp_pasv($conn, true);
        return $conn;
    }

    protected function testFtp($dest)
    {
        $conn = $this->ftpConn($dest);
        if (!$conn) return ['success' => false, 'message' => "Cannot connect/login to {$dest->host}"];
        if (!empty($dest->path) && $dest->path !== '/' && !@ftp_chdir($conn, $dest->path)) {
            @ftp_close($conn);
            return ['success' => false, 'message' => "Cannot access path: {$dest->path}"];
        }
        $files = @ftp_nlist($conn, '.');
        @ftp_close($conn);
        return ['success' => true, 'message' => "Connected to {$dest->host}", 'file_count' => $files ? count($files) : 0];
    }

    protected function ftpUpload($localPath, $dest)
    {
        $conn = $this->ftpConn($dest);
        if (!$conn) return ['success' => false, 'message' => 'FTP connection failed'];
        $ok = $this->ftpEnsurePathAndPut($conn, $dest, $localPath, basename($localPath));
        @ftp_close($conn);
        return $ok ? ['success' => true, 'message' => "Uploaded via FTP"] : ['success' => false, 'message' => 'FTP upload failed'];
    }

    protected function ftpEnsurePathAndPut($conn, $dest, $localPath, $remoteFile)
    {
        if (!empty($dest->path) && $dest->path !== '/') {
            $parts = explode('/', trim($dest->path, '/'));
            foreach ($parts as $part) {
                if ($part === '') continue;
                if (!@ftp_chdir($conn, $part)) { @ftp_mkdir($conn, $part); @ftp_chdir($conn, $part); }
            }
        }
        return $dest->path && $dest->path !== '/'
            ? @ftp_put($conn, ltrim($remoteFile, '/'), $localPath, FTP_BINARY)
            : @ftp_put($conn, ltrim($remoteFile, '/'), $localPath, FTP_BINARY);
    }

    protected function ftpDownload($remoteFile, $localPath, $dest)
    {
        $conn = $this->ftpConn($dest);
        if (!$conn) return ['success' => false, 'message' => 'FTP connection failed'];
        $ok = @ftp_get($conn, $localPath, ltrim($remoteFile, '/'), FTP_BINARY);
        @ftp_close($conn);
        return $ok ? ['success' => true, 'message' => "Downloaded via FTP"] : ['success' => false, 'message' => 'FTP download failed'];
    }

    protected function ftpList($dest, $remotePath)
    {
        $conn = $this->ftpConn($dest);
        if (!$conn) return [];
        if ($remotePath) @ftp_chdir($conn, $remotePath);
        $files = @ftp_nlist($conn, '.') ?: [];
        @ftp_close($conn);
        if ($dest->path && $dest->path !== '/') {
            $files = array_map(fn($f) => str_replace(rtrim($dest->path, '/') . '/', '', basename($f)), $files);
        }
        return array_values(array_filter(array_map('basename', $files), fn($f) => $f !== '.' && $f !== '..'));
    }

    protected function ftpDelete($dest, $remoteFile)
    {
        $conn = $this->ftpConn($dest);
        if (!$conn) return false;
        if (!empty($dest->path) && $dest->path !== '/') @ftp_chdir($conn, $dest->path);
        $ok = @ftp_delete($conn, ltrim($remoteFile, '/'));
        @ftp_close($conn);
        return $ok;
    }

    // ── SFTP / Rsync (sshpass-based) ──
    protected function sshBaseCmd($dest, $suffix = '')
    {
        $port = (int)($dest->port ?? 22);
        $host = $dest->host;
        $user = escapeshellarg($dest->username);
        $opts = '-o StrictHostKeyChecking=no -o ConnectTimeout=15';
        if (!empty($dest->private_key)) {
            $opts .= ' -i ' . escapeshellarg($dest->private_key);
        }
        $ssh = "ssh {$opts} -p {$port}";
        if (empty($dest->private_key) && !empty($dest->password)) {
            $ssh = "sshpass -p " . escapeshellarg($dest->password) . " {$ssh}";
        }
        return $ssh . ' ' . $user . '@' . $host . ' ' . $suffix;
    }

    protected function testSftp($dest)
    {
        $cmd = $this->sshBaseCmd($dest, "'echo ok' 2>/dev/null");
        $out = trim(shell_exec($cmd) ?: '');
        if ($out === 'ok') return ['success' => true, 'message' => "SFTP connected to {$dest->host}"];
        return ['success' => false, 'message' => "Cannot connect SFTP to {$dest->host} (install sshpass or provide key)"];
    }

    protected function testRsync($dest)
    {
        $which = trim(shell_exec('which rsync 2>/dev/null') ?: '');
        if (!$which) return ['success' => false, 'message' => 'rsync binary not installed'];
        $cmd = $this->sshBaseCmd($dest, "'echo ok' 2>/dev/null");
        $out = trim(shell_exec($cmd) ?: '');
        return $out === 'ok'
            ? ['success' => true, 'message' => "Rsync target reachable at {$dest->host}"]
            : ['success' => false, 'message' => "Cannot reach rsync target {$dest->host}"];
    }

    protected function rsyncUpload($localPath, $dest)
    {
        $port = (int)($dest->port ?? 22);
        $rsh = 'ssh -o StrictHostKeyChecking=no -p ' . $port;
        if (!empty($dest->private_key)) $rsh .= ' -i ' . escapeshellarg($dest->private_key);
        elseif (!empty($dest->password)) $rsh = 'sshpass -p ' . escapeshellarg($dest->password) . ' ' . $rsh;
        $rsh = escapeshellarg($rsh);
        $remoteDir = rtrim($dest->path ?? '/', '/') . '/';
        $cmd = "rsync -az -e {$rsh} " . escapeshellarg($localPath) . ' ' . escapeshellarg($dest->username . '@' . $dest->host . ':' . $remoteDir . basename($localPath)) . ' 2>/dev/null';
        exec($cmd, $out, $code);
        return $code === 0
            ? ['success' => true, 'message' => 'Uploaded via rsync']
            : ['success' => false, 'message' => 'rsync upload failed'];
    }

    protected function rsyncDownload($remoteFile, $localPath, $dest)
    {
        $port = (int)($dest->port ?? 22);
        $rsh = 'ssh -o StrictHostKeyChecking=no -p ' . $port;
        if (!empty($dest->private_key)) $rsh .= ' -i ' . escapeshellarg($dest->private_key);
        elseif (!empty($dest->password)) $rsh = 'sshpass -p ' . escapeshellarg($dest->password) . ' ' . $rsh;
        $rsh = escapeshellarg($rsh);
        $remote = rtrim($dest->path ?? '/', '/') . '/' . ltrim($remoteFile, '/');
        $cmd = "rsync -az -e {$rsh} " . escapeshellarg($dest->username . '@' . $dest->host . ':' . $remote) . ' ' . escapeshellarg($localPath) . ' 2>/dev/null';
        exec($cmd, $out, $code);
        return $code === 0
            ? ['success' => true, 'message' => 'Downloaded via rsync']
            : ['success' => false, 'message' => 'rsync download failed'];
    }

    protected function sftpUpload($localPath, $dest)
    {
        if (!empty($dest->private_key)) {
            $scp = "scp -o StrictHostKeyChecking=no -P {$dest->port} -i " . escapeshellarg($dest->private_key);
        } else {
            $scp = "sshpass -p " . escapeshellarg($dest->password) . " scp -o StrictHostKeyChecking=no -P {$dest->port}";
        }
        $remote = rtrim($dest->path ?? '/', '/') . '/' . basename($localPath);
        $cmd = $scp . ' ' . escapeshellarg($localPath) . ' ' . escapeshellarg($dest->username . '@' . $dest->host . ':' . $remote) . ' 2>/dev/null';
        exec($cmd, $out, $code);
        return $code === 0
            ? ['success' => true, 'message' => 'Uploaded via SFTP']
            : ['success' => false, 'message' => 'SFTP upload failed (install sshpass or check key)'];
    }

    protected function sftpDownload($remoteFile, $localPath, $dest)
    {
        if (!empty($dest->private_key)) {
            $scp = "scp -o StrictHostKeyChecking=no -P {$dest->port} -i " . escapeshellarg($dest->private_key);
        } else {
            $scp = "sshpass -p " . escapeshellarg($dest->password) . " scp -o StrictHostKeyChecking=no -P {$dest->port}";
        }
        $remote = rtrim($dest->path ?? '/', '/') . '/' . ltrim($remoteFile, '/');
        $cmd = $scp . ' ' . escapeshellarg($dest->username . '@' . $dest->host . ':' . $remote) . ' ' . escapeshellarg($localPath) . ' 2>/dev/null';
        exec($cmd, $out, $code);
        return $code === 0
            ? ['success' => true, 'message' => 'Downloaded via SFTP']
            : ['success' => false, 'message' => 'SFTP download failed'];
    }

    protected function sshList($dest, $remotePath)
    {
        $dir = rtrim($dest->path ?? '/', '/') . ($remotePath ? '/' . trim($remotePath, '/') : '');
        $cmd = $this->sshBaseCmd($dest, "'ls -1 " . escapeshellarg($dir) . "' 2>/dev/null");
        $out = shell_exec($cmd) ?: '';
        $files = array_filter(explode("\n", trim($out)), fn($f) => $f !== '' && $f !== '.' && $f !== '..');
        return array_values($files);
    }

    protected function sshDelete($dest, $remoteFile)
    {
        $remote = rtrim($dest->path ?? '/', '/') . '/' . ltrim($remoteFile, '/');
        $cmd = $this->sshBaseCmd($dest, "'rm -f " . escapeshellarg($remote) . "' 2>/dev/null");
        shell_exec($cmd);
        return true;
    }

    // ── S3 / S3-compat / B2 (SigV4) ──
    protected function endpointFor($dest)
    {
        $ep = trim($dest->endpoint ?? '');
        if ($ep === '') {
            if ($dest->type === 'b2') $ep = 'https://s3.us-west-004.backblazeb2.com';
            else $ep = 'https://s3.' . ($dest->region ?: 'us-east-1') . '.amazonaws.com';
        }
        if (!preg_match('~^https?://~i', $ep)) $ep = 'https://' . $ep;
        return rtrim($ep, '/');
    }

    protected function s3CanonicalUri($dest, $key)
    {
        $parts = [];
        if (!empty($dest->bucket)) $parts[] = $dest->bucket;
        foreach (explode('/', trim((string)($dest->path ?? ''), '/')) as $seg) if ($seg !== '') $parts[] = $seg;
        foreach (explode('/', ltrim((string)$key, '/')) as $seg) if ($seg !== '') $parts[] = $seg;
        if (empty($parts)) return '/';
        return '/' . implode('/', array_map('rawurlencode', $parts));
    }

    protected function s3SignKey($secret, $date, $region)
    {
        $kDate = hash_hmac('sha256', $date, 'AWS4' . $secret, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);
        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }

    protected function s3Request($dest, $method, $key, $body = null, $contentType = null, array $query = [])
    {
        $endpoint = $this->endpointFor($dest);
        $parsed = parse_url($endpoint);
        $host = $parsed['host'] ?? 's3.amazonaws.com';
        $scheme = $parsed['scheme'] ?? 'https';
        $port = $parsed['port'] ?? null;
        $region = $dest->region ?: 'us-east-1';
        $timestamp = gmdate('Ymd\THis\Z');
        $dateStamp = gmdate('Ymd');
        $payloadHash = hash('sha256', $body ?? '');
        $hostHeader = $port ? "{$host}:{$port}" : $host;

        ksort($query);
        $canonicalQuery = '';
        $queryParts = [];
        foreach ($query as $k => $v) $queryParts[] = rawurlencode((string)$k) . '=' . rawurlencode((string)$v);
        if ($queryParts) $canonicalQuery = implode('&', $queryParts);

        $headers = [
            'host' => $hostHeader,
            'x-amz-content-sha256' => $payloadHash,
            'x-amz-date' => $timestamp,
        ];
        if ($contentType !== null && $contentType !== '') $headers['content-type'] = $contentType;
        ksort($headers);

        $canonicalHeaders = '';
        $signedHeaders = [];
        foreach ($headers as $k => $v) {
            $canonicalHeaders .= strtolower($k) . ':' . trim((string)$v) . "\n";
            $signedHeaders[] = strtolower($k);
        }
        $signedHeaderStr = implode(';', $signedHeaders);

        $canonicalRequest = "{$method}\n{$this->s3CanonicalUri($dest, $key)}\n{$canonicalQuery}\n{$canonicalHeaders}\n{$signedHeaderStr}\n{$payloadHash}";
        $scope = "{$dateStamp}/{$region}/s3/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$timestamp}\n{$scope}\n" . hash('sha256', $canonicalRequest);
        $signingKey = $this->s3SignKey($dest->secret_key, $dateStamp, $region);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);
        $auth = "AWS4-HMAC-SHA256 Credential={$dest->access_key}/{$scope}, SignedHeaders={$signedHeaderStr}, Signature={$signature}";

        $url = "{$scheme}://{$hostHeader}{$this->s3CanonicalUri($dest, $key)}";
        if ($canonicalQuery !== '') $url .= '?' . $canonicalQuery;

        $httpHeaders = [
            'x-amz-content-sha256: ' . $payloadHash,
            'x-amz-date: ' . $timestamp,
            'Authorization: ' . $auth,
        ];
        if ($contentType !== null && $contentType !== '') $httpHeaders[] = 'content-type: ' . $contentType;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        elseif (in_array($method, ['PUT', 'DELETE'])) curl_setopt($ch, CURLOPT_POSTFIELDS, '');

        $response = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$code, $response];
    }

    protected function testS3($dest)
    {
        if (empty($dest->bucket)) return ['success' => false, 'message' => 'Bucket is required'];
        if (empty($dest->access_key) || empty($dest->secret_key)) return ['success' => false, 'message' => 'Access key and secret key are required'];
        [$code, $body] = $this->s3Request($dest, 'GET', '', null, null, ['list-type' => '2', 'max-keys' => '1']);
        if ($code >= 200 && $code < 300) return ['success' => true, 'message' => "Connected to bucket {$dest->bucket}"];
        $err = @simplexml_load_string((string)$body);
        $msg = $err && isset($err->Message) ? (string)$err->Message : strip_tags((string)$body);
        return ['success' => false, 'message' => "S3 connection failed ({$code}): " . substr($msg, 0, 180)];
    }

    protected function s3Upload($localPath, $dest)
    {
        $body = file_get_contents($localPath);
        $contentType = str_ends_with($localPath, '.gz') ? 'application/gzip' : 'application/octet-stream';
        [$code, $resp] = $this->s3Request($dest, 'PUT', basename($localPath), $body, $contentType);
        if ($code >= 200 && $code < 300) return ['success' => true, 'message' => "Uploaded to {$dest->bucket}"];
        return ['success' => false, 'message' => "S3 upload failed ({$code}): " . substr(strip_tags((string)$resp), 0, 180)];
    }

    protected function s3Download($remoteFile, $localPath, $dest)
    {
        [$code, $resp] = $this->s3Request($dest, 'GET', $remoteFile);
        if ($code >= 200 && $code < 300) {
            if (@file_put_contents($localPath, $resp) === false) return ['success' => false, 'message' => 'Cannot write local file'];
            return ['success' => true, 'message' => "Downloaded from {$dest->bucket}"];
        }
        return ['success' => false, 'message' => "S3 download failed ({$code})"];
    }

    protected function s3ListNames($dest, $remotePath)
    {
        $names = $this->s3ListAll($dest);
        return array_map(fn($n) => basename($n), $names);
    }

    protected function s3ListAll($dest)
    {
        $names = [];
        $prefix = $this->s3Prefix($dest, '');
        $token = '';
        $suffix = ($dest->path ?? '') !== '' ? trim((string)$dest->path, '/') . '/' : '';
        do {
            $q = ['list-type' => '2', 'max-keys' => '1000'];
            if ($prefix !== '') $q['prefix'] = $prefix;
            if ($token !== '') $q['continuation-token'] = $token;
            [$code, $body] = $this->s3Request($dest, 'GET', '', null, null, $q);
            if ($code < 200 || $code >= 300) break;
            $xml = @simplexml_load_string((string)$body);
            if (!$xml) break;
            if (isset($xml->Contents)) {
                foreach ($xml->Contents as $c) {
                    $key = (string)$c->Key;
                    $names[] = ltrim(substr($key, strlen($suffix)), '/');
                }
            }
            $token = isset($xml->IsTruncated) && (string)$xml->IsTruncated === 'true' ? (string)($xml->NextContinuationToken ?? '') : '';
        } while ($token !== '');
        return $names;
    }

    protected function s3Prefix($dest, $key)
    {
        return trim((string)($dest->path ?? ''), '/') . ($key ? '/' . ltrim($key, '/') : '');
    }

    protected function s3Usage($dest)
    {
        $bytes = 0;
        $count = 0;
        $suffix = ($dest->path ?? '') !== '' ? trim((string)$dest->path, '/') . '/' : '';
        $prefix = $this->s3Prefix($dest, '');
        $token = '';
        do {
            $q = ['list-type' => '2', 'max-keys' => '1000'];
            if ($prefix !== '') $q['prefix'] = $prefix;
            if ($token !== '') $q['continuation-token'] = $token;
            [$code, $body] = $this->s3Request($dest, 'GET', '', null, null, $q);
            if ($code < 200 || $code >= 300) break;
            $xml = @simplexml_load_string((string)$body);
            if (!$xml) break;
            if (isset($xml->Contents)) {
                foreach ($xml->Contents as $c) {
                    $count++;
                    $bytes += (int)($c->Size ?? 0);
                }
            }
            $token = isset($xml->IsTruncated) && (string)$xml->IsTruncated === 'true' ? (string)($xml->NextContinuationToken ?? '') : '';
        } while ($token !== '');
        return ['count' => $count, 'bytes' => $bytes];
    }

    // ── WebDAV ──
    protected function wdavUrl($dest, $name = '')
    {
        $base = rtrim($dest->host ?? '', '/');
        if (!empty($dest->path)) $base .= '/' . trim($dest->path, '/');
        if ($name !== '') $base .= '/' . ltrim($name, '/');
        return $base;
    }

    protected function wdavCurl($dest, $url, $customMethod = null, $body = null, $localIn = null, $localOut = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $headers = [];
        if (!empty($dest->username)) curl_setopt($ch, CURLOPT_USERPWD, $dest->username . ':' . ($dest->password ?? ''));
        if ($customMethod) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $customMethod);
        if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        if ($localIn) {
            $fh = fopen($localIn, 'rb');
            if ($fh) { curl_setopt($ch, CURLOPT_PUT, true); curl_setopt($ch, CURLOPT_INFILE, $fh); curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localIn)); }
        }
        if ($localOut) {
            $oh = fopen($localOut, 'wb');
            if ($oh) curl_setopt($ch, CURLOPT_FILE, $oh);
        }
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (isset($fh) && is_resource($fh)) fclose($fh);
        if (isset($oh) && is_resource($oh)) fclose($oh);
        return [$code, $resp];
    }

    protected function testWebdav($dest)
    {
        [$code, $resp] = $this->wdavCurl($dest, $this->wdavUrl($dest));
        if ($code === 401 && empty($dest->username)) return ['success' => false, 'message' => 'WebDAV requires credentials'];
        if ($code >= 200 && $code < 400) return ['success' => true, 'message' => 'WebDAV reachable'];
        return ['success' => false, 'message' => "WebDAV connection failed (HTTP $code)"];
    }

    protected function webdavUpload($localPath, $dest)
    {
        [$code] = $this->wdavCurl($dest, $this->wdavUrl($dest, basename($localPath)), 'PUT', null, $localPath);
        if ($code >= 200 && $code < 300) return ['success' => true, 'message' => 'Uploaded via WebDAV'];
        if ($code === 409 || $code === 412) {
            // parent dir missing - try MKCOL one level
            $dir = dirname($this->wdavUrl($dest, basename($localPath)));
            $this->wdavCurl($dest, $dir, 'MKCOL');
            [$code] = $this->wdavCurl($dest, $this->wdavUrl($dest, basename($localPath)), 'PUT', null, $localPath);
        }
        return $code >= 200 && $code < 300
            ? ['success' => true, 'message' => 'Uploaded via WebDAV']
            : ['success' => false, 'message' => "WebDAV upload failed (HTTP $code)"];
    }

    protected function webdavDownload($remoteFile, $localPath, $dest)
    {
        [$code] = $this->wdavCurl($dest, $this->wdavUrl($dest, $remoteFile), null, null, null, $localPath);
        return $code >= 200 && $code < 300
            ? ['success' => true, 'message' => 'Downloaded via WebDAV']
            : ['success' => false, 'message' => "WebDAV download failed (HTTP $code)"];
    }

    protected function webdavList($dest, $remotePath)
    {
        $url = $this->wdavUrl($dest, $remotePath);
        [$code, $body] = $this->wdavCurl($dest, $url, 'PROPFIND');
        if ($code !== 207 && $code !== 200) return [];
        $names = [];
        if (preg_match_all('~<D?:href>([^<]+)</D?:href>~i', (string)$body, $m)) {
            foreach ($m[1] as $h) {
                $name = rawurldecode(basename(rtrim($h, '/')));
                if ($name !== '' && $name !== '.' && $name !== '..') $names[] = $name;
            }
        }
        return array_unique(array_values($names));
    }

    protected function webdavDelete($dest, $remoteFile)
    {
        [$code] = $this->wdavCurl($dest, $this->wdavUrl($dest, $remoteFile), 'DELETE');
        return $code >= 200 && $code < 300;
    }

    // ── Google Drive (rclone) ──
    protected function gdriveRemote($dest)
    {
        return trim($dest->username ?? '');
    }

    protected function testGdrive($dest)
    {
        $which = trim(shell_exec('which rclone 2>/dev/null') ?: '');
        if (!$which) return ['success' => false, 'message' => 'rclone not installed on server'];
        $remote = $this->gdriveRemote($dest);
        if (!$remote) return ['success' => false, 'message' => 'Rclone remote name required (Username field)'];
        exec("rclone lsd " . escapeshellarg($remote . ':') . " 2>&1", $out, $code);
        return $code === 0
            ? ['success' => true, 'message' => "rclone remote '{$remote}' reachable"]
            : ['success' => false, 'message' => "rclone remote unreachable: " . (implode(' | ', array_slice($out, 0, 2)))];
    }

    protected function gdriveUpload($localPath, $dest)
    {
        $remote = $this->gdriveRemote($dest);
        $path = rtrim($dest->path ?? '', '/');
        $destStr = $remote . ':' . ($path ? '/' . $path : '');
        $cmd = "rclone copyto " . escapeshellarg($localPath) . ' ' . escapeshellarg($destStr . '/' . basename($localPath)) . ' 2>&1';
        exec($cmd, $out, $code);
        return $code === 0
            ? ['success' => true, 'message' => 'Uploaded via rclone']
            : ['success' => false, 'message' => 'rclone upload failed: ' . implode(' | ', array_slice($out, 0, 2))];
    }

    protected function gdriveDownload($remoteFile, $localPath, $dest)
    {
        $remote = $this->gdriveRemote($dest);
        $path = rtrim($dest->path ?? '', '/');
        $remoteStr = $remote . ':' . ($path ? '/' . $path . '/' : '/') . ltrim($remoteFile, '/');
        $cmd = "rclone copyto " . escapeshellarg($remoteStr) . ' ' . escapeshellarg($localPath) . ' 2>&1';
        exec($cmd, $out, $code);
        return $code === 0
            ? ['success' => true, 'message' => 'Downloaded via rclone']
            : ['success' => false, 'message' => 'rclone download failed'];
    }

    protected function gdriveList($dest, $remotePath)
    {
        $remote = $this->gdriveRemote($dest);
        $path = rtrim($dest->path ?? '', '/') . ($remotePath ? '/' . trim($remotePath, '/') : '');
        $cmd = "rclone lsf " . escapeshellarg($remote . ':' . ($path ? '/' . $path : '')) . ' 2>/dev/null';
        $out = trim(shell_exec($cmd) ?: '');
        return array_values(array_filter(explode("\n", $out), fn($f) => $f !== ''));
    }

    protected function gdriveDelete($dest, $remoteFile)
    {
        $remote = $this->gdriveRemote($dest);
        $path = rtrim($dest->path ?? '', '/');
        $str = $remote . ':' . ($path ? '/' . $path . '/' : '/') . ltrim($remoteFile, '/');
        exec("rclone deletefile " . escapeshellarg($str) . ' 2>&1', $out, $code);
        return $code === 0;
    }

    // ── Custom ──
    protected function renderTemplate($dest, $op, $remoteFile = null, $localPath = null)
    {
        $tpl = trim($dest->command_template ?? '');
        $map = [
            '{local}'  => $localPath ? escapeshellarg($localPath) : '',
            '{file}'   => $remoteFile ? escapeshellarg($remoteFile) : '',
            '{remote}' => $remoteFile ? escapeshellarg($remoteFile) : '',
            '{path}'   => escapeshellarg(rtrim($dest->path ?? '/', '/')),
            '{user}'   => $dest->username ? escapeshellarg($dest->username) : '',
            '{host}'   => $dest->host ? escapeshellarg($dest->host) : '',
            '{port}'   => (int)($dest->port ?? 22),
            '{password}' => $dest->password ? escapeshellarg($dest->password) : '',
        ];
        if (strpos($tpl, '{op}') !== false) $tpl = str_replace('{op}', $op, $tpl);
        $tpl = str_replace(array_keys($map), array_values($map), $tpl);
        if ($op === 'upload' && strpos($tpl, '{local}') === false) {
            $tpl .= ' ' . escapeshellarg($localPath);
        }
        return trim($tpl);
    }

    protected function testCustom($dest)
    {
        $tpl = trim($dest->command_template ?? '');
        if (!$tpl) return ['success' => false, 'message' => 'No command template provided'];
        return ['success' => true, 'message' => 'Custom destination configured (execute Run Now to verify)'];
    }

    protected function customUpload($localPath, $dest)
    {
        $cmd = $this->renderTemplate($dest, 'upload', basename($localPath), $localPath);
        if (!$cmd) return ['success' => false, 'message' => 'No command template provided'];
        exec($cmd . ' 2>&1', $out, $code);
        return $code === 0
            ? ['success' => true, 'message' => 'Custom upload command executed']
            : ['success' => false, 'message' => 'Custom command failed: ' . implode(' | ', array_slice($out, 0, 3))];
    }

    protected function customDownload($remoteFile, $localPath, $dest)
    {
        $cmd = $this->renderTemplate($dest, 'download', $remoteFile, $localPath);
        if (!$cmd) return ['success' => false, 'message' => 'No command template provided'];
        exec($cmd . ' 2>&1', $out, $code);
        return $code === 0
            ? ['success' => true, 'message' => 'Custom download command executed']
            : ['success' => false, 'message' => 'Custom command failed: ' . implode(' | ', array_slice($out, 0, 3))];
    }

    protected function customList($dest, $remotePath)
    {
        $cmd = $this->renderTemplate($dest, 'list', null, null);
        if ($cmd && (strpos($cmd, 'ls') !== false || strpos($cmd, 'find') !== false)) {
            $out = trim(shell_exec($cmd . ' 2>/dev/null') ?: '');
            return array_values(array_filter(explode("\n", $out), fn($f) => $f !== '' && preg_match('/\.tar\.gz$|\.zip$/i', $f)));
        }
        return [];
    }

    // ── Logging / usage ──
    protected function logTransfer($destId, $filename, $action, $status, $size = 0, $checksum = null, $message = null, $durationMs = 0)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO backup_logs (destination_id, action, status, message, file_path, file_size, checksum, duration_ms, created_at) VALUES (?,?,?,?,?,?,?,?,NOW())");
            $stmt->execute([$destId, $action, $status, $message, $filename, $size, $checksum, $durationMs]);
        } catch (\Exception $e) {}
    }

    protected function bumpUsage($destId, $bytes)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE backup_destinations SET used_bytes = used_bytes + ? WHERE id = ?");
            $stmt->execute([$bytes, $destId]);
        } catch (\Exception $e) {}
    }
}