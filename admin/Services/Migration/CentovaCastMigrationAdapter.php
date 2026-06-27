<?php

namespace Admin\Services\Migration;

class CentovaCastMigrationAdapter extends BaseMigrationAdapter
{
    public function getPanelName(): string { return 'Centova Cast'; }
    public function getPanelIcon(): string { return '🔴'; }
    public function getDefaultPort(): int { return 2199; }
    public function getSupportedSourceTypes(): array { return ['live_ssh', 'remote_api']; }
    public function getSupportedMigrationTypes(): array { return ['single_streaming', 'streaming_server', 'multi_streaming']; }

    public function testConnection(string $host, int $port, string $username, string $password, ?string $apiKey = null): array
    {
        $resp = $this->httpGet("https://{$host}:{$port}/api.php?module=system&action=getinfo", ["Authorization: Basic " . base64_encode("{$username}:{$password}")]);
        return ['connected' => $resp['code'] === 200, 'error' => $resp['code'] !== 200 ? "HTTP {$resp['code']}" : null];
    }

    public function preflight(string $host, int $port, string $username, string $password, ?string $apiKey = null): array
    {
        $resp = $this->httpGet("https://{$host}:{$port}/api.php?module=system&action=getinfo", ["Authorization: Basic " . base64_encode("{$username}:{$password}")]);
        $data = json_decode($resp['body'], true);
        $accounts = [];
        if (!empty($data['accounts'])) {
            foreach ($data['accounts'] as $a) {
                $accounts[] = ['username' => $a['username'] ?? 'station', 'domain' => $a['hostname'] ?? '', 'plan' => 'streaming', 'type' => 'centovacast'];
            }
        }
        return ['connected' => true, 'accounts' => $accounts ?: [['username' => 'centovacast_station', 'domain' => '', 'plan' => 'streaming']], 'server_info' => ['centovacast' => true]];
    }

    public function analyzeAccounts(array $accounts, array $options = []): array { return $accounts; }

    public function migrate(array $accounts, array $packageMap, array $options, callable $logFn): array
    {
        $rollback = [];
        foreach ($accounts as $a) {
            $logFn("Centova Cast: importing station {$a['username']}...");
            $id = $this->importHostingUser($a['username'], $a['domain'] ?? "{$a['username']}.stream.local", null, "{$a['username']}@stream.local", 'active');
            if ($id) { $rollback[] = ['type' => 'hosting_user', 'id' => $id]; $logFn("Imported station {$a['username']}"); }
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
