<?php

namespace User\Controllers;

use Core\Controller;

class DatabasesController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;
    protected $hostingUser;
    protected $package;

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
        if (!$this->hostingUser) { $this->response->redirect('/user'); exit; }
        $this->package = $this->db->table('hosting_packages')->where('id', $this->hostingUser->package_id)->first();
        // Icecast users don't get databases
        $type = $this->package->type ?? '';
        if (stripos($type, 'icecast') !== false || stripos($type, 'shoutcast') !== false) {
            return $this->view('user.databases', ['user' => $user, 'hosting' => $this->hostingUser,
                'restricted' => true, 'databases' => [], 'users' => [], 'title' => 'Databases']);
        }
        return $user;
    }

    public function index()
    {
        $u = $this->requireUser();
        if (isset($u->restricted) || isset($u->databases)) return $u; // restricted view
        $prefix = $this->hostingUser->username . '_';
        $databases = [];
        $dbUsers = [];
        // List databases from MySQL that belong to this user
        $allDb = shell_exec("mysql -u root -e 'SHOW DATABASES' 2>/dev/null");
        if ($allDb) {
            $lines = explode("\n", trim($allDb));
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === 'Database' || $line === '' || $line === 'information_schema' || $line === 'performance_schema' || $line === 'mysql' || $line === 'sys') continue;
                if (str_starts_with($line, $prefix)) {
                    $databases[] = (object)['name' => $line, 'size' => $this->getDbSize($line)];
                }
            }
        }
        // List DB users for this user
        $allUsers = shell_exec("mysql -u root -e \"SELECT User FROM mysql.user WHERE User LIKE '{$prefix}%'\" 2>/dev/null");
        if ($allUsers) {
            $lines = explode("\n", trim($allUsers));
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === 'User' || $line === '') continue;
                $dbUsers[] = (object)['username' => $line];
            }
        }
        return $this->view('user.databases', [
            'user' => $u, 'hosting' => $this->hostingUser, 'package' => $this->package,
            'databases' => $databases, 'users' => $dbUsers, 'restricted' => false, 'title' => 'Databases'
        ]);
    }

    public function createDb()
    {
        $u = $this->requireUser();
        $prefix = $this->hostingUser->username . '_';
        $dbName = $prefix . preg_replace('/[^a-zA-Z0-9_]/', '', $this->request->post('name', 'db'));
        shell_exec("mysql -u root -e \"CREATE DATABASE IF NOT EXISTS {$dbName}\" 2>/dev/null");
        $_SESSION['success'] = "Database {$dbName} created.";
        $this->response->redirect('/user/databases');
        exit;
    }

    public function createUser()
    {
        $u = $this->requireUser();
        $prefix = $this->hostingUser->username . '_';
        $username = $prefix . preg_replace('/[^a-zA-Z0-9_]/', '', $this->request->post('username', 'user'));
        $password = $this->request->post('password', bin2hex(random_bytes(8)));
        $dbName = $this->request->post('database', '');
        shell_exec("mysql -u root -e \"CREATE USER IF NOT EXISTS '{$username}'@'localhost' IDENTIFIED BY '{$password}'\" 2>/dev/null");
        if ($dbName) {
            shell_exec("mysql -u root -e \"GRANT ALL PRIVILEGES ON {$prefix}{$dbName}.* TO '{$username}'@'localhost'\" 2>/dev/null");
        }
        shell_exec("mysql -u root -e 'FLUSH PRIVILEGES' 2>/dev/null");
        $_SESSION['success'] = "User {$username} created. Password: {$password}";
        $this->response->redirect('/user/databases');
        exit;
    }

    public function deleteDb($name)
    {
        $u = $this->requireUser();
        shell_exec("mysql -u root -e \"DROP DATABASE IF EXISTS {$name}\" 2>/dev/null");
        $this->response->redirect('/user/databases');
        exit;
    }

    public function phpMyAdmin()
    {
        $u = $this->requireUser();
        header('Location: /phpmyadmin');
        exit;
    }

    private function getDbSize($db)
    {
        $size = shell_exec("mysql -u root -e \"SELECT ROUND(SUM(data_length+index_length)/1024/1024,2) FROM information_schema.tables WHERE table_schema='{$db}'\" 2>/dev/null | tail -1");
        return trim($size ?: '0') . ' MB';
    }
}
