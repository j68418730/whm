<?php

namespace Plugins\Studio;

use Core\Plugin;
use Core\Application;

class StudioPlugin extends Plugin
{
    public function getName()
    {
        return 'Studio';
    }

    public function getDescription()
    {
        return 'Planet Hosts Studio — modern broadcasting interface';
    }

    public function getCategory()
    {
        return 'streaming';
    }

    public function getAdminUrl()
    {
        return '/admin/studio';
    }

    public function register()
    {
        $app = $this->app;
        $config = $app->get('config');
        $db = $app->get('db');

        $app->set('studio.service', new Services\StudioService($db, $config));
    }

    public function boot()
    {
        $this->loadRoutes();
    }
}