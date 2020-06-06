<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            ## Original
            #__DIR__.'../../../../config/sphinxsearch.php' => config_path('sphinxsearch.php'),
            ## https://github.com/sngrl/sphinxsearch/issues/3
            __DIR__.'/../../../config/sphinxsearch.php' => config_path('sphinxsearch.php'),
        ]);
    }
}
