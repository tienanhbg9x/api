<?php

define('LUMEN_START', microtime(true));
ob_start();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| First we need to get an application instance. This creates an instance
| of the application / container and bootstraps the application so it
| is ready to receive HTTP / Console requests from the environment.
|
*/

$app = require __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$app->run();

$executionTime = microtime(true) - LUMEN_START;
if(env('PAGE_SLOW_TIME',0.2)<=$executionTime){
    $page_slow_log = [
        'domain'=>isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:null,
        'type'=>'page',
        'date'=>date('m-d-Y H:i:s'),
        'time'=>number_format($executionTime,5,".",""),
        'page'=>(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}",
        'user_agent'=> !empty($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'null',
        'IP'=>!empty($_SERVER['HTTP_CLIENT_IP'])?$_SERVER['HTTP_CLIENT_IP']:(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'])
    ];
    @file_put_contents(env('PAGE_SLOW_PATH',storage_path('logs/slow_log.log')),json_encode($page_slow_log,JSON_UNESCAPED_UNICODE,JSON_PRETTY_PRINT) . "\n",FILE_APPEND);
}
