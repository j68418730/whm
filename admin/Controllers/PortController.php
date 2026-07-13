<?php
namespace Admin\Controllers;

use Core\Controller;
use Core\PortManager;

class PortController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;
    protected $portManager;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        $this->portManager = new PortManager();
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $stats = $this->portManager->getUsage();
        $servers = $this->portManager->getServerStats();
        $recent = $this->portManager->getHistory(20);
        $conflicts = $this->portManager->findConflicts();
        return $this->view('admin.port.index', [
            'user' => $user, 'title' => 'Port Manager',
            'stats' => $stats, 'servers' => $servers,
            'recent' => $recent, 'conflicts' => $conflicts,
        ]);
    }

    public function usage()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $type = $this->request->get('type', '');
        $serverId = (int)$this->request->get('server', 1);
        if ($type) {
            $ports = $this->portManager->getByServiceType($type);
        } else {
            $ports = $this->db->pdo()->query("SELECT sp.*, ss.name AS server_name FROM stream_ports sp LEFT JOIN stream_servers ss ON sp.server_id=ss.id ORDER BY sp.port_start LIMIT 200")->fetchAll(\PDO::FETCH_OBJ);
        }
        $ranges = $this->portManager->getRanges();
        return $this->view('admin.port.usage', [
            'user' => $user, 'title' => 'Port Usage',
            'ports' => $ports, 'ranges' => $ranges,
            'currentType' => $type, 'currentServer' => $serverId,
        ]);
    }

    public function search()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $term = $this->request->get('q', '');
        $results = $term ? $this->portManager->search($term) : [];
        return $this->view('admin.port.search', [
            'user' => $user, 'title' => 'Port Search',
            'results' => $results, 'term' => $term,
        ]);
    }

    public function conflicts()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $conflicts = $this->portManager->findConflicts();
        return $this->view('admin.port.conflicts', [
            'user' => $user, 'title' => 'Port Conflicts',
            'conflicts' => $conflicts,
        ]);
    }

    public function history()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $log = $this->portManager->getHistory(200);
        return $this->view('admin.port.history', [
            'user' => $user, 'title' => 'Port History',
            'log' => $log,
        ]);
    }

    public function release($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $ok = $this->portManager->release((int)$id);
        if ($ok) {
            $_SESSION['success_message'] = "Port released back to pool.";
        } else {
            $_SESSION['error_message'] = "Failed to release port.";
        }
        $this->response->redirect('/admin/port/usage');
        exit;
    }

    public function validate($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $port = $this->portManager->getById((int)$id);
        if (!$port) { $_SESSION['error_message'] = 'Port not found.'; $this->response->redirect('/admin/port'); exit; }
        $errors = $this->portManager->validatePort($port->port_start, $port->service_type);
        if (empty($errors)) {
            $_SESSION['success_message'] = "Port {$port->port_start} validated OK.";
        } else {
            $_SESSION['error_message'] = "Issues: " . implode('; ', $errors);
        }
        $this->response->redirect('/admin/port/usage');
        exit;
    }
}
