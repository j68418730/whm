<?php

return [
    'radio' => [
        'global_enabled' => true,
        'servers' => [
            'icecast' => [
                'enabled' => true,
                'binary_path' => '/usr/bin/icecast',
                'config_path' => '/etc/icecast2/icecast.xml',
                'default_port' => 8000,
                'admin_port' => 8001,
                'source_password' => 'hackme',
                'admin_password' => 'hackme',
            ],
            'shoutcast' => [
                'enabled' => true,
                'binary_path' => '/usr/bin/sc_serv',
                'config_path' => '/etc/shoutcast/sc_serv.conf',
                'default_port' => 8000,
            ],
        ],
        'autodj' => [
            'enabled' => true,
            'binary_path' => '/usr/bin/ezstream',
            'temp_directory' => '/home/{user}/radio/autodj/temp',
            'music_directory' => '/home/{user}/radio/autodj/music',
            'playlist_directory' => '/home/{user}/radio/autodj/playlists',
            'bitrate' => 128,
            'format' => 'mp3',
        ],
        'transcoding' => [
            'enabled' => true,
            'ffmpeg_path' => '/usr/bin/ffmpeg',
            'supported_formats' => ['mp3', 'aac', 'ogg'],
            'default_bitrates' => [64, 96, 128, 192, 256, 320],
        ],
        'limits' => [
            'default_listener_limit' => 100,
            'default_bandwidth_limit' => 1000,
            'default_storage_limit' => 10,
            'default_dj_accounts' => 5,
            'default_playlists' => 10,
        ],
        'features' => [
            'listener_analytics' => true,
            'dj_management' => true,
            'playlist_engine' => true,
            'billable_usage' => true,
            'api_access' => true,
        ],
    ],
];
