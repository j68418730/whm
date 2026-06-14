<?php

namespace Admin\Controllers;

use Core\Controller;

class InstallersController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;

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
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        $apps = $this->getPopularApps();
        return $this->view('admin.installers.index', [
            'user' => $user, 'theme_settings' => $theme_settings, 'title' => 'One-Click Installer', 'apps' => $apps
        ]);
    }

    public function install()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $name = $this->request->post('app_name', '');
        $domain = $this->request->post('domain', '');
        $dir = $this->request->post('directory', '');
        $targetDir = $dir ? "/var/www/html/$domain/$dir" : "/var/www/html/$domain";
        $url = $this->getDownloadUrl($name);

        if ($url) {
            @mkdir($targetDir, 0755, true);
            $zip = "$targetDir/app.zip";
            $cmd = "wget -q -O " . escapeshellarg($zip) . " " . escapeshellarg($url) . " 2>&1 && cd " . escapeshellarg($targetDir) . " && unzip -qo app.zip && rm -f app.zip && echo OK";
            $output = shell_exec($cmd);
            $_SESSION['success_message'] = $output ? "$name installed to $targetDir" : "Installation failed";
        } else {
            $_SESSION['success_message'] = "App not found: $name";
        }
        $this->response->redirect('/admin/installers');
    }

    private function getDownloadUrl($name)
    {
        $map = [
            'WordPress' => 'https://wordpress.org/latest.zip',
            'Joomla' => 'https://downloads.joomla.org/cms/joomla5/5-1-4/Joomla_5-1-4-Stable-Full_Package.zip',
            'Drupal' => 'https://ftp.drupal.org/files/projects/drupal-11.0.1.zip',
            'Laravel' => 'https://github.com/laravel/laravel/archive/refs/heads/master.zip',
            'phpMyAdmin' => 'https://files.phpmyadmin.net/phpMyAdmin/5.2.1/phpMyAdmin-5.2.1-all-languages.zip',
            'Nextcloud' => 'https://download.nextcloud.com/server/releases/latest.zip',
        ];
        return $map[$name] ?? null;
    }

    private function getPopularApps()
    {
        return [
            ['name' => 'WordPress', 'icon' => '📝', 'desc' => 'Blog & CMS'],
            ['name' => 'Joomla', 'icon' => '📰', 'desc' => 'CMS Framework'],
            ['name' => 'Drupal', 'icon' => '🌐', 'desc' => 'Enterprise CMS'],
            ['name' => 'Laravel', 'icon' => '⚡', 'desc' => 'PHP Framework'],
            ['name' => 'phpMyAdmin', 'icon' => '🗄️', 'desc' => 'Database Manager'],
            ['name' => 'Nextcloud', 'icon' => '☁️', 'desc' => 'Cloud Storage'],
        ];
    }
}
