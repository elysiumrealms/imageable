<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;

Route::group([
    'as' => 'api.imageable.',
    'prefix' => 'api/v1/imageable',
    'middleware' => config('imageable.route.middleware'),
    'namespace' => 'Elysiumrealms\Imageable\Http\Controllers',
], function (Router $router) {

    $router->get('/{collection}', 'ImageableController@index')
        ->name('index');

    $router->post('/{collection}', 'ImageableController@upload')
        ->name('upload');

    $router->delete('/', 'ImageableController@destroy')
        ->name('destroy');
});
