<?php

namespace App\Jobs;

use App\ScanResult;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client;
use App\Scan;
use GuzzleHttp\Psr7\Request;
use Log;
use Psr\Http\Message\ResponseInterface;

class ScanJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $scan;
	protected $name;
	protected $scanner_url;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct( string $name, string $scanner_url, Scan $scan ) {
		$this->scan        = $scan;
		$this->name        = $name;
		$this->scanner_url = $scanner_url;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() {
		$this->scan->update( [
			'status' => 2
		] );

		/** @var ScanResult $scanResult */
		$scanResult = $this->scan->results()->create( [
			'scanner_type' => $this->name,
		] );

		$callbackUrl = route( 'callback', [ 'scanId' => $scanResult->id ] );
		Log::info('Callback Route' . $callbackUrl);
		$client  = new Client();
		$request = new Request( 'POST', $this->scanner_url, [ 'content-type' => 'application/json' ], \GuzzleHttp\json_encode( [
			'url'          => $this->scan->url,
			'callbackurls' => [ $callbackUrl ],
			'dangerLevel'  => $this->scan->dangerLevel,
		] ) );
		$response = $client->sendAsync( $request );
		try {
			/** @var Response $promise */
			$promise = $response->wait();
			$status  = $promise->getStatusCode();
			Log::info( "StatusCode for ".$this->name." (".$scanResult->scan_id."): " . $status );
			if ( $status !== 200 ) {
				$scanResult->result = self::getErrorArray($this->scan, $status);
			}

		} catch ( Exception $ex ) {
			$scanResult->result = self::getErrorArray($this->scan, 500, $ex->getMessage());
			// only way to make it async
			Log::info( $this->name . ' has started' );
		}
	}

	public static function getErrorArray( string $scanner, int $status, string $exception = '' ) {
		$timeout                                         = array();
		$timeout['name']                                 = 'SCANNER_ERROR';
		$timeout['hasError']                             = true;
		$timeout['dangerlevel']                          = 0;
		$timeout['score']                                = 0;
		$timeout['scoreType']                            = 'success';
		$timeout['testDetails']                          = array();
		$timeout['errorMessage']                         = array();
		$timeout['errorMessage']['placeholder']          = 'SCANNER_ERROR';
		$timeout['errorMessage']['values']               = array();
		$timeout['errorMessage']['values']['scanner']    = $scanner;
		$timeout['errorMessage']['values']['statuscode'] = $status;
		$timeout['errorMessage']['values']['exception'] = $status;

		return array( $timeout );

	}

}
