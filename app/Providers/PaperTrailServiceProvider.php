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
        $syslogHandler = new SyslogUdpHandler(env('PAPERTRAIL_URL'), env('PAPERTRAIL_PORT'));

        $formatter = new LineFormatter('%channel%.%level_name%: %message% %extra%');
        $syslogHandler->setFormatter($formatter);

        $monolog->pushHandler($syslogHandler);
    }
}
