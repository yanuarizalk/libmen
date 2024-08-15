<?php
use App\Http\Middleware\PagingMiddleware;

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'author'], function () use ($router) {
    $router->get('', ['middleware' => PagingMiddleware::class, 'uses' => 'AuthorController@index']);
    $router->get('{id}', 'AuthorController@detail');
    $router->post('', 'AuthorController@store');
    $router->put('{id}', 'AuthorController@update');
    $router->delete('{id}', 'AuthorController@destroy');
    $router->get('{authorId}/books', ['middleware' => PagingMiddleware::class, 'uses' => 'AuthorController@books']);
});

$router->group(['prefix' => 'book'], function () use ($router) {
    $router->get('', ['middleware' => PagingMiddleware::class, 'uses' => 'BookController@index']);
    $router->get('{id}', 'BookController@detail');
    $router->post('', 'BookController@store');
    $router->put('{id}', 'BookController@update');
    $router->delete('{id}', 'BookController@destroy');
});
