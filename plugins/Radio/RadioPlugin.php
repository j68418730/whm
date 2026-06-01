<?php

namespace Plugins\Radio;

use Core\Plugin;
use Core\Application;

class RadioPlugin extends Plugin
{
    public function getName()
    {
        return 'Radio';
    }

    public function register()
    {
        $app = $this->app;

        $config = $app->get('config');
        $db = $app->get('db');

        $app->set('radio.stream', new Services\StreamManager($config, $db));
        $app->set('radio.autodj', new Services\AutoDJManager($config, $db));
        $app->set('radio.transcoding', new Services\TranscodingManager($config));

        $app->set('radio.config', $this->getConfig('radio', []));
    }

    public function boot()
    {
        $this->loadRoutes();
    }
}
