<?php

namespace App\Jobs;

use App\Scan;
use App\ScanResult;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scan;
    protected $name;
    protected $scanner_url;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $name, string $scanner_url, Scan $scan)
    {
        $this->scan = $scan;
        $this->name = $name;
        $this->scanner_url = $scanner_url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->scan->update([
            'status' => 2,
        ]);

        /** @var ScanResult $scanResult */
        $scanResult = $this->scan->results()->create([
            'scanner_type' => $this->name,
        ]);

        $callbackUrl = env('APP_CALLBACK_URL', 'https://api.siwecos.de/ca/current/public/api/v1/callback/').$scanResult->id;
        Log::info('Callback Route'.$callbackUrl);
        $client = new Client();
        $request = new Request('POST', $this->scanner_url, ['content-type' => 'application/json'], \GuzzleHttp\json_encode([
            'url'          => $this->scan->url,
            'callbackurls' => [$callbackUrl],
            'dangerLevel'  => $this->scan->dangerLevel,
        ]));
        $response = $client->sendAsync($request);

        try {
            /** @var Response $promise */
            $promise = $response->wait();
            $status = $promise->getStatusCode();
            Log::info('StatusCode for '.$this->name.' ('.$scanResult->scan_id.'): '.$status);
            if ($status !== 200) {
                $scanResult->result = self::getErrorArray($this->scan, $status);
            }
        } catch (Exception $ex) {
            $scanResult->result = self::getErrorArray($this->scan, 500, $ex->getMessage());
            // only way to make it async
            Log::info($this->name.' has started');
        }
    }

    public static function getErrorArray(string $scanner, int $status, string $exception = '')
    {
        $timeout = [];
        $timeout['name'] = 'SCANNER_ERROR';
        $timeout['hasError'] = true;
        $timeout['dangerlevel'] = 0;
        $timeout['score'] = 0;
        $timeout['scoreType'] = 'success';
        $timeout['testDetails'] = [];
        $timeout['errorMessage'] = [];
        $timeout['errorMessage']['placeholder'] = 'SCANNER_ERROR';
        $timeout['errorMessage']['values'] = [];
        $timeout['errorMessage']['values']['scanner'] = $scanner;
        $timeout['errorMessage']['values']['statuscode'] = $status;
        $timeout['errorMessage']['values']['exception'] = $status;

        return [$timeout];
    }
}
