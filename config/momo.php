<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 03/04/2019
 * Time: 21:15
 */


return [
    'endpoint' => env('MOMO_END_POINT',"https://payment.momo.vn/gw_payment/transactionProcessor"),
    'payUrl'=>env('MOMO_PAY_URL','https://payment.momo.vn/gw_payment/payment/qr'),
    'partnerCode' => env('MOMO_PARTNER_CODE',"MOMOLCQL20190320"),
    'accessKey' => env('MOMO_ACCESS_KEY',"csqtmJy05xNYTAKa"),
    'serectkey' => env('MOMO_SERECT_KEY',"D4er2AObO7Zyzav8DMpwlw45AmFzXXaA"),
    'returnUrl' => "https://sosanhnha.com/quanly/user/profile",
    'notifyurl' => "https://sosanhnha.com/api/v2/momo-notify",
    'requestType' => env('MOMO_REQUEST_TYPE',"captureMoMoWallet"),
    'extraData' => env('MOMO_EXTRA_DATA',"merchantName=;merchantId=")
];

//return [
//    'endpoint' => "https://test-payment.momo.vn/gw_payment/transactionProcessor",
//    'partnerCode' => "MOMOLCQL20190320",
//    'accessKey' => "m7BRYoHHuDIqKuSW",
//    'serectkey' => "JE6u02vqIvResjFQX9hmnqRMA9ByOoMH",
//    'returnUrl' => "https://sosanhnha.com/quanly/user/profile",
//    'notifyurl' => "https://sosanhnha.com/api/v2/momo-notify",
//    'requestType' => "captureMoMoWallet",
//    'extraData' => "merchantName=;merchantId="
//];

//return [
//    'endpoint' => env('MOMO_END_POINT',"https://test-payment.momo.vn/gw_payment/transactionProcessor"),
//    'partnerCode' => env('MOMO_PARTNER_CODE',"MOMO0HGO20180417"),
//    'accessKey' => env('MOMO_ACCESS_KEY',"E8HZuQRy2RsjVtZp"),
//    'serectkey' => env('MOMO_SERECT_KEY',"fj00YKnJhmYqahaFWUgkg75saNTzMrbO"),
//    'returnUrl' => "https://sosanhnha.com/quanly/user/profile",
//    'notifyurl' => "https://www.sosanhnha.com/api/v2/momo-notify",
//    'requestType' => env('MOMO_REQUEST_TYPE',"captureMoMoWallet"),
//    'extraData' => env('MOMO_EXTRA_DATA',"merchantName=;merchantId=")
//];