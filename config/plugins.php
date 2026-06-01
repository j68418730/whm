<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled Plugins
    |--------------------------------------------------------------------------
    |
    | List of plugin classes to load. Comment out or remove a plugin
    | to disable it. Order matters for dependencies.
    |
    */

    'enabled' => [
        // Radio streaming (Icecast, SHOUTcast, AutoDJ, transcoding)
        \Plugins\Radio\RadioPlugin::class,

        // Billing system (invoices, subscriptions, payments)
        // \Plugins\Billing\BillingPlugin::class,

        // Website builder (AI site generator, templates)
        // \Plugins\WebsiteBuilder\WebsiteBuilderPlugin::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin Paths
    |--------------------------------------------------------------------------
    |
    */

    'paths' => [
        base_path('plugins'),
    ],
];
