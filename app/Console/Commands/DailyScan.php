<?php

namespace App\Console\Commands;

use App\Token;
use App\Http\Controllers\ScanController;
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
        $max_schedule = array_key_exists('RECURRENT_PER_RUN', $_ENV) ? $_ENV['RECURRENT_PER_RUN'] : (getenv('RECURRENT_PER_RUN') | \count($domains));
        Log::info(env('RECURRENT_PER_RUN'));
        /** @var String $domain */
        $bar = $this->output->createProgressBar(\min(\count($domains), $max_schedule));
        // If RECURRENT_PER_RUN is defined and > 0 this many scans are started
        // per run
        foreach ($domains as $domain) {
            ScanController::startScanJob(Token::whereToken($domain->token)->first(), $domain->domain, true, 10);
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
