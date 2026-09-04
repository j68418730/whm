<?php

namespace Admin\Controllers;

use Core\Controller;

/**
 * Admin Security Center
 *
 * Integrates open-source security tools (ClamAV, YARA, Trivy, OSV, Lynis,
 * AIDE, rkhunter, chkrootkit, Logwatch, GoAccess, testssl, SpamAssassin,
 * OpenDKIM) via modular install/ scripts.
 *
 * The existing firewall UI (/admin/firewall) is unchanged.
 */
class SecurityController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;
    protected $tools;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        $this->tools = new \Services\SecurityToolsService($this->db);
    }

    protected function guard()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
    }

    public function index()
    {
        $this->guard();
        $user = $this->auth->user();
        $tools = $this->tools->summary();
        $score = $this->tools->score();
        $history = $this->tools->lastScanHistory(20);
        $results = $this->tools->scanResults();
        $hasFindings = $this->tools->hasFindings();

        // Group for display
        $groups = [];
        foreach ($tools as $t) $groups[$t['group']][] = $t;

        return $this->view('admin.security.index', [
            'user' => $user,
            'title' => 'Security Center',
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
            'groups' => $groups,
            'score' => $score,
            'history' => $history,
            'results' => $results,
            'hasFindings' => $hasFindings,
        ]);
    }

    // Run an installer module (or the whole installer)
    public function install($tool = null)
    {
        $this->guard();
        if ($tool && $tool !== 'all') {
            $t = $this->tools->tool($tool);
            if ($t) { $this->tools->runScript($t); $_SESSION['success_message'] = ucfirst($tool) . ' install started.'; }
        } else {
            exec('cd /var/www/radiohosting && sudo bash install-debian.sh > /dev/null 2>&1 &');
            $_SESSION['success_message'] = 'Full Security Center installer started in background.';
        }
        $this->response->redirect('/admin/security');
    }

    // Run a scan for a tool
    public function scan($tool)
    {
        $this->guard();
        $t = $this->tools->tool($tool);
        if ($t) {
            $this->tools->runScan($t);
            $_SESSION['success_message'] = ucfirst($tool) . ' scan started.';
        }
        $this->response->redirect('/admin/security');
    }

    // Run ALL scans in the background
    public function scanAll()
    {
        $this->guard();
        $res = $this->tools->runAllScans();
        if ($res['success']) {
            $_SESSION['success_message'] = 'All security scans started in the background. Check results shortly.';
        } else {
            $_SESSION['error_message'] = $res['error'] ?? 'Failed to start scans.';
        }
        $this->response->redirect('/admin/security');
    }

    // Run a fix for a tool that found issues
    public function fix($tool)
    {
        $this->guard();
        $res = $this->tools->runFix($tool);
        if (!empty($res['warn'])) {
            $_SESSION['warning_message'] = $res['message'];
        } else {
            $_SESSION['success_message'] = $res['message'];
        }
        $this->response->redirect('/admin/security');
    }

    // View a tool's log
    public function logs($tool)
    {
        $this->guard();
        $t = $this->tools->tool($tool);
        $lines = [];
        if ($t) $lines = $this->tools->logTail($t, 300);
        header('Content-Type: text/plain; charset=utf-8');
        echo $lines ? implode("\n", $lines) : 'No log available.';
        exit;
    }

    // Intrusion detection events (attack_alert.php monitor)
    public function intrusions()
    {
        $this->guard();
        $user = $this->auth->user();
        $limit = (int)($this->request->get('limit', 100));
        $type = $this->request->get('type', '');
        try {
            $pdo = $this->db->pdo();
            $where = ''; $params = [];
            if ($type) { $where = 'WHERE type = ?'; $params[] = $type; }
            $q = $pdo->prepare("SELECT * FROM attack_events {$where} ORDER BY last_seen DESC LIMIT " . (int)$limit);
            $q->execute($params);
            $events = $q->fetchAll(\PDO::FETCH_OBJ) ?: [];
            $types = $pdo->query("SELECT type, COUNT(*) c, MAX(last_seen) last FROM attack_events GROUP BY type ORDER BY c DESC")->fetchAll(\PDO::FETCH_OBJ) ?: [];
            $summary = $pdo->query("SELECT COUNT(*) total, SUM(CASE WHEN severity='critical' THEN 1 ELSE 0 END) crit, SUM(CASE WHEN resolved=0 THEN 1 ELSE 0 END) open, COUNT(DISTINCT ip) ips FROM attack_events")->fetch(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            $events = []; $types = []; $summary = (object)['total'=>0,'crit'=>0,'open'=>0,'ips'=>0];
        }
        return $this->view('admin.security.intrusions', [
            'user' => $user,
            'title' => 'Intrusion Detection',
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
            'events' => $events,
            'types' => $types,
            'summary' => $summary,
            'typeFilter' => $type,
        ]);
    }

    // Log Size Watchdog - check for oversized log files
    public function logwatchdog()
    {
        $this->guard();
        $user = $this->auth->user();
        
        // Run the check
        $tools = new \Services\SecurityToolsService($this->db);
        $logwatchdog = $tools->tool('logwatchdog');
        if ($logwatchdog) {
            $tools->runScan($logwatchdog);
        }
        
        // Read alerts
        $alerts = [];
        $alertFile = '/var/www/radiohosting/storage/security/logwatchdog.alerts';
        if (is_file($alertFile)) {
            $content = @file_get_contents($alertFile);
            $alerts = json_decode($content, true) ?: [];
        }
        
        // Also check current log file sizes
        $largeFiles = [];
        $dirs = ['/var/log/planethosts', '/var/log/radiohosting', '/var/log/apache2', '/var/log/shoutcast', '/var/log/icecast2', '/var/log/liquidsoap'];
        $threshold = 1024 * 1024 * 1024; // 1GB
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $files = glob("$dir/*");
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $size = filesize($file);
                        if ($size > 1024 * 1024 * 1024) { // 1GB
                            $largeFiles[] = [
                                'file' => $file,
                                'size' => $size,
                                'size_gb' => round($size / (1024*1024*1024), 2),
                                'size_mb' => round($size / (1024*1024), 2),
                            ];
                        }
                    }
                }
            }
        }
        
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.security.logwatchdog', [
            'user' => $user,
            'title' => 'Log Size Watchdog',
            'theme_settings' => $theme_settings,
            'alerts' => $alerts,
            'largeFiles' => $largeFiles,
        ]);
    }

    // Resolve a set of intrusion events
    public function intrusionResolve()
    {
        $this->guard();
        $id = (int)($this->request->post('id', 0));
        $all = $this->request->post('all', 0);
        try {
            $pdo = $this->db->pdo();
            if ($all) $pdo->exec("UPDATE attack_events SET resolved=1 WHERE resolved=0");
            else $pdo->prepare("UPDATE attack_events SET resolved=1 WHERE id=?")->execute([$id]);
            $_SESSION['success_message'] = 'Marked as resolved.';
        } catch (\Exception $e) {}
        $this->response->redirect('/admin/security/intrusions');
    }

    // Clear all logwatchdog alerts
    public function logwatchdogClear()
    {
        $this->guard();
        $alertFile = '/var/www/radiohosting/storage/security/logwatchdog.alerts';
        @file_put_contents($alertFile, '[]');
        $_SESSION['success_message'] = 'All logwatchdog alerts cleared.';
        $this->response->redirect('/admin/security/logwatchdog');
    }

    // Dismiss a single alert
    public function logwatchdogDismiss()
    {
        $this->guard();
        $input = json_decode(file_get_contents('php://input'), true);
        $file = $input['file'] ?? '';
        
        if (!$file) {
            $this->response->json(['success' => false, 'error' => 'File required']);
            return;
        }
        
        $alertFile = '/var/www/radiohosting/storage/security/logwatchdog.alerts';
        if (!is_file($alertFile)) {
            $this->response->json(['success' => false, 'error' => 'No alerts file']);
            return;
        }
        
        $alerts = json_decode(@file_get_contents($alertFile), true) ?: [];
        $alerts = array_filter($alerts, fn($a) => ($a['file'] ?? '') !== $file);
        @file_put_contents($alertFile, json_encode(array_values($alerts)));
        
        $this->response->json(['success' => true]);
    }

    // Truncate a log file
    public function logwatchdogTruncate()
    {
        $this->guard();
        $input = json_decode(file_get_contents('php://input'), true);
        $file = $input['file'] ?? '';
        
        if (!$file || !is_file($file)) {
            $this->response->json(['success' => false, 'error' => 'Invalid or missing file']);
            return;
        }
        
        // Truncate the file (keep it but clear contents)
        $result = @file_put_contents($file, '');
        if ($result === false) {
            $this->response->json(['success' => false, 'error' => 'Failed to truncate file (permission denied?)']);
            return;
        }
        
        // Remove any related alerts
        $alertFile = '/var/www/radiohosting/storage/security/logwatchdog.alerts';
        if (is_file($alertFile)) {
            $alerts = json_decode(@file_get_contents($alertFile), true) ?: [];
            $alerts = array_filter($alerts, fn($a) => ($a['file'] ?? '') !== $file);
            @file_put_contents($alertFile, json_encode(array_values($alerts)));
        }
        
        // Restart the service if it's a service log
        if (strpos($file, 'autodj') !== false || strpos($file, 'ffmpeg') !== false) {
            @exec('sudo systemctl reload apache2 2>/dev/null');
        }
        
        $this->response->json(['success' => true, 'message' => 'Log file truncated']);
    }
}
