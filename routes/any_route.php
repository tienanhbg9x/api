<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 11/08/2018
 * Time: 09:59
 */

$router->get('/{route:.*}/', function () {
    return [
        'status' => 404,
        'message' => 'Not found url'
    ];
});
$router->post('/{route:.*}/', function () {
    return [
        'status' => 404,
        'message' => 'Not found url'
    ];
});
$router->put('/{route:.*}/', function () {
    return [
        'status' => 404,
        'message' => 'Not found url'
    ];
});
$router->patch('/{route:.*}/', function () {
    return [
        'status' => 404,
        'message' => 'Not found url'
    ];
});
$router->delete('/{route:.*}/', function () {
    return [
        'status' => 404,
        'message' => 'Not found url'
    ];
});