<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:test {type}' ;

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
        $test = $this->argument('type');

        if($test=='redis'){
            $this->testRedis();
        }
        //
    }

    // Redis cache
    //Checking if a Key exists: app('redis')->exists($key)
    //
    //Setting a key/value: app('redis')->set($key, $value)
    //
    //Getting a value by key: app('redis')->get($key)
    //
    //Setting an expiry for the key: app('redis')->expire($key, $seconds)

    function testRedis(){
//        Cache::remember('demo_redis_3',60,function(){
//            return 'hello redis';
//        });
//        Cache::forget('demo_redis_3');
    }
}
