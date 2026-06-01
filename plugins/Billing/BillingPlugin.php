<?php

namespace Plugins\Billing;

use Core\Plugin;

class BillingPlugin extends Plugin
{
    public function getName()
    {
        return 'Billing';
    }

    public function register()
    {
        // Register billing services here
    }

    public function boot()
    {
        $this->loadRoutes();
    }
}
