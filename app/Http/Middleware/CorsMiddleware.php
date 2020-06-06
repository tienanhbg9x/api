<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Accept, Authorization, X-Requested-With, Application'
        ];
        if ($request->isMethod('OPTIONS'))
        {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Max-Age: 86400');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With, Application');
//        $access_token = $request->input("access_token","");
//        App::singleton('access_token', function() use ($access_token){
//            return $access_token;
//        });
        return $next($request);
    }

}
