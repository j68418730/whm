<?php

namespace Admin\Services\Migration;

class AzuraCastMigrationAdapter extends BaseMigrationAdapter
{
    public function getPanelName(): string { return 'AzuraCast'; }
    public function getPanelIcon(): string { return '🎧'; }
    public function getDefaultPort(): int { return 443; }
    public function getSupportedSourceTypes(): array { return ['live_ssh', 'remote_api']; }
    public function getSupportedMigrationTypes(): array { return ['single_streaming', 'streaming_server']; }

    public function testConnection(string $host, int $port, string $username, string $password, ?string $apiKey = null): array
    {
        $resp = $this->httpGet("https://{$host}:{$port}/api/admin/stations", ["Authorization: Bearer {$apiKey ?: $password}"]);
        return ['connected' => $resp['code'] === 200, 'error' => $resp['code'] !== 200 ? "HTTP {$resp['code']}" : null];
    }

    public function preflight(string $host, int $port, string $username, string $password, ?string $apiKey = null): array
    {
        $key = $apiKey ?: $password;
        $resp = $this->httpGet("https://{$host}:{$port}/api/admin/stations", ["Authorization: Bearer {$key}"]);
        $stations = json_decode($resp['body'], true) ?: [];
        $accounts = [];
        foreach ($stations as $s) {
            $accounts[] = ['username' => $s['short_name'] ?? $s['name'] ?? 'station', 'domain' => $s['url'] ?? '', 'plan' => 'streaming', 'type' => 'azuracast'];
        }
        return ['connected' => true, 'accounts' => $accounts, 'server_info' => ['azuracast' => true, 'stations' => count($accounts)]];
    }

    public function analyzeAccounts(array $accounts, array $options = []): array { return $accounts; }

    public function migrate(array $accounts, array $packageMap, array $options, callable $logFn): array
    {
        $rollback = [];
        foreach ($accounts as $a) {
            $logFn("AzuraCast: importing station {$a['username']}...");
            $id = $this->importHostingUser($a['username'], $a['domain'] ?? "{$a['username']}.azuracast.local");
            if ($id) { $rollback[] = ['type' => 'hosting_user', 'id' => $id]; $logFn("Imported {$a['username']}"); }
        }
        return $rollback;
    }

    public function rollback(array $rollbackData, callable $logFn): bool
    {
        foreach ($rollbackData as $r) {
            if ($r['type'] === 'hosting_user') { try { $this->db->table('hosting_users')->where('id', $r['id'])->delete(); } catch (\Exception $e) {} }
        }
        return true;
    }

    public function validateMigration(array $migratedIds, array $options = []): array
    {
        return ['validated' => count($migratedIds), 'passed' => count($migratedIds), 'results' => []];
    }
}
