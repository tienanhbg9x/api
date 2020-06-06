<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Elasticsearch\ClientBuilder;

class ElasticsearchProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('elastic', function ($app) {
            if(config('app.elastic_username')!=null){
                $config = [config('app.elastic_username').":".config('app.elastic_password')."@".config('app.elastic_host').":".config('app.elastic_port')];
            }else{
                $config = [config('app.elastic_host').":".config('app.elastic_port')];
            }
            $config_elastic['hosts'] =$config;
            $client = ClientBuilder::create()->setHosts([config('app.elastic_host').":".config('app.elastic_port')])->build();
            return  $client;
        });
    }
}
