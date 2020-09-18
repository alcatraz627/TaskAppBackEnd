<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function() {
    return "Hello Bois";
});

$router->group(['prefix' => 'api'], function () use($router) {
    $router->get('tasks/', 'TaskController@index');

    $router->post('user/register', 'AuthController@register');
    $router->post('user/login', 'AuthController@login');
});

// $router->get('/', function () use ($router) {
//     return $router->app->version();
// });
