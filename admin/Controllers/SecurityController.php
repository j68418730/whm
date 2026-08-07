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
}
