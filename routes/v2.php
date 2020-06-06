<?php


$router->get('/', function () {
    return [
        'status' => 200,
        'message' => 'Api flatfy version 2'
    ];
});

//$router->get('slug','BaseController@CheckSlug');

//$router->post('login', 'AuthController@checkLogin');
//
//$router->post('login','AuthController@login');
//$router->get('locations/detect', 'LocationController@Detect');
//$router->get('categories/detect', 'CategoryController@Detect');
//
//$router->post('user-customer/add-user-form-template', 'UserCustomerController@addUserFromTemplate');
//
//$router->resource('categories', 'CategoryController', ['only' => ['show', 'index']]);
//
//$router->resource('projects', 'ProjectController', ['only' => ['index', 'show']]);
//
//$router->resource('classifieds', 'ClassifiedController', ['only' => ['index', 'show']]);
//
//$router->resource('investors', 'InvestorController', ['only' => ['index', 'show']]);
//
//$router->resource('accessToken', 'AccessTokenController', ['only' => ['show', 'store']]);
//
//$router->resource('projects-compare', 'ProjectCompareController', ['only' => ['index', 'show']]);
//
//$router->get('rewrites/bread-crumb', 'RewriteController@getBreadCrumb');
//
//$router->resource('rewrites', 'RewriteController', ['only' => ['index', 'show']]);
//
//$router->resource('locations', 'LocationController', ['only' => ['index', 'show']]);
//
//$router->resource('news', 'NewsController', ['only' => ['index', 'show']]);
//
//$router->get('console1', 'ConsoleController@IndexAddress');
//$router->get('console2', 'ConsoleController@CreateDocument');
//$router->get('console3', 'ConsoleController@mapAllLocation');
//$router->get('console4', 'ConsoleController@testMapLocation');
//$router->get('console5', 'ConsoleController@IndexLocation');
//$router->get('console6', 'ConsoleController@IndexProjects');
//$router->get('console7', 'ConsoleController@IndexClassifieds');
//$router->get('console8', 'ConsoleController@UpdateGeoLocation');
//$router->get('console9', 'ConsoleController@IndexGeoLocation');
//
//$router->get('reactnative1', 'ReactNativeController@loginFacebook');
//
//$router->group(['middleware'=>'auth'],function($router){
//
//    $router->get('user-themes/check-slug','UserThemeController@checkSlug');
//
//    $router->resource('users', 'UserController', ['only' => ['index', 'show', 'store','update']]);
//
//    $router->resource('classifieds', 'ClassifiedController', ['only' => ['store', 'destroy','update']]);
//
//    $router->resource('auto-save','AutoSaveController',['only' => ['store', 'destroy','index']]);
//
//    $router->resource('suggest-classified','SuggestClassifiedPost',['only' => ['store', 'destroy','index']]);
//
//    $router->resource('classifieds-vip', 'ClassifiedVipController',['only' => ['store', 'destroy','update']]);
//
//    $router->resource('money', 'MoneyController',['only' => ['index','show']]);
//
//    $router->resource('spend-history', 'UserSpendHistoryController',['only' => ['index','show','store']]);
//
//    $router->post('bao-kim/add-money','BaoKimController@addMoney');
//
//    $router->post('payment/request','PaymentController@request');
//
//    $router->resource('user-customer', 'UserCustomerController',['only' => ['index','show','store','update','destroy']]);
//
//    $router->resource('user-themes', 'UserThemeController',['only' => ['index','show','update','store','destroy']]);
//
//    $router->resource('queue-payment', 'QueuePaymentController',['only' => ['index','show','destroy','store']]);
//
//    $router->resource('classified-vip-show', 'ClassifiedVipShowController',['only' => ['index','show','update','store']]);
//
//    $router->resource('baokim_payment_notification', 'BaoKimPaymentNotificationController',['only' => ['index','show']]);
//
//    $router->resource('user-contacts', 'UserContactController',['only' => ['show']]);
//
//    $router->post('momo-payment','PaymentController@momoCreatePayment');
//
//    $router->post('user-back-payment','PaymentController@returnAddMoney');
//
//    $router->resource('users-favorite', 'UserFavoriteController',['only' => ['index','destroy','store']]);
//
//    $router->resource('user-contacts-buy', 'UserContactBuyController',['only' => ['index','store']]);
//
//});
//
//
//$router->group(['middleware'=>'auth_admin'],function($router){
//
////    $router->resource('configuration_app', 'ConfigurationAppController',['only' => ['store','destroy','update']]);
//    $router->resource('classified-vip-config', 'ClassifiedVipConfigController',['only' => ['store','destroy','update']]);
//
//    $router->resource('classified-vip-show', 'ClassifiedVipShowController',['only' => ['destroy']]);
//
//    $router->resource('money', 'MoneyController',['only' => ['update']]);
//
//    $router->resource('user-contacts', 'UserContactController',['only' => ['destroy']]);
//
//    $router->resource('jobs', 'JobController',['only' => ['index']]);
//
//    $router->resource('configuration-comment', 'ConfigurationCommentController',['only' => ['store']]);
//
//    $router->resource('momo-notify','MomoNotifyController',['only' => ['index','show']]);
//
//});
//
//$router->group(['middleware'=>'auth_dev'],function($router){
//
//    $router->resource('theme-sources', 'ThemeSourceController',['only' => ['index','show','store','destroy','update']]);
//
//    $router->post('themes/{id}','ThemeController@manageFile');
//
//    $router->resource('themes', 'ThemeController',['only' => ['store','update','destroy']]);
//
//    $router->resource('user-notify', 'UserNotifyController',['only' => ['index','store','update','show']]);
//
//    $router->resource('classified-log', 'ClassifiedLogController',['only' => ['index','show']]);
//
//});
//
//$router->group(['middleware'=>['auth_crawler']],function($router){
//    $router->resource('rewrites', 'RewriteController', ['only' => ['store']]);
//});
//
//$router->group(['middleware'=>'auth_web'],function($router){
//
////    $router->resource('users-favorite', 'UserFavoriteController',['only' => ['index','destroy','store']]);
//
//});
//
////$router->resource('configuration_app', 'ConfigurationAppController',['only' => ['index','show']]);
//
//$router->resource('classifieds-vip', 'ClassifiedVipController',['only' => ['show', 'index']]);
//
//$router->resource('attributes_v2', 'AttributesV2Controller',['only' => ['index','show']]);
//
//$router->resource('baokim_payment_notification', 'BaoKimPaymentNotificationController',['only' => ['store']]);
//
////$router->get('bao-kim/get-list-bank','BaoKimController@getSellerInfo');
//$router->get('payment/bank_list','PaymentController@bank_list');
//
//$router->post('cancel-payment','BaoKimController@cancelPayment');
//
//$router->get('download-log/{filename}','BaoKimController@downloadLogBaoKim');
//
//$router->get('file-excel-sample.xlxs','UserCustomerController@downloadFileExcelSample');
//
//$router->get('address/detect', 'AddressController@Detect');
//
//$router->get('address/{id}', 'AddressController@show');
//
//$router->get('address', 'AddressController@index');
//
//$router->resource('themes', 'ThemeController',['only' => ['index','show']]);
//
//$router->resource('classified-vip-config', 'ClassifiedVipConfigController',['only' => ['index','show']]);
//
//$router->get('momo-return','PaymentController@momoReturn');
//
//$router->post('momo-notify','PaymentController@momoNotify');
//
////$router->get('momo-create-qr-code','PaymentController@createQrCodeMomo');
//
//$router->resource('user-contacts', 'UserContactController',['only' => ['store']]);
//
//$router->delete('classifieds/claw/{id}','ClassifiedController@deleteClassifiedClaw');
//
////$router->resource('classified-log', 'ClassifiedLogController',['only' => ['index','show']]);
//
//$router->resource('configuration', 'ConfigurationController',['only' => ['index']]);
//
////$router->resource('classified-log', 'ClassifiedLogController',['only' => ['index','show']]);
//
//$router->resource('configuration-comment', 'ConfigurationCommentController',['only' => ['index']]);
//
//$router->resource('comments', 'CommentController',['only' => ['index','show','store']]);
//
////$router->post('post_cache_redis', 'ClassifiedController@postCacheRedis');
//$router->get('get_cache_redis', 'ClassifiedController@getCacheRedis');
//
//$router->resource('user-contacts', 'UserContactController',['only' => ['index']]);

