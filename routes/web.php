<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

use Illuminate\Routing\Router;

$routeConfig = [
    'namespace' => 'ErpNET\SocialAuth\Controllers',
    'middleware' => ['web'],
];

$router = app(Router::class);

$router->group($routeConfig, function(Router $router) {
    $router->get('socialAuth/{provider}', ['as'=>'socialAuth.redirect', 'uses'=>'SocialAuthController@redirectToProvider']);
    $router->get('socialAuth/{provider}/callback', ['as'=>'socialAuth.callback', 'uses'=>'SocialAuthController@handleProviderCallback']);

});