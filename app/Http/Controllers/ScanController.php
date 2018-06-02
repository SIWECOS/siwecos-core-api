<?php

namespace App\Http\Controllers;

use App\Scan;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Requests\ScannerStartRequest;
use App\Jobs\ScanJob;
use App\Token;
use App\Domain;
use App\ScanResult;
use App\Http\Requests\CallbackRequest;
use App\Siweocs\Models\ScanRawResultResponse;
use App\Siweocs\Models\ScanStatusResponse;
use Illuminate\Support\Carbon;
use Log;

class ScanController extends Controller {
	public function start( ScannerStartRequest $request ) {
		$token = Token::getTokenByString( ( $request->header( 'siwecosToken' ) ) );

		Log::info( 'Token: ' . $token->token );
		if ( $token instanceof Token && $token->reduceCredits() ) {
			$dangerlevel = $request->json('dangerLevel') ?? 10;
			return self::startScanJob( $token, $request->json( 'domain' ), false,  $dangerlevel, true);
		}
	}

	public static function startScanJob( Token $token, string $domain, bool $isRecurrent = false, int $dangerLevel = 0, bool $isRegistered = false ) {


		// create a new scan order
		/** @var Domain $currentDomain */
		$currentDomain = Domain::getDomainOrFail( $domain, $token->id );
		/** @var Scan $scan */
		$scan = $token->scans()->create( [
			'token_id'     => $token->id,
			'url'          => $currentDomain->domain,
			'callbackurls' => [],
			'dangerLevel'  => $dangerLevel,
            'recurrentscan' => $isRecurrent
		] );

		if ($isRegistered){
			// VALIDATION CHECK
			if (!$currentDomain->checkHtmlPage() && !$currentDomain->checkMetatags()){
				$currentDomain->verified = 0;
				$currentDomain->save();
				return response( 'Domain not verified', 422 );
			}
			$currentDomain->verified = 1;
			$currentDomain->save();

		}

		$scan->recurrentscan = $isRecurrent ? 1 : 0;
		$scan->save();

		$envVars = array_key_exists('APP_NAME', $_ENV) ? $_ENV : getenv();

		// dispatch each scanner to the queue
		foreach ( $envVars as $key => $value ) {
		    Log::info($key . ' ' . $value);
			if ( ! preg_match( "/^SCANNER_(\w+)_URL$/", $key, $scanner_name ) ) {
				continue;
			}
			if ( ! preg_match( "/^https?:\/\//", $value ) ) {
				continue;
			}
			ScanJob::dispatch( $scanner_name[1], $value, $scan );
		}

		return response()->json( new ScanStatusResponse( $scan ) );
	}

	public function GetResultById( int $id ) {
		$scan = Scan::find( $id );

		return response()->json( new ScanRawResultResponse( $scan ) );
	}

	public function GetStatusById( int $id ) {
		$scan = Scan::find( $id );

		return response()->json( new ScanStatusResponse( $scan ) );
	}

	public function status( Request $request ) {
//		$token  = Token::getTokenByString( ( $request->header( 'siwecosToken' ) ) );
//		$domain = Domain::getDomainOrFail( $request->get( 'url'), $token->id  );
		$domain = Domain::whereDomain( $request->get( 'url' ) )->first();
		$scan   = Scan::whereUrl( $domain->domain )->latest()->first();
		if ( $scan instanceof Scan ) {
			return response()->json( new ScanStatusResponse( $scan ) );
		}

		return response( 'No results found', 422 );

	}

	public function result( Request $request ) {
		// to be implemented
	}

	/**
	 * @param Request $request
	 *
	 * @return Scan
	 */
	public function startFreeScan( Request $request ) {


		$domainFilter = parse_url( $request->json( 'domain' ) );
		$domain       = $domainFilter['scheme'] . '://' . $domainFilter['host'];

		//PING THE GIVEN DOMAIN
		if (!self::isDomainAlive($domain)){
			Log::info('Domain not found ' . $domain);
			return response( 'Domain not alive', 422 );
		}


		Log::info( 'Start Freescan for:' . $domain );
		/** @var Domain $freeScanDomain */
		$freeScanDomain = Domain::whereDomain( $domain )->first();

		if ( $freeScanDomain instanceof Domain ) {
			//Domain already taken or another freescan has taken
			/** @var Scan $lastScan */
//			$lastScan = $freeScanDomain->scans()->get()->last();
//			if ( $lastScan instanceof Scan ) {
//				// return minified Version
//				return response()->json( new ScanStatusResponse( $lastScan ) );
//			}

			return $this->startNewFreeScan( $freeScanDomain );
		}
		$freeScanDomain = new Domain( [ 'domain' => $domain ] );
		$freeScanDomain->save();

		return $this->startNewFreeScan( $freeScanDomain );

	}

	/**
	 * Check if Domain is Alive or redirects (200 / 301)
	 * @param string $domain
	 *
	 * @return bool
	 */
	public static function isDomainAlive(string $domain){
		$client = new Client();
		try{
			$response = $client->get($domain);
			if ($response->getStatusCode() === 200 || $response->getStatusCode() === 301 ){
				return true;
			}
		}
		catch(\Exception $ex){
			Log::info($domain . ' ' . $ex->getMessage());
			return false;
		}

		return false;
	}

