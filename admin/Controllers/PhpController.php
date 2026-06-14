<?php

namespace Admin\Controllers;

use Core\Controller;
use Admin\Services\PhpManager;

class PhpController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $manager;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->manager = new PhpManager();
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $availVersions = $this->manager->getAvailableVersions();
        $versionNames = array_map(function($v) { return $v['version']; }, $availVersions);
        $phpStats = [
            'available_versions' => $versionNames,
            'default_version' => $this->manager->getDefaultVersion(),
            'php_fpm_pools' => 0,
            'total_ini_directives' => count(ini_get_all()),
            'enabled_extensions' => count(get_loaded_extensions()),
        ];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.php.index', [
            'user' => $user,
            'phpStats' => $phpStats,
            'theme_settings' => $theme_settings
        ]);
    }

    public function extensions()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $exts = get_loaded_extensions();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.php.extensions', [
            'user' => $user,
            'extensions' => $exts,
            'theme_settings' => $theme_settings
        ]);
    }

    public function config()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $iniValues = [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_vars' => ini_get('max_input_vars'),
            'max_input_time' => ini_get('max_input_time'),
        ];
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.php.config', [
            'user' => $user,
            'iniValues' => $iniValues,
            'theme_settings' => $theme_settings
        ]);
    }
}