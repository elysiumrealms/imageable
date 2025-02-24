<?php

return [
    /*
     | --------------------------------------------------------------------------
     | Imageable Filesystem Disk
     | --------------------------------------------------------------------------
     |
     | The disk to use store for the images.
     |
     | Supported Drivers: "public", "s3"
     */
    'disk' => env('IMAGEABLE_DRIVER', 'public'),

    /*
     | --------------------------------------------------------------------------
     | Imageable Directory
     | --------------------------------------------------------------------------
     |
     | The directory to use for the images, every image path will be prefixed
     | with this directory. The directory will be used to distinguish the
     | images which have been uploaded using this package.
     |
     */
    'directory' => 'imageable',

    /*
    | --------------------------------------------------------------------------
    | Imageable Proxy Configuration
    | --------------------------------------------------------------------------
    |
    | The proxy configuration to use for the images.
    |
    */
    'proxy' => [
        /*
        | --------------------------------------------------------------------------
        | Imageable Proxy Enabled
        | --------------------------------------------------------------------------
        |
        | Serve the images from specific url.
        |
        */
        'enabled' => env('IMAGEABLE_PROXY_ENABLED', false),

        /*
        | --------------------------------------------------------------------------
        | Imageable Proxy URL
        | --------------------------------------------------------------------------
        |
        | The URL to use for the proxy. When Url is default to APP_URL, the images
        | will be served from the same url as the application.
        |
        */
        'url' => env('IMAGEABLE_PROXY_URL', env('APP_URL')),

        /*
        | --------------------------------------------------------------------------
        | Imageable Proxy Cache
        | --------------------------------------------------------------------------
        |
        | The cache to use for the proxy.
        |
        */
        'cache' => env('IMAGEABLE_PROXY_CACHE', 'public, max-age=3600'),
    ],

    /*
     | --------------------------------------------------------------------------
     | Imageable Route
     | --------------------------------------------------------------------------
     |
     | The route to use for the images.
     |
     */
    'route' => [

        /*
         | --------------------------------------------------------------------------
         | Imageable Route Middleware
         | --------------------------------------------------------------------------
         |
         | The middleware to use for the images.
         |
         */
        'middleware' => [
            'api',
            'auth:api',
        ],
    ],
];
