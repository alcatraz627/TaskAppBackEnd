<?php

namespace App\Console;

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
        // Package to be able to use `php artisan vendor:publish` in lumen
        // https://github.com/laravelista/lumen-vendor-publish^5.8
        \Laravelista\LumenVendorPublish\VendorPublishCommand::class,
        // Tinker for Lumen
        // https://stackoverflow.com/questions/42635016/command-tinker-is-not-defined
        \Laravel\Tinker\Console\TinkerCommand::class,
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
