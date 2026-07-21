<?php
/**
 * Radio Dashboard Controller
 * Shows radio streaming dashboard with current listeners, peak listeners, stream status, etc.
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class RadioDashboardController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;

    public function __construct()
    {
        $this->auth = \Core\Application::getInstance()->get('auth');
        $this->request = \Core\Application::getInstance()->get('request');
        $this->response = \Core\Application::getInstance()->get('response');
    }

    /**
     * Show radio streaming dashboard
     */
    public function index()
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Get admin user info
        $user = $this->auth->user();

        $radioStats = [
            'current_listeners' => 0,
            'peak_listeners_today' => 0,
            'stream_status' => 'offline',
            'current_song' => 'Unknown - Unknown Artist',
            'cpu_usage' => 0,
            'ram_usage' => 0,
            'bandwidth_usage' => 0,
            'stream_uptime' => '0 minutes',
            'total_streams' => 0,
            'active_streams' => 0,
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the radio dashboard view
        return $this->view('admin.radio_dashboard.index', [
            'user' => $user,
            'radioStats' => $radioStats,
            'theme_settings' => $theme_settings
        ]);
    }

    public function downloads()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','Skylinehosting171');
        $downloads = $pdo->query("SELECT rd.*, ss.name AS station_name FROM radio_downloads rd LEFT JOIN streaming_stations ss ON ss.id=rd.station_id ORDER BY rd.created_at DESC")->fetchAll(\PDO::FETCH_OBJ);
        return $this->view('admin.radio_dashboard.downloads', [
            'user' => $user, 'downloads' => $downloads,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true)
        ]);
    }

    public function uploadDownload()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','Skylinehosting171');
        
        if ($_POST && isset($_FILES['file'])) {
            $name = trim($_POST['name'] ?? pathinfo($_FILES['file']['name'], PATHINFO_FILENAME));
            $desc = trim($_POST['description'] ?? '');
            $stationId = !empty($_POST['station_id']) ? (int)$_POST['station_id'] : null;
            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $allowed = ['mp3','aac','ogg','flac','wav','m4a','zip','pdf','jpg','png','gif','txt','csv','pdf','doc','docx','xls','xlsx'];
            if (!in_array($ext, $allowed)) { $_SESSION['error'] = 'Invalid file type.'; $this->response->redirect('/admin/radio/downloads'); exit; }
            
            $dir = '/var/www/radiohosting/storage/radio_downloads';
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $filename = bin2hex(random_bytes(8)) . '.' . $ext;
            $dest = $dir . '/' . $filename;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
                $pdo->prepare("INSERT INTO radio_downloads (station_id, name, description, file_path, file_size, uploaded_by) VALUES (?,?,?,?,?,?)")
                    ->execute([$stationId, $name, $desc, $dest, filesize($dest), $user->id ?? 0]);
                $_SESSION['success'] = 'File uploaded.';
            } else {
                $_SESSION['error'] = 'Upload failed.';
            }
        }
        $this->response->redirect('/admin/radio/downloads');
    }

    public function deleteDownload($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','Skylinehosting171');
        $dl = $pdo->prepare("SELECT * FROM radio_downloads WHERE id=?")->execute([$id]);
        // Use a direct delete
        $pdo->prepare("DELETE FROM radio_downloads WHERE id=?")->execute([$id]);
        $_SESSION['success'] = 'Download deleted.';
        $this->response->redirect('/admin/radio/downloads');
    }

    public function serveDownload($id)
    {
        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','Skylinehosting171');
        $dl = $pdo->prepare("SELECT * FROM radio_downloads WHERE id=?");
        $dl->execute([$id]);
        $d = $dl->fetch(\PDO::FETCH_OBJ);
        if (!$d || !file_exists($d->file_path)) { http_response_code(404); exit; }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($d->file_path) . '"');
        header('Content-Length: ' . filesize($d->file_path));
        readfile($d->file_path);
        exit;
    }

    public function widgets()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
        $streams = $pdo->query("SELECT id, name AS server_name, server_type, port, status, mount_point FROM streaming_stations ORDER BY id ASC")->fetchAll(\PDO::FETCH_OBJ);

        return $this->view('admin.radio_dashboard.widgets', [
            'user' => $user,
            'theme_settings' => $theme_settings,
            'streams' => $streams,
            'baseUrl' => 'https://planet-hosts.com',
        ]);
    }
}