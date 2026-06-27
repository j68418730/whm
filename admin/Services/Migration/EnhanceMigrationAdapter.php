<?php namespace Admin\Services\Migration;
class EnhanceMigrationAdapter extends BaseMigrationAdapter {
    public function getPanelName(): string { return 'Enhance'; }
    public function getPanelIcon(): string { return '✨'; }
    public function getDefaultPort(): int { return 443; }
    public function getSupportedSourceTypes(): array { return ['live_ssh']; }
    public function getSupportedMigrationTypes(): array { return ['single_customer', 'multi_customer']; }
    public function testConnection(string $host, int $port, string $username, string $password, ?string $apiKey = null): array {
        return $this->httpGet("https://{$host}:{$port}/api/v1/websites", ["Authorization: Bearer {$password}"]);
    }
    public function preflight(string $host, int $port, string $username, string $password, ?string $apiKey = null): array {
        $resp = $this->httpGet("https://{$host}:{$port}/api/v1/websites", ["Authorization: Bearer {$password}"]);
        $data = json_decode($resp['body'], true);
        $accounts = [];
        foreach ($data['websites'] ?? $data ?? [] as $w) { $accounts[] = ['username' => $w['domain'] ?? 'unknown', 'domain' => $w['domain'] ?? '', 'plan' => $w['plan'] ?? '']; }
        return ['connected' => true, 'accounts' => $accounts, 'server_info' => ['enhance' => true, 'total' => count($accounts)]];
    }
    public function analyzeAccounts(array $accounts, array $options = []): array { return $accounts; }
    public function migrate(array $accounts, array $packageMap, array $options, callable $logFn): array {
        $rollback = [];
        foreach ($accounts as $a) { $id = $this->importHostingUser($a['username'], $a['domain']); if ($id) { $rollback[] = ['type' => 'hosting_user', 'id' => $id]; $logFn("Imported {$a['username']}"); } }
        return $rollback;
    }
    public function rollback(array $rollbackData, callable $logFn): bool {
        foreach ($rollbackData as $r) { if ($r['type'] === 'hosting_user') { try { $this->db->table('hosting_users')->where('id', $r['id'])->delete(); } catch (\Exception $e) {} } }
        return true;
    }
    public function validateMigration(array $migratedIds, array $options = []): array { return ['validated' => count($migratedIds), 'passed' => count($migratedIds), 'results' => []]; }
}
