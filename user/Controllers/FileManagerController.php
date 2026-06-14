<?php

namespace User\Controllers;

use Core\Controller;

class FileManagerController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function getUserHome()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $user = $this->auth->user();
        $hosting = $this->db->table('hosting_users')->where('email', $user->email)->first();
        if (!$hosting) return '/tmp';
        return '/home/' . $hosting->username;
    }

    protected function sanitizePath($path)
    {
        $home = $this->getUserHome();
        $real = realpath($home . '/' . ltrim($path, '/'));
        $realHome = realpath($home);
        if (!$real || !$realHome || strpos($real, $realHome) !== 0) {
            return $home;
        }
        return $real;
    }

    public function index()
    {
        $home = $this->getUserHome();
        $dir = $this->sanitizePath($_GET['dir'] ?? '');
        $items = [];
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $f) {
                if ($f === '.' || $f === '..') continue;
                $path = $dir . '/' . $f;
                $items[] = [
                    'name' => $f, 'path' => str_replace($home, '', $path),
                    'is_dir' => is_dir($path), 'size' => is_file($path) ? filesize($path) : 0,
                    'modified' => date('Y-m-d H:i', filemtime($path)),
                ];
            }
        }
        $current = str_replace($home, '', $dir) ?: '/';
        return $this->view('user.filemanager', [
            'items' => $items, 'current' => $current, 'home' => $home,
            'title' => 'File Manager'
        ]);
    }

    public function upload()
    {
        $home = $this->getUserHome();
        $dir = $this->sanitizePath($_POST['dir'] ?? '');
        if (isset($_FILES['file']) && is_dir($dir)) {
            move_uploaded_file($_FILES['file']['tmp_name'], $dir . '/' . basename($_FILES['file']['name']));
        }
        $this->response->redirect('/user/files?dir=' . urlencode(str_replace($home, '', $dir)));
        exit;
    }

    public function download()
    {
        $home = $this->getUserHome();
        $file = $this->sanitizePath($_GET['file'] ?? '');
        if (is_file($file)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            readfile($file);
            exit;
        }
        $this->response->redirect('/user/files');
        exit;
    }

    public function delete()
    {
        $home = $this->getUserHome();
        $path = $this->sanitizePath($_GET['path'] ?? '');
        if (is_file($path)) unlink($path);
        elseif (is_dir($path)) {
            $this->rmdirRecursive($path);
        }
        $this->response->redirect('/user/files?dir=' . urlencode(dirname(str_replace($home, '', $path))));
        exit;
    }

    public function mkdir()
    {
        $home = $this->getUserHome();
        $dir = $this->sanitizePath($_POST['dir'] ?? '');
        $name = basename($_POST['name'] ?? 'newfolder');
        if (is_dir($dir)) mkdir($dir . '/' . $name, 0755, true);
        $this->response->redirect('/user/files?dir=' . urlencode(str_replace($home, '', $dir)));
        exit;
    }

    public function archive()
    {
        $home = $this->getUserHome();
        $dir = $this->sanitizePath($_POST['dir'] ?? '');
        $name = basename($_POST['name'] ?? 'backup');
        $target = $dir . '/' . $name . '.tar.gz';
        if (is_dir($dir)) {
            exec("tar -czf '{$target}' -C '{$dir}' . 2>/dev/null");
        }
        $this->response->redirect('/user/files?dir=' . urlencode(str_replace($home, '', $dir)));
        exit;
    }

    private function rmdirRecursive($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $f) {
            $path = $dir . '/' . $f;
            is_dir($path) ? $this->rmdirRecursive($path) : unlink($path);
        }
        rmdir($dir);
    }
}
