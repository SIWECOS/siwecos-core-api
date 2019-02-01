<?php

namespace App\Http\Controllers;

use App\Domain;
use App\Http\Requests\ScannerStartRequest;
use App\Jobs\ScanJob;
use App\Rules\AnAvailableUrlExistsForTheDomain;
use App\Scan;
use App\ScanResult;
use App\Siweocs\Models\ScanRawResultResponse;
use App\Siweocs\Models\ScanStatusResponse;
use App\Token;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Log;

class ScanController extends Controller
{
    public function start(ScannerStartRequest $request)
    {
        $token = Token::getTokenByString(($request->header('siwecosToken')));

        Log::info('Token: '.$token->token);
        if ($token instanceof Token && $token->reduceCredits()) {
            $isNotTestRunner = $request->json('isNotATest') ?? true;
            $dangerlevel = $request->json('dangerLevel') ?? 10;

            return self::startScanJob($token, $request->json('domain'), false, $dangerlevel, $isNotTestRunner);
        }
    }

    public static function startScanJob(Token $token, string $domain, bool $isRecurrent = false, int $dangerLevel = 0, bool $isRegistered = false)
    {
        $currentDomain = Domain::getDomainOrFail($domain, $token->id);

        $scan = $token->scans()->create([
            'token_id'      => $token->id,
            'url'           => $currentDomain->domain,
            'callbackurls'  => [],
            'dangerLevel'   => $dangerLevel,
            'recurrentscan' => $isRecurrent,
        ]);

        $scan->recurrentscan = $isRecurrent ? 1 : 0;
        $scan->save();

        // dispatch each scanner to the queue
        foreach (Scan::getAvailableScanners() as $scanner) {
            ScanJob::dispatch($scanner['name'], $scanner['url'], $scan);
        }

        return response()->json(new ScanStatusResponse($scan));
    }

    public function GetResultById(int $id)
    {
        $scan = Scan::find($id);

        return response()->json(new ScanRawResultResponse($scan));
    }

    public function GetStatusById(int $id)
    {
        $scan = Scan::find($id);

        return response()->json(new ScanStatusResponse($scan));
    }

    public function status(Request $request)
    {
        $domain = Domain::whereDomain($request->get('url'))->first();
        $scan = Scan::whereUrl($domain->domain)->latest()->first();
        if ($scan instanceof Scan) {
            return response()->json(new ScanStatusResponse($scan));
        }

        return response('No results found', 422);
    }

    /**
     * @param Request $request
     *
     * @return Scan
     */
    public function startFreeScan(Request $request)
    {
        $domain = $request->json('domain');

        $request->validate([
            'domain' => ['required', new AnAvailableUrlExistsForTheDomain()],
        ]);

        $url = Domain::getDomainURL($domain);

        Log::info('Start Freescan for: '.$url);
        /** @var Domain $freeScanDomain */
        $freeScanDomain = Domain::whereDomain($url)->first();

        if ($freeScanDomain instanceof Domain) {
            return $this->startNewFreeScan($freeScanDomain);
        }
        $freeScanDomain = new Domain(['domain' => $domain]);
        $freeScanDomain->save();

        return $this->startNewFreeScan($freeScanDomain);
    }

    /**
     * Check if domain is alive or redirects.
     *
     * @param string $domain
     *
     * @return bool
     */
    public static function isDomainAlive(string $domain, Client $client = null)
    {
        $client = $client ?: new Client([
            'headers' => [
                'User-Agent' => config('app.userAgent'),
            ],
            'timeout' => 25,
        ]);

        try {
            $response = $client->get($domain);
            $allowedStatusCodes = collect([200, 301, 302, 303, 307, 308]);
            if ($allowedStatusCodes->contains($response->getStatusCode())) {
                return true;
            }
        } catch (\Exception $ex) {
            Log::warning('Domain is not alive: '.$domain);
            Log::warning($domain.' '.$ex->getMessage());

            return false;
        }

        return false;
    }

    protected function startNewFreeScan(Domain $freeScanDomain)
    {
        // start Scan and Broadcast Result afterwards
        /** @var Scan $scan */
        $scan = $freeScanDomain->scans()->create([
            'url'          => $freeScanDomain,
            'callbackurls' => [],
            'dangerLevel'  => 0,
            'freescan'     => true,
        ]);
        $scan->freescan = 1;
        $scan->save();

        // dispatch each scanner to the queue
        foreach (Scan::getAvailableScanners() as $scanner) {
            ScanJob::dispatch($scanner['name'], $scanner['url'], $scan);
        }

        return response()->json(new ScanStatusResponse($scan));
    }

    public function getLastScanDate(string $format, string $domain)
    {
        /** @var Scan $currentLastScan */
        $domainReal = 'https://'.$domain;
        $currentLastScan = Scan::whereUrl($domainReal)->where('status', '3')->whereFreescan(false)->get()->last();
        if ($currentLastScan instanceof Scan) {
            return $currentLastScan->updated_at->format($format);
        }
        $domainReal = 'http://'.$domain;
        $currentLastScan = Scan::whereUrl($domainReal)->where('status', '3')->whereFreescan(false)->get()->last();
        if ($currentLastScan instanceof Scan) {
            return $currentLastScan->updated_at->format($format);
        }

        return response('No finished scan found', 422);
    }

