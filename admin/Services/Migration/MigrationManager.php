<?php

namespace Admin\Services\Migration;

class MigrationManager
{
    protected $db;
    protected $adapters = [];
    protected $conversion;
    protected $restoreManager;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
        $this->conversion = new ConversionEngine();
        $this->restoreManager = new RestoreManager();
        $this->ensureTables();
        $this->registerAdapters();
    }

    protected function ensureTables()
    {
        try {
            $this->db->pdo()->exec("CREATE TABLE IF NOT EXISTS `migration_jobs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `source_type` VARCHAR(50) NOT NULL,
                `source_host` VARCHAR(255) NOT NULL,
                `source_port` INT DEFAULT 0,
                `source_username` VARCHAR(255) DEFAULT NULL,
                `migration_type` VARCHAR(50) DEFAULT 'single_customer',
                `source_transport` VARCHAR(50) DEFAULT 'live_ssh',
                `step` INT DEFAULT 1,
                `preflight_data` LONGTEXT DEFAULT NULL,
                `compat_data` LONGTEXT DEFAULT NULL,
                `conversion_data` LONGTEXT DEFAULT NULL,
                `analysis_data` LONGTEXT DEFAULT NULL,
                `package_map` LONGTEXT DEFAULT NULL,
                `selected_items` LONGTEXT DEFAULT NULL,
                `migration_options` LONGTEXT DEFAULT NULL,
                `status` ENUM('pending','preflight','compat_check','package_map','converting','migrating','validating','completed','failed','rolled_back','paused') DEFAULT 'pending',
                `items_migrated` INT DEFAULT 0,
                `total_items` INT DEFAULT 0,
                `log` LONGTEXT DEFAULT NULL,
                `error_message` TEXT DEFAULT NULL,
                `rollback_data` LONGTEXT DEFAULT NULL,
                `validation_data` LONGTEXT DEFAULT NULL,
                `restore_point_id` INT DEFAULT NULL,
                `resume_token` VARCHAR(64) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `completed_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Add columns for existing tables
            $cols = ['analysis_data', 'migration_options'];
            foreach ($cols as $col) {
                try { $this->db->pdo()->exec("ALTER TABLE `migration_jobs` ADD COLUMN `{$col}` LONGTEXT DEFAULT NULL AFTER `conversion_data`"); } catch (\Exception $e) {}
            }

            $this->db->pdo()->exec("CREATE TABLE IF NOT EXISTS `migration_adapters` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `panel_type` VARCHAR(50) NOT NULL UNIQUE,
                `adapter_class` VARCHAR(255) NOT NULL,
                `is_active` TINYINT(1) DEFAULT 1,
                `version` VARCHAR(20) DEFAULT '1.0',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {}
    }

    protected function registerAdapters()
    {
        $defaults = [
            ['cpanel', 'Admin\\Services\\Migration\\CpanelMigrationAdapter'],
            ['plesk', 'Admin\\Services\\Migration\\PleskMigrationAdapter'],
            ['directadmin', 'Admin\\Services\\Migration\\DirectAdminMigrationAdapter'],
            ['cyberpanel', 'Admin\\Services\\Migration\\CyberPanelMigrationAdapter'],
            ['aapanel', 'Admin\\Services\\Migration\\AaPanelMigrationAdapter'],
            ['hestiacp', 'Admin\\Services\\Migration\\HestiaCPMigrationAdapter'],
            ['ispconfig', 'Admin\\Services\\Migration\\ISPConfigMigrationAdapter'],
            ['virtualmin', 'Admin\\Services\\Migration\\VirtualminMigrationAdapter'],
            ['webmin', 'Admin\\Services\\Migration\\WebminMigrationAdapter'],
            ['cwp', 'Admin\\Services\\Migration\\CWPMigrationAdapter'],
            ['froxlor', 'Admin\\Services\\Migration\\FroxlorMigrationAdapter'],
            ['vestacp', 'Admin\\Services\\Migration\\VestaCPMigrationAdapter'],
            ['cloudpanel', 'Admin\\Services\\Migration\\CloudPanelMigrationAdapter'],
            ['enhance', 'Admin\\Services\\Migration\\EnhanceMigrationAdapter'],
            ['sonicpanel', 'Admin\\Services\\Migration\\SonicPanelMigrationAdapter'],
            ['centovacast', 'Admin\\Services\\Migration\\CentovaCastMigrationAdapter'],
            ['mediacp', 'Admin\\Services\\Migration\\MediaCPMigrationAdapter'],
            ['azuracast', 'Admin\\Services\\Migration\\AzuraCastMigrationAdapter'],
            ['custom', 'Admin\\Services\\Migration\\CustomImportAdapter'],
        ];
        foreach ($defaults as $d) {
            try {
                $existing = $this->db->table('migration_adapters')->where('panel_type', $d[0])->first();
                if (!$existing) {
                    $this->db->table('migration_adapters')->insertGetId([
                        'panel_type' => $d[0], 'adapter_class' => $d[1], 'is_active' => 1, 'version' => '1.0',
                    ]);
                }
            } catch (\Exception $e) {}
        }
    }

    public function getAdapter(string $panelType): ?MigrationInterface
    {
        try {
            $row = $this->db->table('migration_adapters')->where('panel_type', $panelType)->where('is_active', 1)->first();
            if (!$row || !class_exists($row->adapter_class)) return null;
            return new $row->adapter_class();
        } catch (\Exception $e) { return null; }
    }

    public function getAvailableAdapters(): array
    {
        $adapters = [];
        try {
            $rows = $this->db->table('migration_adapters')->where('is_active', 1)->get() ?: [];
            foreach ($rows as $r) {
                if (class_exists($r->adapter_class)) {
                    $inst = new $r->adapter_class();
                    $adapters[$r->panel_type] = [
                        'name' => $inst->getPanelName(),
                        'icon' => $inst->getPanelIcon(),
                        'port' => $inst->getDefaultPort(),
                        'source_types' => $inst->getSupportedSourceTypes(),
                        'migration_types' => $inst->getSupportedMigrationTypes(),
                        'conversion_rules' => $inst->getConversionRules(),
                        'adapter_class' => $r->adapter_class,
                        'version' => $r->version,
                    ];
                }
            }
        } catch (\Exception $e) {}
        return $adapters;
    }

    public function createJob(array $data): int
    {
        return $this->db->table('migration_jobs')->insertGetId([
            'source_type' => $data['source_type'],
            'source_host' => $data['source_host'] ?? '',
            'source_port' => (int)($data['source_port'] ?? 0),
            'source_username' => $data['source_username'] ?? '',
            'migration_type' => $data['migration_type'] ?? 'single_customer',
            'source_transport' => $data['source_transport'] ?? 'live_ssh',
            'status' => 'pending',
            'step' => 1,
            'resume_token' => bin2hex(random_bytes(16)),
        ]);
    }

    public function updateJob(int $jobId, array $data)
    {
        $this->db->table('migration_jobs')->where('id', $jobId)->update($data);
    }

    public function getJob(int $jobId): ?object
    {
        return $this->db->table('migration_jobs')->where('id', $jobId)->first();
    }

    public function getJobs(int $limit = 50): array
    {
        $rows = $this->db->table('migration_jobs')->orderBy('id', 'DESC')->limit($limit)->get() ?: [];
        $result = [];
        foreach ($rows as $r) $result[] = $r;
        return $result;
    }

    public function runPreflight(int $jobId, string $host, int $port, string $user, string $pass, ?string $apiKey = null): array
    {
        $job = $this->getJob($jobId);
        if (!$job) return ['error' => 'Job not found'];

        $adapter = $this->getAdapter($job->source_type);
        if (!$adapter) return ['error' => 'Adapter not found for ' . $job->source_type];

        $this->updateJob($jobId, ['status' => 'preflight', 'step' => 3]);

        $connection = $adapter->testConnection($host, $port, $user, $pass, $apiKey);
        if (!$connection['connected']) {
            $this->updateJob($jobId, ['status' => 'failed', 'error_message' => $connection['error'] ?? 'Connection failed']);
            return $connection;
        }

        $preflight = $adapter->preflight($host, $port, $user, $pass, $apiKey);
        $this->updateJob($jobId, [
            'preflight_data' => json_encode($preflight),
            'status' => 'compat_check',
            'step' => 4,
        ]);

        return $preflight;
    }

    public function runCompatibilityCheck(int $jobId): array
    {
        $job = $this->getJob($jobId);
        if (!$job) return ['error' => 'Job not found'];

        $adapter = $this->getAdapter($job->source_type);
        if (!$adapter) return ['error' => 'Adapter not found'];

        $preflight = json_decode($job->preflight_data ?? '{}', true);
        $accounts = $preflight['accounts'] ?? [];
        $conversionRules = $adapter->getConversionRules();

        $compat = [
            'total_accounts' => count($accounts),
            'compatible' => 0,
            'incompatible' => 0,
            'warnings' => [],
            'accounts' => [],
        ];

        foreach ($accounts as $a) {
            $accountCompat = ['username' => $a['username'] ?? '?', 'domain' => $a['domain'] ?? '', 'compatible' => true, 'warnings' => []];
            $homeCheck = "/home/{$a['username']}";
            if (!empty($a['disk_used']) && (float)$a['disk_used'] > 10000) {
                $accountCompat['warnings'][] = 'Large disk usage: ' . round((float)$a['disk_used'], 1) . 'MB';
                $accountCompat['compatible'] = false;
            }
            if (!empty($a['php_version']) && !in_array($a['php_version'], $conversionRules['php_versions'])) {
                $accountCompat['warnings'][] = "PHP version {$a['php_version']} may need conversion";
            }
            if ($accountCompat['compatible']) $compat['compatible']++;
            else $compat['incompatible']++;
            $compat['accounts'][] = $accountCompat;
            $compat['warnings'] = array_merge($compat['warnings'], $accountCompat['warnings']);
        }

        $this->updateJob($jobId, ['compat_data' => json_encode($compat), 'status' => 'package_map', 'step' => 5]);
        return $compat;
    }

    public function runConversion(int $jobId, array $packageMap): array
    {
        $job = $this->getJob($jobId);
        if (!$job) return ['error' => 'Job not found'];

        $preflight = json_decode($job->preflight_data ?? '{}', true);
        $accounts = $preflight['accounts'] ?? [];
        $converted = [];

        foreach ($accounts as $a) {
            $packageId = null;
            $planName = $a['plan'] ?? $a['package'] ?? '';
            if ($planName && isset($packageMap[$planName])) {
                $targetPkg = $packageMap[$planName];
                if ($targetPkg === 'create_new') {
                    $packageId = $this->conversion->convertPackage($a, $planName);
                } elseif (is_numeric($targetPkg)) {
                    $packageId = (int)$targetPkg;
                }
            }
            $converted[] = [
                'username' => $a['username'],
                'domain' => $a['domain'] ?? '',
                'package_id' => $packageId,
                'email' => $a['email'] ?? '',
                'php_version' => $this->conversion->convertPhpVersion($a['php_version'] ?? ''),
                'disk_used' => $a['disk_used'] ?? 0,
                'disk_limit' => $a['disk_limit'] ?? 0,
            ];
        }

        $this->updateJob($jobId, ['conversion_data' => json_encode($converted), 'status' => 'migrating', 'step' => 8]);
        return $converted;
    }

    public function executeMigration(int $jobId, array $selectedItems, callable $progressFn = null): array
    {
        $job = $this->getJob($jobId);
        if (!$job) return ['success' => false, 'error' => 'Job not found'];

        $adapter = $this->getAdapter($job->source_type);
        if (!$adapter) return ['success' => false, 'error' => 'Adapter not found'];

        $creds = json_decode($_SESSION['migration_creds'] ?? '{}', true);
        $host = $creds['host'] ?? $job->source_host;
        $port = (int)($creds['port'] ?? $job->source_port);
        $user = $creds['user'] ?? $job->source_username;
        $pass = $creds['pass'] ?? '';
        $apiKey = $creds['api_key'] ?? '';

        $packageMap = json_decode($job->package_map ?? '{}', true);
        $logData = [];
        $logFn = function($msg) use (&$logData) { $logData[] = $msg; };

        $this->updateJob($jobId, ['status' => 'migrating', 'step' => 8, 'selected_items' => json_encode($selectedItems)]);

        // Create restore point before migration
        $restorePointId = null;
        try {
            $restoreManager = new RestoreManager();
            $restorePointId = $restoreManager->createRestorePoint(0, "Pre-migration: {$job->source_type}", '', ['system'], 'completed', 'Automatic pre-migration safety point');
        } catch (\Exception $e) {}

        $rollbackData = $adapter->migrate($selectedItems, $packageMap, ['host' => $host, 'port' => $port, 'user' => $user, 'pass' => $pass, 'api_key' => $apiKey], $logFn);
        $migrated = count($rollbackData);

        // Run validation
        $validation = $adapter->validateMigration(array_column($rollbackData, 'id'), []);

        $this->updateJob($jobId, [
            'status' => 'validating',
            'step' => 9,
            'items_migrated' => $migrated,
            'total_items' => count($selectedItems),
            'rollback_data' => json_encode($rollbackData),
            'validation_data' => json_encode($validation),
            'log' => implode("\n", $logData),
            'restore_point_id' => $restorePointId,
        ]);

        return [
            'success' => true,
            'migrated' => $migrated,
            'total' => count($selectedItems),
            'rollback_data' => $rollbackData,
            'validation' => $validation,
            'log' => $logData,
        ];
    }

    public function completeJob(int $jobId)
    {
        $this->updateJob($jobId, ['status' => 'completed', 'step' => 10, 'completed_at' => date('Y-m-d H:i:s')]);
    }

    public function rollbackJob(int $jobId): bool
    {
        $job = $this->getJob($jobId);
        if (!$job || !$job->rollback_data) return false;

        $adapter = $this->getAdapter($job->source_type);
        if (!$adapter) return false;

        $rollbackData = json_decode($job->rollback_data, true);
        $logFn = function($msg) {};

        $success = $adapter->rollback($rollbackData, $logFn);
        $this->updateJob($jobId, ['status' => 'rolled_back', 'error_message' => 'Rolled back on ' . date('Y-m-d H:i:s')]);
        return $success;
    }

    public function resumeJob(int $jobId): bool
    {
        $job = $this->getJob($jobId);
        if (!$job || $job->status !== 'failed') return false;

        $this->updateJob($jobId, ['status' => 'migrating', 'error_message' => null]);
        return true;
    }

    public function getConversionEngine(): ConversionEngine
    {
        return $this->conversion;
    }

    public function getRestoreManager(): RestoreManager
    {
        return $this->restoreManager;
    }
}
