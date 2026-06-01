<?php

namespace Core;

abstract class Plugin
{
    protected $app;
    protected $name;
    protected $path;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->name = $this->getName();
        $this->path = $app->getBasePath() . '/plugins/' . $this->name;
    }

    abstract public function getName();

    public function register()
    {
    }

    public function boot()
    {
    }

    public function getPath($sub = '')
    {
        return $sub ? $this->path . '/' . ltrim($sub, '/') : $this->path;
    }

    public function getConfig($key, $default = null)
    {
        $configPath = $this->path . '/config/config.php';
        if (is_file($configPath)) {
            $config = require $configPath;
            return $config[$key] ?? $default;
        }
        return $default;
    }

    protected function loadRoutes()
    {
        $router = $this->app->get('router');
        $routesFile = $this->path . '/routes.php';
        if (is_file($routesFile)) {
            require $routesFile;
        }
    }

    protected function loadViews()
    {
        $viewsPath = $this->path . '/Views';
        if (is_dir($viewsPath)) {
            // In a full implementation, register with a view finder
        }
    }
}