    public function resultRaw(Request $request)
    {
        $token = Token::getTokenByString(($request->header('siwecosToken')));
        $domain = Domain::getDomainOrFail($request->get('domain'), $token->id);
        if ($domain instanceof Domain) {
            $latestScan = Scan::whereUrl($domain->domain)->whereStatus(3)->whereFreescan(false)->latest()->first();

            if ($latestScan instanceof Scan) {
                return response()->json(new ScanRawResultResponse($latestScan));
            }

            return response('No finished scan found.', 404);
        }

        return response('No domain found', 404);
    }

    public function resultRawFree(Request $request)
    {
        $domain = Domain::whereDomain($request->get('domain'))->first();
        if ($domain instanceof Domain) {
            $latestScan = Scan::whereUrl($domain->domain)->whereFreescan(true)->whereStatus(3)->latest()->first();

            if ($latestScan instanceof Scan) {
                return response()->json(new ScanRawResultResponse($latestScan));
            }

            return response('No finished scan found.', 404);
        }

        return response('No domain found', 404);
    }

    public function callback(Request $request, int $scanId)
    {
        /** @var ScanResult $scanResult */
        $scanResult = ScanResult::findOrFail($scanId);
        Log::info($scanId.' / '.$scanResult->scan_id.' Callback: '.json_encode($request->json()->all()));
        if (!$request->json('hasError')) {
            Log::info($request->json('score').' für '.$scanResult->id);
            $scanResult->update([
                'result'      => $request->json('tests'),
                'total_score' => $request->json('score'),
            ]);
            $scanResult->total_score = $request->json('score');
            $scanResult->has_error = 0;
            $scanResult->complete_request = $request->json()->all();
            $scanResult->save();
            //   Sends the ScanResult to the given callback urls.
            foreach ($scanResult->scan->callbackurls as $callback) {
                $client = new Client([
                    'headers' => [
                        'User-Agent' => config('app.userAgent'),
                    ],
                ]);

                $request = new Request('POST', $callback, [
                    'body' => $scanResult,
                ]);

                $client->sendAsync($request);
            }
        } else {
            $scanResult->update([
                'result'        => $request->json('tests'),
                'total_score'   => $request->json('score'),
                'error_message' => $request->json('errorMessage'),
            ]);
            $scanResult->has_error = 1;
            $scanResult->complete_request = $request->json()->all();
            $scanResult->save();
            $scanResult->save();
        }

        $this->updateScanStatus(Scan::find($scanResult->scan_id));
    }

    protected function updateScanStatus(Scan $scan)
    {
        Log::info('Get Progress from id: '.$scan->id.' for '.$scan->url);
        if ($scan->getProgress() >= 99) {
            $scan->update([
                'status' => 3,

            ]);
            Log::info('Ready to update '.$scan->id.' to status 3');
            // SCAN IS FINISHED! INFORM USER
            if ($scan->recurrentscan === 1 && $scan->results->count() === Scan::getAvailableScanners()->count()) {
                //CHECK LAST NOTIFICATION
                // SHOULD FIX #28 IN BLA
                $domainString = $scan->url;
                Log::info('TRY TO GET DOMAIN OBJECT FOR '.$domainString);
                /** @var Domain $domain */
                $domain = Domain::whereDomain($domainString)->first();
                Log::info('SCAN FINISHED FOR'.$domainString.'//'.$domain->domain_token);
                $totalScore = 0;
                /** @var ScanResult $result */
                foreach ($scan->results() as $result) {
                    $totalScore += $result->total_score;
                }

                $totalScore /= Scan::getAvailableScanners()->count();
                Log::info('TOTAL SCORE FOR DOMAIN '.$domain->domain.' // '.$totalScore);
                if ($domain instanceof Domain && ($domain->last_notification === null || $domain->last_notification < Carbon::now()->addWeeks(-1)) && $totalScore <= 50) {
                    Log::info('LAST NOTIFICATION FOR '.$domainString.' EARLIER THEN 1 WEEK');
                    $domain->last_notification = Carbon::now();
                    $domain->save();
                    $client = new Client([
                        'headers' => [
                            'User-Agent' => config('app.userAgent'),
                        ],
                    ]);
                    $client->get(env('BLA_URL', 'https://api.siwecos.de/bla/current/public').'/api/v1/generateLowScoreReport/'.$scan->id);
                    Log::info('CONNECT REPORT GEN ON '.env('BLA_URL'));
                }
            }
            $scan->save();
            Log::info('Done updating   '.$scan->id.' to status 3');
            // Call broadcasting api from business layer
            $client = new Client([
                'headers' => [
                    'User-Agent' => config('app.userAgent'),
                ],
            ]);
            $client->get(env('BLA_URL', 'https://api.siwecos.de/bla/current/public').'/api/v1/freescan/'.$scan->id);
            Log::info('CONNECT FREESCAN ON '.env('BLA_URL'));
        }
    }
}
