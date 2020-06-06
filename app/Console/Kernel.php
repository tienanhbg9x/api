<?php

namespace App\Console;

use App\Console\Commands\ArchiveTables;
use App\Console\Commands\ClawContact;
use App\Console\Commands\CreateRewriteNoaccent;
use App\Console\Commands\CreateSitemapVatgia;
use App\Console\Commands\CronGooglePlace;
use App\Console\Commands\ResetClassifiedVip;
use App\Console\Commands\SyncElasticSearch;
use App\Console\Commands\SearchCategory;
use App\Console\Commands\SynchronizationLocation;
use App\Console\Commands\Test;
use App\Console\Commands\UpdateClassified;
use App\Console\Commands\UpdateClassifiedData;
use App\Console\Commands\UpdateGeoLocation;
use App\Console\Commands\UpdateLocationToAddress;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //        CreateResourceApi::class,
        Commands\UpdateLocation::class,
        SearchCategory::class,
        ArchiveTables::class,
//        SwitchCategory::class,
        SyncElasticSearch::class,
        CronGooglePlace::class,
        SynchronizationLocation::class,
        Test::class,
        ClawContact::class,
        UpdateGeoLocation::class,
        ResetClassifiedVip::class,
        UpdateClassified::class,
        UpdateClassifiedData::class,
        CreateRewriteNoaccent::class,
        CreateSitemapVatgia::class,
        UpdateLocationToAddress::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
