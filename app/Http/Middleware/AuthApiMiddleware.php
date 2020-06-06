<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;

class AuthApiMiddleware
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
        if($request->input("access_token")==null){
            return response()->json(["status_code"=>401,"message"=>"Not auth!"],401);
        }
        try{
            // dd($request->input("access_token"));
            // dd(config("app.secret_access_token"));
             JWT::decode($request->input("access_token"),config("app.secret_access_token"),['HS256']);
            return $next($request);
        }catch (\Exception $e){
            return response()->json(["status_code"=>401,"message"=>$e->getMessage(),'error'=>'middleware'],401);
        }
        $response = $next($request);
        return $response;
    }
}
