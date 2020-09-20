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
    $router->get('tasks/{id}', 'TaskController@retrieve');

    $router->post('auth/register', ['middleware' => 'guest', 'uses' => 'AuthController@register']);
    $router->get('auth/verify/{token}', ['middleware' => 'guest', 'uses' => 'AuthController@verify']);
    $router->post('auth/login', ['middleware' => 'guest', 'uses' => 'AuthController@login']);
    $router->get('auth/me', 'AuthController@me');
    
    // $router->post('auth/forgotpass/request', 'AuthController@forgotpass_request');
    // $router->post('auth/forgotpass/reset', 'AuthController@forgotpass_reset');
    
    $router->get('users', ['middleware' => ['auth', 'permission:user-list'] ,'uses' => 'UserController@index']);
    $router->get('users/{id}', 'UserController@retrieve');
    
    $router->post('users', ['middleware' => ['auth', 'permission:user-create'] ,'uses' => 'UserController@create']);
    $router->patch('users/{id}', 'UserController@update');
    $router->delete('users/{id}', ['middleware' => ['auth', 'permission:user-delete'] ,'uses' => 'UserController@delete']);

});

// $router->get('/', function () use ($router) {
//     return $router->app->version();
// });
