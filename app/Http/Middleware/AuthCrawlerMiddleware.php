<?php

namespace App\Http\Middleware;

use Closure;

class AuthCrawlerMiddleware
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
        return $next($request);
        if($request->input("access_token")==null){
            return response()->json(["status_code"=>401,"message"=>"Error!"],401);
        }
        try{
            $user_info =   JWT::decode($request->input("access_token"),config("app.secret_access_token"),['HS256']);
            if($user_info->data->crawler!==true){
                return response()->json(["status_code"=>401,"message"=>'Not auth','error'=>'middleware'],401);
            }
            return $next($request);
        }catch (\Exception $e){
            return response()->json(["status_code"=>401,"message"=>$e->getMessage(),'error'=>'middleware'],401);
        }

//        return $next($request);
    }
}
