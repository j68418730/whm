<?php
namespace Admin\Controllers;

use Core\Controller;
use Core\WidgetManager;

class WidgetController extends Controller
{
    protected $auth, $response, $request, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->response = $app->get('response');
        $this->request = $app->get('request');
        $this->db = $app->get('db');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        require_once BASE_PATH . '/core/Widget.php';
        require_once BASE_PATH . '/core/WidgetManager.php';
        $wm = WidgetManager::getInstance();
        $wm->setDb($this->db)->setUserId($user->id);
        $wm->ensureDefaults();
        $allWidgets = $wm->getAllWidgets();
        $userWidgets = $wm->getUserWidgets();

        return $this->view('admin.widgets.index', [
            'user' => $user,
            'title' => 'Widget Manager',
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
            'all_widgets' => $allWidgets,
            'user_widgets' => $userWidgets,
        ]);
    }

    public function saveLayout()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $input = json_decode(file_get_contents('php://input'), true);
        $layout = $input['layout'] ?? [];
        $userId = $this->auth->user()->id;
        $wm = WidgetManager::getInstance();
        $wm->setDb($this->db)->saveLayout($userId, $layout);
        $this->response->json(['ok' => true]);
        $this->response->send();
        exit;
    }

    public function remove()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $key = $this->request->post('key', '');
        if (!$key) { $this->response->json(['ok' => false, 'error' => 'No key'])->send(); exit; }
        $userId = $this->auth->user()->id;
        WidgetManager::getInstance()->setDb($this->db)->removeWidget($userId, $key);
        $this->response->json(['ok' => true]);
        $this->response->send();
        exit;
    }

    public function add()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $key = $this->request->post('key', '');
        $zone = $this->request->post('zone', 'main');
        if (!$key) { $this->response->json(['ok' => false, 'error' => 'No key'])->send(); exit; }
        $userId = $this->auth->user()->id;
        WidgetManager::getInstance()->setDb($this->db)->addWidget($userId, $key, $zone);
        $this->response->json(['ok' => true]);
        $this->response->send();
        exit;
    }
}
