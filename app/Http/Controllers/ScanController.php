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
        $scanResult = ScanResult::findOrFail($scanId);

        if (!$request->json('hasError')) {

            $scanResult->update([
                'result'            => $request->json('tests'),
                'total_score'       => $request->json('score'),
                'has_error'         => 0,
                'complete_request'  => $request->json()->all(),
            ]);

            //   Sends the ScanResult to the given callback urls.
            foreach ($scanResult->scan->callbackurls as $callbackURL) {
                $client = new Client([
                    'headers' => [
                        'User-Agent' => config('app.userAgent'),
                    ],
                ]);

                $request = new Request('POST', $callbackURL, [
                    'body' => $scanResult,
                ]);

                $client->sendAsync($request);
            }

        } else {
            $scanResult->update([
                'result'        => $request->json('tests'),
                'total_score'   => $request->json('score'),
                'has_error'     => true,
                'error_message' => $request->json('errorMessage'),
                'complete_request' => $request->json()->all(),
            ]);
        }

        $this->updateScanStatus($scanResult->scan);
    }

    /**
     * Updates the status of a given scan if the scan has finished.
     *
     * @param Scan $scan
     * @return boolean scan was updated / scan is finished
     */
    protected function updateScanStatus(Scan $scan)
    {
        if ($scan->getProgress() >= 99) {
            $scan->update(['status' => 3]);

            $client = new Client([
                'headers' => ['User-Agent' => config('app.userAgent')],
                'timeout' => 25,
            ]);

            // calculate totalScore
            $totalScore = 0;
            foreach ($scan->results() as $result) {
                $totalScore += $result->total_score;
            }
            $totalScore /= Scan::getAvailableScanners()->count();

            /**
             * TODO: Entfernen
             * - Die Domain-Notification hat nichts in der CORE-API zu suchen!
             * - Die Implementierung muss in der BLA erfolgen
             * Siehe https://github.com/SIWECOS/siwecos-business-layer/issues/83
             */
            $domain = Domain::whereDomain($scan->url)->first();
            if ($domain instanceof Domain && ($domain->last_notification === null || $domain->last_notification < Carbon::now()->addWeeks(-1))) {
                $domain->last_notification = Carbon::now();
                $domain->save();
            }

            $BLA_NOTIFICATION_URL = env('BLA_URL', 'https://api.siwecos.de/bla/current/public') . '/api/v1/scan/finished/';
            $client->json('POST', $BLA_NOTIFICATION_URL, [
                'scanId' => $scan->id,
                'scanUrl' => $scan->url,
                'totalScore' => $totalScore,
                'freescan' => $scan->freescan,
            ], ['masterToken' => Token::whereAclLevel(9999)->first()->token]);

            return true;
        }

        return false;
    }
}
