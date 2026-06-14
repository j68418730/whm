<?php

namespace Admin\Controllers;

use Core\Controller;

class PhpController extends Controller
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
        $loaded = get_loaded_extensions();
        $allExts = ['bcmath','bz2','calendar','ctype','curl','date','dom','exif','fileinfo','filter','ftp','gd','gettext','gmp','hash','iconv','imagick','imap','intl','json','ldap','libxml','mbstring','mysqli','mysqlnd','opcache','openssl','pcntl','pcre','PDO','pdo_mysql','pdo_sqlite','pear','phar','posix','pspell','readline','redis','reflection','session','shmop','SimpleXML','soap','sockets','sodium','SPL','sqlite3','standard','sysvmsg','sysvsem','sysvshm','tokenizer','wddx','xml','xmlreader','xmlwriter','xsl','Zend OPcache','zip','zlib'];
        $avail = array_diff($allExts, $loaded);
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.php.index', [
            'user' => $user, 'loaded' => $loaded, 'available' => $avail,
            'phpStats' => ['enabled_extensions' => count($loaded), 'available_versions' => explode('.', PHP_VERSION)],
            'theme_settings' => $theme_settings, 'title' => 'PHP Manager'
        ]);
    }

    public function install($ext)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $ext = basename($ext);
        $output = shell_exec("apt install -y php-{$ext} 2>&1") ?: shell_exec("pecl install {$ext} 2>&1");
        $_SESSION['success_message'] = "Installing {$ext}... Output saved.";
        $this->response->redirect('/admin/php');
        exit;
    }
}
