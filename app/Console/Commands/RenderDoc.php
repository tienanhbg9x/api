<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RenderDoc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:render';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $routers = app()->router->getRoutes();
       dd($routers);
        foreach ($routers as $route){
            dd($route);
        }
        //
    }
}
