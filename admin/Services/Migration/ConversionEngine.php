<?php

namespace Admin\Services\Migration;

class ConversionEngine
{
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
    }

    public function convertPackage(array $sourceLimits, ?string $sourcePlanName = null): ?int
    {
        $name = $sourcePlanName ?? 'Imported_' . uniqid();
        $existing = $this->db->table('hosting_packages')->where('name', $name)->first();
        if ($existing) return $existing->id;

        $disk = $this->convertSize($sourceLimits['disk'] ?? 0);
        $bw = $this->convertSize($sourceLimits['bandwidth'] ?? 0);
        $id = $this->db->table('hosting_packages')->insertGetId([
            'name' => $name,
            'disk_space' => $disk,
            'bandwidth' => $bw,
            'max_databases' => (int)($sourceLimits['databases'] ?? 5),
            'max_email_accounts' => (int)($sourceLimits['email_accounts'] ?? 5),
            'max_ftp_accounts' => (int)($sourceLimits['ftp_accounts'] ?? 5),
            'max_subdomains' => (int)($sourceLimits['subdomains'] ?? 5),
            'allow_ssh' => !empty($sourceLimits['ssh']) ? 1 : 0,
            'allow_cron' => 1,
            'allow_backup' => 1,
            'is_active' => 1,
        ]);
        $this->db->table('hosting_packages')->where('id', $id)->update(['name' => "{$name}_pkg{$id}"]);
        return $id;
    }

    public function convertSize($val): int
    {
        if (is_numeric($val)) return (int)$val;
        $val = strtoupper(trim((string)$val));
        if (strpos($val, 'TB') !== false) return (int)((float)$val * 1024 * 1024);
        if (strpos($val, 'GB') !== false) return (int)((float)$val * 1024);
        if (strpos($val, 'MB') !== false) return (int)((float)$val);
        if (strpos($val, 'KB') !== false) return (int)((float)$val / 1024);
        if (strpos($val, 'G') !== false) return (int)((float)$val * 1024);
        if (strpos($val, 'M') !== false) return (int)((float)$val);
        if (strpos($val, 'T') !== false) return (int)((float)$val * 1024 * 1024);
        return (int)$val;
    }

    public function convertPhpVersion(?string $version): string
    {
        $supported = ['5.6','7.0','7.1','7.2','7.3','7.4','8.0','8.1','8.2','8.3','8.4'];
        if (!$version) return '8.1';
        $v = substr(trim($version), 0, 3);
        return in_array($v, $supported) ? $v : '8.1';
    }

    public function convertDnsRecords(array $records): array
    {
        $converted = [];
        foreach ($records as $r) {
            $type = strtoupper($r['type'] ?? 'A');
            $converted[] = [
                'type' => in_array($type, ['A','AAAA','CNAME','MX','TXT','NS','SRV','SOA','CAA']) ? $type : 'A',
                'name' => $r['name'] ?? '',
                'value' => $r['value'] ?? $r['target'] ?? '',
                'priority' => (int)($r['priority'] ?? 0),
                'ttl' => (int)($r['ttl'] ?? 14400),
            ];
        }
        return $converted;
    }

    public function convertSsl(array $sourceSsl): array
    {
        return [
            'domain' => $sourceSsl['domain'] ?? '',
            'method' => in_array($sourceSsl['type'] ?? '', ['letsencrypt', 'commercial', 'self_signed']) ? $sourceSsl['type'] : 'letsencrypt',
            'certificate' => $sourceSsl['cert'] ?? '',
            'private_key' => $sourceSsl['key'] ?? '',
            'ca_bundle' => $sourceSsl['ca'] ?? '',
            'expires' => $sourceSsl['expires'] ?? null,
        ];
    }

    public function convertStreamingConfig(array $sourceConfig): array
    {
        return [
            'name' => $sourceConfig['name'] ?? 'Imported Station',
            'description' => $sourceConfig['description'] ?? '',
            'type' => in_array($sourceConfig['type'] ?? '', ['shoutcast', 'icecast']) ? $sourceConfig['type'] : 'shoutcast',
            'bitrate' => (int)($sourceConfig['bitrate'] ?? 128),
            'max_listeners' => (int)($sourceConfig['max_listeners'] ?? 100),
            'auto_dj' => !empty($sourceConfig['auto_dj']) ? 1 : 0,
            'source_password' => $sourceConfig['source_password'] ?? bin2hex(random_bytes(8)),
            'admin_password' => $sourceConfig['admin_password'] ?? bin2hex(random_bytes(8)),
        ];
    }

    public function convertDatabaseConfig(array $sourceDb): array
    {
        return [
            'type' => in_array(strtolower($sourceDb['type'] ?? ''), ['mysql', 'mariadb', 'postgresql']) ? strtolower($sourceDb['type']) : 'mysql',
            'name' => preg_replace('/[^a-z0-9_]/', '_', strtolower($sourceDb['name'] ?? 'db')),
            'user' => preg_replace('/[^a-z0-9_]/', '_', strtolower($sourceDb['user'] ?? 'user')),
            'charset' => $sourceDb['charset'] ?? 'utf8mb4',
            'collation' => $sourceDb['collation'] ?? 'utf8mb4_unicode_ci',
        ];
    }

    public function convertEmailConfig(array $sourceEmail): array
    {
        return [
            'email' => $sourceEmail['email'] ?? '',
            'password' => $sourceEmail['password'] ?? bin2hex(random_bytes(8)),
            'quota' => $this->convertSize($sourceEmail['quota'] ?? 100),
            'forwarders' => $sourceEmail['forwarders'] ?? [],
            'autoresponder' => !empty($sourceEmail['autoresponder']) ? [
                'subject' => $sourceEmail['autoresponder']['subject'] ?? 'Away',
                'body' => $sourceEmail['autoresponder']['body'] ?? 'I am away.',
            ] : null,
        ];
    }

    public function validateConvertedData(array $data, string $type): array
    {
        $errors = [];
        switch ($type) {
            case 'package':
                if (empty($data['name'])) $errors[] = 'Package name required';
                break;
            case 'dns':
                if (empty($data['type']) || empty($data['value'])) $errors[] = 'DNS record must have type and value';
                break;
            case 'ssl':
                if (empty($data['domain'])) $errors[] = 'SSL domain required';
                break;
            case 'streaming':
                if (empty($data['name'])) $errors[] = 'Streaming name required';
                break;
            case 'database':
                if (empty($data['name']) || empty($data['user'])) $errors[] = 'Database name and user required';
                break;
        }
        return $errors;
    }
}
