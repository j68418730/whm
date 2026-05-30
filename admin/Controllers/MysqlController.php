<?php
/**
 * MySQL / Database Management Controller
 * Handles MySQL server, root password, database mapping, phpMyAdmin integration
 */

namespace Admin\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class MysqlController extends Controller
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
     * Show MySQL management dashboard
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

        // Get MySQL stats (for demo, we'll use dummy data)
        $mysqlStats = [
            'mysql_version' => '5.7.31',
            'total_databases' => rand(20, 200),
            'total_db_users' => rand(15, 150),
            'database_size' => rand(1, 50), // GB
            'queries_per_second' => rand(10, 1000),
            'slow_queries' => rand(0, 10),
        ];

        // Get admin theme settings
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the MySQL management view
        return $this->view('admin.mysql.index', [
            'user' => $user,
            'mysqlStats' => $mysqlStats,
            'theme_settings' => $theme_settings
        ]);
    }
}