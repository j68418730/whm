<?php
/**
 * Radio Hosting Core Configuration
 * Integrated directly into hosting panel core (WHM)
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Radio Hosting Global Settings
    |--------------------------------------------------------------------------
    |
    | Controls whether radio hosting is enabled globally
    |
    */
    'global_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Radio Server Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the radio streaming servers
    |
    */
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
    ],

    /*
    |--------------------------------------------------------------------------
    | AutoDJ Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the AutoDJ system
    |
    */
    'autodj' => [
        'enabled' => true,
        'binary_path' => '/usr/bin/ezstream',
        'temp_directory' => '/home/{user}/radio/autodj/temp',
        'music_directory' => '/home/{user}/radio/autodj/music',
        'playlist_directory' => '/home/{user}/radio/autodj/playlists',
        'bitrate' => 128,
        'format' => 'mp3',
    ],

    /*
    |--------------------------------------------------------------------------
    | Transcoding Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for audio transcoding
    |
    */
    'transcoding' => [
        'enabled' => true,
        'ffmpeg_path' => '/usr/bin/ffmpeg',
        'supported_formats' => ['mp3', 'aac', 'ogg'],
        'default_bitrates' => [64, 96, 128, 192, 256, 320],
    ],

    /*
    |--------------------------------------------------------------------------
    | Limits and Quotas
    |--------------------------------------------------------------------------
    |
    | Default limits for radio hosting accounts
    |
    */
    'limits' => [
        'default_listener_limit' => 100,
        'default_bandwidth_limit' => 1000, // GB/month
        'default_storage_limit' => 10, // GB
        'default_dj_accounts' => 5,
        'default_playlists' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Enable/disable specific radio features
    |
    */
    'features' => [
        'listener_analytics' => true,
        'dj_management' => true,
        'playlist_engine' => true,
        'billable_usage' => true,
        'api_access' => true,
    ],

];