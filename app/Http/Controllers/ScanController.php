<?php

namespace App\Http\Controllers;

use App\Jobs\ScanInisJob;
use App\Scan;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Requests\ScannerStartRequest;
use App\Jobs\ScanHeadersJob;
use App\Jobs\ScanDOMXSSJob;
use App\Jobs\ScanInfoLeakJob;
use App\Token;
use App\Domain;
use App\ScanResult;
use App\Http\Requests\CallbackRequest;
use App\Siweocs\Models\ScanRawResultResponse;
use App\Siweocs\Models\ScanStatusResponse;
use Log;

class ScanController extends Controller {
	public function start( ScannerStartRequest $request ) {
		Log::info( 'start scan start request' );
		$token = Token::getTokenByString( ( $request->header( 'siwecosToken' ) ) );
		Log::info( 'User ' . $token->id . ' requested Scan Start' );
		if ( $token->reduceCredits() ) {
			Log::info( 'User ' . $token->id . ' requested Scan Start' );
			// create a new scan order
			$scan = $token->scans()->create( [
				'token_id'     => $token->id,
				'url'          => Domain::getDomainOrFail( $request->get( 'domain' ), $token->id )->domain,
				'callbackurls' => $request->get( 'callbackurls' ),
				'dangerLevel'  => $request->get( 'dangerLevel' ),
			] );

			// dispatch each scanner to the queue
			ScanHeadersJob::dispatch( $scan );
			ScanDOMXSSJob::dispatch( $scan );
			ScanInfoLeakJob::dispatch( $scan );
			ScanInisJob::dispatch( $scan );

			// TODO: dispatch TLS-Scanner

			// TODO: Send Response
			return response()->json( [ 'success' => true, 'message' => 'scan started' ] );
		}
	}

	public function status( Request $request ) {
		$token  = Token::getTokenByString( ( $request->header( 'siwecosToken' ) ) );
		$domain = Domain::getDomainOrFail( $request->get( 'url', $token->id ) );

		$scan = Scan::whereDomain( $domain->domain )->latest()->first();

		return response()->json( new ScanStatusResponse( $scan ) );

	}

	public function result( Request $request ) {
		// to be implemented
	}


	public function resultRaw( Request $request ) {
		$token  = Token::getTokenByString( ( $request->header( 'siwecosToken' ) ) );
		$domain = Domain::getDomainOrFail( $request->get( 'domain' ), $token->id );

		$latestScan = $token->scans()->whereUrl( $domain->domain )->whereStatus( 3 )->latest()->first();

		if ( $latestScan instanceof Scan ) {
			return response()->json( new ScanRawResultResponse( $latestScan ) );
		}

		return response( 'No finished scan found.', 404 );
	}


	// TODO: Check and Test
	public function callback( Request $request, int $scanId ) {

		$scanResult = ScanResult::findOrFail( $scanId );
		Log::warning( 'Callback: ' . json_encode( $request->json()->all() ) );
		if ( ! $request->json( 'hasError' ) ) {
			$scanResult->update( [
				'result' => $request->json( 'tests' )
			] );

			//   Sends the ScanResult to the given callback urls.
			foreach ( $scanResult->scan->callbackurls as $callback ) {
				$client = new Client();

				$request = new Request( 'POST', $callback, [
					'body' => $scanResult
				] );

				$client->sendAsync( $request );
			}
		}

		if ($scanResult->result === null || !isset($scanResult->result)){
			$scanResult->update( [
				'result' => array()
			] );
		}

		Log::warning( 'CALLBACK FOR SCAN: ' . $scanId );
		$this->updateScanStatus( Scan::find( $scanResult->scan_id )->first() );
	}

	protected function updateScanStatus( Scan $scan ) {
		if ( $scan->getProgress() >= 99 ) {
			$scan->update( [
				'status' => 3
			] );
			$scan->save();
		}
	}

}
