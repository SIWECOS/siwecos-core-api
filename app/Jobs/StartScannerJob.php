<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Scan;
use App\HTTPClient;

class StartScannerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $scan;
    public $scannerCode;
    public $scannerUrl;
    public $client;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Scan $scan, string $scannerCode, string $scannerUrl, HTTPClient $client = null)
    {
        $this->scan = $scan;
        $this->scannerCode = $scannerCode;
        $this->scannerUrl = $scannerUrl;
        $this->client = $client ?: new HTTPClient();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $logInfo = '\n' . 'Scan ID: ' . $this->scan->id . '\n' . 'Scan URL: ' . $this->scan->url . '\n' . 'Scanner: ' . $this->scannerCode . '\n' . 'Scanner-URL: ' . $this->scannerUrl;

        \Log::debug('Preparing ScanResult' . $logInfo);
        $scanResult = $this->scan->results()->create([
            'scanner_code' => $this->scannerCode
        ]);

        \Log::debug('Sending scan start request' . $logInfo);
        $response = $this->client->request('POST', $this->scannerUrl, ['json' => [
            'url' => $this->scan->url,
            'dangerLevel' => $this->scan->dangerLevel,
            'callbackurls' => $this->scan->callbackurls
        ]]);


        if ($response->getStatusCode() === 200) {
            \Log::info('Scan successful started' . $logInfo);
        } else {
            \Log::critical('Failed to start scan' . $logInfo);
            $scanResult->update([
                'has_error' => true
            ]);
        }
    }
}
