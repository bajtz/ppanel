<?php

return [
    // Default duration in seconds a free server is active after being claimed.
    'default_duration' => env('FREE_SERVER_DURATION', 3 * 60 * 60),
    // How many seconds to extend the server when the user requests an extension.
    'extend_duration' => env('FREE_SERVER_EXTEND', 3 * 60 * 60),
    // How many seconds before expiration the user is allowed to extend.
    'extend_window' => env('FREE_SERVER_EXTEND_WINDOW', 30 * 60),
];
