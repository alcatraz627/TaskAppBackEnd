<?php

use Illuminate\Support\Facades\Route;

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

$router->get('/', function () {
    if(env("APP_DEBUG")) {
        return response()->json(Route::getRoutes());
    }
    return redirect(env('FRONTEND_URL'));
});

$router->group(['prefix' => 'api'], function () use ($router) {
    // $router->get('pushNotif', 'TaskController@pushNotif');

    $router->get('', function () {
        return response()->json(['message' => 'Available'], 200);
    });

    $router->get('tasks', 'TaskController@list');
    $router->get('tasks/{id}', 'TaskController@retrieve');
    $router->post('tasks', 'TaskController@create');
    $router->patch('tasks/{id}', ['middleware' => ['emptyToNull'], 'uses' => 'TaskController@update']);
    $router->delete('tasks/{id}', 'TaskController@delete');

    $router->post('auth/register', ['middleware' => 'guest', 'uses' => 'AuthController@register']);
    $router->post('auth/verify/{token}', ['middleware' => 'guest', 'uses' => 'AuthController@email_verify']);
    $router->post('auth/login', ['middleware' => 'guest', 'uses' => 'AuthController@login']);
    $router->post('auth/refresh', ['middleware' => 'auth', 'uses' => 'AuthController@refresh_token']);
    $router->post('auth/logout', ['middleware' => 'auth', 'uses' => 'AuthController@logout']);

    $router->get('auth/me', ['middleware' => 'auth', 'uses' => 'AuthController@me']);

    $router->post('auth/forgotpass/request', ['middleware' => 'guest', 'uses' => 'AuthController@forgotpass_request']);
    $router->post('auth/forgotpass/verify', ['middleware' => 'guest', 'uses' => 'AuthController@forgotpass_verify']);
    $router->post('auth/forgotpass/reset', ['middleware' => 'guest', 'uses' => 'AuthController@forgotpass_reset']);

    $router->get('users', ['middleware' => ['auth', 'permission:user-list'], 'uses' => 'UserController@index']);
    $router->get('users/{id}', 'UserController@retrieve');

    $router->get('users/{id}/tasks/', 'UserController@tasklist');

    $router->post('users', ['middleware' => ['auth', 'permission:user-create'], 'uses' => 'UserController@create']);
    $router->patch('users/{id}', 'UserController@update');
    $router->delete('users/{id}', ['middleware' => ['auth', 'permission:user-delete'], 'uses' => 'UserController@delete']);
});

// $router->get('/', function () use ($router) {
//     return $router->app->version();
// });
