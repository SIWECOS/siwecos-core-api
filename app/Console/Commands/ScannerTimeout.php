<?php

namespace App\Console\Commands;

use App\Scan;
use App\ScanResult;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScannerTimeout extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'siwecos:timeout';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Check for timeout';

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
		$notFinishedScans = Scan::whereStatus( '2' )->where( 'created_at', '<', Carbon::now()->addMinutes( - 5 ) )->get();
		$this->info( Carbon::now()->addMinutes( - 5 ) );
		/** @var Scan $pendingScan */
		foreach ( $notFinishedScans as $pendingScan ) {
			/** @var ScanResult $result */
			foreach ( $pendingScan->results as &$result ) {
				if ( $result->result == null ) {
					$result->result = '[{
    "name": "TIMEOUT",
    "hasError": true,
    "dangerlevel": 0,
    "errorMessage": {
        "placeholder": "NO_ERRORS",
        "values": []
    },
    "score": 0,
    "scoreType": "success",
    "testDetails": []
}]';
				}
				$result->save();
			}
			$this->info( $pendingScan->url );
			$pendingScan->status = 3;
			$pendingScan->save();
		}
	}
}
