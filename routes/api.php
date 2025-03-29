<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;

/**
 * Public Routes
 */
Route::group([
    'as' => "imageable.",
    'prefix' => config('imageable.directory'),
    'namespace' => 'Elysiumrealms\Imageable\Http\Controllers',
], function (Router $router) {

    $router->get('/{image}', 'ImageableController@show')
        ->name('image.show');
});

/**
 * Authenticated Routes
 */
Route::group([
    'as' => "api.imageable.",
    'prefix' => "api/v1/imageable",
    'middleware' => config('imageable.route.middleware'),
    'namespace' => 'Elysiumrealms\Imageable\Http\Controllers',
], function (Router $router) {

    $router->get('/', 'ImageableController@index')
        ->name('index');

    $router->delete('/', 'ImageableController@destroy')
        ->name('destroy');

    $router->post('/', 'ImageableController@upload')
        ->name('upload');

    $router->post('/{collection}', 'ImageableController@upload')
        ->name('upload');

    $router->delete('/{image}', 'ImageableController@destroy')
        ->name('image.destroy');
});
