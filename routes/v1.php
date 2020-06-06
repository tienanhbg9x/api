<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 31/07/2018
 * Time: 14:55
 */

$router->get('/', function () {
    return [
        'status' => 200,
        'message' => 'Api flatfy version 1'
    ];
});

$router->get('categories', 'Controller@getCategories');

$router->get('cities', 'Controller@getCity');

$router->group(['prefix' => 'classified'], function () use ($router) {

    $router->post('create', 'ClassifiedController@createClassified');

});

$router->group(['prefix' => 'home'], function () use ($router) {

    $router->get('search-location/{type}', 'HomeController@searchLocation');

    $router->get('last-classified', 'HomeController@getLastClassified');

    $router->get('categories','HomeController@getCategories');

    $router->get('link-map-image','HomeController@getImageMapLocation');

});

$router->group(['prefix' => 'category'], function () use ($router) {
    $router->get('{slug}', 'CategoryController@getListClassified');
    $router->get('related-classifieds/{slug}', 'CategoryController@getRelatedClassifieds');
});


$router->get('search', 'SearchController@searchClassified');

$router->get('redirect/{link}', 'RedirectController@redirectLink');