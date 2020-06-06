<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

require_once __DIR__.'/../config/constants.php';

//$app->configure('constant');

$app->instance('path.config', app()->basePath() . DIRECTORY_SEPARATOR . 'config');
$app->instance('path.storage', app()->basePath() . DIRECTORY_SEPARATOR . 'storage');
$app->instance('path.public', app()->basePath() . DIRECTORY_SEPARATOR . 'public');

 $app->withFacades();

 $app->withEloquent();

$app->configure('filesystems');
$app->configure('sphinx');
$app->configure('alias_database');
$app->configure('alias_database_v2');
$app->configure('query_debug');
$app->configure('app');
$app->configure('baokim_config');
$app->configure('phpmailer');
$app->configure('queue');
$app->configure('momo');

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$middleware = [
    App\Http\Middleware\CorsMiddleware::class
];

if(env('APP_DEBUG')){
    $middleware[] =  App\Http\Middleware\DebugbarMiddleware::class;
    $app->configure('debugbar');
    $app->register(Laravel\Tinker\TinkerServiceProvider::class);
    $app->register(Barryvdh\Debugbar\LumenServiceProvider::class);
}

$app->middleware($middleware);

$app->routeMiddleware([
    'cors' => App\Http\Middleware\CorsMiddleware::class,
    'auth' => App\Http\Middleware\AuthApiMiddleware::class,
    'auth_admin' => App\Http\Middleware\AuthAdminApiMiddleware::class,
    'auth_dev' => App\Http\Middleware\AuthDeveloperApiMiddleware::class,
    'auth_crawler' => App\Http\Middleware\AuthCrawlerMiddleware::class,
    'auth_web' => App\Http\Middleware\AuthWebMiddleware::class
]);


define('NOTIFICATION_TYPE_ACTION_POST',1);
define('NOTIFICATION_TYPE_ACTION_COMMENT',2);
define('NOTIFICATION_TYPE_ACTION_LIKE',3);
define('NOTIFICATION_TYPE_OBJECT_CLASSIFIED',1);
define('NOTIFICATION_TYPE_OBJECT_PROJECT',2);
define('NOTIFICATION_TYPE_OBJECT_NEWS',3);

// $app->middleware([
//     App\Http\Middleware\ExampleMiddleware::class
// ]);

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

$app->register(App\Providers\SphinxServiceProvider::class);
//$app->register(Barryvdh\Cors\ServiceProvider::class);
//$app->register(Dingo\Api\Provider\LumenServiceProvider::class);
$app->register(App\Providers\ElasticsearchProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(Illuminate\Filesystem\FilesystemServiceProvider::class);
$app->register(Anhskohbo\NoCaptcha\NoCaptchaServiceProvider::class);

// $app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
