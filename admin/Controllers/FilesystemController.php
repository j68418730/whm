<?php
/**
 * Filesystem & User Management Controller
 * Handles Linux user controls, shell access, jail shell, permissions, ownership
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use PDO;
use Core\View;

class FilesystemController extends Controller
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
     * Show filesystem & user management dashboard
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

        $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
        $stmt = $pdo->query('SELECT id, username, email, domain FROM hosting_users ORDER BY username');
        $users = $stmt ? $stmt->fetchAll(\PDO::FETCH_OBJ) : [];

        $fsStats = [
            'total_users' => count($users),
            'shell_users' => 0,
            'jailed_shell_users' => 0,
            'users_with_sudo' => 0,
            'disk_partitions' => 0,
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the filesystem management view
        return $this->view('admin.filesystem.index', [
            'user' => $user,
            'users' => $users,
            'fsStats' => $fsStats,
            'theme_settings' => $theme_settings
        ]);
    }


    // ΓöÇΓöÇΓöÇ ADMIN FILE MANAGER ΓöÇΓöÇΓöÇ
    public function fileManager()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect("/admin/login"); exit; }
        $pdo = new PDO("mysql:host=localhost;dbname=radiohosting;charset=utf8mb4", "radiouser", "Skylinehosting171");
        $selectedUser = $_GET["user"] ?? "";
        if ($selectedUser) {
            $stmt = $pdo->prepare("SELECT id, username, email, domain FROM hosting_users WHERE username = ?");
            $stmt->execute([$selectedUser]);
            $target = $stmt->fetch(PDO::FETCH_OBJ);
            if (!$target) { $this->response->redirect("/admin/filesystem"); exit; }
            return $this->view("admin.filesystem.browse", [
                "user" => $this->auth->user(), "targetUser" => $target, "title" => "Files: " . $target->username
            ]);
        }
        $stmt = $pdo->query("SELECT id, username, email, domain FROM hosting_users ORDER BY username");
        $users = $stmt ? $stmt->fetchAll(PDO::FETCH_OBJ) : [];
        return $this->view("admin.filesystem.index", [
            "user" => $this->auth->user(), "users" => $users, "title" => "File Manager"
        ]);
    }

    protected function sanitizePath($path, $base)
    {
        $full = realpath($base . "/" . ltrim($path, "/"));
        if ($full === false || !str_starts_with($full, $base)) return $base;
        return $full;
    }

    public function listFiles()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) exit;
        $user = $_GET["user"] ?? "";
        $dir = $_GET["dir"] ?? "";
        $base = $user ? "/home/" . basename($user) : "/home";
        if (!is_dir($base)) { echo json_encode(["items" => []]); exit; }
        $current = $this->sanitizePath($dir, $base);
        $rel = str_replace($base, "", $current) ?: "/";
        $items = [];
        foreach (scandir($current) as $f) {
            if ($f === "." || $f === "..") continue;
            $path = $current . "/" . $f;
            $isDir = is_dir($path);
            $stat = stat($path);
            $perms = substr(sprintf("%o", fileperms($path)), -4);
            $items[] = [
                "name" => $f, "path" => str_replace("/home", "", $path),
                "is_dir" => $isDir, "size" => $isDir ? 0 : ($stat["size"] ?? 0),
                "perms" => $perms, "modified" => date("Y-m-d H:i:s", $stat["mtime"] ?? time()),
                "ext" => $isDir ? "folder" : strtolower(pathinfo($f, PATHINFO_EXTENSION)),
            ];
        }
        usort($items, function($a, $b) { return $b["is_dir"] <=> $a["is_dir"] ?: strcasecmp($a["name"], $b["name"]); });
        // Build tree
        $tree = [];
        $homes = glob("/home/*", GLOB_ONLYDIR);
        sort($homes);
        foreach ($homes as $h) {
            $bn = basename($h);
            if ($bn === "lost+found") continue;
            $subs = [];
            foreach (glob($h . "/*", GLOB_ONLYDIR) as $sub) {
                $sbn = basename($sub);
                if (in_array($sbn, ["public_html","logs","mail","tmp","etc","ssl","music","backups"]))
                    $subs[] = ["name" => $sbn, "path" => str_replace("/home", "", $sub), "children" => []];
            }
            $tree[] = ["name" => $bn, "path" => str_replace("/home", "", $h), "children" => $subs];
        }
        header("Content-Type: application/json");
        echo json_encode(["dir" => $rel, "base" => $base, "current_user" => $user, "tree" => $tree, "items" => $items]);
        exit;
    }

    public function readFile()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) exit;
        $user = $_GET["user"] ?? "";
        $base = $user ? "/home/" . basename($user) : "/home";
        $path = $this->sanitizePath($_GET["file"] ?? "", $base);
        if (is_file($path)) {
            header("Content-Type: application/json");
            echo json_encode(["content" => file_get_contents($path), "name" => basename($path), "path" => str_replace("/home", "", $path)]);
        }
        exit;
    }

    public function saveFile()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) exit;
        $user = $_POST["user"] ?? "";
        $base = $user ? "/home/" . basename($user) : "/home";
        $path = $this->sanitizePath($_POST["file"] ?? "", $base);
        if (is_file($path)) file_put_contents($path, $_POST["content"] ?? "");
        header("Content-Type: application/json");
        echo json_encode(["success" => true]);
        exit;
    }

    public function deleteFile()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) exit;
        $user = $_GET["user"] ?? "";
        $base = $user ? "/home/" . basename($user) : "/home";
        $path = $this->sanitizePath($_GET["file"] ?? "", $base);
        if (file_exists($path)) is_dir($path) ? $this->rmdirRecursive($path) : unlink($path);
        header("Content-Type: application/json");
        echo json_encode(["success" => true]);
        exit;
    }

    public function downloadFile()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) exit;
        $user = $_GET["user"] ?? "";
        $base = $user ? "/home/" . basename($user) : "/home";
        $path = $this->sanitizePath($_GET["file"] ?? "", $base);
        if (is_file($path)) { header("Content-Type: application/octet-stream"); header("Content-Disposition: attachment; filename=\"" . basename($path) . "\""); readfile($path); }
        exit;
    }

    public function makeDir()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) exit;
        $user = $_POST["user"] ?? "";
        $base = $user ? "/home/" . basename($user) : "/home";
        $parent = $this->sanitizePath($_POST["dir"] ?? "", $base);
        $name = preg_replace("/[^a-zA-Z0-9_\- .]/", "", $_POST["name"] ?? "");
        if ($name) @mkdir($parent . "/" . $name, 0755, true);
        header("Content-Type: application/json"); echo json_encode(["success" => true]); exit;
    }

    public function renameFile()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) exit;
        $user = $_POST["user"] ?? "";
        $base = $user ? "/home/" . basename($user) : "/home";
        $old = $this->sanitizePath($_POST["old"] ?? "", $base);
        $newName = preg_replace("/[^a-zA-Z0-9_\- .]/", "", $_POST["new_name"] ?? "");
        if ($old && $newName) { $new = dirname($old) . "/" . $newName; if (file_exists($old) && !file_exists($new)) rename($old, $new); }
        header("Content-Type: application/json"); echo json_encode(["success" => true]); exit;
    }

    public function copyFile()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) exit;
        $user = $_POST["user"] ?? "";
        $base = $user ? "/home/" . basename($user) : "/home";
        $src = $this->sanitizePath($_POST["src"] ?? "", $base);
        $dst = $this->sanitizePath($_POST["dst"] ?? "", $base);
        if ($src && $dst && file_exists($src) && !file_exists($dst)) is_dir($src) ? $this->copyRecursive($src, $dst) : copy($src, $dst);
        header("Content-Type: application/json"); echo json_encode(["success" => true]); exit;
    }

    public function moveFile()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) exit;
        $user = $_POST["user"] ?? "";
        $base = $user ? "/home/" . basename($user) : "/home";
        $src = $this->sanitizePath($_POST["src"] ?? "", $base);
        $dst = $this->sanitizePath($_POST["dst"] ?? "", $base);
        if ($src && $dst && file_exists($src) && !file_exists($dst)) rename($src, $dst);
        header("Content-Type: application/json"); echo json_encode(["success" => true]); exit;
    }

    public function property()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) exit;
        $user = $_GET["user"] ?? "";
        $base = $user ? "/home/" . basename($user) : "/home";
        $path = $this->sanitizePath($_GET["file"] ?? "", $base);
        if (!file_exists($path)) exit;
        $stat = stat($path);
        header("Content-Type: application/json");
        echo json_encode(["name" => basename($path), "path" => str_replace("/home", "", $path), "type" => is_dir($path) ? "directory" : "file", "size" => $stat["size"] ?? 0, "perms" => substr(sprintf("%o", fileperms($path)), -4), "modified" => date("Y-m-d H:i:s", $stat["mtime"] ?? time())]);
        exit;
    }

    public function chmod()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) exit;
        $user = $_POST["user"] ?? "";
        $base = $user ? "/home/" . basename($user) : "/home";
        $path = $this->sanitizePath($_POST["file"] ?? "", $base);
        $perms = (int)($_POST["perms"] ?? 755);
        if (file_exists($path)) chmod($path, $perms);
        header("Content-Type: application/json"); echo json_encode(["success" => true]); exit;
    }

    public function search()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) exit;
        $user = $_GET["user"] ?? "";
        $base = $user ? "/home/" . basename($user) : "/home";
        $q = $_GET["q"] ?? "";
        if (!$q) { echo json_encode([]); exit; }
        $results = [];
        $this->searchRecursive($base, $q, $base, $results);
        header("Content-Type: application/json"); echo json_encode($results); exit;
    }

    private function searchRecursive($dir, $q, $base, &$results, $max = 50)
    {
        if (count($results) >= $max) return;
        foreach (scandir($dir) as $f) {
            if ($f === "." || $f === "..") continue;
            if (count($results) >= $max) break;
            $path = $dir . "/" . $f;
            if (stripos($f, $q) !== false) $results[] = ["name" => $f, "path" => str_replace($base, "", $path), "is_dir" => is_dir($path)];
            if (is_dir($path)) $this->searchRecursive($path, $q, $base, $results, $max);
        }
    }

    public function archive()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) exit;
        $user = $_GET["user"] ?? "";
        $base = $user ? "/home/" . basename($user) : "/home";
        $dir = $this->sanitizePath($_GET["dir"] ?? "", $base);
        $zipFile = $base . "/" . basename($dir) . ".zip";
        if (is_dir($dir)) {
            $za = new ZipArchive();
            if ($za->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $this->addFolderToZip($dir, $za, strlen($base) + 1);
                $za->close();
            }
        }
        header("Content-Type: application/json"); echo json_encode(["success" => true, "file" => basename($zipFile)]); exit;
    }

    public function extract()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) exit;
        $user = $_GET["user"] ?? "";
        $base = $user ? "/home/" . basename($user) : "/home";
        $path = $this->sanitizePath($_GET["file"] ?? "", $base);
        $dir = dirname($path);
        if (is_file($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if ($ext === "zip") { $za = new ZipArchive(); if ($za->open($path) === true) { $za->extractTo($dir); $za->close(); } }
            elseif (in_array($ext, ["tar","gz","tgz"])) { $phar = new PharData($path); $phar->extractTo($dir, null, true); }
        }
        header("Content-Type: application/json"); echo json_encode(["success" => true]); exit;
    }

    private function addFolderToZip($dir, $za, $prefixLen)
    {
        foreach (scandir($dir) as $f) { if ($f === "." || $f === "..") continue;
            $path = $dir . "/" . $f; $local = substr($path, $prefixLen);
            is_dir($path) ? ($za->addEmptyDir($local) . $this->addFolderToZip($path, $za, $prefixLen)) : $za->addFile($path, $local);
        }
    }

    private function rmdirRecursive($dir)
    {
        foreach (scandir($dir) as $f) { if ($f === "." || $f === "..") continue;
            $p = $dir . "/" . $f; is_dir($p) ? $this->rmdirRecursive($p) : unlink($p);
        }
        rmdir($dir);
    }

    private function copyRecursive($src, $dst)
    {
        @mkdir($dst, 0755);
        foreach (scandir($src) as $f) { if ($f === "." || $f === "..") continue;
            $s = $src . "/" . $f; $d = $dst . "/" . $f;
            is_dir($s) ? $this->copyRecursive($s, $d) : copy($s, $d);
        }
    }

}