//$router->resource('address-review', 'AdressReviewController',['only' => ['index','show','store']]);
//$router->get('autocomplete-search','RewriteController@autocompleteSearch');

//$router->get('delete_cla','ClassifiedController@deleteCla');
//
//$router->delete('delete_cla','ClassifiedController@deleteCla');


$router->post('login', 'AuthController@login');
$router->get('locations/detect', 'LocationController@Detect');
$router->get('categories/detect', 'CategoryController@Detect');

$router->post('user-customer/add-user-form-template', 'UserCustomerController@addUserFromTemplate');

//category
$router->get('categories', 'CategoryController@index');

$router->get('categories/{id}', 'CategoryController@show');

//project
$router->get('projects', 'ProjectController@index');

$router->get('projects/{id}', 'ProjectController@show');

//classifieds
$router->get('classifieds', 'ClassifiedController@index');

$router->get('classifieds/{id}', 'ClassifiedController@show');

//investors
$router->get('investors', 'InvestorController@index');

$router->get('investors/{id}', 'InvestorController@show');
//accessToken
$router->get('accessToken/{id}', 'AccessTokenController@show');

$router->post('accessToken', 'AccessTokenController@store');

//projects-compare
$router->get('projects-compare', 'ProjectCompareController@index');

$router->get('projects-compare/{id}', 'ProjectCompareController@show');

