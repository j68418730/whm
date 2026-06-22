<?php
namespace Plugins\GameServers;

use Core\Plugin;

class GameServersPlugin extends Plugin
{
    public function getName()
    {
        return 'Game Servers';
    }

    public function getVersion()
    {
        return '1.0.0';
    }

    public function getMetadata()
    {
        return [
            'name' => 'Game Servers',
            'version' => '1.0.0',
            'description' => 'Deploy and manage Linux dedicated game servers with one-click install and real-time status.',
            'author' => 'Planet Hosts',
            'icon' => '🎮',
        ];
    }

    public function boot()
    {
        $router = $this->app->get('router');
        require __DIR__ . '/routes.php';
    }
}
