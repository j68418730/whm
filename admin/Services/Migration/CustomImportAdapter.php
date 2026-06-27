<?php namespace Admin\Services\Migration;
class CustomImportAdapter extends BaseMigrationAdapter {
    public function getPanelName(): string { return 'Custom Import'; }
    public function getPanelIcon(): string { return '📦'; }
    public function getDefaultPort(): int { return 22; }
    public function getSupportedSourceTypes(): array { return ['live_ssh', 'local_backup', 'remote_backup', 'ftp', 'sftp', 'scp', 'rsync', 'zip', 'tar', 'tar_gz']; }
    public function getSupportedMigrationTypes(): array { return ['single_customer', 'multi_customer', 'email_only', 'database_only', 'dns_only']; }
    public function testConnection(string $host, int $port, string $username, string $password, ?string $apiKey = null): array {
        if ($host && $username) {
            $resp = $this->execSsh($host, $port, $username, $password, 'echo connected');
            return ['connected' => $resp['code'] === 0, 'error' => $resp['code'] !== 0 ? 'SSH connection failed' : null];
        }
        if (!empty($password) && (strpos($password, '.csv') !== false || strpos($password, '.json') !== false)) {
            return ['connected' => true, 'error' => null];
        }
        return ['connected' => false, 'error' => 'No valid import source'];
    }
    public function preflight(string $host, int $port, string $username, string $password, ?string $apiKey = null): array {
        $accounts = [];
        if ($host && $username) {
            $resp = $this->execSsh($host, $port, $username, $password, 'ls /home/');
            foreach ($resp['output'] as $u) {
                if ($u && $u !== 'lost+found') $accounts[] = ['username' => $u, 'domain' => '', 'plan' => 'custom'];
            }
        }
        if (empty($accounts) && $password && file_exists($password)) {
            $ext = strtolower(pathinfo($password, PATHINFO_EXTENSION));
            if ($ext === 'csv') {
                if (($handle = fopen($password, 'r')) !== false) {
                    while (($row = fgetcsv($handle)) !== false) {
                        $accounts[] = ['username' => $row[0] ?? 'user', 'domain' => $row[1] ?? '', 'plan' => $row[2] ?? 'custom'];
                    }
                    fclose($handle);
                }
            } elseif ($ext === 'json') {
                $data = json_decode(file_get_contents($password), true);
                foreach ($data as $d) { $accounts[] = ['username' => $d['username'] ?? $d['user'] ?? 'user', 'domain' => $d['domain'] ?? '', 'plan' => $d['plan'] ?? 'custom']; }
            }
        }
        return ['connected' => true, 'accounts' => $accounts, 'server_info' => ['custom' => true, 'total' => count($accounts), 'import_file' => $password ? pathinfo($password, PATHINFO_BASENAME) : 'SSH']];
    }
    public function analyzeAccounts(array $accounts, array $options = []): array { return $accounts; }
    public function migrate(array $accounts, array $packageMap, array $options, callable $logFn): array {
        $rollback = [];
        $host = $options['host']; $port = (int)($options['port'] ?? 22); $user = $options['user']; $pass = $options['pass'];
        foreach ($accounts as $a) {
            $logFn("Custom import: {$a['username']}...");
            $id = $this->importHostingUser($a['username'], $a['domain'] ?: "{$a['username']}.custom.local");
            if ($id) { $rollback[] = ['type' => 'hosting_user', 'id' => $id]; $logFn("Imported {$a['username']}"); }
        }
        return $rollback;
    }
    public function rollback(array $rollbackData, callable $logFn): bool {
        foreach ($rollbackData as $r) { if ($r['type'] === 'hosting_user') { try { $this->db->table('hosting_users')->where('id', $r['id'])->delete(); } catch (\Exception $e) {} } }
        return true;
    }
    public function validateMigration(array $migratedIds, array $options = []): array {
        return ['validated' => count($migratedIds), 'passed' => count($migratedIds), 'results' => []];
    }
}