	protected function startNewFreeScan( Domain $freeScanDomain ) {
		// start Scan and Broadcast Result afterwards
		/** @var Scan $scan */
		$scan = $freeScanDomain->scans()->create( [
			'url'          => $freeScanDomain,
			'callbackurls' => [],
			'dangerLevel'  => 0,
			'freescan'     => true
		] );
		$scan->freescan = 1;
		$scan->save();

		// dispatch each scanner to the queue
		foreach ( $_ENV as $key => $value ) {
			if ( ! preg_match( "/^SCANNER_(\w+)_URL$/", $key, $scanner_name ) ) {
				continue;
			}
			if ( ! preg_match( "/^https?:\/\//", $value ) ) {
				continue;
			}
			ScanJob::dispatch( $scanner_name[1], $value, $scan );
		}

		return response()->json( new ScanStatusResponse( $scan ) );
	}

	public function getLastScanDate( string $format, string $domain ) {
		/** @var Scan $currentLastScan */
		$domainReal      = 'https://' . $domain;
		$currentLastScan = Scan::whereUrl( $domainReal )->where( 'status', '3' )->whereFreescan(0)->get()->last();
		if ( $currentLastScan instanceof Scan ) {
			return $currentLastScan->updated_at->format( $format );
		}
		$domainReal      = 'http://' . $domain;
		$currentLastScan = Scan::whereUrl( $domainReal )->where( 'status', '3' )->whereFreescan(0)->get()->last();
		if ( $currentLastScan instanceof Scan ) {
			return $currentLastScan->updated_at->format( $format );
		}

		return response( 'No finished scan found', 422 );
	}


	public function resultRaw( Request $request ) {
		$token  = Token::getTokenByString( ( $request->header( 'siwecosToken' ) ) );
		$domain = Domain::getDomainOrFail( $request->get( 'domain' ), $token->id );
		if ( $domain instanceof Domain ) {
			$latestScan = Scan::whereUrl( $domain->domain )->whereStatus( 3 )->latest()->first();

			if ( $latestScan instanceof Scan ) {
				return response()->json( new ScanRawResultResponse( $latestScan ) );
			}

			return response( 'No finished scan found.', 404 );
		}

		return response( 'No domain found', 404 );

	}

	public function resultRawFree( Request $request ) {
		$domain = Domain::whereDomain( $request->get( 'domain' ) )->first();
		if ( $domain instanceof Domain ) {
			$latestScan = Scan::whereUrl( $domain->domain )->whereFreescan(0)->whereStatus( 3 )->latest()->first();

			if ( $latestScan instanceof Scan ) {
				return response()->json( new ScanRawResultResponse( $latestScan ) );
			}

			return response( 'No finished scan found.', 404 );
		}

		return response( 'No domain found', 404 );

	}


	// TODO: Check and Test
	public function callback( Request $request, int $scanId ) {

		/** @var ScanResult $scanResult */
		$scanResult = ScanResult::findOrFail( $scanId );
		Log::info( $scanId . ' / ' . $scanResult->scan_id . ' Callback: ' . json_encode( $request->json()->all() ) );
		if ( ! $request->json( 'hasError' ) ) {
		    Log::info($request->json('score') . ' für ' . $scanResult->id);
			$scanResult->update( [
				'result' => $request->json( 'tests' ),
                'total_score' => $request->json('score'),
			] );
            $scanResult->total_score = $request->json('score');
            $scanResult->has_error = 0;
            $scanResult->complete_request = $request->json()->all();
            $scanResult->save();
			//   Sends the ScanResult to the given callback urls.
			foreach ( $scanResult->scan->callbackurls as $callback ) {
				$client = new Client();

				$request = new Request( 'POST', $callback, [
					'body' => $scanResult
				] );

				$client->sendAsync( $request );
			}
		} else {
			$scanResult->update( [
				'result' => $request->json( 'tests' ),
                'total_score' => $request->json('score'),
				'error_message' => $request->json('errorMessage')
			] );
			$scanResult->has_error = 1;
            $scanResult->complete_request = $request->json()->all();
            $scanResult->save();
			$scanResult->save();
		}

		$this->updateScanStatus( Scan::find( $scanResult->scan_id ) );
	}

	protected function updateScanStatus( Scan $scan ) {
		Log::info( 'Get Progress from id: ' . $scan->id . ' for ' . $scan->url );
		if ( $scan->getProgress() >= 99 ) {
			$scan->update( [
				'status' => 3



			] );
			// SCAN IS FINISHED! INFORM USER
			if ($scan->recurrentscan === 1 && $scan->results->count() === 5){
				//CHECK LAST NOTIFICATION
				// SHOULD FIX #28 IN BLA
				$domainString = $scan->url;
				/** @var Domain $domain */
				$domain = Domain::whereDomain($domainString)->first()->get();
				if ($domain instanceof Domain && ($domain->last_notification === null || $domain->last_notification < Carbon::now()->addWeeks(-1)))
				{
					Log::info("LAST NOTIFICATION FOR " . $domainString . " EARLIER THEN 1 WEEK");
					$domain->last_notification = Carbon::now();
					$domain->save();
					$client = new Client();
					$client->get( 'https://api.siwecos.de/bla/current/public/api/v1/generateLowScoreReport/' . $scan->id );
				}

			}
			$scan->save();

			// Call broadcasting api from business layer
			$client = new Client();
			$client->get( 'https://bla.staging2.siwecos.de/api/v1/freescan/' . $scan->id );
		}
	}

}
