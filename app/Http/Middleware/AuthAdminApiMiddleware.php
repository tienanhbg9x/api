<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;

class AuthAdminApiMiddleware
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
            return response()->json(["status_code"=>401,"message"=>"Error!"],401);
        }
        try{
            $user_info =   JWT::decode($request->input("access_token"),config("app.secret_access_token"),['HS256']);
            if($user_info->data->rol!="admin"){
                return response()->json(["status_code"=>401,"message"=>'Not auth','error'=>'middleware'],401);
            }
            return $next($request);
        }catch (\Exception $e){
            return response()->json(["status_code"=>401,"message"=>$e->getMessage(),'error'=>'middleware'],401);
        }

//        return $next($request);
    }
}
