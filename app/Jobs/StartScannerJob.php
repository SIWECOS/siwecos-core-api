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
        $this->client = $client;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->scan->started_at == null) {
            $this->scan->update(['started_at' => now()]);
        }

        $client = $this->client ?: new HTTPClient();
        $logInfo = PHP_EOL . 'Scan ID: ' . $this->scan->id . PHP_EOL . 'Scan URL: ' . $this->scan->url . PHP_EOL . 'Scanner: ' . $this->scannerCode . PHP_EOL . 'Scanner-URL: ' . $this->scannerUrl;

        \Log::debug('Preparing ScanResult' . $logInfo);
        $scanResult = $this->scan->results()->create([
            'scanner_code' => $this->scannerCode
        ]);

        \Log::debug('Sending scan start request' . $logInfo);
        try {
            $response = $client->request('POST', $this->scannerUrl, ['json' => [
                'url' => $this->scan->url,
                'dangerLevel' => $this->scan->dangerLevel,
                'callbackurls' => [
                    config('app.url') . '/api/v2/callback/' . $scanResult->id
                ]
            ]]);


            if ($response->getStatusCode() === 200) {
                \Log::info('Scan successful started' . $logInfo);
            } else {
                \Log::critical('Failed to start scan' . $logInfo);
                $scanResult->update([
                    'has_error' => true
                ]);
            }
        } catch (\Exception $e) { }
    }
}
