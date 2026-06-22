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
        $type = $this->package->type ?? '';
        if (stripos($type, 'icecast') !== false || stripos($type, 'shoutcast') !== false) {
            return $this->view('user.databases', ['user' => $user, 'hosting' => $this->hostingUser,
                'restricted' => true, 'databases' => [], 'users' => [], 'title' => 'Databases']);
        }
        return $user;
    }

    protected function rootDb()
    {
        return new \PDO('mysql:host=localhost;charset=utf8mb4', 'root', 'rootpassword');
    }

    public function index()
    {
        $u = $this->requireUser();
        if (isset($u->restricted) || isset($u->databases)) return $u;
        $prefix = $this->hostingUser->username . '_';
        $databases = [];
        $dbUsers = [];

        try {
            $pdo = $this->rootDb();
            $q = $pdo->query("SHOW DATABASES");
            foreach ($q as $row) {
                $name = $row[0];
                if ($name === 'Database' || $name === '' || in_array($name, ['information_schema','performance_schema','mysql','sys'])) continue;
                if (str_starts_with($name, $prefix)) {
                    $sizeQ = $pdo->query("SELECT ROUND(SUM(data_length+index_length)/1024/1024,2) FROM information_schema.tables WHERE table_schema=" . $pdo->quote($name));
                    $size = $sizeQ ? $sizeQ->fetchColumn() : 0;
                    $databases[] = (object)['name' => $name, 'size' => ($size ?: 0) . ' MB'];
                }
            }

            $userQ = $pdo->query("SELECT User FROM mysql.user WHERE User LIKE " . $pdo->quote($prefix . '%'));
            if ($userQ) {
                foreach ($userQ as $row) {
                    $dbUsers[] = (object)['username' => $row[0]];
                }
            }
        } catch (\Exception $e) {}

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
        try {
            $this->rootDb()->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}`");
        } catch (\Exception $e) {}
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
        try {
            $pdo = $this->rootDb();
            $pdo->exec("CREATE USER IF NOT EXISTS '{$username}'@'localhost' IDENTIFIED BY " . $pdo->quote($password));
            if ($dbName) {
                $pdo->exec("GRANT ALL PRIVILEGES ON `{$prefix}{$dbName}`.* TO '{$username}'@'localhost'");
            }
            $pdo->exec("FLUSH PRIVILEGES");
        } catch (\Exception $e) {}
        $_SESSION['success'] = "User {$username} created. Password: {$password}";
        $this->response->redirect('/user/databases');
        exit;
    }

    public function deleteDb($name)
    {
        $u = $this->requireUser();
        try {
            $this->rootDb()->exec("DROP DATABASE IF EXISTS `{$name}`");
        } catch (\Exception $e) {}
        $this->response->redirect('/user/databases');
        exit;
    }

    public function phpMyAdmin()
    {
        $u = $this->requireUser();
        header('Location: /phpmyadmin');
        exit;
    }
}
