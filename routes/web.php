<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
| start sphinx : /usr/local/sphinx/bin/searchd --config /usr/local/sphinx/etc/batdongsan.conf
|
*/
//Automatically create resource api, model,router.
//
//
//`php artisan resource:create {route_name} --version_route={default} --controller={null}`
// Generate doc api :  php artisan api:generate --router="dingo" --routePrefix="v2"
$router->get('/', function () use ($router) {
    return [
        'Application' => 'Lumen framework!',
        'Version' => $router->app->version()
    ];
});

$router->get('/test-demo','TestController@test');

//$router->post('/api/test-demo','TestController@testpost');

//$router->group(['middleware' => 'cors', 'namespace' => 'Api\V1', 'prefix' => 'v1'], function () use ($router) {
//    require base_path('routes/router_v1.php');
//});
//
//require base_path('routes/any_route.php');
//$router = app('Dingo\Api\Routing\Router');

$router->get('/', function () use ($router) {
    return ['version'=>$router->app->version()];
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->group(['namespace'=>'Api\V1','prefix'=>'v1'], function () use ($router){
        require_once 'v1.php';
    });
    $router->group(['namespace'=>'Api\V2','prefix'=>'v2'], function () use ($router){
        require_once 'v2.php';
    });
    $router->group(['namespace'=>'Api\V3','prefix'=>'v3'], function () use ($router){
        require_once 'v3.php';
    });
});

//$router->version(['v1', 'v2','v3'], function ($router) {
//    $router->group(['namespace' => 'App\Http\Controllers\Api\V2', 'prefix' => 'v1'], function () use ($router) {
////        require base_path('routes/v2.php');
//    });
//    $router->group(['namespace' => 'App\Http\Controllers\Api\V2', 'prefix' => 'v2'], function () use ($router) {
//        require base_path('routes/v2.php');
//    });
//    $router->group(['namespace' => 'App\Http\Controllers\Api\V3', 'prefix' => 'v3'], function () use ($router) {
//        require base_path('routes/v3.php');
//    });
//});
