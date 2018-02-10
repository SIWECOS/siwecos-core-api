<?php

namespace App\Jobs;

use App\Scan;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;

class ScanInisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   protected $scan;

	/**
	 * Create a new job instance.
	 *
	 * @param Scan $scan
	 */
	public function __construct( Scan $scan ) {
		$this->scan = $scan;
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

		$scanResult = $this->scan->results()->create( [
			'scanner_type' => 'ini-s',
		] );

		$callbackUrl = route( 'callback', [ 'scanId' => $scanResult->id ] );

		$client  = new Client();
		$request = new Request( 'POST', env( 'INI_S_SCANNER_URL' ) , [ 'timeout' => 0.5 ], \GuzzleHttp\json_encode( [
			'url'          => $this->scan->url,
			'callbackurls' => [ $callbackUrl ]
		] ) );
		try {
			Log::info('Calling ' . $request->getUri() );
			Log::info( 'Payload ' . $request->getBody() );
			$response = $client->sendAsync( $request, [ 'timeout' => 0.5 ] );
			$response->wait();
		} catch ( Exception $ex ) {
			// only way to make it async
			// promise running against timeout
			Log::info( 'ini-s has started' );
		}
	}

}
