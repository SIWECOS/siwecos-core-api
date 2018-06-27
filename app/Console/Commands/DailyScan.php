<?php

namespace App\Console\Commands;

use App\Domain;
use App\Http\Controllers\ScanController;
use App\Console\Commands\DB;
use App\Scan;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Log;

class DailyScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siwecos:dailyscan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts a scan for each activated domain';

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
        $test = DB::raw( <<<QUERY
        select domain from domains
        left outer join (
               select url as domain
                    , max(created_at) as last_scan
               from scans
               where recurrentscan
               group by url
        ) LS
        using(domain)
        where verified
        and (
               last_scan is null
               or
               timestampdiff(DAY, last_scan, utc_timestamp()) > 0
        )
        order by last_scan asc
QUERY
);
        Log::info(var_export($test, true));
        $domains = Domain::whereVerified('1')->get();
        /** @var Domain $domain */
        $bar = $this->output->createProgressBar(\count($domains));
        // If RECURRENT_PER_RUN is defined and > 0 this many scans are started
        // per run
        $max_schedule = array_key_exists('RECURRENT_PER_RUN', $_ENV) ? $_ENV['RECURRENT_PER_RUN'] : (getenv('RECURRENT_PER_RUN') | 0);
        foreach ($domains as $domain) {
            /** @var Scan $latestScan */
            $latestScan = $domain->scans()->whereRecurrentscan('1')->latest()->first();
            // TIME CHECK
            if ($latestScan && $latestScan instanceof Scan && $latestScan->created_at > Carbon::now()->addDays(-1)) {
                continue;
            }
            ScanController::startScanJob($domain->token, $domain->domain, true, 10);
            $this->info('Scan started for: '.$domain->domain);
            $bar->advance();
            // no more scans are allowed to be started
            if (--$max_schedule == 0) {
                break;
            }
        }
        $bar->finish();
    }
}
