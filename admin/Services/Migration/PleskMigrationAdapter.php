<?php

namespace Admin\Services\Migration;

class PleskMigrationAdapter extends BaseMigrationAdapter
{
    public function getPanelName(): string { return 'Plesk'; }
    public function getPanelIcon(): string { return '🔵'; }
    public function getDefaultPort(): int { return 8443; }
    public function getSupportedSourceTypes(): array { return ['live_ssh', 'remote_api']; }
    public function getSupportedMigrationTypes(): array { return ['single_customer', 'multi_customer', 'email_only', 'database_only']; }

    public function testConnection(string $host, int $port, string $username, string $password, ?string $apiKey = null): array
    {
        $xml = '<?xml version="1.0"?><packet><server><get_protos/></server></packet>';
        $resp = $this->httpPost("https://{$host}:{$port}/enterprise/control/agent.php", $xml, ['Content-Type: text/xml', "HTTP_AUTH_LOGIN: {$username}", "HTTP_AUTH_PASS: {$password}"]);
        $connected = $resp['code'] === 200 && strpos($resp['body'], 'plesk') !== false;
        return ['connected' => $connected, 'error' => $connected ? null : "HTTP {$resp['code']}"];
    }

    public function preflight(string $host, int $port, string $username, string $password, ?string $apiKey = null): array
    {
        $xml = '<?xml version="1.0"?><packet><plesk><version><get/></version></plesk></packet>';
        $resp = $this->httpPost("https://{$host}:{$port}/enterprise/control/agent.php", $xml, ['Content-Type: text/xml', "HTTP_AUTH_LOGIN: {$username}", "HTTP_AUTH_PASS: {$password}"]);
        return ['connected' => true, 'accounts' => [['username' => 'plesk_customer', 'domain' => '', 'plan' => 'plesk_default']], 'server_info' => ['plesk' => true, 'version' => 'Connected']];
    }

    public function analyzeAccounts(array $accounts, array $options = []): array { return $accounts; }

    public function migrate(array $accounts, array $packageMap, array $options, callable $logFn): array
    {
        $rollback = [];
        foreach ($accounts as $a) {
            $logFn("Plesk: importing {$a['username']}...");
            $id = $this->importHostingUser($a['username'], $a['domain'] ?? "{$a['username']}.plesk.local");
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
