<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server Side Rendering
    |--------------------------------------------------------------------------
    |
    | These options configures if and how Vortex uses Server Side Rendering
    | to pre-render the initial visits made to your application's pages.
    |
    */

    'ssr' => [
        /**
         * Enable or disable Server Side Rendering.
         */
        'enabled' => env('VORTEX_SSR_ENABLED', true),

        /**
         * SSR mode ('cli' or 'server').
         *
         * CLI mode prerenders page from local console output.
         *
         * Server mode prerenders page using HTTP server.
         * Mainly needed if you want to setup SSR on other machine/docker container.
         */
        'mode' => env('VORTEX_SSR_MODE', 'cli'),

        /**
         * Path to the JavaScript runtime executable with additional options and arguments. Examples:
         * - 'node'
         * - 'bun run --bun'
         * - 'deno run --allow-net'
         */
        'bin' => env('VORTEX_SSR_BIN', 'node'),

        /**
         * Time to cache prerendered page in seconds. False - disabled.
         */
        'cache' => env('VORTEX_SSR_CACHE', 60 * 60 * 24),

        /**
         * SSR server worker url (only used in 'server' mode).
         */
        'url' => env('VORTEX_SSR_URL', 'http://127.0.0.1:13714'),

        /**
         * List of possible SSR bundle paths. Vortex will use the first one found.
         */
        'bundle' => [
            base_path('bootstrap/ssr/ssr.js'),
            base_path('bootstrap/ssr/ssr.mjs'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    |
    | The values described here are used to locate Vortex components on the
    | filesystem. For instance, when using `assertVortex`, the assertion
    | attempts to locate the component as a file relative to any of the
    | paths AND with any of the extensions specified here.
    |
    */

    'testing' => [
        'paths' => [
            resource_path('js/pages'),
        ],
        'extensions' => [
            'js', 'jsx',
            'ts', 'tsx',
            'svelte',
            'vue',
        ],
    ],
];