//rewrites
$router->get('rewrites/bread-crumb', 'RewriteController@getBreadCrumb');

$router->get('rewrites', 'RewriteController@index');

$router->get('rewrites/{id}', 'RewriteController@show');

//locations
$router->get('locations', 'LocationController@index');

$router->get('locations/{id}', 'LocationController@show');

//news
$router->get('news', 'NewsController@index');

$router->get('news/{id}', 'NewsController@show');

//console1
$router->get('console1', 'ConsoleController@IndexAddress');
$router->get('console2', 'ConsoleController@CreateDocument');
$router->get('console3', 'ConsoleController@mapAllLocation');
$router->get('console4', 'ConsoleController@testMapLocation');
$router->get('console5', 'ConsoleController@IndexLocation');
$router->get('console6', 'ConsoleController@IndexProjects');
$router->get('console7', 'ConsoleController@IndexClassifieds');
$router->get('console8', 'ConsoleController@UpdateGeoLocation');
$router->get('console9', 'ConsoleController@IndexGeoLocation');


//reactnative1
$router->get('reactnative1', 'ReactNativeController@loginFacebook');

$router->group(['middleware' => 'auth'], function ($router) {

    $router->get('user-themes/check-slug', 'UserThemeController@checkSlug');

    //users
    $router->get('users', 'UserController@index');

    $router->get('users/{id}', 'UserController@show');

    $router->put('users/{id}', 'UserController@update');

    $router->post('users', 'UserController@store');

    //classifieds
    $router->put('classifieds/{id}', 'ClassifiedController@update');

    $router->delete('classifieds/{id}', 'ClassifiedController@destroy');

    $router->post('classifieds', 'ClassifiedController@store');

    //auto-save
    $router->get('auto-save', 'AutoSaveController@index');

    $router->delete('auto-save/{id}', 'AutoSaveController@destroy');

    $router->post('auto-save', 'AutoSaveController@store');

    //suggest-classified
    $router->get('suggest-classified', 'SuggestClassifiedPost@index');

    $router->delete('suggest-classified/{id}', 'SuggestClassifiedPost@destroy');

    $router->post('suggest-classified', 'SuggestClassifiedPost@store');

    //classifieds-vip
    $router->put('classifieds-vip/{id}', 'ClassifiedVipController@update');

    $router->delete('classifieds-vip/{id}', 'ClassifiedVipController@destroy');

    $router->post('classifieds-vip', 'ClassifiedVipController@store');

    //money
    $router->get('money', 'MoneyController@index');

    $router->get('money/{id}', 'MoneyController@show');

    //spend-history
    $router->get('spend-history', 'UserSpendHistoryController@index');

    $router->get('spend-history/{id}', 'UserSpendHistoryController@show');

    $router->post('spend-history', 'UserSpendHistoryController@store');

    $router->post('bao-kim/add-money', 'BaoKimController@addMoney');

    $router->post('payment/request', 'PaymentController@request');

    //user-customer
    $router->get('user-customer', 'UserCustomerController@index');

    $router->get('user-customer/{id}', 'UserCustomerController@show');

    $router->put('user-customer/{id}', 'UserCustomerController@update');

    $router->post('user-customer', 'UserCustomerController@store');

    $router->delete('user-themes/{id}', 'UserThemeController@destroy');

    //queue-payment
    $router->get('queue-payment', 'QueuePaymentController@index');

    $router->get('queue-payment/{id}', 'QueuePaymentController@show');

    $router->post('queue-payment', 'QueuePaymentController@store');

    $router->delete('queue-payment/{id}', 'QueuePaymentController@destroy');

    //classified-vip-show
    $router->get('classified-vip-show', 'ClassifiedVipShowController@index');

    $router->get('classified-vip-show/{id}', 'ClassifiedVipShowController@show');

    $router->put('classified-vip-show/{id}', 'ClassifiedVipShowController@update');

    $router->post('classified-vip-show', 'ClassifiedVipShowController@store');

    //baokim_payment_notification
    $router->get('baokim_payment_notification', 'BaoKimPaymentNotificationController@index');

    $router->get('baokim_payment_notification/{id}', 'BaoKimPaymentNotificationController@show');

    //user-contacts
    $router->get('user-contacts/{id}', 'UserContactController@show');

    $router->post('momo-payment', 'PaymentController@momoCreatePayment');

    $router->post('user-back-payment', 'PaymentController@returnAddMoney');

    //users-favorite
    $router->get('users-favorite', 'UserFavoriteController@index');

    $router->post('users-favorite', 'UserFavoriteController@store');

    $router->delete('users-favorite/{id}', 'UserFavoriteController@destroy');

    //user-contacts-buy
    $router->get('user-contacts-buy', 'UserContactBuyController@index');

    $router->post('user-contacts-buy', 'UserContactBuyController@store');

});


