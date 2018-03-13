<?php

namespace App\Console\Commands;

use App\Domain;
use App\Http\Controllers\ScanController;
use Illuminate\Console\Command;

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
			ScanController::startScanJob( $domain->token, $domain->domain );
			$this->info('Scan started for: ' . $domain->domain);
			$bar->advance();
		}
		$bar->finish();
	}
}
