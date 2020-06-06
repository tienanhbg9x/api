<?php

namespace App\Providers;
use Foolz\SphinxQL\Drivers\Mysqli\Connection;
use Illuminate\Support\ServiceProvider;
use Foolz\SphinxQL\SphinxQL;
use App\Helper\Demo;

class SphinxServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton('sphinx', function ($app) {
            $conn = new Connection();
            $conn->setParams(array('host' => config('sphinx.host'), 'port' => config('sphinx.port')));
            $sphinx = new SphinxQL($conn);
//            $sphinx->match('','','')/;
            return $sphinx;
        });
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
//        dd($sphinxQL);
//        $this->publishes([
//            ## Original
//            #__DIR__.'../../../../config/sphinxsearch.php' => config_path('sphinxsearch.php'),
//            ## https://github.com/sngrl/sphinxsearch/issues/3
//            __DIR__.'/../../../config/sphinx.php' => config('sphinx'),
//        ]);
    }
}
