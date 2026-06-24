<?php
namespace User\Controllers;

use Core\Controller;

class FileManagerController extends Controller
{
    protected $auth, $request, $response, $db, $hostingUser;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function requireUser()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $user = $this->auth->user();
        $this->hostingUser = $this->db->table('hosting_users')->where('email', $user->email)->first();
        if (!$this->hostingUser) $this->hostingUser = $this->db->table('hosting_users')->where('id', $user->id ?? 0)->first();
        return $user;
    }

    protected function getUserHome()
    {
        $dir = '/home/' . ($this->hostingUser->username ?? 'planethosts');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        return realpath($dir) ?: $dir;
    }

    protected function sanitizePath($path)
    {
        $home = $this->getUserHome();
        $full = realpath($home . '/' . ltrim($path, '/'));
        if ($full === false || !str_starts_with($full, $home)) {
            return $home;
        }
        return $full;
    }

    public function index()
    {
        $this->requireUser();
        return $this->view('user.filemanager', [
            'user' => $this->auth->user(), 'hosting' => $this->hostingUser,
            'title' => 'File Manager'
        ]);
    }

    public function listFiles()
    {
        $this->requireUser();
        $home = $this->getUserHome();
        $dir = $this->sanitizePath($_GET['dir'] ?? '');
        if (!is_dir($dir)) $dir = $home;
        $rel = str_starts_with($dir, $home) ? substr($dir, strlen($home) + 1) : '';

        // Build folder tree
        $tree = $this->buildTree($home, $home);

        // List files
        $items = [];
        $files = scandir($dir);
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            $path = $dir . '/' . $f;
            $isDir = is_dir($path);
            $stat = stat($path);
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            $items[] = [
                'name' => $f,
                'path' => str_replace($home, '', $path),
                'is_dir' => $isDir,
                'size' => $isDir ? 0 : ($stat['size'] ?? 0),
                'perms' => $perms,
                'owner' => function_exists('posix_getpwuid') ? posix_getpwuid($stat['uid'])['name'] ?? 'www-data' : 'www-data',
                'modified' => date('Y-m-d H:i:s', $stat['mtime'] ?? time()),
                'ext' => $isDir ? 'folder' : strtolower(pathinfo($f, PATHINFO_EXTENSION)),
            ];
        }
        usort($items, function($a, $b) { return $b['is_dir'] <=> $a['is_dir'] ?: strcasecmp($a['name'], $b['name']); });

        header('Content-Type: application/json');
        echo json_encode(['dir' => $rel, 'home' => $home, 'tree' => $tree, 'items' => $items]);
        exit;
    }

    private function buildTree($base, $current, $maxDepth = 3)
    {
        $tree = [];
        $items = scandir($current);
        $dirs = [];
        foreach ($items as $f) {
            if ($f === '.' || $f === '..') continue;
            if (is_dir($current . '/' . $f)) $dirs[] = $f;
        }
        sort($dirs);
        foreach ($dirs as $d) {
            $path = $current . '/' . $d;
            $rel = str_replace($base, '', $path);
            $children = [];
            if ($maxDepth > 0) {
                $subDirs = glob($path . '/*', GLOB_ONLYDIR);
                if (count($subDirs) > 0) $children = $this->buildTree($base, $path, $maxDepth - 1);
            }
            $tree[] = ['name' => $d, 'path' => $rel, 'children' => $children];
        }
        return $tree;
    }

    public function createFolder()
    {
        $this->requireUser();
        $home = $this->getUserHome();
        $parent = $this->sanitizePath($_POST['dir'] ?? '');
        $name = preg_replace('/[^a-zA-Z0-9_\- .]/', '', $_POST['name'] ?? '');
        if ($name) {
            $path = $parent . '/' . $name;
            if (!is_dir($path)) @mkdir($path, 0755, true);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function createFile()
    {
        $this->requireUser();
        $home = $this->getUserHome();
        $dir = $this->sanitizePath($_POST['dir'] ?? '');
        $name = preg_replace('/[^a-zA-Z0-9_\- .]/', '', $_POST['name'] ?? '');
        if ($name) {
            $path = $dir . '/' . $name;
            if (!is_file($path)) file_put_contents($path, '');
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function readFile()
    {
        $this->requireUser();
        $path = $this->sanitizePath($_GET['file'] ?? '');
        if (is_file($path)) {
            $content = file_get_contents($path);
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            header('Content-Type: application/json');
            echo json_encode(['content' => $content, 'ext' => $ext, 'name' => basename($path)]);
        }
        exit;
    }

    public function saveFile()
    {
        $this->requireUser();
        $path = $this->sanitizePath($_POST['file'] ?? '');
        $content = $_POST['content'] ?? '';
        if ($path && is_file($path)) {
            file_put_contents($path, $content);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function rename()
    {
        $this->requireUser();
        $home = $this->getUserHome();
        $old = $this->sanitizePath($_POST['old'] ?? '');
        $newName = preg_replace('/[^a-zA-Z0-9_\- .]/', '', $_POST['new_name'] ?? '');
        if ($old && $newName) {
            $new = dirname($old) . '/' . $newName;
            if (file_exists($old) && !file_exists($new)) rename($old, $new);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function copy()
    {
        $this->requireUser();
        $home = $this->getUserHome();
        $src = $this->sanitizePath($_POST['src'] ?? '');
        $dst = $home . '/' . ltrim($_POST['dst'] ?? '', '/');
        if (file_exists($src) && !file_exists($dst)) {
            is_dir($src) ? $this->copyRecursive($src, $dst) : copy($src, $dst);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    private function copyRecursive($src, $dst)
    {
        @mkdir($dst, 0755);
        foreach (scandir($src) as $f) {
            if ($f === '.' || $f === '..') continue;
            $s = $src . '/' . $f;
            $d = $dst . '/' . $f;
            is_dir($s) ? $this->copyRecursive($s, $d) : copy($s, $d);
        }
    }

    public function move()
    {
        $this->requireUser();
        $home = $this->getUserHome();
        $src = $this->sanitizePath($_POST['src'] ?? '');
        $dst = $home . '/' . ltrim($_POST['dst'] ?? '', '/');
        if (file_exists($src) && !file_exists($dst)) rename($src, $dst);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function delete()
    {
        $this->requireUser();
        $path = $this->sanitizePath($_GET['file'] ?? ($_POST['file'] ?? ''));
        if (file_exists($path)) {
            is_dir($path) ? $this->rmdirRecursive($path) : unlink($path);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function deleteBatch()
    {
        $this->requireUser();
        $files = $_POST['files'] ?? [];
        foreach ($files as $f) {
            $path = $this->sanitizePath($f);
            if (file_exists($path)) {
                is_dir($path) ? $this->rmdirRecursive($path) : unlink($path);
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    private function rmdirRecursive($dir)
    {
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') continue;
            $p = $dir . '/' . $f;
            is_dir($p) ? $this->rmdirRecursive($p) : unlink($p);
        }
        rmdir($dir);
    }

    public function upload()
    {
        $this->requireUser();
        $home = $this->getUserHome();
        $dir = $this->sanitizePath($_POST['dir'] ?? '');
        $uploaded = [];
        if (!empty($_FILES['files'])) {
            $total = count($_FILES['files']['name']);
            for ($i = 0; $i < $total; $i++) {
                $name = basename($_FILES['files']['name'][$i]);
                $dest = $dir . '/' . $name;
                if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $dest)) {
                    $uploaded[] = $name;
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'uploaded' => count($uploaded)]);
        exit;
    }

    public function download()
    {
        $this->requireUser();
        $path = $this->sanitizePath($_GET['file'] ?? '');
        if (is_file($path)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
            readfile($path);
        }
        exit;
    }

    public function archive()
    {
        $this->requireUser();
        $home = $this->getUserHome();
        $dir = $this->sanitizePath($_GET['dir'] ?? '');
        $name = basename($dir);
        $zipFile = $home . '/' . $name . '.zip';
        if (is_dir($dir)) {
            $za = new \ZipArchive();
            if ($za->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $this->addFolderToZip($dir, $za, strlen($home) + 1);
                $za->close();
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'file' => basename($zipFile)]);
        exit;
    }

    public function extract()
    {
        $this->requireUser();
        $path = $this->sanitizePath($_GET['file'] ?? '');
        $dir = dirname($path);
        if (is_file($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if ($ext === 'zip') {
                $za = new \ZipArchive();
                if ($za->open($path) === true) {
                    $za->extractTo($dir);
                    $za->close();
                }
            } elseif (in_array($ext, ['tar', 'gz', 'tgz'])) {
                $phar = new \PharData($path);
                $phar->extractTo($dir, null, true);
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    private function addFolderToZip($dir, $za, $prefixLen)
    {
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') continue;
            $path = $dir . '/' . $f;
            $local = substr($path, $prefixLen);
            if (is_dir($path)) {
                $za->addEmptyDir($local);
                $this->addFolderToZip($path, $za, $prefixLen);
            } else {
                $za->addFile($path, $local);
            }
        }
    }

    public function chmod()
    {
        $this->requireUser();
        $path = $this->sanitizePath($_POST['file'] ?? '');
        $perms = (int)$_POST['perms'] ?? 755;
        if (file_exists($path)) chmod($path, $perms);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function search()
    {
        $this->requireUser();
        $home = $this->getUserHome();
        $q = $_GET['q'] ?? '';
        $dir = $this->sanitizePath($_GET['dir'] ?? '');
        if (!$q) { echo json_encode([]); exit; }
        $results = $this->searchRecursive($dir, $q, $home);
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }

    private function searchRecursive($dir, $q, $home, $maxResults = 50)
    {
        $results = [];
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') continue;
            if (count($results) >= $maxResults) break;
            $path = $dir . '/' . $f;
            if (stripos($f, $q) !== false) {
                $results[] = ['name' => $f, 'path' => str_replace($home, '', $path), 'is_dir' => is_dir($path)];
            }
            if (is_dir($path)) {
                $results = array_merge($results, $this->searchRecursive($path, $q, $home, $maxResults - count($results)));
            }
        }
        return $results;
    }

    public function properties()
    {
        $this->requireUser();
        $path = $this->sanitizePath($_GET['file'] ?? '');
        if (!file_exists($path)) { echo json_encode(['error' => 'Not found']); exit; }
        $stat = stat($path);
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        header('Content-Type: application/json');
        echo json_encode([
            'name' => basename($path),
            'path' => $path,
            'type' => is_dir($path) ? 'directory' : 'file',
            'size' => $stat['size'] ?? 0,
            'perms' => $perms,
            'owner' => function_exists('posix_getpwuid') ? posix_getpwuid($stat['uid'])['name'] ?? 'www-data' : 'www-data',
            'group' => function_exists('posix_getgrgid') ? posix_getgrgid($stat['gid'])['name'] ?? 'www-data' : 'www-data',
            'modified' => date('Y-m-d H:i:s', $stat['mtime'] ?? time()),
            'created' => date('Y-m-d H:i:s', $stat['ctime'] ?? time()),
        ]);
        exit;
    }
}
