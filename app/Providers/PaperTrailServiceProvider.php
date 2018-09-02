<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogUdpHandler;

class PaperTrailServiceProvider extends ServiceProvider
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
        $monolog = Log::getMonolog();

        // Always add the stderr output for errors over WARNING level.
        $monolog->pushHandler(
            new \Monolog\Handler\StreamHandler('php://stderr', \Monolog\Logger::WARNING)
        );

        // Conditionally add stdout debug.
        if (config('app.debug')) {
            $monolog->pushHandler(
                new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG)
            );
        }
    }
}
