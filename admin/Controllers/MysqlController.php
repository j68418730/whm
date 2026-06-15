<?php

namespace Admin\Controllers;

use Core\Controller;

class MysqlController extends Controller
{
    protected $auth, $request, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $pw = 'Skylinehosting171';
        $version = trim(shell_exec("mysql -u root -p{$pw} -V 2>/dev/null") ?: 'MariaDB -');
        $dbCount = (int)shell_exec("mysql -u root -p{$pw} -e 'SHOW DATABASES' 2>/dev/null | wc -l") ?: 0;
        $userCount = (int)shell_exec("mysql -u root -p{$pw} -e \"SELECT COUNT(*) FROM mysql.user WHERE user NOT IN ('root','mariadb.sys','mysql')\" 2>/dev/null | tail -1") ?: 0;
        $queries = (int)trim(shell_exec("mysql -u root -p{$pw} -e 'SHOW GLOBAL STATUS LIKE \"Questions\"' 2>/dev/null | tail -1 | awk '{print \$2}'") ?: '0');
        $slow = (int)trim(shell_exec("mysql -u root -p{$pw} -e 'SHOW GLOBAL STATUS LIKE \"Slow_queries\"' 2>/dev/null | tail -1 | awk '{print \$2}'") ?: '0');
        $size = (float)trim(shell_exec("mysql -u root -p{$pw} -e 'SELECT ROUND(SUM(data_length+index_length)/1024/1024,1) FROM information_schema.tables' 2>/dev/null | tail -1") ?: '0');
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.mysql.index', [
            'user' => $user, 'mysqlStats' => [
                'mysql_version' => $version, 'total_databases' => max(0, $dbCount - 5),
                'total_db_users' => $userCount, 'database_size' => $size,
                'queries_per_second' => $queries, 'slow_queries' => $slow,
            ], 'theme_settings' => $theme_settings
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
