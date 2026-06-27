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

    protected function getWm()
    {
        $user = $this->auth->user();
        return WidgetManager::getInstance()->setDb($this->db)->setUserId($user->id);
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $wm = $this->getWm();
        $wm->ensureDefaults($user->id);
        $allWidgets = $wm->getAllWidgets();
        $userWidgets = $wm->getUserWidgets();
        $layouts = $wm->getLayouts($user->id);
        $customWidgets = $wm->getCustomWidgets($user->id);

        return $this->view('admin.widgets.index', [
            'user' => $user,
            'title' => 'Widget Manager',
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
            'all_widgets' => $allWidgets,
            'user_widgets' => $userWidgets,
            'layouts' => $layouts,
            'custom_widgets' => $customWidgets,
        ]);
    }

    public function saveLayout()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $input = json_decode(file_get_contents('php://input'), true);
        $layout = $input['layout'] ?? [];
        $user = $this->auth->user();
        $this->getWm()->saveLayout($user->id, $layout);
        $this->response->json(['ok' => true]);
        $this->response->send();
        exit;
    }

    public function remove()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $key = $this->request->post('key', '');
        if (!$key) { $this->response->json(['ok' => false, 'error' => 'No key'])->send(); exit; }
        $user = $this->auth->user();
        $this->getWm()->removeWidget($user->id, $key);
        $this->response->json(['ok' => true]);
        $this->response->send();
        exit;
    }

    public function add()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $key = $this->request->post('key', '');
        $zone = $this->request->post('zone', 'main');
        $layoutName = $this->request->post('layout_name', 'default');
        if (!$key) { $this->response->json(['ok' => false, 'error' => 'No key'])->send(); exit; }
        $user = $this->auth->user();
        $this->getWm()->addWidget($user->id, $key, $zone, $layoutName);
        $this->response->json(['ok' => true]);
        $this->response->send();
        exit;
    }

    // ---- New feature endpoints ----

    public function toggleCollapse()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $id = (int)$this->request->post('id', 0);
        if (!$id) { $this->response->json(['ok' => false])->send(); exit; }
        $user = $this->auth->user();
        $result = $this->getWm()->toggleCollapse($user->id, $id);
        $this->response->json(['ok' => true, 'result' => $result]);
        $this->response->send();
        exit;
    }

    public function togglePin()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $id = (int)$this->request->post('id', 0);
        if (!$id) { $this->response->json(['ok' => false])->send(); exit; }
        $user = $this->auth->user();
        $result = $this->getWm()->togglePin($user->id, $id);
        $this->response->json(['ok' => true, 'result' => $result]);
        $this->response->send();
        exit;
    }

    public function toggleHide()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $id = (int)$this->request->post('id', 0);
        if (!$id) { $this->response->json(['ok' => false])->send(); exit; }
        $user = $this->auth->user();
        $result = $this->getWm()->toggleHide($user->id, $id);
        $this->response->json(['ok' => true, 'result' => $result]);
        $this->response->send();
        exit;
    }

    public function setWidth()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $id = (int)$this->request->post('id', 0);
        $width = (int)$this->request->post('width', 1);
        if (!$id) { $this->response->json(['ok' => false])->send(); exit; }
        $user = $this->auth->user();
        $result = $this->getWm()->setWidth($user->id, $id, $width);
        $this->response->json(['ok' => true, 'result' => $result]);
        $this->response->send();
        exit;
    }

    // Layout management
    public function listLayouts()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $user = $this->auth->user();
        $layouts = $this->getWm()->getLayouts($user->id);
        $this->response->json(['ok' => true, 'layouts' => $layouts]);
        $this->response->send();
        exit;
    }

    public function saveLayoutSnapshot()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $input = json_decode(file_get_contents('php://input'), true);
        $layoutName = $input['layout_name'] ?? 'default';
        $user = $this->auth->user();
        $this->getWm()->saveLayoutSnapshot($user->id, $layoutName);
        $this->response->json(['ok' => true]);
        $this->response->send();
        exit;
    }

    public function applyLayout()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $input = json_decode(file_get_contents('php://input'), true);
        $layoutName = $input['layout_name'] ?? 'default';
        $user = $this->auth->user();
        $ok = $this->getWm()->applyLayoutSnapshot($user->id, $layoutName);
        $this->response->json(['ok' => $ok]);
        $this->response->send();
        exit;
    }

    public function renameLayout()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $old = $this->request->post('old_name', '');
        $new = $this->request->post('new_name', '');
        $user = $this->auth->user();
        $ok = $this->getWm()->renameLayout($user->id, $old, $new);
        $this->response->json(['ok' => $ok]);
        $this->response->send();
        exit;
    }

    public function deleteLayout()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $name = $this->request->post('layout_name', '');
        $user = $this->auth->user();
        $ok = $this->getWm()->deleteLayout($user->id, $name);
        $this->response->json(['ok' => $ok]);
        $this->response->send();
        exit;
    }

    public function exportLayout()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $layoutName = $this->request->get('layout_name', 'default');
        $user = $this->auth->user();
        $json = $this->getWm()->exportLayout($user->id, $layoutName);
        if (!$json) { $this->response->json(['ok' => false, 'error' => 'No data'])->send(); exit; }
        $this->response->addHeader('Content-Type', 'application/json');
        $this->response->addHeader('Content-Disposition', 'attachment; filename="layout-' . $layoutName . '.json"');
        echo $json;
        exit;
    }

    public function importLayout()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $json = $this->request->post('layout_json', '');
        if (!$json) { $this->response->json(['ok' => false, 'error' => 'No JSON provided'])->send(); exit; }
        $user = $this->auth->user();
        $name = $this->getWm()->importLayout($user->id, $json);
        if (!$name) { $this->response->json(['ok' => false, 'error' => 'Invalid layout JSON'])->send(); exit; }
        $this->response->json(['ok' => true, 'layout_name' => $name]);
        $this->response->send();
        exit;
    }

    public function resetLayout()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $layoutName = $this->request->post('layout_name', 'default');
        $user = $this->auth->user();
        $this->getWm()->resetLayout($user->id, $layoutName);
        $this->response->json(['ok' => true]);
        $this->response->send();
        exit;
    }

    // Widget Builder
    public function builder()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        return $this->view('admin.widgets.builder', [
            'user' => $user,
            'title' => 'Widget Builder',
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function createCustom()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $user = $this->auth->user();
        $key = 'custom_' . bin2hex(random_bytes(8));
        $name = $this->request->post('name', 'Custom Widget');
        $type = $this->request->post('widget_type', 'html');
        $config = $this->request->post('config', '{}');
        $configArr = json_decode($config, true) ?: [];
        $this->getWm()->createCustomWidget($user->id, $key, $name, $type, $configArr);
        $this->response->json(['ok' => true, 'widget_key' => $key]);
        $this->response->send();
        exit;
    }

    public function updateCustom()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $user = $this->auth->user();
        $key = $this->request->post('widget_key', '');
        $name = $this->request->post('name', 'Custom Widget');
        $type = $this->request->post('widget_type', 'html');
        $config = $this->request->post('config', '{}');
        $configArr = json_decode($config, true) ?: [];
        $this->getWm()->updateCustomWidget($user->id, $key, $name, $type, $configArr);
        $this->response->json(['ok' => true]);
        $this->response->send();
        exit;
    }

    public function deleteCustom()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { exit; }
        $user = $this->auth->user();
        $key = $this->request->post('widget_key', '');
        $this->getWm()->deleteCustomWidget($user->id, $key);
        $this->response->json(['ok' => true]);
        $this->response->send();
        exit;
    }
}
