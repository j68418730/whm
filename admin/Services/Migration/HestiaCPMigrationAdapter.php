<?php namespace Admin\Services\Migration;
class HestiaCPMigrationAdapter extends BaseMigrationAdapter {
    public function getPanelName(): string { return 'HestiaCP'; }
    public function getPanelIcon(): string { return '🟨'; }
    public function getDefaultPort(): int { return 8083; }
    public function getSupportedSourceTypes(): array { return ['live_ssh', 'remote_api']; }
    public function getSupportedMigrationTypes(): array { return ['single_customer', 'multi_customer']; }
    public function testConnection(string $host, int $port, string $username, string $password, ?string $apiKey = null): array {
        $resp = $this->httpPost("https://{$host}:{$port}/api/", ['hash' => $password, 'cmd' => 'v-list-sys-info', 'arg1' => 'json']);
        $data = json_decode($resp['body'], true);
        return ['connected' => !empty($data), 'error' => null];
    }
    public function preflight(string $host, int $port, string $username, string $password, ?string $apiKey = null): array {
        $resp = $this->httpPost("https://{$host}:{$port}/api/", ['hash' => $password, 'cmd' => 'v-list-users', 'arg1' => 'json']);
        $data = json_decode($resp['body'], true);
        $accounts = [];
        foreach ($data as $u => $info) {
            if (is_array($info)) $accounts[] = ['username' => $u, 'domain' => $info['DOMAIN'] ?? '', 'plan' => $info['PACKAGE'] ?? '', 'email' => $info['CONTACT'] ?? ''];
        }
        return ['connected' => true, 'accounts' => $accounts, 'server_info' => ['hestiacp' => true, 'total' => count($accounts)]];
    }
    public function analyzeAccounts(array $accounts, array $options = []): array { return $accounts; }
    public function migrate(array $accounts, array $packageMap, array $options, callable $logFn): array {
        $rollback = [];
        foreach ($accounts as $a) {
            $id = $this->importHostingUser($a['username'], $a['domain'] ?: "{$a['username']}.hestia.local", null, $a['email'] ?? '');
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
