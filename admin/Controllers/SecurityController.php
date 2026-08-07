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
}
