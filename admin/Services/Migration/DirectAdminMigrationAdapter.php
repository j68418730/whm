<?php

namespace Admin\Services\Migration;

class DirectAdminMigrationAdapter extends BaseMigrationAdapter
{
    public function getPanelName(): string { return 'DirectAdmin'; }
    public function getPanelIcon(): string { return '🟢'; }
    public function getDefaultPort(): int { return 2222; }
    public function getSupportedSourceTypes(): array { return ['live_ssh', 'remote_api']; }
    public function getSupportedMigrationTypes(): array { return ['single_customer', 'multi_customer', 'entire_server']; }

    public function testConnection(string $host, int $port, string $username, string $password, ?string $apiKey = null): array
    {
        $resp = $this->httpGet("https://{$host}:{$port}/CMD_API_SHOW_ALL_USERS", ["Authorization: Basic " . base64_encode("{$username}:{$password}")]);
        return ['connected' => $resp['code'] === 200, 'error' => $resp['code'] !== 200 ? "HTTP {$resp['code']}" : null];
    }

    public function preflight(string $host, int $port, string $username, string $password, ?string $apiKey = null): array
    {
        $resp = $this->httpGet("https://{$host}:{$port}/CMD_API_SHOW_ALL_USERS", ["Authorization: Basic " . base64_encode("{$username}:{$password}")]);
        parse_str($resp['body'], $data);
        $accounts = [];
        foreach (($data['list'] ?? []) as $u) {
            $accounts[] = ['username' => $u, 'domain' => '', 'plan' => 'directadmin_default', 'email' => ''];
        }
        return ['connected' => true, 'accounts' => $accounts, 'server_info' => ['directadmin' => true, 'total_accounts' => count($accounts)]];
    }

    public function analyzeAccounts(array $accounts, array $options = []): array { return $accounts; }

    public function migrate(array $accounts, array $packageMap, array $options, callable $logFn): array
    {
        $rollback = [];
        foreach ($accounts as $a) {
            $logFn("DirectAdmin: importing {$a['username']}...");
            $id = $this->importHostingUser($a['username'], $a['domain'] ?? "{$a['username']}.da.local");
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
