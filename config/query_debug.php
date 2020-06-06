<?php
/**
 * Created by Lê Đình Toản.
 * User: dinhtoan1905@gmail.com
 * Date: 8/9/2018
 * Time: 11:00 AM
 */
return [
    'enabled' => true,
    'log_query' =>env('APP_QUERY_LOG',false),
    'query_path' => env('QUERY_LOGGER_PATH',storage_path('logs/query_logger.log')),
    'slow_path' => env('QUERY_SLOW_PATH',storage_path('logs/slow_log.log')),
    'time_slow' => floatval(env("QUERY_SLOW_TIME",0.05))
];