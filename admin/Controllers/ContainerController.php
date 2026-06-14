<?php

namespace Admin\Controllers;

use Core\Controller;

class ContainerController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $containers = $this->getContainers();
        $images = $this->getImages();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.container.index', [
            'user' => $user, 'containers' => $containers, 'images' => $images,
            'theme_settings' => $theme_settings, 'title' => 'Docker Manager'
        ]);
    }

    private function getContainers()
    {
        if (!shell_exec('command -v docker 2>/dev/null')) return [];
        $raw = shell_exec('docker ps -a --format "{{.ID}}||{{.Image}}||{{.Status}}||{{.Names}}||{{.Ports}}" 2>/dev/null') ?: '';
        $list = [];
        foreach (array_filter(explode("\n", trim($raw))) as $line) {
            $parts = explode('||', $line);
            if (count($parts) >= 4) {
                $list[] = [
                    'id' => substr($parts[0], 0, 12),
                    'image' => $parts[1],
                    'status' => str_starts_with($parts[2], 'Up') ? 'running' : 'stopped',
                    'status_text' => $parts[2],
                    'name' => $parts[3],
                    'ports' => $parts[4] ?? '',
                ];
            }
        }
        return $list;
    }

    public function start($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec("docker start " . escapeshellarg($id) . " 2>&1");
        $this->response->redirect('/admin/container');
    }
    public function stop($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec("docker stop " . escapeshellarg($id) . " 2>&1");
        $this->response->redirect('/admin/container');
    }
    public function restart($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec("docker restart " . escapeshellarg($id) . " 2>&1");
        $this->response->redirect('/admin/container');
    }
    public function remove($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec("docker rm -f " . escapeshellarg($id) . " 2>&1");
        $this->response->redirect('/admin/container');
    }
    public function pull()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $image = $this->request->post('image', '');
        if ($image) shell_exec("docker pull " . escapeshellarg($image) . " 2>&1");
        $this->response->redirect('/admin/container');
    }
    private function getImages()
    {
        $raw = shell_exec('docker images --format "{{.Repository}}||{{.Tag}}||{{.ID}}||{{.Size}}" 2>/dev/null') ?: '';
        $list = [];
        foreach (array_filter(explode("\n", trim($raw))) as $line) {
            $parts = explode('||', $line);
            if (count($parts) >= 4) {
                $list[] = ['repo' => $parts[0], 'tag' => $parts[1], 'id' => substr($parts[2], 0, 12), 'size' => $parts[3]];
            }
        }
        return $list;
    }
}
