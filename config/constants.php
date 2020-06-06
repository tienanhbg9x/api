<?php
//CẤU HÌNH TÀI KHOẢN (Configure account)
define('BAOKIM_EMAIL_BUSINESS',env('BAOKIM_EMAIL_BUSINESS', ''));//Email Bảo kim
define('BAOKIM_MERCHANT_ID',env('BAOKIM_MERCHANT_ID', ''));                // Mã website tích hợp
define('BAOKIM_SECURE_PASS',env('BAOKIM_SECURE_PASS', ''));   // Mật khẩu

// Cấu hình tài khoản tích hợp
define('BAOKIM_API_USER',env('BAOKIM_API_USER', ''));  //API USER
//define('API_PWD','2q1vYc8pJ57bAW9VjCnXH1htk3GOK');       //API PASSWORD
define('BAOKIM_API_PWD',env('BAOKIM_API_PWD', ''));       //API PASSWORD
define('BAOKIM_PRIVATE_KEY_BAOKIM',file_get_contents(dirname(__FILE__) . "/../ssl/sosanhnha.com.baokim.key"));

define('BAOKIM_API_SELLER_INFO','/payment/rest/payment_pro_api/get_seller_info');
define('BAOKIM_API_PAY_BY_CARD','/payment/rest/payment_pro_api/pay_by_card');
define('BAOKIM_API_PAYMENT','/payment/order/version11');
define('BAOKIM_API_VERIFY','/bpn/verify');

define('BAOKIM_URL','https://www.baokim.vn');
//define('BAOKIM_URL','http://baokim.dev');
//define('BAOKIM_URL','http://kiemthu.baokim.vn');

//Phương thức thanh toán bằng thẻ nội địa
define('BAOKIM_PAYMENT_METHOD_TYPE_LOCAL_CARD', 1);
//Phương thức thanh toán bằng thẻ tín dụng quốc tế
define('BAOKIM_PAYMENT_METHOD_TYPE_CREDIT_CARD', 2);
//Dịch vụ chuyển khoản online của các ngân hàng
define('BAOKIM_PAYMENT_METHOD_TYPE_INTERNET_BANKING', 3);
//Dịch vụ chuyển khoản ATM
define('BAOKIM_PAYMENT_METHOD_TYPE_ATM_TRANSFER', 4);
//Dịch vụ chuyển khoản truyền thống giữa các ngân hàng
define('BAOKIM_PAYMENT_METHOD_TYPE_BANK_TRANSFER', 5);

define('BAOKIM_V4_URL','https://api.baokim.vn/payment/api/v4/');
define('BAOKIM_V4_API_KEY',env('BAOKIM_V4_API_KEY',''));
define('BAOKIM_V4_SECRET_KEY',env('BAOKIM_V4_SECRET_KEY',''));
define('SLACK_PAYMENT','https://hooks.slack.com/services/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');