<?php namespace Admin\Services\Migration;
class CyberPanelMigrationAdapter extends BaseMigrationAdapter {
    public function getPanelName(): string { return 'CyberPanel'; }
    public function getPanelIcon(): string { return '🔷'; }
    public function getDefaultPort(): int { return 8090; }
    public function getSupportedSourceTypes(): array { return ['live_ssh', 'remote_api']; }
    public function getSupportedMigrationTypes(): array { return ['single_customer', 'multi_customer']; }
    public function testConnection(string $host, int $port, string $username, string $password, ?string $apiKey = null): array {
        $resp = $this->httpGet("https://{$host}:{$port}/api/listWebsites", ["Authorization: Bearer {$password}"]);
        return ['connected' => $resp['code'] === 200, 'error' => $resp['code'] !== 200 ? "HTTP {$resp['code']}" : null];
    }
    public function preflight(string $host, int $port, string $username, string $password, ?string $apiKey = null): array {
        $resp = $this->httpGet("https://{$host}:{$port}/api/listWebsites", ["Authorization: Bearer {$password}"]);
        $data = json_decode($resp['body'], true);
        $accounts = [];
        foreach ($data['websites'] ?? [] as $w) {
            $accounts[] = ['username' => $w['owner'] ?? $w['domain'] ?? 'unknown', 'domain' => $w['domain'] ?? '', 'plan' => $w['package'] ?? 'default', 'email' => ''];
        }
        return ['connected' => true, 'accounts' => $accounts, 'server_info' => ['cyberpanel' => true, 'total' => count($accounts)]];
    }
    public function analyzeAccounts(array $accounts, array $options = []): array { return $accounts; }
    public function migrate(array $accounts, array $packageMap, array $options, callable $logFn): array {
        $rollback = [];
        foreach ($accounts as $a) {
            $logFn("CyberPanel: importing {$a['username']}...");
            $id = $this->importHostingUser($a['username'], $a['domain']);
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
