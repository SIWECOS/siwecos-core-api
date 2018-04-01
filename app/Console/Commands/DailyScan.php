<?php

namespace App\Console\Commands;

use App\Domain;
use App\Scan;
use App\Http\Controllers\ScanController;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

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
		$bar = $this->output->createProgressBar(count($domains));
		foreach ( $domains as $domain ) {
            /** @var Scan $latestScan */
            $latestScan = $domain->scans()->latest()->get();
		    if ($domain->scans()->latest()->get() > Carbon::now()->addDays(-1)){
		        Log::info('domain: ' . $domain->domain . ' / last scan: ' . $latestScan->updated_at);
		        continue;
            }
			ScanController::startScanJob( $domain->token, $domain->domain );
			$this->info('Scan started for: ' . $domain->domain);
			$bar->advance();
		}
		$bar->finish();
	}
}
