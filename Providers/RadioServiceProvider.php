<?php
/**
 * Radio Service Provider
 * Boots the radio hosting services as part of the core platform
 */

namespace Providers;

use Core\Application;
use Core\ServiceProvider;

class RadioServiceProvider extends ServiceProvider
{
    /**
     * Register the radio services.
     */
    public function register()
    {
        // The services are already registered in the Application's registerCoreServices method
        // but we can add any additional bindings here if needed.
        //
        // For example, we might want to bind an interface to an implementation.
        // $this->app->bind('Radio\StreamManagerInterface', function ($app) {
        //     return new \Services\Stream\StreamManager($app->make('config'), $app->make('db'));
        // });
    }

    /**
     * Boot the radio services.
     */
    public function boot()
    {
        // Load the radio routes for user and admin panels
        $this->loadRoutes();

        // We can perform any booting logic here, such as registering routes, middleware, etc.
        //
        // For example, we might load the radio routes:
        // $this->loadRoutes();

        // Or we might publish configuration (if we were using a package-like structure, but we are not).
        // Since we are building a monolithic core, we don't need to publish anything.

        // However, we can still register the radio navigation menu items for the admin and user panels.
        $this->registerNavigation();
    }

    /**
     * Load the radio routes.
     */
    protected function loadRoutes()
    {
        $router = $this->app->get('router');
        $base = $this->app->getBasePath();

        // Load user panel routes for radio
        if (file_exists($base . '/routes/user.php')) {
            require $base . '/routes/user.php';
        }

        // Load admin panel routes for radio
        if (file_exists($base . '/routes/admin.php')) {
            require $base . '/routes/admin.php';
        }
    }

    /**
     * Register radio navigation menu items.
     */
    protected function registerNavigation()
    {
        // In a real system, we would have a navigation manager.
        // For now, we'll note that we need to add a "Radio Streaming" section to the sidebar.
        // This would be done by modifying the view composers or navigation configuration.
        //
        // Since we are building from scratch, we can leave this as a note for the view layer.
        // The actual implementation would depend on how we build the UI.
    }
}