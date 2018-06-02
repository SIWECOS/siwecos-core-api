<?php

namespace App\Console;

use App\Console\Commands\DailyScan;
use App\Console\Commands\RestockCredits;
use App\Console\Commands\ScannerTimeout;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command(DailyScan::class)->everyMinute()->sendOutputTo('scanResult.log', true);
        $schedule->command(ScannerTimeout::class)->everyMinute();
        $schedule->command(RestockCredits::class)->dailyAt('00:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
