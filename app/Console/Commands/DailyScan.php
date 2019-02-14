<?php

namespace App\Console\Commands;

use App\Http\Controllers\ScanController;
use App\Token;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
        // Get domains which are due to be scanned
        // Longest waiting first.
        $domains = DB::select(DB::raw(<<<'QUERY'
        select domain, token from domains
        left outer join (
               select url as domain
                    , max(created_at) as last_scan
               from scans
               where recurrentscan
               group by url
        ) LS
        using(domain)
        join(tokens)
        on(token_id=tokens.id)
        where verified
        and (
               last_scan is null
               or
               timestampdiff(DAY, last_scan, utc_timestamp()) > 0
        )
        order by last_scan asc
QUERY
        ));
        $max_schedule = min(env('RECURRENT_PER_RUN'), \count($domains));

        $this->info('Max Schedule : '.$max_schedule);
        /** @var string $domain */
        if($max_schedule) {
            $bar = $this->output->createProgressBar($max_schedule);
            // If RECURRENT_PER_RUN is defined and > 0 this many scans are started per run
            foreach ($domains as $domain) {
                ScanController::startScanJob($domain->domain, true, 10, [ 'https://bla.siwecos.de/api/v1/scan/finished' ]);
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
}
