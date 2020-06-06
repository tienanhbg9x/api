<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 21/10/2019
 * Time: 18:00
 */

namespace App\Http\Middleware;

use Closure;

class DebugbarMiddleware
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

        $response = $next($request);
        if (app()->bound('debugbar') && app('debugbar')->isEnabled()) {
            $data = json_decode($response->getContent());
//            dd($data);
            if(!empty($data)){
                $data->_debugbar = app('debugbar')->getData();

                $response->setContent(json_encode($data));
            }

        }

        return $response;

    }

}