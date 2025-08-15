<?php

return [
    // Default duration in seconds a free server is active after being claimed.
    'default_duration' => env('FREE_SERVER_DURATION', 3 * 60 * 60),
    // How many seconds to extend the server when the user requests an extension.
    'extend_duration' => env('FREE_SERVER_EXTEND', 3 * 60 * 60),
    // How many seconds before expiration the user is allowed to extend.
    'extend_window' => env('FREE_SERVER_EXTEND_WINDOW', 30 * 60),

    // Server provisioning defaults used when a user claims a free server.
    'provision' => [
        'node_id' => env('FREE_SERVER_NODE', 1),
        'allocation_id' => env('FREE_SERVER_ALLOCATION', 1),
        'egg_id' => env('FREE_SERVER_EGG', 1),
        'image' => env('FREE_SERVER_IMAGE', 'quay.io/pterodactyl/core:java'),
        'startup' => env('FREE_SERVER_STARTUP', 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar server.jar'),
        'memory' => env('FREE_SERVER_MEMORY', 512),
        'swap' => env('FREE_SERVER_SWAP', 0),
        'disk' => env('FREE_SERVER_DISK', 512),
        'io' => env('FREE_SERVER_IO', 500),
        'cpu' => env('FREE_SERVER_CPU', 0),
        'environment' => [],
    ],
];
