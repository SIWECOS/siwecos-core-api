<?php

namespace App\Console\Commands;

use App\Domain;
use App\Scan;
use App\Http\Controllers\ScanController;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Log;

class DailyScan extends Command {
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
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$domains = Domain::whereVerified( '1' )->get();
		/** @var Domain $domain */
		$bar = $this->output->createProgressBar( \count($domains));
		foreach ( $domains as $domain ) {
            /** @var Scan $latestScan */
            $latestScan = $domain->scans()->where('recurrentscan', '=', '1')->latest()->first();
		    if ($latestScan && $latestScan instanceof Scan && $latestScan->updated_at > Carbon::now()->addDays(-1) && $latestScan->created_at > Carbon::now()->addHours(-2)){
		        continue;
            }
			ScanController::startScanJob( $domain->token, $domain->domain, true, 10 );
			$this->info('Scan started for: ' . $domain->domain);
			$bar->advance();
		}
		$bar->finish();
	}
}
