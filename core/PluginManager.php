<?php

namespace Core;

class PluginManager
{
    protected $app;
    protected $plugins = [];
    protected $loaded = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function register($class)
    {
        if (!class_exists($class)) {
            return false;
        }

        $plugin = new $class($this->app);
        if (!$plugin instanceof Plugin) {
            return false;
        }

        $name = $plugin->getName();
        $this->plugins[$name] = $plugin;
        $plugin->register();

        return true;
    }

    public function boot()
    {
        foreach ($this->plugins as $name => $plugin) {
            $plugin->boot();
            $this->loaded[] = $name;
        }
    }

    public function get($name)
    {
        return $this->plugins[$name] ?? null;
    }

    public function isLoaded($name)
    {
        return in_array($name, $this->loaded, true);
    }

    public function all()
    {
        return $this->plugins;
    }

    public function loadFromConfig(array $pluginClasses)
    {
        foreach ($pluginClasses as $class) {
            $this->register($class);
        }
        $this->boot();
    }
}
