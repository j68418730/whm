<?php

namespace Admin\Services\Migration;

class MigrationAnalysisEngine
{
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
    }

    public function analyze(array $preflightData, string $sourceType, array $conversionRules = []): array
    {
        $accounts = $preflightData['accounts'] ?? [];
        $domains = $preflightData['domains'] ?? [];
        $databases = $preflightData['databases'] ?? [];
        $emailAccounts = $preflightData['email_accounts'] ?? [];
        $ftpAccounts = $preflightData['ftp_accounts'] ?? [];
        $streamingStations = $preflightData['streaming_stations'] ?? [];
        $gameServers = $preflightData['game_servers'] ?? [];
        $dnsZones = $preflightData['dns_zones'] ?? [];
        $sslCerts = $preflightData['ssl_certificates'] ?? [];
        $cronJobs = $preflightData['cron_jobs'] ?? [];
        $phpVersions = $preflightData['php_versions'] ?? [];
        $packages = $preflightData['packages'] ?? [];

        $totalAccounts = count($accounts);
        $totalDomains = count($domains) ?: $totalAccounts;
        $totalDatabases = count($databases);
        $totalEmailAccounts = count($emailAccounts);
        $totalFtp = count($ftpAccounts);
        $totalStreaming = count($streamingStations);
        $totalGameServers = count($gameServers);
        $totalDnsZones = count($dnsZones);
        $totalSsl = count($sslCerts);
        $totalCron = count($cronJobs);
        $totalPackages = count($packages);

        $totalDiskUsed = 0;
        foreach ($accounts as $a) {
            $totalDiskUsed += (float)($a['disk_used'] ?? 0);
        }

        $bwUsed = 0;
        foreach ($accounts as $a) {
            $bwUsed += (float)($a['bandwidth_used'] ?? 0);
        }

        $issueSummary = $this->detectPotentialIssues($accounts, $phpVersions, $sslCerts, $emailAccounts, $preflightData, $conversionRules);
        $estimatedTime = $this->estimateTime($totalAccounts, $totalDatabases, $totalEmailAccounts, $totalStreaming, $totalDiskUsed);
        $recommendations = $this->generateRecommendations($issueSummary);

        return [
            'summary' => [
                'total_accounts' => $totalAccounts,
                'total_domains' => $totalDomains,
                'total_databases' => $totalDatabases,
                'total_email_accounts' => $totalEmailAccounts,
                'total_ftp_accounts' => $totalFtp,
                'total_streaming_stations' => $totalStreaming,
                'total_game_servers' => $totalGameServers,
                'total_dns_zones' => $totalDnsZones,
                'total_ssl_certificates' => $totalSsl,
                'total_cron_jobs' => $totalCron,
                'total_packages' => $totalPackages,
                'total_disk_used_mb' => round($totalDiskUsed, 1),
                'total_disk_used_gb' => round($totalDiskUsed / 1024, 2),
                'total_bandwidth_used_mb' => round($bwUsed, 1),
            ],
            'estimated_time' => $estimatedTime,
            'estimated_time_human' => $this->formatDuration($estimatedTime),
            'potential_issues' => $issueSummary,
            'recommendations' => $recommendations,
            'source_panel' => $sourceType,
            'compatible_accounts' => $totalAccounts,
            'php_version_distribution' => $this->buildPhpDistribution($phpVersions, $accounts),
            'package_breakdown' => $this->buildPackageBreakdown($packages),
        ];
    }

    protected function detectPotentialIssues(array $accounts, array $phpVersions, array $sslCerts, array $emailAccounts, array $preflightData, array $conversionRules): array
    {
        $issues = [];

        $phpCounts = [];
        foreach ($accounts as $a) {
            $pv = $a['php_version'] ?? '';
            if ($pv) {
                $phpCounts[$pv] = ($phpCounts[$pv] ?? 0) + 1;
            }
        }

        $supportedPhp = $conversionRules['php_versions'] ?? ['7.4','8.0','8.1','8.2','8.3','8.4'];
        foreach ($phpCounts as $pv => $count) {
            $short = substr($pv, 0, 3);
            if (!in_array($short, $supportedPhp) && version_compare($short, '7.4', '<')) {
                $issues[] = [
                    'type' => 'php_version',
                    'severity' => 'warning',
                    'title' => "PHP {$short} sites detected",
                    'detail' => "{$count} account(s) use PHP {$pv} which is outdated. These will be converted to PHP 8.1.",
                    'count' => $count,
                    'icon' => '⚠',
                ];
            }
        }

        $now = time();
        $expiredCount = 0;
        foreach ($sslCerts as $s) {
            $expires = strtotime($s['expires'] ?? '') ?: 0;
            if ($expires && $expires < $now) $expiredCount++;
        }
        if ($expiredCount > 0) {
            $issues[] = [
                'type' => 'expired_ssl',
                'severity' => 'warning',
                'title' => 'Expired SSL certificates',
                'detail' => "{$expiredCount} expired SSL certificate(s) found. New Let's Encrypt certificates will be issued.",
                'count' => $expiredCount,
                'icon' => '🔒',
            ];
        }

        $oversizedEmail = 0;
        foreach ($emailAccounts as $e) {
            $diskUsed = (float)($e['disk_used'] ?? 0);
            if ($diskUsed > 500) $oversizedEmail++;
        }
        if ($oversizedEmail > 0) {
            $issues[] = [
                'type' => 'oversized_email',
                'severity' => 'info',
                'title' => 'Oversized email accounts',
                'detail' => "{$oversizedEmail} email account(s) use over 500MB each. They may take longer to transfer.",
                'count' => $oversizedEmail,
                'icon' => '📧',
            ];
        }

        $largeAccounts = 0;
        foreach ($accounts as $a) {
            if ((float)($a['disk_used'] ?? 0) > 10240) $largeAccounts++;
        }
        if ($largeAccounts > 0) {
            $issues[] = [
                'type' => 'large_accounts',
                'severity' => 'info',
                'title' => 'Large accounts detected',
                'detail' => "{$largeAccounts} account(s) use over 10GB. Estimated transfer time may be significant.",
                'count' => $largeAccounts,
                'icon' => '💾',
            ];
        }

        $unsupportedModules = $preflightData['unsupported_modules'] ?? [];
        if (!empty($unsupportedModules)) {
            $issues[] = [
                'type' => 'unsupported_modules',
                'severity' => 'warning',
                'title' => 'Unsupported Apache/Nginx modules',
                'detail' => implode(', ', $unsupportedModules) . ' - These modules are not available in Planet Hosts.',
                'count' => count($unsupportedModules),
                'icon' => '🔧',
            ];
        }

        $streamingIssues = $preflightData['streaming_issues'] ?? [];
        if (!empty($streamingIssues)) {
            $issues[] = [
                'type' => 'streaming_issues',
                'severity' => 'warning',
                'title' => 'Streaming configuration issues',
                'detail' => implode('; ', $streamingIssues),
                'count' => count($streamingIssues),
                'icon' => '📻',
            ];
        }

        return $issues;
    }

    protected function estimateTime(int $accounts, int $databases, int $emails, int $streaming, float $diskMb): int
    {
        $time = 0;
        $time += $accounts * 30;
        $time += $databases * 15;
        $time += $emails * 5;
        $time += $streaming * 60;
        $time += (int)($diskMb / 10);
        return max(60, $time);
    }

    protected function formatDuration(int $seconds): string
    {
        if ($seconds < 60) return "{$seconds}s";
        $m = intdiv($seconds, 60);
        $s = $seconds % 60;
        if ($m < 60) return "{$m}m {$s}s";
        $h = intdiv($m, 60);
        $m = $m % 60;
        if ($h < 24) return "{$h}h {$m}m";
        $d = intdiv($h, 24);
        $h = $h % 24;
        return "{$d}d {$h}h";
    }

    protected function generateRecommendations(array $issues): array
    {
        $recs = [
            ['priority' => 'high', 'text' => 'Ensure all source services are accessible and credentials are correct.'],
            ['priority' => 'medium', 'text' => 'Review PHP version compatibility before migration.'],
            ['priority' => 'medium', 'text' => 'SSL certificates will be re-issued via Let\'s Encrypt after migration.'],
        ];

        foreach ($issues as $issue) {
            if ($issue['type'] === 'php_version') {
                $recs[] = ['priority' => 'high', 'text' => "Update PHP versions for {$issue['count']} account(s) before or after migration."];
            }
            if ($issue['type'] === 'expired_ssl') {
                $recs[] = ['priority' => 'medium', 'text' => "{$issue['count']} SSL certificates will be automatically re-issued."];
            }
        }

        return $recs;
    }

    protected function buildPhpDistribution(array $phpVersions, array $accounts): array
    {
        $dist = [];
        foreach ($accounts as $a) {
            $v = $a['php_version'] ?? 'unknown';
            $short = substr($v, 0, 3);
            $dist[$short] = ($dist[$short] ?? 0) + 1;
        }
        arsort($dist);
        return $dist;
    }

    protected function buildPackageBreakdown(array $packages): array
    {
        $breakdown = [];
        foreach ($packages as $p) {
            $name = is_string($p) ? $p : ($p['name'] ?? $p['plan'] ?? 'Unknown');
            $breakdown[] = [
                'name' => $name,
                'accounts' => $p['accounts'] ?? 0,
                'disk' => $p['disk'] ?? $p['disk_limit'] ?? 0,
            ];
        }
        return $breakdown;
    }

    public function performFullScan(int $jobId, array $accounts, array $options = []): array
    {
        $scan = [
            'hosting_accounts' => $this->scanHostingAccounts($accounts),
            'domains' => $this->scanDomains($accounts),
            'subdomains' => $this->scanSubdomains($accounts),
            'databases' => $this->scanDatabases($accounts),
            'email_accounts' => $this->scanEmailAccounts($accounts),
            'ftp_accounts' => $this->scanFtpAccounts($accounts),
            'ssl_certificates' => $this->scanSslCertificates($accounts),
            'cron_jobs' => $this->scanCronJobs($accounts),
            'dns_zones' => $this->scanDnsZones($accounts),
            'streaming_stations' => $this->scanStreamingStations($accounts),
            'game_servers' => $this->scanGameServers($accounts),
            'packages' => $this->scanPackages($accounts),
            'users' => $this->scanUsers($accounts),
            'php_versions' => $this->scanPhpVersions($accounts),
            'disk_usage' => $this->scanDiskUsage($accounts),
            'bandwidth_usage' => $this->scanBandwidthUsage($accounts),
        ];
        return $scan;
    }

    protected function scanHostingAccounts(array $accounts): array
    {
        $result = [];
        foreach ($accounts as $a) {
            $result[] = [
                'username' => $a['username'] ?? '',
                'domain' => $a['domain'] ?? '',
                'plan' => $a['plan'] ?? '',
                'disk_used' => (float)($a['disk_used'] ?? 0),
                'disk_limit' => (float)($a['disk_limit'] ?? 0),
                'status' => $a['status'] ?? 'active',
                'created' => $a['created'] ?? '',
            ];
        }
        return $result;
    }

    protected function scanDomains(array $accounts): array
    {
        $domains = [];
        foreach ($accounts as $a) {
            if (!empty($a['domain'])) $domains[] = $a['domain'];
            foreach ($a['addon_domains'] ?? [] as $ad) {
                if (is_string($ad)) $domains[] = $ad;
                elseif (is_array($ad) && !empty($ad['domain'])) $domains[] = $ad['domain'];
            }
            foreach ($a['parked_domains'] ?? [] as $pd) {
                if (is_string($pd)) $domains[] = $pd;
                elseif (is_array($pd) && !empty($pd['domain'])) $domains[] = $pd['domain'];
            }
        }
        return array_values(array_unique(array_filter($domains)));
    }

    protected function scanSubdomains(array $accounts): array
    {
        $subs = [];
        foreach ($accounts as $a) {
            foreach ($a['subdomains'] ?? [] as $s) {
                $subs[] = is_string($s) ? $s : ($s['subdomain'] ?? '');
            }
        }
        return array_values(array_unique(array_filter($subs)));
    }

    protected function scanDatabases(array $accounts): array
    {
        $dbs = [];
        foreach ($accounts as $a) {
            foreach ($a['databases'] ?? [] as $db) {
                $dbs[] = [
                    'name' => $db['name'] ?? (is_string($db) ? $db : ''),
                    'user' => $db['user'] ?? '',
                    'type' => $db['type'] ?? 'mysql',
                    'size' => (float)($db['size'] ?? 0),
                ];
            }
        }
        return $dbs;
    }

    protected function scanEmailAccounts(array $accounts): array
    {
        $emails = [];
        foreach ($accounts as $a) {
            foreach ($a['email_accounts'] ?? [] as $e) {
                $emails[] = [
                    'email' => $e['email'] ?? (is_string($e) ? $e : ''),
                    'quota' => (float)($e['quota'] ?? 0),
                    'disk_used' => (float)($e['disk_used'] ?? 0),
                ];
            }
        }
        return $emails;
    }

    protected function scanFtpAccounts(array $accounts): array
    {
        $ftps = [];
        foreach ($accounts as $a) {
            foreach ($a['ftp_accounts'] ?? [] as $f) {
                $ftps[] = [
                    'username' => $f['username'] ?? (is_string($f) ? $f : ''),
                    'directory' => $f['directory'] ?? '',
                ];
            }
        }
        return $ftps;
    }

    protected function scanSslCertificates(array $accounts): array
    {
        $certs = [];
        foreach ($accounts as $a) {
            foreach ($a['ssl_certificates'] ?? [] as $c) {
                $certs[] = [
                    'domain' => $c['domain'] ?? '',
                    'type' => $c['type'] ?? '',
                    'expires' => $c['expires'] ?? '',
                    'issuer' => $c['issuer'] ?? '',
                ];
            }
        }
        return $certs;
    }

    protected function scanCronJobs(array $accounts): array
    {
        $crons = [];
        foreach ($accounts as $a) {
            foreach ($a['cron_jobs'] ?? [] as $c) {
                $crons[] = [
                    'command' => $c['command'] ?? '',
                    'schedule' => $c['schedule'] ?? '',
                    'enabled' => $c['enabled'] ?? true,
                ];
            }
        }
        return $crons;
    }

    protected function scanDnsZones(array $accounts): array
    {
        $zones = [];
        foreach ($accounts as $a) {
            foreach ($a['dns_zones'] ?? [] as $z) {
                $zones[] = [
                    'domain' => $z['domain'] ?? '',
                    'records' => $z['records'] ?? [],
                ];
            }
        }
        return $zones;
    }

    protected function scanStreamingStations(array $accounts): array
    {
        $stations = [];
        foreach ($accounts as $a) {
            foreach ($a['streaming_stations'] ?? [] as $s) {
                $stations[] = [
                    'name' => $s['name'] ?? '',
                    'type' => $s['type'] ?? '',
                    'bitrate' => $s['bitrate'] ?? 0,
                    'listeners' => $s['listeners'] ?? 0,
                    'auto_dj' => !empty($s['auto_dj']),
                ];
            }
        }
        return $stations;
    }

    protected function scanGameServers(array $accounts): array
    {
        $servers = [];
        foreach ($accounts as $a) {
            foreach ($a['game_servers'] ?? [] as $g) {
                $servers[] = [
                    'name' => $g['name'] ?? '',
                    'game' => $g['game'] ?? '',
                    'players' => $g['players'] ?? 0,
                    'slots' => $g['slots'] ?? 0,
                ];
            }
        }
        return $servers;
    }

    protected function scanPackages(array $accounts): array
    {
        $packages = [];
        $seen = [];
        foreach ($accounts as $a) {
            $plan = $a['plan'] ?? '';
            if ($plan && !isset($seen[$plan])) {
                $packages[] = ['name' => $plan, 'accounts' => 1, 'disk' => $a['disk_limit'] ?? 0];
                $seen[$plan] = true;
            }
        }
        return $packages;
    }

    protected function scanUsers(array $accounts): array
    {
        $users = [];
        foreach ($accounts as $a) {
            $users[] = [
                'username' => $a['username'] ?? '',
                'email' => $a['email'] ?? '',
                'role' => $a['role'] ?? 'user',
            ];
        }
        return $users;
    }

    protected function scanPhpVersions(array $accounts): array
    {
        $versions = [];
        foreach ($accounts as $a) {
            $v = substr($a['php_version'] ?? '', 0, 3);
            if ($v) $versions[$v] = ($versions[$v] ?? 0) + 1;
        }
        arsort($versions);
        return $versions;
    }

    protected function scanDiskUsage(array $accounts): array
    {
        $total = 0;
        $byAccount = [];
        foreach ($accounts as $a) {
            $used = (float)($a['disk_used'] ?? 0);
            $total += $used;
            $byAccount[] = ['username' => $a['username'] ?? '', 'used_mb' => $used];
        }
        usort($byAccount, fn($a, $b) => $b['used_mb'] <=> $a['used_mb']);
        return ['total_mb' => $total, 'accounts' => array_slice($byAccount, 0, 20)];
    }

    protected function scanBandwidthUsage(array $accounts): array
    {
        $total = 0;
        foreach ($accounts as $a) {
            $total += (float)($a['bandwidth_used'] ?? 0);
        }
        return ['total_mb' => $total];
    }
}
