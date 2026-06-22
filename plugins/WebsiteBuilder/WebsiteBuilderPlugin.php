<?php

namespace Plugins\WebsiteBuilder;

use Core\Plugin;

class WebsiteBuilderPlugin extends Plugin
{
    public function getName()
    {
        return 'WebsiteBuilder';
    }

    public function getDescription()
    {
        return 'Complete drag-and-drop website builder with 30+ templates';
    }

    public function getCategory()
    {
        return 'website';
    }

    public function getAdminUrl()
    {
        return '/admin/websitebuilder';
    }

    public function getUserUrl()
    {
        return '/user/websites';
    }

    public function getFeatures()
    {
        return [
            'drag_drop_editor' => 'Drag-and-drop page builder',
            '30_templates' => '30+ pre-built templates',
            'blog' => 'Built-in blog engine',
            'forms' => 'Form builder with entries',
            'media' => 'Media library',
            'menus' => 'Menu manager',
            'seo' => 'SEO tools (sitemap, robots.txt)',
        ];
    }

    public function register()
    {
        $this->app->set('websitebuilder.engine', new \Services\WebsiteBuilderEngine());
        $this->app->set('websitebuilder.template', new \Services\WebsiteBuilderTemplate());
    }

    public function boot()
    {
        $this->loadRoutes();
    }
}
