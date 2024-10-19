<?php

// Automatically get certificate files from httpd conf
if (is_file('/etc/httpd/conf.d/nmsprime-admin.conf')) {
    $content = file_get_contents('/etc/httpd/conf.d/nmsprime-admin.conf');

    preg_match('/^[ \t]*SSLCertificateFile (\/.*)/m', $content, $cert);
    preg_match('/^[ \t]*SSLCertificateKeyFile (\/.*)/m', $content, $certKey);
}

if (env('LARAVEL_WEBSOCKETS_RESTRICT_BY_IP', false)) {
    $allowedOrigins = [
        '127.0.0.1',
        'localhost',
        env('APP_URI'),
    ];
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Reverb Server
    |--------------------------------------------------------------------------
    |
    | This option controls the default server used by Reverb to handle
    | incoming messages as well as broadcasting message to all your
    | connected clients. At this time only "reverb" is supported.
    |
    */

    'default' => env('REVERB_SERVER', 'reverb'),

    /*
    |--------------------------------------------------------------------------
    | Reverb Servers
    |--------------------------------------------------------------------------
    |
    | Here you may define details for each of the supported Reverb servers.
    | Each server has its own configuration options that are defined in
    | the array below. You should ensure all the options are present.
    |
    */

    'servers' => [

        'reverb' => [
            'host' => env('REVERB_SERVER_HOST', '127.0.0.1'),
            'port' => env('REVERB_SERVER_PORT', 6001),
            'hostname' => env('REVERB_HOST', '127.0.0.1'),
            'options' => [
                'tls' => [
                    'local_cert' => $cert[1] ?? env('LARAVEL_WEBSOCKETS_SSL_LOCAL_CERT', '/etc/httpd/ssl/httpd.pem'),
                    'local_pk' => $certKey[1] ?? env('LARAVEL_WEBSOCKETS_SSL_LOCAL_PK', '/etc/httpd/ssl/httpd.key'),
                    'allow_self_signed' => env('LARAVEL_WEBSOCKETS_ALLOW_SELF_SIGNED', true),
                    'verify_peer' => env('LARAVEL_WEBSOCKETS_VERIFY_PEER', false),
                ],
            ],
            'max_request_size' => env('REVERB_MAX_REQUEST_SIZE', 10_000),
            'scaling' => [
                'enabled' => env('REVERB_SCALING_ENABLED', false),
                'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
                'server' => [
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'port' => env('REDIS_PORT', '6379'),
                    'password' => env('REDIS_PASSWORD'),
                    'database' => env('REDIS_DB', '0'),
                ],
            ],
            'pulse_ingest_interval' => env('REVERB_PULSE_INGEST_INTERVAL', 15),
            'telescope_ingest_interval' => env('REVERB_TELESCOPE_INGEST_INTERVAL', 15),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Reverb Applications
    |--------------------------------------------------------------------------
    |
    | Here you may define how Reverb applications are managed. If you choose
    | to use the "config" provider, you may define an array of apps which
    | your server will support, including their connection credentials.
    |
    */

    'apps' => [

        'provider' => 'config',

        'apps' => [
            [
                'key' => env('MIX_PUSHER_APP_KEY', 'nmsprime'),
                'secret' => env('PUSHER_APP_SECRET', 'nmsprime'),
                'app_id' => env('PUSHER_APP_ID', 'nmsprime'),
                'options' => [
                    'host' => env('APP_URL', '127.0.0.1'),
                    'port' => (int) env('MIX_WEBSOCKETS_PORT', env('HTTPS_ADMIN_PORT', 8080)),
                    'scheme' => env('PUSHER_APP_SCHEME', 'https'),
                    'useTLS' => env('PUSHER_APP_SCHEME', 'https') === 'https',
                ],
                'allowed_origins' => $allowedOrigins ?? ['*'],
                'ping_interval' => env('REVERB_APP_PING_INTERVAL', 60),
                'max_message_size' => env('REVERB_APP_MAX_MESSAGE_SIZE', 10_000),
            ],
        ],

    ],

];