$router->group(['middleware' => 'auth_admin'], function ($router) {

//    $router->resource('configuration_app', 'ConfigurationAppController',['only' => ['store','destroy','update']]);

    //classified-vip-config
    $router->put('classified-vip-config/{id}', 'ClassifiedVipConfigController@update');

    $router->post('classified-vip-config', 'ClassifiedVipConfigController@store');

    $router->delete('classified-vip-config/{id}', 'ClassifiedVipConfigController@destroy');

    //classified-vip-show
    $router->delete('classified-vip-show/{id}', 'ClassifiedVipShowController@destroy');

    //money
    $router->put('money/{id}', 'MoneyController@update');

    //user-contacts
    $router->delete('user-contacts', 'UserContactController@destroy');

    //jobs
    $router->get('jobs', 'JobController@index');

    //configuration-comment
    $router->post('configuration-comment', 'ConfigurationCommentController@store');

    //momo-notify
    $router->get('momo-notify', 'MomoNotifyController@index');

    $router->get('momo-notify/{id}', 'MomoNotifyController@show');

});

$router->group(['middleware' => 'auth_dev'], function ($router) {

    //theme-sources
    $router->get('theme-sources', 'ThemeSourceController@index');

    $router->get('theme-sources/{id}', 'ThemeSourceController@show');

    $router->post('theme-sources', 'ThemeSourceController@store');

    $router->put('theme-sources/{id}', 'ThemeSourceController@update');

    $router->delete('theme-sources/{id}', 'ThemeSourceController@destroy');

    //themes
    $router->post('themes/{id}', 'ThemeController@manageFile');

    $router->put('themes/{id}', 'ThemeController@update');

    $router->post('themes', 'ThemeController@store');

    $router->delete('themes/{id}', 'ThemeController@destroy');

    //user-notify
    $router->get('user-notify', 'UserNotifyController@index');

    $router->get('user-notify/{id}', 'UserNotifyController@show');

    $router->put('user-notify/{id}', 'UserNotifyController@update');

    $router->post('user-notify', 'UserNotifyController@store');

    //classified-log
    $router->get('classified-log', 'ClassifiedLogController@index', ['only' => ['', 'show']]);

    $router->get('classified-log/{id}', 'ClassifiedLogController', ['only' => ['index', 'show']]);

});

