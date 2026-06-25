<?php
// Planet Hosts - Default Application Configuration
return [
    'name' => 'Planet Hosts',
    'version' => '1.0.0',
    'env' => 'production',
    'debug' => false,
    'url' => 'http://localhost',
    'timezone' => 'UTC',
    'locale' => 'en',
    'panel_dir' => '/var/www/radiohosting',
    'log_dir' => '/var/log/planethosts',
    'license_file' => '/etc/planethosts/license.json',
    'storage' => [
        'backups' => '/var/backups/planethosts',
        'uploads' => '/var/www/radiohosting/uploads',
        'cache' => '/var/www/radiohosting/cache',
        'logs' => '/var/log/planethosts',
        'temp' => '/tmp/planethosts',
    ],
    'services' => [
        'httpd' => 'httpd',
        'mariadb' => 'mariadb',
        'firewalld' => 'firewalld',
        'icecast' => 'icecast',
        'crond' => 'crond',
    ],
];
