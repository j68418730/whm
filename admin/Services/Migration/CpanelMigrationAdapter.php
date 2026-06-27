<?php

namespace Admin\Services\Migration;

class CpanelMigrationAdapter extends BaseMigrationAdapter
{
    public function getPanelName(): string { return 'cPanel / WHM'; }
    public function getPanelIcon(): string { return '🟠'; }
    public function getDefaultPort(): int { return 2087; }

    public function testConnection(string $host, int $port, string $username, string $password, ?string $apiKey = null): array
    {
        $resp = $this->httpGet("https://{$host}:{$port}/json-api/version?api.version=1", ["Authorization: whm {$username}:{$apiKey ?: $password}"]);
        $connected = $resp['code'] === 200 && !empty($resp['body']);
        return ['connected' => $connected, 'error' => $connected ? null : "HTTP {$resp['code']}: {$resp['error']}", 'version' => json_decode($resp['body'], true)['version'] ?? '?'];
    }

    public function preflight(string $host, int $port, string $username, string $password, ?string $apiKey = null): array
    {
        $token = $apiKey ?: $password;
        $resp = $this->httpGet("https://{$host}:{$port}/json-api/listaccts?api.version=1", ["Authorization: whm {$username}:{$token}"]);
        $data = json_decode($resp['body'], true);
        $accounts = [];
        foreach ($data['data']['acct'] ?? [] as $a) {
            $accounts[] = [
                'username' => $a['user'], 'domain' => $a['domain'] ?? '', 'plan' => $a['plan'] ?? '',
                'email' => $a['email'] ?? '', 'disk_used' => $a['diskused'] ?? 0, 'disk_limit' => $a['disklimit'] ?? 0,
                'php_version' => $a['phpversion'] ?? '', 'ip' => $a['ip'] ?? '',
            ];
        }
        return ['connected' => true, 'accounts' => $accounts, 'server_info' => ['cpanel' => true, 'version' => $data['metadata']['version'] ?? '?', 'total_accounts' => count($accounts)]];
    }

    public function analyzeAccounts(array $accounts, array $options = []): array
    {
        return array_map(function($a) {
            $problems = [];
            if (($a['disk_used'] ?? 0) > 5000) $problems[] = 'Large account';
            return ['username' => $a['username'], 'domain' => $a['domain'], 'issues' => $problems];
        }, $accounts);
    }

    public function migrate(array $accounts, array $packageMap, array $options, callable $logFn): array
    {
        $rollback = [];
        $host = $options['host']; $port = $options['port']; $token = $options['api_key'] ?? $options['pass'];
        foreach ($accounts as $a) {
            $logFn("Migrating {$a['username']}...");
            $pkgId = null;
            if (!empty($a['plan']) && isset($packageMap[$a['plan']])) {
                $ce = new ConversionEngine();
                $pkgId = $ce->convertPackage(['disk' => $a['disk_limit'] ?? 0], $a['plan']);
            }
            $id = $this->importHostingUser($a['username'], $a['domain'] ?? '', $pkgId, $a['email'] ?? '');
            if ($id) { $rollback[] = ['type' => 'hosting_user', 'id' => $id, 'username' => $a['username']]; $logFn("Imported {$a['username']} (ID: {$id})"); }
            else $logFn("Failed to import {$a['username']}");
        }
        return $rollback;
    }

    public function rollback(array $rollbackData, callable $logFn): bool
    {
        foreach ($rollbackData as $r) {
            if ($r['type'] === 'hosting_user') {
                try { $this->db->table('hosting_users')->where('id', $r['id'])->delete(); $logFn("Rolled back: {$r['username']}"); } catch (\Exception $e) {}
            }
        }
        return true;
    }

    public function validateMigration(array $migratedIds, array $options = []): array
    {
        $results = [];
        foreach ($migratedIds as $id) {
            $user = $this->db->table('hosting_users')->where('id', $id)->first();
            $results[] = ['id' => $id, 'username' => $user->username ?? '?', 'exists' => !!$user, 'valid' => !!$user];
        }
        return ['validated' => count($results), 'passed' => count(array_filter($results, fn($r) => $r['valid'])), 'results' => $results];
    }

    public function getSupportedSourceTypes(): array { return ['live_ssh', 'remote_api', 'local_backup']; }
    public function getSupportedMigrationTypes(): array { return ['single_customer', 'multi_customer', 'entire_server', 'email_only', 'database_only', 'dns_only']; }
}
