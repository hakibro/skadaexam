<?php

return [

    /*
     * Path ke Google Chrome / Chromium
     */
    'chrome_path' => env('CHROME_PATH', '/usr/bin/google-chrome'),

    /*
     * Node & NPM binary
     */
    'node_binary' => env('NODE_BINARY', '/usr/bin/node'),
    'npm_binary' => env('NPM_BINARY', '/usr/bin/npm'),

    /*
     * Timeout render
     */
    'timeout' => 120,

    /*
     * Argumen Chromium
     * WAJIB no-sandbox di VPS / shared hosting
     */
    'chromium_arguments' => [
        '--no-sandbox',
        '--disable-setuid-sandbox',
    ],
];
