<?php
namespace Admin\Controllers;

use Core\Controller;
use Core\ThemeEngine;

class ThemesController extends Controller
{
    protected $auth, $response;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->response = $app->get('response');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $te = ThemeEngine::getInstance();
        return $this->view('admin.themes.index', [
            'adminThemes' => $te->listThemes('admin'),
            'publicThemes' => $te->listThemes('public'),
            'activeAdmin' => $te->getActiveAdminTheme(),
            'activePublic' => $te->getActivePublicTheme(),
            'user' => $this->auth->user(), 'title' => 'Theme Manager',
        ]);
    }

    public function activate($type, $name)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $te = ThemeEngine::getInstance();
        $theme = $te->loadTheme($type === 'admin' ? BASE_PATH . '/themes/admin' : BASE_PATH . '/themes/public', $name);
        if (!$theme) { $_SESSION['error_message'] = "Theme '{$name}' not found."; $this->response->redirect('/admin/themes'); exit; }
        if ($type === 'admin') $te->setActiveAdminTheme($name);
        else $te->setActivePublicTheme($name);
        $_SESSION['success_message'] = "Theme '{$name}' activated.";
        $this->response->redirect('/admin/themes');
    }

    public function upload()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        if (!isset($_FILES['theme_file']) || $_FILES['theme_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_message'] = 'Upload failed.';
            $this->response->redirect('/admin/themes'); exit;
        }
        $ext = strtolower(pathinfo($_FILES['theme_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['zip', 'gz', 'tar'])) { $_SESSION['error_message'] = 'Invalid format (zip/gz/tar).'; $this->response->redirect('/admin/themes'); exit; }
        $type = $_POST['theme_type'] ?? 'admin';
        $targetDir = BASE_PATH . '/themes/' . ($type === 'admin' ? 'admin' : 'public');
        $tmp = $_FILES['theme_file']['tmp_name'];
        $phar = new \PharData($tmp);
        $extractTo = $targetDir . '/' . pathinfo($_FILES['theme_file']['name'], PATHINFO_FILENAME);
        @mkdir($extractTo, 0755, true);
        $phar->extractTo($extractTo, null, true);
        if (!is_file($extractTo . '/theme.json')) {
            // Try subdirectory
            $items = scandir($extractTo);
            foreach ($items as $item) {
                if ($item[0] === '.') continue;
                $sub = $extractTo . '/' . $item;
                if (is_dir($sub) && is_file($sub . '/theme.json')) {
                    // Move contents up
                    foreach (scandir($sub) as $f) {
                        if ($f[0] === '.') continue;
                        rename($sub . '/' . $f, $extractTo . '/' . $f);
                    }
                    @rmdir($sub);
                    break;
                }
            }
        }
        if (!is_file($extractTo . '/theme.json')) { @array_map('unlink', glob($extractTo . '/*')); @rmdir($extractTo); $_SESSION['error_message'] = 'No theme.json found in archive.'; $this->response->redirect('/admin/themes'); exit; }
        $_SESSION['success_message'] = 'Theme installed.';
        $this->response->redirect('/admin/themes');
    }

    public function export($type, $name)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $base = BASE_PATH . '/themes/' . ($type === 'admin' ? 'admin' : 'public');
        $dir = $base . '/' . $name;
        if (!is_dir($dir) || !is_file($dir . '/theme.json')) { $_SESSION['error_message'] = 'Theme not found.'; $this->response->redirect('/admin/themes'); exit; }
        $te = ThemeEngine::getInstance();
        $theme = $te->loadTheme($base, $name);
        $exportName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name) . '.tar.gz';
        $tmpDir = sys_get_temp_dir() . '/theme_export_' . uniqid();
        @mkdir($tmpDir, 0755, true);
        $tmpDirInner = $tmpDir . '/' . $name;
        $te->copyDir($dir, $tmpDirInner);
        $archive = new \PharData($tmpDir . '/' . $exportName);
        $archive->buildFromDirectory($tmpDir);
        $archive->compress(\Phar::GZ);
        $finalFile = $tmpDir . '/' . preg_replace('/\.tar\.gz$/', '.tar.gz', $exportName);
        header('Content-Type: application/gzip');
        header('Content-Disposition: attachment; filename="' . basename($finalFile) . '"');
        header('Content-Length: ' . filesize($finalFile));
        readfile($finalFile);
        $te->removeDir($tmpDir);
        exit;
    }

    public function delete($type, $name)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        // Don't delete active theme
        $te = ThemeEngine::getInstance();
        $active = $type === 'admin' ? $te->getActiveAdminTheme() : $te->getActivePublicTheme();
        if ($name === $active) { $_SESSION['error_message'] = 'Cannot delete active theme.'; $this->response->redirect('/admin/themes'); exit; }
        $dir = BASE_PATH . '/themes/' . ($type === 'admin' ? 'admin' : 'public') . '/' . $name;
        if (!is_dir($dir)) { $_SESSION['error_message'] = 'Theme not found.'; $this->response->redirect('/admin/themes'); exit; }
        $te->removeDir($dir);
        $_SESSION['success_message'] = "Theme '{$name}' deleted.";
        $this->response->redirect('/admin/themes');
    }
}
