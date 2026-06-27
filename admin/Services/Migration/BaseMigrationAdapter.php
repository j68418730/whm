<?php

namespace Admin\Services\Migration;

abstract class BaseMigrationAdapter implements MigrationInterface
{
    protected $db;
    protected $log = [];
    protected $errors = [];

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
    }

    public function getSupportedSourceTypes(): array
    {
        return ['live_ssh', 'local_backup', 'remote_backup'];
    }

    public function getSupportedMigrationTypes(): array
    {
        return ['single_website', 'single_customer', 'multi_customer', 'entire_server'];
    }

    protected function log($msg) { $this->log[] = $msg; }
    protected function error($msg) { $this->errors[] = $msg; $this->log("ERROR: {$msg}"); }

    protected function httpGet(string $url, array $headers = [], int $timeout = 30): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false, CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        return ['code' => $httpCode, 'body' => $resp, 'error' => $error];
    }

    protected function httpPost(string $url, array $postData, array $headers = [], int $timeout = 30): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false, CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => is_array($postData) ? http_build_query($postData) : $postData,
            CURLOPT_TIMEOUT => $timeout, CURLOPT_HTTPHEADER => $headers,
        ]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        return ['code' => $httpCode, 'body' => $resp, 'error' => $error];
    }

    protected function importHostingUser(string $username, string $domain, ?int $packageId = null, string $email = '', string $status = 'active'): ?int
    {
        try {
            $existing = $this->db->table('hosting_users')->where('username', $username)->first();
            if ($existing) { $this->log("Skipping existing user: {$username}"); return $existing->id; }
            return $this->db->table('hosting_users')->insertGetId([
                'reseller_id' => 1, 'package_id' => $packageId, 'username' => $username,
                'domain' => $domain, 'ip' => $_SERVER['SERVER_ADDR'] ?? '127.0.0.1',
                'password_hash' => '', 'email' => $email ?: "{$username}@imported.local",
                'status' => $status,
            ]);
        } catch (\Exception $e) { $this->error("Import user failed: {$e->getMessage()}"); return null; }
    }

    protected function execSsh(string $host, int $port, string $user, string $pass, string $cmd): array
    {
        $escaped = escapeshellarg($cmd);
        $fullCmd = "sshpass -p " . escapeshellarg($pass) . " ssh -o StrictHostKeyChecking=no -o ConnectTimeout=15 -p {$port} {$user}@{$host} {$escaped} 2>&1";
        exec($fullCmd, $output, $code);
        return ['output' => $output, 'code' => $code];
    }

    public function getConversionRules(): array
    {
        return [
            'package_limits' => ['disk', 'bandwidth', 'databases', 'email_accounts', 'ftp_accounts'],
            'user_permissions' => ['ssh', 'shell', 'cron', 'backup'],
            'php_versions' => ['5.6', '7.0', '7.1', '7.2', '7.3', '7.4', '8.0', '8.1', '8.2', '8.3', '8.4'],
            'dns' => ['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS', 'SRV', 'SOA'],
            'ssl' => ['letsencrypt', 'commercial', 'self_signed'],
            'streaming' => ['auto_dj', 'playlists', 'mount_points', 'source_passwords'],
            'email' => ['accounts', 'forwarders', 'autoresponders', 'spam_filters'],
            'database' => ['mysql', 'mariadb', 'postgresql'],
        ];
    }

    public function getLog(): array { return $this->log; }
    public function getErrors(): array { return $this->errors; }
}
