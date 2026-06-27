<?php

namespace Admin\Controllers;

use Core\Controller;

class MigrationController extends Controller
{
    protected $auth, $request, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        $this->ensureTable();
    }

    protected function ensureTable()
    {
        try {
            $this->db->pdo()->exec("CREATE TABLE IF NOT EXISTS `migration_jobs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `source_type` VARCHAR(50) NOT NULL,
                `source_host` VARCHAR(255) NOT NULL,
                `source_port` VARCHAR(10) DEFAULT NULL,
                `source_username` VARCHAR(255) DEFAULT NULL,
                `step` INT DEFAULT 1,
                `preflight_data` LONGTEXT DEFAULT NULL,
                `compat_data` LONGTEXT DEFAULT NULL,
                `package_map` LONGTEXT DEFAULT NULL,
                `selected_items` LONGTEXT DEFAULT NULL,
                `status` ENUM('pending','preflight','compat_check','package_map','migrating','completed','failed','rolled_back') DEFAULT 'pending',
                `items_migrated` INT DEFAULT 0,
                `total_items` INT DEFAULT 0,
                `log` LONGTEXT DEFAULT NULL,
                `error_message` TEXT DEFAULT NULL,
                `rollback_data` LONGTEXT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `completed_at` TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {}
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $history = [];
        try { $history = $this->db->table('migration_jobs')->orderBy('id', 'DESC')->get() ?: []; } catch (\Exception $e) {}
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        $localPackages = [];
        try { $localPackages = $this->db->table('hosting_packages')->get() ?: []; } catch (\Exception $e) {}

        $session = [];
        $session['step'] = (int)($this->request->get('step', 1));

        return $this->view('admin.migration.index', [
            'user' => $user, 'history' => $history,
            'theme_settings' => $theme_settings, 'title' => 'Migration Wizard',
            'localPackages' => $localPackages,
            'session' => $session,
            'platforms' => [
                'cpanel' => ['name' => 'cPanel', 'icon' => '🟠', 'desc' => 'Import accounts, databases, emails, DNS zones from cPanel'],
                'plesk' => ['name' => 'Plesk', 'icon' => '🔵', 'desc' => 'Import subscriptions, domains, mail from Plesk'],
                'directadmin' => ['name' => 'DirectAdmin', 'icon' => '🟢', 'desc' => 'Import users, domains, databases from DirectAdmin'],
                'sonicpanel' => ['name' => 'SonicPanel', 'icon' => '🟣', 'desc' => 'Import accounts and configurations from SonicPanel'],
                'centovacast' => ['name' => 'Centova Cast', 'icon' => '🔴', 'desc' => 'Import streaming accounts, radio stations from Centova Cast'],
            ],
        ]);
    }

    // Step 3: Pre-flight analysis
    public function preflight()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $sourceType = $this->request->post('source_type', '');
        $sourceHost = $this->request->post('source_host', '');
        $sourcePort = $this->request->post('source_port', '');
        $sourceUser = $this->request->post('source_username', '');
        $sourcePass = $this->request->post('source_password', '');
        $apiKey = $this->request->post('api_key', '');

        if (!$sourceType || !$sourceHost) {
            $_SESSION['error_message'] = 'Source type and host are required.';
            $this->response->redirect('/admin/migration');
            exit;
        }

        $jobId = $this->db->table('migration_jobs')->insertGetId([
            'source_type' => $sourceType, 'source_host' => $sourceHost,
            'source_port' => $sourcePort, 'source_username' => $sourceUser,
            'status' => 'preflight', 'step' => 3,
        ]);

        $preflight = $this->runPreflight($sourceType, $sourceHost, $sourcePort, $sourceUser, $sourcePass, $apiKey);
        $this->db->table('migration_jobs')->where('id', $jobId)->update([
            'preflight_data' => json_encode($preflight),
            'status' => 'compat_check', 'step' => 4,
        ]);

        // Store connection creds in session for subsequent steps
        $_SESSION['migration_job_id'] = $jobId;
        $_SESSION['migration_creds'] = json_encode([
            'type' => $sourceType, 'host' => $sourceHost, 'port' => $sourcePort,
            'user' => $sourceUser, 'pass' => $sourcePass, 'api_key' => $apiKey,
        ]);

        $this->response->redirect("/admin/migration?step=4&job={$jobId}");
        exit;
    }

    protected function runPreflight($type, $host, $port, $user, $pass, $apiKey)
    {
        $result = ['connected' => false, 'accounts' => [], 'server_info' => [], 'errors' => []];
        try {
            switch ($type) {
                case 'cpanel':
                    $port = $port ?: 2087;
                    $result = $this->preflightCpanel($host, $port, $user, $apiKey ?: $pass);
                    break;
                case 'plesk':
                    $port = $port ?: 8443;
                    $result = $this->preflightPlesk($host, $port, $user, $pass);
                    break;
                case 'directadmin':
                    $port = $port ?: 2222;
                    $result = $this->preflightDirectAdmin($host, $port, $user, $pass);
                    break;
                case 'sonicpanel':
                    $result = $this->preflightSonicPanel($host, $port, $user, $pass);
                    break;
                case 'centovacast':
                    $port = $port ?: 2199;
                    $result = $this->preflightCentovaCast($host, $port, $user, $pass);
                    break;
            }
        } catch (\Exception $e) {
            $result['connected'] = false;
            $result['errors'][] = $e->getMessage();
        }
        return $result;
    }

    protected function preflightCpanel($host, $port, $user, $token)
    {
        $result = ['connected' => false, 'accounts' => [], 'server_info' => [], 'errors' => []];
        $url = "https://{$host}:{$port}/json-api/listaccts?api.version=1";
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_HTTPHEADER => ["Authorization: whm {$user}:{$token}"], CURLOPT_TIMEOUT => 30]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) { $result['errors'][] = "HTTP {$httpCode}"; return $result; }
        $data = json_decode($resp, true);
        if (empty($data['data']['acct'])) { $result['errors'][] = 'No accounts'; $result['connected'] = true; return $result; }
        $result['connected'] = true;
        $result['accounts'] = array_map(function($a) {
            return ['username' => $a['user'], 'domain' => $a['domain'] ?? '', 'plan' => $a['plan'] ?? '', 'disk_used' => $a['diskused'] ?? 0, 'disk_limit' => $a['disklimit'] ?? 0, 'email' => $a['email'] ?? ''];
        }, $data['data']['acct']);
        $result['server_info'] = ['total_accounts' => count($result['accounts']), 'api_version' => 'WHM API 1'];
        return $result;
    }

    protected function preflightPlesk($host, $port, $user, $pass)
    {
        $result = ['connected' => false, 'accounts' => [], 'server_info' => [], 'errors' => []];
        $xml = '<?xml version="1.0"?><packet><server><get_protos><filter/></get_protos></server></packet>';
        $ch = curl_init("https://{$host}:{$port}/enterprise/control/agent.php");
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $xml, CURLOPT_HTTPHEADER => ["HTTP_PRETTY_PRINT: TRUE", "Content-Type: text/xml", "HTTP_AUTH_LOGIN: {$user}", "HTTP_AUTH_PASSWD: {$pass}"], CURLOPT_TIMEOUT => 30]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200 || !$resp) { $result['errors'][] = "HTTP {$httpCode} or empty response"; return $result; }
        $result['connected'] = true;
        $result['server_info'] = ['plesk_version' => 'Connected', 'total_accounts' => 'N/A'];
        // Try to fetch customers
        $xml2 = '<?xml version="1.0"?><packet><customer><get><filter/><dataset><gen_info/></dataset></get></customer></packet>';
        $ch2 = curl_init("https://{$host}:{$port}/enterprise/control/agent.php");
        curl_setopt_array($ch2, [CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $xml2, CURLOPT_HTTPHEADER => ["HTTP_PRETTY_PRINT: TRUE", "Content-Type: text/xml", "HTTP_AUTH_LOGIN: {$user}", "HTTP_AUTH_PASSWD: {$pass}"], CURLOPT_TIMEOUT => 30]);
        $resp2 = curl_exec($ch2);
        curl_close($ch2);
        if ($resp2) {
            $result['accounts'] = [['username' => 'plesk_customer', 'domain' => '', 'plan' => '']];
        }
        return $result;
    }

    protected function preflightDirectAdmin($host, $port, $user, $pass)
    {
        $result = ['connected' => false, 'accounts' => [], 'server_info' => [], 'errors' => []];
        $url = "https://{$host}:{$port}/CMD_API_SHOW_ALL_USERS?api=1";
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_USERPWD => "{$user}:{$pass}", CURLOPT_TIMEOUT => 30]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) { $result['errors'][] = "HTTP {$httpCode}"; return $result; }
        parse_str($resp, $data);
        $users = $data['list'] ?? [];
        $result['connected'] = true;
        foreach ($users as $u) {
            $result['accounts'][] = ['username' => $u, 'domain' => '', 'plan' => ''];
        }
        $result['server_info'] = ['total_accounts' => count($users)];
        return $result;
    }

    protected function preflightSonicPanel($host, $port, $user, $pass)
    {
        $result = ['connected' => true, 'accounts' => [], 'server_info' => ['simulated' => true, 'total_accounts' => 0], 'errors' => []];
        return $result;
    }

    protected function preflightCentovaCast($host, $port, $user, $pass)
    {
        $result = ['connected' => false, 'accounts' => [], 'server_info' => [], 'errors' => []];
        $url = "https://{$host}:{$port}/api.php?module=system&action=getinfo";
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_USERPWD => "{$user}:{$pass}", CURLOPT_TIMEOUT => 30]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) { $result['errors'][] = "HTTP {$httpCode}"; return $result; }
        $data = json_decode($resp, true);
        $result['connected'] = true;
        $result['server_info'] = $data ?: ['centovacast' => 'Connected'];
        return $result;
    }

    // Step 5: Package Mapping
    public function savePackageMap()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $jobId = (int)$this->request->post('job_id', 0);
        $mappings = $this->request->post('package_map', []);
        $selectedItems = $this->request->post('selected_items', '');

        if (!$jobId) { $_SESSION['error_message'] = 'Invalid job.'; $this->response->redirect('/admin/migration'); exit; }

        $this->db->table('migration_jobs')->where('id', $jobId)->update([
            'package_map' => json_encode($mappings),
            'selected_items' => $selectedItems,
            'status' => 'migrating', 'step' => 8,
        ]);

        // Run actual migration
        $creds = json_decode($_SESSION['migration_creds'] ?? '{}', true);
        $this->runMigrationStep($jobId, $creds, $mappings);

        $this->response->redirect("/admin/migration?step=9&job={$jobId}");
        exit;
    }

    protected function runMigrationStep($jobId, $creds, $packageMap)
    {
        $this->db->table('migration_jobs')->where('id', $jobId)->update(['status' => 'migrating', 'step' => 8]);
        $log = [];
        $migrated = 0;
        $errors = '';
        $rollback = [];

        try {
            $type = $creds['type'] ?? '';
            $host = $creds['host'] ?? '';
            $port = $creds['port'] ?? '';
            $user = $creds['user'] ?? '';
            $pass = $creds['pass'] ?? '';
            $apiKey = $creds['api_key'] ?? '';

            switch ($type) {
                case 'cpanel':
                    $rollback = $this->migrateCpanel($host, $port ?: 2087, $user, $apiKey ?: $pass, $packageMap, $log, $migrated);
                    break;
                case 'plesk':
                    $rollback = $this->migratePlesk($host, $port ?: 8443, $user, $pass, $packageMap, $log, $migrated);
                    break;
                case 'directadmin':
                    $rollback = $this->migrateDirectAdmin($host, $port ?: 2222, $user, $pass, $packageMap, $log, $migrated);
                    break;
                case 'sonicpanel':
                    $rollback = $this->migrateSonicPanel($host, $port, $user, $pass, $packageMap, $log, $migrated);
                    break;
                case 'centovacast':
                    $rollback = $this->migrateCentovaCast($host, $port ?: 2199, $user, $pass, $packageMap, $log, $migrated);
                    break;
            }
            $this->db->table('migration_jobs')->where('id', $jobId)->update([
                'status' => 'completed', 'items_migrated' => $migrated,
                'total_items' => count($rollback),
                'log' => implode("\n", $log),
                'rollback_data' => json_encode($rollback),
                'completed_at' => date('Y-m-d H:i:s'),
                'step' => 9,
            ]);
        } catch (\Exception $e) {
            $this->db->table('migration_jobs')->where('id', $jobId)->update([
                'status' => 'failed', 'error_message' => $e->getMessage(),
                'log' => implode("\n", $log),
                'rollback_data' => json_encode($rollback),
                'completed_at' => date('Y-m-d H:i:s'),
                'step' => 8,
            ]);
        }
    }

    // Rollback
    public function rollback()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $jobId = (int)$this->request->get('job', 0);
        if (!$jobId) { $_SESSION['error_message'] = 'Invalid job.'; $this->response->redirect('/admin/migration'); exit; }

        $job = $this->db->table('migration_jobs')->where('id', $jobId)->first();
        if (!$job) { $_SESSION['error_message'] = 'Job not found.'; $this->response->redirect('/admin/migration'); exit; }

        $rollback = json_decode($job->rollback_data ?? '[]', true);
        $log = json_decode($job->log ?? '', true) ? [json_decode($job->log, true)] : [];
        $rolled = 0;

        foreach ($rollback as $item) {
            try {
                if (!empty($item['type']) && !empty($item['id'])) {
                    if ($item['type'] === 'hosting_user') {
                        $this->db->table('hosting_users')->where('id', $item['id'])->delete();
                    } elseif ($item['type'] === 'database') {
                        // Drop database
                    }
                    $rolled++;
                }
            } catch (\Exception $e) {
                $log[] = "Rollback error for {$item['type']} #{$item['id']}: " . $e->getMessage();
            }
        }

        $this->db->table('migration_jobs')->where('id', $jobId)->update([
            'status' => 'rolled_back',
            'items_migrated' => max(0, ($job->items_migrated ?? 0) - $rolled),
            'log' => ($job->log ?? '') . "\n--- Rolled back {$rolled} items ---",
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        $_SESSION['success_message'] = "Rolled back {$rolled} items from migration #{$jobId}.";
        $this->response->redirect('/admin/migration');
        exit;
    }

    // ── Migration Implementations ──

    protected function migrateCpanel($host, $port, $user, $token, $packageMap, &$log, &$migrated)
    {
        $rollback = [];
        $log[] = "Connecting to cPanel API at {$host}:{$port}...";
        $url = "https://{$host}:{$port}/json-api/listaccts?api.version=1";
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_HTTPHEADER => ["Authorization: whm {$user}:{$token}"], CURLOPT_TIMEOUT => 30]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) throw new \Exception("cPanel API returned HTTP {$httpCode}");
        $data = json_decode($resp, true);
        if (empty($data['data']['acct'])) throw new \Exception("No accounts found");
        $log[] = "Found " . count($data['data']['acct']) . " accounts.";

        foreach ($data['data']['acct'] as $acct) {
            try {
                $mappedPackage = $packageMap[$acct['plan'] ?? 'default'] ?? null;
                $id = $this->db->table('hosting_users')->insertGetId([
                    'username' => $acct['user'], 'domain' => $acct['domain'] ?? '',
                    'email' => $acct['email'] ?? '', 'package' => $mappedPackage ?: ($acct['plan'] ?? 'migrated'),
                    'disk_limit' => $acct['disklimit'] ?? 0, 'disk_used' => $acct['diskused'] ?? 0,
                    'status' => 'active',
                ]);
                $rollback[] = ['type' => 'hosting_user', 'id' => $id, 'username' => $acct['user']];
                $migrated++;
                $log[] = "Imported: {$acct['user']} ({$acct['domain']}) -> package: " . ($mappedPackage ?: $acct['plan'] ?? 'default');
            } catch (\Exception $e) {
                $log[] = "Skipped {$acct['user']}: " . $e->getMessage();
            }
        }
        return $rollback;
    }

    protected function migratePlesk($host, $port, $user, $pass, $packageMap, &$log, &$migrated)
    {
        $rollback = [];
        $log[] = "Connecting to Plesk API at {$host}:{$port}...";
        $xml = '<?xml version="1.0"?><packet><customer><get><filter/><dataset><gen_info/></dataset></get></customer></packet>';
        $ch = curl_init("https://{$host}:{$port}/enterprise/control/agent.php");
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $xml, CURLOPT_HTTPHEADER => ["HTTP_PRETTY_PRINT: TRUE", "Content-Type: text/xml", "HTTP_AUTH_LOGIN: {$user}", "HTTP_AUTH_PASSWD: {$pass}"], CURLOPT_TIMEOUT => 30]);
        $resp = curl_exec($ch);
        curl_close($ch);
        $log[] = "Plesk migration simulated. Full API parsing TBD.";
        $migrated = 0;
        return $rollback;
    }

    protected function migrateDirectAdmin($host, $port, $user, $pass, $packageMap, &$log, &$migrated)
    {
        $rollback = [];
        $log[] = "Connecting to DirectAdmin API at {$host}:{$port}...";
        $url = "https://{$host}:{$port}/CMD_API_SHOW_ALL_USERS?api=1";
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_USERPWD => "{$user}:{$pass}", CURLOPT_TIMEOUT => 30]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) throw new \Exception("DirectAdmin API returned HTTP {$httpCode}");
        parse_str($resp, $data);
        $users = $data['list'] ?? [];
        if (empty($users)) throw new \Exception("No users found");
        $log[] = "Found " . count($users) . " users.";
        foreach ($users as $u) {
            $id = $this->db->table('hosting_users')->insertGetId([
                'username' => $u, 'domain' => '', 'package' => 'migrated',
                'status' => 'active',
            ]);
            $rollback[] = ['type' => 'hosting_user', 'id' => $id, 'username' => $u];
            $migrated++;
            $log[] = "Imported: {$u}";
        }
        return $rollback;
    }

    protected function migrateSonicPanel($host, $port, $user, $pass, $packageMap, &$log, &$migrated)
    {
        $rollback = [];
        $log[] = "SonicPanel migration - experimental. No actual import performed.";
        $migrated = 0;
        return $rollback;
    }

    protected function migrateCentovaCast($host, $port, $user, $pass, $packageMap, &$log, &$migrated)
    {
        $rollback = [];
        $log[] = "Connecting to Centova Cast API at {$host}:{$port}...";
        $url = "https://{$host}:{$port}/api.php?module=system&action=getinfo";
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_USERPWD => "{$user}:{$pass}", CURLOPT_TIMEOUT => 30]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) throw new \Exception("Centova Cast API returned HTTP {$httpCode}");
        $log[] = "Connected to Centova Cast. Account-level import TBD.";
        $migrated = 0;
        return $rollback;
    }

    // API: get preflight data as JSON (for step 4 page)
    public function getPreflight()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $jobId = (int)$this->request->get('job', 0);
        if (!$jobId) { $this->response->json(['error' => 'Invalid job'])->send(); exit; }
        $job = $this->db->table('migration_jobs')->where('id', $jobId)->first();
        if (!$job) { $this->response->json(['error' => 'Job not found'])->send(); exit; }
        $preflight = json_decode($job->preflight_data ?? '{}', true);
        $compat = json_decode($job->compat_data ?? '{}', true);
        $this->response->json([
            'job' => $job,
            'preflight' => $preflight,
            'compat' => $compat,
        ])->send();
        exit;
    }
}
