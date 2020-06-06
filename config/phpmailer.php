<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 10/12/2018
 * Time: 09:40
 */
return [
    'host'=>env('PHPMAILER_HOST','smtp.gmail.com'),
    'smtp_auth'=>env('PHPMAILER_SMTP_AUTH',true),
    'user_name'=>env('PHPMAILER_USERNAME','ngocnm@vatgia.com'),
    'password'=>env('PHPMAILER_PASSWORD','vatgia'),
    'smtp_secure'=>env('PHPMAILER_SMTP_SECURE','ssl'),
    'port'=>env('PHPMAILER_POST',465),
];