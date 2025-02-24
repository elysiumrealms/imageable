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
