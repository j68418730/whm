<?php

namespace Admin\Controllers;

use Core\Controller;
use Admin\Services\Migration\MigrationManager;
use Admin\Services\Migration\MigrationAnalysisEngine;
use Admin\Services\Migration\VerificationEngine;
use Admin\Services\Migration\MigrationReportGenerator;

class MigrationController extends Controller
{
    protected $auth, $request, $response, $db, $migration;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        $this->migration = new MigrationManager();
    }

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
    }

    protected function theme()
    {
        $user = $this->auth->user();
        return json_decode($user->theme_settings ?? '{}', true);
    }

    public function index()
    {
        $this->guard();
        $user = $this->auth->user();
        $adapters = $this->migration->getAvailableAdapters();
        $jobs = $this->migration->getJobs(20);
        $step = (int)($this->request->get('step', 1));
        $jobId = (int)($this->request->get('job', 0));
        $job = $jobId ? $this->migration->getJob($jobId) : null;
        $packages = [];
        try { $packages = $this->db->table('hosting_packages')->get() ?: []; } catch (\Exception $e) {}
        $theme_settings = $this->theme();
        $restoreTypes = $this->migration->getRestoreManager()->getRestoreTypes();

        return $this->view('admin.migration.index', [
            'user' => $user, 'title' => 'Migration & Restore Center',
            'theme_settings' => $theme_settings,
            'adapters' => $adapters, 'jobs' => $jobs,
            'step' => $step, 'job' => $job, 'jobId' => $jobId,
            'packages' => $packages,
            'restoreTypes' => $restoreTypes,
        ]);
    }

    public function adapters()
    {
        $this->guard();
        $user = $this->auth->user();
        $adapters = $this->migration->getAvailableAdapters();
        $theme_settings = $this->theme();
        return $this->view('admin.migration.index', [
            'user' => $user, 'adapters' => $adapters,
            'adapterView' => true, 'theme_settings' => $theme_settings,
        ]);
    }

    // ── Step 1→2: Start a new migration ──
    public function start()
    {
        $this->guard();
        $sourceType = $this->request->post('source_type', '');
        $sourceHost = $this->request->post('source_host', '');
        $sourcePort = (int)$this->request->post('source_port', 0);
        $sourceUser = $this->request->post('source_username', '');
        $migrationType = $this->request->post('migration_type', 'single_customer');
        $sourceTransport = $this->request->post('source_transport', 'live_ssh');

        if (!$sourceType || !$this->migration->getAdapter($sourceType)) {
            $_SESSION['error_message'] = 'Invalid source type or adapter not found.';
            $this->response->redirect('/admin/migration'); exit;
        }

        $jobId = $this->migration->createJob([
            'source_type' => $sourceType, 'source_host' => $sourceHost,
            'source_port' => $sourcePort, 'source_username' => $sourceUser,
            'migration_type' => $migrationType, 'source_transport' => $sourceTransport,
        ]);

        $_SESSION['migration_job_id'] = $jobId;
        $_SESSION['migration_creds'] = json_encode([
            'type' => $sourceType, 'host' => $sourceHost, 'port' => $sourcePort,
            'user' => $sourceUser, 'pass' => $this->request->post('source_password', ''),
            'api_key' => $this->request->post('api_key', ''),
            'ssh_key' => $this->request->post('ssh_key', ''),
            'panel_url' => $this->request->post('panel_url', ''),
        ]);

        $this->response->redirect("/admin/migration/run-preflight/{$jobId}");
        exit;
    }

    // ── Step 2→3: Test connection ──
    public function testConnection()
    {
        $this->guard();
        $sourceType = $this->request->post('source_type', '');
        $host = $this->request->post('source_host', '');
        $port = (int)$this->request->post('source_port', 0);
        $user = $this->request->post('source_username', '');
        $pass = $this->request->post('source_password', '');
        $apiKey = $this->request->post('api_key', '');

        $adapter = $this->migration->getAdapter($sourceType);
        if (!$adapter) {
            $this->response->json(['connected' => false, 'error' => 'Adapter not found'])->send(); exit;
        }

        $result = $adapter->testConnection($host, $port, $user, $pass, $apiKey);
        $this->response->json($result)->send();
        exit;
    }

    // ── Step 2→3: Run preflight analysis ──
    public function preflight($jobId)
    {
        $this->guard();
        $jobId = (int)$jobId;
        if (!$jobId) { $this->response->redirect('/admin/migration'); exit; }

        $creds = json_decode($_SESSION['migration_creds'] ?? '{}', true);
        $result = $this->migration->runPreflight(
            $jobId, $creds['host'] ?? '', (int)($creds['port'] ?? 0),
            $creds['user'] ?? '', $creds['pass'] ?? '', $creds['api_key'] ?? ''
        );

        if (!empty($result['error'])) {
            $_SESSION['error_message'] = 'Preflight failed: ' . $result['error'];
            $this->response->redirect("/admin/migration?step=2&job={$jobId}"); exit;
        }

        // Run full analysis
        $job = $this->migration->getJob($jobId);
        $adapter = $this->migration->getAdapter($job->source_type);
        $analysisEngine = new MigrationAnalysisEngine();
        $analysis = $analysisEngine->analyze($result, $job->source_type, $adapter ? $adapter->getConversionRules() : []);
        $this->migration->updateJob($jobId, ['analysis_data' => json_encode($analysis), 'step' => 4]);

        // Run compatibility check
        $compat = $this->migration->runCompatibilityCheck($jobId);

        $this->response->redirect("/admin/migration?step=3&job={$jobId}");
        exit;
    }

    // ── Step 3→4: Select accounts ──
    public function selectAccounts()
    {
        $this->guard();
        $jobId = (int)$this->request->get('job', 0);
        if (!$jobId) { $this->response->redirect('/admin/migration'); exit; }

        $selected = $this->request->post('selected_accounts', []);
        $this->migration->updateJob($jobId, [
            'selected_items' => json_encode($selected),
            'total_items' => count($selected),
            'step' => 5,
        ]);

        $this->response->redirect("/admin/migration?step=5&job={$jobId}");
        exit;
    }

    // ── Step navigation ──
    public function goToStep($step)
    {
        $this->guard();
        $jobId = (int)$this->request->get('job', 0);
        $step = (int)$step;
        if ($step > 1 && $jobId) {
            $this->migration->updateJob($jobId, ['step' => $step]);
        }
        $this->response->redirect("/admin/migration?step={$step}&job={$jobId}");
        exit;
    }

    // ── Step 6→7: Save package map ──
    public function savePackageMap()
    {
        $this->guard();
        $jobId = (int)$this->request->get('job', 0);
        if (!$jobId) { $this->response->redirect('/admin/migration'); exit; }

        $packageMap = $this->request->post('package_map', []);
        $selectedItems = $this->request->post('selected_items', []);
        $this->migration->updateJob($jobId, [
            'package_map' => json_encode($packageMap),
            'selected_items' => json_encode($selectedItems),
            'status' => 'converting', 'step' => 7,
        ]);

        $converted = $this->migration->runConversion($jobId, $packageMap);
        $this->response->redirect("/admin/migration?step=7&job={$jobId}");
        exit;
    }

    // ── Step 7→8: Start the actual migration ──
    public function startMigration()
    {
        $this->guard();
        $jobId = (int)$this->request->get('job', 0);
        if (!$jobId) { $this->response->redirect('/admin/migration'); exit; }

        $options = $this->request->post('migration_options', []);
        $this->migration->updateJob($jobId, [
            'migration_options' => json_encode($options),
            'status' => 'migrating', 'step' => 8,
        ]);

        $this->response->redirect("/admin/migration?step=8&job={$jobId}");
        exit;
    }

    // ── Step 8: Execute migration (AJAX + initial call) ──
    public function execute()
    {
        $this->guard();
        $jobId = (int)$this->request->get('job', 0);
        if (!$jobId) { $this->response->json(['error' => 'No job'])->send(); exit; }

        $job = $this->migration->getJob($jobId);
        $selectedItems = json_decode($job->selected_items ?? '[]', true);

        if ($this->request->method() === 'POST') {
            $result = $this->migration->executeMigration($jobId, $selectedItems);
            $this->response->json(['started' => true, 'jobId' => $jobId])->send();
            exit;
        }

        $result = $this->migration->executeMigration($jobId, $selectedItems);
        if ($result['success']) {
            // Run verification
            $verifier = new VerificationEngine();
            $preflight = json_decode($job->preflight_data ?? '{}', true);
            $validationData = [
                'paths' => $preflight['home_directories'] ?? [],
                'databases' => $preflight['databases'] ?? [],
                'email_accounts' => $preflight['email_accounts'] ?? [],
                'zones' => $preflight['dns_zones'] ?? [],
                'certificates' => $preflight['ssl_certificates'] ?? [],
                'stations' => $preflight['streaming_stations'] ?? [],
            ];
            $validation = $verifier->verifyAll($validationData);
            $this->migration->updateJob($jobId, [
                'validation_data' => json_encode($validation),
                'step' => 9, 'status' => 'validating',
            ]);
            $this->response->redirect("/admin/migration?step=9&job={$jobId}");
        } else {
            $_SESSION['error_message'] = 'Migration failed: ' . ($result['error'] ?? 'Unknown');
            $this->response->redirect("/admin/migration?step=8&job={$jobId}");
        }
        exit;
    }

    // ── Step 8: Real-time progress polling ──
    public function progress($jobId)
    {
        $this->guard();
        $jobId = (int)$jobId;
        $job = $this->migration->getJob($jobId);
        if (!$job) { $this->response->json(['error' => 'Not found'])->send(); exit; }

        $total = max($job->total_items, 1);
        $pct = min(100, round(($job->items_migrated / $total) * 100));

        // Extract progress info from log
        $log = $job->log ?? '';
        $lines = explode("\n", trim($log));
        $lastLine = count($lines) > 0 ? end($lines) : '';

        $this->response->json([
            'progress' => $pct,
            'items_migrated' => $job->items_migrated,
            'total_items' => $total,
            'current_file' => $lastLine,
            'current_account' => $this->extractCurrentAccount($lastLine),
            'speed' => $this->extractSpeed($lastLine),
            'eta' => $pct > 0 && $pct < 100 ? round((100 - $pct) / max($pct, 1) * 30) . 's' : '--',
            'log' => $log,
            'errors' => $job->error_message ?? '',
            'status' => $job->status,
            'completed' => in_array($job->status, ['completed', 'failed', 'validating']),
        ])->send();
        exit;
    }

    protected function extractCurrentAccount(string $line): string
    {
        if (preg_match('/account[:\s]+(\S+)/i', $line, $m)) return $m[1];
        if (preg_match('/migrating[:\s]+(\S+)/i', $line, $m)) return $m[1];
        return '';
    }

    protected function extractSpeed(string $line): string
    {
        if (preg_match('/(\d+\.?\d*)\s*(MB|KB|GB)\/s/i', $line, $m)) return $m[1] . ' ' . $m[2] . '/s';
        return '-- MB/s';
    }

    // ── Step 9→10: Complete ──
    public function complete($jobId)
    {
        $this->guard();
        $jobId = (int)$jobId;
        $this->migration->completeJob($jobId);
        $this->response->redirect("/admin/migration?step=10&job={$jobId}");
        exit;
    }

    // ── Step 10: View report ──
    public function report($jobId)
    {
        $this->guard();
        $jobId = (int)$jobId;
        $job = $this->migration->getJob($jobId);
        if (!$job) { $this->response->redirect('/admin/migration'); exit; }

        $generator = new MigrationReportGenerator();
        $preflight = json_decode($job->preflight_data ?? '{}', true);
        $analysis = json_decode($job->analysis_data ?? '{}', true);
        $compat = json_decode($job->compat_data ?? '{}', true);
        $conversion = json_decode($job->conversion_data ?? '{}', true);
        $validation = json_decode($job->validation_data ?? '{}', true);
        $generator->streamReport((array)$job, $analysis, $preflight, $compat, $conversion, $validation);
        exit;
    }

    // ── Step 10: Download PDF ──
    public function reportPdf($jobId)
    {
        $this->guard();
        $jobId = (int)$jobId;
        $job = $this->migration->getJob($jobId);
        if (!$job) { $this->response->redirect('/admin/migration'); exit; }

        $generator = new MigrationReportGenerator();
        $preflight = json_decode($job->preflight_data ?? '{}', true);
        $analysis = json_decode($job->analysis_data ?? '{}', true);
        $compat = json_decode($job->compat_data ?? '{}', true);
        $conversion = json_decode($job->conversion_data ?? '{}', true);
        $validation = json_decode($job->validation_data ?? '{}', true);

        $pdfPath = $generator->generatePdf((array)$job, $analysis, $preflight, $compat, $conversion, $validation);
        if (file_exists($pdfPath)) {
            $ext = pathinfo($pdfPath, PATHINFO_EXTENSION);
            $contentType = $ext === 'pdf' ? 'application/pdf' : 'text/html';
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="migration_report_' . $jobId . '.' . $ext . '"');
            readfile($pdfPath);
            exit;
        }
        $_SESSION['error_message'] = 'Could not generate report.';
        $this->response->redirect("/admin/migration?step=10&job={$jobId}");
        exit;
    }

    // ── Rollback ──
    public function rollback()
    {
        $this->guard();
        $jobId = (int)$this->request->get('job', 0);
        if (!$jobId) { $this->response->redirect('/admin/migration'); exit; }

        $success = $this->migration->rollbackJob($jobId);
        if ($success) {
            $_SESSION['success_message'] = 'Migration rolled back successfully.';
        } else {
            $_SESSION['error_message'] = 'Rollback failed.';
        }
        $this->response->redirect("/admin/migration?job={$jobId}");
        exit;
    }

    // ── Resume failed migration ──
    public function resume($jobId)
    {
        $this->guard();
        $jobId = (int)$jobId;
        if ($this->migration->resumeJob($jobId)) {
            $_SESSION['success_message'] = 'Migration resumed.';
        } else {
            $_SESSION['error_message'] = 'Cannot resume.';
        }
        $this->response->redirect("/admin/migration?step=8&job={$jobId}");
        exit;
    }

    // ── Get preflight data (JSON) ──
    public function getPreflight($jobId)
    {
        $this->guard();
        $jobId = (int)$jobId;
        $job = $this->migration->getJob($jobId);
        if (!$job) { $this->response->json(['error' => 'Job not found'])->send(); exit; }
        $this->response->json([
            'job' => $job,
            'preflight' => json_decode($job->preflight_data ?? '{}', true),
            'compat' => json_decode($job->compat_data ?? '{}', true),
            'conversion' => json_decode($job->conversion_data ?? '{}', true),
        ])->send();
        exit;
    }
}
