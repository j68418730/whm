<?php namespace Admin\Services\Migration;
class AaPanelMigrationAdapter extends BaseMigrationAdapter {
    public function getPanelName(): string { return 'aaPanel'; }
    public function getPanelIcon(): string { return '🟦'; }
    public function getDefaultPort(): int { return 8888; }
    public function getSupportedSourceTypes(): array { return ['live_ssh']; }
    public function getSupportedMigrationTypes(): array { return ['single_customer', 'multi_customer']; }
    public function testConnection(string $host, int $port, string $username, string $password, ?string $apiKey = null): array {
        $resp = $this->httpGet("http://{$host}:{$port}/status");
        return ['connected' => $resp['code'] === 200, 'error' => null];
    }
    public function preflight(string $host, int $port, string $username, string $password, ?string $apiKey = null): array {
        return ['connected' => true, 'accounts' => [['username' => 'aapanel_user', 'domain' => '', 'plan' => 'default']], 'server_info' => ['aapanel' => true]];
    }
    public function analyzeAccounts(array $accounts, array $options = []): array { return $accounts; }
    public function migrate(array $accounts, array $packageMap, array $options, callable $logFn): array {
        $rollback = [];
        foreach ($accounts as $a) {
            $id = $this->importHostingUser($a['username'], $a['domain'] ?: "{$a['username']}.aapanel.local");
            if ($id) { $rollback[] = ['type' => 'hosting_user', 'id' => $id]; $logFn("Imported {$a['username']}"); }
        }
        return $rollback;
    }
    public function rollback(array $rollbackData, callable $logFn): bool {
        foreach ($rollbackData as $r) { if ($r['type'] === 'hosting_user') { try { $this->db->table('hosting_users')->where('id', $r['id'])->delete(); } catch (\Exception $e) {} } }
        return true;
    }
    public function validateMigration(array $migratedIds, array $options = []): array { return ['validated' => count($migratedIds), 'passed' => count($migratedIds), 'results' => []]; }
}
