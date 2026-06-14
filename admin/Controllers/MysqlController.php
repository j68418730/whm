<?php

namespace Admin\Controllers;

use Core\Controller;

class MysqlController extends Controller
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
        $version = trim(shell_exec("mysql -V 2>/dev/null") ?: 'MariaDB -');
        $databases = shell_exec("mysql -u root -e 'SHOW DATABASES' 2>/dev/null | wc -l") ?: 0;
        $dbUsers = shell_exec("mysql -u root -e \"SELECT COUNT(*) FROM mysql.user WHERE user NOT IN ('root','mariadb.sys')\" 2>/dev/null | tail -1") ?: 0;
        $queries = trim(shell_exec("mysql -u root -e 'SHOW GLOBAL STATUS LIKE \"Questions\"' 2>/dev/null | tail -1 | awk '{print \$2}'") ?: '0');
        $slow = trim(shell_exec("mysql -u root -e 'SHOW GLOBAL STATUS LIKE \"Slow_queries\"' 2>/dev/null | tail -1 | awk '{print \$2}'") ?: '0');
        $size = trim(shell_exec("mysql -u root -e 'SELECT ROUND(SUM(data_length+index_length)/1024/1024,1) FROM information_schema.tables' 2>/dev/null | tail -1") ?: '0');
        $dbName = getenv('DB_DATABASE') ?: 'radiohosting';
        $dbPass = getenv('DB_PASSWORD') ?: '';
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.mysql.index', [
            'user' => $user, 'mysqlStats' => [
                'mysql_version' => $version, 'total_databases' => max(0, (int)$databases - 3),
                'total_db_users' => max(0, (int)$dbUsers), 'database_size' => (float)$size,
                'queries_per_second' => (int)$queries, 'slow_queries' => (int)$slow,
            ], 'dbName' => $dbName, 'dbPass' => $dbPass, 'theme_settings' => $theme_settings
        ]);
    }

    public function restart()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        shell_exec("systemctl restart mariadb 2>/dev/null >/dev/null &");
        $_SESSION['success_message'] = 'MariaDB restarted.';
        $this->response->redirect('/admin/mysql');
        exit;
    }
}
