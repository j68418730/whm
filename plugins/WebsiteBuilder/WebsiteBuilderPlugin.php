<?php

namespace Plugins\WebsiteBuilder;

use Core\Plugin;

class WebsiteBuilderPlugin extends Plugin
{
    public function getName()
    {
        return 'WebsiteBuilder';
    }

    public function register()
    {
    }

    public function boot()
    {
        $this->loadRoutes();
    }
}
