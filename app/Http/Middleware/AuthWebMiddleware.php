<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Exception;

class AuthWebMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->input("access_token") == null) {
            return response()->json(["status_code" => 401, "message" => "Not found access_token Error!(middleware)"], 401);
        }
        try {
            JWT::decode($request->input("access_token"), config("app.secret_form_template"), ['HS256']);
            return $next($request);
        } catch (Exception $e) {
            return response()->json(["status_code" => 401, "message" => $e->getMessage(), 'error' => 'middleware(auth web)'], 401);
        }
    }
}
