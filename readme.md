# Start project with command

php -S localhost:8000 -t public

# Lumen PHP Framework

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://poser.pugx.org/laravel/lumen-framework/d/total.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://poser.pugx.org/laravel/lumen-framework/v/stable.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Unstable Version](https://poser.pugx.org/laravel/lumen-framework/v/unstable.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://poser.pugx.org/laravel/lumen-framework/license.svg)](https://packagist.org/packages/laravel/lumen-framework)

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

## Official Documentation

Documentation for the framework can be found on the [Lumen website](http://lumen.laravel.com/docs).

## Security Vulnerabilities

If you discover a security vulnerability within Lumen, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

The Lumen framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
Command
Tạo controller
php artisan resource:create photo --version_route=v3 --controller=PhotoController
php artisan resource:create category --version_route=v2 --controller=CategoryController
php artisan resource:create accessToken --version_route=v2 --controller=AccessTokenController
./artisan api:generate --router="dingo" --routePrefix="v2" --header="isdoc:1"

./artisan api:generate --router="dingo" --routePrefix="v2" --header="isdoc:1"

Automatically create resource api, model,router.


`php artisan resource:create {route_name} --version_route={default} --controller={null}`


https://developers.google.com/analytics/devguides/reporting/core/v3/quickstart/service-php
=======
composer dump-autoload --optimize --no-dev --classmap-authoritative


https://github.com/gordalina/cachetool

https://maps.googleapis.com/maps/api/place/details/json?placeid=ChIJK7QjP5usNTERmyNtoKoB-kk&key=AIzaSyDv6T_qZKzpWtggXdRgMAj76zRxthycM_k&hl=vi

https://maps.googleapis.com/maps/api/place/autocomplete/json?input=royal%20city%20thanh%20xu%C3%A2n%20h%C3%A0%20n%E1%BB%99i&key=AIzaSyDv6T_qZKzpWtggXdRgMAj76zRxthycM_k&sessiontoken=1234567890&num=15&hl=vi

https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input=Museum%20of%20Contemporary%20Art%20Australia&inputtype=textquery&fields=photos,formatted_address,name,rating,opening_hours,geometry&key=AIzaSyDv6T_qZKzpWtggXdRgMAj76zRxthycM_k

https://maps.googleapis.com/maps/api/place/textsearch/json?query=123+main+street&fields=formatted_address,geometry,name,photos,rating&key=AIzaSyDv6T_qZKzpWtggXdRgMAj76zRxthycM_k

https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=20.9809233,105.8451305&radius=1500&num=100&key=AIzaSyDv6T_qZKzpWtggXdRgMAj76zRxthycM_k

https://vi.wikipedia.org/api/rest_v1/page/summary/Mazda

https://vi.wikipedia.org/w/api.php?action=opensearch&format=json&formatversion=2&search=sa&namespace=0&limit=10&suggest=true
https://roads.googleapis.com/v1/snapToRoads?path=-35.27801,149.12958|-35.28032,149.12907|-35.28099,149.12929|-35.28144,149.12984|-35.28194,149.13003|-35.28282,149.12956|-35.28302,149.12881|-35.28473,149.12836&interpolate=true&key=AIzaSyC5Uu54VnlfZdB6lt0sjnT2_djzrOwMTrk
https://github.com/githubharald/SimpleHTR

https://maps.googleapis.com/maps/api/directions/json?origin=102%20th%C3%A1i%20th%E1%BB%8Bnh,%20%C4%91%E1%BB%91ng%20%C4%91a,%20h%C3%A0%20n%E1%BB%99i,%20vietnam&destination=1%20th%C3%A1i%20th%E1%BB%8Bnh,%20%C4%91%E1%BB%91ng%20%C4%91a,%20h%C3%A0%20n%E1%BB%99i,%20vi%E1%BB%87t%20nam&key=AIzaSyC5Uu54VnlfZdB6lt0sjnT2_djzrOwMTrk
