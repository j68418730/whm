<?php

namespace Admin\Services\Migration;

class VerificationEngine
{
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
    }

    public function verify(string $type, array $data, array $options = []): array
    {
        $method = "verify" . ucfirst($type);
        if (method_exists($this, $method)) {
            return $this->$method($data, $options);
        }
        return ['type' => $type, 'passed' => false, 'errors' => ["No verification method for {$type}"]];
    }

    public function verifyAll(array $migrationData, array $options = []): array
    {
        $results = [];
        $types = ['files', 'databases', 'email', 'dns', 'ssl', 'streaming', 'permissions', 'services', 'checksums'];
        foreach ($types as $type) {
            $results[$type] = $this->verify($type, $migrationData[$type] ?? [], $options);
        }
        $passed = count(array_filter($results, fn($r) => $r['passed']));
        return [
            'results' => $results,
            'passed' => $passed,
            'total' => count($types),
            'all_passed' => $passed === count($types),
            'verified_at' => date('Y-m-d H:i:s'),
        ];
    }

    protected function verifyFiles(array $data, array $options): array
    {
        $errors = [];
        $checked = 0;
        $missing = 0;
        foreach ($data['paths'] ?? [] as $path) {
            $checked++;
            if (!file_exists($path)) {
                $errors[] = "Missing: {$path}";
                $missing++;
            }
        }
        return [
            'type' => 'files',
            'passed' => $missing === 0,
            'total' => $checked,
            'passed_count' => $checked - $missing,
            'failed_count' => $missing,
            'errors' => $errors,
        ];
    }

    protected function verifyDatabases(array $data, array $options): array
    {
        $errors = [];
        $checked = 0;
        $connected = 0;
        $dbHost = $options['db_host'] ?? 'localhost';
        foreach ($data['databases'] ?? [] as $db) {
            $checked++;
            try {
                $pdo = new \PDO(
                    "mysql:host={$dbHost};dbname={$db['name']}",
                    $db['user'] ?? 'root',
                    $db['pass'] ?? '',
                    [\PDO::ATTR_TIMEOUT => 3]
                );
                $connected++;
            } catch (\Exception $e) {
                $errors[] = "Database {$db['name']}: " . $e->getMessage();
            }
        }
        return [
            'type' => 'databases',
            'passed' => $connected === $checked,
            'total' => $checked,
            'passed_count' => $connected,
            'failed_count' => $checked - $connected,
            'errors' => $errors,
        ];
    }

    protected function verifyEmail(array $data, array $options): array
    {
        $errors = [];
        $checked = 0;
        $deliverable = 0;
        foreach ($data['email_accounts'] ?? [] as $email) {
            $checked++;
            $domain = substr(strrchr($email, '@'), 1);
            if ($domain && checkdnsrr($domain, 'MX')) {
                $deliverable++;
            } else {
                $errors[] = "No MX record for {$email}";
            }
        }
        return [
            'type' => 'email',
            'passed' => $deliverable === $checked,
            'total' => $checked,
            'passed_count' => $deliverable,
            'failed_count' => $checked - $deliverable,
            'errors' => $errors,
        ];
    }

    protected function verifyDns(array $data, array $options): array
    {
        $errors = [];
        $checked = 0;
        $resolved = 0;
        foreach ($data['zones'] ?? [] as $zone) {
            $domain = $zone['domain'] ?? $zone;
            $checked++;
            $record = dns_get_record($domain, DNS_A | DNS_AAAA);
            if (!empty($record)) {
                $resolved++;
            } else {
                $errors[] = "{$domain} has no A/AAAA records";
            }
        }
        return [
            'type' => 'dns',
            'passed' => $resolved === $checked,
            'total' => $checked,
            'passed_count' => $resolved,
            'failed_count' => $checked - $resolved,
            'errors' => $errors,
        ];
    }

    protected function verifySsl(array $data, array $options): array
    {
        $errors = [];
        $checked = 0;
        $valid = 0;
        $context = stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => false]]);
        foreach ($data['certificates'] ?? $data['domains'] ?? [] as $domain) {
            $d = $domain['domain'] ?? $domain;
            $checked++;
            try {
                $client = @stream_socket_client("ssl://{$d}:443", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);
                if ($client) {
                    $valid++;
                    fclose($client);
                } else {
                    $errors[] = "Cannot verify SSL for {$d}";
                }
            } catch (\Exception $e) {
                $errors[] = "SSL check failed for {$d}";
            }
        }
        return [
            'type' => 'ssl',
            'passed' => $valid === $checked,
            'total' => $checked,
            'passed_count' => $valid,
            'failed_count' => $checked - $valid,
            'errors' => $errors,
        ];
    }

    protected function verifyStreaming(array $data, array $options): array
    {
        $errors = [];
        $checked = 0;
        $responding = 0;
        foreach ($data['stations'] ?? [] as $station) {
            $url = $station['url'] ?? '';
            $checked++;
            if ($url) {
                $headers = @get_headers($url, true);
                if ($headers && (strpos($headers[0] ?? '', '200') !== false || strpos($headers[0] ?? '', '302') !== false)) {
                    $responding++;
                } else {
                    $errors[] = "Station not responding: {$url}";
                }
            }
        }
        return [
            'type' => 'streaming',
            'passed' => $responding === $checked,
            'total' => $checked,
            'passed_count' => $responding,
            'failed_count' => $checked - $responding,
            'errors' => $errors,
        ];
    }

    protected function verifyPermissions(array $data, array $options): array
    {
        $errors = [];
        $checked = 0;
        $correct = 0;
        $expectedOwner = $options['owner'] ?? 'nobody';
        foreach ($data['paths'] ?? [] as $path) {
            if (!file_exists($path)) {
                $errors[] = "{$path} does not exist";
                continue;
            }
            $checked++;
            $perms = fileperms($path);
            $owner = fileowner($path);
            $expectedPerms = is_dir($path) ? 0755 : 0644;
            $permOk = ($perms & 0777) === $expectedPerms;
            if ($permOk) $correct++;
            else $errors[] = "Wrong permissions on {$path}: " . substr(sprintf('%o', $perms), -4);
        }
        return [
            'type' => 'permissions',
            'passed' => $correct === $checked,
            'total' => $checked,
            'passed_count' => $correct,
            'failed_count' => $checked - $correct,
            'errors' => $errors,
        ];
    }

    protected function verifyServices(array $data, array $options): array
    {
        $errors = [];
        $checked = 0;
        $running = 0;
        $expectedServices = ['httpd', 'nginx', 'mysqld', 'dovecot', 'postfix', 'named'];
        foreach ($expectedServices as $svc) {
            $checked++;
            $output = [];
            exec("systemctl is-active {$svc} 2>/dev/null", $output, $code);
            $status = trim(implode('', $output));
            if ($code === 0 && $status === 'active') {
                $running++;
            } else {
                $errors[] = "Service {$svc} is not running";
            }
        }
        return [
            'type' => 'services',
            'passed' => $running === $checked,
            'total' => $checked,
            'passed_count' => $running,
            'failed_count' => $checked - $running,
            'errors' => $errors,
        ];
    }

    protected function verifyChecksums(array $data, array $options): array
    {
        $errors = [];
        $checked = 0;
        $matches = 0;
        foreach ($data['checksums'] ?? [] as $cs) {
            $checked++;
            $path = $cs['path'] ?? '';
            $expected = $cs['checksum'] ?? '';
            if (!file_exists($path)) {
                $errors[] = "Checksum target missing: {$path}";
                continue;
            }
            $actual = md5_file($path);
            if ($actual === $expected) {
                $matches++;
            } else {
                $errors[] = "Checksum mismatch for {$path}";
            }
        }
        return [
            'type' => 'checksums',
            'passed' => $matches === $checked,
            'total' => $checked,
            'passed_count' => $matches,
            'failed_count' => $checked - $matches,
            'errors' => $errors,
        ];
    }
}
