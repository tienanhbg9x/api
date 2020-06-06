<?php

namespace App\Providers;

use App\Models\Configuration;
use http\Env\Response;
use Illuminate\Support\ServiceProvider;
use Queue;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $setting = Configuration::first()->toArray();
        config(['configuration'=>$setting]);
        if(config('configuration.con_disable_all')==1){
           echo   json_encode(['status_code'=>500,'message'=>'Hệ thống đang nâng cấp. Vui lòng quay lại sau!']); exit();
        }
        Queue::failing(function ($connection, $job, $data) {
            @file_put_contents(storage_path('logs/job_error.log'), date('d-m-Y H:i:s') . ':' , json_encode($data), FILE_APPEND);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        //
    }

}
