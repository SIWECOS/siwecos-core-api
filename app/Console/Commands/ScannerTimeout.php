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
		$timeout = getenv('SCANNER_TIMEOUT');
		if( $timeout ) {
			$timeout = intval( $timeout, 10 );
		} else {
			$timeout = 300;
		}
		$start_time = Carbon::now()->subSeconds( $timeout );
		$notFinishedScans = Scan::whereStatus( '2' )->where( 'created_at', '<', $start_time )->get();
		$this->info( $start_time );
		/** @var Scan $pendingScan */
		foreach ( $notFinishedScans as $pendingScan ) {
			/** @var ScanResult $result */
			foreach ( $pendingScan->results as &$result ) {
				if ( $result->result == null ) {
					$result->result = self::getTimeOutArray($result->scanner_type, $timeout);
				}
				$result->save();
			}
			$this->info( $pendingScan->url );
			$pendingScan->status = 3;
			$pendingScan->save();
		}
	}

	public static function getTimeOutArray( string $scanner, $to_val ) {
		$timeout                                           = array();
		$timeout['name']                                   = 'TIMEOUT';
		$timeout['hasError']                               = true;
		$timeout['dangerlevel']                            = 0;
		$timeout['score']                                  = 0;
		$timeout['scoreType']                              = 'success';
		$timeout['testDetails']                            = array();
		$timeout['errorMessage']                           = array();
		$timeout['errorMessage']['placeholder']            = 'SCANNER_TIMEOUT';
		$timeout['errorMessage']['values']                 = array();
		$timeout['errorMessage']['values']['scanner']      = $scanner;
		$timeout['errorMessage']['values']['timeoutvalue'] = $to_val;
		return array($timeout);

	}
}