$router->group(['middleware' => ['auth_crawler']], function ($router) {
    $router->post('rewrites', 'RewriteController@store');
});

$router->group(['middleware' => 'auth_web'], function ($router) {

//    $router->resource('users-favorite', 'UserFavoriteController',['only' => ['index','destroy','store']]);

});

//$router->resource('configuration_app', 'ConfigurationAppController',['only' => ['index','show']]);

//classifieds-vip
$router->get('classifieds-vip', 'ClassifiedVipController@index');

$router->get('classifieds-vip/{id}', 'ClassifiedVipController');

//attributes_v2
$router->get('attributes_v2', 'AttributesV2Controller@index');

$router->get('attributes_v2/{id}', 'AttributesV2Controller@show');

//baokim_payment_notification
$router->post('baokim_payment_notification', 'BaoKimPaymentNotificationController@store');

//$router->get('bao-kim/get-list-bank','BaoKimController@getSellerInfo');
$router->get('payment/bank_list', 'PaymentController@bank_list');

$router->post('cancel-payment', 'BaoKimController@cancelPayment');

$router->get('download-log/{filename}', 'BaoKimController@downloadLogBaoKim');

$router->get('file-excel-sample.xlxs', 'UserCustomerController@downloadFileExcelSample');

$router->get('address/detect', 'AddressController@Detect');

$router->get('address/{id}', 'AddressController@show');

$router->get('address', 'AddressController@index');

//themes
$router->get('themes', 'ThemeController@index');

$router->get('themes/{id}', 'ThemeController@show');

//classified-vip-config
$router->get('classified-vip-config', 'ClassifiedVipConfigController@index');

$router->get('classified-vip-config/{id}', 'ClassifiedVipConfigController@show');

$router->get('momo-return', 'PaymentController@momoReturn');

$router->post('momo-notify', 'PaymentController@momoNotify');

//$router->get('momo-create-qr-code','PaymentController@createQrCodeMomo');

//user-contacts
$router->post('user-contacts', 'UserContactController@store');

$router->delete('classifieds/claw/{id}', 'ClassifiedController@deleteClassifiedClaw');

//$router->resource('classified-log', 'ClassifiedLogController',['only' => ['index','show']]);

//configuration
$router->get('configuration', 'ConfigurationController@index');

//$router->resource('classified-log', 'ClassifiedLogController',['only' => ['index','show']]);

//configuration-comment
$router->get('configuration-comment', 'ConfigurationCommentController', ['only' => ['index']]);

//comments
$router->get('comments', 'CommentController@index');

$router->get('comments/{id}', 'CommentController@show');

$router->post('comments', 'CommentController@store');

//$router->post('post_cache_redis', 'ClassifiedController@postCacheRedis');
$router->get('get_cache_redis', 'ClassifiedController@getCacheRedis');

//user-contacts
$router->get('user-contacts', 'UserContactController@index');
