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

        if ($token instanceof Token && $token->reduceCredits()) {

            $url = Domain::getDomainURL($request->json('domain'));
            return self::startScanJob($url, false, $request->json('dangerLevel') ?: 0, $request->json('callbackurls') ?: []);
        }
    }

    public function startFreeScan(ScannerStartRequest $request)
    {
        $url = Domain::getDomainURL($request->json('domain'));

        Log::info('Start Freescan for: ' . $url);

        $freeScanDomain = Domain::whereDomain($url)->first();

        if (!($freeScanDomain instanceof Domain)) {
            $freeScanDomain = new Domain(['domain' => $request->json('domain')]);
            $freeScanDomain->save();
        }

        return $this->startScanJob($freeScanDomain->domain, false, 0, $request->json('callbackurls') ?: [], true);
    }


    public static function startScanJob(string $url, bool $isRecurrent = false, int $dangerLevel = 0, array $callbackurls, bool $isFreescan = false)
    {
        $scan = Scan::create([
            'url' => $url,
            'callbackurls' => $callbackurls,
            'dangerLevel' => $dangerLevel,
            'recurrentscan' => $isRecurrent,
            'freescan' => $isFreescan,
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
            Log::warning('Domain is not alive: ' . $domain);
            Log::warning($domain . ' ' . $ex->getMessage());

            return false;
        }

        return false;
    }



    public function getLastScanDate(string $format, string $domain)
    {
        /** @var Scan $currentLastScan */
        $domainReal = 'https://' . $domain;
        $currentLastScan = Scan::whereUrl($domainReal)->where('status', '3')->whereFreescan(false)->get()->last();
        if ($currentLastScan instanceof Scan) {
            return $currentLastScan->updated_at->format($format);
        }
        $domainReal = 'http://' . $domain;
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

    /**
     * Only used for certificate page.
     * Returns the latest non-free scan result in order to fix the seal's presentation.
     * Will be moved to the BLA in the near future.
     *
     * @param Request $request
     * @return ScanRawResultResponse|404
     */
    public function resultRawFree(Request $request)
    {
        $domain = Domain::whereDomain($request->get('domain'))->first();
        if ($domain instanceof Domain) {
            $latestScan = Scan::whereUrl($domain->domain)->whereFreescan(false)->whereStatus(3)->latest()->first();

            if ($latestScan instanceof Scan) {
                return response()->json(new ScanRawResultResponse($latestScan));
            }

            return response('No finished scan found.', 404);
        }

        return response('No domain found', 404);
    }


    /**
     * Triggered when a SIWECOS-Scanner is finished.
     *
     * @param Request $request
     * @param integer $scanId
     * @return void
     */
    public function callback(Request $request, int $scanId)
    {
        $scanResult = ScanResult::whereId($scanId)->first();

        if ($scanResult === null) {
            Log::warning('ScanResult with ID ' . $scanId . ' not found !');
            return response('ScanResult with ID ' . $scanId . ' not found!', 404);
        }

        $scanResult->update([
            'result' => $request->json('tests'),
            'total_score' => $request->json('score'),
            'has_error' => $request->json('hasError'),
            'complete_request' => $request->json()->all(),
        ]);

        $this->updateScanStatus($scanResult->scan);
    }

    /**
     * Updates the status of a given scan if a SIWECOS-Scan has finished.
     *
     * @param Scan $scan
     * @return boolean scan was updated / scan is finished
     */
    protected function updateScanStatus(Scan $scan)
    {
        if ($scan->getProgress() == 100) {
            $scan->update(['status' => 3]);

            $client = new Client([
                'headers' => ['User-Agent' => config('app.userAgent')],
                'timeout' => 5,
            ]);

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

            foreach ($scan->callbackurls as $callbackURL) {
                // TODO: Make this async
                $client->post($callbackURL, [
                    'json' => [
                        'scanId' => $scan->id,
                        'scanUrl' => $scan->url,
                        'totalScore' => $scan->getTotalScore(),
                        'freescan' => $scan->freescan,
                        'recurrentscan' => $scan->recurrentscan,
                        'results' => $scan->results,
                    ]
                ]);
            }

            return true;
        }

        return false;
    }
}
