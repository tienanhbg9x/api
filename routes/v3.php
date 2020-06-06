<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 21/10/2019
 * Time: 21:37
 */


$router->get('/', function () {
    return [
        'status' => 200,
        'message' => 'Api version 3'
    ];
});

$router->group(['prefix'=>'payment'],function() use ($router){

    $router->group(['prefix'=>'bao-kim'],function() use ($router){

        $router->get('payment-methods',['middleware' => 'auth','uses'=>'PaymentController@getPaymentMethodBaoKim']);

        $router->post('send-order', ['middleware' => 'auth','uses'=>'PaymentController@createOrderBaoKim']);

        $router->post('webhook-notification','PaymentController@webhookNotificationBaoKim');

    });

    $router->get('check-order',['middleware' => 'auth','uses'=>'PaymentController@orderIdCheck']);

});

//classified

$router->get('classifieds','ClassifiedController@index');